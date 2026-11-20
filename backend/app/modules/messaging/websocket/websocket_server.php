<?php

/**
 * MASAR - WebSocket server
 *
 * Long-running, single-process RFC 6455 server built on PHP core streams.
 * Authentication reuses the JWT access tokens issued by the HTTP API, and
 * subscription authorization reuses conversation_service_access() so the
 * WebSocket channel cannot bypass HTTP authorization rules.
 *
 * New messages are delivered from the message_events outbox, which is written
 * in the same database transaction as the message itself.
 */

require_once __DIR__ . '/websocket_hub.php';
require_once __DIR__ . '/../repositories/message_event_repository.php';
require_once __DIR__ . '/../repositories/message_repository.php';


function ws_server_config(): array
{
    return [
        'host'               => (string) (getenv('WS_HOST') ?: '127.0.0.1'),
        'port'               => (int) (getenv('WS_PORT') ?: 8081),
        'max_connections'    => max(1, (int) (getenv('WS_MAX_CONNECTIONS') ?: 100)),
        'ping_interval'      => max(5, (int) (getenv('WS_PING_INTERVAL') ?: 30)),
        'outbox_interval_ms' => max(50, (int) (getenv('WS_OUTBOX_INTERVAL_MS') ?: 500)),
        'outbox_batch'       => max(1, min(200, (int) (getenv('WS_OUTBOX_BATCH') ?: 50))),
    ];
}


function ws_server_log(string $message): void
{
    error_log('[MASAR WS] ' . $message);
}


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

function ws_server_client_ip($socket = null): string
{
    if (is_resource($socket)) {

        $peer =
            stream_socket_get_name(
                $socket,
                true
            );

        if (is_string($peer) && $peer !== '') {

            $separator =
                strrpos($peer, ':');

            return
                $separator === false
                    ? $peer
                    : substr($peer, 0, $separator);
        }
    }

    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}


function ws_server_token_from_request(
    array $headers,
    array $query
): string {

    $authorization =
        (string) ($headers['authorization'] ?? '');


    if (
        preg_match(
            '/^Bearer\s+(.+)$/i',
            $authorization,
            $matches
        ) === 1
    ) {
        return trim($matches[1]);
    }

    /*
     * Browsers cannot set custom headers on a WebSocket handshake. The query
     * parameter is supported for browser clients; it must be used over TLS in
     * production. The token is never logged.
     */

    return
        trim(
            (string) ($query['token'] ?? '')
        );
}


function ws_server_authenticate(
    array $headers,
    array $query,
    string $client_ip
): ?int {

    if (
        function_exists('security_check_rate_limit')
    ) {

        $limit =
            security_check_rate_limit(
                'ws_connect',
                $client_ip,
                30,
                60
            );


        if (empty($limit['allowed'])) {
            return null;
        }
    }

    $token =
        ws_server_token_from_request(
            $headers,
            $query
        );


    if ($token === '') {
        return null;
    }

    $payload =
        jwt_validate_access_token($token);


    if ($payload === false) {
        return null;
    }

    $user_id =
        (int) ($payload['sub'] ?? 0);


    if ($user_id <= 0) {
        return null;
    }

    $user =
        auth_find_user_by_id($user_id);


    if (
        $user === null ||
        !auth_user_is_active($user)
    ) {
        return null;
    }

    return $user_id;
}


function ws_server_set_auth_user(int $user_id): bool
{
    $user =
        auth_find_user_by_id($user_id);


    if (
        $user === null ||
        !auth_user_is_active($user)
    ) {
        return false;
    }

    auth_set_user($user);

    return true;
}


/*
|--------------------------------------------------------------------------
| Handshake
|--------------------------------------------------------------------------
*/

