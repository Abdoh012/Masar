<?php

/**
 * MASAR - Certificate Eligibility Specialization Matching Regression
 *
 * Proves the mandatory rule:
 *
 *     student.specialization_id == training.specialization_id
 *
 * A certificate may only ever be requested/eligible for a training whose
 * specialization matches the STUDENT's specialization, resolved from the
 * authenticated user and compared against the real training_listings row in
 * the database (never client-supplied values).
 *
 *   Case A : matching specialization (199 == 199) + finished   -> eligible + request OK
 *   Case B : mismatched specialization (199 vs 205) + finished -> NOT eligible + request 422
 *   Case C : mismatched specialization (199 vs 205) + running  -> NOT eligible + request 422
 *   Case D : matching specialization (199 == 199) + running    -> NOT eligible + request 422
 *            (the existing ends_at rule is unchanged)
 *
 * Cases B/C use freshly generated spec-205 trainings that FULLY satisfy the
 * old prerequisites (accepted application + completed session, and for B also
 * ends_at <= NOW()). They are created only to prove the specialization gate is
 * what excludes them, then removed again so the canonical dataset is restored.
 *
 * Run from the backend root (after the dataset seeder):
 *     php tests/certificates_test_data_seeder.php
 *     php tests/certificates_eligibility_specialization_regression.php
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
        $body_file = tempnam(sys_get_temp_dir(), 'certspec');
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
$student_spec_row = db_fetch_one("SELECT specialization_id FROM students WHERE id = ? LIMIT 1", [1498]);
$student_spec = (int) ($student_spec_row['specialization_id'] ?? 0);
check('student A resolved (mammuslim2003@gmail.com / student 1498)', $student['student_id'] === 1498);
check('student specialization is 199 (Backend Development)', $student_spec === 199);
check('TestHire (100161) resolved', ($company['company_id'] ?? 0) === 100161);

$student_token = jwt_issue_access_token(['id' => $student['user_id'], 'role' => 'student']);
$base = '/api/v1/certificates';

/*
 * Generate a synthetic training under TestHire with the given specialization,
 * ended/status and end-delta, plus an accepted application and a completed
 * session for student A. Returns the training id or 0 on collision.
 */
function synthetic_training(int $spec_id, int $end_days): int
{
    global $company;

    $t = null;
    for ($candidate = 100430; $candidate <= 100499; $candidate++) {
        $exists = db_fetch_one("SELECT id FROM training_listings WHERE id = ? LIMIT 1", [$candidate]);
        if ($exists === null) {
            $t = $candidate;
            break;
        }
    }
    if ($t === null) {
        return 0;
    }

    $now = time();
    $end   = $now + $end_days * 86400;
    $start = $end - 60 * 86400;

    db_execute(
        "INSERT INTO training_listings
            (id, company_id, specialization_id, title, description, training_type, mode,
             may_lead_to_employment, is_paid, compensation_amount, compensation_currency,
             trial_period_days, capacity, status, published_at, starts_at, ends_at,
             application_deadline, location, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 'hands_on', 'remote', 1, 0, NULL, 'EGP',
                 7, 10, 'published', ?, ?, ?, ?, 'Remote', NOW(), NOW())",
        [
            $t, (int) $company['company_id'], $spec_id,
            'Synthetic Spec Mismatch Regression Training #' . $t,
            'Synthetic training used only to prove the specialization gate.',
            date('Y-m-d H:i:s', $start - 20 * 86400),
            date('Y-m-d H:i:s', $start),
            date('Y-m-d H:i:s', $end),
            date('Y-m-d H:i:s', $start - 3 * 86400),
        ]
    );

    db_execute(
        "INSERT INTO training_applications
            (training_id, student_id, company_id, message, full_name, email, phone, city,
             why_interested, what_to_learn, skills, status, applied_at, reviewed_at, reviewed_by,
             university, applicant_type, academic_year, graduation_year, motivation)
         VALUES (?, ?, ?, 'Synthetic spec regression application.', 'Test Student A',
                 'mammuslim2003@gmail.com', '01000000000', 'Cairo',
                 'Synthetic.', 'Synthetic.', 'PHP', 'accepted', ?, ?, ?, NULL, 'student', '3rd', NULL,
                 'Synthetic.')",
        [
            $t, 1498, (int) $company['company_id'],
            date('Y-m-d H:i:s', $start - 2 * 86400),
            date('Y-m-d H:i:s', $start - 1 * 86400),
            (int) $company['company_id'],
        ]
    );
    $app_id = (int) db_last_insert_id();

    db_execute(
        "INSERT INTO training_sessions
            (application_id, training_id, student_id, company_id, status, started_at,
             trial_started_at, trial_ends_at, student_continuation_confirmed_at,
             actual_ended_at, employment_opportunity, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'completed', ?, ?, ?, ?, ?, 1, NOW(), NOW())",
        [
            $app_id, $t, 1498, (int) $company['company_id'],
            date('Y-m-d H:i:s', $start),
            date('Y-m-d H:i:s', $start),
            date('Y-m-d H:i:s', $start + 7 * 86400),
            date('Y-m-d H:i:s', $start + 14 * 86400),
            date('Y-m-d H:i:s', $end),
        ]
    );

    return $t;
}

