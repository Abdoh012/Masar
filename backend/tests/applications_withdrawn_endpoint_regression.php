<?php

/**
 * MASAR - Applications Withdrawn Endpoint Regression Test
 *
 * Verifies the dedicated GET /api/v1/applications/withdrawn endpoint:
 *
 *   Case 1  Guest (no credentials)  -> 401 Unauthorized
 *   Case 2  Company token           -> 403 'Student access required.'
 *   Case 3  Valid student token     -> 200 OK, ONLY withdrawn apps
 *           scoped to the student's own specialization
 *   Case 4  Exact Withdrawn Card DTO contract: every item has exactly the
 *           12 fields (no more, no fewer)
 *   Case 5  Forbidden raw/PII/internal fields are absent
 *   Case 6  Every item status === "Withdrawn" (never the DB value 'withdrawn')
 *   Case 7  withdrawn_at comes from applications.withdrawn_at (ISO-8601,
 *           exact after normalization) and never from applied_at/reviewed_at
 *   Case 8  training_title matches the DB
 *   Case 9  specialization matches the student specialization
 *   Case 10 company_name matches the DB
 *   Case 11 company_logo follows the existing company-logo architecture
 *   Case 12 training_type exists
 *   Case 13 method exists
 *   Case 14 starts_at / ends_at exist (ISO-8601 from the training listing)
 *   Case 15 Newest withdrawn first (withdrawn_at DESC, applied_at DESC)
 *   Case 16 Specialization filtering happens at the SQL/repository level:
 *           a withdrawn application for a training in a DIFFERENT
 *           specialization is never returned
 *   Case 17 Pagination values are correct
 *   Case 18 Other student endpoints still work: /applied, /accepted,
 *           /rejected and /applications/{id}
 *
 * A temporary withdrawn application IN the student's specialization (with a
 * distinct, newer withdrawn_at) and one OUTSIDE the specialization are seeded
 * through the real repository and removed at the end, so ordering and
 * SQL-level scoping are provable even when real data is sparse.
 *
 * Run from the backend root:
 *     php tests/applications_withdrawn_endpoint_regression.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/core/database/query.php';
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

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/applications/withdrawn'): array
{
    $case_file = dirname(__FILE__) . '/applications_withdrawn_endpoint_case.php';
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

/** Fresh training (not yet applied to by the student) with an optional spec. */
function pick_fresh_training(int $sid, int $spec_id = 0, bool $exclude_spec = false): int
{
    $spec_clause = '';
    if ($spec_id > 0 && !$exclude_spec) {
        $spec_clause = " AND t.specialization_id = " . (int) $spec_id;
    }
    if ($exclude_spec) {
        $spec_clause = " AND IFNULL(t.specialization_id, 0) <> " . (int) $spec_id;
    }

    $t = db_fetch_one(
        "
        SELECT t.id
        FROM training_listings t
        LEFT JOIN training_applications a
               ON a.training_id = t.id AND a.student_id = ?
        WHERE a.id IS NULL
          AND t.status IN ('published', 'open', 'active')
          AND (t.application_deadline IS NULL OR t.application_deadline > NOW())
          {$spec_clause}
        ORDER BY t.id
        LIMIT 1
        ",
        [$sid]
    );
    return $t ? (int) $t['id'] : 0;
}

$created_ids = [];

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
    $sid     = (int) $student['student_id'];

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

$student_spec_id = (int) ($student['specialization_id'] ?? 0);
$student_spec_name = '';
if ($student_spec_id > 0) {
    $spec_row = db_fetch_one(
        "SELECT name FROM specializations WHERE id = ? LIMIT 1",
        [$student_spec_id]
    );
    $student_spec_name = is_array($spec_row) ? (string) $spec_row['name'] : '';
}
check('student specialization resolved', $student_spec_id > 0 && $student_spec_name !== '');

/*
|--------------------------------------------------------------------------
| Seed fixtures through the real repository
|--------------------------------------------------------------------------
*/

$fixture_in_spec_id = 0;
$fixture_out_spec_id = 0;

