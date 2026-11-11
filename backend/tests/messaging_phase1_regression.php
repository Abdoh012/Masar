<?php

/**
 * MASAR - Messaging Phase 1 Regression Suite
 *
 * Covers the Phase 1 fixes only:
 *   - message creation (URL conversation id, sender derivation, validation)
 *   - GET /conversations/{id}/messages query parameters
 *   - conversation-scoped authorization (IDOR/BOLA)
 *   - withdrawn-application read/send policy
 *   - transactional message creation (commit + rollback)
 *   - idempotent and concurrent conversation creation
 *
 * Fixtures:
 *   - conversation 225 (seeded) for read/authorization cases
 *   - accepted application 2009 (seeded, no conversation) for creation,
 *     query, withdrawal and transaction cases
 *
 * Every row this suite writes carries the run prefix and is removed by
 * messaging_cleanup(), which is also registered as a shutdown handler.
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
require_once BASE . 'app/modules/messaging/validators/message_validator.php';

$failures = 0;
$run_id   = bin2hex(random_bytes(4));
$prefix   = 'MSGPH1-' . $run_id . '-';

$READ_CONVERSATION_ID   = 225;
$READ_STUDENT_USER_ID   = 100532;
$READ_COMPANY_USER_ID   = 100568;
$OTHER_STUDENT_USER_ID  = 100530;
$OTHER_COMPANY_USER_ID  = 100569;

$FIXTURE_APPLICATION_ID = 2009;
$FIXTURE_STUDENT_USER_ID = 100588;
$FIXTURE_COMPANY_USER_ID = 100595;

$GLOBALS['MESSAGING_STATE'] = [
    'prefix'                 => $prefix,
    'application_id'         => $FIXTURE_APPLICATION_ID,
    'application_status'     => 'accepted',
    'read_conversation_id'   => $READ_CONVERSATION_ID,
    'read_updated_at'        => null,
    'conversation_ids'       => [],
    'trigger_names'          => [],
    'cleanup_done'           => false,
];

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function messaging_token(int $user_id, string $role): string
{
    static $tokens = [];

    $key = $role . ':' . $user_id;

    if (!isset($tokens[$key])) {
        $tokens[$key] = jwt_issue_access_token([
            'id'   => $user_id,
            'role' => $role,
        ]);
    }

    return $tokens[$key];
}

/**
 * Starts one case-runner subprocess.
 */
function messaging_start(
    string $path,
    string $method,
    string $scenario,
    string $token,
    ?array $body = null
): array {

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg(__DIR__ . '/messaging_phase1_case.php')
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_BODY' => $body === null ? '' : (string) json_encode($body),
        'MASAR_CASE_PATH' => $path,
    ];

    $process = proc_open(
        $command,
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        BASE,
        $environment + getenv()
    );

    return [
        'process' => $process,
        'pipes'   => $pipes,
        'output'  => '',
    ];
}

function messaging_finish(array $handle): array
{
    $output = '';
    $errors = '';

    if (is_resource($handle['process'])) {
        $output = (string) stream_get_contents($handle['pipes'][1]);
        $errors = (string) stream_get_contents($handle['pipes'][2]);

        fclose($handle['pipes'][1]);
        fclose($handle['pipes'][2]);

        proc_close($handle['process']);
    }

    $status   = 0;
    $body_out = '';

    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, strlen('STATUS='));
        }

        if (str_starts_with($line, 'BODY=')) {
            $body_out = substr($line, strlen('BODY='));
        }
    }

    $payload = json_decode($body_out, true);

    return [
        'status'  => $status,
        'raw'     => $body_out,
        'stderr'  => $errors,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'message' => is_array($payload) ? ($payload['message'] ?? null) : null,
        'errors'  => is_array($payload) ? ($payload['errors'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? null) : null,
    ];
}

function messaging_request(
    string $path,
    string $method,
    string $scenario,
    string $token = '',
    ?array $body = null
): array {

    return messaging_finish(
        messaging_start($path, $method, $scenario, $token, $body)
    );
}

function messaging_student_request(string $path, string $method, int $user_id, ?array $body = null): array
{
    return messaging_request($path, $method, 'bearer', messaging_token($user_id, 'student'), $body);
}

function messaging_company_request(string $path, string $method, int $user_id, ?array $body = null): array
{
    return messaging_request($path, $method, 'bearer', messaging_token($user_id, 'company'), $body);
}

function messaging_conversation_ids_for_application(int $application_id): array
{
    $rows = db_fetch_all(
        'SELECT id FROM conversations WHERE application_id = ? ORDER BY id',
        [$application_id]
    );

    return array_map('intval', array_column($rows, 'id'));
}

