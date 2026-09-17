<?php

/**
 * MASAR - Certificate Eligibility Timing Regression
 *
 * Proves the ONLY source of certificate eligibility timing is
 * training_listings.ends_at, compared with the SAME time source the backend
 * uses (the database NOW()).
 *
 * Dataset (tests/certificates_test_data_seeder.php):
 *   - 100400-100407 : eight FINISHED trainings (ends_at <= NOW()) with
 *                     accepted app + completed session + no certificate ->
 *                     ELIGIBLE, can_request=true.
 *   - 100422        : one UNFINISHED training (ends_at > NOW()) that STILL has
 *                     an accepted application AND a completed session -> NOT
 *                     eligible, NOT requestable. Its session is the SAME state
 *                     as the finished ones, so any difference can only come
 *                     from ends_at: the exact behaviour the gate enforces.
 *
 * Run from the backend root (after the dataset seeder):
 *     php tests/certificates_test_data_seeder.php
 *     php tests/certificates_eligibility_timing_regression.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/core/database/connection.php';
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/core/auth/token.php';
require_once __DIR__ . '/certificates_fixtures.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run_api(string $scenario, string $token, string $method, string $uri, string $body = ''): array
{
    $case_file = dirname(__FILE__) . '/certificates_api_case.php';
    $body_file = '';
    if ($body !== '') {
        $body_file = tempnam(sys_get_temp_dir(), 'certtime');
        if ($body_file !== false) {
            file_put_contents($body_file, $body);
        } else {
            $body_file = '';
        }
    }
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($uri);
    if ($body_file !== '') {
        $command .= ' ' . escapeshellarg($body_file);
    }
    $output = (string) shell_exec($command . ' 2>&1');
    if ($body_file !== '') {
        @unlink($body_file);
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
        'body'    => $body_out,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? []) : [],
    ];
}

echo "== Resolve identity ==\n";

$student = cf_resolve_student_first(['mammuslim2003@gmail.com']);
$company = cf_resolve_company('company@test.local');
$admin_user_id = cf_resolve_user_id_first(['admin@test.local', 'admin@masar.eg', 'compliance@masar.eg']);

check('student A resolved (mammuslim2003@gmail.com / student 1498)', $student['student_id'] === 1498);
check('TestHire (100161) resolved', $company['company_id'] === 100161);
check('real admin resolved', $admin_user_id > 0);

$student_token = jwt_issue_access_token(['id' => $student['user_id'], 'role' => 'student']);
$admin_token   = jwt_issue_access_token(['id' => $admin_user_id, 'role' => 'admin']);

$base = '/api/v1/certificates';

$FINISHED = [100400, 100401, 100402, 100403, 100404, 100405, 100406, 100407];
$UNFINISHED = 100422;

echo "\n== Case A: BEFORE the training has ended (ends_at > NOW()) ==\n";

$unfinished_db = db_fetch_one(
    "SELECT ends_at,
            ends_at > NOW() AS running_now,
            (SELECT status FROM training_applications
              WHERE training_id = ? AND student_id = ? LIMIT 1) AS app_status,
            (SELECT ts.status FROM training_sessions ts
              JOIN training_applications a ON a.id = ts.application_id
              WHERE a.training_id = ? AND a.student_id = ? LIMIT 1) AS session_status
     FROM training_listings WHERE id = ? LIMIT 1",
    [$UNFINISHED, 1498, $UNFINISHED, 1498, $UNFINISHED]
);
check("$UNFINISHED still runs (ends_at > NOW(), same NOW as the backend)", (int) ($unfinished_db['running_now'] ?? 0) === 1);
check("$UNFINISHED has an accepted application (prerequisite present)", ($unfinished_db['app_status'] ?? '') === 'accepted');
check("$UNFINISHED has a completed session (prerequisite present)", ($unfinished_db['session_status'] ?? '') === 'completed');

$elig_a = run_api('bearer', $student_token, 'GET', $base . '/eligible');
$elig_items = is_array($elig_a['data']) ? $elig_a['data'] : [];
check('GET /eligible returns 200', $elig_a['status'] === 200);
check("$UNFINISHED is NOT listed as eligible (training has not ended)", count(array_filter($elig_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $UNFINISHED)) === 0);
check('no eligible row with a running ends_at exists in the feed', count(array_filter($elig_items, function ($i) {
    $tid = (int) ($i['training_id'] ?? 0);
    $row = db_fetch_one("SELECT ends_at > NOW() AS running FROM training_listings WHERE id = ? LIMIT 1", [$tid]);
    return (int) ($row['running'] ?? 0) === 1;
})) === 0);

$request_running = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $UNFINISHED]));
check("POST /certificates for $UNFINISHED is rejected (422 - must be accepted and completed)", $request_running['status'] === 422 && ($request_running['success'] ?? true) === false);

echo "\n== Case B: AFTER the training has ended (ends_at <= NOW()) ==\n";

$finished_db = db_fetch_all(
    "SELECT id AS tid FROM training_listings
     WHERE id IN (" . implode(',', array_fill(0, count($FINISHED), '?')) . ")
       AND ends_at <= NOW()",
    $FINISHED
);
check('all eight finished dataset trainings have ends_at <= NOW()', count($finished_db) === count($FINISHED));

check('GET /eligible returns multiple finished trainings', is_array($elig_a['data']) && count($elig_a['data']) >= 6);

$all_requestable = true;
$paid_ok = true;
foreach ($elig_items as $i) {
    $tid = (int) ($i['training_id'] ?? 0);
    $db = db_fetch_one(
        "SELECT ends_at <= NOW() AS finished, is_paid FROM training_listings WHERE id = ? LIMIT 1",
        [$tid]
    );
    if ((int) ($db['finished'] ?? 0) !== 1) {
        $all_requestable = false;
    }
    if (($i['can_request'] ?? false) !== true || ($i['can_download'] ?? true) !== false) {
        $all_requestable = false;
    }
    if ((bool) ($i['is_paid'] ?? false) !== ((int) ($db['is_paid'] ?? 0) === 1)) {
        $paid_ok = false;
    }
}
check('every eligible row is a FINISHED training with can_request=true / can_download=false', $all_requestable);
check('every eligible row is_paid mirrors training_listings.is_paid', $paid_ok);

$first_eligible = array_values(array_filter($elig_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $FINISHED[0]));
$first_item = $first_eligible[0] ?? null;
check("finished training {$FINISHED[0]} is eligible with status=eligible", is_array($first_item) && ($first_item['status'] ?? '') === 'eligible' && ($first_item['can_request'] ?? false) === true);
check("finished training {$FINISHED[0]} carries company/specialization context", is_array($first_item) && (int) ($first_item['company_id'] ?? 0) === 100161 && !empty($first_item['company_name']) && !empty($first_item['training_title']) && (bool) ($first_item['is_paid'] ?? false) === false);

echo "\n== Case B2: end-to-end requestability on a finished training (then cleanup) ==\n";

$request_finished = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $FINISHED[0]]));
check("POST /certificates for finished {$FINISHED[0]} succeeds (201)", $request_finished['status'] === 201 && ($request_finished['success'] ?? false) === true);
check('the fresh request is pending with can_request=false / can_view=true', is_array($request_finished['data']) && ($request_finished['data']['status'] ?? '') === 'pending' && ($request_finished['data']['can_request'] ?? true) === false && ($request_finished['data']['can_view'] ?? false) === true);

$created_id = (int) ($request_finished['data']['id'] ?? 0);
if ($created_id > 0) {
    db_execute("DELETE FROM certificate_appeals WHERE certificate_id = ?", [$created_id]);
    db_execute("DELETE FROM notifications WHERE entity_type = 'certificate' AND entity_id = ?", [$created_id]);
    db_execute("DELETE FROM certificates WHERE id = ?", [$created_id]);
}
check('timing-regression certificate removed again (dataset restored)', $created_id > 0);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);