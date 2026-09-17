<?php

/**
 * MASAR - Applications Issued Certificates Endpoint Regression Test
 *
 * Verifies the new GET /api/v1/applications/certificates endpoint (the
 * Applications module's "Certificates / Issued Certificates" tab):
 *
 *   Case 1  Guest (no credentials)   -> 401 Unauthorized
 *   Case 2a Expired student token    -> 401 Unauthorized
 *   Case 2b Invalid/malformed token  -> 401 Unauthorized
 *   Case 3  Valid student token      -> 200 OK, success=true, ONLY status=issued
 *           certificates, scoped to the authenticated student's own issued set
 *           (never pending/revoked/eligible), with the full issued presentation
 *           (certificate_number, issued_at, grade, grade_label, is_paid,
 *           nested student object, ...).
 *   Case 4  Response parity          -> The response BODY is BYTE-FOR-BYTE
 *           identical to GET /api/v1/certificates/issued for the same token:
 *           same envelope, message, item count, certificate ids, fields and
 *           values.
 *   Case 5  student_id override      -> ?student_id=<other student> with student
 *           A's token still returns ONLY student A's issued certificates
 *           (client-supplied student_id is ignored, never trusted).
 *   Case 6  Isolation                -> another student (student B) receives
 *           only student B's own issued certificates - never student A's.
 *
 * Run from the backend root:
 *     php tests/applications_certificates_endpoint_regression.php
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
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/modules/certificates/services/certificate_service.php';
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

/**
 * Run the NEW endpoint through the applications route shim. Returns the raw
 * body and parsed bits.
 */