function messaging_delete_conversations_for_application(int $application_id): void
{
    db_execute(
        'DELETE FROM conversations WHERE application_id = ?',
        [$application_id]
    );
}

function messaging_message_count(int $conversation_id): int
{
    return (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE conversation_id = ?',
        [$conversation_id]
    );
}

function messaging_conversation_updated_at(int $conversation_id): ?string
{
    $value = db_fetch_column(
        'SELECT updated_at FROM conversations WHERE id = ?',
        [$conversation_id]
    );

    return $value === false || $value === null ? null : (string) $value;
}

/**
 * Inserts the query-parameter fixture rows in one prepared statement.
 *
 * @return int[] inserted message ids, oldest first
 */
function messaging_insert_fixture_messages(
    int $conversation_id,
    int $student_user_id,
    int $company_user_id,
    string $prefix,
    int $count
): array {

    $base = new DateTimeImmutable('2026-01-01 10:00:00', new DateTimeZone('Africa/Cairo'));

    $values = [];
    $params = [];

    for ($i = 0; $i < $count; $i++) {
        $values[] = '(?, ?, ?, 0, ?)';

        $params[] = $conversation_id;
        $params[] = ($i % 2 === 0) ? $student_user_id : $company_user_id;
        $params[] = $prefix . 'Q-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
        $params[] = $base->modify('+' . $i . ' minutes')->format('Y-m-d H:i:s');
    }

    db_execute(
        'INSERT INTO messages (conversation_id, sender_user_id, body, is_read, created_at) VALUES '
        . implode(', ', $values),
        $params
    );

    $rows = db_fetch_all(
        'SELECT id FROM messages WHERE conversation_id = ? AND body LIKE ? ORDER BY created_at ASC, id ASC',
        [$conversation_id, $prefix . 'Q-%']
    );

    return array_map('intval', array_column($rows, 'id'));
}

function messaging_create_trigger_update_block(int $conversation_id, string $name, string $signal): bool
{
    try {
        db_execute("DROP TRIGGER IF EXISTS `{$name}`");

        db_execute(
            "CREATE TRIGGER `{$name}` BEFORE UPDATE ON `conversations` "
            . "FOR EACH ROW BEGIN "
            . "IF NEW.id = {$conversation_id} THEN "
            . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '{$signal}'; "
            . "END IF; END"
        );

        return true;
    } catch (Throwable $exception) {
        echo "Trigger setup failed: {$exception->getMessage()}\n";

        return false;
    }
}

function messaging_create_trigger_insert_block(string $body_marker, string $name, string $signal): bool
{
    try {
        db_execute("DROP TRIGGER IF EXISTS `{$name}`");

        db_execute(
            "CREATE TRIGGER `{$name}` BEFORE INSERT ON `messages` "
            . "FOR EACH ROW BEGIN "
            . "IF NEW.body = '{$body_marker}' THEN "
            . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '{$signal}'; "
            . "END IF; END"
        );

        return true;
    } catch (Throwable $exception) {
        echo "Trigger setup failed: {$exception->getMessage()}\n";

        return false;
    }
}

function messaging_drop_trigger(string $name): void
{
    try {
        db_execute("DROP TRIGGER IF EXISTS `{$name}`");
    } catch (Throwable $exception) {
        // Cleanup must not mask the test result.
    }
}

