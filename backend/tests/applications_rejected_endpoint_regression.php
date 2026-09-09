<?php

/**
 * MASAR - Applications Rejected Endpoint Regression Test
 *
 * Verifies the dedicated GET /api/v1/applications/rejected endpoint:
 *
 *   Case 1  Guest (no credentials)  -> 401 Unauthorized
 *   Case 2  Company token           -> 403 'Student access required.'
 *   Case 3  Valid student token     -> 200 OK, ONLY rejected apps
 *           scoped to the student's own specialization
 *   Case 4  Exact Rejected Card DTO contract: every item has exactly the
 *           12 fields (no more, no fewer)
 *   Case 5  Forbidden raw/PII fields are absent
 *   Case 6  Every item status === "Rejected" (never the DB value 'rejected')
 *   Case 7  Every training specialization matches the student's specialization
 *   Case 8  rejected_at === reviewed_at (ISO-8601, exact after normalization)
 *   Case 9  Newest rejection appears first (reviewed_at DESC)
 *   Case 10 Pagination values are correct
 *
 * Run from the backend root:
 *     php tests/applications_rejected_endpoint_regression.php
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

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/applications/rejected'): array
{
    $case_file = dirname(__FILE__) . '/applications_rejected_endpoint_case.php';
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
}

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_scenario('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2: company token on student endpoint ==\n";

$company_case = run_scenario('bearer', $company_token ?? '');
check('company token returns HTTP 403', $company_case['status'] === 403);
check('company token body mentions student access', stripos($company_case['body'], 'student access') !== false);

echo "\n== Case 3: valid student token -> /applications/rejected ==\n";

$rejected = run_scenario('bearer', $valid_token ?? '');
check('rejected returns HTTP 200', $rejected['status'] === 200);
check('rejected returns success=true', $rejected['success'] === true);

$rejected_data = is_array($rejected['data']) ? $rejected['data'] : [];
check('rejected data is an array', is_array($rejected_data));

$pagination_keys = ['current_page', 'per_page', 'total', 'total_pages', 'has_next_page', 'has_previous_page'];
$pagination_ok = true;
if (!isset($rejected_data['pagination']) || !is_array($rejected_data['pagination'])) {
    $pagination_ok = false;
} else {
    foreach ($pagination_keys as $key) {
        if (!array_key_exists($key, $rejected_data['pagination'])) {
            $pagination_ok = false;
        }
    }
}
check('rejected pagination envelope is identical to Applied/Accepted (key set)', $pagination_ok);

$rejected_items = items($rejected);

// Pure DTO invariant: even an empty raw row shapes into exactly the 12 keys.
$empty_dto = application_rejected_card([
    'id' => null, 'training_id' => null, 'training_title' => null,
    'specialization_name' => null, 'company_name' => null, 'company_logo' => null,
    'training_type' => null, 'mode' => null, 'reviewed_at' => null,
    'rejection_reason' => null, 'rejection_note' => null,
]);
check('rejected card shapes an empty row into exactly the 12 DTO keys', is_array($empty_dto) && count($empty_dto) === 12);

$required_keys = [
    'id', 'training_id', 'training_title', 'status', 'specialization',
    'company_name', 'company_logo', 'training_type', 'method',
    'rejected_at', 'rejection_reason', 'rejection_note',
];
$forbidden_keys = [
    'student_id', 'company_id', 'message', 'full_name', 'email', 'phone',
    'city', 'address', 'why_interested', 'what_to_learn', 'skills',
    'reviewed_by', 'cv_file_id', 'faculty_id', 'university', 'applicant_type',
    'academic_year', 'graduation_year', 'motivation', 'location',
    'starts_at', 'ends_at', 'withdrawn_at', 'reviewed_at', 'applied_at',
    'status_db', 'can_withdraw', 'is_paid', 'may_lead_to_hire',
    'payment_status', 'bank_account', 'free_trial_days', 'free_trial_days_remaining',
    'duration', 'accepted_at', 'motivational_message',
];
$iso_regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)$/';

$student_spec_id = (int) ($student['specialization_id'] ?? 0);
$student_spec_name = '';
if ($student_spec_id > 0) {
    $spec_row = db_fetch_one(
        "SELECT name FROM specializations WHERE id = ? LIMIT 1",
        [$student_spec_id]
    );
    $student_spec_name = is_array($spec_row) ? (string) $spec_row['name'] : '';
}

$expected_order_ids = [];
if (is_array($student) && $student_spec_id > 0) {
    $db_scoped_rows = db_fetch_all(
        "SELECT a.id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE
             a.student_id = ?
             AND a.status = 'rejected'
             AND t.specialization_id = ?
         ORDER BY a.reviewed_at DESC, a.applied_at DESC",
        [(int) $student['student_id'], $student_spec_id]
    );
    foreach (is_array($db_scoped_rows) ? $db_scoped_rows : [] as $ar) {
        $expected_order_ids[] = (int) $ar['id'];
    }
}
$expected_scoped_total = count($expected_order_ids);

$db_rejected_count = 0;
$db_rejected_other_spec = 0;
if (is_array($student)) {
    $all_rejected_rows = db_fetch_all(
        "SELECT a.id, a.training_id, a.status,
                IFNULL(t.specialization_id, 0) AS specialization_id
         FROM training_applications a
         LEFT JOIN training_listings t ON t.id = a.training_id
         WHERE a.student_id = ? AND a.status = 'rejected'",
        [(int) $student['student_id']]
    );
    foreach (is_array($all_rejected_rows) ? $all_rejected_rows : [] as $ar) {
        $db_rejected_count++;
        if ((int) ($ar['specialization_id'] ?? 0) !== $student_spec_id) {
            $db_rejected_other_spec++;
        }
    }
}
check('student has at least one rejected application to scope', $db_rejected_count >= 1);
check('student has at least one rejected application in their specialization', $expected_scoped_total >= 1);
check('student has at least one rejected application OUTSIDE their specialization (scoping is meaningful)', $db_rejected_other_spec >= 1);

echo "\n== Case 4-10: Rejected Card DTO contract ==\n";

$key_ok = true;          // exactly the 12 required keys, nothing else
$forbidden_present = [];
$status_ok = true;
$spec_matches_student = true;
$reviewed_at_ok = true;  // rejected_at == ISO(reviewed_at) from DB
$reason_ok = true;       // rejection_reason present (non-null for real rows)
$note_nullable_ok = true;
$title_ok = true;
$company_ok = true;
$returned_ids_in_order = [];
$returned_timestamps = [];

foreach ($rejected_items as $item) {
    if (!is_array($item)) {
        $key_ok = false;
        continue;
    }

    $item_keys = array_keys($item);
    sort($item_keys);
    $expected_keys = $required_keys;
    sort($expected_keys);
    if ($item_keys !== $expected_keys) {
        $key_ok = false;
    }

    foreach ($required_keys as $key) {
        if (!array_key_exists($key, $item)) {
            $key_ok = false;
        }
    }

    foreach ($forbidden_keys as $key) {
        if (array_key_exists($key, $item)) {
            $forbidden_present[] = $key;
        }
    }

    if (($item['status'] ?? '') !== 'Rejected') {
        $status_ok = false;
    }

    $training_id = (int) ($item['training_id'] ?? 0);
    $returned_ids_in_order[] = (int) ($item['id'] ?? 0);

    $row = db_fetch_one(
        "SELECT
             a.reviewed_at,
             a.rejection_reason,
             a.rejection_note,
             t.title AS training_title,
             t.specialization_id,
             s.name  AS specialization_name,
             c.legal_name AS company_name
         FROM training_applications a
         LEFT JOIN training_listings t ON t.id = a.training_id
         LEFT JOIN specializations s ON s.id = t.specialization_id
         LEFT JOIN companies c ON c.id = t.company_id
         WHERE a.id = ?
         LIMIT 1",
        [(int) ($item['id'] ?? 0)]
    );

    if (is_array($row)) {
        $expected_rejected_at = application_iso8601($row['reviewed_at'] ?? null);
        if (($item['rejected_at'] ?? 'x') !== $expected_rejected_at) {
            $reviewed_at_ok = false;
        }
        if ((string) ($item['training_title'] ?? '') !== (string) ($row['training_title'] ?? '')) {
            $title_ok = false;
        }
        if ((string) ($item['company_name'] ?? '') !== (string) ($row['company_name'] ?? '')) {
            $company_ok = false;
        }
        if ((int) ($row['specialization_id'] ?? 0) !== $student_spec_id) {
            $spec_matches_student = false;
        }
        $row_reason = (isset($row['rejection_reason']) && $row['rejection_reason'] !== null)
            ? (string) $row['rejection_reason'] : null;
        if ((($item['rejection_reason'] ?? 'x') === null || (string) ($item['rejection_reason'] ?? '') !== (string) $row_reason)) {
            $reason_ok = false;
        }
        if ($row['rejection_reason'] === null && ($item['rejection_reason'] ?? 'x') !== null) {
            $reason_ok = false;
        }
        $expected_note = ($row['rejection_note'] !== null && trim((string) $row['rejection_note']) !== '')
            ? (string) $row['rejection_note'] : null;
        if (($item['rejection_note'] ?? 'x') !== $expected_note) {
            $note_nullable_ok = false;
        }
    } else {
        $title_ok = false;
    }

    $db_spec_row = db_fetch_one(
        "SELECT t.specialization_id FROM training_listings t WHERE t.id = ? LIMIT 1",
        [$training_id]
    );
    if (
        !is_array($db_spec_row)
        ||
        (int) ($db_spec_row['specialization_id'] ?? 0) !== $student_spec_id
    ) {
        $spec_matches_student = false;
    }

    if (($item['rejected_at'] ?? null) !== null) {
        if (!preg_match($iso_regex, (string) $item['rejected_at'])) {
            $reviewed_at_ok = false;
        }
        $returned_timestamps[] = (string) $item['rejected_at'];
    } else {
        $reviewed_at_ok = false;
    }

    if (!array_key_exists('rejection_reason', $item)) {
        $reason_ok = false;
    }
    if (($item['rejection_note'] ?? 'x') !== null && !is_string($item['rejection_note'])) {
        $note_nullable_ok = false;
    }
}

check('rejected only exposes exactly the 12 required DTO keys', $key_ok);
check('rejected excludes all forbidden PII/raw keys', $forbidden_present === []);
check('rejected item status equals "Rejected" (never the DB value rejected)', $status_ok);
check('rejected every training specialization matches student specialization', $spec_matches_student);
check('rejected_at exists on every item', count($returned_timestamps) === count($rejected_items));
check('rejected_at is ISO-8601 with timezone offset', clean_regex_check($returned_timestamps, $iso_regex));
check('rejected_at equals reviewed_at (application_iso8601(reviewed_at))', $reviewed_at_ok);
check('rejected training_title matches DB', $title_ok);
check('rejected company_name matches DB', $company_ok);
check('rejection_reason present on every item', $reason_ok);
check('rejection_note is null or a non-empty string', $note_nullable_ok);

echo "\n== Case 9: newest rejection first ==\n";

$order_ok = $returned_ids_in_order === $expected_order_ids;
check('returned order matches DB reviewed_at DESC (+ applied_at DESC tie-break)', $order_ok);

$chronological_ok = true;
for ($i = 1; $i < count($returned_timestamps); $i++) {
    if ($returned_timestamps[$i] > $returned_timestamps[$i - 1]) {
        $chronological_ok = false;
    }
}
check('rejection timestamps are non-increasing (newest first)', $chronological_ok);

if (!empty($expected_order_ids)) {
    $newest_id = $expected_order_ids[0];
    $first_id = $returned_ids_in_order[0] ?? 0;
    check('the first returned item is the newest rejected application', $first_id === $newest_id);
}

echo "\n== Case 10: pagination values ==\n";

$pagination = is_array($rejected_data['pagination'] ?? null) ? $rejected_data['pagination'] : [];
$p_total = (int) ($pagination['total'] ?? -1);
$p_page = (int) ($pagination['current_page'] ?? 0);
$p_per = (int) ($pagination['per_page'] ?? 0);
$p_pages = (int) ($pagination['total_pages'] ?? -1);
$p_has_next = (bool) ($pagination['has_next_page'] ?? null);
$p_has_prev = (bool) ($pagination['has_previous_page'] ?? null);

check('pagination total equals the specialization-filtered rejected count', $p_total === $expected_scoped_total);
check('rejected items count matches min(total, per_page)', count($rejected_items) === min($expected_scoped_total, 20));
check('pagination current_page is 1', $p_page === 1);
check('pagination per_page is 20', $p_per === 20);
check('pagination total_pages equals ceil(total/per_page)', $p_pages === (int) ceil($expected_scoped_total / 20));
check('pagination has_next_page is false on the last page', $p_has_next === false);
check('pagination has_previous_page is false on page 1', $p_has_prev === false);

$ret_training_ids = array_map(static fn ($it) => (int) ($it['training_id'] ?? 0), $rejected_items);
$scoped_training_ids = [];
if (is_array($student) && $student_spec_id > 0) {
    $st_rows = db_fetch_all(
        "SELECT a.training_id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE a.student_id = ? AND a.status = 'rejected' AND t.specialization_id = ?",
        [(int) $student['student_id'], $student_spec_id]
    );
    foreach (is_array($st_rows) ? $st_rows : [] as $sr) {
        $scoped_training_ids[] = (int) $sr['training_id'];
    }
}
sort($scoped_training_ids);
sort($ret_training_ids);
check('rejected covers exactly the rejected trainings matching the student specialization', $scoped_training_ids === $ret_training_ids);

if (is_array($student)) {
    $expected = application_service_list_rejected(
        (int) $student['user_id'],
        ['page' => 1, 'limit' => 100, 'scope_to_specialization' => true]
    );
    $expected_items = is_array($expected['data'] ?? null) ? ($expected['data']['items'] ?? []) : [];
    $expected_count = (int) ($expected['success'] ? count($expected_items) : -1);
    check('rejected count matches the specialization-scoped list_rejected service count', count($rejected_items) === $expected_count);
}

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);

function clean_regex_check(array $values, string $regex): bool
{
    foreach ($values as $value) {
        if (!preg_match($regex, (string) $value)) {
            return false;
        }
    }
    return true;
}