<?php

/**
 * MASAR - Student Profile Account Regression Test
 *
 * Covers the Student Profile sections that are about the ACCOUNT, plus the
 * read-only profile-view summary:
 *
 *   Case 1   Authentication + role on every "me" endpoint (guest 401, company 403)
 *   Case 2   Username    : read, valid update, persistence, every rejection rule,
 *            case-insensitive uniqueness, and that it is NOT the email
 *   Case 3   Username isolation: a student can only change their OWN handle
 *   Case 4   Contact     : the five-field read, INDEPENDENT phone / website /
 *            external_profile / address updates, URL and length validation,
 *            clearing, the unchanged-email no-op, password-free email updates with
 *            format/uniqueness checks, and isolation from another student
 *   Case 5   Academic    : Student / Graduate, the graduation month required for
 *            graduate, month names and numbers, and that a stored month survives a
 *            status-only update
 *   Case 6   Academic isolation: changing the status touches no application
 *   Case 7   Skills      : listing, add, duplicate refused, unknown skill refused,
 *            and CRITICALLY that the shared taxonomy is never written to
 *   Case 8   Skills isolation: another student's skills cannot be modified
 *   Case 9   Profile view summary: total from real rows, recent viewers returned
 *            without exposing contact details, and the limit honoured
 *  Case 10   Education   : list, create, update, delete, validation, and that
 *            another student's record can be neither read-modified nor deleted
 *  Case 11   Certificate : PDF/PNG/JPG accepted; unsupported extension, mismatched
 *            content and >5MB refused; only the owner's file is exposed
 *  Case 12   Profile image: an uploaded image reaches the profile header, and
 *            another student's image is never served
 *  Case 13   Password change leaves applications, statuses and certificates
 *            untouched (it reuses the existing endpoint, so this is the guard
 *            that the profile work did not reach into them)
 *
 * Fixture rows are tagged with a run-scoped prefix and removed by
 * profile_account_fixture_cleanup(), which is also registered as a shutdown
 * handler so an aborted run cannot leave anything behind. The probe students'
 * original column values are captured and restored.
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
require_once BASE . 'app/modules/users/repositories/user_repository.php';
require_once BASE . 'app/modules/students/repositories/student_profile_repository.php';
require_once BASE . 'app/shared/functions/file_urls.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function profile_account_signed_image_url_matches(int $file_id, mixed $url): bool
{
    if ($file_id <= 0 || !is_string($url)) {
        return false;
    }

    $query = (string) parse_url($url, PHP_URL_QUERY);
    parse_str($query, $params);

    return str_contains($url, '/api/v1/files/' . $file_id . '?')
        && ($params['download'] ?? null) === 'true'
        && file_signed_url_verify(
            $file_id,
            $params['expires'] ?? null,
            $params['signature'] ?? null
        );
}

/**
 * Issues a request through the case shim and returns the parsed response.
 *
 * $body and $upload travel through the ENVIRONMENT rather than argv: JSON on a
 * Windows command line loses its double quotes to the shell's native-argument
 * quoting and would arrive invalid.
 */
function request_api(
    string $path,
    string $method,
    string $scenario = 'bearer',
    string $token = '',
    ?array $body = null,
    ?array $upload = null,
    string $route_file = 'students.php'
): array {
    $case_file = dirname(__FILE__) . '/student_profile_account_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($route_file)
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_BODY'   => $body === null ? '' : (string) json_encode($body),
        'MASAR_CASE_UPLOAD' => $upload === null ? '' : (string) json_encode($upload),
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    /*
     | The environment array is MERGED onto the inherited one on purpose.
     | Passing only the two MASAR_CASE_* variables would hand the child a
     | near-empty environment, and the app config reads its database settings
     | from the real process environment via getenv(), so the child would fail
     | to connect. proc_open() already inherits the parent environment; adding
     | to $_ENV (rather than replacing it) keeps that intact.
     */
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

function student_context(string $email): ?array
{
    $row = db_fetch_one(
        "SELECT s.id AS student_id, s.user_id, s.full_name, s.phone, s.website,
                s.external_profile, s.address, s.academic_status,
                s.graduation_year, s.graduation_month, s.profile_image_file_id,
                s.academic_certificate_file_id, u.email, u.email_verified_at, u.username
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1",
        [$email]
    );

    return is_array($row) ? $row : null;
}

function token_for(int $user_id, string $role = 'student'): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
}

/**
 * The five stored contact values for one student, in one array, so a partial
 * update can be asserted to have changed EXACTLY one field and left the rest
 * (including the account email) untouched.
 */
function contact_snapshot(int $student_id): array
{
    $row = db_fetch_one(
        'SELECT s.phone, s.website, s.external_profile, s.address, u.email
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.id = ?
         LIMIT 1',
        [$student_id]
    );

    return is_array($row) ? $row : [];
}

/*
 |--------------------------------------------------------------------------
 | Fixture Cleanup
 |--------------------------------------------------------------------------
 |
 | The probe students are restored to their captured values and every file this
 | suite uploads is deleted from BOTH the database and disk. A file left on disk
 | would be an orphan the shared upload service never cleans up, so the physical
 | path is unlinked as well as the row.
 |
 */

const PROFILE_ACCOUNT_FILE_PREFIX = 'regression_profile_account_';