function messaging_cleanup(): void
{
    $state = &$GLOBALS['MESSAGING_STATE'];

    if (!empty($state['cleanup_done'])) {
        return;
    }

    $state['cleanup_done'] = true;

    foreach ($state['trigger_names'] as $trigger_name) {
        messaging_drop_trigger($trigger_name);
    }

    try {
        db_execute(
            'DELETE FROM messages WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }

    foreach (array_unique($state['conversation_ids']) as $conversation_id) {
        try {
            db_execute('DELETE FROM conversations WHERE id = ?', [$conversation_id]);
        } catch (Throwable $exception) {
            // Best-effort cleanup.
        }
    }

    try {
        messaging_delete_conversations_for_application((int) $state['application_id']);

        db_execute(
            'UPDATE training_applications SET status = ? WHERE id = ?',
            [$state['application_status'], (int) $state['application_id']]
        );

        if (!empty($state['read_updated_at'])) {
            db_execute(
                'UPDATE conversations SET updated_at = ? WHERE id = ?',
                [$state['read_updated_at'], (int) $state['read_conversation_id']]
            );
        }
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }
}

register_shutdown_function('messaging_cleanup');

echo "== Setup: fixtures ==\n";

$read_conversation = db_fetch_one(
    'SELECT c.id, c.application_id, s.user_id AS student_user_id, co.user_id AS company_user_id,
            c.updated_at
     FROM conversations c
     INNER JOIN students s ON s.id = c.student_id
     INNER JOIN companies co ON co.id = c.company_id
     WHERE c.id = ?',
    [$READ_CONVERSATION_ID]
);

$fixture_application = db_fetch_one(
    'SELECT id, status FROM training_applications WHERE id = ?',
    [$FIXTURE_APPLICATION_ID]
);

check('seeded read conversation 225 resolved', is_array($read_conversation));
check('seeded accepted application 2009 resolved', is_array($fixture_application));

if (!is_array($read_conversation) || !is_array($fixture_application)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MESSAGING_STATE']['application_status'] = (string) $fixture_application['status'];
$GLOBALS['MESSAGING_STATE']['read_updated_at'] = (string) $read_conversation['updated_at'];

/* A deterministic starting point for the fixture application. */
messaging_delete_conversations_for_application($FIXTURE_APPLICATION_ID);
db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['accepted', $FIXTURE_APPLICATION_ID]
);

echo "\n== Message creation ==\n";

/* 1. Authenticated student sends a valid message. */
$student_message = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => $prefix . 'student-valid']
);

check('1: student sends a valid message (201)', $student_message['status'] === 201 && $student_message['success'] === true);
check(
    '1: the student message is persisted in the URL conversation',
    (int) db_fetch_column(
        'SELECT conversation_id FROM messages WHERE body = ?',
        [$prefix . 'student-valid']
    ) === $READ_CONVERSATION_ID
);
check(
    '1: the persisted sender is the authenticated student',
    (int) db_fetch_column(
        'SELECT sender_user_id FROM messages WHERE body = ?',
        [$prefix . 'student-valid']
    ) === $READ_STUDENT_USER_ID
);

/* 2. Authenticated company sends a valid message. */
$company_message = messaging_company_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_COMPANY_USER_ID,
    ['body' => $prefix . 'company-valid']
);

check('2: company sends a valid message (201)', $company_message['status'] === 201 && $company_message['success'] === true);
check(
    '2: the persisted sender is the authenticated company',
    (int) db_fetch_column(
        'SELECT sender_user_id FROM messages WHERE body = ?',
        [$prefix . 'company-valid']
    ) === $READ_COMPANY_USER_ID
);

/* 3. The URL conversation id is authoritative; no body id required. */
$url_only = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => $prefix . 'url-authoritative']
);

check('3: a body conversation_id is not required (201)', $url_only['status'] === 201);
check(
    '3: the URL conversation id was used for the insert',
    (int) db_fetch_column(
        'SELECT conversation_id FROM messages WHERE body = ?',
        [$prefix . 'url-authoritative']
    ) === $READ_CONVERSATION_ID
);

/* 4. Missing body. */
$missing_body = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    []
);

check('4: missing body is rejected (422)', $missing_body['status'] === 422 && $missing_body['success'] === false);
check('4: the refusal names the body field', isset($missing_body['errors']['body']));

/* 5. Empty and whitespace-only body. */
$empty_body = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => '']
);

$blank_body = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => "  \t \n "]
);

check('5: an empty body is rejected (422)', $empty_body['status'] === 422 && isset($empty_body['errors']['body']));
check('5: a whitespace-only body is rejected (422)', $blank_body['status'] === 422 && isset($blank_body['errors']['body']));

/* 6. More than 5000 characters. */
$too_long = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => str_repeat('a', 5001)]
);

$at_limit = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => $prefix . str_repeat('b', 5000 - strlen($prefix))]
);

check('6: a 5001-character body is rejected (422)', $too_long['status'] === 422 && isset($too_long['errors']['body']));
check('6: a body of exactly 5000 characters is accepted (201)', $at_limit['status'] === 201);

/*
 * Invalid UTF-8 cannot travel through a JSON request body, so the byte-level
 * guard is asserted directly against the validator the request flow uses.
 */

$invalid_utf8 = message_validator_body_value("\xFF\xFE");

check(
    '6: invalid UTF-8 is rejected by the body validator',
    $invalid_utf8['valid'] === false && isset($invalid_utf8['errors']['body'])
);

$multibyte_at_limit = message_validator_body_value(str_repeat('é', 5000));

check(
    '6: the 5000 limit counts characters, not bytes',
    $multibyte_at_limit['valid'] === true && mb_strlen($multibyte_at_limit['value'], 'UTF-8') === 5000
);

