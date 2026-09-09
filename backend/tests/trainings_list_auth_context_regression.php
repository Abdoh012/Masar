<?php

/**
 * MASAR - Trainings List Auth Context Regression Test
 *
 * Verifies GET /api/v1/trainings/list distinguishes the three auth states:
 *
 *   Case 1  Guest (no credentials)      -> 200 OK, public catalog
 *   Case 2  Valid student access token  -> 200 OK, specialization-scoped list
 *   Case 3  Provided but invalid token  -> 401 Unauthorized (no silent guest
 *                                         downgrade, no 200 full catalog)
 *
 * Cases 3 covers expired JWT and malformed/invalid tokens. The refresh flow,
 * access TTL, and student-specialization SQL filtering are NOT exercised or
 * modified here; a valid student request must produce exactly the same total
 * as training_service_list() with the resolved student context.
 *
 * Run from the backend root:
 *     php tests/trainings_list_auth_context_regression.php
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
require_once BASE . 'app/modules/training/services/training_service.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run_scenario(string $scenario, string $token, string $request_uri = '/api/v1/trainings/list'): array
{
    $case_file = dirname(__FILE__) . '/trainings_list_auth_context_case.php';
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
        'status' => $status,
        'body' => $body,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'total' => is_array($payload) ? ((int) ($payload['data']['pagination']['total'] ?? -1)) : -1,
    ];
}

echo "== Setup: student context ==\n";

$student = db_fetch_one(
    "SELECT u.id AS user_id, u.role, u.status, s.id AS student_id, s.specialization_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.status = 'active' AND u.role = 'student' AND s.specialization_id IS NOT NULL
     ORDER BY s.id LIMIT 1"
);

check('an active student with a specialization exists', is_array($student));

$guest_total = -1;
$scoped_total = -1;

if (is_array($student)) {
    $user_id = (int) $student['user_id'];
    $student_id = (int) $student['student_id'];

    $base_filters = ['page' => 1, 'limit' => 20, 'sort' => 'newest'];

    $guest_result = training_service_list($base_filters, null);
    $guest_total = (int) ($guest_result['success']
        ? ($guest_result['data']['pagination']['total'] ?? -1)
        : -1);

    $scoped_result = training_service_list($base_filters, $student_id);
    $scoped_total = (int) ($scoped_result['success']
        ? ($scoped_result['data']['pagination']['total'] ?? -1)
        : -1);

    echo "student_id={$student_id} spec={$student['specialization_id']} guest_total={$guest_total} scoped_total={$scoped_total}\n";

    $config = jwt_config();

    $valid_token = jwt_issue_access_token([
        'id' => $user_id,
        'role' => 'student',
    ]);

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
check('setup student resolved', is_array($student));

$guest = run_scenario('guest', '');
check('guest returns HTTP 200', $guest['status'] === 200);
check('guest returns success=true', $guest['success'] === true);
check('guest body is valid JSON with pagination total', $guest['total'] >= 0);
check('guest total matches public service count', $guest['total'] === $guest_total);

echo "\n== Case 2: valid student access token ==\n";
check('setup student resolved', is_array($student));

$valid = run_scenario('bearer', $valid_token ?? '');
check('valid token returns HTTP 200', $valid['status'] === 200);
check('valid token returns success=true', $valid['success'] === true);
check('valid token total matches specialization-scoped service count', $valid['total'] === $scoped_total);
check('scoped total does not exceed public total', $scoped_total <= $guest_total);

echo "\n== Case 3a: expired access token ==\n";
check('setup student resolved', is_array($student));

$expired = run_scenario('bearer', $expired_token ?? '');
check('expired token returns HTTP 401', $expired['status'] === 401);
check('expired token returns success=false', $expired['success'] === false);
check('expired token does NOT return 200 full catalog', $expired['status'] !== 200);
check('expired token body mentions access token', stripos($expired['body'], 'token') !== false);

echo "\n== Case 3b: invalid (malformed) access token ==\n";
check('setup student resolved', is_array($student));

$invalid = run_scenario('bearer', $invalid_token ?? '');
check('invalid token returns HTTP 401', $invalid['status'] === 401);
check('invalid token returns success=false', $invalid['success'] === false);
check('invalid token does NOT return 200 full catalog', $invalid['status'] !== 200);

echo "\n== Case 4: /trainings/details/{id} keeps previous guest behavior ==\n";
check('setup student resolved', is_array($student));

$details_id_row = db_fetch_one(
    "SELECT id FROM training_listings WHERE status = 'published' ORDER BY id LIMIT 1"
);
check('a published training exists for details', is_array($details_id_row));

$details_uri = '/api/v1/trainings/details/' . (int) ($details_id_row['id'] ?? 0);

$details_guest = run_scenario('guest', '', $details_uri);
check('details with no token returns HTTP 200', $details_guest['status'] === 200);
check('details with no token returns success=true', $details_guest['success'] === true);

check('setup student resolved', is_array($student));

$details_invalid = run_scenario('bearer', $invalid_token ?? '', $details_uri);
check('details with invalid token returns HTTP 200 (guest, unchanged)', $details_invalid['status'] === 200);
check('details with invalid token returns success=true', $details_invalid['success'] === true);

check('setup student resolved', is_array($student));

$details_valid = run_scenario('bearer', $valid_token ?? '', $details_uri);
check('details with valid token returns HTTP 200', $details_valid['status'] === 200);
check('details with valid token returns success=true', $details_valid['success'] === true);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";
exit($failures === 0 ? 0 : 1);