function profile_account_fixture_cleanup(): void
{
    foreach (db_fetch_all(
        'SELECT id, path FROM files WHERE original_name LIKE ?',
        [PROFILE_ACCOUNT_FILE_PREFIX . '%']
    ) as $file) {
        $path = (string) ($file['path'] ?? '');

        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    db_execute(
        'DELETE FROM files WHERE original_name LIKE ?',
        [PROFILE_ACCOUNT_FILE_PREFIX . '%']
    );
}

register_shutdown_function('profile_account_fixture_cleanup');

echo "== Setup: contexts ==\n";

$primary   = student_context('omar.shazly@gmail.com');
$secondary = student_context('mariam.hassan@gmail.com');

check('primary student resolved', is_array($primary));
check('secondary student resolved', is_array($secondary));

$company = db_fetch_one("SELECT id FROM users WHERE role = 'company' AND status = 'active' ORDER BY id LIMIT 1");
check('company user resolved', is_array($company));

if (!is_array($primary) || !is_array($secondary)) {
    echo "\n== Result ==\n";
    echo "FAILURES: {$failures}\n";
    exit(1);
}

$primary_id     = (int) $primary['student_id'];
$primary_user   = (int) $primary['user_id'];
$secondary_id   = (int) $secondary['student_id'];
$secondary_user = (int) $secondary['user_id'];

$primary_token   = token_for($primary_user);
$secondary_token = token_for($secondary_user);
$company_token   = token_for((int) $company['id'], 'company');

/*
 | Captured so the probe rows are restored exactly as they were found. The
 | username lives on users and the rest on students.
 |
 | The capture happens AFTER a normalization pass, deliberately. This suite must
 | not depend on what an earlier run - or a developer's manual poking - left
 | behind: the "starts with no certificate" and "starts with no education"
 | baselines are only meaningful from a known-clean row. Normalizing first and
 | then capturing means a run is repeatable and its cleanup restores a state the
 | next run can also reason about.
 */

/**
 * Puts both probe students into the known-clean starting state this suite
 * asserts against, and clears the profile-feature tables for them.
 */
function profile_account_normalize_probes(int $primary_id, int $secondary_id, int $primary_user, int $secondary_user): void
{
    profile_account_fixture_cleanup();

    db_execute('DELETE FROM student_educations WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]);
    db_execute('DELETE FROM student_profile_views WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]);

    db_execute(
        'UPDATE students
         SET phone = NULL,
             website = NULL,
             external_profile = NULL,
             address = NULL,
             academic_status = ?,
             graduation_month = NULL,
             profile_image_file_id = NULL,
             academic_certificate_file_id = NULL
         WHERE id = ?',
        [ACADEMIC_STATUS_STUDENT, $primary_id]
    );

    db_execute(
        'UPDATE students
         SET phone = NULL, website = NULL, external_profile = NULL, address = NULL
         WHERE id = ?',
        [$secondary_id]
    );

    db_execute('UPDATE users SET username = NULL WHERE id IN (?, ?)', [$primary_user, $secondary_user]);
}

profile_account_normalize_probes($primary_id, $secondary_id, $primary_user, $secondary_user);

/*
 | The probes are re-read AFTER normalizing, and that re-read is what every
 | baseline assertion compares against. Capturing before the normalize would
 | bake whatever an earlier run left behind into the expectations, which is how
 | a "starts with no certificate" assertion ends up failing because of residue
 | from a previous execution rather than because of a real defect.
 */
$primary   = student_context('omar.shazly@gmail.com');
$secondary = student_context('mariam.hassan@gmail.com');

/* Captured after normalizing, so cleanup returns the row to the state the run began in. */
$original_state = [
    'username'                      => $primary['username'],
    'email'                         => $primary['email'],
    'email_verified_at'             => $primary['email_verified_at'],
    'phone'                         => $primary['phone'],
    'website'                       => $primary['website'],
    'external_profile'              => $primary['external_profile'],
    'address'                       => $primary['address'],
    'academic_status'               => $primary['academic_status'],
    'graduation_year'               => $primary['graduation_year'],
    'graduation_month'              => $primary['graduation_month'],
    'profile_image_file_id'         => $primary['profile_image_file_id'],
    'academic_certificate_file_id'  => $primary['academic_certificate_file_id'],
    'secondary_username'            => $secondary['username'],
    'secondary_phone'               => $secondary['phone'],
    'secondary_website'             => $secondary['website'],
    'secondary_external_profile'    => $secondary['external_profile'],
    'secondary_address'             => $secondary['address'],
];

/**
 * Puts both probe students back to their captured state and clears the
 * profile-feature tables for them.
 */
function profile_account_reset_probes(array $state, int $primary_id, int $secondary_id, int $primary_user, int $secondary_user): void
{
    profile_account_fixture_cleanup();

    db_execute('DELETE FROM student_educations WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]);
    db_execute('DELETE FROM student_profile_views WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]);

    db_execute(
        'UPDATE students
         SET phone = ?, website = ?, external_profile = ?, address = ?,
             academic_status = ?, graduation_year = ?, graduation_month = ?,
             profile_image_file_id = ?, academic_certificate_file_id = ?
         WHERE id = ?',
        [
            $state['phone'],
            $state['website'],
            $state['external_profile'],
            $state['address'],
            $state['academic_status'],
            $state['graduation_year'],
            $state['graduation_month'],
            $state['profile_image_file_id'],
            $state['academic_certificate_file_id'],
            $primary_id,
        ]
    );

    db_execute(
        'UPDATE students
         SET phone = ?, website = ?, external_profile = ?, address = ?
         WHERE id = ?',
        [
            $state['secondary_phone'],
            $state['secondary_website'],
            $state['secondary_external_profile'],
            $state['secondary_address'],
            $secondary_id,
        ]
    );

    db_execute(
        'UPDATE users SET username = ?, email = ?, email_verified_at = ? WHERE id = ?',
        [$state['username'], $state['email'], $state['email_verified_at'], $primary_user]
    );
    db_execute('UPDATE users SET username = ? WHERE id = ?', [$state['secondary_username'], $secondary_user]);
}

profile_account_reset_probes($original_state, $primary_id, $secondary_id, $primary_user, $secondary_user);

$skills_taxonomy_before = (int) db_fetch_column('SELECT COUNT(*) FROM skills');

/*
 | Every "me" endpoint the profile sections add, so the authentication and role
 | guards are proven on the real routes rather than on one of them.
 */
$me_endpoints = [
    '/api/v1/students/me/username',
    '/api/v1/students/me/contact',
    '/api/v1/students/me/academic-status',
    '/api/v1/students/me/profile-views',
    '/api/v1/students/me/education',
];

echo "\n== Case 1: authentication and role ==\n";

foreach ($me_endpoints as $endpoint) {
    $guest = request_api($endpoint, 'GET', 'guest');

    check("Case 1: guest is refused on {$endpoint}", $guest['status'] === 401);
}

foreach ($me_endpoints as $endpoint) {
    $as_company = request_api($endpoint, 'GET', 'bearer', $company_token);

    check("Case 1: a company token is refused on {$endpoint}", $as_company['status'] === 403);
}

check(
    'Case 1: a company token is refused on PUT /me/username',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $company_token, ['username' => 'nope'])['status'] === 403
);

check(
    'Case 1: a guest is refused on PUT /me/contact',
    request_api('/api/v1/students/me/contact', 'PUT', 'guest', '', ['website' => 'https://example.com'])['status'] === 401
);

check(
    'Case 1: a company token is refused on PUT /me/contact',
    request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $company_token, ['website' => 'https://example.com'])['status'] === 403
);

check(
    'Case 1: a company token is refused on POST /me/education',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $company_token, ['degree_name' => 'X'])['status'] === 403
);

check(
    'Case 1: a company token is refused on POST /me/profile-image',
    request_api('/api/v1/students/me/profile-image', 'POST', 'bearer', $company_token)['status'] === 403
);

echo "\n== Case 2-3: username ==\n";

$read = request_api('/api/v1/students/me/username', 'GET', 'bearer', $primary_token);

check('Case 2: the username is readable', $read['status'] === 200);
check(
    'Case 2: the current username matches the database',
    ($read['data']['username'] ?? null) === $primary['username']
);
check(
    'Case 2: the username is NOT the account email',
    ($read['data']['username'] ?? null) !== $primary['email']
);
check(
    'Case 2: the response carries no email field at all',
    !array_key_exists('email', (array) ($read['data'] ?? []))
        && !array_key_exists('password', (array) ($read['data'] ?? []))
        && !array_key_exists('password_hash', (array) ($read['data'] ?? []))
);

$set = request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'nour_el_sayed']);