/* 7. A client cannot impersonate another sender. */
$impersonation = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    [
        'body'            => $prefix . 'impersonation',
        'sender_user_id'  => $READ_COMPANY_USER_ID,
    ]
);

check('7: impersonating another sender is rejected (422)', $impersonation['status'] === 422);
check(
    '7: no message was stored for the impersonation attempt',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE body = ?',
        [$prefix . 'impersonation']
    ) === 0
);

/* 8. Mismatched body conversation id cannot redirect the message. */
$mismatch = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    [
        'conversation_id' => 226,
        'body'            => $prefix . 'mismatch',
    ]
);

check('8: a mismatched body conversation_id is rejected (422)', $mismatch['status'] === 422 && isset($mismatch['errors']['conversation_id']));
check(
    '8: the mismatched message was not stored anywhere',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE body = ?',
        [$prefix . 'mismatch']
    ) === 0
);

/* Unsupported fields are rejected instead of silently ignored. */
$unsupported = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $READ_STUDENT_USER_ID,
    [
        'body'         => $prefix . 'unsupported',
        'message_type' => 'text',
    ]
);

check('unsupported message_type is rejected (422)', $unsupported['status'] === 422 && isset($unsupported['errors']['message_type']));

echo "\n== Query parameters ==\n";

/* Create the fixture conversation through the API, then add query fixtures. */
$creation = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

check('query setup: conversation created for application 2009 (201)', $creation['status'] === 201 && is_array($creation['data']));

$fixture_conversation_id = is_array($creation['data'])
    ? (int) ($creation['data']['id'] ?? 0)
    : 0;

check('query setup: conversation id returned', $fixture_conversation_id > 0);

if ($fixture_conversation_id <= 0) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MESSAGING_STATE']['conversation_ids'][] = $fixture_conversation_id;

$fixture_ids = messaging_insert_fixture_messages(
    $fixture_conversation_id,
    $FIXTURE_STUDENT_USER_ID,
    $FIXTURE_COMPANY_USER_ID,
    $prefix,
    120
);

check('query setup: 120 fixture messages inserted', count($fixture_ids) === 120);

$query_path = "/api/v1/conversations/{$fixture_conversation_id}/messages";

/* 9. Default limit is 50. */
$default_list = messaging_student_request($query_path, 'GET', $FIXTURE_STUDENT_USER_ID);

check('9: default limit returns 50 messages', $default_list['status'] === 200 && is_array($default_list['data']) && count($default_list['data']) === 50);

/* 10. Maximum limit is 100. */
$max_limit = messaging_student_request($query_path . '?limit=100', 'GET', $FIXTURE_STUDENT_USER_ID);

check('10: limit=100 returns 100 messages', $max_limit['status'] === 200 && is_array($max_limit['data']) && count($max_limit['data']) === 100);

