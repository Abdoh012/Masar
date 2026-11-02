<?php

/**
 * MASAR - File Access Regression Test
 *
 * Covers the two access paths for GET /api/v1/files/{id}:
 *
 *   - the existing Bearer + ownership path used by CVs, certificates and
 *     authenticated downloads;
 *   - the short-lived signed URL used by `profile_image_url`, which a browser
 *     <img> tag can load without an Authorization header.
 *
 *   Case 1   Signed URL shape + signature verification (valid, tampered,
 *            expired, wrong file id)
 *   Case 2   The generated URL resolves through the controller to the real
 *            image bytes and is served inline
 *   Case 3   Guest access: plain URL 401, signed profile image 200, bad
 *            signature 401, expired signature 401
 *   Case 4   Ownership: the owner can download their own image/CV/certificate,
 *            another student gets 404
 *   Case 5   Private files stay private: a signed URL for a CV or an academic
 *            certificate is refused (404), and a guest plain URL is 401
 *   Case 6   Missing files and invalid ids return 404
 *
 * Fixture rows are tagged with a run-scoped prefix and removed by a shutdown
 * handler, which also unlinks the physical files.
 *
 * Run with:  php tests/file_access_regression.php
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
require_once BASE . 'app/shared/functions/file_urls.php';
require_once BASE . 'app/modules/files/services/file_upload_service.php';
require_once BASE . 'app/modules/files/repositories/file_repository.php';
require_once BASE . 'app/modules/files/controllers/file_controller.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function file_access_request(
    string $path,
    string $method = 'GET',
    string $scenario = 'guest',
    string $token = ''
): array {
    $case_file = dirname(__FILE__) . '/file_access_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    $process = proc_open($command, $descriptors, $pipes, BASE, getenv());

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
    $body_b64 = '';

    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, strlen('STATUS='));
        }
        if (str_starts_with($line, 'BODY_B64=')) {
            $body_b64 = substr($line, strlen('BODY_B64='));
        }
    }

    $body = base64_decode($body_b64, true);

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'json' => is_string($body) ? json_decode($body, true) : null,
    ];
}

function file_access_student_context(string $email): ?array
{
    $row = db_fetch_one(
        "SELECT s.id AS student_id, s.user_id, u.email
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1",
        [$email]
    );

    return is_array($row) ? $row : null;
}

function file_access_token_for(int $user_id, string $role = 'student'): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
}

const FILE_ACCESS_PREFIX = 'regression_file_access_';

function file_access_fixture_cleanup(): void
{
    foreach (db_fetch_all(
        'SELECT id, path FROM files WHERE original_name LIKE ?',
        [FILE_ACCESS_PREFIX . '%']
    ) as $file) {
        $path = (string) ($file['path'] ?? '');

        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    db_execute('DELETE FROM files WHERE original_name LIKE ?', [FILE_ACCESS_PREFIX . '%']);
}

register_shutdown_function('file_access_fixture_cleanup');

function file_access_insert(
    int $user_id,
    string $type,
    string $name,
    string $path,
    string $mime,
    int $size
): int {
    db_execute(
        'INSERT INTO files (user_id, type, original_name, stored_name, path, mime_type, size_bytes, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
        [
            $user_id,
            $type,
            FILE_ACCESS_PREFIX . $name,
            FILE_ACCESS_PREFIX . $name,
            $path,
            $mime,
            $size,
        ]
    );

    return (int) db_last_insert_id();
}

function file_access_write(string $relative_dir, string $name, string $bytes): string
{
    $dir = rtrim(file_upload_service_storage_base(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . $relative_dir;

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $path = $dir . DIRECTORY_SEPARATOR . FILE_ACCESS_PREFIX . $name;
    file_put_contents($path, $bytes);

    return $path;
}

function file_access_signed_query(string $url): array
{
    $query = (string) parse_url($url, PHP_URL_QUERY);
    parse_str($query, $params);

    return $params;
}

function file_access_relative_path(int $file_id, array $params): string
{
    $query = ['download' => 'true'];

    if (array_key_exists('expires', $params)) {
        $query['expires'] = (string) $params['expires'];
    }

    if (array_key_exists('signature', $params)) {
        $query['signature'] = (string) $params['signature'];
    }

    return '/api/v1/files/' . $file_id . '?' . http_build_query($query);
}

echo "== Setup: contexts and fixtures ==\n";

$primary = file_access_student_context('omar.shazly@gmail.com');
$secondary = file_access_student_context('mariam.hassan@gmail.com');

check('primary student resolved', is_array($primary));
check('secondary student resolved', is_array($secondary));

if (!is_array($primary) || !is_array($secondary)) {
    echo "\n== Result ==\n";
    echo "FAILURES: {$failures}\n";
    exit(1);
}

$primary_user = (int) $primary['user_id'];
$secondary_user = (int) $secondary['user_id'];

$primary_token = file_access_token_for($primary_user);
$secondary_token = file_access_token_for($secondary_user);

/*
 * A real 1x1 PNG and a minimal PDF. finfo detects both, which is what the
 * signed-image controller re-checks before it serves anything.
 */