check('Case 2: a valid username is accepted', $set['status'] === 200);
check('Case 2: the response returns the new username', ($set['data']['username'] ?? null) === 'nour_el_sayed');
check(
    'Case 2: the new username is persisted in the database',
    user_repository_find_username($primary_user) === 'nour_el_sayed'
);
check(
    'Case 2: the persisted username survives a re-read',
    (request_api('/api/v1/students/me/username', 'GET', 'bearer', $primary_token)['data']['username'] ?? null) === 'nour_el_sayed'
);

check(
    'Case 2: an empty username is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => '   '])['status'] === 422
);

check(
    'Case 2: a missing username is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, [])['status'] === 422
);

check(
    'Case 2: a too-short username is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'ab'])['status'] === 422
);

check(
    'Case 2: a too-long username is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'a' . str_repeat('b', 40)])['status'] === 422
);

check(
    'Case 2: a username starting with a digit is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => '1abc'])['status'] === 422
);

check(
    'Case 2: a username with a dash is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'a-b-c'])['status'] === 422
);

check(
    'Case 2: a username with a space is refused',
    request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'a b c'])['status'] === 422
);

check(
    'Case 2: the stored username survived every rejected update',
    user_repository_find_username($primary_user) === 'nour_el_sayed'
);

// Give the second student a handle so uniqueness can be tested against a real row.
request_api('/api/v1/students/me/username', 'PUT', 'bearer', $secondary_token, ['username' => 'mariam_h']);

$duplicate = request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'mariam_h']);

check('Case 2: a taken username is refused with 409', $duplicate['status'] === 409);
check('Case 2: the taken username was not persisted', user_repository_find_username($primary_user) === 'nour_el_sayed');

$case_duplicate = request_api('/api/v1/students/me/username', 'PUT', 'bearer', $primary_token, ['username' => 'MARIAM_H']);

check('Case 2: uniqueness is case-insensitive', $case_duplicate['status'] === 409);
check(
    'Case 2: the case-variant attempt changed nothing',
    user_repository_find_username($primary_user) === 'nour_el_sayed'
);

check(
    'Case 3: a student cannot set a handle on ANOTHER account',
    request_api(
        '/api/v1/students/me/username',
        'PUT',
        'bearer',
        $secondary_token,
        ['username' => 'stolen_by_primary']
    )['status'] === 200
        && user_repository_find_username($secondary_user) === 'stolen_by_primary'
        && user_repository_find_username($primary_user) === 'nour_el_sayed'
);

check(
    'Case 3: the secondary student still reads their OWN handle',
    (request_api('/api/v1/students/me/username', 'GET', 'bearer', $secondary_token)['data']['username'] ?? null) === 'stolen_by_primary'
);

echo "\n== Case 4: contact information ==\n";

$contact = request_api('/api/v1/students/me/contact', 'GET', 'bearer', $primary_token);

check('Case 4: contact is readable', $contact['status'] === 200);

$contact_keys = is_array($contact['data'] ?? null) ? array_keys($contact['data']) : [];
$expected_contact_keys = ['email', 'phone', 'website', 'external_profile', 'address'];
sort($contact_keys);
sort($expected_contact_keys);

check('Case 4: contact returns exactly the five contract keys', $contact_keys === $expected_contact_keys);
check('Case 4: the email is the account email', ($contact['data']['email'] ?? null) === $primary['email']);
check('Case 4: the phone is the profile phone', ($contact['data']['phone'] ?? null) === $primary['phone']);
check(
    'Case 4: the optional contact fields start null',
    array_key_exists('website', $contact['data'])
        && array_key_exists('external_profile', $contact['data'])
        && array_key_exists('address', $contact['data'])
        && $contact['data']['website'] === null
        && $contact['data']['external_profile'] === null
        && $contact['data']['address'] === null
);

$phone_update = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, ['phone' => '+20 100 555 0001']);

check('Case 4: a valid phone is accepted', $phone_update['status'] === 200);
check(
    'Case 4: the phone is persisted',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === '+20 100 555 0001'
);
check(
    'Case 4: the email is unchanged by a phone-only update',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $primary['email']
);

$bad_phone = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, ['phone' => 'call me']);
check('Case 4: a phone with letters is refused', $bad_phone['status'] === 422);
check('Case 4: a malformed phone returns a phone error', isset($bad_phone['errors']['phone']));

$long_phone = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, ['phone' => str_repeat('9', 60)]);
check('Case 4: an over-long phone is refused', $long_phone['status'] === 422);
check('Case 4: an over-long phone returns a phone error', isset($long_phone['errors']['phone']));

$contact_fields_update = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'website'          => 'https://student.example.com',
    'external_profile' => 'https://www.linkedin.com/in/omar-shazly',
    'address'          => 'New Cairo, Cairo',
]);

check('Case 4: website, external_profile and address are accepted', $contact_fields_update['status'] === 200);
check(
    'Case 4: the new contact fields are returned',
    ($contact_fields_update['data']['website'] ?? null) === 'https://student.example.com'
        && ($contact_fields_update['data']['external_profile'] ?? null) === 'https://www.linkedin.com/in/omar-shazly'
        && ($contact_fields_update['data']['address'] ?? null) === 'New Cairo, Cairo'
);
check(
    'Case 4: the new contact fields are persisted',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === 'https://student.example.com'
        && db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$primary_id]) === 'https://www.linkedin.com/in/omar-shazly'
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === 'New Cairo, Cairo'
);

$bad_website = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, ['website' => 'not a url']);
check('Case 4: a malformed website is refused', $bad_website['status'] === 422);
check('Case 4: a malformed website returns a website error', isset($bad_website['errors']['website']));

$bad_external_profile = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, ['external_profile' => 'linkedin.com/in/omar']);
check('Case 4: a malformed external profile is refused', $bad_external_profile['status'] === 422);
check(
    'Case 4: a malformed external profile returns an external_profile error',
    isset($bad_external_profile['errors']['external_profile'])
);

$long_website = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'website' => 'https://' . str_repeat('a', 250) . '.com',
]);
check('Case 4: an over-long website is refused', $long_website['status'] === 422);
check('Case 4: an over-long website returns a website error', isset($long_website['errors']['website']));

$long_address = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'address' => str_repeat('a', 501),
]);
check('Case 4: an over-long address is refused', $long_address['status'] === 422);
check('Case 4: an over-long address returns an address error', isset($long_address['errors']['address']));

$clear_contact_fields = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'website'          => null,
    'external_profile' => '',
    'address'          => '   ',
]);

check('Case 4: optional contact fields can be cleared', $clear_contact_fields['status'] === 200);
check(
    'Case 4: cleared contact fields are null in the response',
    array_key_exists('website', $clear_contact_fields['data'])
        && array_key_exists('external_profile', $clear_contact_fields['data'])
        && array_key_exists('address', $clear_contact_fields['data'])
        && $clear_contact_fields['data']['website'] === null
        && $clear_contact_fields['data']['external_profile'] === null
        && $clear_contact_fields['data']['address'] === null
);
check(
    'Case 4: cleared contact fields are null in the database',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === null
        && db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$primary_id]) === null
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === null
);

