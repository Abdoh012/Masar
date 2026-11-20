<?php

/**
 * MASAR - Conversations List Regression Suite
 *
 * Covers the Conversations List API improvements:
 *   - latest message preview (last_message: id, body, sender_user_id, created_at)
 *   - application_status from the linked application
 *   - training object (title, starts_at, ends_at) from the linked listing
 *   - query-parameter pagination (?limit=&offset=) with metadata
 *   - per-user visibility and scoped totals
 *   - message-retrieval regressions (after_id / before_id&order=desc)
 *
 * Fixtures:
 *   - seeded conversation 225 (student 100532) for read/authorization cases
 *   - a tagged fixture training listing + application + conversation for the
 *     last_message, application_status and null-date cases
 *   - two tie messages with the same created_at in the fixture conversation
 *
 * Every row this suite writes carries the run prefix and is removed by
 * messaging_conversations_list_cleanup(), which is also registered as a
 * shutdown handler.
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

$failures = 0;
$run_id   = bin2hex(random_bytes(4));
$prefix   = 'MSGCL-' . $run_id . '-';

$GLOBALS['MSGCL_STATE'] = [
    'prefix'          => $prefix,
    'conversation_id' => 0,
    'application_id'  => 0,
    'listing_id'      => 0,
    'cleanup_done'    => false,
];

$READ_STUDENT_USER_ID  = 100532;
$OTHER_STUDENT_USER_ID = 100530;
$READ_COMPANY_USER_ID  = 100568;
$OTHER_COMPANY_USER_ID = 100569;
$READ_CONVERSATION_ID  = 225;

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
    string $token,
    ?array $body = null
): array {

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg(__DIR__ . '/messaging_conversations_list_case.php')
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg('bearer')
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
    string $token = '',
    ?array $body = null
): array {
    return messaging_finish(messaging_start($path, $method, $token, $body));
}

function messaging_student_request(string $path, string $method, int $user_id, ?array $body = null): array
{
    return messaging_request($path, $method, messaging_token($user_id, 'student'), $body);
}

function messaging_company_request(string $path, string $method, int $user_id, ?array $body = null): array
{
    return messaging_request($path, $method, messaging_token($user_id, 'company'), $body);
}

function messaging_conversations_sorted(array $ids): array
{
    $ids = array_map('intval', $ids);
    sort($ids);

    return $ids;
}

function messaging_conversations_find(?array $data, int $conversation_id): ?array
{
    foreach (($data['items'] ?? []) as $item) {
        if ((int) ($item['id'] ?? 0) === $conversation_id) {
            return $item;
        }
    }

    return null;
}

function messaging_conversations_ids(?array $data): array
{
    $items = $data['items'] ?? [];

    return is_array($items)
        ? array_map('intval', array_column($items, 'id'))
        : [];
}

/**
 * The same visibility the list query uses, in the list query's order.
 */
function messaging_visibility_rows(int $user_id): array
{
    return db_fetch_all(
        '
        SELECT c.id
        FROM conversations c
        WHERE (
            c.student_id IN (
                SELECT s2.id
                FROM students s2
                WHERE s2.user_id = ?
            )
            OR
            c.company_id IN (
                SELECT co2.id
                FROM companies co2
                WHERE co2.user_id = ?
            )
        )
        ORDER BY COALESCE(c.updated_at, c.created_at) DESC, c.id DESC
        ',
        [$user_id, $user_id]
    );
}