/* 11. Invalid limit / offset are rejected with 400. */
$invalid_limit_zero = messaging_student_request($query_path . '?limit=0', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_limit_high = messaging_student_request($query_path . '?limit=101', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_limit_text = messaging_student_request($query_path . '?limit=abc', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_offset     = messaging_student_request($query_path . '?offset=-1', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_offset_txt = messaging_student_request($query_path . '?offset=abc', 'GET', $FIXTURE_STUDENT_USER_ID);

check('11: limit=0 is rejected (400)', $invalid_limit_zero['status'] === 400 && isset($invalid_limit_zero['errors']['limit']));
check('11: limit=101 is rejected (400)', $invalid_limit_high['status'] === 400 && isset($invalid_limit_high['errors']['limit']));
check('11: limit=abc is rejected (400)', $invalid_limit_text['status'] === 400 && isset($invalid_limit_text['errors']['limit']));
check('11: offset=-1 is rejected (400)', $invalid_offset['status'] === 400 && isset($invalid_offset['errors']['offset']));
check('11: offset=abc is rejected (400)', $invalid_offset_txt['status'] === 400 && isset($invalid_offset_txt['errors']['offset']));

/* 12. Supported filters. */
$student_sender = messaging_student_request(
    $query_path . '?limit=100&sender_id=' . $FIXTURE_STUDENT_USER_ID,
    'GET',
    $FIXTURE_STUDENT_USER_ID
);

$all_student_sender = $student_sender['status'] === 200 && is_array($student_sender['data']);
$sender_ok = true;

if ($all_student_sender) {
    foreach ($student_sender['data'] as $row) {
        if ((int) ($row['sender_user_id'] ?? 0) !== $FIXTURE_STUDENT_USER_ID) {
            $sender_ok = false;
            break;
        }
    }
}

check('12: sender_id filter returns only that sender', $all_student_sender && $sender_ok && count($student_sender['data']) === 60);

$middle_index = 60;
$middle_id    = $fixture_ids[$middle_index] ?? 0;

$before = messaging_student_request($query_path . '?limit=100&before_id=' . $middle_id, 'GET', $FIXTURE_STUDENT_USER_ID);
$after  = messaging_student_request($query_path . '?limit=100&after_id=' . $middle_id, 'GET', $FIXTURE_STUDENT_USER_ID);

$before_ok = $before['status'] === 200 && is_array($before['data']) && count($before['data']) === 60;
$after_ok  = $after['status'] === 200 && is_array($after['data']) && count($after['data']) === 59;

if ($before_ok) {
    foreach ($before['data'] as $row) {
        if ((int) $row['id'] >= $middle_id) {
            $before_ok = false;
            break;
        }
    }
}

if ($after_ok) {
    foreach ($after['data'] as $row) {
        if ((int) $row['id'] <= $middle_id) {
            $after_ok = false;
            break;
        }
    }
}

check('12: before_id returns the older messages only', $before_ok);
check('12: after_id returns the newer messages only', $after_ok);

$date_range = messaging_student_request(
    $query_path . '?limit=100&from_date=2026-01-01%2010:10:00&to_date=2026-01-01%2010:19:59',
    'GET',
    $FIXTURE_STUDENT_USER_ID
);

check('12: from_date/to_date return the requested range', $date_range['status'] === 200 && is_array($date_range['data']) && count($date_range['data']) === 10);

$date_only = messaging_student_request(
    $query_path . '?limit=100&from_date=2026-01-01%2011:50:00&to_date=2026-01-01',
    'GET',
    $FIXTURE_STUDENT_USER_ID
);

check('12: a date-only to_date includes the whole day', $date_only['status'] === 200 && is_array($date_only['data']) && count($date_only['data']) === 10);

$descending = messaging_student_request($query_path . '?limit=100&order=desc', 'GET', $FIXTURE_STUDENT_USER_ID);

check(
    '12: order=desc returns newest first',
    $descending['status'] === 200
        && is_array($descending['data'])
        && (int) $descending['data'][0]['id'] === end($fixture_ids)
);

$invalid_order = messaging_student_request($query_path . '?order=sideways', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_before = messaging_student_request($query_path . '?before_id=abc', 'GET', $FIXTURE_STUDENT_USER_ID);
$invalid_date   = messaging_student_request($query_path . '?from_date=not-a-date', 'GET', $FIXTURE_STUDENT_USER_ID);

check('12: an invalid order is rejected (400)', $invalid_order['status'] === 400 && isset($invalid_order['errors']['order']));
check('12: an invalid before_id is rejected (400)', $invalid_before['status'] === 400 && isset($invalid_before['errors']['before_id']));
check('12: an invalid date format is rejected (400)', $invalid_date['status'] === 400 && isset($invalid_date['errors']['from_date']));

echo "\n== Authorization ==\n";

/* 13. Unauthorized users cannot retrieve messages. */
$guest_messages = messaging_request($query_path, 'GET', 'guest');
$guest_send     = messaging_request(
    $query_path,
    'POST',
    'guest',
    '',
    ['body' => $prefix . 'guest']
);

check('13: an unauthenticated GET is rejected (401)', $guest_messages['status'] === 401);
check('13: an unauthenticated send is rejected (401)', $guest_send['status'] === 401);

/* 14. Student cannot access another student's conversation. */
$other_student_read = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}",
    'GET',
    $OTHER_STUDENT_USER_ID
);

$other_student_messages = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'GET',
    $OTHER_STUDENT_USER_ID
);

check('14: another student cannot read the conversation (403)', $other_student_read['status'] === 403);
check('14: another student cannot read its messages (403)', $other_student_messages['status'] === 403);

/* 15. Company cannot access another company's conversation. */
$other_company_read = messaging_company_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}",
    'GET',
    $OTHER_COMPANY_USER_ID
);

$other_company_messages = messaging_company_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'GET',
    $OTHER_COMPANY_USER_ID
);

check('15: another company cannot read the conversation (403)', $other_company_read['status'] === 403);
check('15: another company cannot read its messages (403)', $other_company_messages['status'] === 403);

/* 16. Non-participants cannot read or send. */
$other_student_send = messaging_student_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $OTHER_STUDENT_USER_ID,
    ['body' => $prefix . 'other-student-send']
);

