<?php

/**
 * MASAR - Applications Manual Payment Confirm Regression Test
 *
 * Verifies the manual (bank transfer) payment lifecycle writer:
 *
 *   POST /api/v1/applications/{id}/payment/confirm
 *
 *   Case 1  Guest (no credentials)                         -> 401
 *   Case 2  Student token on company endpoint              -> 403
 *   Case 3  Non-owning company on a paid app               -> 403 ownership
 *   Case 4  Owning company on a free (unpaid) app          -> 422
 *   Case 5  Owning company on a non-accepted app           -> 409 (when the DB
 *           provides such an app)
 *   Case 6  Owning company on an accepted PAID app         -> 200, creates one
 *           'paid' payment row; /accepted then shows payment_status 'paid'
 *           plus the owning company's bank_account object
 *   Case 7  Repeating the same confirm (idempotency)       -> 200 'already
 *           confirmed', same payment_id, still one row
 *
 * Cleanup restores the payment table to its pre-test state so re-runs and the
 * accepted-endpoint regression (payment_status 'pending' for paid trainings)
 * stay stable.
 *
 * Run from the backend root:
 *     php tests/applications_payment_manual_regression.php
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

function run_confirm(string $scenario, string $token, int $application_id): array
{
    $case_file = dirname(__FILE__) . '/applications_payment_confirm_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token)
        . ' ' . escapeshellarg('/api/v1/applications/' . $application_id . '/payment/confirm');

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

function run_accepted(string $scenario, string $token, string $request_uri = '/api/v1/applications/accepted'): array
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

function payment_rows(int $training_id, int $student_id): array
{
    $rows = db_fetch_all(
        "SELECT id, training_id, student_id, status, paid_at, payment_method
         FROM payments
         WHERE training_id = ? AND student_id = ?
         ORDER BY id ASC",
        [$training_id, $student_id]
    );
    return is_array($rows) ? $rows : [];
}

echo "== Setup: resolve real DB relationships ==\n";

$student = db_fetch_one(
    "SELECT u.id AS user_id, s.id AS student_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.email = 'mammuslim2003@gmail.com'
     LIMIT 1"
);

if (!is_array($student)) {
    $student = db_fetch_one(
        "SELECT u.id AS user_id, s.id AS student_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.status = 'active' AND u.role = 'student'
         ORDER BY s.id LIMIT 1"
    );
}

check('test student resolved', is_array($student));

$accepted_rows = [];
if (is_array($student)) {
    $accepted_rows = db_fetch_all(
        "SELECT
             a.id AS application_id,
             a.training_id,
             a.student_id,
             t.is_paid,
             t.company_id,
             c.user_id AS company_user_id,
             c.bank_account_number,
             c.bank_name,
             c.bank_account_name,
             c.bank_transfer_instructions
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         INNER JOIN companies c ON c.id = t.company_id
         WHERE a.student_id = ? AND a.status = 'accepted'
         ORDER BY a.id ASC",
        [(int) $student['student_id']]
    );
}
$accepted_rows = is_array($accepted_rows) ? $accepted_rows : [];
check('student has at least one accepted free application', count(array_filter($accepted_rows, static fn ($r) => !(bool) ($r['is_paid'] ?? false))) >= 1);
check('student has at least one accepted paid application', count(array_filter($accepted_rows, static fn ($r) => (bool) ($r['is_paid'] ?? false))) >= 1);

$paid_app = null;
$paid_app_with_bank = null;
$free_app = null;
foreach ($accepted_rows as $ar) {
    if ((bool) ($ar['is_paid'] ?? false)) {
        if ($paid_app === null) {
            $paid_app = $ar;
        }
        if (
            $paid_app_with_bank === null
            &&
            trim((string) ($ar['bank_account_number'] ?? '')) !== ''
        ) {
            $paid_app_with_bank = $ar;
        }
    }
}
foreach ($accepted_rows as $ar) {
    if (!(bool) ($ar['is_paid'] ?? false)) {
        $free_app = $ar;
        break;
    }
}

$owner_has_bank = is_array($paid_app_with_bank);
if ($owner_has_bank) {
    $paid_app = $paid_app_with_bank;
}

$owner_user_id = is_array($paid_app) ? (int) ($paid_app['company_user_id'] ?? 0) : 0;

$other_company_user_id = 0;
if (is_array($paid_app) && is_array($free_app)) {
    $free_owner = (int) ($free_app['company_user_id'] ?? 0);
    if ($free_owner !== $owner_user_id && $free_owner > 0) {
        $other_company_user_id = $free_owner;
    } else {
        $other_row = db_fetch_one(
            "SELECT u.id AS user_id FROM users u
             JOIN companies c ON c.user_id = u.id
             WHERE c.id <> ? AND u.status = 'active' AND u.role = 'company'
             LIMIT 1",
            [(int) ($paid_app['company_id'] ?? 0)]
        );
        $other_company_user_id = is_array($other_row) ? (int) ($other_row['user_id'] ?? 0) : 0;
    }
}

$not_accepted_app = null;
if ($owner_user_id > 0) {
    $not_accepted_app = db_fetch_one(
        "SELECT a.id AS application_id, t.company_id
         FROM training_applications a
         INNER JOIN training_listings t ON t.id = a.training_id
         WHERE t.company_id = (
             SELECT id FROM companies WHERE user_id = ? LIMIT 1
         )
         AND a.status <> 'accepted'
         ORDER BY a.id ASC LIMIT 1",
        [$owner_user_id]
    );
}

check('owning company user resolved', $owner_user_id > 0);
check('non-owning company user resolved', $other_company_user_id > 0 && $other_company_user_id !== $owner_user_id);
check('free accepted application resolved', is_array($free_app));
check('paid accepted application resolved', is_array($paid_app));

/*
| Snapshot the payment table BEFORE any confirm runs so the cleanup below can
| restore exactly the pre-test state (delete what we created or revert a row
| we promoted), keeping this test and the accepted-endpoint regression stable
| across re-runs.
*/
$before_payment_snapshot = null;
if (is_array($student) && is_array($paid_app)) {
    $before_payment_snapshot = db_fetch_one(
        "SELECT id, status, paid_at FROM payments
         WHERE training_id = ? AND student_id = ?
         ORDER BY id DESC LIMIT 1",
        [(int) $paid_app['training_id'], (int) $student['student_id']]
    );
}