function messaging_conversations_list_cleanup(): void
{
    $state = &$GLOBALS['MSGCL_STATE'];

    if (!empty($state['cleanup_done'])) {
        return;
    }

    $state['cleanup_done'] = true;

    try {
        db_execute(
            'DELETE FROM messages WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );

        db_execute(
            'DELETE FROM message_events WHERE body LIKE ?',
            [$state['prefix'] . '%']
        );
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }

    try {
        db_execute(
            'DELETE FROM conversations WHERE id = ?',
            [$state['conversation_id']]
        );
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }

    try {
        db_execute(
            'DELETE FROM training_applications WHERE id = ?',
            [$state['application_id']]
        );
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }

    try {
        db_execute(
            'DELETE FROM training_listings WHERE id = ?',
            [$state['listing_id']]
        );
    } catch (Throwable $exception) {
        // Best-effort cleanup.
    }
}

register_shutdown_function('messaging_conversations_list_cleanup');

echo "== Setup: users and expected visibility ==\n";

$student_row = db_fetch_one(
    'SELECT id FROM students WHERE user_id = ?',
    [$READ_STUDENT_USER_ID]
);
$other_student_row = db_fetch_one(
    'SELECT id FROM students WHERE user_id = ?',
    [$OTHER_STUDENT_USER_ID]
);
$company_row = db_fetch_one(
    'SELECT id FROM companies WHERE user_id = ?',
    [$READ_COMPANY_USER_ID]
);
$other_company_row = db_fetch_one(
    'SELECT id FROM companies WHERE user_id = ?',
    [$OTHER_COMPANY_USER_ID]
);

check('setup: student user 100532 maps to a students row', is_array($student_row));
check('setup: other student user 100530 maps to a students row', is_array($other_student_row));
check('setup: company user 100568 maps to a companies row', is_array($company_row));
check('setup: other company user 100569 maps to a companies row', is_array($other_company_row));

if (
    !is_array($student_row)
    ||
    !is_array($other_student_row)
    ||
    !is_array($company_row)
    ||
    !is_array($other_company_row)
) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$READ_STUDENT_DB_ID  = (int) $student_row['id'];
$OTHER_STUDENT_DB_ID = (int) $other_student_row['id'];
$READ_COMPANY_DB_ID  = (int) $company_row['id'];

/*
 * Fixture: a training listing with null dates, an accepted application on
 * it, and the conversation the API creates for that application.
 */

db_execute(
    '
    INSERT INTO training_listings
    (company_id, specialization_id, title, description, training_type, mode, status, starts_at, ends_at)
    VALUES (?, 199, ?, ?, ?, ?, ?, NULL, NULL)
    ',
    [
        $READ_COMPANY_DB_ID,
        $prefix . 'Fixture Null Dates Training',
        'Fixture training for the Conversations List suite.',
        'hands_on',
        'onsite',
        'published',
    ]
);

$fixture_listing_id = (int) db_fetch_column('SELECT LAST_INSERT_ID() AS id');
check('setup: fixture training listing created', $fixture_listing_id > 0);

db_execute(
    '
    INSERT INTO training_applications
    (training_id, student_id, company_id, status)
    VALUES (?, ?, ?, ?)
    ',
    [
        $fixture_listing_id,
        $READ_STUDENT_DB_ID,
        $READ_COMPANY_DB_ID,
        APPLICATION_STATUS_ACCEPTED,
    ]
);

$fixture_application_id = (int) db_fetch_column('SELECT LAST_INSERT_ID() AS id');
check('setup: fixture training application created', $fixture_application_id > 0);

$creation = messaging_student_request(
    '/api/v1/conversations',
    'POST',
    $READ_STUDENT_USER_ID,
    ['application_id' => $fixture_application_id]
);

$fixture_conversation_id = is_array($creation['data'])
    ? (int) ($creation['data']['id'] ?? 0)
    : 0;

check(
    'setup: fixture conversation created via the API (201)',
    $creation['status'] === 201 && $fixture_conversation_id > 0
);

if ($fixture_conversation_id <= 0) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$GLOBALS['MSGCL_STATE']['conversation_id'] = $fixture_conversation_id;
$GLOBALS['MSGCL_STATE']['application_id']  = $fixture_application_id;
$GLOBALS['MSGCL_STATE']['listing_id']      = $fixture_listing_id;

/*
 * Expected visibility is computed after the fixture was created so it
 * includes it.
 */

$STUDENT_EXPECTED      = array_map('intval', array_column(messaging_visibility_rows($READ_STUDENT_USER_ID), 'id'));
$OTHER_STUDENT_EXPECTED = array_map('intval', array_column(messaging_visibility_rows($OTHER_STUDENT_USER_ID), 'id'));
$COMPANY_EXPECTED      = array_map('intval', array_column(messaging_visibility_rows($READ_COMPANY_USER_ID), 'id'));
$OTHER_COMPANY_EXPECTED = array_map('intval', array_column(messaging_visibility_rows($OTHER_COMPANY_USER_ID), 'id'));

$STUDENT_TOTAL       = count($STUDENT_EXPECTED);
$OTHER_STUDENT_TOTAL = count($OTHER_STUDENT_EXPECTED);
$COMPANY_TOTAL       = count($COMPANY_EXPECTED);
$OTHER_COMPANY_TOTAL = count($OTHER_COMPANY_EXPECTED);

check('setup: the student user can see the fixture conversation', in_array($fixture_conversation_id, $STUDENT_EXPECTED, true));
check('setup: the company user can see the fixture conversation', in_array($fixture_conversation_id, $COMPANY_EXPECTED, true));

echo "\n== Conversation response: last_message ==\n";

$student_list = messaging_student_request('/api/v1/conversations', 'GET', $READ_STUDENT_USER_ID);
check('setup: the authenticated student list is available', $student_list['status'] === 200 && is_array($student_list['data']['items']));

$fixture_item = messaging_conversations_find($student_list['data'], $fixture_conversation_id);
check('2: a conversation without messages returns last_message: null', is_array($fixture_item) && $fixture_item['last_message'] === null);

/*
 * Two messages that share the same created_at: the deterministic "latest"
 * must be the one with the higher message id.
 */

$tie_timestamp = '2026-10-01 12:00:00';

db_execute(
    'INSERT INTO messages (conversation_id, sender_user_id, body, is_read, created_at) VALUES (?, ?, ?, 0, ?)',
    [$fixture_conversation_id, $READ_STUDENT_USER_ID, $prefix . 'tie-a', $tie_timestamp]
);
db_execute(
    'INSERT INTO messages (conversation_id, sender_user_id, body, is_read, created_at) VALUES (?, ?, ?, 0, ?)',
    [$fixture_conversation_id, $READ_COMPANY_USER_ID, $prefix . 'tie-b', $tie_timestamp]
);

$tie_count = (int) db_fetch_column(
    'SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND created_at = ?',
    [$fixture_conversation_id, $tie_timestamp]
);
check('setup: two fixture messages share the same created_at', $tie_count === 2);

$tie_expected = db_fetch_one(
    'SELECT id, body, sender_user_id, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at DESC, id DESC LIMIT 1',
    [$fixture_conversation_id]
);
check('setup: the deterministic latest message is resolvable', is_array($tie_expected));

$student_list = messaging_student_request('/api/v1/conversations', 'GET', $READ_STUDENT_USER_ID);
$fixture_item = messaging_conversations_find($student_list['data'], $fixture_conversation_id);

check(
    '3: the latest message is deterministic when timestamps are equal',
    is_array($fixture_item)
    && is_array($fixture_item['last_message'])
    && (int) $fixture_item['last_message']['id'] === (int) $tie_expected['id']
    && $fixture_item['last_message']['body'] === $tie_expected['body']
    && (int) $fixture_item['last_message']['sender_user_id'] === (int) $tie_expected['sender_user_id']
    && $fixture_item['last_message']['created_at'] === $tie_expected['created_at']
);

$item_225 = messaging_conversations_find($student_list['data'], $READ_CONVERSATION_ID);
$latest_225 = db_fetch_one(
    'SELECT id, body, sender_user_id, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at DESC, id DESC LIMIT 1',
    [$READ_CONVERSATION_ID]
);

check('setup: conversation 225 has messages', is_array($latest_225));

check(
    '1: a conversation with messages returns the correct last_message',
    is_array($item_225)
    && is_array($item_225['last_message'])
    && (int) $item_225['last_message']['id'] === (int) $latest_225['id']
    && $item_225['last_message']['body'] === $latest_225['body']
    && (int) $item_225['last_message']['sender_user_id'] === (int) $latest_225['sender_user_id']
    && $item_225['last_message']['created_at'] === $latest_225['created_at']
);

echo "\n== Conversation response: application_status and training ==\n";

$app_225 = db_fetch_one(
    '
    SELECT
        a.status AS application_status,
        t.title AS training_title,
        t.starts_at AS starts_at,
        t.ends_at AS ends_at
    FROM conversations c
    LEFT JOIN training_applications a ON a.id = c.application_id
    LEFT JOIN training_listings t ON t.id = a.training_id
    WHERE c.id = ?
    ',
    [$READ_CONVERSATION_ID]
);

check('setup: the linked application for conversation 225 resolves', is_array($app_225));

check(
    '4: application_status is read from the linked application',
    is_array($item_225)
    && $item_225['application_status'] === $app_225['application_status']
);

check(
    '5: training title is returned',
    is_array($item_225)
    && is_array($item_225['training'])
    && $item_225['training']['title'] === $app_225['training_title']
);

check(
    '6: training starts_at is returned',
    is_array($item_225)
    && is_array($item_225['training'])
    && $item_225['training']['starts_at'] === $app_225['starts_at']
);

check(
    '7: training ends_at is returned',
    is_array($item_225)
    && is_array($item_225['training'])
    && $item_225['training']['ends_at'] === $app_225['ends_at']
);

/*
 * application_status must be live: a status change on the application is
 * reflected without recreating the conversation.
 */

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    [APPLICATION_STATUS_REJECTED, $fixture_application_id]
);