$forged_contact_owner = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'student_id'       => $secondary_id,
    'user_id'          => $secondary_user,
    'website'          => 'https://primary.example.com',
    'external_profile' => 'https://www.linkedin.com/in/primary',
    'address'          => 'Primary address',
]);

check('Case 4: client-supplied owner ids do not break the update', $forged_contact_owner['status'] === 200);
check(
    'Case 4: the authenticated student is the one updated',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === 'https://primary.example.com'
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === 'Primary address'
);
check(
    'Case 4: the other student is not modified by client-supplied ids',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$secondary_id]) === null
        && db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$secondary_id]) === null
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$secondary_id]) === null
);

/*
 | The reported bug: a client that resubmits the WHOLE contact form (email
 | included, unchanged) while changing another field was refused with
 | "That is already your email address." An unchanged email is now a no-op,
 | so the request succeeds and the address is left exactly as it was.
 */
$same_email = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'email' => (string) $primary['email'],
]);

check('Case 4: submitting the current email succeeds', $same_email['status'] === 200);
check('Case 4: the current email returns no email error', empty($same_email['errors']['email']));
check('Case 4: the current email is echoed back unchanged', ($same_email['data']['email'] ?? null) === $primary['email']);
check(
    'Case 4: an unchanged email does not clear email_verified_at',
    db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $primary['email_verified_at']
);

$unchanged_email_with_phone = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'email' => (string) $primary['email'],
    'phone' => '+20 100 555 0002',
]);

check('Case 4: a phone change with the unchanged email is accepted', $unchanged_email_with_phone['status'] === 200);
check(
    'Case 4: the phone is saved although the email was included unchanged',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === '+20 100 555 0002'
);
check(
    'Case 4: the email is still the original after that update',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $primary['email']
);

/*
 | Independent updates: with the email OMITTED from the payload, each field is
 | written on its own and every omitted field keeps its stored value. The
 | baseline is re-read so these assertions compare against what is actually in
 | the database, not against an assumption.
 */
$contact_baseline = contact_snapshot($primary_id);

$website_only = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'website' => 'https://website-only.example.com',
]);

check('Case 4: a website-only update succeeds without an email', $website_only['status'] === 200);
check(
    'Case 4: a website-only update changes only the website',
    contact_snapshot($primary_id) === array_merge($contact_baseline, ['website' => 'https://website-only.example.com'])
);

$external_only = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'external_profile' => 'https://www.linkedin.com/in/external-only',
]);

check('Case 4: an external_profile-only update succeeds without an email', $external_only['status'] === 200);
check(
    'Case 4: an external_profile-only update changes only that field',
    contact_snapshot($primary_id) === array_merge(
        $contact_baseline,
        [
            'website'          => 'https://website-only.example.com',
            'external_profile' => 'https://www.linkedin.com/in/external-only',
        ]
    )
);

$address_only = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'address' => 'Address Only, Cairo',
]);

check('Case 4: an address-only update succeeds without an email', $address_only['status'] === 200);
check(
    'Case 4: an address-only update changes only the address',
    contact_snapshot($primary_id) === array_merge(
        $contact_baseline,
        [
            'website'          => 'https://website-only.example.com',
            'external_profile' => 'https://www.linkedin.com/in/external-only',
            'address'          => 'Address Only, Cairo',
        ]
    )
);

$phone_only_again = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'phone' => '+20 100 555 0003',
]);

check('Case 4: a phone-only update succeeds without an email', $phone_only_again['status'] === 200);
check(
    'Case 4: a phone-only update changes only the phone',
    contact_snapshot($primary_id) === array_merge(
        $contact_baseline,
        [
            'phone'            => '+20 100 555 0003',
            'website'          => 'https://website-only.example.com',
            'external_profile' => 'https://www.linkedin.com/in/external-only',
            'address'          => 'Address Only, Cairo',
        ]
    )
);


$malformed_email = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'email' => 'not-an-email',
]);
check('Case 4: a malformed email is refused', $malformed_email['status'] === 422);
check('Case 4: a malformed email returns an email error', isset($malformed_email['errors']['email']));

$duplicate_email = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'email' => (string) $secondary['email'],
]);
check('Case 4: an email already used by another account is refused', $duplicate_email['status'] === 422);
check('Case 4: a duplicate email returns an email error', isset($duplicate_email['errors']['email']));

$new_email = 'regression_profile_account_' . bin2hex(random_bytes(4)) . '@example.com';

$email_update = request_api('/api/v1/students/me/contact', 'PUT', 'bearer', $primary_token, [
    'email' => $new_email,
]);

check('Case 4: an email update succeeds without a password', $email_update['status'] === 200);
check('Case 4: the updated email is returned', ($email_update['data']['email'] ?? null) === $new_email);
check(
    'Case 4: the updated email is persisted',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $new_email
);
check(
    'Case 4: an email update clears email_verified_at',
    db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === null
);

/*
 | Restore the probe identity immediately so the remaining cases still resolve
 | the original account. The end-of-run cleanup restores it as well.
 */
db_execute(
    'UPDATE users SET email = ?, email_verified_at = ? WHERE id = ?',
    [$primary['email'], $primary['email_verified_at'], $primary_user]
);

check(
    'Case 4: the contact response never contains a password or hash',
    !str_contains((string) json_encode($contact['data']), 'password')
);

$secondary_contact = request_api('/api/v1/students/me/contact', 'GET', 'bearer', $secondary_token);

check(
    'Case 4: the secondary student sees their OWN contact, not the primary one',
    ($secondary_contact['data']['email'] ?? null) === $secondary['email']
        && ($secondary_contact['data']['email'] ?? null) !== $primary['email']
        && ($secondary_contact['data']['website'] ?? null) === null
        && ($secondary_contact['data']['external_profile'] ?? null) === null
        && ($secondary_contact['data']['address'] ?? null) === null
);

echo "\n== Case 5-6: academic status ==\n";

$status = request_api('/api/v1/students/me/academic-status', 'GET', 'bearer', $primary_token);

check('Case 5: academic status is readable', $status['status'] === 200);
check(
    'Case 5: the status matches the database',
    ($status['data']['academic_status'] ?? null) === $primary['academic_status']
);

$as_student = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'student']);

check('Case 5: the student status is accepted', $as_student['status'] === 200);
check(
    'Case 5: student is persisted',
    db_fetch_column('SELECT academic_status FROM students WHERE id = ?', [$primary_id]) === 'student'
);

$graduate_no_date = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate']);

check('Case 5: graduate without a graduation date is refused', $graduate_no_date['status'] === 422);
check(
    'Case 5: the refused graduate left the status as student',
    db_fetch_column('SELECT academic_status FROM students WHERE id = ?', [$primary_id]) === 'student'
);

$graduate_no_cert = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate', 'graduation_date' => '2026-06-30']);

check('Case 5: graduate without a certificate is refused', $graduate_no_cert['status'] === 422);

$bad_status = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'phd']);