$student_token = '';
$owner_token = '';
$other_token = '';

if (is_array($student) && $owner_user_id > 0 && $other_company_user_id > 0) {
    $student_token = jwt_issue_access_token([
        'id' => (int) $student['user_id'],
        'role' => 'student',
    ]);
    $owner_token = jwt_issue_access_token([
        'id' => $owner_user_id,
        'role' => 'company',
    ]);
    $other_token = jwt_issue_access_token([
        'id' => $other_company_user_id,
        'role' => 'company',
    ]);
}

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_confirm('guest', '', is_array($paid_app) ? (int) $paid_app['application_id'] : 0);
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2: student token on company endpoint ==\n";

$student_case = run_confirm('bearer', $student_token, is_array($paid_app) ? (int) $paid_app['application_id'] : 0);
check('student token returns HTTP 403', $student_case['status'] === 403);
check('student token body mentions company access', stripos($student_case['body'], 'company access required') !== false);

echo "\n== Case 3: non-owning company on the paid app ==\n";

$not_owner = run_confirm('bearer', $other_token, is_array($paid_app) ? (int) $paid_app['application_id'] : 0);
check('non-owning company returns HTTP 403', $not_owner['status'] === 403);
check('non-owning company returns success=false', $not_owner['success'] === false);

echo "\n== Case 4: owning company on a free (unpaid) app ==\n";

$free_owned_by_other = is_array($free_app) && (int) ($free_app['company_user_id'] ?? 0) === $other_company_user_id;
if ($free_owned_by_other) {
    $no_fee = run_confirm('bearer', $other_token, is_array($free_app) ? (int) $free_app['application_id'] : 0);
    check('free app confirm returns HTTP 422', $no_fee['status'] === 422);
    check('free app confirm body mentions no payment required', stripos($no_fee['body'], 'does not require payment') !== false);
} else {
    echo "SKIP (no free accepted application owned by the non-owning company available)"
        . (is_array($paid_app) ? '; paid app: ' . (int) $paid_app['application_id'] : '')
        . "\n";
}

echo "\n== Case 5: owning company on a non-accepted app (only when available) ==\n";

if (is_array($not_accepted_app)) {
    $not_accepted = run_confirm('bearer', $owner_token, (int) $not_accepted_app['application_id']);
    check('non-accepted app confirm returns HTTP 409', $not_accepted['status'] === 409);
    check('non-accepted app body mentions accepted only', stripos($not_accepted['body'], 'accepted') !== false);
} else {
    echo "SKIP (no non-accepted application available for the owning company)\n";
}

echo "\n== Case 6: owning company confirms an accepted paid app ==\n";

$confirm = run_confirm('bearer', $owner_token, is_array($paid_app) ? (int) $paid_app['application_id'] : 0);
check('confirm returns HTTP 200', $confirm['status'] === 200);
check('confirm returns success=true', $confirm['success'] === true);
check('confirm body mentions confirmed successfully', stripos($confirm['body'], 'confirmed successfully') !== false);

