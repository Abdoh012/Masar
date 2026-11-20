<?php

/**
 * MASAR - Messages Pagination Regression Suite
 *
 * Covers cursor pagination for GET /conversations/{id}/messages:
 *   - default page of 12 latest messages, chronological order
 *   - limit 1..20 validation and rejection of invalid values
 *   - before_id / after_id exclusivity, direction and metadata
 *   - consecutive pages without duplicates or gaps
 *   - empty conversations, invalid/nonexistent cursors
 *   - conversation scoping (no cross-conversation leakage)
 *   - index coverage and query plans for message/unread retrieval
 *
 * Run with: php tests/messaging_pagination_regression.php
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
require_once BASE . 'app/services/jwt_service.php';

$failures = 0;
$run_id   = bin2hex(random_bytes(4));
$prefix   = 'MSGPAG-' . $run_id . '-';

$APPLICATION_ID      = 2009;
$STUDENT_USER_ID     = 100588;
$COMPANY_USER_ID     = 100595;
$MESSAGE_COUNT       = 25;

$GLOBALS['MSGPAG_STATE'] = [
    'prefix'             => $prefix,
    'application_id'     => $APPLICATION_ID,
    'application_status' => null,
    'conversation_id'    => 0,
    'cleanup_done'       => false,
];

function pag_sorted(array $ids): array
{
    sort($ids, SORT_NUMERIC);

    return array_values($ids);
}

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function pag_token(int $user_id, string $role): string
{
    return jwt_issue_access_token([
        'id'   => $user_id,
        'role' => $role,
    ]);
}

function pag_request(
    string $path,
    int $user_id,
    string $role
): array {

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'tests/messaging_phase1_case.php')
        . ' '
        . escapeshellarg($path)
        . ' GET bearer '
        . escapeshellarg(pag_token($user_id, $role));

    $environment = [
        'MASAR_CASE_PATH' => $path,
        'MASAR_CASE_BODY' => '',
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

    $json = json_decode($raw, true);

    return [
        'status'     => $status,
        'json'       => is_array($json) ? $json : [],
        'data'       => is_array($json['data'] ?? null) ? $json['data'] : [],
        'pagination' => is_array($json['pagination'] ?? null) ? $json['pagination'] : [],
        'errors'     => is_array($json['errors'] ?? null) ? $json['errors'] : [],
    ];
}

function pag_cleanup(): void
{
    $state = &$GLOBALS['MSGPAG_STATE'];

    if (!empty($state['cleanup_done'])) {
        return;
    }

    $state['cleanup_done'] = true;

    try {

        db_execute(
            'DELETE FROM message_events WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );

        db_execute(
            'DELETE FROM messages WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );

        db_execute(
            'DELETE FROM conversations WHERE id = ?',
            [(int) $state['conversation_id']]
        );

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
        // Best-effort cleanup.
    }
}

register_shutdown_function('pag_cleanup');

echo "== Setup ==\n";

$application =
    db_fetch_one(
        'SELECT id, status FROM training_applications WHERE id = ?',
        [$APPLICATION_ID]
    );

check('seeded application 2009 resolved', is_array($application));

if (!is_array($application)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MSGPAG_STATE']['application_status'] = (string) $application['status'];

db_execute(
    'DELETE FROM conversations WHERE application_id = ?',
    [$APPLICATION_ID]
);

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    ['accepted', $APPLICATION_ID]
);

/*
 * Creation is a POST; the helper above is GET-only, so use a separate runner.
 */

