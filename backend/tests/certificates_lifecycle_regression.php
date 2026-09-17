<?php

/**
 * MASAR - Certificates Full Lifecycle Regression Test
 *
 * Drives the certificate API over the HTTP request shim and verifies the
 * whole lifecycle contract against the REAL database dataset:
 *
 *   eligible -> pending (request) -> issued (confirm) -> revoked
 *
 *   Real data used:
 *     - company@test.local = TestHire Solutions (company 100161)
 *     - admin@masar.eg = real admin
 *     - mammuslim2003@gmail.com (student 1498) = lifecycle driver A. Its
 *       lifecycle drivers are resolved at runtime from the large realistic
 *       dataset seeder (tests/certificates_test_data_seeder.php): two TestHire
 *       trainings (free + paid) that are FINISHED (ends_at <= NOW()), with an
 *       accepted application + completed session and no certificate row yet.
 *       Defaults: free 100400, paid 100404 (resolved by lowest stable id).
 *     - sara.fahim@gmail.com (student 1464) = isolation driver B (empty lists)
 *     - a second real company user = the non-owning 403 actor.
 *
 *   Eligibility is time-gated on training_listings.ends_at - a training whose
 *   ends_at is still in the future is NOT eligible/requestable. The dataset
 *   keeps 100422 as an unfinished (running) training to prove this outside
 *   this run (see certificates_eligibility_timing_regression.php).
 *
 *   The generators (applications, sessions, trainings, certificates) are owned
 *   by the dataset seeder and are never inserted/deleted by this script. This
 *   script creates only its own two certificate rows (free + paid drivers),
 *   drives them to revoked, then removes exactly those two rows + their
 *   appeals/notices. Statistics/search assertions use baselines captured at
 *   startup, so a previously converged dataset never breaks this regression.
 *
 *   Coverage:
 *   Auth & scoping        guest 401s; student cannot confirm/revoke; only the
 *                         owning company (or admin) can confirm/revoke; other
 *                         students cannot view foreign certificates.
 *   Capabilities          can_request / can_view match each state;
 *                         can_download is ALWAYS false (view-only module,
 *                         download route removed).
 *                         certificate_number + issued_at are exposed
 *                         ONLY once issued; is_paid mirrors
 *                         training_listings.is_paid in every state.
 *   Duplicate protection  one certificate per (student, training) in any state,
 *                         including revoked.
 *   Pending directory    GET /certificates/pending is the ONLY dedicated pending
 *                         endpoint: it returns ONLY status=pending certificates
 *                         for the authenticated user; scope is auth-derived.
 *   Issued directory     GET /certificates/issued - ONLY status=issued, scope
 *                         auth-derived.
 *   Revoked directory    GET /certificates/revoked - ONLY status=revoked, kept
 *                         stable number + original issued_at + reason.
 *   Statistics           GET /certificates/statistics - scoped counts per
 *                         status; client student_id/company_id ignored.
 *   Search               GET /certificates/search - keyword matches the
 *                         certificate title/code; scope auth-derived; 422
 *                         without a keyword.
 *
 * Run from the backend root:
 *     php tests/certificates_lifecycle_regression.php
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
        $body_file = tempnam(sys_get_temp_dir(), 'certcred');
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

echo "== Setup: resolve the real certificate dataset ==\n";

$data = cf_prepare_lifecycle_dataset();

check('real dataset resolved without mutation conflict', $data['ok'] === true && $data['reason'] === '');

$training_id      = (int) $data['training_id'];
$training_title   = (string) $data['training_title'];
$paid_training_id = (int) $data['paid_training_id'];
$paid_training_title = (string) $data['paid_training_title'];
$company_id       = (int) $data['company_id'];
$company_user_id  = (int) $data['company_user_id'];
$admin_user_id    = (int) $data['admin_user_id'];
$a_uid = (int) $data['student_a']['user_id'];
$a_sid = (int) $data['student_a']['student_id'];
$b_uid = (int) $data['student_b']['user_id'];
$b_sid = (int) $data['student_b']['student_id'];

$other = cf_other_company_user($company_id);
$other_user_id = (int) $other['user_id'];

check('real free driver training discovered', $training_id > 0);
check('real paid driver training discovered (distinct)', $paid_training_id > 0 && $paid_training_id !== $training_id);
check('driver trainings are real records (fixture removed)', stripos($training_title, 'Backend Certificate Lifecycle') === false);
check('company@test.local (TestHire Solutions) resolved', $company_id === 100161 && $company_user_id > 0);
check('admin@masar.eg resolved', $admin_user_id > 0);
check('student A resolved (mammuslim2003@gmail.com)', $a_uid > 0 && $a_sid === 1498);
check('student B resolved (sara.fahim@gmail.com)', $b_uid > 0 && $b_sid === 1464);
check('non-owning company resolved for 403 tests', $other_user_id > 0 && $other_user_id !== $company_user_id);
check('driver trainings resolve with their dataset completed sessions', (int) $data['driver_session_free'] > 0 && (int) $data['driver_session_paid'] > 0);

$student_token = jwt_issue_access_token(['id' => $a_uid, 'role' => 'student']);
$sara_token    = jwt_issue_access_token(['id' => $b_uid, 'role' => 'student']);
$company_token = jwt_issue_access_token(['id' => $company_user_id, 'role' => 'company']);
$other_token   = jwt_issue_access_token(['id' => $other_user_id, 'role' => 'company']);
$admin_token   = jwt_issue_access_token(['id' => $admin_user_id, 'role' => 'admin']);

$base = '/api/v1/certificates';

echo "\n== Case group 1: authentication & eligibility discovery ==\n";

$guest_list = run_api('guest', '', 'GET', $base);
check('guest list returns HTTP 401', $guest_list['status'] === 401);

$guest_eligible = run_api('guest', '', 'GET', $base . '/eligible');
check('guest eligible returns HTTP 401', $guest_eligible['status'] === 401);

$guest_request = run_api('guest', '', 'POST', $base, json_encode(['training_id' => $training_id]));
check('guest request returns HTTP 401', $guest_request['status'] === 401);

$elig_a = run_api('bearer', $student_token, 'GET', $base . '/eligible');
check('student A eligible returns HTTP 200', $elig_a['status'] === 200);
$elig_a_items = is_array($elig_a['data']) ? $elig_a['data'] : [];
check('student A sees the real free driver training as eligible', count(array_filter($elig_a_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $training_id)) === 1);
$elig_a_item = null;
foreach ($elig_a_items as $i) {
    if ((int) ($i['training_id'] ?? 0) === $training_id) {
        $elig_a_item = $i;
        break;
    }
}
check('free eligible item marks status eligible', is_array($elig_a_item) && ($elig_a_item['status'] ?? '') === 'eligible');
check('free eligible item can_request=true', is_array($elig_a_item) && ($elig_a_item['can_request'] ?? false) === true);
check('free eligible item can_download=false', is_array($elig_a_item) && ($elig_a_item['can_download'] ?? true) === false);

$elig_a_paid = array_values(array_filter($elig_a_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $paid_training_id));
check('student A sees the real paid driver training as eligible', count($elig_a_paid) === 1);
$elig_a_paid_item = $elig_a_paid[0] ?? null;
check('paid eligible item marks status eligible', is_array($elig_a_paid_item) && ($elig_a_paid_item['status'] ?? '') === 'eligible');
check('paid eligible item is_paid=true', is_array($elig_a_paid_item) && ($elig_a_paid_item['is_paid'] ?? null) === true);
check('free eligible item is_paid=false', is_array($elig_a_item) && ($elig_a_item['is_paid'] ?? null) === false);
check('eligible is_paid is a strict boolean', is_array($elig_a_item) && is_bool($elig_a_item['is_paid'] ?? null) && is_array($elig_a_paid_item) && is_bool($elig_a_paid_item['is_paid'] ?? null));

$free_db_paid = (bool) ((int) (db_fetch_one("SELECT is_paid FROM training_listings WHERE id = ? LIMIT 1", [$training_id])['is_paid'] ?? 0));
$paid_db_paid = (bool) ((int) (db_fetch_one("SELECT is_paid FROM training_listings WHERE id = ? LIMIT 1", [$paid_training_id])['is_paid'] ?? 0));
check('free eligible is_paid matches the DB training flag', $free_db_paid === false && ($elig_a_item['is_paid'] ?? null) === false);
check('paid eligible is_paid matches the DB training flag', $paid_db_paid === true && ($elig_a_paid_item['is_paid'] ?? null) === true);

$elig_paid_ok = true;
foreach ($elig_a_items as $i) {
    $tid = (int) ($i['training_id'] ?? 0);
    $db_row = $tid > 0 ? db_fetch_one("SELECT is_paid FROM training_listings WHERE id = ? LIMIT 1", [$tid]) : null;
    $expected = (bool) ((int) ($db_row['is_paid'] ?? 0));
    if ((bool) ($i['is_paid'] ?? false) !== $expected) {
        $elig_paid_ok = false;
        break;
    }
}
check('every eligible item is_paid matches its DB training flag', $elig_paid_ok);

$elig_b = run_api('bearer', $sara_token, 'GET', $base . '/eligible');
check('student B (isolation) /eligible returns 200 with an empty list', $elig_b['status'] === 200 && is_array($elig_b['data']) && count($elig_b['data']) === 0);

$stats_initial = run_api('bearer', $student_token, 'GET', $base . '/statistics');
check('student A statistics expose the seeded dataset (multiple certs)', $stats_initial['status'] === 200 && is_array($stats_initial['data']) && (int) ($stats_initial['data']['total'] ?? -1) >= 8);

// Baselines captured BEFORE this run mutates anything, so every later
// statistics/search assertion is a delta on the already-seeded dataset.
$base_a = [
    'total'   => (int) ($stats_initial['data']['total'] ?? 0),
    'pending' => (int) ($stats_initial['data']['pending'] ?? 0),
    'issued'  => (int) ($stats_initial['data']['issued'] ?? 0),
    'revoked' => (int) ($stats_initial['data']['revoked'] ?? 0),
    'active'  => (int) ($stats_initial['data']['active'] ?? 0),
    'valid'   => (int) ($stats_initial['data']['valid'] ?? 0),
];
$stats_company_base = run_api('bearer', $company_token, 'GET', $base . '/statistics');
$base_c_total = (int) (($stats_company_base['data']['total'] ?? 0));
$base_c_revoked = (int) (($stats_company_base['data']['revoked'] ?? 0));
$stats_admin_base = run_api('bearer', $admin_token, 'GET', $base . '/statistics');
$base_am_total = (int) (($stats_admin_base['data']['total'] ?? 0));
$base_am_revoked = (int) (($stats_admin_base['data']['revoked'] ?? 0));
$base_cert_count = (int) ((db_fetch_one("SELECT COUNT(*) AS n FROM certificates"))['n'] ?? 0);
$base_cert_count_a = (int) ((db_fetch_one("SELECT COUNT(*) AS n FROM certificates WHERE student_id = ?", [$a_sid]))['n'] ?? 0);

check('baseline certificate counts captured (dataset present for student A)', $base_cert_count_a >= 8 && $base_cert_count >= $base_cert_count_a);

$elig_company = run_api('bearer', $company_token, 'GET', $base . '/eligible');
check('company eligible returns HTTP 200', $elig_company['status'] === 200);
$elig_company_items = is_array($elig_company['data']) ? $elig_company['data'] : [];
check('company dashboard sees student A as eligible on the free driver', count(array_filter($elig_company_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $training_id)) === 1);
check('company dashboard sees student A as eligible on the paid driver', count(array_filter($elig_company_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $paid_training_id)) === 1);

check('eligible cannot view a certificate that was never requested', run_api('bearer', $student_token, 'GET', $base . '/99999999')['status'] === 404);
check('eligible cannot download (old download route is gone)', run_api('bearer', $student_token, 'GET', $base . '/99999999/download')['status'] === 404);

echo "\n== Case group 2: student requests (pending) ==\n";

$tr_db = db_fetch_one("SELECT title, specialization_id, company_id FROM training_listings WHERE id = ? LIMIT 1", [$training_id]);
$co_db = db_fetch_one("SELECT legal_name FROM companies WHERE id = ? LIMIT 1", [(int) ($tr_db['company_id'] ?? 0)]);
$sp_db = db_fetch_one("SELECT name FROM specializations WHERE id = ? LIMIT 1", [(int) ($tr_db['specialization_id'] ?? 0)]);
$a_student_db = db_fetch_one(
    "SELECT s.id, s.full_name, s.phone, s.city, s.university_id, s.field_id, s.specialization_id, u.email
     FROM students s
     JOIN users u ON u.id = s.user_id
     WHERE s.id = ? LIMIT 1",
    [$a_sid]
);
$un_db = db_fetch_one("SELECT name FROM universities WHERE id = ? LIMIT 1", [(int) ($a_student_db['university_id'] ?? 0)]);
$fi_db = db_fetch_one("SELECT name FROM study_fields WHERE id = ? LIMIT 1", [(int) ($a_student_db['field_id'] ?? 0)]);
$sp2_db = db_fetch_one("SELECT name FROM specializations WHERE id = ? LIMIT 1", [(int) ($a_student_db['specialization_id'] ?? 0)]);

$request_a = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $training_id]));
check('student A request returns HTTP 201', $request_a['status'] === 201);
check('student A request returns success=true', $request_a['success'] === true);
$req_a = is_array($request_a['data']) ? $request_a['data'] : [];
$cert_a_id = (int) ($req_a['id'] ?? 0);
check('request returns a certificate id', $cert_a_id > 0);
check('request creates status pending', ($req_a['status'] ?? '') === 'pending');
check('pending request requested_at present', ($req_a['requested_at'] ?? null) !== null);
check('pending request company_name matches the training company', is_array($req_a) && ($req_a['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('pending request training_title matches the training record', is_array($req_a) && ($req_a['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('pending request specialization_id matches the training', is_array($req_a) && (int) ($req_a['specialization_id'] ?? 0) === (int) ($tr_db['specialization_id'] ?? 0));
check('pending request specialization_name matches the training specialization', is_array($req_a) && ($req_a['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('pending request student id matches the authenticated student', is_array($req_a) && (int) ($req_a['student']['id'] ?? 0) === (int) $a_sid);
check('pending request student full_name matches the DB student', is_array($req_a) && ($req_a['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('pending request student email matches the DB student', is_array($req_a) && ($req_a['student']['email'] ?? null) === ($a_student_db['email'] ?? null));
check('pending request student phone matches the DB student', is_array($req_a) && ($req_a['student']['phone'] ?? null) === ($a_student_db['phone'] ?? null));
check('pending request student city matches the DB student', is_array($req_a) && ($req_a['student']['city'] ?? null) === ($a_student_db['city'] ?? null));
check('pending request student university matches the DB student', is_array($req_a) && ($req_a['student']['university'] ?? null) === ($un_db['name'] ?? null));
check('pending request student field matches the DB student', is_array($req_a) && ($req_a['student']['field'] ?? null) === ($fi_db['name'] ?? null));
check('pending request student specialization matches the DB student', is_array($req_a) && ($req_a['student']['specialization'] ?? null) === ($sp2_db['name'] ?? null));
check('pending cert does NOT expose certificate_number', !array_key_exists('certificate_number', $req_a));
check('pending cert does NOT expose issued_at', !array_key_exists('issued_at', $req_a));
check('pending cert can_view=true', ($req_a['can_view'] ?? false) === true);
check('pending cert can_download=false', ($req_a['can_download'] ?? true) === false);

$dup = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $training_id]));
check('duplicate request returns HTTP 409', $dup['status'] === 409);
check('duplicate request body mentions already requested', stripos($dup['body'], 'already been requested') !== false);

$list_a = run_api('bearer', $student_token, 'GET', $base);
check('student A list returns HTTP 200', $list_a['status'] === 200);
$list_a_items = is_array($list_a['data']) ? $list_a['data'] : [];
$matching = array_filter($list_a_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id);
check('list contains the pending certificate', count($matching) === 1);
$pending_view = reset($matching);
check('list item hides certificate_number while pending', is_array($pending_view) && !array_key_exists('certificate_number', $pending_view));
check('pending list item requested_at present', is_array($pending_view) && ($pending_view['requested_at'] ?? null) !== null);
check('pending list item company_name matches the training company', is_array($pending_view) && ($pending_view['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('pending list item training_title matches the training record', is_array($pending_view) && ($pending_view['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('pending list item specialization_name matches the training specialization', is_array($pending_view) && ($pending_view['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('pending list item student full_name matches the DB student', is_array($pending_view) && ($pending_view['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));

$elig_a2 = run_api('bearer', $student_token, 'GET', $base . '/eligible');
$elig_a2_items = is_array($elig_a2['data']) ? $elig_a2['data'] : [];
check('eligible no longer lists the requested free training', count(array_filter($elig_a2_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $training_id)) === 0);
check('eligible now lists only the still-eligible paid training', count(array_filter($elig_a2_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $paid_training_id)) === 1);

$elig_company2 = run_api('bearer', $company_token, 'GET', $base . '/eligible');
$elig_company2_items = is_array($elig_company2['data']) ? $elig_company2['data'] : [];
check('company dashboard eligible count drops to 0 for the free training after request', count(array_filter($elig_company2_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $training_id)) === 0);
check('company dashboard still lists the eligible paid training', count(array_filter($elig_company2_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $paid_training_id)) === 1);

echo "\n== Case group 2b: dedicated pending endpoint (GET /certificates/pending) ==\n";

$pending_guest = run_api('guest', '', 'GET', $base . '/pending');
check('pending endpoint requires auth (guest 401)', $pending_guest['status'] === 401);

$pending_endpoint = run_api('bearer', $student_token, 'GET', $base . '/pending');
check('pending endpoint returns HTTP 200', $pending_endpoint['status'] === 200);
check('pending endpoint returns success=true', $pending_endpoint['success'] === true);
$pending_items = is_array($pending_endpoint['data']) ? $pending_endpoint['data'] : [];
check('pending endpoint returns only pending records (Case B)', is_array($pending_items) && count(array_filter($pending_items, static fn ($c) => ($c['status'] ?? '') !== 'pending')) === 0);
check('pending endpoint contains no issued/revoked/eligible records', is_array($pending_items) && count(array_filter($pending_items, static fn ($c) => in_array(($c['status'] ?? ''), ['issued', 'revoked', 'eligible'], true))) === 0);

$pending_row_a = array_values(array_filter($pending_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id));
check('pending endpoint contains the pending request (Case A)', count($pending_row_a) === 1);
$pending_row_a = $pending_row_a[0] ?? null;
check('pending endpoint item status=pending', is_array($pending_row_a) && ($pending_row_a['status'] ?? '') === 'pending');

$pending_required = ['id', 'certificate_id', 'status', 'student_id', 'company_id', 'company_name', 'training_id', 'training_title', 'specialization_id', 'specialization_name', 'requested_at', 'is_paid', 'can_request', 'can_view', 'can_download', 'student'];
$pending_missing = is_array($pending_row_a) ? array_values(array_filter($pending_required, static fn ($f) => !array_key_exists($f, $pending_row_a))) : $pending_required;
check('pending item exposes every required field (Case C)', is_array($pending_row_a) && count($pending_missing) === 0);

check('pending endpoint item company_id matches the training company', is_array($pending_row_a) && (int) ($pending_row_a['company_id'] ?? 0) === (int) ($tr_db['company_id'] ?? 0));
check('pending endpoint item company_name matches the training company', is_array($pending_row_a) && ($pending_row_a['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('pending endpoint item training_id matches the training record', is_array($pending_row_a) && (int) ($pending_row_a['training_id'] ?? 0) === (int) $training_id);
check('pending endpoint item training_title matches the training record', is_array($pending_row_a) && ($pending_row_a['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('pending endpoint item specialization_id matches the training', is_array($pending_row_a) && (int) ($pending_row_a['specialization_id'] ?? 0) === (int) ($tr_db['specialization_id'] ?? 0));
check('pending endpoint item specialization_name matches the training specialization', is_array($pending_row_a) && ($pending_row_a['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('pending endpoint item student_id matches the authenticated student', is_array($pending_row_a) && (int) ($pending_row_a['student_id'] ?? 0) === (int) $a_sid);
check('pending endpoint requested_at present', is_array($pending_row_a) && !empty($pending_row_a['requested_at']));
check('pending endpoint hides certificate_number while pending', is_array($pending_row_a) && !array_key_exists('certificate_number', $pending_row_a));
check('pending endpoint hides issued_at while pending', is_array($pending_row_a) && !array_key_exists('issued_at', $pending_row_a));
check('pending endpoint hides revoked_at while pending', is_array($pending_row_a) && !array_key_exists('revoked_at', $pending_row_a));
check('pending endpoint hides revocation_reason while pending', is_array($pending_row_a) && !array_key_exists('revocation_reason', $pending_row_a));
check('pending endpoint is_paid is a strict boolean', is_array($pending_row_a) && is_bool($pending_row_a['is_paid'] ?? null));
check('pending endpoint is_paid=false for the real free training (Case G)', is_array($pending_row_a) && ($pending_row_a['is_paid'] ?? true) === false);
check('pending endpoint can_request=false (Case D)', is_array($pending_row_a) && ($pending_row_a['can_request'] ?? true) === false);
check('pending endpoint can_view=true (Case D)', is_array($pending_row_a) && ($pending_row_a['can_view'] ?? false) === true);
check('pending endpoint can_download=false (Case D)', is_array($pending_row_a) && ($pending_row_a['can_download'] ?? true) === false);
check('pending endpoint student block is present', is_array($pending_row_a) && is_array($pending_row_a['student'] ?? null));
check('pending endpoint student id matches the authenticated student', is_array($pending_row_a) && (int) ($pending_row_a['student']['id'] ?? 0) === (int) $a_sid);
check('pending endpoint student full_name matches the DB student', is_array($pending_row_a) && ($pending_row_a['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('pending endpoint does NOT expose may_lead_to_hire', is_array($pending_row_a) && !array_key_exists('may_lead_to_hire', $pending_row_a));
check('pending endpoint exposes no download_url (Case I)', is_array($pending_row_a) && !array_key_exists('download_url', $pending_row_a));

$pending_b = run_api('bearer', $sara_token, 'GET', $base . '/pending');
check('pending endpoint isolates students (student B sees none of student A)', $pending_b['status'] === 200 && is_array($pending_b['data']) && count(array_filter($pending_b['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);
check('pending endpoint empty result is a normal 200 empty list', $pending_b['status'] === 200 && is_array($pending_b['data']) && count($pending_b['data']) === 0 && $pending_b['success'] === true);

$pending_spoof = run_api('bearer', $student_token, 'GET', $base . '/pending?student_id=' . $b_sid);
check('pending endpoint ignores client student_id (still own cert only)', is_array($pending_spoof['data']) && count(array_filter($pending_spoof['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1 && count(array_filter($pending_spoof['data'], static fn ($c) => ($c['status'] ?? '') !== 'pending')) === 0);

$pending_foreign_show = run_api('bearer', $sara_token, 'GET', $base . '/' . $cert_a_id);
check('student B cannot view student A pending cert (403, Case F)', $pending_foreign_show['status'] === 403);

$pending_view_owner = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id);
$pending_view_owner_data = is_array($pending_view_owner['data']) ? $pending_view_owner['data'] : [];
check('pending preview (GET /{id}) returns 200 for owner', $pending_view_owner['status'] === 200);
check('pending preview status=pending (Case E)', is_array($pending_view_owner_data) && ($pending_view_owner_data['status'] ?? '') === 'pending');
check('pending preview can_view=true and can_download=false (Case E)', is_array($pending_view_owner_data) && ($pending_view_owner_data['can_view'] ?? false) === true && ($pending_view_owner_data['can_download'] ?? true) === false);
check('pending preview hides certificate_number', is_array($pending_view_owner_data) && !array_key_exists('certificate_number', $pending_view_owner_data));
check('pending preview training/company/specialization/student match the pending item', is_array($pending_view_owner_data) && ($pending_view_owner_data['training_title'] ?? null) === ($pending_row_a['training_title'] ?? null) && ($pending_view_owner_data['company_name'] ?? null) === ($pending_row_a['company_name'] ?? null) && ($pending_view_owner_data['specialization_name'] ?? null) === ($pending_row_a['specialization_name'] ?? null) && ($pending_view_owner_data['student']['id'] ?? null) === ($pending_row_a['student']['id'] ?? null));

check('pending cert cannot be downloaded (route not found, Case I)', run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id . '/download')['status'] === 404);

echo "\n== Case group 3: authorization on the pending certificate ==\n";

$foreign_show = run_api('bearer', $sara_token, 'GET', $base . '/' . $cert_a_id);
check('student B cannot view student A pending cert (403)', $foreign_show['status'] === 403);

$student_confirm = run_api('bearer', $student_token, 'POST', $base . '/' . $cert_a_id . '/confirm');
check('student cannot confirm own pending cert (403)', $student_confirm['status'] === 403);

$other_confirm = run_api('bearer', $other_token, 'POST', $base . '/' . $cert_a_id . '/confirm');
check('non-owning company cannot confirm (403)', $other_confirm['status'] === 403);

$show_pending = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id);
$show_pending_data = is_array($show_pending['data']) ? $show_pending['data'] : [];
check('pending cert can be viewed by owner (200)', $show_pending['status'] === 200);
check('pending view hides certificate_number', is_array($show_pending_data) && !array_key_exists('certificate_number', $show_pending_data));
check('pending view company_name matches the training company', is_array($show_pending_data) && ($show_pending_data['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('pending view training_title matches the training record', is_array($show_pending_data) && ($show_pending_data['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('pending view specialization_name matches the training specialization', is_array($show_pending_data) && ($show_pending_data['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('pending view student full_name matches the DB student', is_array($show_pending_data) && ($show_pending_data['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('pending view can_view=true', is_array($show_pending_data) && ($show_pending_data['can_view'] ?? false) === true);
check('pending view can_download=false', is_array($show_pending_data) && ($show_pending_data['can_download'] ?? true) === false);

echo "\n== Case group 4: download is removed while pending ==\n";

$dl_pending = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id . '/download');
check('download while pending returns route-not-found (404)', $dl_pending['status'] === 404);
check('download while pending returns JSON (no certificate file)', stripos($dl_pending['body'], '{"success":false') !== false);
check('pending preview does not claim issued status', ($show_pending_data['status'] ?? '') === 'pending');

echo "\n== Case group 5: company confirms (issued) ==\n";

$confirm = run_api('bearer', $company_token, 'POST', $base . '/' . $cert_a_id . '/confirm');
check('owning company confirm returns HTTP 200', $confirm['status'] === 200);
check('owning company confirm returns success=true', $confirm['success'] === true);
$conf_data = is_array($confirm['data']) ? $confirm['data'] : [];
check('confirm moves status to issued', ($conf_data['status'] ?? '') === 'issued');
check('issued cert exposes certificate_number', is_array($conf_data) && is_string($conf_data['certificate_number'] ?? null));
check('certificate_number matches MASAR format', (bool) preg_match('/^MASAR-\d{4}-[0-9A-F]{8}$/', (string) ($conf_data['certificate_number'] ?? '')));
check('issued cert exposes issued_at', is_array($conf_data) && !empty($conf_data['issued_at']));
check('issued cert can_download=false (view-only)', ($conf_data['can_download'] ?? true) === false);

$issued_number_a = (string) ($conf_data['certificate_number'] ?? '');

check('issued cert is_paid is a strict boolean', is_array($conf_data) && is_bool($conf_data['is_paid'] ?? null));
check('issued free cert is_paid=false', ($conf_data['is_paid'] ?? true) === false);
$free_db_flag = (bool) ((int) (db_fetch_one("SELECT is_paid FROM training_listings WHERE id = ? LIMIT 1", [$training_id])['is_paid'] ?? 0));
check('free issued is_paid matches the real DB training flag (false)', $free_db_flag === false && ($conf_data['is_paid'] ?? true) === false);
check('issued cert preserves the requested_at from the request', ($conf_data['requested_at'] ?? null) === ($req_a['requested_at'] ?? null));
check('issued cert can_request=false', ($conf_data['can_request'] ?? true) === false);
check('issued cert can_view=true', ($conf_data['can_view'] ?? false) === true);
check('issued cert company_name matches the training company', ($conf_data['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('issued cert training_title matches the training record', ($conf_data['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('issued cert specialization_id matches the training', is_array($conf_data) && (int) ($conf_data['specialization_id'] ?? 0) === (int) ($tr_db['specialization_id'] ?? 0));
check('issued cert specialization_name matches the training specialization', ($conf_data['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('issued cert student block is present', is_array($conf_data['student'] ?? null));
check('issued cert student full_name matches the DB student', ($conf_data['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('issued cert student email matches the DB student', ($conf_data['student']['email'] ?? null) === ($a_student_db['email'] ?? null));
check('issued cert does NOT expose may_lead_to_hire', !array_key_exists('may_lead_to_hire', $conf_data));

$confirm_again = run_api('bearer', $company_token, 'POST', $base . '/' . $cert_a_id . '/confirm');
check('confirm on an issued cert returns HTTP 409', $confirm_again['status'] === 409);

$show_issued = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id);
$show_issued_data = is_array($show_issued['data']) ? $show_issued['data'] : [];
check('student A sees issued cert (200)', $show_issued['status'] === 200);
check('issued view exposes certificate_number', is_array($show_issued_data) && is_string($show_issued_data['certificate_number'] ?? null));
check('issued view certificate_number equals the confirmation value (stable)', ($show_issued_data['certificate_number'] ?? null) === $issued_number_a);
check('issued view exposes issued_at', is_array($show_issued_data) && !empty($show_issued_data['issued_at']));
check('issued view can_view=true', is_array($show_issued_data) && ($show_issued_data['can_view'] ?? false) === true);
check('issued view can_download=false', is_array($show_issued_data) && ($show_issued_data['can_download'] ?? true) === false);
check('issued view is_paid=false for the real free training', ($show_issued_data['is_paid'] ?? true) === false);

$foreign_show_issued = run_api('bearer', $sara_token, 'GET', $base . '/' . $cert_a_id);
check('student B cannot view student A issued cert (403)', $foreign_show_issued['status'] === 403);

echo "\n== Case group 6: dedicated issued endpoint (GET /certificates/issued) ==\n";

$issued_guest = run_api('guest', '', 'GET', $base . '/issued');
check('issued endpoint requires auth (guest 401)', $issued_guest['status'] === 401);

$issued_list = run_api('bearer', $student_token, 'GET', $base . '/issued');
check('issued endpoint returns HTTP 200', $issued_list['status'] === 200);
check('issued endpoint returns success=true', $issued_list['success'] === true);
$issued_list_items = is_array($issued_list['data']) ? $issued_list['data'] : [];
check('issued endpoint returns only issued certificates', is_array($issued_list_items) && count(array_filter($issued_list_items, static fn ($c) => ($c['status'] ?? '') !== 'issued')) === 0);
check('issued endpoint contains no pending/revoked/eligible records', is_array($issued_list_items) && count(array_filter($issued_list_items, static fn ($c) => in_array(($c['status'] ?? ''), ['pending', 'revoked', 'eligible'], true))) === 0);

$issued_row_a = array_values(array_filter($issued_list_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id));
check('issued endpoint contains the issued certificate', count($issued_row_a) === 1);
$issued_row_a = $issued_row_a[0] ?? null;
check('issued endpoint item status=issued', is_array($issued_row_a) && ($issued_row_a['status'] ?? '') === 'issued');
check('issued endpoint item exposes certificate_number', is_array($issued_row_a) && is_string($issued_row_a['certificate_number'] ?? null));
check('issued endpoint certificate_number stable vs confirm value', is_array($issued_row_a) && ($issued_row_a['certificate_number'] ?? null) === $issued_number_a);
check('issued endpoint certificate_number stable vs GET /{id}', is_array($issued_row_a) && ($issued_row_a['certificate_number'] ?? null) === ($show_issued_data['certificate_number'] ?? null));
check('issued endpoint item exposes issued_at', is_array($issued_row_a) && !empty($issued_row_a['issued_at']));
check('issued endpoint item exposes requested_at', is_array($issued_row_a) && !empty($issued_row_a['requested_at']));
check('issued endpoint company_name matches the training company', is_array($issued_row_a) && ($issued_row_a['company_name'] ?? null) === ($co_db['legal_name'] ?? null));
check('issued endpoint training_title matches the training record', is_array($issued_row_a) && ($issued_row_a['training_title'] ?? null) === ($tr_db['title'] ?? null));
check('issued endpoint specialization_id matches the training', is_array($issued_row_a) && (int) ($issued_row_a['specialization_id'] ?? 0) === (int) ($tr_db['specialization_id'] ?? 0));
check('issued endpoint specialization_name matches the training specialization', is_array($issued_row_a) && ($issued_row_a['specialization_name'] ?? null) === ($sp_db['name'] ?? null));
check('issued endpoint is_paid is a strict boolean', is_array($issued_row_a) && is_bool($issued_row_a['is_paid'] ?? null));
check('issued endpoint is_paid=false for the real free training', is_array($issued_row_a) && ($issued_row_a['is_paid'] ?? true) === false);
check('issued endpoint student block is present', is_array($issued_row_a) && is_array($issued_row_a['student'] ?? null));
check('issued endpoint student full_name matches the DB student', is_array($issued_row_a) && ($issued_row_a['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('issued endpoint does NOT expose may_lead_to_hire', is_array($issued_row_a) && !array_key_exists('may_lead_to_hire', $issued_row_a));
check('issued endpoint exposes no download_url', is_array($issued_row_a) && !array_key_exists('download_url', $issued_row_a));
check('issued endpoint can_request=false', is_array($issued_row_a) && ($issued_row_a['can_request'] ?? true) === false);
check('issued endpoint can_view=true', is_array($issued_row_a) && ($issued_row_a['can_view'] ?? false) === true);
check('issued endpoint can_download=false', is_array($issued_row_a) && ($issued_row_a['can_download'] ?? true) === false);

$issued_b = run_api('bearer', $sara_token, 'GET', $base . '/issued');
check('issued endpoint isolates students (student B sees none of student A)', $issued_b['status'] === 200 && is_array($issued_b['data']) && count(array_filter($issued_b['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);
check('issued endpoint empty result is a normal 200 empty list', $issued_b['status'] === 200 && is_array($issued_b['data']) && count($issued_b['data']) === 0 && $issued_b['success'] === true);

$issued_spoof = run_api('bearer', $student_token, 'GET', $base . '/issued?student_id=' . $b_sid);
check('issued endpoint ignores client student_id (still own cert only)', is_array($issued_spoof['data']) && count(array_filter($issued_spoof['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1 && count(array_filter($issued_spoof['data'], static fn ($c) => ($c['status'] ?? '') !== 'issued')) === 0);

$pending_after_issue = run_api('bearer', $student_token, 'GET', $base . '/pending');
check('pending endpoint no longer returns the confirmed cert (now issued)', is_array($pending_after_issue['data']) && count(array_filter($pending_after_issue['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);

echo "\n== Case group 7: paid driver requested + admin confirms (is_paid=true) ==\n";

$paid_req = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $paid_training_id]));
check('student A request on the real paid training returns HTTP 201', $paid_req['status'] === 201);
$paid_req_data = is_array($paid_req['data']) ? $paid_req['data'] : [];
$paid_cert_id = (int) ($paid_req_data['id'] ?? 0);
check('paid pending certificate created', $paid_cert_id > 0);
check('paid pending cert status=pending', ($paid_req_data['status'] ?? '') === 'pending');
check('paid pending cert is_paid=true', ($paid_req_data['is_paid'] ?? false) === true);

$paid_pending_list = run_api('bearer', $student_token, 'GET', $base . '/pending');
$paid_pending_items = is_array($paid_pending_list['data']) ? $paid_pending_list['data'] : [];
check('pending endpoint contains the paid pending cert', count(array_filter($paid_pending_items, static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 1);
check('pending endpoint paid item is_paid=true (Case G)', count(array_filter($paid_pending_items, static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id && ($c['is_paid'] ?? false) === true)) === 1);

$other_confirm_paid = run_api('bearer', $other_token, 'POST', $base . '/' . $paid_cert_id . '/confirm');
check('non-owning company cannot confirm the paid pending cert (403)', $other_confirm_paid['status'] === 403);

$admin_confirm = run_api('bearer', $admin_token, 'POST', $base . '/' . $paid_cert_id . '/confirm');
check('admin confirm returns HTTP 200', $admin_confirm['status'] === 200);
$admin_conf_data = is_array($admin_confirm['data']) ? $admin_confirm['data'] : [];
check('admin confirm issues the pending paid cert', ($admin_conf_data['status'] ?? '') === 'issued');
check('paid issued cert is_paid=true', ($admin_conf_data['is_paid'] ?? false) === true);
check('paid issued cert exposes certificate_number', is_array($admin_conf_data) && is_string($admin_conf_data['certificate_number'] ?? null));
check('paid issued cert exposes issued_at', is_array($admin_conf_data) && !empty($admin_conf_data['issued_at']));
check('paid issued cert can_download=false', ($admin_conf_data['can_download'] ?? true) === false);
$paid_version_number = (string) ($admin_conf_data['certificate_number'] ?? '');
$paid_db_flag = (bool) ((int) (db_fetch_one("SELECT is_paid FROM training_listings WHERE id = ? LIMIT 1", [$paid_training_id])['is_paid'] ?? 0));
check('paid issued is_paid matches the real DB training flag', $paid_db_flag === true && ($admin_conf_data['is_paid'] ?? false) === true);

$other_confirm_after = run_api('bearer', $other_token, 'POST', $base . '/' . $paid_cert_id . '/confirm');
check('non-owning company cannot confirm after issue (403; auth precedes state)', $other_confirm_after['status'] === 403);

$paid_view = run_api('bearer', $student_token, 'GET', $base . '/' . $paid_cert_id);
$paid_view_data = is_array($paid_view['data']) ? $paid_view['data'] : [];
check('paid issued cert view keeps is_paid=true', ($paid_view_data['is_paid'] ?? false) === true);
check('paid issued cert view keeps the same certificate_number (stable)', ($paid_view_data['certificate_number'] ?? null) === $paid_version_number);

$sara_paid_view = run_api('bearer', $sara_token, 'GET', $base . '/' . $paid_cert_id);
check('student B cannot view student A paid issued cert (403)', $sara_paid_view['status'] === 403);

$issued_list_paid = run_api('bearer', $student_token, 'GET', $base . '/issued');
$issued_list_paid_items = is_array($issued_list_paid['data']) ? $issued_list_paid['data'] : [];
check('/issued contains the paid issued cert', count(array_filter($issued_list_paid_items, static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 1);
check('/issued still contains the issued free cert', count(array_filter($issued_list_paid_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$paid_pending_after_confirm = run_api('bearer', $student_token, 'GET', $base . '/pending');
check('pending endpoint no longer returns the confirmed paid cert', is_array($paid_pending_after_confirm['data']) && count(array_filter($paid_pending_after_confirm['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 0);

check('paid issued cert cannot be downloaded (route not found)', run_api('bearer', $student_token, 'GET', $base . '/' . $paid_cert_id . '/download')['status'] === 404);

echo "\n== Case group 8: no download + list visibility ==\n";

$dl_issued = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id . '/download');
check('issued cert cannot be downloaded (route not found)', $dl_issued['status'] === 404);
$dl_issued_company = run_api('bearer', $company_token, 'GET', $base . '/' . $cert_a_id . '/download');
check('company cannot download either (route not found)', $dl_issued_company['status'] === 404);

$list_company = run_api('bearer', $company_token, 'GET', $base);
check('company list returns HTTP 200', $list_company['status'] === 200);
$list_company_items = is_array($list_company['data']) ? $list_company['data'] : [];
$company_row_a = null;
foreach ($list_company_items as $c) {
    if ((int) ($c['id'] ?? 0) === $cert_a_id) {
        $company_row_a = $c;
        break;
    }
}
check('company list contains student A issued cert', is_array($company_row_a));
check('company sees the issued certificate_number', is_array($company_row_a) && is_string($company_row_a['certificate_number'] ?? null));
check('company list certificate_number matches the confirmation value (stable)', is_array($company_row_a) && ($company_row_a['certificate_number'] ?? null) === $issued_number_a);

echo "\n== Case group 9: revocation of the free certificate ==\n";

$revoke_missing_reason = run_api('bearer', $company_token, 'POST', $base . '/' . $cert_a_id . '/revoke');
check('revoke without reason returns HTTP 422', $revoke_missing_reason['status'] === 422);

$revoke = run_api('bearer', $company_token, 'POST', $base . '/' . $cert_a_id . '/revoke', json_encode(['reason' => 'Administrative verification failed']));
check('owning company revoke returns HTTP 200', $revoke['status'] === 200);
check('owning company revoke returns success=true', $revoke['success'] === true);
$rev_data = is_array($revoke['data']) ? $revoke['data'] : [];
check('revoke moves status to revoked', ($rev_data['status'] ?? '') === 'revoked');
check('revoke stores revocation_reason', is_array($rev_data) && !empty($rev_data['revocation_reason']));
check('revoked cert can_download=false', ($rev_data['can_download'] ?? true) === false);

$revoke_again = run_api('bearer', $company_token, 'POST', $base . '/' . $cert_a_id . '/revoke', json_encode(['reason' => 'Re-revocation attempt']));
check('second revoke returns HTTP 409', $revoke_again['status'] === 409);

$dl_revoked = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id . '/download');
check('download after revoke is gone (route not found)', $dl_revoked['status'] === 404);

$request_after_revoke = run_api('bearer', $student_token, 'POST', $base, json_encode(['training_id' => $training_id]));
check('student cannot request again after revoke (409 duplicate)', $request_after_revoke['status'] === 409);

$show_revoked = run_api('bearer', $student_token, 'GET', $base . '/' . $cert_a_id);
$show_revoked_data = is_array($show_revoked['data']) ? $show_revoked['data'] : [];
check('student sees revoked cert with reason', is_array($show_revoked_data) && ($show_revoked_data['status'] ?? '') === 'revoked' && !empty($show_revoked_data['revocation_reason']));
check('revoked view status stays revoked', ($show_revoked_data['status'] ?? '') === 'revoked');
check('revoked view exposes certificate_number', is_array($show_revoked_data) && is_string($show_revoked_data['certificate_number'] ?? null));
check('revoked view certificate_number unchanged (matches confirm value)', is_array($show_revoked_data) && ($show_revoked_data['certificate_number'] ?? null) === $issued_number_a);
check('revoked view exposes issued_at', is_array($show_revoked_data) && !empty($show_revoked_data['issued_at']));
check('revoked view keeps the original issued_at', is_array($show_revoked_data) && ($show_revoked_data['issued_at'] ?? null) === ($show_issued_data['issued_at'] ?? null));
check('revoked view exposes revoked_at', is_array($show_revoked_data) && !empty($show_revoked_data['revoked_at']));
check('revoked view exposes revocation_reason', is_array($show_revoked_data) && !empty($show_revoked_data['revocation_reason']));
check('revoked view can_view=true', is_array($show_revoked_data) && ($show_revoked_data['can_view'] ?? false) === true);
check('revoked view can_download=false', is_array($show_revoked_data) && ($show_revoked_data['can_download'] ?? true) === false);

$issued_after_revoke = run_api('bearer', $student_token, 'GET', $base . '/issued');
$issued_after_revoke_items = is_array($issued_after_revoke['data']) ? $issued_after_revoke['data'] : [];
check('revoked cert is NOT returned by the /issued endpoint', count(array_filter($issued_after_revoke_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);

echo "\n== Case group 10: admin revokes the paid cert + final eligibility ==\n";

$admin_revoke = run_api('bearer', $admin_token, 'POST', $base . '/' . $paid_cert_id . '/revoke', json_encode(['reason' => 'Issued in error']));
check('admin revoke returns HTTP 200', $admin_revoke['status'] === 200);
$admin_rev_data = is_array($admin_revoke['data']) ? $admin_revoke['data'] : [];
check('admin revoke sets status revoked', ($admin_rev_data['status'] ?? '') === 'revoked');
check('paid revoked cert keeps its stable certificate_number', is_array($admin_rev_data) && ($admin_rev_data['certificate_number'] ?? null) === $paid_version_number);
check('paid revoked cert keeps issued_at', is_array($admin_rev_data) && !empty($admin_rev_data['issued_at']));
check('paid revoked cert stores the revocation_reason', is_array($admin_rev_data) && ($admin_rev_data['revocation_reason'] ?? null) === 'Issued in error');
check('paid revoked cert is_paid=true', is_array($admin_rev_data) && ($admin_rev_data['is_paid'] ?? false) === true);
check('paid revoked cert can_download=false', ($admin_rev_data['can_download'] ?? true) === false);

$paid_revoke_dupe = run_api('bearer', $admin_token, 'POST', $base . '/' . $paid_cert_id . '/revoke', json_encode(['reason' => 'Repeat']));
check('second admin revoke returns HTTP 409', $paid_revoke_dupe['status'] === 409);

$issued_after_all = run_api('bearer', $student_token, 'GET', $base . '/issued');
$issued_after_all_items = is_array($issued_after_all['data']) ? $issued_after_all['data'] : [];
check('/issued excludes the revoked free cert', count(array_filter($issued_after_all_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);
check('/issued excludes the revoked paid cert', count(array_filter($issued_after_all_items, static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 0);

$elig_company_final = run_api('bearer', $company_token, 'GET', $base . '/eligible');
$elig_company_final_items = is_array($elig_company_final['data']) ? $elig_company_final['data'] : [];
check('company dashboard no longer lists the free driver (certificate row exists)', count(array_filter($elig_company_final_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $training_id)) === 0);
check('company dashboard no longer lists the paid driver (certificate row exists)', count(array_filter($elig_company_final_items, static fn ($i) => (int) ($i['training_id'] ?? 0) === $paid_training_id)) === 0);

echo "\n== Case group 9b: dedicated revoked endpoint (GET /certificates/revoked) ==\n";

$revoked_guest = run_api('guest', '', 'GET', $base . '/revoked');
check('revoked endpoint requires auth (guest 401)', $revoked_guest['status'] === 401);

$revoked_list = run_api('bearer', $student_token, 'GET', $base . '/revoked');
check('revoked endpoint returns HTTP 200', $revoked_list['status'] === 200);
check('revoked endpoint returns success=true', $revoked_list['success'] === true);
$revoked_items = is_array($revoked_list['data']) ? $revoked_list['data'] : [];
check('revoked endpoint returns only revoked certificates', is_array($revoked_items) && count(array_filter($revoked_items, static fn ($c) => ($c['status'] ?? '') !== 'revoked')) === 0);
check('revoked endpoint contains no pending/issued/eligible records', is_array($revoked_items) && count(array_filter($revoked_items, static fn ($c) => in_array(($c['status'] ?? ''), ['pending', 'issued', 'eligible'], true))) === 0);

$revoked_row_a = array_values(array_filter($revoked_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id));
check('revoked endpoint contains the revoked free certificate', count($revoked_row_a) === 1);
$revoked_row_a = $revoked_row_a[0] ?? null;
check('revoked endpoint item status=revoked', is_array($revoked_row_a) && ($revoked_row_a['status'] ?? '') === 'revoked');

$required_fields = ['id', 'certificate_id', 'status', 'student_id', 'company_id', 'company_name', 'training_id', 'training_title', 'specialization_id', 'specialization_name', 'requested_at', 'issued_at', 'revoked_at', 'certificate_number', 'revocation_reason', 'is_paid', 'can_request', 'can_view', 'can_download', 'student'];
$missing_fields = is_array($revoked_row_a) ? array_values(array_filter($required_fields, static fn ($f) => !array_key_exists($f, $revoked_row_a))) : $required_fields;
check('revoked item exposes every required field (Case B)', is_array($revoked_row_a) && count($missing_fields) === 0);

check('revoked endpoint item exposes certificate_number', is_array($revoked_row_a) && is_string($revoked_row_a['certificate_number'] ?? null));
check('revoked endpoint certificate_number not regenerated (matches confirm value)', is_array($revoked_row_a) && ($revoked_row_a['certificate_number'] ?? null) === $issued_number_a);
check('revoked endpoint certificate_number matches GET /{id} (stable)', is_array($revoked_row_a) && ($revoked_row_a['certificate_number'] ?? null) === ($show_revoked_data['certificate_number'] ?? null));
check('revoked endpoint item keeps the original issued_at', is_array($revoked_row_a) && !empty($revoked_row_a['issued_at']) && ($revoked_row_a['issued_at'] ?? null) === ($show_issued_data['issued_at'] ?? null));
check('revoked endpoint item exposes revoked_at', is_array($revoked_row_a) && !empty($revoked_row_a['revoked_at']));
check('revoked endpoint item exposes revocation_reason', is_array($revoked_row_a) && ($revoked_row_a['revocation_reason'] ?? null) === 'Administrative verification failed');
check('revoked endpoint is_paid is a strict boolean', is_array($revoked_row_a) && is_bool($revoked_row_a['is_paid'] ?? null));
check('revoked endpoint is_paid=false for the real free training', is_array($revoked_row_a) && ($revoked_row_a['is_paid'] ?? true) === false);
check('revoked endpoint can_request=false', is_array($revoked_row_a) && ($revoked_row_a['can_request'] ?? true) === false);
check('revoked endpoint can_view=true', is_array($revoked_row_a) && ($revoked_row_a['can_view'] ?? false) === true);
check('revoked endpoint can_download=false', is_array($revoked_row_a) && ($revoked_row_a['can_download'] ?? true) === false);
check('revoked endpoint student block is present', is_array($revoked_row_a) && is_array($revoked_row_a['student'] ?? null));
check('revoked endpoint student full_name matches the DB student', is_array($revoked_row_a) && ($revoked_row_a['student']['full_name'] ?? null) === ($a_student_db['full_name'] ?? null));
check('revoked endpoint does NOT expose may_lead_to_hire', is_array($revoked_row_a) && !array_key_exists('may_lead_to_hire', $revoked_row_a));
check('revoked endpoint exposes no download_url', is_array($revoked_row_a) && !array_key_exists('download_url', $revoked_row_a));

$revoked_paid_row = array_values(array_filter($revoked_items, static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id));
check('revoked endpoint contains the revoked paid certificate', count($revoked_paid_row) === 1 && (($revoked_paid_row[0]['status'] ?? '') === 'revoked'));
check('revoked endpoint is_paid=true for the paid revoked cert', count($revoked_paid_row) === 1 && ($revoked_paid_row[0]['is_paid'] ?? false) === true);
check('revoked endpoint keeps the paid cert number stable', count($revoked_paid_row) === 1 && ($revoked_paid_row[0]['certificate_number'] ?? null) === $paid_version_number);

$revoked_b = run_api('bearer', $sara_token, 'GET', $base . '/revoked');
check('revoked endpoint isolates students (student B sees none of student A)', $revoked_b['status'] === 200 && is_array($revoked_b['data']) && count(array_filter($revoked_b['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 0);
check('student B /revoked empty list is a normal 200', $revoked_b['status'] === 200 && is_array($revoked_b['data']) && count($revoked_b['data']) === 0 && $revoked_b['success'] === true);

$foreign_revoked_show = run_api('bearer', $sara_token, 'GET', $base . '/' . $cert_a_id);
check('student B cannot view student A revoked cert (403)', $foreign_revoked_show['status'] === 403);

$revoked_spoof = run_api('bearer', $student_token, 'GET', $base . '/revoked?student_id=' . $b_sid);
check('revoked endpoint ignores client student_id (still own cert only)', is_array($revoked_spoof['data']) && count(array_filter($revoked_spoof['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1 && count(array_filter($revoked_spoof['data'], static fn ($c) => ($c['status'] ?? '') !== 'revoked')) === 0);

$revoked_company = run_api('bearer', $company_token, 'GET', $base . '/revoked');
check('company /revoked returns 200 and keeps its own-training scope (free cert present)', $revoked_company['status'] === 200 && is_array($revoked_company['data']) && count(array_filter($revoked_company['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);
check('company /revoked also sees the paid cert of its training', is_array($revoked_company['data']) && count(array_filter($revoked_company['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 1);

$revoked_admin = run_api('bearer', $admin_token, 'GET', $base . '/revoked');
check('admin /revoked returns 200 (all revoked certs)', $revoked_admin['status'] === 200 && is_array($revoked_admin['data']) && count(array_filter($revoked_admin['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

check('paid revoked cert cannot be downloaded (route not found)', run_api('bearer', $student_token, 'GET', $base . '/' . $paid_cert_id . '/download')['status'] === 404);

echo "\n== Case group 11: certificate statistics ==\n";

$stats_admin = run_api('bearer', $admin_token, 'GET', $base . '/statistics');
check('admin statistics requires auth-enabled access and returns 200', $stats_admin['status'] === 200 && $stats_admin['success'] === true);
$stats_admin_data = is_array($stats_admin['data']) ? $stats_admin['data'] : [];
$stats_keys = ['total', 'issued', 'active', 'valid', 'revoked', 'pending'];
check('statistics exposes all six status counts', count(array_filter($stats_keys, static fn ($k) => !is_numeric($stats_admin_data[$k] ?? null))) === 0);
check('admin statistics include the baseline dataset plus the test pair (delta +2)', (int) ($stats_admin_data['total'] ?? 0) === $base_am_total + 2 && (int) ($stats_admin_data['revoked'] ?? 0) === $base_am_revoked + 2);

$stats_a = run_api('bearer', $student_token, 'GET', $base . '/statistics');
$stats_a_data = is_array($stats_a['data']) ? $stats_a['data'] : [];
check('student A statistics total is baseline + 2 (free + paid, both now revoked)', $stats_a['status'] === 200 && (int) ($stats_a_data['total'] ?? -1) === $base_a['total'] + 2);
check('student A statistics revoked = baseline revoked + 2', (int) ($stats_a_data['revoked'] ?? -1) === $base_a['revoked'] + 2);
check('student A statistics pending unchanged from baseline', (int) ($stats_a_data['pending'] ?? -1) === (int) $base_a['pending']);
check('student A statistics issued unchanged from baseline', (int) ($stats_a_data['issued'] ?? -1) === (int) $base_a['issued']);
check('student A statistics active/valid unchanged from baseline', (int) ($stats_a_data['active'] ?? -1) === (int) $base_a['active'] && (int) ($stats_a_data['valid'] ?? -1) === (int) $base_a['valid']);

$stats_a_spoof = run_api('bearer', $student_token, 'GET', $base . '/statistics?student_id=' . $b_sid);
check('statistics ignore a client student_id (still scoped to the caller)', is_array($stats_a_spoof['data']) && (int) ($stats_a_spoof['data']['total'] ?? -1) === $base_a['total'] + 2);

$stats_company = run_api('bearer', $company_token, 'GET', $base . '/statistics');
$stats_company_data = is_array($stats_company['data']) ? $stats_company['data'] : [];
check('company statistics scoped to its own trainings (baseline + 2 for TestHire)', $stats_company['status'] === 200 && (int) ($stats_company_data['total'] ?? -1) === $base_c_total + 2 && (int) ($stats_company_data['revoked'] ?? -1) === $base_c_revoked + 2);
check('company statistics ignore a client company_id', is_array(run_api('bearer', $company_token, 'GET', $base . '/statistics?company_id=100144')['data']) && (int) (run_api('bearer', $company_token, 'GET', $base . '/statistics?company_id=100144')['data']['total'] ?? -1) === $base_c_total + 2);

echo "\n== Case group 12: certificate search ==\n";

$search_guest = run_api('guest', '', 'GET', $base . '/search?q=DevOps');
check('search requires auth (guest 401)', $search_guest['status'] === 401);

$search_empty = run_api('bearer', $student_token, 'GET', $base . '/search');
check('search without a keyword returns HTTP 422', $search_empty['status'] === 422);

$search_a = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode($training_title));
check('student A search by title fragment returns 200 success', $search_a['status'] === 200 && $search_a['success'] === true);
$search_a_items = is_array($search_a['data']) ? $search_a['data'] : [];
check('student A search returns the free driver certificate', count(array_filter($search_a_items, static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$search_a_paid = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode($paid_training_title));
check('student A search by paid-training title returns the paid certificate', $search_a_paid['status'] === 200 && is_array($search_a_paid['data']) && count(array_filter($search_a_paid['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $paid_cert_id)) === 1);

$search_by_number = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode($issued_number_a));
check('student A search by certificate_number returns the matching cert', $search_by_number['status'] === 200 && is_array($search_by_number['data']) && count(array_filter($search_by_number['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$search_status_issued = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode($training_title) . '&status=issued');
check('search respects the status filter (no issued free-driver cert now)', $search_status_issued['status'] === 200 && is_array($search_status_issued['data']) && count($search_status_issued['data']) === 0);

$search_status_revoked = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode($training_title) . '&status=revoked');
check('search status=revoked returns the revoked free cert', $search_status_revoked['status'] === 200 && is_array($search_status_revoked['data']) && count(array_filter($search_status_revoked['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$search_b = run_api('bearer', $sara_token, 'GET', $base . '/search?q=' . urlencode($training_title));
check('student B search cannot find student A certificates (scoped)', $search_b['status'] === 200 && is_array($search_b['data']) && count($search_b['data']) === 0);

$search_company = run_api('bearer', $company_token, 'GET', $base . '/search?q=' . urlencode($training_title));
check('company search finds certificates of its own training', $search_company['status'] === 200 && is_array($search_company['data']) && count(array_filter($search_company['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$search_admin = run_api('bearer', $admin_token, 'GET', $base . '/search?q=' . urlencode($training_title));
check('admin search finds the certificate across all scopes', $search_admin['status'] === 200 && is_array($search_admin['data']) && count(array_filter($search_admin['data'], static fn ($c) => (int) ($c['id'] ?? 0) === $cert_a_id)) === 1);

$search_garbage = run_api('bearer', $student_token, 'GET', $base . '/search?q=' . urlencode('zz-no-such-cert-xyz'));
check('search with an unmatched keyword returns an empty list (200)', $search_garbage['status'] === 200 && is_array($search_garbage['data']) && count($search_garbage['data']) === 0);

echo "\n== Cleanup: remove only the rows this run created ==\n";

$created_ids = array_filter([$cert_a_id, $paid_cert_id], static fn ($id) => $id > 0);
foreach ($created_ids as $cid) {
    db_execute("DELETE FROM certificate_appeals WHERE certificate_id = ?", [$cid]);
    db_execute("DELETE FROM certificates WHERE id = ?", [$cid]);
}
if ($created_ids) {
    db_execute(
        "DELETE FROM notifications WHERE entity_type = 'certificate' AND entity_id IN (" . implode(',', $created_ids) . ")"
    );
}

$remaining_free = db_fetch_all("SELECT id FROM certificates WHERE training_id = ?", [$training_id]);
$remaining_paid = db_fetch_all("SELECT id FROM certificates WHERE training_id = ?", [$paid_training_id]);
check('all certificate rows created during the test were removed (free + paid)', is_array($remaining_free) && count($remaining_free) === 0 && is_array($remaining_paid) && count($remaining_paid) === 0);

cf_cleanup_lifecycle_transients($data);

$leftover_sessions = (int) ((db_fetch_one(
    "SELECT COUNT(*) AS n FROM training_sessions WHERE application_id IN (?, ?)",
    [$data['application_free'], $data['application_paid']]
))['n'] ?? 0);
check('the dataset completed sessions of the driver trainings remain (seeder-owned)', $leftover_sessions === 2);

$real_cert_count = (int) ((db_fetch_one("SELECT COUNT(*) AS n FROM certificates"))['n'] ?? 0);
check('DB certificate count returns to the captured baseline (dataset intact)', $real_cert_count === $base_cert_count);

$synthetic_left = db_fetch_one(
    "SELECT COUNT(*) AS n FROM training_listings WHERE title LIKE 'Backend Certificate Lifecycle%'"
);
check('no synthetic certificate training remains', (int) ($synthetic_left['n'] ?? 1) === 0);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);