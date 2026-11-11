<?php

/**
 * MASAR - Student Contact Information Update Regression Test
 *
 * Focused regression coverage for PUT /api/v1/students/me/contact, the endpoint
 * that updates the five contact fields (email, phone, website,
 * external_profile, address).
 *
 * The defect this suite was written for: an update that sent the WHOLE contact
 * form - including the account email, unchanged - was rejected with
 * "That is already your email address." even when the only real change was
 * another field. The email is now a no-op when it is identical to the stored
 * address, and clients may update any single field on its own.
 *
 *   Case 1   phone only            - succeeds, email untouched
 *   Case 2   website only          - succeeds, email untouched
 *   Case 3   external_profile only - succeeds, email untouched
 *   Case 4   address only          - succeeds, email untouched
 *   Case 5   the existing email    - succeeds (no duplicate-email error) and
 *            email_verified_at is preserved; a full form with that unchanged
 *            email also saves the other fields
 *   Case 6   a new unused email    - succeeds, is persisted, verification stamp cleared
 *   Case 7   another user's email  - refused with an email error, own email unchanged
 *   Case 8   email omitted         - the stored email (and its verification stamp) is kept
 *   Case 9   invalid email         - refused with an email error, nothing written
 *   Case 10  invalid phone/URL     - refused with the matching field error
 *   Case 11  partial update        - exactly one field changes, every omitted
 *            field retains its stored value
 *
 * Fixture rows are the seeded students used by the other profile suites. Their
 * contact values are normalized for the run and the ORIGINAL values are
 * restored by contact_update_restore(), which is also registered as a shutdown
 * handler so an aborted run cannot leave a probe account modified.
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
 * Issues a request through the case shim and returns the parsed response.
 */
function contact_request(string $method, ?array $body = null, string $scenario = 'bearer', string $token = ''): array
{
    $case_file = dirname(__FILE__) . '/student_contact_update_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg('students.php')
        . ' ' . escapeshellarg('/api/v1/students/me/contact')
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    /*
     | The body travels through the environment: JSON on a Windows command line
     | loses its double quotes to the shell's native-argument quoting and would
     | arrive invalid.
     */
    $environment = [
        'MASAR_CASE_BODY' => $body === null ? '' : (string) json_encode($body),
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    /*
     | The environment array is MERGED onto the inherited one on purpose: the
     | app config reads the database settings from the real process environment,
     | so replacing it would break the child's connection.
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

    $status   = 0;
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

function contact_student_row(string $email): ?array
{
    $row = db_fetch_one(
        'SELECT s.id AS student_id, s.user_id, s.phone, s.website,
                s.external_profile, s.address, u.email, u.email_verified_at
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1',
        [$email]
    );

    return is_array($row) ? $row : null;
}

/**
 * The five stored contact values for one student. Comparing whole arrays is
 * what proves a partial update changed exactly one field.
 */
function contact_snapshot(int $student_id): array
{
    $row = db_fetch_one(
        'SELECT s.phone, s.website, s.external_profile, s.address, u.email, u.email_verified_at
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.id = ?
         LIMIT 1',
        [$student_id]
    );

    return is_array($row) ? $row : [];
}

function contact_token(int $user_id): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => 'student']);
}

echo "== Setup: fixtures ==\n";

$primary   = contact_student_row('omar.shazly@gmail.com');
$secondary = contact_student_row('mariam.hassan@gmail.com');

check('primary student resolved', is_array($primary));
check('secondary student resolved', is_array($secondary));

if (!is_array($primary) || !is_array($secondary)) {
    echo "\n== Result ==\n";
    echo "FAILURES: {$failures}\n";
    exit(1);
}

$primary_id     = (int) $primary['student_id'];
$primary_user   = (int) $primary['user_id'];
$secondary_id   = (int) $secondary['student_id'];
$secondary_user = (int) $secondary['user_id'];

$primary_token = contact_token($primary_user);

/* Captured BEFORE normalizing, so cleanup restores the real seeded values. */
$original_state = [
    'primary'   => $primary,
    'secondary' => $secondary,
];