$other_company_send = messaging_company_request(
    "/api/v1/conversations/{$READ_CONVERSATION_ID}/messages",
    'POST',
    $OTHER_COMPANY_USER_ID,
    ['body' => $prefix . 'other-company-send']
);

check('16: a non-participant student cannot send (403)', $other_student_send['status'] === 403);
check('16: a non-participant company cannot send (403)', $other_company_send['status'] === 403);
check(
    '16: the refused sends wrote nothing',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE body IN (?, ?)',
        [$prefix . 'other-student-send', $prefix . 'other-company-send']
    ) === 0
);

$missing_conversation = messaging_student_request(
    '/api/v1/conversations/999999999/messages',
    'GET',
    $READ_STUDENT_USER_ID
);

check('16: a missing conversation is reported as 404', $missing_conversation['status'] === 404);

echo "\n== Conversation creation ==\n";

/* Start from a clean application again (removes the query fixture conversation). */
messaging_delete_conversations_for_application($FIXTURE_APPLICATION_ID);

$missing_application = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    []
);

$invalid_application = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['application_id' => 'abc']
);

$unknown_application = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['application_id' => 999999999]
);

check('26: a missing application_id is rejected (422)', $missing_application['status'] === 422 && isset($missing_application['errors']['application_id']));
check('26: a non-numeric application_id is rejected (422)', $invalid_application['status'] === 422 && isset($invalid_application['errors']['application_id']));
check('26: an unknown application is reported as 404', $unknown_application['status'] === 404);

$non_participant_create = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $OTHER_STUDENT_USER_ID,
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

check('27: a non-participant cannot create the conversation (403)', $non_participant_create['status'] === 403);

$unsupported_title = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    [
        'application_id' => $FIXTURE_APPLICATION_ID,
        'title'          => 'Not persisted',
    ]
);

check('conversation creation rejects an unsupported title (422)', $unsupported_title['status'] === 422 && isset($unsupported_title['errors']['title']));

$created = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

$created_id = is_array($created['data']) ? (int) ($created['data']['id'] ?? 0) : 0;

check('conversation creation succeeds (201)', $created['status'] === 201 && $created_id > 0);

$GLOBALS['MESSAGING_STATE']['conversation_ids'][] = $created_id;

/* 28. Existing conversation is returned safely. */
$duplicate = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

$duplicate_id = is_array($duplicate['data']) ? (int) ($duplicate['data']['id'] ?? 0) : 0;

check('28: an existing conversation is returned (200)', $duplicate['status'] === 200 && $duplicate['success'] === true);
check('28: the existing conversation keeps its id', $duplicate_id === $created_id);

$company_create = messaging_company_request(
    '/api/v1/conversations',
    'POST',
    $FIXTURE_COMPANY_USER_ID,
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

check(
    '28: the company participant receives the same existing conversation',
    in_array($company_create['status'], [200, 201], true)
        && (int) ($company_create['data']['id'] ?? 0) === $created_id
);

/* 29. Concurrent duplicate creation. */
messaging_delete_conversations_for_application($FIXTURE_APPLICATION_ID);

$concurrent_a = messaging_start(
    '/api/v1/conversations',
    'POST',
    'bearer',
    messaging_token($FIXTURE_STUDENT_USER_ID, 'student'),
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

$concurrent_b = messaging_start(
    '/api/v1/conversations',
    'POST',
    'bearer',
    messaging_token($FIXTURE_STUDENT_USER_ID, 'student'),
    ['application_id' => $FIXTURE_APPLICATION_ID]
);

$concurrent_a = messaging_finish($concurrent_a);
$concurrent_b = messaging_finish($concurrent_b);

$concurrent_ids = array_filter([
    (int) ($concurrent_a['data']['id'] ?? 0),
    (int) ($concurrent_b['data']['id'] ?? 0),
]);

$stored = messaging_conversation_ids_for_application($FIXTURE_APPLICATION_ID);

check(
    '29: both concurrent requests succeed',
    in_array($concurrent_a['status'], [200, 201], true)
        && in_array($concurrent_b['status'], [200, 201], true)
);
check(
    '29: both concurrent requests return the same conversation',
    count($concurrent_ids) === 2
        && count(array_unique($concurrent_ids)) === 1
        && (int) reset($concurrent_ids) === (int) ($stored[0] ?? 0)
);
check('29: exactly one conversation exists for the application', count($stored) === 1);

$fixture_conversation_id = (int) ($stored[0] ?? 0);

check('fixture conversation ready for withdrawal/transaction cases', $fixture_conversation_id > 0);

if ($fixture_conversation_id <= 0) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MESSAGING_STATE']['conversation_ids'][] = $fixture_conversation_id;

echo "\n== Withdrawal policy ==\n";

$fixture_path = "/api/v1/conversations/{$fixture_conversation_id}/messages";

$before_withdrawal = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $prefix . 'before-withdrawal']
);

