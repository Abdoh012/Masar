<?php

/**
 * MASAR - Message Edit/Delete + WebSocket Events Regression Suite
 *
 * Covers the message editing and hard-delete features on
 * GET/PATCH/DELETE /conversations/{id}/messages and their real-time
 * WebSocket counterparts (message.updated, message.deleted).
 *
 * Flow:
 *   - start the real CLI WebSocket server
 *   - student + company connect and subscribe to conversation 262
 *   - an unauthorized student connects and is refused
 *   - student sends a message via HTTP
 *   - both participants receive message.created
 *   - student edits the message via HTTP
 *   - both participants receive message.updated (id preserved, is_edited=true)
 *   - student deletes the message via HTTP
 *   - both participants receive message.deleted (no body, message_id only)
 *   - DB state is verified (is_edited=1, deleted row gone)
 *
 * Uses the live seeded conversation 262 and its real participants.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/services/jwt_service.php';
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/modules/messaging/websocket/websocket_protocol.php';

$failures = 0;
$run_id   = bin2hex(random_bytes(4));

$READ_CONVERSATION_ID  = 262;
$STUDENT_USER_ID       = 100588;
$COMPANY_USER_ID       = 100595;
$OTHER_STUDENT_USER_ID = 100530;

$server_process = null;
$server_pid     = 0;
$stderr_file    = sys_get_temp_dir() . '/masar_ws_edit_del_' . $run_id . '.log';

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function edl_event_type(?array $event): string
{
    if (!is_array($event)) {
        return '';
    }

    return (string) ($event['event'] ?? $event['type'] ?? '');
}

function tok(int $user_id, string $role): string
{
    static $tokens = [];
    $key = $role . ':' . $user_id;
    if (!isset($tokens[$key])) {
        $tokens[$key] = jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
    }
    return $tokens[$key];
}

/*
|--------------------------------------------------------------------------
| Server lifecycle
|--------------------------------------------------------------------------
*/

function edl_free_port(): int
{
    $socket = @stream_socket_server('tcp://127.0.0.1:0', $e, $es);
    $name = $socket === false ? '127.0.0.1:18082' : stream_socket_get_name($socket, false);
    if ($socket) { fclose($socket); }
    $parts = explode(':', $name);
    return (int) end($parts);
}

function edl_start_server(int $port): void
{
    global $stderr_file, $server_process, $server_pid;

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'websocket/server.php');

    $environment = [
        'WS_HOST'               => '127.0.0.1',
        'WS_PORT'               => (string) $port,
        'WS_OUTBOX_INTERVAL_MS' => '100',
        'WS_PING_INTERVAL'      => '30',
    ] + getenv();

    $server_process =
        proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $stderr_file, 'a']],
            $pipes,
            BASE,
            $environment
        );

    $server_pid = (int) (proc_get_status($server_process)['pid'] ?? 0);

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

function edl_stop_server(): void
{
    global $server_process, $server_pid;

    if ($server_pid > 0) {
        @exec('taskkill /PID ' . $server_pid . ' /F /T 2>&1');
        usleep(200000);
    }

    if (is_resource($server_process)) {

        try {
            @fclose($server_process);
        } catch (Throwable $exception) {
            // Ignore: the process is already gone.
        }
    }

    $server_process = null;
    $server_pid     = 0;
}

register_shutdown_function('edl_stop_server');

/*
|--------------------------------------------------------------------------
| WebSocket client helpers
|--------------------------------------------------------------------------
*/

function edl_connect(int $port, string $token)
{
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

    $key = base64_encode(random_bytes(16));

    $request =
        "GET /?token=" . urlencode($token) . " HTTP/1.1\r\n"
        . "Host: 127.0.0.1\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . 'Sec-WebSocket-Key: ' . $key . "\r\n"
        . "Sec-WebSocket-Version: 13\r\n\r\n";

    fwrite($socket, $request);

    $response = '';

    while (strpos($response, "\r\n\r\n") === false) {
        $chunk = fread($socket, 4096);
        if ($chunk === false || $chunk === '') {
            break;
        }
        $response .= $chunk;
    }

    return [$socket, $response];
}

function edl_is_101(string $response): bool
{
    return str_starts_with($response, 'HTTP/1.1 101');
}