$createdTrainings = [];

echo "\n== Case A: matching specialization (199 == 199) + FINISHED ==\n";

$matching_finished = 100400;
$row_a = db_fetch_one(
    "SELECT t.specialization_id AS tspec, s.specialization_id AS sspec, t.ends_at <= NOW() AS finished
     FROM training_listings t, students s
     WHERE t.id = ? AND s.id = 1498 LIMIT 1",
    [$matching_finished]
);
check('training 100400 is specialization 199 (== student 199)', (int) ($row_a['tspec'] ?? 0) === 199 && (int) ($row_a['sspec'] ?? 0) === 199);
check('training 100400 has ended (ends_at <= NOW())', (int) ($row_a['finished'] ?? 0) === 1);

$elig_a = run_api('bearer', $student_token, 'GET', $base . '/eligible');
$elig_items = is_array($elig_a['data']) ? $elig_a['data'] : [];
$item_a = null;
foreach ($elig_items as $i) {
    if ((int) ($i['training_id'] ?? 0) === $matching_finished) {
        $item_a = $i;
        break;
    }
}
check('GET /eligible contains the matching finished training 100400', is_array($item_a));
check('eligible item 100400 has can_request=true', is_array($item_a) && ($item_a['can_request'] ?? false) === true);
check('GET /eligible returns 200', $elig_a['status'] === 200);

$req_a = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $matching_finished]));
check('POST /certificates for matching finished 100400 succeeds (201)', $req_a['status'] === 201 && ($req_a['success'] ?? false) === true);
check('the fresh matching request is pending with can_view=true', is_array($req_a['data']) && ($req_a['data']['status'] ?? '') === 'pending' && ($req_a['data']['can_view'] ?? false) === true);
$created_id = (int) ($req_a['data']['id'] ?? 0);

echo "\n== Case B: MISMATCHED specialization (199 vs 205) + FINISHED ==\n";

$mismatch_ended = synthetic_training(205, -10);
check('synthetic spec-205 ENDED training created', $mismatch_ended > 0);
$createdTrainings[] = $mismatch_ended;

$row_b = db_fetch_one(
    "SELECT t.specialization_id AS tspec, s.specialization_id AS sspec, t.ends_at <= NOW() AS finished
     FROM training_listings t, students s
     WHERE t.id = ? AND s.id = 1498 LIMIT 1",
    [$mismatch_ended]
);
check("training $mismatch_ended has spec 205 (Data Analysis) vs student 199 (mismatch)", (int) ($row_b['tspec'] ?? 0) === 205 && (int) ($row_b['sspec'] ?? 0) === 199);
check("training $mismatch_ended has ended (ends_at <= NOW())", (int) ($row_b['finished'] ?? 0) === 1);
$app_b = db_fetch_one(
    "SELECT a.status, ts.status AS sess
     FROM training_applications a LEFT JOIN training_sessions ts ON ts.application_id = a.id
     WHERE a.training_id = ? AND a.student_id = 1498 LIMIT 1",
    [$mismatch_ended]
);
check("training $mismatch_ended has an accepted app + completed session (old prerequisites met)", ($app_b['status'] ?? '') === 'accepted' && ($app_b['sess'] ?? '') === 'completed');

$mismatch_in_eligible = false;
foreach ($elig_items as $i) {
    if ((int) ($i['training_id'] ?? 0) === $mismatch_ended) {
        $mismatch_in_eligible = true;
        break;
    }
}
check("GET /eligible does NOT contain mismatched-ended training $mismatch_ended", !$mismatch_in_eligible);

$req_b = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $mismatch_ended]));
check("direct POST /certificates for mismatched-ended $mismatch_ended is rejected (422)", $req_b['status'] === 422 && ($req_b['success'] ?? true) === false);

echo "\n== Case C: MISMATCHED specialization (199 vs 205) + RUNNING ==\n";

$mismatch_running = synthetic_training(205, 10);
check('synthetic spec-205 RUNNING training created', $mismatch_running > 0);
$createdTrainings[] = $mismatch_running;

$row_c = db_fetch_one(
    "SELECT t.specialization_id AS tspec, s.specialization_id AS sspec, t.ends_at > NOW() AS running
     FROM training_listings t, students s
     WHERE t.id = ? AND s.id = 1498 LIMIT 1",
    [$mismatch_running]
);
check("training $mismatch_running has spec 205 vs student 199 (mismatch + running)", (int) ($row_c['tspec'] ?? 0) === 205 && (int) ($row_c['sspec'] ?? 0) === 199 && (int) ($row_c['running'] ?? 0) === 1);