if (is_array($student) && $student_spec_id > 0) {
    $in_training = pick_fresh_training($sid, $student_spec_id);
    $out_training = pick_fresh_training($sid, $student_spec_id, true);

    check('fresh in-spec training available for fixture', $in_training > 0);
    check('fresh out-of-spec training available for fixture', $out_training > 0);

    if ($in_training > 0 && $out_training > 0) {
        foreach ([
            'in'  => $in_training,
            'out' => $out_training,
        ] as $which => $training_id) {
            $training = db_fetch_one(
                "SELECT company_id FROM training_listings WHERE id = ? LIMIT 1",
                [$training_id]
            );
            $created = application_repository_create([
                'training_id'    => $training_id,
                'student_id'     => $sid,
                'company_id'     => is_array($training) ? ($training['company_id'] ?? null) : null,
                'message'        => 'Withdrawn endpoint regression fixture (' . $which . ').',
                'full_name'      => 'Test Student',
                'email'          => 'test.student@example.com',
                'phone'          => '01000000000',
                'city'           => 'Cairo',
                'address'        => 'Cairo',
                'why_interested' => 'Regression fixture.',
                'what_to_learn'  => 'Regression fixture.',
                'skills'         => 'PHP, SQL',
                'applicant_type' => 'student',
                'academic_year'  => '3rd',
                'status'         => 'withdrawn',
            ]);

            if ($created !== null && $created > 0) {
                $created_ids[] = $created;
                db_execute(
                    "UPDATE training_applications SET withdrawn_at = ? WHERE id = ?",
                    ['2026-09-09 05:00:00', $created]
                );
                if ($which === 'in') {
                    $fixture_in_spec_id = $created;
                } else {
                    $fixture_out_spec_id = $created;
                }
            }
        }
    }
}

check('in-spec withdrawn fixture seeded', $fixture_in_spec_id > 0);
check('out-of-spec withdrawn fixture seeded', $fixture_out_spec_id > 0);

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_scenario('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2: company token on student endpoint ==\n";

$company_case = run_scenario('bearer', $company_token ?? '');
check('company token returns HTTP 403', $company_case['status'] === 403);
check('company token body mentions student access', stripos($company_case['body'], 'student access') !== false);

echo "\n== Case 3: valid student token -> /applications/withdrawn ==\n";

$withdrawn = run_scenario('bearer', $valid_token ?? '');
check('withdrawn returns HTTP 200', $withdrawn['status'] === 200);
check('withdrawn returns success=true', $withdrawn['success'] === true);

$withdrawn_data = is_array($withdrawn['data']) ? $withdrawn['data'] : [];
check('withdrawn data is an array', is_array($withdrawn_data));

$pagination_keys = ['current_page', 'per_page', 'total', 'total_pages', 'has_next_page', 'has_previous_page'];
$pagination_ok = true;
if (!isset($withdrawn_data['pagination']) || !is_array($withdrawn_data['pagination'])) {
    $pagination_ok = false;
} else {
    foreach ($pagination_keys as $key) {
        if (!array_key_exists($key, $withdrawn_data['pagination'])) {
            $pagination_ok = false;
        }
    }
}
check('withdrawn pagination envelope is identical to Applied/Accepted/Rejected (key set)', $pagination_ok);

$withdrawn_items = items($withdrawn);

// Pure DTO invariant: even an empty raw row shapes into exactly the 12 keys.
$empty_dto = application_withdrawn_card([
    'id' => null, 'training_id' => null, 'training_title' => null,
    'specialization_name' => null, 'company_name' => null, 'company_logo' => null,
    'training_type' => null, 'mode' => null, 'withdrawn_at' => null,
    'starts_at' => null, 'ends_at' => null,
]);
check('withdrawn card shapes an empty row into exactly the 12 DTO keys', is_array($empty_dto) && count($empty_dto) === 12);

$required_keys = [
    'id', 'training_id', 'training_title', 'status', 'specialization',
    'company_name', 'company_logo', 'training_type', 'method',
    'withdrawn_at', 'starts_at', 'ends_at',
];
$forbidden_keys = [
    'student_id', 'company_id', 'message', 'full_name', 'email', 'phone',
    'city', 'address', 'why_interested', 'what_to_learn', 'skills',
    'reviewed_by', 'cv_file_id', 'faculty_id', 'university', 'applicant_type',
    'academic_year', 'graduation_year', 'motivation', 'location',
    'applied_at', 'reviewed_at', 'rejected_at', 'rejection_reason', 'rejection_note',
    'status_db', 'can_withdraw', 'is_paid', 'may_lead_to_hire',
    'payment_status', 'bank_account', 'free_trial_days', 'free_trial_days_remaining',
    'duration', 'accepted_at', 'motivational_message',
];
$iso_regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)$/';

