<?php

/**
 * MASAR - Conversation Meta Regression Suite
 *
 * Focused checks for the top-level `conversation` object on
 * GET /api/v1/conversations/{id}/messages (WhatsApp-style header):
 *
 *  A. Student gets the correct company id and name (authorization boundary).
 *  B. Company image is the existing relative storage path and the file is
 *     accessible; a company without a logo returns null.
 *  C. A non-participant gets 403 (no company metadata leaks).
 *  D. The company user reads the same conversation header.
 *  E. `data` stays the flat message array; company info is not repeated
 *     inside message objects; pagination metadata is preserved.
 *
 * Fixtures: conversation 262 (student 100588, company 100161) and conversation
 * 225 (student 100532, company 100144). Both are pre-existing seeded rows.
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
require_once BASE . 'app/modules/files/services/file_upload_service.php';

$failures = 0;

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function meta_token(int $user_id, string $role): string
{
    return jwt_issue_access_token([
        'id'   => $user_id,
        'role' => $role,
    ]);
}

function meta_request(string $path, int $user_id, string $role): array
{
    $token = meta_token($user_id, $role);

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'tests/messaging_conversation_meta_case.php')
        . ' '
        . escapeshellarg($path)
        . ' GET bearer '
        . escapeshellarg($token);

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

$db_logos = [];
foreach (db_fetch_all('SELECT id, company_logo FROM companies') as $row) {
    $db_logos[(int) $row['id']] = $row['company_logo'];
}

$conv262 = db_fetch_one('SELECT id, company_id FROM conversations WHERE id = 262');
$conv225 = db_fetch_one('SELECT id, company_id FROM conversations WHERE id = 225');

check('fixture conversation 262 exists', is_array($conv262));
check('fixture conversation 225 exists', is_array($conv225));

echo "\n== Test A: authorized student receives the correct company header ==\n";

$page_262 =
    meta_request(
        '/api/v1/conversations/262/messages',
        100588,
        'student'
    );

check('A: GET /api/v1/conversations/262/messages succeeded (200)', $page_262['status'] === 200);

$conversation_262 = $page_262['json']['conversation'] ?? null;

check('A: conversation.id is 262', is_array($conversation_262) && (int) ($conversation_262['id'] ?? 0) === 262);
check(
    'A: company.id matches DB (100161)',
    is_array($conversation_262) && (int) ($conversation_262['company']['id'] ?? 0) === (int) ($conv262['company_id'] ?? 0)
);
check(
    'A: company.name is the actual company name',
    is_array($conversation_262)
        && ($conversation_262['company']['name'] ?? null) === (db_fetch_column('SELECT legal_name FROM companies WHERE id = ' . (int) $conv262['company_id']))
);
check(
    'A: company.image equals the DB company_logo',
    is_array($conversation_262)
        && ($conversation_262['company']['image'] ?? null) === ($db_logos[(int) $conv262['company_id']] ?? null)
);
check(
    'A: image points to an accessible file (is_file + getimagesize)',
    is_array($conversation_262)
        && ($conversation_262['company']['image'] ?? null) !== null
        && is_file(
            rtrim((string) file_upload_service_storage_base(), '/')
                . '/'
                . (string) $conversation_262['company']['image']
        )
        && getimagesize(
            rtrim((string) file_upload_service_storage_base(), '/')
                . '/'
                . (string) $conversation_262['company']['image']
        ) !== false
);

echo "\n== Test B: company without an image returns null (conv 225) ==\n";

$page_225 =
    meta_request(
        '/api/v1/conversations/225/messages',
        100532,
        'student'
    );

check('B: GET /api/v1/conversations/225/messages succeeded (200)', $page_225['status'] === 200);

$conversation_225 = $page_225['json']['conversation'] ?? null;

check('B: conversation.id is 225', is_array($conversation_225) && (int) ($conversation_225['id'] ?? 0) === 225);

$db_logo_225 =
    db_fetch_column(
        'SELECT company_logo FROM companies WHERE id = ?',
        [(int) $conv225['company_id']]
    );

check(
    'B: company.image is null when the company has no logo',
    is_array($conversation_225)
        && ($conversation_225['company']['image'] ?? null) === null
        && $db_logo_225 === null
);

echo "\n== Test C: non-participant cannot retrieve the metadata ==\n";

$page_other =
    meta_request(
        '/api/v1/conversations/225/messages',
        100530,
        'student'
    );

check('C: a non-participant gets 403 (no company metadata)', $page_other['status'] === 403);

echo "\n== Test D: the company participant also receives the header ==\n";

$page_by_company =
    meta_request(
        '/api/v1/conversations/262/messages',
        100595,
        'company'
    );

check('D: the company user reads conv 262 (200)', $page_by_company['status'] === 200);

$conversation_by_company = $page_by_company['json']['conversation'] ?? null;

check(
    'D: same company id and name',
    is_array($conversation_by_company)
        && (int) ($conversation_by_company['id'] ?? 0) === 262
        && ($conversation_by_company['company']['id'] ?? null) === 100161
        && ($conversation_by_company['company']['name'] ?? null) === 'TestHire Solutions'
);

echo "\n== Test E: data unchanged; company info not repeated; pagination preserved ==\n";

$messages_262 = is_array($page_262['json']['data'] ?? null) ? $page_262['json']['data'] : [];
$messages_225 = is_array($page_225['json']['data'] ?? null) ? $page_225['json']['data'] : [];

check('E: data is the flat message array for conv 262', is_array($page_262['json']['data'] ?? null));
check('E: data is the flat message array for conv 225', is_array($page_225['json']['data'] ?? null));
check('E: conv 262 has messages', $messages_262 !== []);

$message_keys_ok = true;
$company_repeated = false;

foreach (array_merge($messages_262, $messages_225) as $message) {
    foreach (['id', 'conversation_id', 'sender_user_id', 'body', 'is_read', 'created_at'] as $key) {
        if (!array_key_exists($key, $message)) {
            $message_keys_ok = false;
        }
    }
    if (array_key_exists('company', $message) || array_key_exists('company_image', $message)) {
        $company_repeated = true;
    }
}

check('E: message objects have the standard keys (id, conversation_id, sender_user_id, body, is_read, created_at)', $message_keys_ok);
check('E: company info is not repeated inside message objects', !$company_repeated);

check(
    'E: pagination metadata preserved (limit=12 default, has_more bool, cursors)',
    ($page_262['json']['pagination'] ?? null) !== null
        && ((int) ($page_262['json']['pagination']['limit'] ?? -1)) === 12
        && array_key_exists('has_more', $page_262['json']['pagination'] ?? [])
        && array_key_exists('next_before_id', $page_262['json']['pagination'] ?? [])
        && array_key_exists('next_after_id', $page_262['json']['pagination'] ?? [])
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