$confirm_data = is_array($confirm['data']) ? $confirm['data'] : [];
check('confirm returns a payment id', (int) ($confirm_data['payment_id'] ?? 0) > 0);
check('confirm reports status paid', ($confirm_data['status'] ?? '') === 'paid');
check('confirm reports already_confirmed=false', ($confirm_data['already_confirmed'] ?? true) === false);

if (is_array($paid_app)) {
    $training_id = (int) $paid_app['training_id'];
    $rows_after = payment_rows($training_id, (int) $student['student_id']);
    check('exactly one payment row exists after confirm', count($rows_after) === 1);
    check('payment row is marked paid', count($rows_after) === 1 && ($rows_after[0]['status'] ?? '') === 'paid');
    check('payment row uses the manual method', count($rows_after) === 1 && ($rows_after[0]['payment_method'] ?? '') === 'manual');
}

echo "\n== Case 7: accepting ledger reflects the confirmation ==\n";

$accepted = run_accepted('bearer', $student_token);
check('accepted returns HTTP 200 after confirm', $accepted['status'] === 200);

$ledger_item = null;
if (is_array($accepted['data']) && is_array($accepted['data']['items'] ?? null) && is_array($paid_app)) {
    foreach ($accepted['data']['items'] as $item) {
        if ((int) ($item['training_id'] ?? 0) === (int) $paid_app['training_id']) {
            $ledger_item = $item;
            break;
        }
    }
}
check('accepted ledger contains the confirmed training', is_array($ledger_item));
check('accepted ledger payment_status=paid for the confirmed training', is_array($ledger_item) && ($ledger_item['payment_status'] ?? '') === 'paid');
if ($owner_has_bank) {
    check('accepted ledger exposes the company bank_account object', is_array($ledger_item) && is_array($ledger_item['bank_account'] ?? null));
    check('accepted ledger bank account number matches the owning company', is_array($ledger_item) && trim((string) ($ledger_item['bank_account']['account_number'] ?? '')) === trim((string) ($paid_app['bank_account_number'] ?? '')));
} else {
    echo "SKIP (owning company has no bank details configured; bank_account object covered by accepted-endpoint regression)\n";
}

echo "\n== Case 8: idempotency (repeat confirm) ==\n";

$repeat = run_confirm('bearer', $owner_token, is_array($paid_app) ? (int) $paid_app['application_id'] : 0);
check('repeat confirm returns HTTP 200', $repeat['status'] === 200);
check('repeat confirm reports already_confirmed=true', is_array($repeat['data'] ?? null) && ($repeat['data']['already_confirmed'] ?? false) === true);
check('repeat confirm body mentions already confirmed', stripos($repeat['body'], 'already confirmed') !== false);

if (is_array($paid_app)) {
    $rows_repeat = payment_rows((int) $paid_app['training_id'], (int) $student['student_id']);
    check('repeat confirm does not duplicate the payment row', count($rows_repeat) === 1);
    check('repeat confirm keeps the same payment id', count($rows_repeat) === 1 && (int) ($rows_repeat[0]['id'] ?? 0) === (int) ($confirm_data['payment_id'] ?? 0));
}

echo "\n== Cleanup: restore the payment table to its pre-test state ==\n";

if (is_array($paid_app)) {
    $training_id = (int) $paid_app['training_id'];
    $student_id = (int) $student['student_id'];

    $before = $before_payment_snapshot;

    if (is_array($before)) {
        db_execute(
            "UPDATE payments
             SET status = ?, paid_at = ?
             WHERE id = ? LIMIT 1",
            [
                (string) ($before['status'] ?? 'pending'),
                $before['paid_at'] ?? null,
                (int) $before['id'],
            ]
        );
    } else {
        db_execute(
            "DELETE FROM payments WHERE training_id = ? AND student_id = ?",
            [$training_id, $student_id]
        );
    }

    check('payment table restored (no leftover paid row)', count(payment_rows($training_id, $student_id)) === (is_array($before) ? 1 : 0));
    if (!is_array($before)) {
        $after_accepted = run_accepted('bearer', $student_token);
        $restored = null;
        if (is_array($after_accepted['data']) && is_array($after_accepted['data']['items'] ?? null)) {
            foreach ($after_accepted['data']['items'] as $item) {
                if ((int) ($item['training_id'] ?? 0) === $training_id) {
                    $restored = $item;
                    break;
                }
            }
        }
        check('accepted ledger payment_status back to pending after cleanup', is_array($restored) && ($restored['payment_status'] ?? '') === 'pending');
    }
}

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);