$png_bytes = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z4a8AAAAASUVORK5CYII=',
    true
);
$pdf_bytes = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";

$primary_image_path = file_access_write('profile', 'primary.png', $png_bytes);
$secondary_image_path = file_access_write('profile', 'secondary.png', $png_bytes);
$primary_cv_path = file_access_write('general', 'primary_cv.pdf', $pdf_bytes);
$primary_cert_path = file_access_write('general', 'primary_cert.pdf', $pdf_bytes);
$missing_image_path = rtrim(file_upload_service_storage_base(), DIRECTORY_SEPARATOR)
    . DIRECTORY_SEPARATOR . 'profile' . DIRECTORY_SEPARATOR . FILE_ACCESS_PREFIX . 'missing.png';
$fake_image_path = file_access_write('profile', 'fake.png', 'this is not an image');
$fake_image_bytes = 'this is not an image';

$primary_image_id = file_access_insert($primary_user, FILE_TYPE_PROFILE_IMAGE, 'primary.png', $primary_image_path, 'image/png', strlen($png_bytes));
$secondary_image_id = file_access_insert($secondary_user, FILE_TYPE_PROFILE_IMAGE, 'secondary.png', $secondary_image_path, 'image/png', strlen($png_bytes));
$primary_cv_id = file_access_insert($primary_user, FILE_TYPE_CV, 'primary_cv.pdf', $primary_cv_path, 'application/pdf', strlen($pdf_bytes));
$primary_cert_id = file_access_insert($primary_user, FILE_TYPE_ACADEMIC_CERTIFICATE, 'primary_cert.pdf', $primary_cert_path, 'application/pdf', strlen($pdf_bytes));
$missing_image_id = file_access_insert($primary_user, FILE_TYPE_PROFILE_IMAGE, 'missing.png', $missing_image_path, 'image/png', strlen($png_bytes));
$fake_image_id = file_access_insert($primary_user, FILE_TYPE_PROFILE_IMAGE, 'fake.png', $fake_image_path, 'image/png', strlen($fake_image_bytes));

check('the primary profile image fixture exists on disk', is_file($primary_image_path));
check('the primary CV fixture exists on disk', is_file($primary_cv_path));
check('the primary certificate fixture exists on disk', is_file($primary_cert_path));

echo "\n== Case 1: signed URL shape and verification ==\n";

$signed_url = file_signed_url($primary_image_id);
$params = is_string($signed_url) ? file_access_signed_query($signed_url) : [];

check('Case 1: a signed URL is generated', is_string($signed_url) && $signed_url !== '');
check(
    'Case 1: the URL keeps the file id and download shape',
    is_string($signed_url)
        && str_contains($signed_url, '/api/v1/files/' . $primary_image_id . '?')
        && ($params['download'] ?? null) === 'true'
);
check(
    'Case 1: the URL carries expires and a 64-hex signature',
    isset($params['expires'], $params['signature'])
        && ctype_digit((string) $params['expires'])
        && preg_match('/^[0-9a-f]{64}$/', (string) $params['signature']) === 1
);
check(
    'Case 1: the signature verifies for this file',
    file_signed_url_verify($primary_image_id, $params['expires'] ?? null, $params['signature'] ?? null)
);
check(
    'Case 1: the signature is scoped to the file id',
    !file_signed_url_verify($secondary_image_id, $params['expires'] ?? null, $params['signature'] ?? null)
);
check(
    'Case 1: a tampered signature is rejected',
    !file_signed_url_verify($primary_image_id, $params['expires'] ?? null, str_repeat('0', 64))
);
check(
    'Case 1: a missing signature is rejected',
    !file_signed_url_verify($primary_image_id, $params['expires'] ?? null, null)
);
check(
    'Case 1: a malformed signature is rejected',
    !file_signed_url_verify($primary_image_id, $params['expires'] ?? null, 'not-a-hex-signature')
);
check(
    'Case 1: a missing expiry is rejected',
    !file_signed_url_verify($primary_image_id, null, $params['signature'] ?? null)
);

