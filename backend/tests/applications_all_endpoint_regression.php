<?php

/**
 * MASAR - Applications All Endpoint Regression Test
 *
 * Verifies the unified GET /api/v1/applications/all endpoint that returns
 * the student's complete application set across the four tab states:
 *
 *   Case 1   Guest (no credentials)     -> 401 Unauthorized
 *   Case 2   Company token              -> 403 'Student access required.'
 *   Case 3   Valid student token        -> 200 OK, success=true
 *   Case 4   Pagination envelope matches Applied/Accepted/Rejected/Withdrawn
 *   Case 5   Exactly the four tab statuses are present and each maps to its
 *            DB value (submitted->Applied, accepted->Accepted,
 *            rejected->Rejected, withdrawn->Withdrawn)
 *   Case 6   Every item exposes the exact per-status card DTO key set (no
 *            more, no fewer): Applied 16 keys, Accepted 20 keys, Rejected
 *            12 keys, Withdrawn 12 keys
 *   Case 7   All items share the common card keys (id, training_id, ...)
 *   Case 8   Forbidden raw/PII/internal fields are absent from every item
 *   Case 9   Status-specific fields are present and correct per status
 *            (applied_at / accepted_at / rejected_at / withdrawn_at,
 *            free_trial_*, payment_status, bank_account, can_withdraw,
 *            rejection_* ...) and never leak into other statuses' cards
 *   Case 10  training_title / company_name / specialization / training_type
 *            / method match the DB, and specialization always equals the
 *            student specialization
 *   Case 11  Ordering is deterministic newest-first across ALL statuses:
 *            GREATEST(withdrawn_at, reviewed_at, applied_at) DESC, id DESC
 *   Case 12  SQL/repository-level scoping: out-of-specialization fixtures
 *            of ANY status are never returned
 *   Case 13  Pagination: total equals the scoped 4-status count; page=2 &
 *            limit=1 gives a different item than page=1 (no duplicates)
 *   Case 14  Other student endpoints still work: /withdrawn, /applied,
 *            /accepted, /rejected and /applications/{id}
 *
 * Fixtures through the real repository: one in-specialization application
 * for each of the four states (each with a distinct, newer activity
 * timestamp therefore a deterministic top-of-list order) plus one
 * out-of-specialization rejected application proving SQL-level exclusion.
 *
 * Run from the backend root:
 *     php tests/applications_all_endpoint_regression.php
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

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/applications/all'): array
{
    $case_file = dirname(__FILE__) . '/applications_all_endpoint_case.php';
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

/*
 * One in-spec fixture per tab state, each with a distinct newer activity
 * timestamp so the All ordering is provable and deterministic:
 *   submitted -> applied_at = 2026-09-09 05:00:00  (Applied card)
 *   accepted  -> reviewed_at = 2026-09-09 05:01:00 (Accepted card)
 *   rejected  -> reviewed_at = 2026-09-09 05:02:00 (Rejected card)
 *   withdrawn -> withdrawn_at = 2026-09-09 05:03:00 (Withdrawn card)
 * Plus one out-of-spec rejected fixture (excluded at the SQL level).
 */

$seed_plans = [
    'submitted' => ['applied_at'   => '2026-09-09 05:00:00'],
    'accepted'  => ['reviewed_at'  => '2026-09-09 05:01:00'],
    'rejected'  => ['reviewed_at'  => '2026-09-09 05:02:00'],
    'withdrawn' => ['withdrawn_at' => '2026-09-09 05:03:00'],
];

$fixture_in_spec_by_status = [];
$fixture_out_spec_id = 0;