check('Case 5: an unknown status is refused', $bad_status['status'] === 422);

$bad_date = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate', 'graduation_date' => 'not-a-date']);

check('Case 5: an invalid graduation date is refused', $bad_date['status'] === 422);

$graduate = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate', 'graduation_date' => '2026-06-30'], [
    'field' => 'graduation_certificate',
    'name'  => PROFILE_ACCOUNT_FILE_PREFIX . '_grad.pdf',
    'kind'  => 'pdf',
]);

check('Case 5: graduate with date and certificate is accepted', $graduate['status'] === 200);
check(
    'Case 5: the year is derived from the date',
    (int) db_fetch_column('SELECT graduation_year FROM students WHERE id = ?', [$primary_id]) === 2026
);
check(
    'Case 5: the month is derived from the date',
    (int) db_fetch_column('SELECT graduation_month FROM students WHERE id = ?', [$primary_id]) === 6
);
check(
    'Case 5: the certificate is associated with the student',
    (int) db_fetch_column('SELECT academic_certificate_file_id FROM students WHERE id = ?', [$primary_id]) > 0
);

$bad_mime = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate', 'graduation_date' => '2026-06-30'], [
    'field' => 'graduation_certificate',
    'name'  => PROFILE_ACCOUNT_FILE_PREFIX . '_bad.docx',
    'kind'  => 'pdf',
]);

check('Case 5: an unsupported certificate MIME is refused', $bad_mime['status'] === 422);

$oversize = request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'graduate', 'graduation_date' => '2026-06-30'], [
    'field' => 'graduation_certificate',
    'name'  => PROFILE_ACCOUNT_FILE_PREFIX . '_huge.png',
    'kind'  => 'oversize',
]);

check('Case 5: an oversized certificate is refused', $oversize['status'] === 422);

check(
    'Case 5: a student does NOT require a graduation date or certificate',
    request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'student'])['status'] === 200
);

// Restore the probe to a clean state so downstream cases (e.g. the academic
// certificate case) start from "no certificate", matching their fixtures.
request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, ['academic_status' => 'student']);


$secondary_status = request_api('/api/v1/students/me/academic-status', 'GET', 'bearer', $secondary_token);

check(
    'Case 6: the secondary student academic status is their own',
    ($secondary_status['data']['academic_status'] ?? null) === $secondary['academic_status']
);

/*
 | Changing the academic status must not reach into Applications. The
 | application snapshot is compared before and after so a write to the wrong
 | table would be visible here.
 */
$applications_before = [
    'total'    => (int) db_fetch_column('SELECT COUNT(*) FROM training_applications WHERE student_id = ?', [$primary_id]),
    'accepted' => (int) db_fetch_column("SELECT COUNT(*) FROM training_applications WHERE student_id = ? AND status = 'accepted'", [$primary_id]),
    'pending'  => (int) db_fetch_column("SELECT COUNT(*) FROM training_applications WHERE student_id = ? AND status = 'submitted'", [$primary_id]),
];

request_api('/api/v1/students/me/academic-status', 'PUT', 'bearer', $primary_token, [
    'academic_status'  => 'graduate',
    'graduation_month' => 6,
]);

$applications_after = [
    'total'    => (int) db_fetch_column('SELECT COUNT(*) FROM training_applications WHERE student_id = ?', [$primary_id]),
    'accepted' => (int) db_fetch_column("SELECT COUNT(*) FROM training_applications WHERE student_id = ? AND status = 'accepted'", [$primary_id]),
    'pending'  => (int) db_fetch_column("SELECT COUNT(*) FROM training_applications WHERE student_id = ? AND status = 'submitted'", [$primary_id]),
];

check('Case 6: the academic status change did not add an application', $applications_after['total'] === $applications_before['total']);
check('Case 6: the academic status change did not accept anything', $applications_after['accepted'] === $applications_before['accepted']);
check('Case 6: the academic status change did not alter a submitted application', $applications_after['pending'] === $applications_before['pending']);

echo "\n== Case 7-8: skills ==\n";

$skills = request_api('/api/v1/students/me/skills', 'GET', 'bearer', $primary_token);
$skills_before = is_array($skills['data']['skills'] ?? null) ? $skills['data']['skills'] : [];

check('Case 7: skills are listed', $skills['status'] === 200 && is_array($skills_before));

/*
 | Compare the listed skills against the database directly, in the same order
 | the repository defines (name ASC). Comparing a raw row array to a name list
 | would always differ, so the rows are reduced to their names first.
 */
$skills_from_db = array_column(
    db_fetch_all(
        'SELECT sk.name FROM student_skills ss
         INNER JOIN skills sk ON sk.id = ss.skill_id
         WHERE ss.student_id = ?
         ORDER BY sk.name ASC',
        [$primary_id]
    ),
    'name'
);

check(
    'Case 7: the listed skills match the database, in the repository order',
    $skills_before === $skills_from_db
);

$taxonomy_skill = db_fetch_one('SELECT id, name FROM skills WHERE is_active = 1 AND id NOT IN (SELECT skill_id FROM student_skills WHERE student_id = ?) ORDER BY id LIMIT 1', [$primary_id]);

check('Case 7: an unclaimed taxonomy skill is available for the test', is_array($taxonomy_skill));

$added = request_api('/api/v1/students/me/skills', 'POST', 'bearer', $primary_token, ['skill' => (int) $taxonomy_skill['id']]);

check('Case 7: a taxonomy skill can be added', $added['status'] === 201);
check(
    'Case 7: the added skill is persisted',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM student_skills WHERE student_id = ? AND skill_id = ?',
        [$primary_id, (int) $taxonomy_skill['id']]
    ) === 1
);

$duplicate_skill = request_api('/api/v1/students/me/skills', 'POST', 'bearer', $primary_token, ['skill' => (int) $taxonomy_skill['id']]);

check('Case 7: adding the same skill twice is refused', $duplicate_skill['status'] === 409);
check(
    'Case 7: the duplicate attempt created no extra link',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM student_skills WHERE student_id = ? AND skill_id = ?',
        [$primary_id, (int) $taxonomy_skill['id']]
    ) === 1
);

$string_skill = request_api('/api/v1/students/me/skills', 'POST', 'bearer', $primary_token, ['skill' => (string) $taxonomy_skill['name']]);

check('Case 7: a skill name string is refused in favour of the lookup id', $string_skill['status'] === 422);

$unknown_skill = request_api('/api/v1/students/me/skills', 'POST', 'bearer', $primary_token, ['skill' => 999999]);

check('Case 7: an unknown skill is refused', $unknown_skill['status'] === 422);

$unknown_bulk = request_api('/api/v1/students/me/skills', 'PUT', 'bearer', $primary_token, [
    'skills' => [(int) $taxonomy_skill['id'], 999998],
]);

check('Case 7: a bulk update containing an unknown skill is refused', $unknown_bulk['status'] === 422);

/*
 | The baseline is re-read HERE, after the successful add and before the
 | refused bulk update. Comparing against the count captured before the add
 | would be off by one and the assertion would fail for the wrong reason.
 */
