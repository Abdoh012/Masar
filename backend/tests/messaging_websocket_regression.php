<?php

/**
 * MASAR - WebSocket Messaging Regression Suite
 *
 * Starts the real CLI WebSocket server, performs RFC 6455 handshakes, verifies
 * JWT authentication and conversation-scoped subscriptions, sends messages
 * through the existing HTTP API and asserts that message.created events reach
 * only authorized subscribers. Also covers withdrawn applications, downtime
 * recovery through after_id and rejection of stale replay.
 *
 * Run with: php tests/messaging_websocket_regression.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/config/constants.php';
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/services/jwt_service.php';
require_once BASE . 'app/modules/messaging/websocket/websocket_protocol.php';

$failures = 0;
$run_id   = bin2hex(random_bytes(4));
$prefix   = 'MSGWS-' . $run_id . '-';

$READ_CONVERSATION_ID    = 225;
$READ_STUDENT_USER_ID    = 100532;
$READ_COMPANY_USER_ID    = 100568;
$OTHER_STUDENT_USER_ID   = 100530;

$FIXTURE_APPLICATION_ID  = 2009;
$FIXTURE_STUDENT_USER_ID = 100588;
$FIXTURE_COMPANY_USER_ID = 100595;

$stderr_file = sys_get_temp_dir()
    . DIRECTORY_SEPARATOR
    . 'masar_ws_stderr_' . $run_id . '.log';

$server_process = null;
$server_pid     = 0;
$port           = 0;

$GLOBALS['MSGWS_STATE'] = [
    'prefix'              => $prefix,
    'application_id'      => $FIXTURE_APPLICATION_ID,
    'application_status'  => null,
    'conversation_ids'    => [],
    'cleanup_done'        => false,
];

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function wsreg_token(int $user_id, string $role): string
{
    static $tokens = [];

    $key = $role . ':' . $user_id;

    if (!isset($tokens[$key])) {
        $tokens[$key] =
            jwt_issue_access_token([
                'id'   => $user_id,
                'role' => $role,
            ]);
    }

    return $tokens[$key];
}


/*
|--------------------------------------------------------------------------
| Server lifecycle
|--------------------------------------------------------------------------
*/

function wsreg_free_port(): int
{
    $socket =
        stream_socket_server(
            'tcp://127.0.0.1:0',
            $errno,
            $errstr
        );

    if ($socket === false) {
        return 18081;
    }

    $name =
        stream_socket_get_name(
            $socket,
            false
        );

    fclose($socket);

    $parts =
        explode(':', (string) $name);

    return (int) end($parts);
}

function wsreg_start_server(int $port): void
{
    global $stderr_file, $server_process, $server_pid;

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'websocket/server.php');

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['file', $stderr_file, 'a'],
        2 => ['file', $stderr_file, 'a'],
    ];

    $environment = [
        'WS_HOST'               => '127.0.0.1',
        'WS_PORT'               => (string) $port,
        'WS_OUTBOX_INTERVAL_MS' => '100',
        'WS_PING_INTERVAL'      => '5',
    ] + getenv();

    $server_process =
        proc_open(
            $command,
            $descriptors,
            $pipes,
            BASE,
            $environment
        );

    if (!is_resource($server_process)) {
        throw new RuntimeException('Unable to start WebSocket server.');
    }

    $status =
        proc_get_status($server_process);

    $server_pid =
        (int) ($status['pid'] ?? 0);

    for ($i = 0; $i < 100; $i++) {

        $probe =
            @stream_socket_client(
                'tcp://127.0.0.1:' . $port,
                $errno,
                $errstr,
                0.2
            );

        if ($probe !== false) {
            fclose($probe);
            return;
        }

        usleep(100000);
    }

    throw new RuntimeException(
        'WebSocket server did not start. stderr: '
        . (string) @file_get_contents($stderr_file)
    );
}

function wsreg_stop_server(): void
{
    global $server_process, $server_pid;

    if ($server_pid > 0) {
        @exec('taskkill /PID ' . $server_pid . ' /F /T 2>&1');
        usleep(200000);
    }

    if (is_resource($server_process)) {
        @proc_get_status($server_process);
    }

    $server_process = null;
    $server_pid     = 0;
}