check('withdrawal setup: sending works while accepted (201)', $before_withdrawal['status'] === 201);

$count_before_withdrawal = messaging_message_count($fixture_conversation_id);

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['withdrawn', $FIXTURE_APPLICATION_ID]
);

$student_read_conversation = messaging_student_request(
    "/api/v1/conversations/{$fixture_conversation_id}",
    'GET',
    $FIXTURE_STUDENT_USER_ID
);

$company_read_conversation = messaging_company_request(
    "/api/v1/conversations/{$fixture_conversation_id}",
    'GET',
    $FIXTURE_COMPANY_USER_ID
);

check('17: the student can still read the conversation after withdrawal', $student_read_conversation['status'] === 200 && (int) ($student_read_conversation['data']['id'] ?? 0) === $fixture_conversation_id);
check('18: the company can still read the conversation after withdrawal', $company_read_conversation['status'] === 200 && (int) ($company_read_conversation['data']['id'] ?? 0) === $fixture_conversation_id);

$student_history = messaging_student_request(
    $fixture_path . '?limit=100',
    'GET',
    $FIXTURE_STUDENT_USER_ID
);

$company_history = messaging_company_request(
    $fixture_path . '?limit=1&order=desc',
    'GET',
    $FIXTURE_COMPANY_USER_ID
);

check('17: the student can read the message history after withdrawal', $student_history['status'] === 200 && is_array($student_history['data']) && count($student_history['data']) === $count_before_withdrawal);
check('18: the company can read the message history after withdrawal', $company_history['status'] === 200 && is_array($company_history['data']) && count($company_history['data']) === 1);
check(
    'read history includes the message sent before withdrawal',
    is_array($company_history['data'])
        && ($company_history['data'][0]['body'] ?? null) === $prefix . 'before-withdrawal'
);

$student_send_withdrawn = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $prefix . 'student-after-withdrawal']
);

$company_send_withdrawn = messaging_company_request(
    $fixture_path,
    'POST',
    $FIXTURE_COMPANY_USER_ID,
    ['body' => $prefix . 'company-after-withdrawal']
);

check('19: the student cannot send after withdrawal (403)', $student_send_withdrawn['status'] === 403);
check('20: the company cannot send after withdrawal (403)', $company_send_withdrawn['status'] === 403);

$student_reason = strtolower((string) ($student_send_withdrawn['message'] ?? ''));
$company_reason = strtolower((string) ($company_send_withdrawn['message'] ?? ''));

check('19/20: the refusal explains the withdrawal', str_contains($student_reason, 'withdrawn') && str_contains($company_reason, 'withdrawn'));
check('19/20: the refused sends wrote nothing', messaging_message_count($fixture_conversation_id) === $count_before_withdrawal);

check(
    '21: withdrawal did not delete the conversation or its messages',
    db_fetch_column('SELECT COUNT(*) FROM conversations WHERE id = ?', [$fixture_conversation_id]) == 1
        && messaging_message_count($fixture_conversation_id) === $count_before_withdrawal
);

/* 22. Other statuses keep their previous behavior (only withdrawal blocks sends). */
db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['submitted', $FIXTURE_APPLICATION_ID]
);

$submitted_read = messaging_student_request($fixture_path . '?limit=10', 'GET', $FIXTURE_STUDENT_USER_ID);
$submitted_send = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $prefix . 'submitted-send']
);

check('22: reading still works for a submitted application', $submitted_read['status'] === 200);
check('22: a non-withdrawn status keeps its previous send behavior (201)', $submitted_send['status'] === 201);

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['accepted', $FIXTURE_APPLICATION_ID]
);

echo "\n== Transactions ==\n";

/* 23. A successful send commits both the message and the conversation timestamp. */
db_execute(
    'UPDATE conversations SET updated_at = ? WHERE id = ?',
    ['2000-01-01 00:00:00', $fixture_conversation_id]
);

$committed = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $prefix . 'transaction-commit']
);

$committed_updated_at = messaging_conversation_updated_at($fixture_conversation_id);

check('23: a successful send returns 201', $committed['status'] === 201);
check(
    '23: the message was inserted',
    (int) db_fetch_column('SELECT COUNT(*) FROM messages WHERE body = ?', [$prefix . 'transaction-commit']) === 1
);
check(
    '23: the conversation timestamp was updated in the same commit',
    $committed_updated_at !== null && $committed_updated_at !== '2000-01-01 00:00:00'
);

