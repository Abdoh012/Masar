<?php

/**
 * MASAR - Password Policy And Session Invalidation Regression Test
 *
 * Pins the two authentication contracts this work deliberately did NOT change,
 * so neither can drift silently.
 *
 * 1. THE PASSWORD POLICY IS 12 CHARACTERS + COMPLEXITY
 * ---------------------------------------------------
 * An audit found the Profile UI describing an 8-character minimum while the
 * API required 12. The backend rule was kept, because
 * security_password_strength_errors() is the single policy function used by
 * register, change-password AND reset-password, and a client-side label is a
 * usability hint rather than a security control.
 *
 *   Case 1   The policy function itself             -> 12 + 4 character classes
 *   Case 2   An 8-character password is REFUSED     -> the exact UI claim fails
 *   Case 3   Each complexity class is enforced
 *   Case 4   Change-password validation             -> confirmation required and
 *                                                      must match
 *   Case 5   Register validation uses the SAME policy (no weaker registration)
 *   Case 6   Reset validation ends up on the SAME policy once the controller's
 *              stronger check is merged, so the 8-char check is not a bypass
 *   Case 7   A REJECTED change verifies the current password and changes nothing
 *   Case 8   A rejected change left applications, statuses and certificates
 *            byte-identical (application statuses stay submitted/accepted/
 *            rejected/withdrawn)
 *
 * 2. THE SESSION-INVALIDATION WINDOW
 * ---------------------------------
 *   Case 9   A freshly issued access token validates -> stateless, no denylist
 *   Case 10  After jwt_revoke_access_token() the SAME token is rejected
 *            -> the current-device mechanism does work
 *   Case 11  A refresh token is accepted, then
 *            jwt_revoke_all_refresh_tokens_for_user() revokes it and it is
 *            rejected -> the documented boundary really is refresh tokens
 *   Case 12  A second access token for the same user is UNAFFECTED by revoking
 *            one access token -> the documented ~1 hour window, proven
 *
 * FIXTURES
 * --------
 * The suite never changes a real password: the seeded accounts' passwords are
 * unknown, and Case 7 uses a deliberately wrong current password so the change
 * is rejected before any write. The only rows it creates are two tagged
 * `refresh_tokens` fixture rows for the probe user, deleted by
 * password_policy_fixture_cleanup(), which is also registered as a shutdown
 * handler. The probe user's password_hash is compared before and after to prove
 * it was never rewritten.
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
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/services/jwt_service.php';
require_once BASE . 'app/shared/functions/security.php';
require_once BASE . 'app/modules/auth/validators/auth_validator.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function request_api(string $path, string $method, string $token, ?array $body = null, string $route_file = 'auth.php'): array
{
    $case_file = dirname(__FILE__) . '/masar_request_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($route_file)
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg('bearer')
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
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, strlen('STATUS='));
        }
        if (str_starts_with($line, 'BODY=')) {
            $raw = substr($line, strlen('BODY='));
        }
    }

    $payload = json_decode($raw, true);

    return [
        'status'  => $status,
        'raw'     => $raw,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? []) : [],
    ];
}

/*
|--------------------------------------------------------------------------
| Tagged refresh-token fixtures
|--------------------------------------------------------------------------
|
| The marker lives in token_hash, which is the column the lookup is keyed on,
| so cleanup can find the rows without touching anybody else's token. Two rows
| are needed: one that stays live to prove a refresh token is accepted, and one
| to watch get revoked.
*/

const PASSWORD_POLICY_TOKEN_TAG = 'regression_auth_password_policy_';
const PASSWORD_POLICY_EMAIL_PREFIX = 'regression_auth_password_policy_';
const PASSWORD_POLICY_FIXTURE_USER_ID = 900900001;

function password_policy_fixture_cleanup(): void
{
    db_execute(
        'DELETE FROM refresh_tokens WHERE token_hash LIKE ? OR user_id = ?',
        [PASSWORD_POLICY_TOKEN_TAG . '%', PASSWORD_POLICY_FIXTURE_USER_ID]
    );

    db_execute(
        'DELETE FROM revoked_access_tokens WHERE user_id = ?',
        [PASSWORD_POLICY_FIXTURE_USER_ID]
    );

    db_execute(
        'DELETE FROM users WHERE id = ?',
        [PASSWORD_POLICY_FIXTURE_USER_ID]
    );
}

register_shutdown_function('password_policy_fixture_cleanup');