/*
| Expected DB order (with the in-spec fixture newest). Same ORDER BY the
| repository uses: withdrawn_at DESC, applied_at DESC.
*/

$expected_order_ids = [];
if (is_array($student) && $student_spec_id > 0) {
    $db_scoped_rows = db_fetch_all(
        "SELECT a.id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE
             a.student_id = ?
             AND a.status = 'withdrawn'
             AND t.specialization_id = ?
         ORDER BY a.withdrawn_at DESC, a.applied_at DESC",
        [$sid, $student_spec_id]
    );
    foreach (is_array($db_scoped_rows) ? $db_scoped_rows : [] as $ar) {
        $expected_order_ids[] = (int) $ar['id'];
    }
}
$expected_scoped_total = count($expected_order_ids);

$db_withdrawn_count = 0;
$db_withdrawn_other_spec = 0;
if (is_array($student)) {
    $all_withdrawn_rows = db_fetch_all(
        "SELECT a.id, a.training_id, a.status,
                IFNULL(t.specialization_id, 0) AS specialization_id
         FROM training_applications a
         LEFT JOIN training_listings t ON t.id = a.training_id
         WHERE a.student_id = ? AND a.status = 'withdrawn'",
        [$sid]
    );
    foreach (is_array($all_withdrawn_rows) ? $all_withdrawn_rows : [] as $ar) {
        $db_withdrawn_count++;
        if ((int) ($ar['specialization_id'] ?? 0) !== $student_spec_id) {
            $db_withdrawn_other_spec++;
        }
    }
}
check('student has at least one withdrawn application to scope', $db_withdrawn_count >= 1);
check('student has at least one withdrawn application in their specialization', $expected_scoped_total >= 1);
check('student has at least one withdrawn application OUTSIDE their specialization (scoping is meaningful)', $db_withdrawn_other_spec >= 1);

echo "\n== Cases 4-14: Withdrawn Card DTO contract ==\n";

$key_ok = true;            // exactly the 12 required keys, nothing else
$forbidden_present = [];
$status_ok = true;
$spec_matches_student = true;
$withdrawn_at_ok = true;   // withdrawn_at == ISO(withdrawn_at) from DB
$title_ok = true;
$company_ok = true;
$logo_ok = true;
$training_type_ok = true;
$method_ok = true;
$starts_ends_ok = true;
$returned_ids_in_order = [];
$returned_timestamps = [];