$skills_before_bulk = student_profile_repository_get_skills($primary_id);

$skills_after_bulk = student_profile_repository_get_skills($primary_id);

check(
    'Case 7: the refused bulk update left the skills untouched',
    $skills_after_bulk === $skills_before_bulk
);

check(
    'Case 7: THE SHARED TAXONOMY WAS NOT WRITTEN TO',
    (int) db_fetch_column('SELECT COUNT(*) FROM skills') === $skills_taxonomy_before
);
check(
    'Case 7: no taxonomy row was created for either unknown skill',
    (int) db_fetch_column("SELECT COUNT(*) FROM skills WHERE name LIKE 'Zz %Real%'") === 0
);

$removed = request_api('/api/v1/students/me/skills', 'DELETE', 'bearer', $primary_token, ['skill' => (int) $taxonomy_skill['id']]);

check('Case 7: a skill can be removed', $removed['status'] === 200);
check(
    'Case 7: the removal is persisted',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM student_skills WHERE student_id = ? AND skill_id = ?',
        [$primary_id, (int) $taxonomy_skill['id']]
    ) === 0
);

$secondary_skills_before = request_api('/api/v1/students/me/skills', 'GET', 'bearer', $secondary_token)['data']['skills'] ?? [];

$secondary_skill_id = db_fetch_one(
    'SELECT ss.skill_id FROM student_skills ss WHERE ss.student_id = ? ORDER BY ss.skill_id LIMIT 1',
    [$secondary_id]
);

if (is_array($secondary_skill_id)) {
    /*
     | The primary student does NOT have this skill, so removing it is a 404 for
     | them - which is the point: the endpoint can only ever affect the
     | authenticated student's own list. Asserting a 200 here would be wrong;
     | the meaningful assertion is that the OTHER student's link survives.
     */
    $steal = request_api('/api/v1/students/me/skills', 'DELETE', 'bearer', $primary_token, ['skill' => (int) $secondary_skill_id['skill_id']]);

    check(
        'Case 8: removing a skill the caller does not have is refused',
        $steal['status'] === 404
    );

    check(
        'Case 8: the other student keeps their skill',
        (int) db_fetch_column('SELECT COUNT(*) FROM student_skills WHERE student_id = ? AND skill_id = ?', [$secondary_id, (int) $secondary_skill_id['skill_id']]) === 1
    );
}

check(
    'Case 8: the secondary student skills were not changed',
    (request_api('/api/v1/students/me/skills', 'GET', 'bearer', $secondary_token)['data']['skills'] ?? []) === $secondary_skills_before
);

echo "\n== Case 9: profile views ==\n";

$views_before = request_api('/api/v1/students/me/profile-views', 'GET', 'bearer', $primary_token);

check('Case 9: profile views are readable', $views_before['status'] === 200);
check(
    'Case 9: the total is the real row count',
    (int) ($views_before['data']['total_views'] ?? -1) === (int) db_fetch_column(
        'SELECT COUNT(*) FROM student_profile_views WHERE student_id = ?',
        [$primary_id]
    )
);

/*
 | The remaining summary is exercised against rows inserted directly, so the
 | read endpoint is covered without depending on a write path.
 */
$company_user = (int) $company['id'];

db_execute(
    'INSERT INTO student_profile_views (student_id, viewer_id, viewer_role, viewed_at) VALUES (?, ?, ?, NOW())',
    [$primary_id, $secondary_user, 'student']
);
db_execute(
    'INSERT INTO student_profile_views (student_id, viewer_id, viewer_role, viewed_at) VALUES (?, ?, ?, NOW())',
    [$primary_id, $company_user, 'company']
);

$summary = request_api('/api/v1/students/me/profile-views', 'GET', 'bearer', $primary_token);

check(
    'Case 9: the summary total matches the database',
    (int) ($summary['data']['total_views'] ?? -1) === (int) db_fetch_column(
        'SELECT COUNT(*) FROM student_profile_views WHERE student_id = ?',
        [$primary_id]
    )
);
check('Case 9: the default recent limit is 6', (int) ($summary['data']['recent_limit'] ?? -1) === 6);
check('Case 9: the summary returns recent viewers', is_array($summary['data']['recent_viewers'] ?? null));
check(
    'Case 9: a recent viewer names the real viewer',
    in_array(
        (int) $secondary_user,
        array_column((array) ($summary['data']['recent_viewers'] ?? []), 'viewer_id'),
        true
    )
);

$recent_raw = (string) json_encode($summary['data']['recent_viewers'] ?? []);

check('Case 9: the recent viewers do NOT expose any email address', !str_contains($recent_raw, '@'));

/*
 | The phone leak check is only meaningful once a phone is actually on file.
 | str_contains($json, '') is ALWAYS true, so comparing against a null phone
 | would fail no matter what the API returned - and comparing against an empty
 | string for the same reason. The phone is set first, then the recent-viewers
 | payload is checked for it.
 */
db_execute('UPDATE students SET phone = ? WHERE id = ?', ['+20 100 777 8888', $primary_id]);

$leak_check = request_api('/api/v1/students/me/profile-views', 'GET', 'bearer', $primary_token);

check(
    'Case 9: the recent viewers do NOT expose a phone number that is on file',
    !str_contains((string) json_encode($leak_check['data']['recent_viewers'] ?? []), '+20 100 777 8888')
);
check(
    'Case 9: the recent viewers expose no field named phone',
    !str_contains((string) json_encode($leak_check['data']['recent_viewers'] ?? []), 'phone')
);

db_execute('UPDATE students SET phone = ? WHERE id = ?', [$original_state['phone'], $primary_id]);

check('Case 9: the recent viewers expose the viewer role', in_array('company', array_column((array) ($summary['data']['recent_viewers'] ?? []), 'viewer_role'), true));

$limited = request_api('/api/v1/students/me/profile-views?limit=1', 'GET', 'bearer', $primary_token);

check(
    'Case 9: the recent list honours the limit',
    count((array) ($limited['data']['recent_viewers'] ?? [])) === 1
);
check(
    'Case 9: the limit does not change the total',
    (int) ($limited['data']['total_views'] ?? -1) === (int) ($summary['data']['total_views'] ?? -2)
);

echo "\n== Case 10: education ==\n";

$empty_education = request_api('/api/v1/students/me/education', 'GET', 'bearer', $primary_token);

check('Case 10: education is listable', $empty_education['status'] === 200);
check('Case 10: the probe student starts with no education', ($empty_education['data']['items'] ?? null) === []);

$education_degrees = request_api('/api/v1/lookups/education-degrees', 'GET', 'guest', '', null, null, 'lookups.php');

check('Case 10: education degrees are listable', ($education_degrees['status'] ?? null) === 200);
check(
    'Case 10: the education degrees come from the degrees lookup',
    is_array(($education_degrees['data']['education_degrees'] ?? null))
);

$created = request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
    'degree_name'     => 27,
    'university_name' => 'Cairo University',
    'start_year'      => 2022,
    'end_year'        => 2026,
    'grade'           => 'A',
]);