/* 24. Failure during the timestamp update rolls back the insert. */
$trigger_conversation = 'msgph1_' . $run_id . '_conv';

db_execute(
    'UPDATE conversations SET updated_at = ? WHERE id = ?',
    ['2000-01-01 00:00:00', $fixture_conversation_id]
);

$GLOBALS['MESSAGING_STATE']['trigger_names'][] = $trigger_conversation;

$trigger_ready = messaging_create_trigger_update_block(
    $fixture_conversation_id,
    $trigger_conversation,
    'blocked-conversation-update'
);

check('24: the conversation-update trigger was created', $trigger_ready);

$count_before_update_failure = messaging_message_count($fixture_conversation_id);

$update_failure = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $prefix . 'transaction-update-failure']
);

messaging_drop_trigger($trigger_conversation);

check('24: a timestamp-update failure returns a safe 500', $update_failure['status'] === 500 && $update_failure['success'] === false);
check(
    '24: the response does not leak the database error',
    !str_contains($update_failure['raw'], 'blocked-conversation-update')
        && !str_contains($update_failure['raw'], 'SQLSTATE')
        && !str_contains(strtoupper($update_failure['raw']), 'UPDATE CONVERSATIONS')
);
check(
    '24: the message insert was rolled back',
    messaging_message_count($fixture_conversation_id) === $count_before_update_failure
        && (int) db_fetch_column('SELECT COUNT(*) FROM messages WHERE body = ?', [$prefix . 'transaction-update-failure']) === 0
);
check(
    '24: the conversation timestamp was not advanced',
    messaging_conversation_updated_at($fixture_conversation_id) === '2000-01-01 00:00:00'
);

/* 25. Failure during the message insert does not update the timestamp. */
$trigger_message = 'msgph1_' . $run_id . '_msg';
$GLOBALS['MESSAGING_STATE']['trigger_names'][] = $trigger_message;

$marker = $prefix . 'transaction-insert-failure';

$trigger_ready = messaging_create_trigger_insert_block(
    $marker,
    $trigger_message,
    'blocked-message-insert'
);

check('25: the message-insert trigger was created', $trigger_ready);

db_execute(
    'UPDATE conversations SET updated_at = ? WHERE id = ?',
    ['2000-01-01 00:00:00', $fixture_conversation_id]
);

$count_before_insert_failure = messaging_message_count($fixture_conversation_id);

$insert_failure = messaging_student_request(
    $fixture_path,
    'POST',
    $FIXTURE_STUDENT_USER_ID,
    ['body' => $marker]
);

messaging_drop_trigger($trigger_message);

check('25: an insert failure returns a safe 500', $insert_failure['status'] === 500 && $insert_failure['success'] === false);
check(
    '25: the response does not leak the database error',
    !str_contains($insert_failure['raw'], 'blocked-message-insert')
        && !str_contains($insert_failure['raw'], 'SQLSTATE')
        && !str_contains(strtoupper($insert_failure['raw']), 'INSERT INTO MESSAGES')
);
check('25: no message was inserted', messaging_message_count($fixture_conversation_id) === $count_before_insert_failure);
check(
    '25: the conversation timestamp stayed unchanged',
    messaging_conversation_updated_at($fixture_conversation_id) === '2000-01-01 00:00:00'
);

echo "\n== Cleanup ==\n";

messaging_cleanup();

check(
    'Cleanup: no tagged fixture messages remain',
    (int) db_fetch_column('SELECT COUNT(*) FROM messages WHERE body LIKE ?', [$prefix . '%']) === 0
);
check(
    'Cleanup: no fixture conversation remains for application 2009',
    count(messaging_conversation_ids_for_application($FIXTURE_APPLICATION_ID)) === 0
);
check(
    'Cleanup: application 2009 status was restored',
    (string) db_fetch_column('SELECT status FROM training_applications WHERE id = ?', [$FIXTURE_APPLICATION_ID])
        === (string) $GLOBALS['MESSAGING_STATE']['application_status']
);
check(
    'Cleanup: the seeded read conversation timestamp was restored',
    messaging_conversation_updated_at($READ_CONVERSATION_ID)
        === $GLOBALS['MESSAGING_STATE']['read_updated_at']
);
check(
    'Cleanup: no messaging test trigger remains',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_NAME LIKE ?',
        ['msgph1_' . $run_id . '_%']
    ) === 0
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