/*
|--------------------------------------------------------------------------
| WebSocket client helpers
|--------------------------------------------------------------------------
*/

function wsreg_connect(
    int $port,
    ?string $token,
    string &$response = ''
) {

    $socket =
        stream_socket_client(
            'tcp://127.0.0.1:' . $port,
            $errno,
            $errstr,
            2
        );


    if ($socket === false) {
        throw new RuntimeException('Unable to connect: ' . $errstr);
    }

    stream_set_timeout($socket, 3);

    $key =
        base64_encode(random_bytes(16));

    $target =
        $token !== null
            ? '/?token=' . urlencode($token)
            : '/';

    $request =
        "GET {$target} HTTP/1.1\r\n"
        . "Host: 127.0.0.1\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . 'Sec-WebSocket-Key: ' . $key . "\r\n"
        . "Sec-WebSocket-Version: 13\r\n\r\n";

    fwrite($socket, $request);

    $response = '';

    while (
        strpos($response, "\r\n\r\n") === false
    ) {

        $chunk =
            fread($socket, 4096);


        if ($chunk === false || $chunk === '') {
            break;
        }

        $response .= $chunk;
    }

    return $socket;
}

function wsreg_is_101(string $response): bool
{
    return
        str_starts_with($response, 'HTTP/1.1 101') &&
        stripos($response, 'Sec-WebSocket-Accept:') !== false;
}

function wsreg_send_json($socket, array $message): void
{
    fwrite(
        $socket,
        ws_frame_text(
            (string) json_encode(
                $message,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            )
        )
    );
}

function wsreg_read_frames(
    $socket,
    float $timeout = 2.0
): array {

    $buffer = '';
    $deadline = microtime(true) + $timeout;


    while (microtime(true) < $deadline) {

        $candidate = $buffer;

        try {
            $frames = ws_decode_frames($candidate);
        } catch (Throwable $exception) {
            return [];
        }

        $buffer = $candidate;


        /*
         * Ignore RFC 6455 control frames (ping/pong/close): only JSON text
         * frames are application events.
         */

        $frames =
            array_values(
                array_filter(
                    $frames,
                    static fn (array $frame): bool =>
                        (int) $frame['opcode'] === WS_OP_TEXT
                )
            );


        if ($frames !== []) {
            return $frames;
        }

        $read = [$socket];
        $write = null;
        $except = null;

        $remaining =
            $deadline - microtime(true);

        if ($remaining <= 0) {
            break;
        }

        $seconds = (int) $remaining;
        $microseconds =
            (int) (($remaining - $seconds) * 1000000);

        @stream_select(
            $read,
            $write,
            $except,
            $seconds,
            $microseconds
        );


        if ($read !== []) {

            $chunk =
                fread($socket, 8192);


            if ($chunk === false || $chunk === '') {

                if (feof($socket)) {
                    break;
                }

                continue;
            }

            $buffer .= $chunk;
        }
    }

    return [];
}

function wsreg_read_json(
    $socket,
    float $timeout = 2.0
): ?array {

    $frames =
        wsreg_read_frames($socket, $timeout);


    foreach ($frames as $frame) {

        if ((int) $frame['opcode'] !== WS_OP_TEXT) {
            continue;
        }

        $decoded =
            json_decode(
                (string) $frame['payload'],
                true
            );

        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function wsreg_subscribe($socket, int $conversation_id): ?array
{
    wsreg_send_json(
        $socket,
        [
            'type'            => 'subscribe',
            'conversation_id' => $conversation_id,
        ]
    );

    return wsreg_read_json($socket, 2.0);
}


/*
|--------------------------------------------------------------------------
| HTTP helpers (reuse the existing in-process request shim)
|--------------------------------------------------------------------------
*/

function wsreg_http(
    string $path,
    string $method,
    int $user_id,
    string $role,
    ?array $body = null
): array {

    $token =
        wsreg_token($user_id, $role);

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'tests/messaging_phase1_case.php')
        . ' '
        . escapeshellarg($path)
        . ' '
        . escapeshellarg($method)
        . ' bearer '
        . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_PATH' => $path,
        'MASAR_CASE_BODY' => $body === null ? '' : (string) json_encode($body),
    ] + getenv();

    $process =
        proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            BASE,
            $environment
        );

    $output =
        (string) stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $status = 0;
    $raw = '';

    foreach (explode("\n", $output) as $line) {

        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, 7);
        }

        if (str_starts_with($line, 'BODY=')) {
            $raw = substr($line, 5);
        }
    }

    $json =
        json_decode(
            $raw,
            true
        );

    return [
        'status' => $status,
        'json'   => is_array($json) ? $json : [],
    ];
}