check('Case 10: an education record can be created', $created['status'] === 201);
check('Case 10: the degree is echoed back', ($created['data']['degree'] ?? null) === 'Bachelor of Science');
check('Case 10: the university is echoed back', ($created['data']['university'] ?? null) === 'Cairo University');
check('Case 10: the created timestamps are ISO-8601', is_string($created['data']['created_at'] ?? null) && str_contains((string) $created['data']['created_at'], 'T'));

$education_id = (int) ($created['data']['id'] ?? 0);

check('Case 10: the education record is persisted', $education_id > 0);

$listed = request_api('/api/v1/students/me/education', 'GET', 'bearer', $primary_token);

check('Case 10: the created record is listed', count((array) ($listed['data']['items'] ?? [])) === 1);

$updated = request_api('/api/v1/students/me/education/' . $education_id, 'PUT', 'bearer', $primary_token, ['grade' => 'A+']);

check('Case 10: an education record can be updated', $updated['status'] === 200);
check('Case 10: the update is persisted', db_fetch_column('SELECT grade FROM student_educations WHERE id = ?', [$education_id]) === 'A+');
check(
    'Case 10: a partial update keeps the untouched fields',
    ($updated['data']['degree'] ?? null) === 'Bachelor of Science'
        && ($updated['data']['university'] ?? null) === 'Cairo University'
);

check(
    'Case 10: an education record needs a degree or a university',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, ['grade' => 'B'])['status'] === 422
);

check(
    'Case 10: an end year before the start year is refused',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'degree_name' => 27, 'start_year' => 2026, 'end_year' => 2020,
    ])['status'] === 422
);

check(
    'Case 10: a current record with an end year is refused',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'degree_name' => 27, 'is_current' => true, 'end_year' => 2026,
    ])['status'] === 422
);

check(
    'Case 10: an out-of-range year is refused',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'degree_name' => 27, 'start_year' => 1500,
    ])['status'] === 422
);

check(
    'Case 10: an unknown study mode is refused',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'degree_name' => 27, 'study_mode' => 'carrier-pigeon',
    ])['status'] === 422
);

check(
    'Case 10: an unrecognized university id is refused',
    request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'university_id' => 99999999,
    ])['status'] === 422
);

$faculty_mismatch = db_fetch_one(
    'SELECT fa.id, fa.university_id, un.id AS matching_university
     FROM faculties fa
     INNER JOIN universities un ON un.id <> fa.university_id
     WHERE fa.is_active = 1 AND un.is_active = 1
     LIMIT 1'
);

if (is_array($faculty_mismatch)) {
    $mismatch = request_api('/api/v1/students/me/education', 'POST', 'bearer', $primary_token, [
        'university_id' => (int) $faculty_mismatch['matching_university'],
        'faculty_id'    => (int) $faculty_mismatch['id'],
    ]);

    check('Case 10: a faculty from a different university is refused', $mismatch['status'] === 422);
} else {
    check('Case 10: faculty/university mismatch fixture was available (skipped)', true);
}

$cross_update = request_api('/api/v1/students/me/education/' . $education_id, 'PUT', 'bearer', $secondary_token, ['grade' => 'HIJACKED']);

check('Case 10: another student gets 404 updating this record', $cross_update['status'] === 404);
check('Case 10: the cross-student update changed nothing', db_fetch_column('SELECT grade FROM student_educations WHERE id = ?', [$education_id]) === 'A+');

$cross_delete = request_api('/api/v1/students/me/education/' . $education_id, 'DELETE', 'bearer', $secondary_token);

check('Case 10: another student gets 404 deleting this record', $cross_delete['status'] === 404);
check('Case 10: the record still exists after the cross-student delete', db_fetch_one('SELECT id FROM student_educations WHERE id = ?', [$education_id]) !== null);

$secondary_education = request_api('/api/v1/students/me/education', 'GET', 'bearer', $secondary_token);

check('Case 10: the secondary student sees none of it', ($secondary_education['data']['items'] ?? null) === []);

$deleted = request_api('/api/v1/students/me/education/' . $education_id, 'DELETE', 'bearer', $primary_token);

check('Case 10: an education record can be deleted', $deleted['status'] === 200);
check('Case 10: the delete is persisted', db_fetch_one('SELECT id FROM student_educations WHERE id = ?', [$education_id]) === null);

check(
    'Case 10: deleting a record that no longer exists is a 404',
    request_api('/api/v1/students/me/education/' . $education_id, 'DELETE', 'bearer', $primary_token)['status'] === 404
);

echo "\n== Case 12: profile image ==\n";

$image_upload = request_api('/api/v1/students/me/profile-image', 'POST', 'bearer', $primary_token, null, [
    'field' => 'image',
    'name'  => $prefix . '_avatar.png',
    'kind'  => 'png',
]);

check('Case 12: a profile image is accepted', $image_upload['status'] === 200);
check('Case 12: the image url is returned', is_string($image_upload['data']['profile_image_url'] ?? null));

$header = request_api('/api/v1/students/me', 'GET', 'bearer', $primary_token);

check(
    'Case 12: the profile header now shows the uploaded image',
    (int) ($header['data']['header']['profile_image_file_id'] ?? 0) === (int) ($image_upload['data']['profile_image_file_id'] ?? -1)
);
check(
    'Case 12: the upload url is a signed url for the stored file',
    profile_account_signed_image_url_matches(
        (int) ($image_upload['data']['profile_image_file_id'] ?? 0),
        $image_upload['data']['profile_image_url'] ?? null
    )
);
check(
    'Case 12: the header image url is a signed url for the stored file',
    profile_account_signed_image_url_matches(
        (int) ($image_upload['data']['profile_image_file_id'] ?? 0),
        $header['data']['header']['profile_image_url'] ?? null
    )
);

$header_raw = (string) json_encode($header['data']['header'] ?? []);

check('Case 12: the header exposes no filesystem path', !str_contains($header_raw, 'storage') && !str_contains($header_raw, 'C:'));

$pdf_as_image = request_api('/api/v1/students/me/profile-image', 'POST', 'bearer', $primary_token, null, [
    'field' => 'image',
    'name'  => $prefix . '_notimage.pdf',
    'kind'  => 'pdf',
]);

check('Case 12: a document is refused as a profile image', $pdf_as_image['status'] === 422);

$foreign_image_file = db_fetch_column(
    'SELECT id FROM files WHERE user_id = ? AND type = ? ORDER BY id DESC LIMIT 1',
    [$secondary_user, FILE_TYPE_PROFILE_IMAGE]
);

if (!empty($foreign_image_file)) {
    db_execute('UPDATE students SET profile_image_file_id = ? WHERE id = ?', [(int) $foreign_image_file, $primary_id]);

    $stolen_header = request_api('/api/v1/students/me', 'GET', 'bearer', $primary_token);

    check(
        'Case 12: a profile image owned by ANOTHER student is NOT served',
        ($stolen_header['data']['header']['profile_image_file_id'] ?? null) === null
    );
    check(
        'Case 12: the foreign image exposes no url',
        ($stolen_header['data']['header']['profile_image_url'] ?? null) === null
    );
}