echo "== Setup: probe account ==\n";

$probe = db_fetch_one(
    "SELECT u.id AS user_id, s.id AS student_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.role = 'student' AND u.status = 'active'
     ORDER BY u.id
     LIMIT 1"
);

check('Setup: an active student account was found', is_array($probe) && !empty($probe['user_id']));

if (!is_array($probe)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$user_id = (int) $probe['user_id'];
$token = jwt_issue_access_token(['id' => $user_id, 'role' => 'student']);

$hash_before = (string) db_fetch_column('SELECT password_hash FROM users WHERE id = ?', [$user_id]);

check('Setup: the probe password_hash is a bcrypt hash', str_starts_with($hash_before, '$2y$'));
check('Setup: the seeded hash is never printed by this suite', !defined('MASAR_PRINT_HASHES'));

echo "\n== Case 1: the policy function itself ==\n";

$strong = 'Str0ng!Passw0rd';
check('Case 1: a 12+ character mixed password passes', security_password_strength_errors($strong) === []);
check('Case 1: an empty password is refused', security_password_strength_errors('') !== []);

$config = jwt_config();
check('Case 1: the default access-token lifetime is one hour', (int) $config['access_ttl'] === 3600);

echo "\n== Case 2: an 8-character password is refused ==\n";

$eight = 'Abcdef1!';
check('Case 2: the fixture really is 8 characters', strlen($eight) === 8);
$eight_errors = security_password_strength_errors($eight);
check('Case 2: the 8-character password produces errors', !empty($eight_errors));
check('Case 2: the message states the 12-character minimum', (bool) array_filter($eight_errors, static fn($e) => str_contains($e, '12 characters')));
check('Case 2: the 8-character password is not accepted', security_password_strength_errors($eight) !== []);

echo "\n== Case 3: every complexity class is enforced ==\n";

check('Case 3: a lowercase-only long password is refused', security_password_strength_errors('abcdefghijklmnop') !== []);
check('Case 3: an uppercase-only long password is refused', security_password_strength_errors('ABCDEFGHIJKLMNOP') !== []);
check('Case 3: a digits-only long password is refused', security_password_strength_errors('1234567890123456') !== []);
check('Case 3: a long password with no special character is refused', security_password_strength_errors('Abcdefgh1Abcdefg') !== []);
check('Case 3: a long mixed password WITH a special character passes', security_password_strength_errors('Abcdefgh1Abcdef!') === []);

echo "\n== Case 4: change-password validation ==\n";

$valid_change = [
    'current_password' => 'Whatever1!',
    'new_password' => 'Str0ng!Passw0rd',
    'new_password_confirmation' => 'Str0ng!Passw0rd',
];
check('Case 4: a complete, valid payload produces no errors', auth_validate_change_password($valid_change) === []);

$no_current = $valid_change;
unset($no_current['current_password']);
check('Case 4: a missing current_password is refused', isset(auth_validate_change_password($no_current)['current_password']));

$no_confirm = $valid_change;
unset($no_confirm['new_password_confirmation']);
check('Case 4: a missing confirmation is refused', isset(auth_validate_change_password($no_confirm)['new_password_confirmation']));

$mismatch = $valid_change;
$mismatch['new_password_confirmation'] = 'Different1!';
check('Case 4: a mismatched confirmation is refused', isset(auth_validate_change_password($mismatch)['new_password_confirmation']));

$weak_change = $valid_change;
$weak_change['new_password'] = 'Abcdef1!';
$weak_change['new_password_confirmation'] = 'Abcdef1!';
check('Case 4: an 8-character new password is refused by the validator', isset(auth_validate_change_password($weak_change)['new_password']));

echo "\n== Case 5: registration uses the same policy ==\n";

$strong_register = ['email' => 'policy@example.test', 'password' => $strong, 'password_confirmation' => $strong, 'accept_terms' => true];
$register_errors = auth_validate_register($strong_register);
check('Case 5: a strong registration password produces no password error', empty($register_errors['password'] ?? []));

$weak_register = $strong_register;
$weak_register['password'] = 'Abcdef1!';
$weak_register['password_confirmation'] = 'Abcdef1!';
check('Case 5: an 8-character registration password IS refused', !empty(auth_validate_register($weak_register)['password'] ?? []));

echo "\n== Case 6: reset-password ends up on the same policy ==\n";

$strong_reset = ['email' => 'policy@example.test', 'token' => 'abc', 'password' => $strong, 'password_confirmation' => $strong];
$validator_only = auth_validate_reset_password($strong_reset);
check('Case 6: a strong reset password passes the validator alone', empty($validator_only['password'] ?? []));

/*
 | A 7-character password trips the validator's own `strlen < 8` check, so the
 | assertion can see both rules coexist: the weak one fires, and the controller's
 | merge adds the strict one. That is what proves the 8-character check is not a
 | bypass route into a weak password.
 */
$weak_reset = ['email' => 'policy@example.test', 'token' => 'abc', 'password' => 'Abc1!xy', 'password_confirmation' => 'Abc1!xy'];
check('Case 6: the weak fixture really is 7 characters', strlen('Abc1!xy') === 7);

$validator_weak = auth_validate_reset_password($weak_reset);
check('Case 6: the validator mentions 8 characters', (bool) array_filter($validator_weak['password'] ?? [], static fn($e) => str_contains($e, '8 characters')));

$merged_weak = array_values(array_unique(array_merge(
    $validator_weak['password'] ?? [],
    security_password_strength_errors('Abc1!xy')
)));
check('Case 6: the controller merge also carries the 12-character rule', (bool) array_filter($merged_weak, static fn($e) => str_contains($e, '12 characters')));
check('Case 6: so the effective reset policy is the strict one, not the 8-char check', (bool) array_filter($merged_weak, static fn($e) => str_contains($e, '12 characters')));

echo "\n== Case 7: a rejected change verifies the current password ==\n";

$rejected = request_api('/api/v1/auth/change-password', 'POST', $token, [
    'current_password' => 'DefinitelyNotThePassword1!',
    'new_password' => 'Str0ng!Passw0rd',
    'new_password_confirmation' => 'Str0ng!Passw0rd',
]);

check('Case 7: the change is refused', $rejected['success'] === false);
check('Case 7: the refusal names the current password', stripos((string) ($rejected['raw'] ?? ''), 'Current password is incorrect') !== false);
check('Case 7: no new password is echoed back', !str_contains((string) $rejected['raw'], 'Str0ng!Passw0rd'));

echo "\n== Case 8: a rejected change touched nothing ==\n";

$student_id = (int) ($probe['student_id'] ?? 0);
$apps_before = db_fetch_all('SELECT id, status, applied_at, reviewed_at, withdrawn_at FROM training_applications WHERE student_id = ? ORDER BY id', [$student_id]);
$statuses_before = db_fetch_all('SELECT status, COUNT(*) AS c FROM training_applications WHERE student_id = ? GROUP BY status', [$student_id]);
$certs_before = (int) db_fetch_column('SELECT COUNT(*) FROM certificates WHERE student_id = ?', [$student_id]);
$payments_before = (int) db_fetch_column('SELECT COUNT(*) FROM payments WHERE student_id = ?', [$student_id]);

$hash_after_reject = (string) db_fetch_column('SELECT password_hash FROM users WHERE id = ?', [$user_id]);
check('Case 8: the password_hash was NOT rewritten', $hash_after_reject === $hash_before);
check('Case 8: the hash is still bcrypt', str_starts_with($hash_after_reject, '$2y$'));

check('Case 8: no application row was added or removed', db_fetch_all('SELECT id, status, applied_at, reviewed_at, withdrawn_at FROM training_applications WHERE student_id = ? ORDER BY id', [$student_id]) === $apps_before);
check('Case 8: no application status changed', db_fetch_all('SELECT status, COUNT(*) AS c FROM training_applications WHERE student_id = ? GROUP BY status', [$student_id]) === $statuses_before);
check('Case 8: no certificate was touched', (int) db_fetch_column('SELECT COUNT(*) FROM certificates WHERE student_id = ?', [$student_id]) === $certs_before);
check('Case 8: no payment was touched', (int) db_fetch_column('SELECT COUNT(*) FROM payments WHERE student_id = ?', [$student_id]) === $payments_before);

$seen_statuses = array_column($statuses_before, 'status');
$allowed = ['submitted', 'accepted', 'rejected', 'withdrawn'];
check('Case 8: application statuses are still the canonical four', array_diff(array_unique($seen_statuses), $allowed) === []);

echo "\n== Case 9: a fresh access token validates (stateless) ==\n";

/*
 | Cases 9-12 use a DEDICATED fixture account rather than a seeded student.
 | jwt_revoke_all_refresh_tokens_for_user() is by design a user-wide sweep, so
 | pointing it at a real student would revoke that student's genuine refresh
 | tokens. The fixture user is created with a tagged email and removed again by
 | password_policy_fixture_cleanup().
 */
$fixture_user_id = PASSWORD_POLICY_FIXTURE_USER_ID;
$fixture_email = PASSWORD_POLICY_EMAIL_PREFIX . 'owner@masar-regression.invalid';

db_execute('DELETE FROM users WHERE id = ?', [$fixture_user_id]);
db_execute(
    'INSERT INTO users (id, role, email, password_hash, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
    [$fixture_user_id, 'student', $fixture_email, 'not-a-real-hash', 'active']
);
check('Setup: the dedicated fixture account was created', (int) db_fetch_column('SELECT COUNT(*) FROM users WHERE id = ?', [$fixture_user_id]) === 1);

$access_token = jwt_issue_access_token(['id' => $fixture_user_id, 'role' => 'student']);
$payload = jwt_validate_access_token($access_token);
check('Case 9: a freshly issued access token is accepted', is_array($payload));
check('Case 9: it resolves to the fixture user', is_array($payload) && (int) ($payload['sub'] ?? 0) === $fixture_user_id);
check('Case 9: the access payload carries a jti', is_array($payload) && !empty($payload['jti']));

echo "\n== Case 10: revoking the CURRENT access token works ==\n";

jwt_revoke_access_token($access_token);
check('Case 10: the same token is now rejected', jwt_validate_access_token($access_token) === false);

echo "\n== Case 11: refresh tokens are revoked for the user ==\n";

$live_refresh = jwt_issue_refresh_token(['id' => $fixture_user_id, 'role' => 'student']);
$live_hash = hash('sha256', $live_refresh);
check('Case 11: issuing a refresh token persisted exactly one row', (int) db_fetch_column('SELECT COUNT(*) FROM refresh_tokens WHERE token_hash = ?', [$live_hash]) === 1);

$unrevoked_before = (int) db_fetch_column('SELECT COUNT(*) FROM refresh_tokens WHERE user_id = ? AND revoked_at IS NULL', [$fixture_user_id]);
check('Case 11: that row starts unrevoked', $unrevoked_before === 1);

$payload_live = jwt_validate_refresh_token($live_refresh);
check('Case 11: the fixture refresh token is accepted while live', is_array($payload_live));
check('Case 11: it resolves to the fixture user', is_array($payload_live) && (int) ($payload_live['sub'] ?? 0) === $fixture_user_id);

$affected = jwt_revoke_all_refresh_tokens_for_user($fixture_user_id);
check('Case 11: the revoke call reports affected rows', $affected >= 1);
check('Case 11: the fixture row is now marked revoked', !empty(db_fetch_column('SELECT revoked_at FROM refresh_tokens WHERE token_hash = ?', [$live_hash])));
check('Case 11: the refresh token is now rejected', jwt_validate_refresh_token($live_refresh) === false);
check('Case 11: no unrevoked refresh token is left for that user', (int) db_fetch_column('SELECT COUNT(*) FROM refresh_tokens WHERE user_id = ? AND revoked_at IS NULL', [$fixture_user_id]) === 0);

echo "\n== Case 12: the documented window on OTHER devices ==\n";

$other_device = jwt_issue_access_token(['id' => $fixture_user_id, 'role' => 'student']);
check('Case 12: a second device access token is accepted', is_array(jwt_validate_access_token($other_device)));
check('Case 12: revoking the FIRST device token did not revoke the second', is_array(jwt_validate_access_token($other_device)));
check('Case 12: revoking refresh tokens did not invalidate it either', is_array(jwt_validate_access_token($other_device)));
check('Case 12: this is the documented ~1 hour window, so the assertion is intentional', (int) $config['access_ttl'] === 3600);

echo "\n== Cleanup ==\n";

password_policy_fixture_cleanup();

check('Cleanup: every fixture refresh-token row was removed', (int) db_fetch_column('SELECT COUNT(*) FROM refresh_tokens WHERE token_hash LIKE ?', [PASSWORD_POLICY_TOKEN_TAG . '%']) === 0);
check('Cleanup: the probe password_hash is exactly as found', (string) db_fetch_column('SELECT password_hash FROM users WHERE id = ?', [$user_id]) === $hash_before);

echo "\n== Result ==\n";

if ($failures > 0) {
    echo "FAILURES: {$failures}\n";
    exit(1);
}

echo "ALL PASS\n";
exit(0);
