<?php

/**
 * MASAR - Applications Accepted Endpoint Regression Test
 *
 * Verifies the dedicated GET /api/v1/applications/accepted endpoint:
 *
 *   Case 1  Guest (no credentials)  -> 401 Unauthorized
 *   Case 2  Invalid/expired token   -> 401 Unauthorized
 *   Case 3  Company token           -> 403 'Student access required.'
 *   Case 4  Valid student token     -> 200 OK, ONLY accepted apps
 *           scoped to the student's own specialization
 *           (items all 'Accepted', each training_listing.specialization_id
 *           equals the student's specialization; accepted apps for other
 *           specializations are excluded; count matches the scoped
 *           application_service_list_accepted(scope) result)
 *           Accepted Card DTO: no PII/detail fields, accepted_at from
 *           reviewed_at, paid-only free-trial fields, a deterministic daily
 *           motivational message, a manual payment lifecycle state
 *           (payment_status) and a company bank_account that must both match
 *           the database (paid trainings) or be absent (free trainings).
 *
 * Run from the backend root:
 *     php tests/applications_accepted_endpoint_regression.php
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

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/applications/accepted'): array
{
    $case_file = dirname(__FILE__) . '/applications_accepted_endpoint_case.php';
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

echo "\n== Case 4: valid student token -> /applications/accepted (Accepted Card DTO) ==\n";

$accepted = run_scenario('bearer', $valid_token ?? '');
check('accepted returns HTTP 200', $accepted['status'] === 200);
check('accepted returns success=true', $accepted['success'] === true);

$accepted_data = is_array($accepted['data']) ? $accepted['data'] : [];
check('accepted data is an array', is_array($accepted_data));

$pagination_keys = ['current_page', 'per_page', 'total', 'total_pages', 'has_next_page', 'has_previous_page'];
$pagination_ok = true;
if (!isset($accepted_data['pagination']) || !is_array($accepted_data['pagination'])) {
    $pagination_ok = false;
} else {
    foreach ($pagination_keys as $key) {
        if (!array_key_exists($key, $accepted_data['pagination'])) {
            $pagination_ok = false;
        }
    }
}
check('accepted pagination envelope is identical to Applied (key set)', $pagination_ok);

$accepted_items = items($accepted);
check('accepted returns at least one application', count($accepted_items) >= 1);

$student_spec_id = (int) ($student['specialization_id'] ?? 0);
$student_spec_row = $student_spec_id > 0
    ? db_fetch_one(
        "SELECT name FROM specializations WHERE id = ? LIMIT 1",
        [$student_spec_id]
    )
    : null;
$student_spec_name = is_array($student_spec_row) ? (string) $student_spec_row['name'] : '';

$db_accepted_count = 0;
$expected_match_trainings = [];
if (is_array($student) && $student_spec_id > 0) {
    $all_accepted_rows = db_fetch_all(
        "SELECT a.training_id, t.specialization_id
         FROM training_applications a
         INNER JOIN training_listings t
             ON t.id = a.training_id
         WHERE a.student_id = ? AND a.status = 'accepted'
         ORDER BY a.training_id ASC",
        [(int) $student['student_id']]
    );
    foreach (is_array($all_accepted_rows) ? $all_accepted_rows : [] as $ar) {
        $db_accepted_count++;
        if ((int) ($ar['specialization_id'] ?? 0) === $student_spec_id) {
            $expected_match_trainings[] = (int) $ar['training_id'];
        }
    }
}
$expected_match_trainings = array_values(array_unique($expected_match_trainings));

check('student has at least one accepted application to scope', $db_accepted_count >= 1);
check('student has at least one accepted application in their specialization', count($expected_match_trainings) >= 1);
check('student has at least one accepted application OUTSIDE their specialization (scoping is meaningful)', $db_accepted_count > count($expected_match_trainings));

$pagination_total = (int) ($accepted_data['pagination']['total'] ?? -1);
check('accepted pagination total equals the specialization-filtered count', $pagination_total === count($expected_match_trainings));

$required_keys = [
    'id', 'training_id', 'training_title', 'status', 'specialization',
    'company_name', 'company_logo', 'training_type', 'method', 'is_paid',
    'may_lead_to_hire', 'accepted_at', 'starts_at', 'ends_at', 'duration',
    'free_trial_days', 'free_trial_days_remaining', 'motivational_message',
    'payment_status', 'bank_account',
];
$forbidden_keys = [
    'full_name', 'email', 'phone', 'city', 'address', 'why_interested',
    'what_to_learn', 'skills', 'rejection_reason', 'rejection_note',
    'reviewed_at', 'withdrawn_at', 'reviewed_by', 'cv_file_id', 'faculty_id',
    'university', 'applicant_type', 'academic_year', 'graduation_year',
    'motivation', 'applied_at', 'can_withdraw',
];
$iso_regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)$/';

$key_ok = true;
$forbidden_present = [];
$status_ok = true;
$hire_ok = true;
$iso_ok = true;
$duration_ok = true;
$title_ok = true;
$spec_ok = true;
$company_ok = true;
$spec_matches_student = true;
$returned_training_ids = [];
$paid_items = 0;
$trial_ok = true;
$message_ok = true;
$bank_ok = true;
$payment_status_ok = true;
$accepted_at_ok = true;
$daily_message = '';
$message_same = true;

foreach ($accepted_items as $item) {
    if (!is_array($item)) {
        $key_ok = false;
        continue;
    }

    if (($item['status'] ?? '') !== 'Accepted') {
        $status_ok = false;
    }

    if ((bool)($item['may_lead_to_hire'] ?? false) !== (bool)($item['is_paid'] ?? false)) {
        $hire_ok = false;
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

    foreach (['accepted_at', 'starts_at', 'ends_at'] as $key) {
        if (($item[$key] ?? null) !== null && !preg_match($iso_regex, (string)$item[$key])) {
            $iso_ok = false;
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
                 c.legal_name AS company_name,
                 t.is_paid,
                 t.trial_period_days,
                 t.starts_at,
                 t.ends_at,
                 c.bank_name,
                 c.bank_account_name,
                 c.bank_account_number,
                 c.bank_transfer_instructions
             FROM training_listings t
             LEFT JOIN specializations s ON s.id = t.specialization_id
             LEFT JOIN companies c ON c.id = t.company_id
             WHERE t.id = ?
             LIMIT 1",
            [$training_id]
        )
        : null;

    if (!array_key_exists('bank_account', $item)) {
        $bank_ok = false;
    }
    if (!array_key_exists('payment_status', $item)) {
        $payment_status_ok = false;
    }

    /*
    | Manual payment lifecycle + bank destination must both match the DB:
    |   free training      -> bank_account null,  payment_status 'not_required'
    |   paid, no row       -> bank_account per company, payment_status 'pending'
    |   paid + confirmation-> bank_account per company, payment_status from row
    */
    $item_is_paid = (bool) ($item['is_paid'] ?? false);
    if (!$item_is_paid) {
        if ($item['bank_account'] !== null) {
            $bank_ok = false;
        }
        if (($item['payment_status'] ?? '') !== 'not_required') {
            $payment_status_ok = false;
        }
    } elseif (is_array($row)) {
        $db_bank_number = $row['bank_account_number'] ?? null;
        $db_bank_number = $db_bank_number !== null ? trim((string) $db_bank_number) : '';

        if ($db_bank_number === '') {
            if ($item['bank_account'] !== null) {
                $bank_ok = false;
            }
        } else {
            $bank_account = $item['bank_account'];
            if (!is_array($bank_account)) {
                $bank_ok = false;
            } else {
                foreach ([
                    'bank_name' => $row['bank_name'] ?? null,
                    'account_name' => $row['bank_account_name'] ?? null,
                    'account_number' => $row['bank_account_number'] ?? null,
                    'instructions' => $row['bank_transfer_instructions'] ?? null,
                ] as $bank_key => $db_value) {
                    $expected = ($db_value !== null && trim((string) $db_value) !== '') ? (string) $db_value : null;
                    if (array_key_exists($bank_key, $bank_account) && ($bank_account[$bank_key] ?? null) !== $expected) {
                        $bank_ok = false;
                    }
                }
            }
        }

        $db_payment = db_fetch_one(
            "SELECT status FROM payments
             WHERE training_id = ? AND student_id = ?
             ORDER BY id DESC LIMIT 1",
            [$training_id, (int) ($student['student_id'] ?? 0)]
        );
        $expected_status = is_array($db_payment) ? (string) $db_payment['status'] : 'pending';
        if (($item['payment_status'] ?? '') !== $expected_status) {
            $payment_status_ok = false;
        }
    }

    if (!is_string($item['motivational_message'] ?? '') || trim((string)($item['motivational_message'] ?? '')) === '') {
        $message_ok = false;
    }
    if ($daily_message === '') {
        $daily_message = (string) ($item['motivational_message'] ?? '');
    } elseif ($daily_message !== (string) ($item['motivational_message'] ?? '')) {
        $message_same = false;
    }
    if (($item['motivational_message'] ?? null) !== application_daily_motivational_message()) {
        $message_ok = false;
    }

    if (array_key_exists('is_paid', $item)) {
        if ($item['is_paid']) {
            $paid_items++;
        }
    }

    if (
        is_int($item['free_trial_days_remaining'] ?? null)
        &&
        (
            $item['free_trial_days_remaining'] < 0
            ||
            (
                is_int($item['free_trial_days'] ?? null)
                &&
                $item['free_trial_days_remaining'] > $item['free_trial_days']
            )
        )
    ) {
        $trial_ok = false;
    }

    $training_id = (int)($item['training_id'] ?? 0);

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

        $db_is_paid = (bool)($row['is_paid'] ?? false);
        $db_trial = null;
        if ($db_is_paid && isset($row['trial_period_days']) && $row['trial_period_days'] !== null && $row['trial_period_days'] !== '') {
            $db_trial = (int) $row['trial_period_days'];
        }

        if (($item['free_trial_days'] ?? 'x') !== $db_trial) {
            $trial_ok = false;
        }
        if (($item['free_trial_days_remaining'] ?? 'x') !== application_free_trial_days_remaining($db_trial, $row['starts_at'] ?? null)) {
            $trial_ok = false;
        }
        if ((bool)($item['is_paid'] ?? false) !== $db_is_paid) {
            $trial_ok = false;
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

check('accepted only exposes the required DTO keys', $key_ok);
check('accepted excludes all forbidden PII/detail keys', $forbidden_present === []);
check('accepted item status equals "Accepted"', $status_ok);
check('accepted may_lead_to_hire always equals is_paid', $hire_ok);
check('accepted timestamps are ISO-8601 with tz offset', $iso_ok);
check('accepted duration equals training_calculate_duration', $duration_ok);
check('accepted training_title matches DB', $title_ok);
check('accepted specialization matches DB', $spec_ok);
check('accepted company_name matches DB', $company_ok);
check('accepted free-trial fields match the DB training (null for free, trial days/countdown for paid)', $trial_ok);
check('accepted bank_account matches each training/company row (paid) or is null (free)', $bank_ok);
check('accepted payment_status matches each training/company row (not_required for free; pending/paid for paid)', $payment_status_ok);
check('accepted motivational_message is non-empty and equals the daily helper', $message_ok);
check('accepted motivational_message is the same for every item today', $message_same);
check('accepted dataset contains at least one paid item', $paid_items >= 1);

echo "\n== Accepted DTO free-trial helpers (pure-function invariants) ==\n";

check('free_trial_days_remaining is null when trial days are missing', application_free_trial_days_remaining(null, '2026-09-15 09:00:00') === null);
check('free_trial_days_remaining is null when starts_at is missing', application_free_trial_days_remaining(10, null) === null);
check('free_trial_days_remaining is full before the training starts', application_free_trial_days_remaining(10, '2099-01-01 09:00:00') === 10);
check('free_trial_days_remaining is 0 long after the trial ended', application_free_trial_days_remaining(10, '2000-01-01 09:00:00') === 0);
check('daily motivational message is a non-empty string', is_string(application_daily_motivational_message()) && trim(application_daily_motivational_message()) !== '');

sort($returned_training_ids);
$missing_matches = array_diff($expected_match_trainings, $returned_training_ids);
$unexpected_matches = array_diff($returned_training_ids, $expected_match_trainings);
check('accepted covers exactly the accepted trainings matching the student specialization', $missing_matches === [] && $unexpected_matches === []);
check('accepted every card specialization equals the student specialization', $spec_matches_student);
check('accepted pagination total matches the returned specialization-filtered set', $pagination_total === count($returned_training_ids));

if (is_array($student)) {
    $expected = application_service_list_accepted(
        (int) $student['user_id'],
        ['page' => 1, 'limit' => 100, 'scope_to_specialization' => true]
    );
    $expected_items = is_array($expected['data'] ?? null) ? ($expected['data']['items'] ?? []) : [];
    $expected_count = (int) ($expected['success'] ? count($expected_items) : -1);
    check('accepted count matches the specialization-scoped accepted service count', count($accepted_items) === $expected_count);
}

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);