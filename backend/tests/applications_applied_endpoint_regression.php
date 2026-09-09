<?php

/**
 * MASAR - Applications Applied Endpoint Regression Test
 *
 * Verifies the dedicated GET /api/v1/applications/applied endpoint:
 *
 *   Case 1  Guest (no credentials)  -> 401 Unauthorized
 *   Case 2  Invalid/expired token   -> 401 Unauthorized
 *   Case 3  Company token           -> 403 'Student access required.'
 *   Case 4  Valid student token     -> 200 OK, ONLY pending/submitted apps
 *           scoped to the student's own specialization
 *           (items all 'Applied' and each training_listing.specialization_id
 *           equals the student's specialization; mismatched pending trainings
 *           are excluded; count matches the scoped
 *           application_service_list_student(status=pending, scope))
 *
 * Run from the backend root:
 *     php tests/applications_applied_endpoint_regression.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/modules/training/services/application_service.php';
require_once BASE . 'app/modules/training/services/training_service.php';
require_once BASE . 'app/shared/functions/application_cards.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/applications/applied'): array
{
    $case_file = dirname(__FILE__) . '/applications_applied_endpoint_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token)
        . ' ' . escapeshellarg($request_uri);

    $output = (string) shell_exec($command . ' 2>&1');

    $status = 0;
    $body = '';
    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, strlen('STATUS='));
        }
        if (str_starts_with($line, 'BODY=')) {
            $body = substr($line, strlen('BODY='));
        }
    }

    $payload = json_decode($body, true);

    return [
        'status'  => $status,
        'body'    => $body,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? []) : [],
    ];
}

function items(array $scenario_result): array
{
    $data = $scenario_result['data'];
    $items = is_array($data) ? ($data['items'] ?? []) : [];
    return is_array($items) ? $items : [];
}

echo "== Setup: student context ==\n";

$student = db_fetch_one(
    "SELECT u.id AS user_id, u.role, u.status, s.id AS student_id,
            s.specialization_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.email = 'mammuslim2003@gmail.com'
     LIMIT 1"
);

if (!is_array($student)) {
    $student = db_fetch_one(
        "SELECT u.id AS user_id, u.role, u.status, s.id AS student_id,
                s.specialization_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.status = 'active' AND u.role = 'student'
         ORDER BY s.id LIMIT 1"
    );
}

check('test student resolved', is_array($student));

$company = db_fetch_one(
    "SELECT u.id AS user_id FROM users u
     JOIN companies c ON c.user_id = u.id
     WHERE u.email = 'company@test.local'
     LIMIT 1"
);

check('test company resolved', is_array($company));

if (is_array($student)) {
    $user_id = (int) $student['user_id'];

    $valid_token = jwt_issue_access_token([
        'id' => $user_id,
        'role' => 'student',
    ]);

    $company_token = '';
    if (is_array($company)) {
        $company_token = jwt_issue_access_token([
            'id' => (int) $company['user_id'],
            'role' => 'company',
        ]);
    }

    $config = jwt_config();
    $now = time();
    $expired_token = jwt_sign(
        json_encode(['alg' => $config['algorithm'], 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES),
        json_encode([
            'sub' => $user_id,
            'role' => 'student',
            'type' => 'access',
            'iat' => $now - 7200,
            'exp' => $now - 3600,
            'jti' => bin2hex(random_bytes(16)),
        ], JSON_UNESCAPED_SLASHES),
        $config['secret'],
        $config['algorithm']
    );

    $invalid_token = 'invalid-token-not-a-jwt';
}

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_scenario('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2a: expired student access token ==\n";

$expired = run_scenario('bearer', $expired_token ?? '');
check('expired token returns HTTP 401', $expired['status'] === 401);
check('expired token returns success=false', $expired['success'] === false);

echo "\n== Case 2b: invalid (malformed) access token ==\n";

$invalid = run_scenario('bearer', $invalid_token ?? '');
check('invalid token returns HTTP 401', $invalid['status'] === 401);
check('invalid token returns success=false', $invalid['success'] === false);

echo "\n== Case 3: company token on student endpoint ==\n";

$company_case = run_scenario('bearer', $company_token ?? '');
check('company token returns HTTP 403', $company_case['status'] === 403);
check('company token body mentions student access', stripos($company_case['body'], 'student access') !== false);

echo "\n== Case 4: valid student token -> /applications/applied (Applied Card DTO) ==\n";

$applied = run_scenario('bearer', $valid_token ?? '');
check('applied returns HTTP 200', $applied['status'] === 200);
check('applied returns success=true', $applied['success'] === true);

$applied_data = is_array($applied['data']) ? $applied['data'] : [];
check('applied data is an array', is_array($applied_data));
check('applied data has pagination', isset($applied_data['pagination']) && is_array($applied_data['pagination']));

$applied_items = items($applied);
check('applied returns at least one application', count($applied_items) >= 1);

$student_spec_id = (int) ($student['specialization_id'] ?? 0);
$student_spec_row = $student_spec_id > 0
    ? db_fetch_one(
        "SELECT name FROM specializations WHERE id = ? LIMIT 1",
        [$student_spec_id]
    )
    : null;
$student_spec_name = is_array($student_spec_row) ? (string) $student_spec_row['name'] : '';

$expected_match_trainings = [];
if (is_array($student) && $student_spec_id > 0) {
    $match_rows = db_fetch_all(
        "SELECT a.training_id
         FROM training_applications a
         INNER JOIN training_listings t
             ON t.id = a.training_id
         WHERE
             a.student_id = ?
             AND a.status = 'submitted'
             AND t.specialization_id = ?
         ORDER BY a.training_id ASC",
        [(int) $student['student_id'], $student_spec_id]
    );
    foreach (is_array($match_rows) ? $match_rows : [] as $mr) {
        $expected_match_trainings[] = (int) $mr['training_id'];
    }
}
$expected_match_trainings = array_values(array_unique($expected_match_trainings));

$pagination_total = (int) ($applied_data['pagination']['total'] ?? -1);
check('applied pagination total equals the specialization-filtered count', $pagination_total === count($expected_match_trainings));
check('applied specialization-filtered set is non-empty', count($expected_match_trainings) >= 1);

$required_keys = [
    'id', 'training_id', 'training_title', 'status', 'specialization',
    'company_name', 'company_logo', 'training_type', 'method', 'is_paid',
    'may_lead_to_hire', 'applied_at', 'starts_at', 'ends_at', 'duration',
    'can_withdraw',
];
$forbidden_keys = [
    'full_name', 'email', 'phone', 'city', 'address', 'why_interested',
    'what_to_learn', 'skills', 'rejection_reason', 'rejection_note',
    'reviewed_at', 'withdrawn_at', 'reviewed_by', 'cv_file_id', 'faculty_id',
    'university', 'applicant_type', 'academic_year', 'graduation_year',
    'motivation',
];
$iso_regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)$/';

$key_ok = true;
$forbidden_present = [];
$status_ok = true;
$hire_ok = true;
$withdraw_ok = true;
$iso_ok = true;
$duration_ok = true;
$title_ok = true;
$spec_ok = true;
$company_ok = true;
$spec_matches_student = true;
$returned_training_ids = [];
$paid_items = 0;
$free_items = 0;

foreach ($applied_items as $item) {
    if (!is_array($item)) {
        $key_ok = false;
        continue;
    }

    if (($item['status'] ?? '') !== 'Applied') {
        $status_ok = false;
    }

    if ((bool)($item['may_lead_to_hire'] ?? false) !== (bool)($item['is_paid'] ?? false)) {
        $hire_ok = false;
    }

    if ((bool)($item['can_withdraw'] ?? false) !== true) {
        $withdraw_ok = false;
    }

    foreach ($forbidden_keys as $key) {
        if (array_key_exists($key, $item)) {
            $forbidden_present[] = $key;
        }
    }

    foreach ($required_keys as $key) {
        if (!array_key_exists($key, $item)) {
            $key_ok = false;
        }
    }

    foreach (['applied_at', 'starts_at', 'ends_at'] as $key) {
        if (($item[$key] ?? null) !== null && !preg_match($iso_regex, (string)$item[$key])) {
            $iso_ok = false;
        }
    }

    if (array_key_exists('is_paid', $item)) {
        if ($item['is_paid']) {
            $paid_items++;
        } else {
            $free_items++;
        }
    }

    $training_id = (int)($item['training_id'] ?? 0);
    $returned_training_ids[] = $training_id;

    if ((string)($item['specialization'] ?? '') !== $student_spec_name) {
        $spec_matches_student = false;
    }

    $row = $training_id > 0
        ? db_fetch_one(
            "SELECT
                 t.title AS training_title,
                 s.name  AS specialization_name,
                 c.legal_name AS company_name
             FROM training_listings t
             LEFT JOIN specializations s ON s.id = t.specialization_id
             LEFT JOIN companies c ON c.id = t.company_id
             WHERE t.id = ?
             LIMIT 1",
            [$training_id]
        )
        : null;

    if (is_array($row)) {
        if ((string)($item['training_title'] ?? '') !== (string)($row['training_title'] ?? '')) {
            $title_ok = false;
        }
        if ((string)($item['specialization'] ?? '') !== (string)($row['specialization_name'] ?? '')) {
            $spec_ok = false;
        }
        if ((string)($item['company_name'] ?? '') !== (string)($row['company_name'] ?? '')) {
            $company_ok = false;
        }
    } else {
        $title_ok = false;
    }

    $ends = db_fetch_one(
        "SELECT ends_at FROM training_listings WHERE id = ? LIMIT 1",
        [$training_id]
    );
    $expected_duration = is_array($ends) ? training_calculate_duration($ends['ends_at'] ?? null) : null;
    if (!is_int($expected_duration) || (int)($item['duration'] ?? -1) !== $expected_duration) {
        $duration_ok = false;
    }
}

check('applied only exposes the required DTO keys', $key_ok);
check('applied excludes all forbidden PII/detail keys', $forbidden_present === []);
check('applied item status equals "Applied"', $status_ok);
check('applied may_lead_to_hire always equals is_paid', $hire_ok);
check('applied can_withdraw is true for all pending items', $withdraw_ok);
check('applied timestamps are ISO-8601 with tz offset', $iso_ok);
check('applied duration equals training_calculate_duration', $duration_ok);
check('applied training_title matches DB', $title_ok);
check('applied specialization matches DB', $spec_ok);
check('applied company_name matches DB', $company_ok);
check('applied dataset contains at least one paid item', $paid_items >= 1);
check('applied dataset contains at least one free item', $free_items >= 1);

sort($returned_training_ids);
$missing_matches = array_diff($expected_match_trainings, $returned_training_ids);
$unexpected_matches = array_diff($returned_training_ids, $expected_match_trainings);
check('applied covers exactly the pending trainings matching the student specialization', $missing_matches === [] && $unexpected_matches === []);
check('applied every card specialization equals the student specialization', $spec_matches_student);
check('applied pagination total matches the returned specialization-filtered set', $pagination_total === count($returned_training_ids));

if (is_array($student)) {
    $expected = application_service_list_student(
        (int) $student['user_id'],
        ['status' => 'pending', 'page' => 1, 'limit' => 100, 'scope_to_specialization' => true]
    );
    $expected_items = is_array($expected['data'] ?? null) ? ($expected['data']['items'] ?? []) : [];
    $expected_count = (int) ($expected['success'] ? count($expected_items) : -1);
    check('applied count matches the specialization-scoped pending service count', count($applied_items) === $expected_count);
}

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);