$expired_url = file_signed_url($primary_image_id, time() - 60);
$expired_params = is_string($expired_url) ? file_access_signed_query($expired_url) : [];
check(
    'Case 1: an expired signature is rejected',
    !file_signed_url_verify($primary_image_id, $expired_params['expires'] ?? null, $expired_params['signature'] ?? null)
);
check('Case 1: a non-numeric expires is rejected', !file_signed_url_verify($primary_image_id, 'soon', $params['signature'] ?? null));

echo "\n== Case 2: the generated URL resolves to real image bytes ==\n";

$controller_result = file_controller_signed_profile_image(
    $primary_image_id,
    $params['expires'] ?? null,
    $params['signature'] ?? null
);

check('Case 2: the controller accepts the generated URL', ($controller_result['success'] ?? false) === true);
check('Case 2: the response is marked inline', ($controller_result['inline'] ?? false) === true);
check('Case 2: the response is the image MIME type', ($controller_result['mime_type'] ?? null) === 'image/png');
check('Case 2: the response points at the fixture file', ($controller_result['path'] ?? null) === $primary_image_path);
check('Case 2: the response size matches the fixture', (int) ($controller_result['size'] ?? 0) === strlen($png_bytes));

$guest_signed = file_access_request(file_access_relative_path($primary_image_id, $params));

check('Case 2: the route serves the image without a Bearer token', $guest_signed['status'] === 200);
check(
    'Case 2: the route body is the real PNG bytes',
    is_string($guest_signed['body']) && str_starts_with($guest_signed['body'], "\x89PNG")
);

$fake_signed_url = file_signed_url($fake_image_id);
$fake_params = is_string($fake_signed_url) ? file_access_signed_query($fake_signed_url) : [];
$guest_fake = file_access_request(file_access_relative_path($fake_image_id, $fake_params));
check('Case 2: a profile_image row with non-image bytes is 404', $guest_fake['status'] === 404);
check('Case 2: the refused non-image response is JSON', is_array($guest_fake['json']));

$png_sanitized = file_controller_sanitize_file([
    'id' => 999998,
    'type' => FILE_TYPE_PROFILE_IMAGE,
    'mime_type' => 'image/png',
    'path' => 'unused',
    'storage_path' => 'unused',
]);
check(
    'Case 2: a PNG profile_image row gets a signed URL',
    str_contains((string) ($png_sanitized['url'] ?? ''), 'signature=')
);

$gif_sanitized = file_controller_sanitize_file([
    'id' => 999999,
    'type' => FILE_TYPE_PROFILE_IMAGE,
    'mime_type' => 'image/gif',
    'path' => 'unused',
    'storage_path' => 'unused',
]);
check(
    'Case 2: a GIF profile_image row is not given a signed URL',
    !str_contains((string) ($gif_sanitized['url'] ?? ''), 'signature=')
);
check(
    'Case 2: the GIF row keeps the Bearer download URL',
    str_contains((string) ($gif_sanitized['url'] ?? ''), '?download=true')
);

echo "\n== Case 3: guest access policy ==\n";

$guest_plain = file_access_request('/api/v1/files/' . $primary_image_id . '?download=true');

check('Case 3: a guest plain URL is 401', $guest_plain['status'] === 401);
check(
    'Case 3: the 401 uses the standard error envelope',
    is_array($guest_plain['json']) && ($guest_plain['json']['success'] ?? null) === false
);

$tampered = $params;
$tampered['signature'] = str_repeat('0', 64);
$guest_tampered = file_access_request(file_access_relative_path($primary_image_id, $tampered));
check('Case 3: a tampered signed URL is 401', $guest_tampered['status'] === 401);

$guest_expired = file_access_request(file_access_relative_path($primary_image_id, $expired_params));
check('Case 3: an expired signed URL is 401', $guest_expired['status'] === 401);

$missing_signature = $params;
unset($missing_signature['signature']);
$guest_missing_signature = file_access_request(file_access_relative_path($primary_image_id, $missing_signature));
check('Case 3: a signed URL without a signature is 401', $guest_missing_signature['status'] === 401);

$malformed_signature = $params;
$malformed_signature['signature'] = 'not-a-hex-signature';
$guest_malformed_signature = file_access_request(file_access_relative_path($primary_image_id, $malformed_signature));
check('Case 3: a signed URL with a malformed signature is 401', $guest_malformed_signature['status'] === 401);