function ws_server_on_connect(
    $client,
    array $config
): void {

    $request =
        ws_read_http_request($client);


    if ($request === null) {
        @fclose($client);
        return;
    }

    $headers =
        $request['headers'];


    $upgrade =
        strtolower((string) ($headers['upgrade'] ?? ''));

    $connection =
        strtolower((string) ($headers['connection'] ?? ''));

    $key =
        (string) ($headers['sec-websocket-key'] ?? '');

    $version =
        (string) ($headers['sec-websocket-version'] ?? '');


    $valid_upgrade =
        $upgrade === 'websocket' &&
        str_contains($connection, 'upgrade') &&
        $version === '13' &&
        base64_decode($key, true) !== false &&
        strlen(base64_decode($key, true)) === 16;


    if (!$valid_upgrade) {
        @fwrite(
            $client,
            ws_http_response(400, 'Bad Request', ['error' => 'invalid_upgrade'])
        );
        @fclose($client);
        return;
    }


    /*
     * Parse the query string without trusting any other part of the request.
     */

    /*
     * The request line is "GET /?token=... HTTP/1.1", not a URL, so parse the
     * request target directly instead of feeding the whole line to parse_url.
     */

    $request_line_parts =
        preg_split(
            '/\s+/',
            trim((string) $request['request_line'])
        );

    $request_target =
        is_array($request_line_parts) && isset($request_line_parts[1])
            ? (string) $request_line_parts[1]
            : '';

    $request_parts =
        parse_url(
            $request_target,
            PHP_URL_QUERY
        );

    $query = [];

    if (is_string($request_parts)) {
        parse_str($request_parts, $query);
    }


    $client_ip =
        ws_server_client_ip($client);

    $user_id =
        ws_server_authenticate(
            $headers,
            $query,
            $client_ip
        );


    if ($user_id === null) {

        @fwrite(
            $client,
            ws_http_response(
                401,
                'Unauthorized',
                ['error' => 'unauthorized'],
                ['WWW-Authenticate: Bearer']
            )
        );

        @fclose($client);

        ws_server_log(
            'Rejected unauthenticated connection from '
            . $client_ip
        );

        return;
    }


    if (ws_hub_count() >= $config['max_connections']) {

        @fwrite(
            $client,
            ws_http_response(
                503,
                'Service Unavailable',
                ['error' => 'server_full']
            )
        );

        @fclose($client);
        return;
    }

    @fwrite(
        $client,
        ws_handshake_response($key)
    );

    stream_set_blocking($client, false);

    $id =
        ws_hub_add(
            $client,
            $user_id,
            $client_ip
        );

    ws_server_log(
        'Connected id=' . $id . ' user=' . $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Client messages
|--------------------------------------------------------------------------
*/

function ws_server_process_message(
    int $id,
    string $text
): void {

    $connection =
        ws_hub_get($id);


    if ($connection === null) {
        return;
    }

    $message =
        json_decode(
            $text,
            true
        );


    if (
        !is_array($message) ||
        !isset($message['type']) ||
        !is_string($message['type'])
    ) {

        ws_hub_send_error(
            $id,
            'invalid_message',
            'Expected a JSON object with a type field.'
        );

        return;
    }

    $type =
        strtolower(
            trim($message['type'])
        );

    $user_id =
        (int) $connection['user_id'];


    if ($type === 'ping') {

        ws_hub_send_ack($id, 'pong');
        return;
    }


    if ($type === 'unsubscribe') {

        $conversation_id =
            (int) ($message['conversation_id'] ?? 0);


        if ($conversation_id <= 0) {

            ws_hub_send_error(
                $id,
                'validation_error',
                'A valid conversation_id is required.'
            );

            return;
        }

        ws_hub_unsubscribe($id, $conversation_id);

        ws_hub_send_ack(
            $id,
            'unsubscribed',
            ['conversation_id' => $conversation_id]
        );

        return;
    }


    if ($type === 'subscribe') {

        $conversation_id =
            filter_var(
                $message['conversation_id'] ?? null,
                FILTER_VALIDATE_INT
            );


        if (
            $conversation_id === false ||
            (int) $conversation_id <= 0
        ) {

            ws_hub_send_error(
                $id,
                'validation_error',
                'A valid conversation_id is required.'
            );

            return;
        }

        $conversation_id =
            (int) $conversation_id;


        /*
         * Re-check membership against the database on every subscription.
         * Never trust a client-provided conversation id.
         */

        if (
            !ws_server_set_auth_user($user_id)
        ) {

            ws_hub_send_error(
                $id,
                'unauthorized',
                'Authentication is no longer valid.'
            );

            return;
        }

        $access =
            conversation_service_access(
                $conversation_id,
                $user_id
            );


        if (empty($access['ok'])) {

            $code =
                (int) ($access['status'] ?? 403);

            ws_hub_send_error(
                $id,
                $code === 404 ? 'not_found' : 'forbidden',
                (string) (
                    $access['message']
                    ?? 'You are not authorized for this conversation.'
                )
            );

            return;
        }

        $baseline =
            message_event_repository_latest_message_id(
                $conversation_id
            );

        ws_hub_subscribe(
            $id,
            $conversation_id,
            $baseline
        );

        ws_hub_send_ack(
            $id,
            'subscribed',
            [
                'conversation_id'     => $conversation_id,
                'baseline_message_id' => $baseline,
            ]
        );

        return;
    }


    ws_hub_send_error(
        $id,
        'unknown_event',
        'Unsupported event type.'
    );
}


function ws_server_handle_frame(
    int $id,
    array $frame
): void {

    $opcode =
        (int) $frame['opcode'];

    $payload =
        (string) $frame['payload'];

    $fin =
        (bool) $frame['fin'];


    if ($opcode === WS_OP_PING) {

        $connection =
            ws_hub_get($id);

        if ($connection !== null) {
            ws_hub_update(
                $id,
                [
                    'out_buffer' =>
                        $connection['out_buffer'] .
                        ws_frame_pong($payload),
                ]
            );
        }

        return;
    }


    if ($opcode === WS_OP_PONG) {

        ws_hub_update($id, ['last_pong' => microtime(true)]);
        return;
    }


    if ($opcode === WS_OP_CLOSE) {

        ws_hub_close($id, 1000);
        return;
    }


    if ($opcode === WS_OP_BINARY) {

        ws_hub_send_error(
            $id,
            'unsupported_frame',
            'Only text frames are supported.'
        );

        ws_hub_close($id, 1003, 'Unsupported frame type.');
        return;
    }


    if (
        $opcode !== WS_OP_TEXT &&
        $opcode !== WS_OP_CONTINUATION
    ) {
        return;
    }

    $connection =
        ws_hub_get($id);


    if ($connection === null) {
        return;
    }

    $fragment =
        $connection['fragment'];


    if ($opcode === WS_OP_TEXT) {

        if ($fin) {
            ws_server_process_message($id, $payload);
            return;
        }

        ws_hub_update(
            $id,
            [
                'fragment' => [
                    'payload' => $payload,
                ],
            ]
        );

        return;
    }


    if ($fragment === null) {

        ws_hub_send_error(
            $id,
            'protocol_error',
            'Unexpected continuation frame.'
        );

        return;
    }

    $text =
        $fragment['payload'] . $payload;


    if ($fin) {

        ws_hub_update($id, ['fragment' => null]);
        ws_server_process_message($id, $text);

        return;
    }

    ws_hub_update(
        $id,
        ['fragment' => ['payload' => $text]]
    );
}


function ws_server_read_connection(int $id): void
{
    $connection =
        ws_hub_get($id);


    if ($connection === null) {
        return;
    }

    $socket =
        $connection['socket'];

    $chunk =
        @fread(
            $socket,
            8192
        );


    if ($chunk === false) {
        ws_hub_remove($id);
        return;
    }

    if ($chunk === '') {

        if (feof($socket)) {
            ws_hub_remove($id);
        }

        return;
    }

    $buffer =
        $connection['in_buffer'] . $chunk;


    try {

        $frames =
            ws_decode_frames($buffer);

    } catch (Throwable $exception) {

        ws_server_log(
            'Protocol error on id=' . $id . ': '
            . $exception->getMessage()
        );

        ws_hub_remove($id);
        return;
    }

    ws_hub_update($id, ['in_buffer' => $buffer]);


    foreach ($frames as $frame) {
        ws_server_handle_frame($id, $frame);
    }
}


/*
|--------------------------------------------------------------------------
| Outbox relay
|--------------------------------------------------------------------------
*/

function ws_server_build_event_payload(
    array $event
): ?array {

    $event_type =
        (string) ($event['event_type'] ?? 'message.created');


    if ($event_type === 'message.deleted') {

        return [
            'event'          => 'message.deleted',
            'conversation_id' => (int) ($event['conversation_id'] ?? 0),
            'message_id'      => (int) ($event['message_id'] ?? 0),
            'deleted_by'      => (int) ($event['sender_user_id'] ?? 0),
        ];
    }


    if ($event_type === 'message.updated') {

        $message =
            message_repository_find(
                (int) $event['message_id']
            );


        if ($message === null) {
            return null;
        }


        return [
            'event'          => 'message.updated',
            'conversation_id' => (int) ($event['conversation_id'] ?? 0),
            'message'         => [
                'id'             => (int) ($message['id'] ?? 0),
                'sender_user_id' => (int) ($message['sender_user_id'] ?? 0),
                'body'           => (string) ($message['body'] ?? ''),
                'is_edited'      => (int) ($message['is_edited'] ?? 0) === 1,
                'created_at'     => $message['created_at'],
                'updated_at'     => $message['updated_at'],
            ],
        ];
    }


    /*
     * Default: message.created.
     */

    return [
        'type' => 'message.created',
        'data' => [
            'event_id'        => (int) ($event['id'] ?? 0),
            'conversation_id' => (int) ($event['conversation_id'] ?? 0),
            'message_id'      => (int) ($event['message_id'] ?? 0),
            'sender_user_id'  => (int) ($event['sender_user_id'] ?? 0),
            'body'            => (string) ($event['body'] ?? ''),
            'created_at'      => (string) ($event['created_at'] ?? ''),
        ],
    ];
}


function ws_server_drain_outbox(
    int $batch
): void {

    try {

        $events =
            message_event_repository_pending($batch);


        if ($events === []) {
            return;
        }


        $ids = [];


        foreach ($events as $event) {

            $payload =
                ws_server_build_event_payload($event);


            if ($payload !== null) {

                ws_hub_broadcast_event(
                    (int) ($event['conversation_id'] ?? 0),
                    $payload
                );
            }

            $ids[] =
                (int) ($event['id'] ?? 0);
        }

        message_event_repository_mark_dispatched($ids);

    } catch (Throwable $exception) {

        ws_server_log(
            'Outbox drain failed: ' . $exception->getMessage()
        );

        /*
         * A stale PDO handle cannot be recovered safely inside the long-lived
         * process. Exit so the supervisor restarts the server; pending events
         * are discarded on startup and messages remain in MySQL.
         */

        exit(1);
    }
}


function ws_server_ping_all(int $interval): void
{
    $now = microtime(true);


    foreach (array_keys(ws_hub_all()) as $id) {

        $connection =
            ws_hub_get($id);

        if ($connection === null) {
            continue;
        }

        if (
            $now - $connection['last_ping'] < $interval
        ) {
            continue;
        }

        $socket =
            $connection['socket'];

        ws_hub_update(
            $id,
            [
                'out_buffer' =>
                    $connection['out_buffer'] .
                    ws_frame_ping((string) (int) $now),
                'last_ping' => $now,
            ]
        );

        /*
         * Missed pongs from the previous interval mean the peer disappeared.
         */

        if (
            $now - $connection['last_pong'] > ($interval * 3)
        ) {

            ws_server_log(
                'Closing stale connection id=' . $id
            );

            ws_hub_remove($id);
        }
    }
}


/*
|--------------------------------------------------------------------------
| Main loop
|--------------------------------------------------------------------------
*/

function ws_server_run(array $config): void
{
    ws_hub_init();

    $errno = 0;
    $errstr = '';

    $server =
        @stream_socket_server(
            'tcp://' . $config['host'] . ':' . $config['port'],
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );


    if ($server === false) {

        ws_server_log(
            'Unable to listen on '
            . $config['host'] . ':'
            . $config['port'] . ' - ' . $errstr
        );

        exit(1);
    }

    stream_set_blocking($server, false);

    /*
     * Anything left pending from a previous run is stale. Clients recover
     * missed messages through the after_id HTTP cursor instead of a replay.
     */

    $discarded =
        message_event_repository_discard_pending();

    if ($discarded > 0) {
        ws_server_log('Discarded ' . $discarded . ' stale outbox events.');
    }

    ws_server_log(
        'Listening on '
        . $config['host'] . ':' . $config['port']
    );

    $last_outbox = microtime(true);
    $last_ping = microtime(true);


    while (true) {

        $now = microtime(true);

        if (
            ($now - $last_outbox) * 1000 >=
            $config['outbox_interval_ms']
        ) {

            ws_server_drain_outbox(
                (int) $config['outbox_batch']
            );

            $last_outbox = $now;
        }

        if ($now - $last_ping >= 1) {

            ws_server_ping_all(
                (int) $config['ping_interval']
            );

            $last_ping = $now;
        }


        foreach (array_keys(ws_hub_all()) as $id) {

            $connection =
                ws_hub_get($id);

            if (
                $connection === null
            ) {
                continue;
            }

            if (
                $connection['closing'] &&
                $connection['out_buffer'] === ''
            ) {

                ws_hub_remove($id);
                continue;
            }

            if (!ws_hub_flush($id)) {
                ws_hub_remove($id);
            }
        }


        $read = [$server];
        $write = [];


        foreach (ws_hub_all() as $connection) {

            if ($connection['closing']) {
                continue;
            }

            $read[] =
                $connection['socket'];


            if ($connection['out_buffer'] !== '') {
                $write[] = $connection['socket'];
            }
        }

        $except = null;

        @stream_select(
            $read,
            $write,
            $except,
            0,
            200000
        );


        foreach ($read as $socket) {

            if ($socket === $server) {

                $client =
                    @stream_socket_accept($server, 0);

                if ($client !== false) {
                    stream_set_blocking($client, false);
                    ws_server_on_connect($client, $config);
                }

                continue;
            }

            ws_server_read_connection((int) $socket);
        }


        foreach ($write as $socket) {
            ws_hub_flush((int) $socket);
        }
    }
}