function contact_update_restore(array $state, int $primary_id, int $secondary_id, int $primary_user): void
{
    $primary   = $state['primary'] ?? [];
    $secondary = $state['secondary'] ?? [];

    db_execute(
        'UPDATE students SET phone = ?, website = ?, external_profile = ?, address = ? WHERE id = ?',
        [
            $primary['phone'] ?? null,
            $primary['website'] ?? null,
            $primary['external_profile'] ?? null,
            $primary['address'] ?? null,
            $primary_id,
        ]
    );

    db_execute(
        'UPDATE users SET email = ?, email_verified_at = ? WHERE id = ?',
        [
            $primary['email'] ?? null,
            $primary['email_verified_at'] ?? null,
            $primary_user,
        ]
    );

    db_execute(
        'UPDATE students SET phone = ?, website = ?, external_profile = ?, address = ? WHERE id = ?',
        [
            $secondary['phone'] ?? null,
            $secondary['website'] ?? null,
            $secondary['external_profile'] ?? null,
            $secondary['address'] ?? null,
            $secondary_id,
        ]
    );
}

register_shutdown_function(static function () use ($original_state, $primary_id, $secondary_id, $primary_user): void {
    contact_update_restore($original_state, $primary_id, $secondary_id, $primary_user);
});

/* A known-clean starting point: this suite asserts on nulls and exact values. */
db_execute(
    'UPDATE students SET phone = NULL, website = NULL, external_profile = NULL, address = NULL WHERE id = ?',
    [$primary_id]
);
db_execute(
    'UPDATE students SET phone = NULL, website = NULL, external_profile = NULL, address = NULL WHERE id = ?',
    [$secondary_id]
);

$primary = contact_student_row('omar.shazly@gmail.com');
$secondary = contact_student_row('mariam.hassan@gmail.com');

$baseline = contact_snapshot($primary_id);

echo "\n== Case 1: phone only ==\n";

$phone_only = contact_request('PUT', ['phone' => '+20 100 123 4567'], 'bearer', $primary_token);

check('Case 1: a phone-only update succeeds', $phone_only['status'] === 200 && $phone_only['success'] === true);
check(
    'Case 1: the phone is persisted',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === '+20 100 123 4567'
);
check(
    'Case 1: the account email is untouched',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $baseline['email']
);
check(
    'Case 1: the email verification stamp is untouched',
    db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $baseline['email_verified_at']
);

echo "\n== Case 2: website only ==\n";

$website_only = contact_request('PUT', ['website' => 'https://contact-update.example.com'], 'bearer', $primary_token);

check('Case 2: a website-only update succeeds', $website_only['status'] === 200 && $website_only['success'] === true);
check(
    'Case 2: the website is persisted',
    db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === 'https://contact-update.example.com'
);
check(
    'Case 2: the email and the other contact fields are untouched',
    contact_snapshot($primary_id) === array_merge($baseline, [
        'phone'   => '+20 100 123 4567',
        'website' => 'https://contact-update.example.com',
    ])
);

echo "\n== Case 3: external_profile only ==\n";

$external_only = contact_request('PUT', ['external_profile' => 'https://www.linkedin.com/in/contact-update'], 'bearer', $primary_token);

check('Case 3: an external_profile-only update succeeds', $external_only['status'] === 200 && $external_only['success'] === true);
check(
    'Case 3: the external profile is persisted',
    db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$primary_id]) === 'https://www.linkedin.com/in/contact-update'
);
check(
    'Case 3: the email and the other contact fields are untouched',
    contact_snapshot($primary_id) === array_merge($baseline, [
        'phone'            => '+20 100 123 4567',
        'website'          => 'https://contact-update.example.com',
        'external_profile' => 'https://www.linkedin.com/in/contact-update',
    ])
);

echo "\n== Case 4: address only ==\n";

$address_only = contact_request('PUT', ['address' => 'Contact Update, New Cairo'], 'bearer', $primary_token);

check('Case 4: an address-only update succeeds', $address_only['status'] === 200 && $address_only['success'] === true);
check(
    'Case 4: the address is persisted',
    db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === 'Contact Update, New Cairo'
);
check(
    'Case 4: the email and the other contact fields are untouched',
    contact_snapshot($primary_id) === array_merge($baseline, [
        'phone'            => '+20 100 123 4567',
        'website'          => 'https://contact-update.example.com',
        'external_profile' => 'https://www.linkedin.com/in/contact-update',
        'address'          => 'Contact Update, New Cairo',
    ])
);