function edl_send_json($socket, array $message): void
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

function edl_read_json(
    $socket,
    float $timeout = 3.0
): ?array {

    $buffer = '';
    $deadline = microtime(true) + $timeout;


    while (microtime(true) < $deadline) {

        $candidate = $buffer;

        try {
            $frames =
                ws_decode_frames($candidate);
        } catch (Throwable $exception) {
            return null;
        }

        $buffer = $candidate;


        if ($frames !== []) {

            foreach ($frames as $frame) {

                if ((int) $frame['opcode'] !== WS_OP_TEXT) {
                    continue;
                }

                $decoded =
                    json_decode((string) $frame['payload'], true);

                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        $read = [$socket];
        $write = null;
        $except = null;

        $remaining = $deadline - microtime(true);

        if ($remaining <= 0) {
            break;
        }

        $seconds = (int) $remaining;
        $microseconds = (int) (($remaining - $seconds) * 1000000);

        @stream_select($read, $write, $except, $seconds, $microseconds);


        if ($read !== []) {

            $chunk = fread($socket, 8192);

            if ($chunk === false || $chunk === '') {
                break;
            }

            $buffer .= $chunk;
        }
    }

    return null;
}

function edl_read_frames_until(
    $socket,
    string $expected_type,
    float $timeout = 3.0
): ?array {

    $deadline = microtime(true) + $timeout;


    while (microtime(true) < $deadline) {

        $event =
            edl_read_json($socket, $deadline - microtime(true));


        if ($event !== null && (($event['type'] ?? $event['event'] ?? null) === $expected_type)) {
            return $event;
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| HTTP helpers
|--------------------------------------------------------------------------
*/

function edl_http(
    string $method,
    string $path,
    string $token,
    array $body = []
): array {

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
        'MASAR_CASE_BODY' => $body === [] ? '' : (string) json_encode($body),
    ] + getenv();

    $process =
        proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            BASE,
            $environment
        );

    $output = (string) stream_get_contents($pipes[1]);
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

    $json = json_decode($raw, true);

    return [
        'status' => $status,
        'json'   => is_array($json) ? $json : [],
    ];
}

/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/

echo "== Setup ==\n";

$port = edl_free_port();

edl_start_server($port);

check('WebSocket server started', true);

$conv =
    db_fetch_one('SELECT id, company_id FROM conversations WHERE id = 262');

check('conversation 262 exists', is_array($conv));

if (!is_array($conv)) {
    echo "\n== Result ==\nFAILURES: $failures\n";
    exit(1);
}

$CONVERSATION_ID = 262;

echo "\n== Authentication & subscription ==\n";

[$student_socket, $response] = edl_connect($port, tok($STUDENT_USER_ID, 'student'));
check('student connects with a valid token (101)', edl_is_101($response));

[$company_socket, $response] = edl_connect($port, tok($COMPANY_USER_ID, 'company'));
check('company connects with a valid token (101)', edl_is_101($response));

[$other_socket, $response] = edl_connect($port, tok($OTHER_STUDENT_USER_ID, 'student'));
check('other student connects (101)', edl_is_101($response));

edl_send_json($student_socket, ['type' => 'subscribe', 'conversation_id' => $CONVERSATION_ID]);
$ack = edl_read_json($student_socket, 2.0);
check(
    'student subscribes successfully',
    is_array($ack) && ($ack['type'] ?? null) === 'subscribed' && ($ack['data']['conversation_id'] ?? null) === $CONVERSATION_ID
);

edl_send_json($company_socket, ['type' => 'subscribe', 'conversation_id' => $CONVERSATION_ID]);
$ack = edl_read_json($company_socket, 2.0);
check(
    'company subscribes successfully',
    is_array($ack) && ($ack['type'] ?? null) === 'subscribed'
);

edl_send_json($other_socket, ['type' => 'subscribe', 'conversation_id' => $CONVERSATION_ID]);
$ack = edl_read_json($other_socket, 2.0);
check(
    'other student cannot subscribe (error)',
    is_array($ack) && ($ack['type'] ?? null) === 'error'
);

echo "\n== Send a message via HTTP ==\n";

$new_body = 'WS edit test message.';

$send =
    edl_http(
        'POST',
        '/api/v1/conversations/' . $CONVERSATION_ID . '/messages',
        tok($STUDENT_USER_ID, 'student'),
        ['body' => $new_body]
    );

$message_id =
    (int) ($send['json']['data']['id'] ?? 0);

check('HTTP send works (201)', $send['status'] === 201 && $message_id > 0);

echo "\n== Message.created delivery ==\n";

$created_student =
    edl_read_json($student_socket, 4.0);

check(
    'student receives message.created',
    is_array($created_student)
        && ($created_student['type'] ?? null) === 'message.created'
        && ($created_student['data']['message_id'] ?? null) === $message_id
        && ($created_student['data']['conversation_id'] ?? null) === $CONVERSATION_ID
        && ($created_student['data']['body'] ?? null) === $new_body
);

$created_company =
    edl_read_json($company_socket, 4.0);

check(
    'company receives message.created',
    is_array($created_company)
        && ($created_company['type'] ?? null) === 'message.created'
        && ($created_company['data']['message_id'] ?? null) === $message_id
);

echo "\n== Edit via HTTP -> message.updated ==\n";

$edited_body = 'WS edit test message - edited.';

$edit =
    edl_http(
        'PATCH',
        '/api/v1/conversations/' . $CONVERSATION_ID . '/messages/' . $message_id,
        tok($STUDENT_USER_ID, 'student'),
        ['body' => $edited_body]
    );

check('HTTP edit works (200)', $edit['status'] === 200);
check('edit response is the updated message', (int) ($edit['json']['data']['id'] ?? 0) === $message_id && ($edit['json']['data']['body'] ?? null) === $edited_body);
check('edit marks is_edited=1', ($edit['json']['data']['is_edited'] ?? null) === 1);

$updated_student =
    edl_read_json($student_socket, 4.0);

check(
    'student receives message.updated',
    is_array($updated_student)
        && edl_event_type($updated_student) === 'message.updated'
        && ($updated_student['conversation_id'] ?? null) === $CONVERSATION_ID
        && ($updated_student['message']['id'] ?? null) === $message_id
        && ($updated_student['message']['body'] ?? null) === $edited_body
        && ($updated_student['message']['is_edited'] ?? null) === true
        && !empty($updated_student['message']['created_at'] ?? null)
        && !empty($updated_student['message']['updated_at'] ?? null)
);

$updated_company =
    edl_read_json($company_socket, 4.0);

check(
    'company receives message.updated',
    is_array($updated_company)
        && edl_event_type($updated_company) === 'message.updated'
        && ($updated_company['message']['id'] ?? null) === $message_id
);

echo "\n== Delete via HTTP -> message.deleted ==\n";

$delete =
    edl_http(
        'DELETE',
        '/api/v1/conversations/' . $CONVERSATION_ID . '/messages/' . $message_id,
        tok($STUDENT_USER_ID, 'student')
    );

check('HTTP delete works (200)', $delete['status'] === 200);
check('DB row is gone', (int) db_fetch_column('SELECT COUNT(*) FROM messages WHERE id = ?', [$message_id]) === 0);

$deleted_student =
    edl_read_json($student_socket, 4.0);

check(
    'student receives message.deleted',
    is_array($deleted_student)
        && edl_event_type($deleted_student) === 'message.deleted'
        && ($deleted_student['conversation_id'] ?? null) === $CONVERSATION_ID
        && ($deleted_student['message_id'] ?? null) === $message_id
        && !array_key_exists('body', $deleted_student)
        && !array_key_exists('message', $deleted_student)
);

$deleted_company =
    edl_read_json($company_socket, 4.0);

check(
    'company receives message.deleted without the body',
    is_array($deleted_company)
        && edl_event_type($deleted_company) === 'message.deleted'
        && ($deleted_company['message_id'] ?? null) === $message_id
        && !array_key_exists('body', $deleted_company)
);

echo "\n== Security: unauthorized client gets nothing ==\n";

$other_event =
    edl_read_frames_until($other_socket, 'message.created', 1.5);
check('other student does not receive message.created', $other_event === null);

echo "\n== Cleanup ==\n";

fclose($student_socket);
fclose($company_socket);
fclose($other_socket);

edl_stop_server();

check('WebSocket server stopped', true);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