if (is_array($student) && $student_spec_id > 0) {
    foreach ($seed_plans as $status => $timestamp_update) {
        $training_id = pick_fresh_training($sid, $student_spec_id);

        if ($training_id > 0) {
            $training = db_fetch_one(
                "SELECT company_id FROM training_listings WHERE id = ? LIMIT 1",
                [$training_id]
            );

            $created = application_repository_create([
                'training_id'    => $training_id,
                'student_id'     => $sid,
                'company_id'     => is_array($training) ? ($training['company_id'] ?? null) : null,
                'message'        => 'All endpoint regression fixture (' . $status . ').',
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
                'status'         => $status,
            ]);

            if ($created !== null && $created > 0) {
                $created_ids[] = $created;
                $set = $status === 'accepted' || $status === 'rejected'
                    ? 'reviewed_at'
                    : ($status === 'withdrawn' ? 'withdrawn_at' : 'applied_at');
                $ts = $status === 'accepted' || $status === 'rejected'
                    ? '2026-09-09 05:0' . ($status === 'accepted' ? '1' : '2') . ':00'
                    : ($status === 'withdrawn' ? '2026-09-09 05:03:00' : '2026-09-09 05:00:00');

                db_execute(
                    "UPDATE training_applications SET {$set} = ? WHERE id = ?",
                    [$ts, $created]
                );

                $fixture_in_spec_by_status[$status] = $created;
            }
        }
    }

    $out_training = pick_fresh_training($sid, $student_spec_id, true);
    if ($out_training > 0) {
        $training = db_fetch_one(
            "SELECT company_id FROM training_listings WHERE id = ? LIMIT 1",
            [$out_training]
        );
        $created = application_repository_create([
            'training_id'    => $out_training,
            'student_id'     => $sid,
            'company_id'     => is_array($training) ? ($training['company_id'] ?? null) : null,
            'message'        => 'All endpoint regression fixture (out-of-spec).',
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
            'status'         => 'rejected',
        ]);
        if ($created !== null && $created > 0) {
            $created_ids[] = $created;
            db_execute(
                "UPDATE training_applications SET reviewed_at = ? WHERE id = ?",
                ['2026-09-09 05:04:00', $created]
            );
            $fixture_out_spec_id = $created;
        }
    }
}