$student_list = messaging_student_request('/api/v1/conversations', 'GET', $READ_STUDENT_USER_ID);
$fixture_item = messaging_conversations_find($student_list['data'], $fixture_conversation_id);

check(
    '4b: application_status is read live from the linked application',
    is_array($fixture_item) && $fixture_item['application_status'] === APPLICATION_STATUS_REJECTED
);

db_execute(
    'UPDATE training_applications SET status = ? WHERE id = ?',
    [APPLICATION_STATUS_ACCEPTED, $fixture_application_id]
);

check(
    '8: the fixture training returns null dates',
    is_array($fixture_item)
    && is_array($fixture_item['training'])
    && $fixture_item['training']['title'] === $prefix . 'Fixture Null Dates Training'
    && $fixture_item['training']['starts_at'] === null
    && $fixture_item['training']['ends_at'] === null
);

echo "\n== Pagination ==\n";

$page = messaging_company_request('/api/v1/conversations?limit=20&offset=0', 'GET', $READ_COMPANY_USER_ID);
$page_ids = messaging_conversations_sorted(messaging_conversations_ids($page['data'] ?? []));

check('9: limit=20&offset=0 returns 200', $page['status'] === 200);
check('9: items are returned for the page', is_array($page['data']['items'] ?? null) && count($page['data']['items']) === min(20, $COMPANY_TOTAL));
check('9: pagination.limit is 20', (int) ($page['data']['pagination']['limit'] ?? -1) === 20);
check('9: pagination.offset is 0', (int) ($page['data']['pagination']['offset'] ?? -1) === 0);
check('9: pagination.total is the user total', (int) ($page['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL);
check('9: the page matches the expected visibility', $page_ids === messaging_conversations_sorted($COMPANY_EXPECTED));

$page_slice = messaging_company_request('/api/v1/conversations?limit=2&offset=2', 'GET', $READ_COMPANY_USER_ID);
$page_slice_ids = array_map('intval', array_column($page_slice['data']['items'] ?? [], 'id'));

check('10: offset=2&limit=2 returns the next page', $page_slice['status'] === 200 && is_array($page_slice['data']['items'] ?? null) && count($page_slice['data']['items']) === 2);
check('10: the next page continues the expected order', $page_slice_ids === array_slice($COMPANY_EXPECTED, 2, 2));

$page_beyond = messaging_company_request('/api/v1/conversations?limit=20&offset=20', 'GET', $READ_COMPANY_USER_ID);
check('10: offset beyond the total returns the empty page', $page_beyond['status'] === 200 && is_array($page_beyond['data']['items'] ?? null) && $page_beyond['data']['items'] === []);
check('10: the empty page still returns metadata', (int) ($page_beyond['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL && (int) ($page_beyond['data']['pagination']['limit'] ?? -1) === 20 && (int) ($page_beyond['data']['pagination']['offset'] ?? -1) === 20);

$default_page = messaging_company_request('/api/v1/conversations', 'GET', $READ_COMPANY_USER_ID);
check('11: the default limit is 20', (int) ($default_page['data']['pagination']['limit'] ?? -1) === 20 && (int) ($default_page['data']['pagination']['offset'] ?? -1) === 0);

$max_page = messaging_company_request('/api/v1/conversations?limit=100', 'GET', $READ_COMPANY_USER_ID);
check('12: limit=100 is accepted', $max_page['status'] === 200 && (int) ($max_page['data']['pagination']['limit'] ?? -1) === 100);

$limit_zero  = messaging_company_request('/api/v1/conversations?limit=0', 'GET', $READ_COMPANY_USER_ID);
$limit_high  = messaging_company_request('/api/v1/conversations?limit=101', 'GET', $READ_COMPANY_USER_ID);
$limit_text  = messaging_company_request('/api/v1/conversations?limit=abc', 'GET', $READ_COMPANY_USER_ID);
$offset_neg  = messaging_company_request('/api/v1/conversations?offset=-1', 'GET', $READ_COMPANY_USER_ID);
$offset_text = messaging_company_request('/api/v1/conversations?offset=abc', 'GET', $READ_COMPANY_USER_ID);

check('13: limit=0 is rejected (400 + errors.limit)', $limit_zero['status'] === 400 && isset($limit_zero['errors']['limit']));
check('14: limit=101 is rejected (400 + errors.limit)', $limit_high['status'] === 400 && isset($limit_high['errors']['limit']));
check('16: limit=abc is rejected (400 + errors.limit)', $limit_text['status'] === 400 && isset($limit_text['errors']['limit']));
check('15: offset=-1 is rejected (400 + errors.offset)', $offset_neg['status'] === 400 && isset($offset_neg['errors']['offset']));
check('16: offset=abc is rejected (400 + errors.offset)', $offset_text['status'] === 400 && isset($offset_text['errors']['offset']));

$total_a = messaging_company_request('/api/v1/conversations?limit=1&offset=0', 'GET', $READ_COMPANY_USER_ID);
$total_b = messaging_company_request('/api/v1/conversations?limit=100&offset=0', 'GET', $READ_COMPANY_USER_ID);
$total_c = messaging_company_request('/api/v1/conversations', 'GET', $READ_COMPANY_USER_ID);
check('18: pagination.total is independent of limit and offset', (int) ($total_a['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL && (int) ($total_b['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL && (int) ($total_c['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL);

echo "\n== Authorization ==\n";

$student_list = messaging_student_request('/api/v1/conversations', 'GET', $READ_STUDENT_USER_ID);
check(
    '19: the student only sees their own conversations',
    messaging_conversations_sorted(messaging_conversations_ids($student_list['data'] ?? []))
    === messaging_conversations_sorted($STUDENT_EXPECTED)
);
check('21: the student pagination total is scoped to the student', (int) ($student_list['data']['pagination']['total'] ?? -1) === $STUDENT_TOTAL);

$other_student_list = messaging_student_request('/api/v1/conversations', 'GET', $OTHER_STUDENT_USER_ID);
check(
    '19/20: the other student only sees their own conversations',
    messaging_conversations_sorted(messaging_conversations_ids($other_student_list['data'] ?? []))
    === messaging_conversations_sorted($OTHER_STUDENT_EXPECTED)
);

$company_list = messaging_company_request('/api/v1/conversations', 'GET', $READ_COMPANY_USER_ID);
check(
    '20: the company only sees their own conversations',
    messaging_conversations_sorted(messaging_conversations_ids($company_list['data'] ?? []))
    === messaging_conversations_sorted($COMPANY_EXPECTED)
);
check('21: the company pagination total is scoped to the company', (int) ($company_list['data']['pagination']['total'] ?? -1) === $COMPANY_TOTAL);

$other_company_list = messaging_company_request('/api/v1/conversations', 'GET', $OTHER_COMPANY_USER_ID);
check(
    '20/21: the other company only sees their own conversations',
    messaging_conversations_sorted(messaging_conversations_ids($other_company_list['data'] ?? []))
    === messaging_conversations_sorted($OTHER_COMPANY_EXPECTED)
);
check('21: the other company total is scoped to the other company', (int) ($other_company_list['data']['pagination']['total'] ?? -1) === $OTHER_COMPANY_TOTAL);

$latest_ok = true;
foreach (($student_list['data']['items'] ?? []) as $item) {
    $conversation_id = (int) $item['id'];

    $expected_row = db_fetch_one(
        'SELECT id FROM messages WHERE conversation_id = ? ORDER BY created_at DESC, id DESC LIMIT 1',
        [$conversation_id]
    );
    $expected_latest = $expected_row === null ? 0 : (int) $expected_row['id'];
    $actual_latest = isset($item['last_message']) ? (int) $item['last_message']['id'] : 0;

    if ($expected_latest !== $actual_latest) {
        $latest_ok = false;
        break;
    }

    if ($expected_latest > 0) {
        $belongs = (int) db_fetch_column(
            'SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND id = ?',
            [$conversation_id, $expected_latest]
        ) === 1;

        if (!$belongs) {
            $latest_ok = false;
            break;
        }
    }
}
check('22: last_message is null for empty conversations and belongs to each item', $latest_ok);

echo "\n== Regression ==\n";

$show_225 = messaging_student_request('/api/v1/conversations/225', 'GET', $READ_STUDENT_USER_ID);
check('23: the conversation details endpoint is gone (404)', $show_225['status'] === 404);

$send_225 = messaging_student_request(
    '/api/v1/conversations/225/messages',
    'POST',
    $READ_STUDENT_USER_ID,
    ['body' => $prefix . 'regression-message']
);
check('24: message creation remains compatible (201)', $send_225['status'] === 201);
db_execute('DELETE FROM messages WHERE body LIKE ?', [$prefix . 'regression-message%']);
db_execute('DELETE FROM message_events WHERE body LIKE ?', [$prefix . 'regression-message%']);

$message_ids_225 = array_map(
    'intval',
    array_column(
        db_fetch_all(
            'SELECT id FROM messages WHERE conversation_id = ? ORDER BY id ASC',
            [$READ_CONVERSATION_ID]
        ),
        'id'
    )
);

$after_id = count($message_ids_225) >= 2
    ? $message_ids_225[count($message_ids_225) - 2]
    : 0;

$after_response = messaging_student_request(
    '/api/v1/conversations/225/messages?after_id=' . $after_id,
    'GET',
    $READ_STUDENT_USER_ID
);

$after_expected = array_map(
    'intval',
    array_column(
        db_fetch_all(
            'SELECT id FROM messages WHERE conversation_id = ? AND id > ? ORDER BY created_at ASC, id ASC',
            [$READ_CONVERSATION_ID, $after_id]
        ),
        'id'
    )
);

$after_actual = is_array($after_response['data'] ?? null)
    ? array_map('intval', array_column($after_response['data'], 'id'))
    : null;

check('25: after_id still retrieves newer messages', $after_response['status'] === 200 && $after_actual === $after_expected);

$before_id = count($message_ids_225) >= 1
    ? $message_ids_225[count($message_ids_225) - 1]
    : 0;

$before_response = messaging_student_request(
    '/api/v1/conversations/225/messages?before_id=' . $before_id . '&order=desc',
    'GET',
    $READ_STUDENT_USER_ID
);

$before_expected = array_map(
    'intval',
    array_column(
        db_fetch_all(
            'SELECT id FROM messages WHERE conversation_id = ? AND id < ? ORDER BY created_at DESC, id DESC',
            [$READ_CONVERSATION_ID, $before_id]
        ),
        'id'
    )
);

$before_actual = is_array($before_response['data'] ?? null)
    ? array_map('intval', array_column($before_response['data'], 'id'))
    : null;

check('26: before_id with order=desc still retrieves older messages', $before_response['status'] === 200 && $before_actual === $before_expected);

$other_read = messaging_student_request('/api/v1/conversations/225/messages', 'GET', $OTHER_STUDENT_USER_ID);
$other_company_read = messaging_company_request('/api/v1/conversations/225/messages', 'GET', $OTHER_COMPANY_USER_ID);
check('27: another student cannot read conversation 225 messages (403)', $other_read['status'] === 403);
check('27: another company cannot read conversation 225 messages (403)', $other_company_read['status'] === 403);

echo "\n== Cleanup ==\n";

messaging_conversations_list_cleanup();

check(
    'Cleanup: no tagged fixture messages remain',
    (int) db_fetch_column('SELECT COUNT(*) FROM messages WHERE body LIKE ?', [$prefix . '%']) === 0
);
check(
    'Cleanup: the fixture conversation was removed',
    (int) db_fetch_column('SELECT COUNT(*) FROM conversations WHERE id = ?', [$fixture_conversation_id]) === 0
);
check(
    'Cleanup: the fixture application was removed',
    (int) db_fetch_column('SELECT COUNT(*) FROM training_applications WHERE id = ?', [$fixture_application_id]) === 0
);
check(
    'Cleanup: the fixture listing was removed',
    (int) db_fetch_column('SELECT COUNT(*) FROM training_listings WHERE id = ?', [$fixture_listing_id]) === 0
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
