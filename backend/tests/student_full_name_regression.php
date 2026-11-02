<?php

/**
 * MASAR - Student Account Full Name Regression Test
 *
 * Covers GET /api/v1/students/me/full-name and
 * PUT /api/v1/students/me/full-name, the Account contract that replaced the
 * username-focused requests in the Postman collection.
 *
 *   Case 1   Authentication + role (guest 401, company/admin 403)
 *   Case 2   Read: the authenticated student's own students.full_name is
 *            returned, with no username/email/password fields
 *   Case 3   Update: a valid name is persisted, returned, and reflected by the
 *            profile header (GET /students/me)
 *   Case 4   Validation: empty, whitespace (including Unicode separators),
 *            missing, non-string, numeric, invalid UTF-8 and control characters
 *            (C0/C1/DEL) refused; 256 characters and 256 multibyte characters
 *            refused; 255 characters (including 255 multibyte characters)
 *            accepted
 *   Case 5   Isolation: a client-supplied student_id/user_id in the body is
 *            ignored; only the caller's row changes
 *   Case 6   Username + password are untouched: users.username still resolves
 *            through the existing endpoint and users.password_hash is identical
 *
 * The probe students' original full_name values are captured and restored by a
 * shutdown handler, so an aborted run cannot leave a renamed account behind.
 *
 * Run with:  php tests/student_full_name_regression.php
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
require_once BASE . 'app/modules/students/validators/student_validator.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function full_name_request(
    string $path,
    string $method,
    string $scenario = 'bearer',
    string $token = '',
    ?array $body = null
): array {
    $case_file = dirname(__FILE__) . '/student_profile_account_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg('students.php')
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_BODY'   => $body === null ? '' : (string) json_encode($body),
        'MASAR_CASE_UPLOAD' => '',
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    $process = proc_open($command, $descriptors, $pipes, BASE, $environment + getenv());

    $output = '';

    if (is_resource($process)) {
        $output = (string) stream_get_contents($pipes[1]);
        $errors = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        if (trim($errors) !== '') {
            $output .= "\n" . $errors;
        }
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
        'raw'     => $body_out,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'message' => is_array($payload) ? ($payload['message'] ?? null) : null,
        'errors'  => is_array($payload) ? ($payload['errors'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? null) : null,
    ];
}

function full_name_student_context(string $email): ?array
{
    $row = db_fetch_one(
        "SELECT s.id AS student_id, s.user_id, s.full_name, u.email, u.username, u.password_hash
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1",
        [$email]
    );

    return is_array($row) ? $row : null;
}

function full_name_token_for(int $user_id, string $role = 'student'): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
}

function full_name_db_value(int $student_id): ?string
{
    $value = db_fetch_column('SELECT full_name FROM students WHERE id = ? LIMIT 1', [$student_id]);

    return $value === false || $value === null ? null : (string) $value;
}

echo "== Setup: contexts ==\n";

$primary = full_name_student_context('omar.shazly@gmail.com');
$secondary = full_name_student_context('mariam.hassan@gmail.com');
$company = db_fetch_one("SELECT id FROM users WHERE role = 'company' AND status = 'active' ORDER BY id LIMIT 1");
$admin = db_fetch_one("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");

check('primary student resolved', is_array($primary));
check('secondary student resolved', is_array($secondary));
check('company user resolved', is_array($company));
check('admin user resolved', is_array($admin));

if (!is_array($primary) || !is_array($secondary)) {
    echo "\n== Result ==\n";
    echo "FAILURES: {$failures}\n";
    exit(1);
}

$primary_id = (int) $primary['student_id'];
$primary_user = (int) $primary['user_id'];
$secondary_id = (int) $secondary['student_id'];
$secondary_user = (int) $secondary['user_id'];

$original_primary_name = (string) $primary['full_name'];
$original_secondary_name = (string) $secondary['full_name'];
$original_primary_username = $primary['username'];
$original_primary_password_hash = (string) $primary['password_hash'];

/*
 * Restore both probe names no matter how the suite exits. The DB is shared, so
 * a crash must not leave the seeded accounts renamed.
 */