function pag_request_method(
    string $path,
    string $method,
    int $user_id,
    string $role,
    ?array $body = null
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
        . escapeshellarg(pag_token($user_id, $role));

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

$creation =
    pag_request_method(
        '/api/v1/conversations',
        'POST',
        $STUDENT_USER_ID,
        'student',
        ['application_id' => $APPLICATION_ID]
    );

$conversation_id =
    (int) ($creation['json']['data']['id'] ?? 0);

check(
    'fixture conversation created',
    $creation['status'] === 201 && $conversation_id > 0
);

if ($conversation_id <= 0) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MSGPAG_STATE']['conversation_id'] = $conversation_id;

$path =
    "/api/v1/conversations/{$conversation_id}/messages";


/*
|--------------------------------------------------------------------------
| Empty conversation
|--------------------------------------------------------------------------
*/

echo "\n== Empty conversation ==\n";

$empty =
    pag_request($path, $STUDENT_USER_ID, 'student');

check(
    'an empty conversation returns an empty page',
    $empty['status'] === 200
        && $empty['data'] === []
        && $empty['pagination']['has_more'] === false
);


/*
|--------------------------------------------------------------------------
| Fixture messages
|--------------------------------------------------------------------------
*/

echo "\n== Fixture messages ==\n";

$base =
    new DateTimeImmutable(
        '2026-02-01 10:00:00',
        new DateTimeZone('Africa/Cairo')
    );

$values = [];
$params = [];

for ($i = 0; $i < $MESSAGE_COUNT; $i++) {

    $values[] = '(?, ?, ?, 0, ?)';

    $params[] = $conversation_id;
    $params[] = ($i % 2 === 0) ? $STUDENT_USER_ID : $COMPANY_USER_ID;
    $params[] = $prefix . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
    $params[] = $base
        ->modify('+' . $i . ' minutes')
        ->format('Y-m-d H:i:s');
}

db_execute(
    'INSERT INTO messages (conversation_id, sender_user_id, body, is_read, created_at) VALUES '
    . implode(', ', $values),
    $params
);

$fixture_ids =
    array_map(
        'intval',
        array_column(
            db_fetch_all(
                'SELECT id FROM messages WHERE conversation_id = ? AND body LIKE ? ORDER BY id ASC',
                [$conversation_id, $prefix . '%']
            ),
            'id'
        )
    );

check(
    '25 fixture messages inserted',
    count($fixture_ids) === $MESSAGE_COUNT
);


/*
|--------------------------------------------------------------------------
| Default page
|--------------------------------------------------------------------------
*/

echo "\n== Default page ==\n";

$default =
    pag_request($path, $STUDENT_USER_ID, 'student');

$default_ids =
    array_map(
        static fn (array $row): int => (int) $row['id'],
        $default['data']
    );

check(
    'default page contains exactly 12 messages',
    $default['status'] === 200 && count($default_ids) === 12
);

check(
    'the default page is the latest 12 messages',
    $default_ids === array_slice($fixture_ids, -12)
);

check(
    'the default page is chronological (oldest to newest)',
    $default_ids === pag_sorted($default_ids)
);

check(
    'default metadata reports limit=12 and a further older page',
    ($default['pagination']['limit'] ?? 0) === 12
        && ($default['pagination']['has_more'] ?? null) === true
);

check(
    'default metadata exposes usable cursors',
    (int) ($default['pagination']['next_before_id'] ?? 0) === (int) ($default_ids[0] ?? 0)
        && (int) ($default['pagination']['next_after_id'] ?? 0) === (int) end($default_ids)
);


/*
|--------------------------------------------------------------------------
| Limit validation
|--------------------------------------------------------------------------
*/

echo "\n== Limit validation ==\n";

$limit_20 =
    pag_request($path . '?limit=20', $STUDENT_USER_ID, 'student');

check(
    'limit=20 returns at most 20 messages',
    $limit_20['status'] === 200 && count($limit_20['data']) === 20
);

foreach (
    [
        'limit=21',
        'limit=0',
        'limit=-1',
        'limit=abc',
    ]
    as $invalid
) {

    $response =
        pag_request(
            $path . '?' . $invalid,
            $STUDENT_USER_ID,
            'student'
        );

    check(
        $invalid . ' is rejected with 400',
        $response['status'] === 400 && isset($response['errors']['limit'])
    );
}


/*
|--------------------------------------------------------------------------
| Cursor pagination
|--------------------------------------------------------------------------
*/

echo "\n== Cursor pagination ==\n";

$middle_id =
    (int) $fixture_ids[12];

$before =
    pag_request(
        $path . '?limit=12&before_id=' . $middle_id,
        $STUDENT_USER_ID,
        'student'
    );

$before_ids =
    array_map(
        static fn (array $row): int => (int) $row['id'],
        $before['data']
    );

check(
    'before_id returns only messages older than the cursor',
    $before['status'] === 200
        && $before_ids !== []
        && max($before_ids) < $middle_id
);

check(
    'before_id excludes the cursor message',
    !in_array($middle_id, $before_ids, true)
);

check(
    'before_id results are chronological',
    $before_ids === pag_sorted($before_ids)
);

$after =
    pag_request(
        $path . '?limit=12&after_id=' . $middle_id,
        $STUDENT_USER_ID,
        'student'
    );

$after_ids =
    array_map(
        static fn (array $row): int => (int) $row['id'],
        $after['data']
    );

check(
    'after_id returns only messages newer than the cursor',
    $after['status'] === 200
        && $after_ids !== []
        && min($after_ids) > $middle_id
);

check(
    'after_id excludes the cursor message',
    !in_array($middle_id, $after_ids, true)
);

check(
    'after_id results are chronological',
    $after_ids === pag_sorted($after_ids)
);

$both =
    pag_request(
        $path . '?before_id=' . $middle_id . '&after_id=' . $middle_id,
        $STUDENT_USER_ID,
        'student'
    );

check(
    'before_id and after_id together are rejected',
    $both['status'] === 400
        && isset($both['errors']['before_id_after_id'])
);

$invalid_cursor =
    pag_request(
        $path . '?before_id=abc',
        $STUDENT_USER_ID,
        'student'
    );

check(
    'a non-numeric cursor is rejected',
    $invalid_cursor['status'] === 400
        && isset($invalid_cursor['errors']['before_id'])
);

$nonexistent_cursor =
    pag_request(
        $path . '?before_id=999999999',
        $STUDENT_USER_ID,
        'student'
    );

check(
    'a nonexistent cursor is handled safely',
    $nonexistent_cursor['status'] === 200
        && count($nonexistent_cursor['data']) === 12
);

$zero_cursor =
    pag_request(
        $path . '?before_id=0',
        $STUDENT_USER_ID,
        'student'
    );

check(
    'a zero cursor is rejected',
    $zero_cursor['status'] === 400
        && isset($zero_cursor['errors']['before_id'])
);


/*
|--------------------------------------------------------------------------
| Page walking
|--------------------------------------------------------------------------
*/

echo "\n== Consecutive pages ==\n";

$walked = [];
$cursor = null;

for ($page = 0; $page < 10; $page++) {

    $suffix =
        $cursor === null
            ? ''
            : '&before_id=' . $cursor;

    $response =
        pag_request(
            $path . '?limit=12' . $suffix,
            $STUDENT_USER_ID,
            'student'
        );

    $ids =
        array_map(
            static fn (array $row): int => (int) $row['id'],
            $response['data']
        );

    if ($ids === []) {
        break;
    }

    $walked =
        array_merge(
            $walked,
            $ids
        );

    $cursor =
        $response['pagination']['next_before_id']
        ?? null;

    if (($response['pagination']['has_more'] ?? false) !== true) {
        break;
    }
}

check(
    'walking pages returns every message exactly once',
    count($walked) === count($fixture_ids)
        && count(array_unique($walked)) === count($walked)
        && pag_sorted($walked) === $fixture_ids
);


/*
|--------------------------------------------------------------------------
| Conversation scoping
|--------------------------------------------------------------------------
*/

echo "\n== Conversation scoping ==\n";

$scoped =
    pag_request(
        '/api/v1/conversations/225/messages?limit=20',
        100532,
        'student'
    );

$foreign = false;

foreach ($scoped['data'] as $row) {

    if ((int) ($row['conversation_id'] ?? 0) !== 225) {
        $foreign = true;
        break;
    }

    if (str_starts_with((string) ($row['body'] ?? ''), $prefix)) {
        $foreign = true;
        break;
    }
}

check(
    'another conversation never returns this fixture’s messages',
    $scoped['status'] === 200 && !$foreign
);

$not_participant =
    pag_request(
        $path,
        100530,
        'student'
    );

check(
    'a non-participant cannot read the page (403)',
    $not_participant['status'] === 403
);


/*
|--------------------------------------------------------------------------
| Database performance
|--------------------------------------------------------------------------
*/

echo "\n== Database performance ==\n";

$indexes = [];

foreach (
    db_fetch_all(
        "SELECT COLUMN_NAME FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'messages'
           AND INDEX_NAME = 'idx_messages_conversation_id'
         ORDER BY SEQ_IN_INDEX"
    ) as $row
) {
    $indexes[] = $row['COLUMN_NAME'];
}

check(
    'the (conversation_id, id) index exists',
    $indexes === ['conversation_id', 'id']
);

$unread_index = [];

foreach (
    db_fetch_all(
        "SELECT COLUMN_NAME FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'messages'
           AND INDEX_NAME = 'idx_messages_unread'
         ORDER BY SEQ_IN_INDEX"
    ) as $row
) {
    $unread_index[] = $row['COLUMN_NAME'];
}

check(
    'the unread index exists',
    $unread_index === ['conversation_id', 'read_at', 'sender_user_id']
);

$explain =
    db_fetch_all(
        'EXPLAIN SELECT id, conversation_id, sender_user_id, body, is_read, read_at, created_at
         FROM messages
         WHERE conversation_id = ?
           AND id < ?
         ORDER BY id DESC
         LIMIT 13',
        [$conversation_id, $middle_id]
    );

$key =
    (string) ($explain[0]['key'] ?? '');

check(
    'the cursor query uses an indexed access path',
    in_array(
        $key,
        [
            'idx_messages_conversation_id',
            'idx_messages_conversation_created',
            'PRIMARY',
        ],
        true
    )
);

echo "\n== Cleanup ==\n";

pag_cleanup();

check(
    'Cleanup: no tagged messages remain',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM messages WHERE body LIKE ?',
        [$prefix . '%']
    ) === 0
);

check(
    'Cleanup: application 2009 status restored',
    (string) db_fetch_column(
        'SELECT status FROM training_applications WHERE id = ?',
        [$APPLICATION_ID]
    ) === (string) $GLOBALS['MSGPAG_STATE']['application_status']
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
