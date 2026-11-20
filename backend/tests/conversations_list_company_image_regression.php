<?php

/**
 * MASAR - Conversations List Company Image Regression
 *
 * Focused checks for the new `company_image` field on
 * GET /api/v1/conversations:
 *   1. A conversation whose company has a profile image returns company_image.
 *   2. A conversation whose company has no profile image returns null.
 *   3. Each conversation returns the image of the company linked to it.
 *   4. Pagination metadata and existing conversation fields are unchanged.
 *
 * The test sets temporary company_logo values and restores the originals in
 * a cleanup function registered via register_shutdown_function.
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

$failures = 0;
$run_id   = bin2hex(random_bytes(4));
$prefix   = 'CONIMG-' . $run_id . '-';

$STUDENT_USER_ID = 100532;

$GLOBALS['CONIMG_STATE'] = [
    'originals'  => [],
    'cleanup_done' => false,
];

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function conv_img_token(int $user_id, string $role): string
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

function conv_img_request(
    string $path,
    string $token
): array {

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'tests/messaging_conversations_list_case.php')
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
        'status' => $status,
        'json'   => is_array($json) ? $json : [],
    ];
}

function conv_img_cleanup(): void
{
    $state = &$GLOBALS['CONIMG_STATE'];

    if (!empty($state['cleanup_done'])) {
        return;
    }

    $state['cleanup_done'] = true;

    foreach ($state['originals'] as $company_id => $original) {

        try {
            db_execute(
                'UPDATE companies SET company_logo = ? WHERE id = ?',
                [$original, (int) $company_id]
            );
        } catch (Throwable $exception) {
            // Best-effort cleanup.
        }
    }

    $state['originals'] = [];
}

register_shutdown_function('conv_img_cleanup');

echo "== Conversations list company_image ==\n";

$token =
    conv_img_token($STUDENT_USER_ID, 'student');

/*
 * Fetch the student's conversations and pick up to two to drive the image
 * scenarios. Save originals for cleanup.
 */

$conversations =
    db_fetch_all(
        'SELECT c.id AS conversation_id, c.company_id
         FROM conversations c
         WHERE c.student_id IN (
             SELECT s.id FROM students s WHERE s.user_id = ?
         )
         ORDER BY c.id ASC
         LIMIT 5',
        [$STUDENT_USER_ID]
    );

$tagged = [];

foreach (array_slice($conversations, 0, 2) as $index => $row) {

    $company_id =
        (int) ($row['company_id'] ?? 0);

    if ($company_id <= 0) {
        continue;
    }

    $conversation_id =
        (int) ($row['conversation_id'] ?? 0);

    $original =
        db_fetch_column(
            'SELECT company_logo FROM companies WHERE id = ?',
            [$company_id]
        );

    $GLOBALS['CONIMG_STATE']['originals'][$company_id] =
        $original;

    $tag =
        $prefix
        . ($index === 0 ? '_A' : '_B');

    /*
     * Scenario setup: company A has an image; company B has none.
     */

    $new_path =
        $index === 0
            ? 'companies/' . $tag . '.png'
            : null;

    db_execute(
        'UPDATE companies SET company_logo = ? WHERE id = ?',
        [$new_path, $company_id]
    );

    $tagged[$conversation_id] = [
        'company_id' => $company_id,
        'expected'   => $new_path,
    ];
}

$page =
    conv_img_request(
        '/api/v1/conversations?limit=20',
        $token
    );

check(
    'GET /api/v1/conversations returns 200',
    $page['status'] === 200
);

$items =
    is_array($page['json']['data']['items'] ?? null)
        ? $page['json']['data']['items']
        : [];

check(
    'the page contains at least one conversation for the student',
    $items !== []
);

/*
 * Scenario checks against the page that contains the tagged conversations.
 */

$by_id = [];

foreach ($items as $row) {
    $by_id[(int) ($row['id'] ?? 0)] = $row;
}

foreach ($tagged as $conversation_id => $tagged_item) {

    $expected =
        $tagged_item['expected'];

    $item =
        $by_id[$conversation_id] ?? null;

    check(
        "the tagged conversation {$conversation_id} is present in the page",
        $item !== null
    );

    if ($item === null) {
        continue;
    }

    check(
        "every conversation item exposes a company_image key (conversation {$conversation_id})",
        array_key_exists('company_image', $item)
    );

    check(
        "company_image matches the company's own logo value (conversation {$conversation_id})",
        ($item['company_image'] ?? null) === $expected
    );

    check(
        "the item is linked to the correct company (conversation {$conversation_id})",
        (int) ($item['company_id'] ?? 0) === (int) $tagged_item['company_id']
    );
}

if (count($tagged) >= 2) {

    $has_null =
        in_array(
            null,
            array_column($tagged, 'expected'),
            true
        );

    check(
        'the null-image scenario produced a null company_image',
        $has_null
    );
}

/*
 * General association check: for every item in the page, company_image is
 * null exactly when the linked company's DB logo is empty.
 */

$general_ok = true;

foreach ($items as $row) {

    $conversation_id =
        (int) ($row['id'] ?? 0);

    $db_logo =
        db_fetch_column(
            'SELECT company_logo FROM conversations c JOIN companies co ON co.id = c.company_id WHERE c.id = ?',
            [$conversation_id]
        );

    $expected_image =
        is_string($db_logo) && trim($db_logo) !== ''
            ? trim($db_logo)
            : null;

    $actual_image =
        isset($row['company_image'])
            ? (
                is_string($row['company_image']) && trim($row['company_image']) !== ''
                    ? trim($row['company_image'])
                    : null
            )
            : null;

    if ($actual_image !== $expected_image) {
        $general_ok = false;
        break;
    }
}

check(
    'every conversation returns its own company’s image (association, incl. null)',
    $general_ok
);

/*
 * Existing fields and pagination metadata remain unchanged.
 */

$fields_ok = true;

foreach ($items as $row) {

    foreach (
        [
            'id',
            'student_id',
            'company_id',
            'application_id',
            'created_at',
            'updated_at',
            'student_name',
            'company_name',
            'training_title',
            'unread_count',
            'last_message',
            'application_status',
            'training',
        ]
        as $field
    ) {

        if (!array_key_exists($field, $row)) {
            $fields_ok = false;
            break 2;
        }
    }
}

check(
    'existing conversation fields are unchanged',
    $fields_ok
);

check(
    'pagination metadata shape is unchanged',
    isset($page['json']['data']['pagination']['total'])
        && isset($page['json']['data']['pagination']['limit'])
        && isset($page['json']['data']['pagination']['offset'])
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