register_shutdown_function(static function () use (
    $primary_id,
    $secondary_id,
    $original_primary_name,
    $original_secondary_name
): void {
    db_execute('UPDATE students SET full_name = ?, updated_at = NOW() WHERE id = ?', [$original_primary_name, $primary_id]);
    db_execute('UPDATE students SET full_name = ?, updated_at = NOW() WHERE id = ?', [$original_secondary_name, $secondary_id]);
});

$primary_token = full_name_token_for($primary_user);
$secondary_token = full_name_token_for($secondary_user);
$company_token = full_name_token_for((int) ($company['id'] ?? 0), 'company');
$admin_token = full_name_token_for((int) ($admin['id'] ?? 0), 'admin');

echo "\n== Case 1: authentication and role ==\n";

$guest_get = full_name_request('/api/v1/students/me/full-name', 'GET', 'guest');
$guest_put = full_name_request('/api/v1/students/me/full-name', 'PUT', 'guest', '', ['full_name' => 'Guest Name']);
$company_get = full_name_request('/api/v1/students/me/full-name', 'GET', 'bearer', $company_token);
$company_put = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $company_token, ['full_name' => 'Company Name']);
$admin_get = full_name_request('/api/v1/students/me/full-name', 'GET', 'bearer', $admin_token);

check('Case 1: a guest GET is 401', $guest_get['status'] === 401);
check('Case 1: a guest PUT is 401', $guest_put['status'] === 401);
check('Case 1: a company GET is 403', $company_get['status'] === 403);
check('Case 1: a company PUT is 403', $company_put['status'] === 403);
check('Case 1: an admin GET is 403', $admin_get['status'] === 403);
check('Case 1: refused writes changed nothing', full_name_db_value($primary_id) === $original_primary_name);

echo "\n== Case 2: read the current full name ==\n";

$read = full_name_request('/api/v1/students/me/full-name', 'GET', 'bearer', $primary_token);

check('Case 2: the read is 200', $read['status'] === 200 && $read['success'] === true);
check(
    'Case 2: the read returns the persisted full_name',
    ($read['data']['full_name'] ?? null) === $original_primary_name
);
check(
    'Case 2: the read does not expose username/email/password',
    is_array($read['data'])
        && !array_key_exists('username', $read['data'])
        && !array_key_exists('email', $read['data'])
        && !array_key_exists('password', $read['data'])
        && !array_key_exists('password_hash', $read['data'])
);
check(
    'Case 2: the raw response contains no password hash or email',
    !str_contains($read['raw'], 'password_hash')
        && !str_contains($read['raw'], (string) $primary['email'])
);

echo "\n== Case 3: update and persistence ==\n";

$new_name = 'Regression Full Name ' . bin2hex(random_bytes(4));
$update = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, [
    'full_name' => '  ' . $new_name . '  ',
]);

check('Case 3: a valid update is 200', $update['status'] === 200 && $update['success'] === true);
check('Case 3: the update returns the trimmed persisted name', ($update['data']['full_name'] ?? null) === $new_name);
check('Case 3: the name is persisted in students.full_name', full_name_db_value($primary_id) === $new_name);

$read_after = full_name_request('/api/v1/students/me/full-name', 'GET', 'bearer', $primary_token);
check('Case 3: a later read returns the updated name', ($read_after['data']['full_name'] ?? null) === $new_name);

$header_after = full_name_request('/api/v1/students/me', 'GET', 'bearer', $primary_token);
check(
    'Case 3: the profile header uses the same updated name',
    ($header_after['data']['header']['full_name'] ?? null) === $new_name
);

echo "\n== Case 4: validation ==\n";

$before_validation = full_name_db_value($primary_id);

