<?php

/**
 * MASAR - WebSocket connection hub
 *
 * In-memory registry of live connections and their authorized conversation
 * subscriptions. No client-provided conversation id is trusted: the server
 * verifies membership before a subscription is stored.
 */

require_once __DIR__ . '/websocket_protocol.php';


function ws_hub_init(): void
{
    if (!isset($GLOBALS['MASAR_WS_CONNECTIONS'])) {
        $GLOBALS['MASAR_WS_CONNECTIONS'] = [];
    }
}


function ws_hub_all(): array
{
    ws_hub_init();

    return $GLOBALS['MASAR_WS_CONNECTIONS'];
}


function ws_hub_count(): int
{
    return count(ws_hub_all());
}


function ws_hub_add(
    $socket,
    int $user_id,
    string $ip
): int {

    ws_hub_init();

    $id =
        (int) $socket;

    $GLOBALS['MASAR_WS_CONNECTIONS'][$id] = [
        'id'            => $id,
        'socket'        => $socket,
        'user_id'       => $user_id,
        'ip'            => $ip,
        'connected_at'  => microtime(true),
        'subscriptions' => [],
        'in_buffer'     => '',
        'out_buffer'    => '',
        'last_ping'     => microtime(true),
        'last_pong'     => microtime(true),
        'fragment'      => null,
        'closing'       => false,
    ];

    return $id;
}


function ws_hub_get(int $id): ?array
{
    return ws_hub_all()[$id] ?? null;
}


function ws_hub_update(int $id, array $changes): void
{
    ws_hub_init();

    if (!isset($GLOBALS['MASAR_WS_CONNECTIONS'][$id])) {
        return;
    }

    $GLOBALS['MASAR_WS_CONNECTIONS'][$id] =
        array_merge(
            $GLOBALS['MASAR_WS_CONNECTIONS'][$id],
            $changes
        );
}


function ws_hub_remove(int $id): void
{
    ws_hub_init();

    if (!isset($GLOBALS['MASAR_WS_CONNECTIONS'][$id])) {
        return;
    }

    $socket =
        $GLOBALS['MASAR_WS_CONNECTIONS'][$id]['socket'];

    if (is_resource($socket)) {
        @fclose($socket);
    }

    unset($GLOBALS['MASAR_WS_CONNECTIONS'][$id]);
}


function ws_hub_send(
    int $id,
    string $json
): void {

    $connection =
        ws_hub_get($id);

    if ($connection === null || $connection['closing']) {
        return;
    }

    ws_hub_update(
        $id,
        [
            'out_buffer' =>
                $connection['out_buffer'] .
                ws_frame_text($json),
        ]
    );
}


function ws_hub_send_error(
    int $id,
    string $code,
    string $message
): void {

    ws_hub_send(
        $id,
        (string) json_encode(
            [
                'type'    => 'error',
                'error'   => $code,
                'message' => $message,
            ],
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );
}


function ws_hub_send_ack(
    int $id,
    string $type,
    array $data = []
): void {

    ws_hub_send(
        $id,
        (string) json_encode(
            [
                'type' => $type,
                'data' => $data,
            ],
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );
}


function ws_hub_subscribe(
    int $id,
    int $conversation_id,
    int $baseline_message_id
): void {

    $connection =
        ws_hub_get($id);

    if ($connection === null) {
        return;
    }

    $subscriptions =
        $connection['subscriptions'];

    $subscriptions[$conversation_id] = [
        'baseline' => $baseline_message_id,
    ];

    ws_hub_update($id, ['subscriptions' => $subscriptions]);
}


function ws_hub_unsubscribe(
    int $id,
    int $conversation_id
): void {

    $connection =
        ws_hub_get($id);

    if ($connection === null) {
        return;
    }

    $subscriptions =
        $connection['subscriptions'];

    unset($subscriptions[$conversation_id]);

    ws_hub_update($id, ['subscriptions' => $subscriptions]);
}


/*
|--------------------------------------------------------------------------
| Broadcast
|--------------------------------------------------------------------------
|
| A message event is delivered only to authenticated connections that are
| subscribed to the event's conversation AND whose subscription baseline is
| older than the message id in the payload. This prevents replaying history
| to a fresh subscriber.
|
*/

function ws_server_event_message_id(
    array $payload
): int {

    if (isset($payload['data']['message_id'])) {
        return (int) $payload['data']['message_id'];
    }


    if (isset($payload['message_id'])) {
        return (int) $payload['message_id'];
    }


    if (isset($payload['message']['id'])) {
        return (int) $payload['message']['id'];
    }


    return 0;
}


function ws_hub_broadcast_event(
    int $conversation_id,
    array $payload
): int {

    $json =
        (string) json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

    $message_id =
        ws_server_event_message_id($payload);


    $delivered = 0;


    foreach (array_keys(ws_hub_all()) as $id) {

        $connection =
            ws_hub_get($id);


        if (
            $connection === null ||
            $connection['closing']
        ) {
            continue;
        }


        $subscription =
            $connection['subscriptions'][$conversation_id]
            ?? null;


        if ($subscription === null) {
            continue;
        }


        if ($message_id <= (int) ($subscription['baseline'] ?? 0)) {
            continue;
        }


        ws_hub_send(
            $id,
            $json
        );

        $delivered++;
    }


    return $delivered;
}


/*
|--------------------------------------------------------------------------
| Outbound flush
|--------------------------------------------------------------------------
*/

function ws_hub_flush(int $id): bool
{
    $connection =
        ws_hub_get($id);

    if ($connection === null) {
        return true;
    }


    if ($connection['out_buffer'] === '') {
        return true;
    }

    $socket =
        $connection['socket'];

    $written =
        @fwrite(
            $socket,
            $connection['out_buffer']
        );


    if ($written === false) {
        return false;
    }

    if ($written === 0) {
        return true;
    }

    ws_hub_update(
        $id,
        [
            'out_buffer' =>
                substr(
                    $connection['out_buffer'],
                    $written
                ),
        ]
    );

    return true;
}


function ws_hub_close(
    int $id,
    int $code = 1000,
    string $reason = ''
): void {

    $connection =
        ws_hub_get($id);


    if ($connection === null) {
        return;
    }

    ws_hub_update(
        $id,
        [
            'out_buffer' =>
                $connection['out_buffer'] .
                ws_frame_close($code, $reason),
            'closing' => true,
        ]
    );
}