foreach ($seed_plans as $status => $_) {
    check("in-spec {$status} fixture seeded", ($fixture_in_spec_by_status[$status] ?? 0) > 0);
}
check('out-of-spec rejected fixture seeded', $fixture_out_spec_id > 0);

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_scenario('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2: company token on student endpoint ==\n";

$company_case = run_scenario('bearer', $company_token ?? '');
check('company token returns HTTP 403', $company_case['status'] === 403);
check('company token body mentions student access', stripos($company_case['body'], 'student access') !== false);

echo "\n== Case 3: valid student token -> /applications/all ==\n";

$all = run_scenario('bearer', $valid_token ?? '');
check('all returns HTTP 200', $all['status'] === 200);
check('all returns success=true', $all['success'] === true);

$all_data = is_array($all['data']) ? $all['data'] : [];
check('all data is an array', is_array($all_data));

echo "\n== Case 4: pagination envelope ==\n";

$pagination_keys = ['current_page', 'per_page', 'total', 'total_pages', 'has_next_page', 'has_previous_page'];
$pagination_ok = true;
if (!isset($all_data['pagination']) || !is_array($all_data['pagination'])) {
    $pagination_ok = false;
} else {
    foreach ($pagination_keys as $key) {
        if (!array_key_exists($key, $all_data['pagination'])) {
            $pagination_ok = false;
        }
    }
}
check('all pagination envelope matches Applied/Accepted/Rejected/Withdrawn (key set)', $pagination_ok);

$all_items = items($all);

echo "\n== Cases 5-10: statuses, DTO contracts, DB cross-checks ==\n";

/*
 * Expected per-status card DTO key sets (exact).
 */
$expected_keys_by_card = [
    'Applied' => [
        'id', 'training_id', 'training_title', 'status', 'specialization',
        'company_name', 'company_logo', 'training_type', 'method',
        'is_paid', 'may_lead_to_hire', 'applied_at',
        'starts_at', 'ends_at', 'duration', 'can_withdraw',
    ],
    'Accepted' => [
        'id', 'training_id', 'training_title', 'status', 'specialization',
        'company_name', 'company_logo', 'training_type', 'method',
        'is_paid', 'may_lead_to_hire', 'accepted_at',
        'starts_at', 'ends_at', 'duration',
        'free_trial_days', 'free_trial_days_remaining',
        'motivational_message', 'payment_status', 'bank_account',
    ],
    'Rejected' => [
        'id', 'training_id', 'training_title', 'status', 'specialization',
        'company_name', 'company_logo', 'training_type', 'method',
        'rejected_at', 'rejection_reason', 'rejection_note',
    ],
    'Withdrawn' => [
        'id', 'training_id', 'training_title', 'status', 'specialization',
        'company_name', 'company_logo', 'training_type', 'method',
        'withdrawn_at', 'starts_at', 'ends_at',
    ],
];

$common_keys = [
    'id', 'training_id', 'training_title', 'status', 'specialization',
    'company_name', 'company_logo', 'training_type', 'method',
];

/*
 * Keys that must NEVER appear on an All item (raw / PII / internal only).
 * Legitimate per-status card fields (applied_at, rejected_at, can_withdraw,
 * payment_status, ...) are covered by the exact key-set checks instead.
 */
$forbidden_keys = [
    'student_id', 'company_id', 'message', 'full_name', 'email', 'phone',
    'city', 'address', 'why_interested', 'what_to_learn', 'skills',
    'reviewed_by', 'cv_file_id', 'faculty_id', 'university', 'applicant_type',
    'academic_year', 'graduation_year', 'motivation', 'location',
    'status_db', 'reviewed_at',
];

$raw_status_to_card = [
    'submitted' => 'Applied',
    'pending'   => 'Applied',
    'accepted'  => 'Accepted',
    'rejected'  => 'Rejected',
    'withdrawn' => 'Withdrawn',
];

$key_set_by_status = true;
$common_ok = true;
$forbidden_present = [];
$status_mapping_ok = true;
$saw_card_statuses = [];
$db_checks_ok = true;   // title/company/spec/type/method + status-specific fields
$db_check_reasons = [];
$returned_ids_in_order = [];
$returned_activity = [];

foreach ($all_items as $item) {
    if (!is_array($item)) {
        $key_set_by_status = false;
        continue;
    }

    foreach ($common_keys as $key) {
        if (!array_key_exists($key, $item)) {
            $common_ok = false;
        }
    }

    foreach ($forbidden_keys as $key) {
        if (array_key_exists($key, $item)) {
            $forbidden_present[] = $key;
        }
    }

    $card_status = (string) ($item['status'] ?? '');
    $saw_card_statuses[$card_status] = true;

    $expected_keys = $expected_keys_by_card[$card_status] ?? null;
    if ($expected_keys === null) {
        $key_set_by_status = false;
    } else {
        $item_keys = array_keys($item);
        sort($item_keys);
        $expected_sorted = $expected_keys;
        sort($expected_sorted);
        if ($item_keys !== $expected_sorted) {
            $key_set_by_status = false;
        }
    }

    $id = (int) ($item['id'] ?? 0);
    $returned_ids_in_order[] = $id;

    $row = db_fetch_one(
        "SELECT
             a.status,
             a.applied_at,
             a.reviewed_at,
             a.withdrawn_at,
             a.rejection_reason,
             a.rejection_note,
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
        [$id]
    );

    if (is_array($row)) {
        $raw_status = strtolower((string) ($row['status'] ?? ''));
        $expected_card = $raw_status_to_card[$raw_status] ?? null;
        if ($expected_card !== $card_status) {
            $status_mapping_ok = false;
        }

        $dbcheck_probe = function (bool $bad, string $why) use (&$db_checks_ok, &$db_check_reasons): void {
            if ($bad) {
                $db_checks_ok = false;
                $db_check_reasons[] = $why;
            }
        };

        $dbcheck_probe((string) ($item['training_title'] ?? '') !== (string) ($row['training_title'] ?? ''), 'title');
        $dbcheck_probe((string) ($item['company_name'] ?? '') !== (string) ($row['company_name'] ?? ''), 'company');
        $dbcheck_probe(
            (int) ($row['specialization_id'] ?? 0) !== $student_spec_id
            || ($item['specialization'] ?? '') !== (string) ($row['specialization_name'] ?? ''),
            'spec'
        );
        $dbcheck_probe(
            (string) ($item['training_type'] ?? '') !== (string) ($row['training_type'] ?? '')
            || (string) ($item['method'] ?? '') !== (string) ($row['mode'] ?? ''),
            'type/method'
        );

        switch ($raw_status) {
            case 'submitted':
            case 'pending':
                $dbcheck_probe(
                    ($item['applied_at'] ?? 'x') !== application_iso8601($row['applied_at']),
                    'applied_at'
                );
                $dbcheck_probe(
                    array_key_exists('rejected_at', $item)
                    || array_key_exists('rejection_reason', $item)
                    || array_key_exists('accepted_at', $item)
                    || array_key_exists('withdrawn_at', $item),
                    'applied-leaks'
                );
                $dbcheck_probe(!isset($item['can_withdraw']) || !is_bool($item['can_withdraw']), 'can_withdraw');
                $returned_activity[] = (string) ($row['applied_at'] ?? '');
                break;

            case 'accepted':
                $dbcheck_probe(
                    ($item['accepted_at'] ?? 'x') !== application_iso8601($row['reviewed_at']),
                    'accepted_at'
                );
                $dbcheck_probe(
                    array_key_exists('rejected_at', $item)
                    || array_key_exists('rejection_reason', $item)
                    || array_key_exists('withdrawn_at', $item),
                    'accepted-leaks'
                );
                $dbcheck_probe(
                    !in_array((string) ($item['payment_status'] ?? ''), ['not_required', 'pending', 'paid', 'partially_paid', 'failed'], true),
                    'payment_status'
                );
                $returned_activity[] = (string) ($row['reviewed_at'] ?? $row['applied_at'] ?? '');
                break;

            case 'rejected':
                $dbcheck_probe(
                    ($item['rejected_at'] ?? 'x') !== application_iso8601($row['reviewed_at']),
                    'rejected_at'
                );
                $dbcheck_probe(
                    (string) ($item['rejection_reason'] ?? '') !== (string) ($row['rejection_reason'] ?? ''),
                    'rejection_reason'
                );
                $dbcheck_probe(
                    (string) ($item['rejection_note'] ?? '') !== (string) ($row['rejection_note'] ?? ''),
                    'rejection_note'
                );
                $dbcheck_probe(
                    array_key_exists('accepted_at', $item)
                    || array_key_exists('withdrawn_at', $item)
                    || array_key_exists('applied_at', $item),
                    'rejected-leaks'
                );
                $returned_activity[] = (string) ($row['reviewed_at'] ?? $row['applied_at'] ?? '');
                break;

            case 'withdrawn':
                $dbcheck_probe(
                    ($item['withdrawn_at'] ?? 'x') !== application_iso8601($row['withdrawn_at']),
                    'withdrawn_at'
                );
                $dbcheck_probe(
                    array_key_exists('rejected_at', $item)
                    || array_key_exists('rejection_reason', $item)
                    || array_key_exists('accepted_at', $item)
                    || array_key_exists('applied_at', $item),
                    'withdrawn-leaks'
                );
                $dbcheck_probe(
                    ($item['starts_at'] ?? 'x') !== application_iso8601($row['starts_at'] ?? null)
                    || ($item['ends_at'] ?? 'x') !== application_iso8601($row['ends_at'] ?? null),
                    'starts/ends'
                );
                $returned_activity[] = (string) ($row['withdrawn_at'] ?? $row['reviewed_at'] ?? $row['applied_at'] ?? '');
                break;
        }
    } else {
        $db_checks_ok = false;
        $db_check_reasons[] = 'no-db-row';
    }
}

foreach (['Applied', 'Accepted', 'Rejected', 'Withdrawn'] as $expected_status) {
    check("all endpoint returns {$expected_status} items", isset($saw_card_statuses[$expected_status]));
}

check('status mapping correct for every item (submitted->Applied, accepted->Accepted, rejected->Rejected, withdrawn->Withdrawn)', $status_mapping_ok);
check('every item exposes the exact per-status card DTO key set (16/20/12/12)', $key_set_by_status);
check('every item exposes the common card keys', $common_ok);
check('all items exclude all forbidden raw/PII/internal keys', $forbidden_present === []);
check('status-specific fields present/correct per status and never leak across statuses', $db_checks_ok);
if (!$db_checks_ok) {
    echo '  reasons: ' . implode(', ', array_unique($db_check_reasons)) . "\n";
}

echo "\n== Case 11: deterministic newest-first ordering across statuses ==\n";

$expected_order_ids = [];
if (is_array($student) && $student_spec_id > 0) {
    $db_scoped_rows = db_fetch_all(
        "SELECT a.id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE
             a.student_id = ?
             AND a.status IN ('submitted', 'accepted', 'rejected', 'withdrawn')
             AND t.specialization_id = ?
         ORDER BY
             GREATEST(
                 COALESCE(a.withdrawn_at, a.applied_at),
                 COALESCE(a.reviewed_at, a.applied_at),
                 a.applied_at
             ) DESC,
             a.id DESC",
        [$sid, $student_spec_id]
    );
    foreach (is_array($db_scoped_rows) ? $db_scoped_rows : [] as $ar) {
        $expected_order_ids[] = (int) $ar['id'];
    }
}
$expected_scoped_total = count($expected_order_ids);

check('returned order matches the repository ORDER BY (activity DESC, id DESC)', $returned_ids_in_order === $expected_order_ids);

$chronological_ok = true;
for ($i = 1; $i < count($returned_activity); $i++) {
    if ($returned_activity[$i] > $returned_activity[$i - 1]) {
        $chronological_ok = false;
    }
}
check('effective activity timestamps are non-increasing (newest first)', $chronological_ok);

$expected_first_ids = [
    $fixture_in_spec_by_status['withdrawn'] ?? 0, // 05:03
    $fixture_in_spec_by_status['rejected']  ?? 0, // 05:02
    $fixture_in_spec_by_status['accepted']  ?? 0, // 05:01
    $fixture_in_spec_by_status['submitted'] ?? 0, // 05:00
];
check('the four seeded fixtures are the newest items in reverse timestamp order', array_slice($returned_ids_in_order, 0, 4) === $expected_first_ids);
check('the newest item is the seeded in-spec withdrawn fixture', ($returned_ids_in_order[0] ?? 0) === ($fixture_in_spec_by_status['withdrawn'] ?? -1));

echo "\n== Case 12: SQL/repository-level scoping ==\n";

$returned_ids = $returned_ids_in_order;
check('out-of-specification rejected fixture is NEVER returned', !in_array($fixture_out_spec_id, $returned_ids, true));

$fixture_out_found = (bool) db_fetch_one(
    "SELECT id FROM training_applications WHERE id = ? AND status = 'rejected' LIMIT 1",
    [$fixture_out_spec_id]
);
check('out-of-spec fixture exists in the DB as rejected (proof filtering is real)', $fixture_out_found);

$expected_scoped_ids = [];
$count_all_by_status = [];
if (is_array($student) && $student_spec_id > 0) {
    $rows = db_fetch_all(
        "SELECT a.id, a.status
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE a.student_id = ? AND t.specialization_id = ?",
        [$sid, $student_spec_id]
    );
    foreach (is_array($rows) ? $rows : [] as $r) {
        $expected_scoped_ids[] = (int) $r['id'];
        $count_all_by_status[(string) ($r['status'] ?? '')] =
            ($count_all_by_status[(string) ($r['status'] ?? '')] ?? 0) + 1;
    }
}
sort($expected_scoped_ids);
$returned_sorted = $returned_ids;
sort($returned_sorted);
check('every returned app is in the student specialization (SQL scoping)', $returned_sorted === $expected_scoped_ids);
check('the merged list contains all four per-status scoped groups', isset($count_all_by_status['submitted']) && isset($count_all_by_status['accepted']) && isset($count_all_by_status['rejected']) && isset($count_all_by_status['withdrawn']));

echo "\n== Case 13: pagination values and no-cross-page duplicates ==\n";

$pagination = is_array($all_data['pagination'] ?? null) ? $all_data['pagination'] : [];
$p_total = (int) ($pagination['total'] ?? -1);
$p_page = (int) ($pagination['current_page'] ?? 0);
$p_per = (int) ($pagination['per_page'] ?? 0);
$p_pages = (int) ($pagination['total_pages'] ?? -1);

check('pagination total equals the scoped 4-status count', $p_total === $expected_scoped_total);
check('all items count matches min(total, per_page)', count($all_items) === min($expected_scoped_total, 20));
check('pagination current_page is 1', $p_page === 1);
check('pagination per_page is 20', $p_per === 20);
check('pagination total_pages equals ceil(total/per_page)', $p_pages === (int) ceil($expected_scoped_total / 20));

if (is_array($student)) {
    $expected = application_service_list_all(
        (int) $student['user_id'],
        ['page' => 1, 'limit' => 100, 'scope_to_specialization' => true]
    );
    $expected_items = is_array($expected['data'] ?? null) ? ($expected['data']['items'] ?? []) : [];
    $expected_count = (int) ($expected['success'] ? count($expected_items) : -1);
    check('all count matches the scoped list_all service count', count($all_items) === $expected_count);

    $service_pagination = is_array($expected['data']['pagination'] ?? null) ? $expected['data']['pagination'] : [];
    check('service total matches HTTP total', (int) ($service_pagination['total'] ?? -1) === $p_total);

    $page1 = application_service_list_all(
        (int) $student['user_id'],
        ['page' => 1, 'limit' => 1, 'scope_to_specialization' => true]
    );
    $page2 = application_service_list_all(
        (int) $student['user_id'],
        ['page' => 2, 'limit' => 1, 'scope_to_specialization' => true]
    );
    $p1 = is_array($page1['data']['pagination'] ?? null) ? $page1['data']['pagination'] : [];
    $p2 = is_array($page2['data']['pagination'] ?? null) ? $page2['data']['pagination'] : [];
    $i1 = is_array($page1['data']['items'] ?? null) ? $page1['data']['items'] : [];
    $i2 = is_array($page2['data']['items'] ?? null) ? $page2['data']['items'] : [];

    check('service page=2&limit=1 advances the page', (int) ($p2['current_page'] ?? 0) === 2 && (int) ($p2['per_page'] ?? 0) === 1);
    check('service total stays the same across pages', (int) ($p2['total'] ?? -1) === $p_total);
    $id1 = (int) ($i1[0]['id'] ?? 0);
    $id2 = (int) ($i2[0]['id'] ?? 0);
    check('page=1 and page=2 return different applications (no cross-page duplicates)', $id1 > 0 && $id2 > 0 && $id1 !== $id2);
}

echo "\n== Case 14: other application endpoints still work ==\n";

$withdrawn = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/withdrawn');
check('withdrawn endpoint still works (HTTP 200)', $withdrawn['status'] === 200);

$applied = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/applied');
check('applied endpoint still works (HTTP 200)', $applied['status'] === 200);

$accepted = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/accepted');
check('accepted endpoint still works (HTTP 200)', $accepted['status'] === 200);

$rejected = run_scenario('bearer', $valid_token ?? '', '/api/v1/applications/rejected');
check('rejected endpoint still works (HTTP 200)', $rejected['status'] === 200);

$detail_id = ($fixture_in_spec_by_status['withdrawn'] ?? 0) > 0 ? $fixture_in_spec_by_status['withdrawn'] : 1884;
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