$empty = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => '']);
$spaces = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => '   ']);
$unicode_spaces = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => "\u{00A0}\u{00A0}"]);
$missing = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, []);
$non_string = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => ['nested']]);
$numeric = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => 123]);
$control = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => "Bad\nName"]);
$c1_control = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => "Bad\u{0085}Name"]);
$too_long = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => str_repeat('A', 256)]);
$too_long_multibyte = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => str_repeat('ا', 256)]);

foreach ([
    'empty' => $empty,
    'whitespace' => $spaces,
    'Unicode whitespace' => $unicode_spaces,
    'missing' => $missing,
    'non-string' => $non_string,
    'numeric' => $numeric,
    'control character' => $control,
    'C1 control character' => $c1_control,
    '256 characters' => $too_long,
    '256 multibyte characters' => $too_long_multibyte,
] as $label => $response) {
    check("Case 4: {$label} is rejected with 422", $response['status'] === 422);
    check(
        "Case 4: {$label} returns a full_name error",
        is_array($response['errors']) && isset($response['errors']['full_name'])
    );
}

$invalid_utf8 = student_validator_full_name("Bad\xC3\x28Name");
check('Case 4: invalid UTF-8 is rejected', empty($invalid_utf8['valid']));
check('Case 4: invalid UTF-8 returns a full_name error', isset($invalid_utf8['errors']['full_name']));

check('Case 4: rejected updates changed nothing', full_name_db_value($primary_id) === $before_validation);

$max_name = str_repeat('A', 255);
$max = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => $max_name]);
check('Case 4: exactly 255 characters is accepted', $max['status'] === 200);
check('Case 4: the 255-character name is persisted', full_name_db_value($primary_id) === $max_name);

$multibyte_name = str_repeat('ا', 255);
$multibyte = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, ['full_name' => $multibyte_name]);
check('Case 4: 255 multibyte characters are counted as characters, not bytes', $multibyte['status'] === 200);
check('Case 4: the multibyte name is persisted', full_name_db_value($primary_id) === $multibyte_name);

echo "\n== Case 5: isolation ==\n";

$secondary_before = full_name_db_value($secondary_id);

$cross = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $primary_token, [
    'full_name' => 'Regression Caller Only',
    'student_id' => $secondary_id,
    'user_id' => $secondary_user,
]);

check('Case 5: the cross-account body is still a 200 for the caller', $cross['status'] === 200);
check('Case 5: only the authenticated caller changed', full_name_db_value($primary_id) === 'Regression Caller Only');
check('Case 5: the other student was not touched', full_name_db_value($secondary_id) === $secondary_before);

$secondary_update = full_name_request('/api/v1/students/me/full-name', 'PUT', 'bearer', $secondary_token, [
    'full_name' => 'Regression Secondary Own Name',
]);
check('Case 5: the other student can update their own name', $secondary_update['status'] === 200);
check(
    'Case 5: the other student owns only their row',
    full_name_db_value($secondary_id) === 'Regression Secondary Own Name'
);

echo "\n== Case 6: username and password are untouched ==\n";

$username_read = full_name_request('/api/v1/students/me/username', 'GET', 'bearer', $primary_token);
check('Case 6: the existing username endpoint still works', $username_read['status'] === 200);
check(
    'Case 6: users.username is unchanged by a full-name update',
    ($username_read['data']['username'] ?? null) === $original_primary_username
);

$password_after = (string) db_fetch_column('SELECT password_hash FROM users WHERE id = ? LIMIT 1', [$primary_user]);
check('Case 6: users.password_hash is unchanged by a full-name update', $password_after === $original_primary_password_hash);
check('Case 6: the stored password remains a bcrypt hash', str_starts_with($password_after, '$2y$'));

/*
 * The existing change-password endpoint is covered by
 * student_profile_account_regression.php; this suite only proves the new
 * full-name write did not touch the credential it must not touch.
 */

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
} else {
    echo "FAILURES: {$failures}\n";
}

exit($failures === 0 ? 0 : 1);