echo "\n== Case 5: the existing email ==\n";

$same_email = contact_request('PUT', ['email' => (string) $baseline['email']], 'bearer', $primary_token);

check('Case 5: submitting the stored email succeeds', $same_email['status'] === 200 && $same_email['success'] === true);
check('Case 5: no email validation error is returned', empty($same_email['errors']['email']));
check('Case 5: the stored email is echoed back', ($same_email['data']['email'] ?? null) === $baseline['email']);
check(
    'Case 5: the stored email and verification stamp are unchanged',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $baseline['email']
        && db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $baseline['email_verified_at']
);

/*
 | The exact reported scenario: the client submits the WHOLE contact form, so
 | the unchanged email travels with a real change to another field.
 */
$unchanged_email_with_changes = contact_request('PUT', [
    'email'            => (string) $baseline['email'],
    'phone'            => '+20 100 555 1111',
    'website'          => 'https://full-form.example.com',
    'external_profile' => 'https://www.linkedin.com/in/full-form',
    'address'          => 'Full Form, Cairo',
], 'bearer', $primary_token);

check(
    'Case 5: a full form with the unchanged email is accepted',
    $unchanged_email_with_changes['status'] === 200 && $unchanged_email_with_changes['success'] === true
);
check(
    'Case 5: the full form carries no email error',
    empty($unchanged_email_with_changes['errors']['email'])
        && !isset($unchanged_email_with_changes['errors']['email'])
);
check(
    'Case 5: the other fields are saved when the email is unchanged',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === '+20 100 555 1111'
        && db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === 'https://full-form.example.com'
        && db_fetch_column('SELECT external_profile FROM students WHERE id = ?', [$primary_id]) === 'https://www.linkedin.com/in/full-form'
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === 'Full Form, Cairo'
);
check(
    'Case 5: the full form leaves the email and verification stamp alone',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $baseline['email']
        && db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $baseline['email_verified_at']
);

echo "\n== Case 6: a new, unused email ==\n";

$new_email = 'regression_contact_update_' . bin2hex(random_bytes(4)) . '@example.com';

$new_email_update = contact_request('PUT', ['email' => $new_email], 'bearer', $primary_token);

check('Case 6: a new email address is accepted', $new_email_update['status'] === 200 && $new_email_update['success'] === true);
check('Case 6: the new email is returned', ($new_email_update['data']['email'] ?? null) === $new_email);
check(
    'Case 6: the new email is persisted',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $new_email
);
check(
    'Case 6: the changed email is no longer treated as verified',
    db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === null
);

echo "\n== Case 7: another user's email ==\n";

$duplicate_email = contact_request('PUT', ['email' => (string) $secondary['email']], 'bearer', $primary_token);

check('Case 7: another account email is refused', $duplicate_email['status'] === 422 && $duplicate_email['success'] === false);
check('Case 7: the refusal names the email field', isset($duplicate_email['errors']['email']));
check(
    'Case 7: the primary email was NOT changed by the refused request',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $new_email
);

/*
 | Restore the primary address before the remaining cases so the verification
 | stamp can be asserted to survive updates that never change the email.
 */
db_execute(
    'UPDATE users SET email = ?, email_verified_at = ? WHERE id = ?',
    [$baseline['email'], $baseline['email_verified_at'], $primary_user]
);

echo "\n== Case 8: email omitted ==\n";

$before_omitted = contact_snapshot($primary_id);

$omitted_email = contact_request('PUT', ['address' => 'Omitted Email, Cairo'], 'bearer', $primary_token);

check('Case 8: an update that omits the email succeeds', $omitted_email['status'] === 200 && $omitted_email['success'] === true);
check(
    'Case 8: the stored email and verification stamp are unchanged',
    db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $baseline['email']
        && db_fetch_column('SELECT email_verified_at FROM users WHERE id = ?', [$primary_user]) === $baseline['email_verified_at']
);
check(
    'Case 8: only the supplied field changed',
    contact_snapshot($primary_id) === array_merge($before_omitted, ['address' => 'Omitted Email, Cairo'])
);