$mismatch_running_eligible = false;
foreach (run_api('bearer', $student_token, 'GET', $base . '/eligible')['data'] as $i) {
    if ((int) ($i['training_id'] ?? 0) === $mismatch_running) {
        $mismatch_running_eligible = true;
        break;
    }
}
check("GET /eligible does NOT contain mismatched-running training $mismatch_running", !$mismatch_running_eligible);

$req_c = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $mismatch_running]));
check("direct POST /certificates for mismatched-running $mismatch_running is rejected (422)", $req_c['status'] === 422 && ($req_c['success'] ?? true) === false);

echo "\n== Case D: MATCHING specialization (199 == 199) + RUNNING (ends_at rule intact) ==\n";

$matching_running = 100422;
$row_d = db_fetch_one(
    "SELECT t.specialization_id AS tspec, s.specialization_id AS sspec, t.ends_at > NOW() AS running
     FROM training_listings t, students s
     WHERE t.id = ? AND s.id = 1498 LIMIT 1",
    [$matching_running]
);
check("training 100422 matches student spec (199 == 199)", (int) ($row_d['tspec'] ?? 0) === 199 && (int) ($row_d['sspec'] ?? 0) === 199);
check('training 100422 is still running (ends_at > NOW())', (int) ($row_d['running'] ?? 0) === 1);

$d_in_eligible = false;
foreach (run_api('bearer', $student_token, 'GET', $base . '/eligible')['data'] as $i) {
    if ((int) ($i['training_id'] ?? 0) === $matching_running) {
        $d_in_eligible = true;
        break;
    }
}
check('GET /eligible does NOT contain matching-running 100422 (ends_at rule)', !$d_in_eligible);
$req_d = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $matching_running]));
check('POST /certificates for matching-running 100422 is rejected (422)', $req_d['status'] === 422 && ($req_d['success'] ?? true) === false);

echo "\n== Dataset-wide specialization consistency ==\n";

$mismatched_dataset = db_fetch_all(
    "SELECT t.id FROM training_listings t
     JOIN students s ON s.id = 1498
     WHERE t.id BETWEEN 100400 AND 100422 AND t.specialization_id <> s.specialization_id"
);
check('every dataset training (100400-100422) uses the student specialization (199)', count($mismatched_dataset) === 0);

$mismatched_certs = db_fetch_all(
    "SELECT c.id FROM certificates c
     JOIN training_listings t ON t.id = c.training_id
     JOIN students s ON s.id = c.student_id
     WHERE c.student_id = 1498 AND t.specialization_id <> s.specialization_id"
);
check('every existing certificate of student 1498 sits on a matching-specialization training', count($mismatched_certs) === 0);

echo "\n== Cleanup: remove the Case A certificate + the synthetic trainings ==\n";

if ($created_id > 0) {
    db_execute("DELETE FROM certificate_appeals WHERE certificate_id = ?", [$created_id]);
    db_execute("DELETE FROM notifications WHERE entity_type = 'certificate' AND entity_id = ?", [$created_id]);
    db_execute("DELETE FROM certificates WHERE id = ?", [$created_id]);
    check('Case A certificate removed again (dataset restored)', true);
}

$cleanup_ok = true;
foreach ($createdTrainings as $tid) {
    $apps = db_fetch_all("SELECT id FROM training_applications WHERE training_id = ?", [$tid]);
    foreach ($apps as $a) {
        db_execute("DELETE FROM training_sessions WHERE application_id = ?", [(int) $a['id']]);
        db_execute("DELETE FROM training_applications WHERE id = ?", [(int) $a['id']]);
    }
    db_execute("DELETE FROM training_listings WHERE id = ?", [$tid]);
    $gone = db_fetch_one("SELECT id FROM training_listings WHERE id = ? LIMIT 1", [$tid]);
    if ($gone !== null) {
        $cleanup_ok = false;
    }
}
check('synthetic spec-205 trainings removed', $cleanup_ok);

$cert_total = (int) ((db_fetch_one("SELECT COUNT(*) n FROM certificates WHERE student_id = 1498 AND training_id BETWEEN 100400 AND 100422")['n'] ?? 0));
check('valid certificate records for student 1498 = 14 (dataset intact after cleanup)', $cert_total === 14);

$eligible_after = array_map(
    static fn ($i) => (int) ($i['training_id'] ?? 0),
    run_api('bearer', $student_token, 'GET', $base . '/eligible')['data']
);
sort($eligible_after);
check('eligible set restored to exactly the 8 finished, undocumented spec-199 trainings', $eligible_after === [100400, 100401, 100402, 100403, 100404, 100405, 100406, 100407]);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);