function run_applications(string $scenario, string $token, string $request_uri = '/api/v1/applications/certificates'): array
{
    $case_file = dirname(__FILE__) . '/applications_certificates_endpoint_case.php';
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

/**
 * Run the CANONICAL GET /api/v1/certificates/issued through the existing
 * certificates route shim (tests/certificates_api_case.php) so the two
 * endpoints can be compared for the same authenticated student.
 */
function run_certificates_issued(string $token): array
{
    $case_file = dirname(__FILE__) . '/certificates_api_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' bearer'
        . ' ' . escapeshellarg($token)
        . ' GET'
        . ' /api/v1/certificates/issued';

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

function cert_ids(array $data): array
{
    $ids = [];
    foreach ($data as $item) {
        if (is_array($item) && isset($item['id'])) {
            $ids[] = (int) $item['id'];
        }
    }
    sort($ids);
    return $ids;
}

echo "== Setup: student context ==\n";

$student_a = cf_resolve_student_first(['mammuslim2003@gmail.com']);
check('student A resolved (mammuslim2003@gmail.com, student 1498)', $student_a['user_id'] > 0 && $student_a['student_id'] === 1498);

$student_b = cf_resolve_student_first(['sara.fahim@gmail.com', 'sara.mostafa@test.local']);
if ($student_b['user_id'] <= 0) {
    $row = db_fetch_one(
        "SELECT u.id AS user_id, s.id AS student_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.role = 'student' AND u.status = 'active'
           AND s.id <> ?
         ORDER BY s.id LIMIT 1",
        [$student_a['student_id'] ?? 0]
    );
    if (is_array($row)) {
        $student_b = [
            'user_id'    => (int) $row['user_id'],
            'student_id' => (int) $row['student_id'],
            'full_name'  => '',
        ];
    }
}
check('student B resolved (isolation student)', is_array($student_b) && ($student_b['user_id'] ?? 0) > 0);

$expected_a_ids = [];
$pending_revoked_a_ids = [];
if ($student_a['student_id'] ?? 0 > 0) {
    foreach (db_fetch_all(
        "SELECT id FROM certificates WHERE student_id = ? AND status = 'issued' ORDER BY id ASC",
        [(int) $student_a['student_id']]
    ) ?? [] as $row) {
        $expected_a_ids[] = (int) $row['id'];
    }
    foreach (db_fetch_all(
        "SELECT id FROM certificates WHERE student_id = ? AND status IN ('pending','revoked') ORDER BY id ASC",
        [(int) $student_a['student_id']]
    ) ?? [] as $row) {
        $pending_revoked_a_ids[] = (int) $row['id'];
    }
}
$expected_a_ids = array_values(array_unique($expected_a_ids));
$pending_revoked_a_ids = array_values(array_unique($pending_revoked_a_ids));

$expected_b_ids = [];
if (($student_b['user_id'] ?? 0) > 0) {
    foreach (db_fetch_all(
        "SELECT id FROM certificates WHERE student_id = ? AND status = 'issued' ORDER BY id ASC",
        [(int) $student_b['student_id']]
    ) ?? [] as $row) {
        $expected_b_ids[] = (int) $row['id'];
    }
}
$expected_b_ids = array_values(array_unique($expected_b_ids));

check('student A has issued certificates (expected ' . count($expected_a_ids) . ')', count($expected_a_ids) >= 1);
check('student A has pending/revoked certificates to exclude', count($pending_revoked_a_ids) >= 1);
check('student A and student B are different students', $student_a['student_id'] !== $student_b['student_id']);

$valid_token = '';
$expired_token = '';
$invalid_token = 'invalid-token-not-a-jwt';

if (($student_a['user_id'] ?? 0) > 0) {
    $valid_token = jwt_issue_access_token([
        'id' => (int) $student_a['user_id'],
        'role' => 'student',
    ]);

    $config = jwt_config();
    $now = time();
    $expired_token = jwt_sign(
        json_encode(['alg' => $config['algorithm'], 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES),
        json_encode([
            'sub' => $student_a['user_id'],
            'role' => 'student',
            'type' => 'access',
            'iat' => $now - 7200,
            'exp' => $now - 3600,
            'jti' => bin2hex(random_bytes(16)),
        ], JSON_UNESCAPED_SLASHES),
        $config['secret'],
        $config['algorithm']
    );
}

$student_b_token = '';
if (($student_b['user_id'] ?? 0) > 0) {
    $student_b_token = jwt_issue_access_token([
        'id' => (int) $student_b['user_id'],
        'role' => 'student',
    ]);
}

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_applications('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);

echo "\n== Case 2a: expired student access token ==\n";

$expired = run_applications('bearer', $expired_token);
check('expired token returns HTTP 401', $expired['status'] === 401);
check('expired token returns success=false', $expired['success'] === false);

echo "\n== Case 2b: invalid (malformed) access token ==\n";

$invalid = run_applications('bearer', $invalid_token);
check('invalid token returns HTTP 401', $invalid['status'] === 401);
check('invalid token returns success=false', $invalid['success'] === false);

echo "\n== Case 3: valid student token -> /applications/certificates (issued only) ==\n";

$apps = run_applications('bearer', $valid_token);
check('applications/certificates returns HTTP 200', $apps['status'] === 200);
check('applications/certificates returns success=true', $apps['success'] === true);

$apps_data = is_array($apps['data']) ? $apps['data'] : [];
check('data is an array (issued flat list, no application DTO)', is_array($apps_data));

$apps_ids = cert_ids($apps_data);
sort($expected_a_ids);
check('item count matches the student\'s issued certificates (' . count($expected_a_ids) . ')', count($apps_ids) === count($expected_a_ids));
check('items are exactly the student\'s issued certificate ids', $apps_ids === $expected_a_ids);

$only_issued = true;
$excluded_present = false;
$has_fields = true;
$student_object_ok = true;
$grade_ok = true;
foreach ($apps_data as $item) {
    if (!is_array($item)) {
        $only_issued = false;
        continue;
    }
    if (($item['status'] ?? '') !== 'issued') {
        $only_issued = false;
    }
    if (in_array((int)($item['id'] ?? 0), $pending_revoked_a_ids, true)) {
        $excluded_present = true;
    }
    $expected_keys = [
        'id', 'status', 'student_id', 'company_id', 'company_name', 'training_id',
        'training_title', 'specialization_id', 'specialization_name', 'requested_at',
        'issued_at', 'certificate_number', 'is_paid', 'student',
        'can_request', 'can_view', 'can_download',
    ];
    foreach ($expected_keys as $key) {
        if (!array_key_exists($key, $item)) {
            $has_fields = false;
        }
    }
    if (!array_key_exists('grade', $item) || !array_key_exists('grade_label', $item)) {
        $grade_ok = false;
    }
    if (!is_array($item['student'] ?? null) || (int)($item['student']['id'] ?? 0) !== (int)$item['student_id']) {
        $student_object_ok = false;
    }
}
check('every item has status=issued (no pending/revoked/eligible)', $only_issued);
check('no pending or revoked certificate appears', !$excluded_present);
check('every item exposes the issued presentation fields', $has_fields);
check('every item exposes grade and grade_label', $grade_ok);
check('every item carries the nested student object matching student_id', $student_object_ok);

echo "\n== Case 4: response parity with GET /certificates/issued ==\n";

$issued = run_certificates_issued($valid_token);
check('certificates/issued returns HTTP 200', $issued['status'] === 200);
check('certificates/issued returns success=true', $issued['success'] === true);

$issued_ids = cert_ids(is_array($issued['data']) ? $issued['data'] : []);
check('certificates/issued returns one item per issued certificate', $issued_ids === $expected_a_ids);

check('applications/certificates BODY is byte-identical to certificates/issued', $apps['body'] === $issued['body']);
check('applications/certificates data equals certificates/issued data', $apps['data'] === $issued['data']);

echo "\n== Case 5: client-supplied student_id override is ignored ==\n";

$override_uri = '/api/v1/applications/certificates?student_id=' . ($student_b['student_id'] ?? 0);
$override = run_applications('bearer', $valid_token, $override_uri);
check('student_id override still returns HTTP 200', $override['status'] === 200);
check('student_id override returns success=true', $override['success'] === true);
check('student_id override response BODY is identical to the plain call', $override['body'] === $apps['body']);

$override_ids = cert_ids(is_array($override['data']) ? $override['data'] : []);
check('student_id override never returns another student\'s certificates', $override_ids === $expected_a_ids);

echo "\n== Case 6: isolation - another student cannot see these certificates ==\n";

$apps_b = run_applications('bearer', $student_b_token);
check('student B receives HTTP 200', $apps_b['status'] === 200);
check('student B receives success=true', $apps_b['success'] === true);

$apps_b_ids = cert_ids(is_array($apps_b['data']) ? $apps_b['data'] : []);
sort($expected_b_ids);
check('student B receives only their own issued certificates', $apps_b_ids === $expected_b_ids);
$intersection = array_intersect($apps_b_ids, $expected_a_ids);
check('student B sees none of student A\'s certificates', $intersection === []);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);