<?php

/**
 * MASAR - Student Profile Views Seeded Regression Test
 *
 * Verifies the deterministic profile-view dataset written by
 * database/seeders/student_profile_views_seeder.php and the read-only
 * GET /api/v1/students/me/profile-views contract.
 *
 * Covered:
 * - The target student receives the 13 seeded rows.
 * - total_views is a real COUNT(*) and is not capped at recent_limit.
 * - recent_viewers is limited to 6 by default, ordered newest first, and
 *   matches the seeded viewer identities.
 * - limit=3 shortens the recent list without changing total_views.
 * - limit=10 still returns 10 viewers, so the default does not cap custom limits.
 * - A student with no rows still receives 0 and an empty list.
 * - Guest requests are 401 and company requests are 403.
 * - The recent list exposes only viewer_id, viewer_role, display_name and
 *   viewed_at; no email or phone leaks.
 * - The target student has no self-view, and reading the summary does not
 *   create one. The endpoint is read-only by contract.
 * - Running the seeder twice keeps exactly 13 rows with the same identities
 *   and timestamps.
 *
 * This suite intentionally leaves the seeded rows in place: the dataset is
 * the fixture the profile-views UI is meant to demonstrate. It does not delete
 * or modify any other student's rows.
 *
 * The request shim is the existing generic tests/student_profile_account_case.php.
 *
 * Run from the backend root:
 *     php tests/student_profile_views_seeded_regression.php
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
require_once BASE . 'database/seeders/student_profile_views_seeder.php';

$failures = 0;

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function request_api(
    string $path,
    string $method,
    string $scenario = 'bearer',
    string $token = '',
    ?array $body = null,
    ?array $upload = null,
    string $route_file = 'students.php'
): array {
    $case_file = dirname(__FILE__) . '/student_profile_account_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($route_file)
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_BODY'   => $body === null ? '' : (string) json_encode($body),
        'MASAR_CASE_UPLOAD' => $upload === null ? '' : (string) json_encode($upload),
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    $process = proc_open($command, $descriptors, $pipes, BASE, $environment + getenv());

    $output = '';

    if (is_resource($process)) {
        $output = (string) stream_get_contents($pipes[1]);
        $errors = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        if (trim($errors) !== '') {
            $output .= "\n" . $errors;
        }
    }

    $status = 0;
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
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'message' => is_array($payload) ? ($payload['message'] ?? null) : null,
        'errors'  => is_array($payload) ? ($payload['errors'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? null) : null,
    ];
}

function token_for(int $user_id, string $role = ROLE_STUDENT): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
}

function profile_view_rows(int $student_id): array
{
    return db_fetch_all(
        'SELECT id, viewer_id, viewer_role, viewed_at
         FROM student_profile_views
         WHERE student_id = ?
         ORDER BY viewer_id',
        [$student_id]
    );
}

$target = db_fetch_one(
    "SELECT s.id AS student_id, s.user_id, u.email, s.full_name
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     WHERE u.email = ?
     LIMIT 1",
    [PVIEW_TARGET_EMAIL]
);

if (!is_array($target)) {
    fwrite(STDERR, 'Target student ' . PVIEW_TARGET_EMAIL . " was not found.\n");
    exit(1);
}

$target_student_id = (int) $target['student_id'];
$target_user_id = (int) $target['user_id'];

$zero_view_student = db_fetch_one(
    "SELECT s.id AS student_id, s.user_id, u.email, s.full_name
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     WHERE u.email = ?
     LIMIT 1",
    ['youssef.farouk@outlook.com']
);

$company_user = db_fetch_one(
    "SELECT u.id AS user_id, u.email, u.role
     FROM users u
     WHERE u.email = ?
     LIMIT 1",
    ['careers@niletech.eg']
);

if (!is_array($zero_view_student) || !is_array($company_user)) {
    fwrite(STDERR, "Required regression fixture users were not found.\n");
    exit(1);
}

check('target student exists', $target_student_id > 0);
check('target student is the seeded test student', (string) $target['email'] === PVIEW_TARGET_EMAIL);
check('zero-view fixture student exists', (int) $zero_view_student['student_id'] > 0);
check('company fixture exists and is a company', (string) $company_user['role'] === ROLE_COMPANY);

$zero_view_count = (int) db_fetch_column(
    'SELECT COUNT(*) FROM student_profile_views WHERE student_id = ?',
    [(int) $zero_view_student['student_id']]
);
check('zero-view fixture really has no profile views', $zero_view_count === 0);

$pdo = get_database_connection();

$first_report = pview_seed($pdo);
$after_first = profile_view_rows($target_student_id);

check('seeder resolves 13 viewer identities', count($first_report['viewers']) === 13);
check('first seeder run writes 13 rows', count($after_first) === 13);
check('first seeder run reports 13 rows', (int) $first_report['total'] === 13);
check('all seeded viewers are distinct', count(array_unique(array_column($after_first, 'viewer_id'))) === 13);

$second_report = pview_seed($pdo);
$after_second = profile_view_rows($target_student_id);

check('second seeder run keeps 13 rows', count($after_second) === 13);
check('second seeder run reports 13 rows', (int) $second_report['total'] === 13);
check('second seeder run changes no row identities or timestamps', $after_first === $after_second);

$seeded_viewer_ids = array_map('intval', array_column($after_second, 'viewer_id'));
check('no self-view row exists for the target student', !in_array($target_user_id, $seeded_viewer_ids, true));

$expected_viewers = $second_report['viewers'];
$expected_top_6 = array_slice($expected_viewers, 0, 6);
$expected_top_3 = array_slice($expected_viewers, 0, 3);

$token = token_for($target_user_id);
$response = request_api('/api/v1/students/me/profile-views', 'GET', 'bearer', $token);

check('profile-views request succeeds', $response['status'] === 200 && $response['success'] === true);
check('profile-views response keeps the envelope', $response['errors'] === null && $response['message'] === null);

$data = is_array($response['data']) ? $response['data'] : [];
$recent = is_array($data['recent_viewers'] ?? null) ? $data['recent_viewers'] : [];

check('total_views is the seeded total 13', ($data['total_views'] ?? null) === 13);
check('recent_limit defaults to 6', ($data['recent_limit'] ?? null) === 6);
check('recent_viewers is populated with 6 rows', count($recent) === 6);
check('total_views is not capped at recent_limit', ($data['total_views'] ?? 0) > count($recent));
check(
    'recent viewer ids match the newest-first seed',
    array_column($recent, 'viewer_id') === array_column($expected_top_6, 'viewer_id')
);
check(
    'recent display names match the seed',
    array_column($recent, 'display_name') === array_column($expected_top_6, 'display_name')
);
check(
    'recent viewer roles match the seed',
    array_column($recent, 'viewer_role') === array_column($expected_top_6, 'viewer_role')
);

$timestamps = array_map(
    static fn (array $viewer): int|false => strtotime((string) ($viewer['viewed_at'] ?? '')),
    $recent
);

$newest_first = true;
for ($index = 0; $index < count($timestamps) - 1; $index++) {
    if (
        $timestamps[$index] === false
        || $timestamps[$index + 1] === false
        || $timestamps[$index] <= $timestamps[$index + 1]
    ) {
        $newest_first = false;
        break;
    }
}
check('recent_viewers is ordered newest first', $newest_first);

$contract_shape = true;
foreach ($recent as $viewer) {
    if (array_keys($viewer) !== ['viewer_id', 'viewer_role', 'display_name', 'viewed_at']) {
        $contract_shape = false;
    }
}
check('recent_viewers exposes exactly the contract fields', $contract_shape);
check(
    'recent_viewers does not leak email or phone',
    !str_contains($response['raw'], '@') && !str_contains(strtolower($response['raw']), 'phone')
);

$after_api = profile_view_rows($target_student_id);
check('reading the summary creates no self-view or new row', $after_api === $after_second);

$limited = request_api('/api/v1/students/me/profile-views?limit=3', 'GET', 'bearer', $token);
$limited_data = is_array($limited['data']) ? $limited['data'] : [];
$limited_recent = is_array($limited_data['recent_viewers'] ?? null)
    ? $limited_data['recent_viewers']
    : [];

check('limit=3 returns exactly 3 viewers', count($limited_recent) === 3);
check('limit=3 keeps total_views at 13', ($limited_data['total_views'] ?? null) === 13);
check('limit=3 echoes recent_limit 3', ($limited_data['recent_limit'] ?? null) === 3);
check(
    'limit=3 returns the newest-first prefix',
    array_column($limited_recent, 'viewer_id') === array_column($expected_top_3, 'viewer_id')
);

$custom = request_api('/api/v1/students/me/profile-views?limit=10', 'GET', 'bearer', $token);
$custom_data = is_array($custom['data']) ? $custom['data'] : [];
$custom_recent = is_array($custom_data['recent_viewers'] ?? null)
    ? $custom_data['recent_viewers']
    : [];
$expected_top_10 = array_slice($expected_viewers, 0, 10);

check('limit=10 still returns 10 viewers', count($custom_recent) === 10);
check('limit=10 keeps total_views at 13', ($custom_data['total_views'] ?? null) === 13);
check('limit=10 echoes recent_limit 10', ($custom_data['recent_limit'] ?? null) === 10);
check(
    'limit=10 returns the newest-first prefix',
    array_column($custom_recent, 'viewer_id') === array_column($expected_top_10, 'viewer_id')
);

$zero_response = request_api(
    '/api/v1/students/me/profile-views',
    'GET',
    'bearer',
    token_for((int) $zero_view_student['user_id'])
);
$zero_data = is_array($zero_response['data']) ? $zero_response['data'] : [];

check('student without views gets total_views 0', ($zero_data['total_views'] ?? null) === 0);
check('student without views gets an empty recent_viewers list', ($zero_data['recent_viewers'] ?? null) === []);
check('student without views still gets recent_limit 6', ($zero_data['recent_limit'] ?? null) === 6);

$guest_response = request_api('/api/v1/students/me/profile-views', 'GET', 'guest');
check('guest is rejected with 401', $guest_response['status'] === 401);

$company_response = request_api(
    '/api/v1/students/me/profile-views',
    'GET',
    'bearer',
    token_for((int) $company_user['user_id'], ROLE_COMPANY)
);
check('company is rejected with 403', $company_response['status'] === 403);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