foreach ($withdrawn_items as $item) {
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

    if (($item['status'] ?? '') !== 'Withdrawn') {
        $status_ok = false;
    }

    $returned_ids_in_order[] = (int) ($item['id'] ?? 0);

    $row = db_fetch_one(
        "SELECT
             a.withdrawn_at,
             a.applied_at,
             a.reviewed_at,
             t.title AS training_title,
             t.training_type,
             t.mode,
             t.starts_at,
             t.ends_at,
             t.specialization_id,
             s.name  AS specialization_name,
             c.legal_name AS company_name,
             c.company_logo AS company_logo
         FROM training_applications a
         LEFT JOIN training_listings t ON t.id = a.training_id
         LEFT JOIN specializations s ON s.id = t.specialization_id
         LEFT JOIN companies c ON c.id = t.company_id
         WHERE a.id = ?
         LIMIT 1",
        [(int) ($item['id'] ?? 0)]
    );

    if (is_array($row)) {
        $expected_withdrawn_at = application_iso8601($row['withdrawn_at'] ?? null);
        if (($item['withdrawn_at'] ?? 'x') !== $expected_withdrawn_at) {
            $withdrawn_at_ok = false;
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
        if (($item['specialization'] ?? '') !== (string) ($row['specialization_name'] ?? '')) {
            $spec_matches_student = false;
        }
        if ((string) ($item['training_type'] ?? '') !== (string) ($row['training_type'] ?? '')) {
            $training_type_ok = false;
        }
        if ((string) ($item['method'] ?? '') !== (string) ($row['mode'] ?? '')) {
            $method_ok = false;
        }
        $expected_starts = application_iso8601($row['starts_at'] ?? null);
        $expected_ends = application_iso8601($row['ends_at'] ?? null);
        if (($item['starts_at'] ?? 'x') !== $expected_starts || ($item['ends_at'] ?? 'x') !== $expected_ends) {
            $starts_ends_ok = false;
        }
        if (array_key_exists('company_logo', $item) && $item['company_logo'] !== $row['company_logo']) {
            $logo_ok = false;
        }
    } else {
        $title_ok = false;
    }

    if (($item['withdrawn_at'] ?? null) !== null) {
        if (!preg_match($iso_regex, (string) $item['withdrawn_at'])) {
            $withdrawn_at_ok = false;
        }
        $returned_timestamps[] = (string) $item['withdrawn_at'];
    } else {
        $withdrawn_at_ok = false;
    }
}

check('withdrawn only exposes exactly the 12 required DTO keys', $key_ok);
check('withdrawn excludes all forbidden PII/raw/internal keys', $forbidden_present === []);
check('withdrawn item status equals "Withdrawn" (never the DB value withdrawn)', $status_ok);
check('withdrawn every training specialization matches student specialization (name too)', $spec_matches_student);
check('withdrawn_at exists on every item', count($returned_timestamps) === count($withdrawn_items));
check('withdrawn_at is ISO-8601 with timezone offset', clean_regex_check($returned_timestamps, $iso_regex));
check('withdrawn_at equals applications.withdrawn_at (application_iso8601(withdrawn_at))', $withdrawn_at_ok);
check('withdrawn training_title matches DB', $title_ok);
check('withdrawn company_name matches DB', $company_ok);
check('withdrawn company_logo matches the existing company-logo source', $logo_ok);
check('withdrawn training_type matches DB', $training_type_ok);
check('withdrawn method matches DB', $method_ok);
check('withdrawn starts_at/ends_at equal the training listing timestamps (ISO-8601)', $starts_ends_ok);

echo "\n== Case 15: newest withdrawn first ==\n";

$order_ok = $returned_ids_in_order === $expected_order_ids;
check('returned order matches DB withdrawn_at DESC (+ applied_at DESC tie-break)', $order_ok);

$chronological_ok = true;
for ($i = 1; $i < count($returned_timestamps); $i++) {
    if ($returned_timestamps[$i] > $returned_timestamps[$i - 1]) {
        $chronological_ok = false;
    }
}
check('withdrawn_at timestamps are non-increasing (newest first)', $chronological_ok);

if (!empty($expected_order_ids)) {
    $newest_id = $expected_order_ids[0];
    $first_id = $returned_ids_in_order[0] ?? 0;
    check('the first returned item is the newest withdrawn application (seeded fixture)', $newest_id === $fixture_in_spec_id && $first_id === $fixture_in_spec_id);
}

echo "\n== Case 16: specialization filtering at the SQL/repository level ==\n";

$returned_ids = $returned_ids_in_order;
check('out-of-specification withdrawn fixture is NEVER returned', !in_array($fixture_out_spec_id, $returned_ids, true));

$fixture_out_found = (bool) db_fetch_one(
    "SELECT id FROM training_applications WHERE id = ? AND status = 'withdrawn' LIMIT 1",
    [$fixture_out_spec_id]
);
check('out-of-spec fixture exists in the DB as withdrawn (proof filtering is real)', $fixture_out_found);

$ret_training_ids = array_map(static fn ($it) => (int) ($it['training_id'] ?? 0), $withdrawn_items);
$scoped_training_ids = [];
if (is_array($student) && $student_spec_id > 0) {
    $st_rows = db_fetch_all(
        "SELECT a.training_id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE a.student_id = ? AND a.status = 'withdrawn' AND t.specialization_id = ?",
        [$sid, $student_spec_id]
    );
    foreach (is_array($st_rows) ? $st_rows : [] as $sr) {
        $scoped_training_ids[] = (int) $sr['training_id'];
    }
}
sort($scoped_training_ids);
sort($ret_training_ids);
check('withdrawn covers exactly the withdrawn trainings matching the student specialization', $scoped_training_ids === $ret_training_ids);

echo "\n== Case 17: pagination values ==\n";

$pagination = is_array($withdrawn_data['pagination'] ?? null) ? $withdrawn_data['pagination'] : [];
$p_total = (int) ($pagination['total'] ?? -1);
$p_page = (int) ($pagination['current_page'] ?? 0);
$p_per = (int) ($pagination['per_page'] ?? 0);
$p_pages = (int) ($pagination['total_pages'] ?? -1);
$p_has_next = (bool) ($pagination['has_next_page'] ?? null);
$p_has_prev = (bool) ($pagination['has_previous_page'] ?? null);

check('pagination total equals the specialization-filtered withdrawn count', $p_total === $expected_scoped_total);
check('withdrawn items count matches min(total, per_page)', count($withdrawn_items) === min($expected_scoped_total, 20));
check('pagination current_page is 1', $p_page === 1);
check('pagination per_page is 20', $p_per === 20);
check('pagination total_pages equals ceil(total/per_page)', $p_pages === (int) ceil($expected_scoped_total / 20));
check('pagination has_next_page is false on the last page', $p_has_next === false);
check('pagination has_previous_page is false on page 1', $p_has_prev === false);

if (is_array($student)) {
    $expected = application_service_list_withdrawn(
        (int) $student['user_id'],
        ['page' => 1, 'limit' => 100, 'scope_to_specialization' => true]
    );
    $expected_items = is_array($expected['data'] ?? null) ? ($expected['data']['items'] ?? []) : [];
    $expected_count = (int) ($expected['success'] ? count($expected_items) : -1);
    check('withdrawn count matches the specialization-scoped list_withdrawn service count', count($withdrawn_items) === $expected_count);

    $page2 = application_service_list_withdrawn(
        (int) $student['user_id'],
        ['page' => 2, 'limit' => 1, 'scope_to_specialization' => true]
    );
    $p2 = is_array($page2['data']['pagination'] ?? null) ? $page2['data']['pagination'] : [];
    check('service pagination page=2&limit=1 returns offset row', (int) ($p2['current_page'] ?? 0) === 2 && (int) ($p2['per_page'] ?? 0) === 1);
    check('service pagination total stays the same across pages', (int) ($p2['total'] ?? -1) === $p_total);
}

echo "\n== Case 18: other application endpoints still work ==\n";

$applied = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/applied');
check('applied endpoint still works (HTTP 200)', $applied['status'] === 200);

$accepted = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/accepted');
check('accepted endpoint still works (HTTP 200)', $accepted['status'] === 200);

$rejected = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/rejected');
check('rejected endpoint still works (HTTP 200)', $rejected['status'] === 200);

$detail_id = $fixture_in_spec_id > 0 ? $fixture_in_spec_id : 1884;
$detail = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/' . $detail_id);
check("GET /applications/{$detail_id} still works (HTTP 200)", $detail['status'] === 200);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";

// ---- Cleanup seeded fixtures ----
if (!empty($created_ids)) {
    $sql = 'DELETE FROM training_applications WHERE id IN (' . implode(',', array_fill(0, count($created_ids), '?')) . ')';
    $del = db_execute($sql, $created_ids);
    echo "cleanup: deleted " . $del->rowCount() . " test application(s)\n";
}

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