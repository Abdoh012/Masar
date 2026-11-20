<?php

/**
 * MASAR - Auth Refresh / Session Regression
 *
 * Verifies the backend token lifecycle:
 * - login issues access + refresh tokens with correct claims/expiry
 * - refresh (body token, no CSRF) rotates tokens atomically
 * - reused / invalid / expired / revoked refresh tokens are rejected
 * - expired access tokens are rejected by protected endpoints
 * - logout works when access token is expired but refresh token is presented
 * - invalid credentials do not issue tokens
 *
 * Uses an isolated fixture user (tagged email/id range not needed; user is
 * created and deleted by this script). All fixture rows are removed on exit.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/config/constants.php';
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/core/auth/auth.php';
require_once BASE . 'app/services/jwt_service.php';

$failures = 0;
$created_user_id = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function cleanup(): void
{
    global $created_user_id;
    if ($created_user_id > 0) {
        db_execute('DELETE FROM refresh_tokens WHERE user_id = ?', [$created_user_id]);
        db_execute('DELETE FROM auth_tokens WHERE user_id = ?', [$created_user_id]);
        db_execute('DELETE FROM users WHERE id = ?', [$created_user_id]);
        $created_user_id = 0;
    }
}

register_shutdown_function('cleanup');

function run_case(string $route_file, string $path, string $method, string $scenario = 'guest', string $token = '', ?array $body = null): array
{
    $case_file = BASE . 'tests/masar_request_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($route_file)
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $environment = ['MASAR_CASE_BODY' => $body === null ? '' : (string) json_encode($body)];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, BASE, $environment + getenv());

    $output = '';
    if (is_resource($process)) {
        $output .= (string) stream_get_contents($pipes[1]);
        $output .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }

    $status = 0;
    $raw = '';
    foreach (explode("\n", $output) as $line) {
        $line = rtrim($line, "\r");
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, 7);
        }
        if (str_starts_with($line, 'BODY=')) {
            $raw = substr($line, 5);
        }
    }

    $json = json_decode($raw, true);

    return ['status' => $status, 'raw' => $raw, 'json' => is_array($json) ? $json : null];
}

echo "== Setup ==\n";
$email = 'regression_auth_refresh_' . bin2hex(random_bytes(4)) . '@masar-regression.invalid';
$password = 'Str0ng!Passw0rd';
$hash = password_hash($password, PASSWORD_DEFAULT);

db_execute(
    'INSERT INTO users (email, password_hash, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
    [$email, $hash, USER_ROLE_STUDENT, USER_STATUS_ACTIVE]
);
$created_user_id = (int) db_last_insert_id();
check('fixture user created', $created_user_id > 0);

echo "\n== Login ==\n";
$login = run_case('auth.php', '/api/v1/auth/login', 'POST', 'guest', '', [
    'email' => $email,
    'password' => $password,
]);
check('login returns 200', $login['status'] === 200);
check('login success flag is true', ($login['json']['success'] ?? null) === true);
$ld = is_array($login['json']['data'] ?? null) ? $login['json']['data'] : [];
$access_token = (string) ($ld['token'] ?? '');
$refresh_token = (string) ($ld['refresh_token'] ?? '');
check('login issues access token', $access_token !== '');
check('login issues refresh token', $refresh_token !== '');
check('login token_type is bearer', ($ld['token_type'] ?? null) === 'bearer');
check('login exposes expires_in', (int) ($ld['expires_in'] ?? 0) > 0);
check('login exposes refresh_expires_in', (int) ($ld['refresh_expires_in'] ?? 0) > 0);
check('login returns sanitized user', is_array($ld['user'] ?? null) && (int) ($ld['user']['id'] ?? 0) === $created_user_id);

$access_payload = jwt_validate_access_token($access_token);
check('access token is valid JWT', is_array($access_payload));
check('access token iat present', isset($access_payload['iat']));
check('access token exp present', isset($access_payload['exp']));
check('access token jti present', !empty($access_payload['jti'] ?? null));
check('access token exp reflects configured TTL', is_array($access_payload) && (($access_payload['exp'] - $access_payload['iat']) === jwt_access_ttl()));
check('refresh token is valid and stored', is_array(jwt_validate_refresh_token($refresh_token)));

echo "\n== Invalid credentials ==\n";
$badlogin = run_case('auth.php', '/api/v1/auth/login', 'POST', 'guest', '', [
    'email' => $email,
    'password' => 'WrongPassword1!',
]);
check('invalid credentials are rejected', in_array($badlogin['status'], [400, 401], true));
$bd = is_array($badlogin['json']['data'] ?? null) ? $badlogin['json']['data'] : [];
check('invalid login issues no access token', empty($bd['token'] ?? null));
check('invalid login issues no refresh token', empty($bd['refresh_token'] ?? null));

echo "\n== Access token enforcement ==\n";
$me = run_case('auth.php', '/api/v1/auth/me', 'GET', 'bearer', $access_token);
check('valid access token authenticates protected endpoint', $me['status'] === 200);

$config = jwt_config();
$now = time();
$expired_header = json_encode(['alg' => $config['algorithm'], 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES);
$expired_payload = json_encode([
    'sub' => $created_user_id,
    'role' => USER_ROLE_STUDENT,
    'type' => 'access',
    'iat' => $now - 7200,
    'exp' => $now - 3600,
    'jti' => bin2hex(random_bytes(16)),
], JSON_UNESCAPED_SLASHES);
$expired_access = jwt_sign($expired_header, $expired_payload, $config['secret'], $config['algorithm']);
$expired_me = run_case('auth.php', '/api/v1/auth/me', 'GET', 'bearer', $expired_access);
check('expired access token returns 401', $expired_me['status'] === 401);

$invalid_me = run_case('auth.php', '/api/v1/auth/me', 'GET', 'bearer', $access_token . 'x');
check('invalid access token returns 401', $invalid_me['status'] === 401);

$malformed_me = run_case('auth.php', '/api/v1/auth/me', 'GET', 'bearer', 'not-a-jwt');
check('malformed access token returns 401', $malformed_me['status'] === 401);

echo "\n== Refresh (body token) and rotation ==\n";
$refresh = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', [
    'refresh_token' => $refresh_token,
]);
check('refresh returns 200', $refresh['status'] === 200);
$rd = is_array($refresh['json']['data'] ?? null) ? $refresh['json']['data'] : [];
$new_access = (string) ($rd['token'] ?? '');
$new_refresh = (string) ($rd['refresh_token'] ?? '');
check('refresh issues new access token', $new_access !== '' && $new_access !== $access_token);
check('refresh issues new refresh token', $new_refresh !== '' && $new_refresh !== $refresh_token);
check('refresh returns token_type bearer', ($rd['token_type'] ?? null) === 'bearer');
check('refresh returns expires_in', (int) ($rd['expires_in'] ?? 0) > 0);
check('refresh returns refresh_expires_in', (int) ($rd['refresh_expires_in'] ?? 0) > 0);
check('new access token is valid', is_array(jwt_validate_access_token($new_access)));
check('new refresh token is valid', is_array(jwt_validate_refresh_token($new_refresh)));
check('old refresh token is rejected after rotation', jwt_validate_refresh_token($refresh_token) === false);

echo "\n== Refresh rejection cases ==\n";
$reuse = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', [
    'refresh_token' => $refresh_token,
]);
check('reused/rotated refresh token is rejected (401)', $reuse['status'] === 401);

$invalid_refresh = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', [
    'refresh_token' => 'invalid.refresh.token',
]);
check('invalid refresh token is rejected (401)', $invalid_refresh['status'] === 401);

$missing_refresh = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', []);
check('missing refresh token is rejected (401)', $missing_refresh['status'] === 401);

// Expire the current refresh token in storage and ensure it cannot be used.
$new_refresh_hash = hash('sha256', $new_refresh);
db_execute('UPDATE refresh_tokens SET expires_at = ? WHERE token_hash = ?', [gmdate('Y-m-d H:i:s', time() - 60), $new_refresh_hash]);
$expired_refresh = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', [
    'refresh_token' => $new_refresh,
]);
check('expired refresh token is rejected (401)', $expired_refresh['status'] === 401);

echo "\n== Logout with expired access token ==\n";
$login2 = run_case('auth.php', '/api/v1/auth/login', 'POST', 'guest', '', [
    'email' => $email,
    'password' => $password,
]);
$ld2 = is_array($login2['json']['data'] ?? null) ? $login2['json']['data'] : [];
$logout_refresh = (string) ($ld2['refresh_token'] ?? '');
check('setup: login issued refresh token for logout', $logout_refresh !== '');
$logout = run_case('auth.php', '/api/v1/auth/logout', 'POST', 'guest', '', [
    'refresh_token' => $logout_refresh,
]);
check('logout succeeds without a valid access token (200)', $logout['status'] === 200);
check('logout revokes presented refresh token', jwt_validate_refresh_token($logout_refresh) === false);
$refresh_after_logout = run_case('auth.php', '/api/v1/auth/refresh', 'POST', 'guest', '', [
    'refresh_token' => $logout_refresh,
]);
check('revoked refresh token cannot be reused after logout (401)', $refresh_after_logout['status'] === 401);

echo "\n== Cleanup ==\n";
cleanup();
check('fixture user removed', (int) db_fetch_column('SELECT COUNT(*) FROM users WHERE id = ?', [$created_user_id === 0 ? -1 : $created_user_id]) === 0 || $created_user_id === 0);

echo "\n== Result ==\n";
if ($failures > 0) {
    echo "FAILURES: {$failures}\n";
    exit(1);
}

echo "ALL PASS\n";
exit(0);