$missing_expiry = $params;
unset($missing_expiry['expires']);
$guest_missing_expiry = file_access_request(file_access_relative_path($primary_image_id, $missing_expiry));
check('Case 3: a signed URL without an expiry is 401', $guest_missing_expiry['status'] === 401);

echo "\n== Case 4: ownership ==\n";

$owner_image = file_access_request('/api/v1/files/' . $primary_image_id . '?download=true', 'GET', 'bearer', $primary_token);
check('Case 4: the owner can download their own image', $owner_image['status'] === 200);
check('Case 4: the owner gets the image bytes', str_starts_with($owner_image['body'], "\x89PNG"));

$other_image = file_access_request('/api/v1/files/' . $primary_image_id . '?download=true', 'GET', 'bearer', $secondary_token);
check('Case 4: another student cannot download the image', $other_image['status'] === 404);

$owner_cv = file_access_request('/api/v1/files/' . $primary_cv_id . '?download=true', 'GET', 'bearer', $primary_token);
check('Case 4: the owner can download their own CV', $owner_cv['status'] === 200);
check('Case 4: the CV body is the PDF bytes', str_starts_with($owner_cv['body'], '%PDF'));

$owner_cert = file_access_request('/api/v1/files/' . $primary_cert_id . '?download=true', 'GET', 'bearer', $primary_token);
check('Case 4: the owner can download their own academic certificate', $owner_cert['status'] === 200);

echo "\n== Case 5: CVs and certificates stay private ==\n";

$guest_cv = file_access_request('/api/v1/files/' . $primary_cv_id . '?download=true');
check('Case 5: a guest CV download is 401', $guest_cv['status'] === 401);

$other_cv = file_access_request('/api/v1/files/' . $primary_cv_id . '?download=true', 'GET', 'bearer', $secondary_token);
check('Case 5: another student cannot download the CV', $other_cv['status'] === 404);

$other_cert = file_access_request('/api/v1/files/' . $primary_cert_id . '?download=true', 'GET', 'bearer', $secondary_token);
check('Case 5: another student cannot download the certificate', $other_cert['status'] === 404);

$cv_url = file_signed_url($primary_cv_id);
$cv_params = is_string($cv_url) ? file_access_signed_query($cv_url) : [];
$signed_cv = file_access_request(file_access_relative_path($primary_cv_id, $cv_params));
check('Case 5: a signed URL for a CV is refused', $signed_cv['status'] === 404);
check('Case 5: the refused CV response is JSON, not PDF bytes', is_array($signed_cv['json']));

$cert_url = file_signed_url($primary_cert_id);
$cert_params = is_string($cert_url) ? file_access_signed_query($cert_url) : [];
$signed_cert = file_access_request(file_access_relative_path($primary_cert_id, $cert_params));
check('Case 5: a signed URL for an academic certificate is refused', $signed_cert['status'] === 404);
check('Case 5: the refused certificate response is JSON, not PDF bytes', is_array($signed_cert['json']));

$controller_cv = file_controller_signed_profile_image(
    $primary_cv_id,
    $cv_params['expires'] ?? null,
    $cv_params['signature'] ?? null
);
check('Case 5: the controller itself refuses a non-profile_image row', ($controller_cv['success'] ?? true) === false);

echo "\n== Case 6: missing files and invalid ids ==\n";

$missing_url = file_signed_url($missing_image_id);
$missing_params = is_string($missing_url) ? file_access_signed_query($missing_url) : [];
$missing_request = file_access_request(file_access_relative_path($missing_image_id, $missing_params));
check('Case 6: a missing physical file is 404', $missing_request['status'] === 404);

$unknown_url = file_signed_url(999999999);
$unknown_params = is_string($unknown_url) ? file_access_signed_query($unknown_url) : [];
$unknown_request = file_access_request(file_access_relative_path(999999999, $unknown_params));
check('Case 6: an unknown file id is 404', $unknown_request['status'] === 404);

$invalid_path = file_access_request('/api/v1/files/not-a-number?download=true');
check('Case 6: a non-numeric file id is 404', $invalid_path['status'] === 404);

$controller_missing = file_controller_signed_profile_image(
    $missing_image_id,
    $missing_params['expires'] ?? null,
    $missing_params['signature'] ?? null
);
check('Case 6: the controller reports the missing physical file', ($controller_missing['status'] ?? 0) === 404);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
} else {
    echo "FAILURES: {$failures}\n";
}

exit($failures === 0 ? 0 : 1);