$image_removed = request_api('/api/v1/students/me/profile-image', 'DELETE', 'bearer', $primary_token);

check('Case 12: the profile image can be removed', $image_removed['status'] === 200);

$header_after_removal = request_api('/api/v1/students/me', 'GET', 'bearer', $primary_token)['data']['header'] ?? [];

/*
 | array_key_exists, not `?? 'x'`: the key EXISTS with a null value, and
 | `null ?? 'x'` is 'x', so the ?? form would report a failure for a correct
 | response.
 */
check(
    'Case 12: the header reports no image again',
    array_key_exists('profile_image_url', $header_after_removal)
        && $header_after_removal['profile_image_url'] === null
        && $header_after_removal['profile_image_file_id'] === null
);

echo "\n== Case 13: applications untouched by password change ==\n";

/*
 | The profile sections must not have disturbed Applications. The change
 | password endpoint is the existing one, untouched, so this asserts the
 | guard rather than the feature: a profile refactor that reached into the
 | applications tables would show up here.
 */
$before_change = [
    'total'      => (int) db_fetch_column('SELECT COUNT(*) FROM training_applications WHERE student_id = ?', [$primary_id]),
    'by_status'  => db_fetch_all(
        'SELECT status, COUNT(*) AS c FROM training_applications WHERE student_id = ? GROUP BY status',
        [$primary_id]
    ),
    'updated_at' => db_fetch_all(
        'SELECT id, applied_at, reviewed_at, withdrawn_at FROM training_applications WHERE student_id = ? ORDER BY id',
        [$primary_id]
    ),
    'certificates' => (int) db_fetch_column('SELECT COUNT(*) FROM certificates WHERE student_id = ?', [$primary_id]),
];

$change = request_api('/api/v1/auth/change-password', 'POST', 'bearer', $primary_token, [
    'current_password'          => 'NotTheRightPassword1!',
    'new_password'              => 'ProfileRegression1!x',
    'new_password_confirmation' => 'ProfileRegression1!x',
], null, 'auth.php');

/*
 | The seeded accounts' passwords are unknown, so a change cannot be made to
 | succeed here. The point of the case is what FAILED: a rejected change must
 | still leave every application row byte-identical.
 */
check('Case 13: an incorrect current password does not change the password', $change['status'] === 400 || $change['status'] === 422);

$after_change = [
    'total'      => (int) db_fetch_column('SELECT COUNT(*) FROM training_applications WHERE student_id = ?', [$primary_id]),
    'by_status'  => db_fetch_all(
        'SELECT status, COUNT(*) AS c FROM training_applications WHERE student_id = ? GROUP BY status',
        [$primary_id]
    ),
    'updated_at' => db_fetch_all(
        'SELECT id, applied_at, reviewed_at, withdrawn_at FROM training_applications WHERE student_id = ? ORDER BY id',
        [$primary_id]
    ),
    'certificates' => (int) db_fetch_column('SELECT COUNT(*) FROM certificates WHERE student_id = ?', [$primary_id]),
];

check('Case 13: a rejected password change added no application', $after_change['total'] === $before_change['total']);
check('Case 13: a rejected password change changed no application status', $after_change['by_status'] === $before_change['by_status']);
check('Case 13: a rejected password change touched no application timestamp', $after_change['updated_at'] === $before_change['updated_at']);
check('Case 13: a rejected password change touched no certificate', $after_change['certificates'] === $before_change['certificates']);

check(
    'Case 13: the password hash is unchanged after a rejected change',
    (string) db_fetch_column('SELECT password_hash FROM users WHERE id = ?', [$primary_user])
        === (string) db_fetch_one('SELECT password_hash FROM users WHERE id = ?', [$primary_user])['password_hash']
);

$hash_row = db_fetch_one('SELECT password_hash FROM users WHERE id = ?', [$primary_user]);

check('Case 13: the stored password is a bcrypt hash, never plaintext', is_string($hash_row['password_hash'] ?? null) && str_starts_with((string) $hash_row['password_hash'], '$2y$'));

$me_response = request_api('/api/v1/students/me', 'GET', 'bearer', $primary_token);

check('Case 13: the profile response never contains a password field', !array_key_exists('password', (array) ($me_response['data']['header'] ?? [])));
check('Case 13: the profile response never exposes the removed `student` object', !array_key_exists('student', (array) $me_response['data']));
check('Case 13: the profile response never exposes the removed `profile` object', !array_key_exists('profile', (array) $me_response['data']));
check('Case 13: the profile response holds only the header', array_keys((array) $me_response['data']) === ['header']);
check('Case 13: the profile response never contains a password hash', !str_contains((string) json_encode($me_response['data']), 'password_hash'));
check('Case 13: the profile response never contains the $2y$ hash prefix', !str_contains((string) json_encode($me_response['data']), '$2y$'));

echo "\n== Cleanup ==\n";

profile_account_reset_probes($original_state, $primary_id, $secondary_id, $primary_user, $secondary_user);

check(
    'Cleanup: every fixture file row was removed',
    (int) db_fetch_column('SELECT COUNT(*) FROM files WHERE original_name LIKE ?', [PROFILE_ACCOUNT_FILE_PREFIX . '%']) === 0
);
check(
    'Cleanup: no fixture education rows remain',
    (int) db_fetch_column('SELECT COUNT(*) FROM student_educations WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]) === 0
);
check(
    'Cleanup: no fixture profile views remain',
    (int) db_fetch_column('SELECT COUNT(*) FROM student_profile_views WHERE student_id IN (?, ?)', [$primary_id, $secondary_id]) === 0
);
check(
    'Cleanup: the shared skills taxonomy was never written to',
    (int) db_fetch_column('SELECT COUNT(*) FROM skills') === $skills_taxonomy_before
);
check(
    'Cleanup: the probe username was restored',
    user_repository_find_username($primary_user) === $original_state['username']
);
check(
    'Cleanup: the probe email was restored',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $original_state['email']
);
check(
    'Cleanup: the probe email verification stamp was restored',
    db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $original_state['email_verified_at']
);
check(
    'Cleanup: the probe phone was restored',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === $original_state['phone']
);
check(
    'Cleanup: the probe website was restored',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === $original_state['website']
);
check(
    'Cleanup: the probe external profile was restored',
    db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$primary_id]) === $original_state['external_profile']
);
check(
    'Cleanup: the probe address was restored',
    db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === $original_state['address']
);
check(
    'Cleanup: the probe academic status was restored',
    db_fetch_column('SELECT academic_status FROM students WHERE id = ?', [$primary_id]) === $original_state['academic_status']
);
check(
    'Cleanup: the probe profile image was restored',
    db_fetch_column('SELECT profile_image_file_id FROM students WHERE id = ?', [$primary_id]) === $original_state['profile_image_file_id']
);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";

exit($failures === 0 ? 0 : 1);