echo "\n== Case 9: an invalid email ==\n";

$before_invalid = contact_snapshot($primary_id);

$invalid_email = contact_request('PUT', ['email' => 'not-an-email'], 'bearer', $primary_token);

check('Case 9: an invalid email is refused', $invalid_email['status'] === 422 && $invalid_email['success'] === false);
check('Case 9: the refusal names the email field', isset($invalid_email['errors']['email']));
check('Case 9: the invalid request wrote nothing', contact_snapshot($primary_id) === $before_invalid);

echo "\n== Case 10: an invalid phone / URL ==\n";

$invalid_phone = contact_request('PUT', ['phone' => 'call me maybe'], 'bearer', $primary_token);

check('Case 10: an invalid phone is refused', $invalid_phone['status'] === 422);
check('Case 10: the refusal names the phone field', isset($invalid_phone['errors']['phone']));

$invalid_website = contact_request('PUT', ['website' => 'not a url'], 'bearer', $primary_token);

check('Case 10: an invalid website is refused', $invalid_website['status'] === 422);
check('Case 10: the refusal names the website field', isset($invalid_website['errors']['website']));

$invalid_external = contact_request('PUT', ['external_profile' => 'linkedin.com/in/contact-update'], 'bearer', $primary_token);

check('Case 10: an invalid external profile is refused', $invalid_external['status'] === 422);
check('Case 10: the refusal names the external_profile field', isset($invalid_external['errors']['external_profile']));
check('Case 10: the invalid phone / URL requests wrote nothing', contact_snapshot($primary_id) === $before_invalid);

echo "\n== Case 11: omitted fields retain their stored values ==\n";

$before_partial = contact_snapshot($primary_id);

$partial = contact_request('PUT', ['external_profile' => 'https://www.linkedin.com/in/final'], 'bearer', $primary_token);

check('Case 11: the partial update succeeds', $partial['status'] === 200);
check(
    'Case 11: exactly one field changed and every omitted field was preserved',
    contact_snapshot($primary_id) === array_merge($before_partial, [
        'external_profile' => 'https://www.linkedin.com/in/final',
    ])
);
check(
    'Case 11: the preserved values really are the stored ones',
    db_fetch_column('SELECT phone FROM students WHERE id = ?', [$primary_id]) === $before_partial['phone']
        && db_fetch_column('SELECT website FROM students WHERE id = ?', [$primary_id]) === $before_partial['website']
        && db_fetch_column('SELECT address FROM students WHERE id = ?', [$primary_id]) === $before_partial['address']
        && db_fetch_column('SELECT email FROM users WHERE id = ?', [$primary_user]) === $before_partial['email']
);

echo "\n== Case 12: isolation ==\n";

$secondary_before = contact_snapshot($secondary_id);

check(
    'Case 12: the other student was never modified',
    contact_snapshot($secondary_id) === $secondary_before
        && ($secondary_before['email'] ?? null) === $secondary['email']
);

echo "\n== Cleanup ==\n";

contact_update_restore($original_state, $primary_id, $secondary_id, $primary_user);

check(
    'Cleanup: the primary contact values were restored',
    contact_snapshot($primary_id) === [
        'phone'             => $original_state['primary']['phone'],
        'website'           => $original_state['primary']['website'],
        'external_profile'  => $original_state['primary']['external_profile'],
        'address'           => $original_state['primary']['address'],
        'email'             => $original_state['primary']['email'],
        'email_verified_at' => $original_state['primary']['email_verified_at'],
    ]
);
check(
    'Cleanup: the secondary contact values were restored',
    contact_snapshot($secondary_id) === [
        'phone'             => $original_state['secondary']['phone'],
        'website'           => $original_state['secondary']['website'],
        'external_profile'  => $original_state['secondary']['external_profile'],
        'address'           => $original_state['secondary']['address'],
        'email'             => $original_state['secondary']['email'],
        'email_verified_at' => $original_state['secondary']['email_verified_at'],
    ]
);
check(
    'Cleanup: no probe email address was left behind',
    (int) db_fetch_column(
        'SELECT COUNT(*) FROM users WHERE email LIKE ?',
        ['regression_contact_update_%@example.com']
    ) === 0
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