function wsreg_cleanup(): void
{
    global $stderr_file;

    $state = &$GLOBALS['MSGWS_STATE'];

    if (!empty($state['cleanup_done'])) {
        return;
    }

    $state['cleanup_done'] = true;

    wsreg_stop_server();

    try {

        db_execute(
            'DELETE FROM message_events WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );

        db_execute(
            'DELETE FROM messages WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );

        foreach ($state['conversation_ids'] as $conversation_id) {
            db_execute(
                'DELETE FROM conversations WHERE id = ?',
                [$conversation_id]
            );
        }

        if ($state['application_status'] !== null) {
            db_execute(
                'UPDATE training_applications SET status = ? WHERE id = ?',
                [
                    $state['application_status'],
                    (int) $state['application_id'],
                ]
            );
        }

    } catch (Throwable $exception) {
        // Cleanup must not mask the test result.
    }

    @unlink($stderr_file);
}

register_shutdown_function('wsreg_cleanup');


/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

echo "== Setup ==\n";

$fixture_application =
    db_fetch_one(
        'SELECT id, status FROM training_applications WHERE id = ?',
        [$FIXTURE_APPLICATION_ID]
    );

check(
    'seeded application 2009 resolved',
    is_array($fixture_application)
);

if (!is_array($fixture_application)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MSGWS_STATE']['application_status'] =
    (string) $fixture_application['status'];

db_execute(
    'DELETE FROM conversations WHERE application_id = ?',
    [$FIXTURE_APPLICATION_ID]
);

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['accepted', $FIXTURE_APPLICATION_ID]
);

$port = wsreg_free_port();

try {
    wsreg_start_server($port);
    check('WebSocket server started', true);
} catch (Throwable $exception) {
    check('WebSocket server started - ' . $exception->getMessage(), false);
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

echo "\n== Authentication ==\n";

$response = '';
$invalid =
    wsreg_connect($port, 'not-a-token', $response);

check(
    'an invalid JWT is rejected during the handshake (401)',
    str_starts_with($response, 'HTTP/1.1 401')
);
fclose($invalid);

$response = '';
$student_socket =
    wsreg_connect($port, wsreg_token($READ_STUDENT_USER_ID, 'student'), $response);

check(
    'a valid JWT is authenticated during the handshake (101)',
    wsreg_is_101($response)
);

$response = '';
$company_socket =
    wsreg_connect($port, wsreg_token($READ_COMPANY_USER_ID, 'company'), $response);

check(
    'the company participant connects (101)',
    wsreg_is_101($response)
);

$response = '';
$other_socket =
    wsreg_connect($port, wsreg_token($OTHER_STUDENT_USER_ID, 'student'), $response);

check(
    'a third-party student connects (101) but cannot subscribe',
    wsreg_is_101($response)
);


/*
|--------------------------------------------------------------------------
| Subscription authorization
|--------------------------------------------------------------------------
*/

echo "\n== Subscription authorization ==\n";

$student_subscribe =
    wsreg_subscribe($student_socket, $READ_CONVERSATION_ID);

check(
    'the student can subscribe to their conversation',
    is_array($student_subscribe)
        && ($student_subscribe['type'] ?? null) === 'subscribed'
);

$company_subscribe =
    wsreg_subscribe($company_socket, $READ_CONVERSATION_ID);

check(
    'the company can subscribe to the same conversation',
    is_array($company_subscribe)
        && ($company_subscribe['type'] ?? null) === 'subscribed'
);

$other_subscribe =
    wsreg_subscribe($other_socket, $READ_CONVERSATION_ID);

check(
    'a non-participant cannot subscribe (forbidden)',
    is_array($other_subscribe)
        && ($other_subscribe['type'] ?? null) === 'error'
        && ($other_subscribe['error'] ?? null) === 'forbidden'
);

$missing_subscribe =
    wsreg_subscribe($student_socket, 999999999);

check(
    'a nonexistent conversation cannot be subscribed to (not_found)',
    is_array($missing_subscribe)
        && ($missing_subscribe['error'] ?? null) === 'not_found'
);


/*
|--------------------------------------------------------------------------
| New message delivery
|--------------------------------------------------------------------------
*/

echo "\n== New message delivery ==\n";

$stale =
    wsreg_read_frames($student_socket, 0.5);

check(
    'historical messages are not broadcast to a fresh subscriber',
    $stale === []
);

$body =
    $prefix . 'new-message';

$send =
    wsreg_http(
        "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
        'POST',
        $READ_STUDENT_USER_ID,
        'student',
        ['body' => $body]
    );

check(
    'the HTTP API persists the message (201)',
    $send['status'] === 201
        && (int) ($send['json']['data']['id'] ?? 0) > 0
);

$message_id =
    (int) ($send['json']['data']['id'] ?? 0);

$student_event =
    wsreg_read_json($student_socket, 8.0);

check(
    'the sender receives exactly one message.created event',
    is_array($student_event)
        && ($student_event['type'] ?? null) === 'message.created'
);

$event_data =
    is_array($student_event['data'] ?? null)
        ? $student_event['data']
        : [];

check(
    'the event carries the correct conversation and message ids',
    (int) ($event_data['conversation_id'] ?? 0) === $READ_CONVERSATION_ID
        && (int) ($event_data['message_id'] ?? 0) === $message_id
);

check(
    'the event carries the sender and body only',
    (int) ($event_data['sender_user_id'] ?? 0) === $READ_STUDENT_USER_ID
        && ($event_data['body'] ?? null) === $body
        && !empty($event_data['created_at'])
);

check(
    'the event exposes no token or private user data',
    !isset($event_data['token'])
        && !isset($event_data['email'])
        && !isset($event_data['password'])
        && !isset($event_data['authorization'])
);

$company_event =
    wsreg_read_json($company_socket, 3.0);

check(
    'the other participant also receives the event',
    is_array($company_event)
        && ($company_event['type'] ?? null) === 'message.created'
        && (int) ($company_event['data']['message_id'] ?? 0) === $message_id
);

$other_event =
    wsreg_read_frames($other_socket, 0.6);

check(
    'the non-participant receives no message event',
    $other_event === []
);

$duplicate =
    wsreg_read_frames($student_socket, 0.6);

check(
    'the message is not broadcast twice',
    $duplicate === []
);


/*
|--------------------------------------------------------------------------
| Withdrawal policy
|--------------------------------------------------------------------------
*/

echo "\n== Withdrawal policy ==\n";

$creation =
    wsreg_http(
        '/api/v1/conversations',
        'POST',
        $FIXTURE_STUDENT_USER_ID,
        'student',
        ['application_id' => $FIXTURE_APPLICATION_ID]
    );

$fixture_conversation_id =
    (int) ($creation['json']['data']['id'] ?? 0);

check(
    'fixture conversation created for the withdrawal case',
    $creation['status'] === 201 && $fixture_conversation_id > 0
);

if ($fixture_conversation_id > 0) {
    $GLOBALS['MSGWS_STATE']['conversation_ids'][] =
        $fixture_conversation_id;

    $response = '';
    $fixture_socket =
        wsreg_connect(
            $port,
            wsreg_token($FIXTURE_STUDENT_USER_ID, 'student'),
            $response
        );

    $fixture_subscribe =
        wsreg_subscribe(
            $fixture_socket,
            $fixture_conversation_id
        );

    check(
        'the fixture participant can subscribe while accepted',
        ($fixture_subscribe['type'] ?? null) === 'subscribed'
    );

    db_execute(
        'UPDATE training_applications SET status = ? WHERE id = ?',
        ['withdrawn', $FIXTURE_APPLICATION_ID]
    );

    $withdrawn_send =
        wsreg_http(
            "/api/v1/conversations/{$fixture_conversation_id}/messages",
            'POST',
            $FIXTURE_STUDENT_USER_ID,
            'student',
            ['body' => $prefix . 'withdrawn-send']
        );

    check(
        'a withdrawn application blocks message creation (403)',
        $withdrawn_send['status'] === 403
    );

    $withdrawn_event =
        wsreg_read_frames($fixture_socket, 0.6);

    check(
        'no message.created event is produced for a refused send',
        $withdrawn_event === []
    );

    fclose($fixture_socket);

    db_execute(
        'UPDATE training_applications SET status = ? WHERE id = ?',
        ['accepted', $FIXTURE_APPLICATION_ID]
    );
}


/*
|--------------------------------------------------------------------------
| Downtime and recovery
|--------------------------------------------------------------------------
*/

echo "\n== Downtime and recovery ==\n";

$max_before =
    (int) db_fetch_column(
        'SELECT COALESCE(MAX(id), 0) FROM messages WHERE conversation_id = ?',
        [$READ_CONVERSATION_ID]
    );

wsreg_stop_server();

$offline_body =
    $prefix . 'offline-message';

$offline_send =
    wsreg_http(
        "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
        'POST',
        $READ_COMPANY_USER_ID,
        'company',
        ['body' => $offline_body]
    );

check(
    'messages still persist while the WebSocket server is down',
    $offline_send['status'] === 201
);

$offline_id =
    (int) ($offline_send['json']['data']['id'] ?? 0);

check(
    'the offline message is not lost from MySQL',
    $offline_id > 0
        && (int) db_fetch_column(
            'SELECT COUNT(*) FROM messages WHERE id = ?',
            [$offline_id]
        ) === 1
);

check(
    'an outbox row was written in the same transaction',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM message_events WHERE message_id = ?',
        [$offline_id]
    ) === 1
);

wsreg_start_server($port);
check('WebSocket server restarted', true);

$response = '';
$recovered_socket =
    wsreg_connect(
        $port,
        wsreg_token($READ_STUDENT_USER_ID, 'student'),
        $response
    );

check(
    'a client can reconnect after downtime',
    wsreg_is_101($response)
);

$recovered_subscribe =
    wsreg_subscribe(
        $recovered_socket,
        $READ_CONVERSATION_ID
    );

check(
    'the reconnected client subscribes successfully',
    ($recovered_subscribe['type'] ?? null) === 'subscribed'
);

$stale_replay =
    wsreg_read_frames($recovered_socket, 0.8);

check(
    'messages sent during downtime are not replayed as live events',
    $stale_replay === []
);

$after =
    wsreg_http(
        "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages"
        . '?after_id=' . $max_before . '&limit=20',
        'GET',
        $READ_STUDENT_USER_ID,
        'student'
    );

$after_ids = [];

foreach (($after['json']['data'] ?? []) as $row) {
    $after_ids[] = (int) ($row['id'] ?? 0);
}

check(
    'the missed message is recoverable via after_id',
    $after['status'] === 200 && in_array($offline_id, $after_ids, true)
);

fclose($student_socket);
fclose($company_socket);
fclose($other_socket);
fclose($recovered_socket);

echo "\n== Cleanup ==\n";

wsreg_cleanup();

check(
    'Cleanup: no tagged messages remain',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE body LIKE ?',
        [$prefix . '%']
    ) === 0
);

check(
    'Cleanup: no tagged outbox events remain',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM message_events WHERE body LIKE ?',
        [$prefix . '%']
    ) === 0
);

check(
    'Cleanup: application 2009 status restored',
    (string) db_fetch_column(
        'SELECT status FROM training_applications WHERE id = ?',
        [$FIXTURE_APPLICATION_ID]
    ) === (string) $GLOBALS['MSGWS_STATE']['application_status']
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
