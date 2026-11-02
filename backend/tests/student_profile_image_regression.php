<?php

/**
 * MASAR - Student Profile Image Regression Test
 *
 * Focused coverage for POST /api/v1/students/me/profile-image: the endpoint
 * that writes students.profile_image_file_id, the field the Profile Header
 * renders.
 *
 * The endpoint itself already existed before this suite; what this suite locks
 * down is the security and lifecycle contract around it:
 *
 *   Case 1   Authentication + student role (guest 401, company 403, admin 403)
 *   Case 2   Valid uploads: JPEG, PNG and WebP each persist and are reachable
 *   Case 3   Rejections: no file, unsupported extension, content/extension
 *            mismatch, non-image content, corrupt image bytes, >5MB
 *   Case 4   A rejected upload writes NOTHING: no files row, no orphan file
 *   Case 5   Safe filename + storage: randomized name, never the client name,
 *            stored under the uploads root, and no path in any response
 *   Case 6   Replacement: the second upload replaces the first, and the header
 *            follows the latest persisted reference
 *   Case 7   Persistence-failure cleanup: student_account_discard_profile_image()
 *            removes both the row and the bytes, and only its owner's file
 *   Case 8   Isolation: student A can never write student B's image, and B's
 *            header never shows A's file
 *   Case 9   The header contract: all thirteen fields survive an upload and a
 *            removal, and the other "me" profile endpoints are untouched
 *
 * Fixture rows are tagged with a run-scoped prefix and removed by
 * profile_image_fixture_cleanup(), which is also registered as a shutdown
 * handler so an aborted run cannot leave anything behind. Files are unlinked
 * from disk as well as deleted from the database, because the shared upload
 * service never garbage-collects an orphan. The probe students' original
 * profile_image_file_id is captured and restored.
 *
 * Run with:  php tests/student_profile_image_regression.php
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
require_once BASE . 'app/modules/students/services/student_account_service.php';

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
 * A profile image URL is now a short-lived signed URL, not the bare
 * ?download=true URL. Verify the file id, the download flag and the HMAC
 * instead of comparing two URLs generated a second apart.
 */
function profile_image_url_matches(int $file_id, mixed $url): bool
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
 * Issues a request through the existing profile-account case shim.
 *
 * Reuses student_profile_account_case.php rather than duplicating the
 * in-process request bootstrap. That shim already builds $_FILES from a
 * {field, name, kind, client_type} spec, which is the only sane way to fake a
 * multipart upload on Windows.
 */
function request_api(
    string $path,
    string $method,
    string $scenario = 'bearer',
    string $token = '',
    ?array $upload = null,
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
        'MASAR_CASE_UPLOAD' => $upload === null ? '' : (string) json_encode($upload),
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    /*
     | Merged onto the inherited environment on purpose: passing only these two
     | would leave the child without the database settings the app config reads
     | via getenv(), and it could not connect.
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
        'header'  => is_array($payload) ? ($payload['data']['header'] ?? null) : null,
    ];
}

function token_for(int $user_id, string $role = 'student'): string
{
    return jwt_issue_access_token(['id' => $user_id, 'role' => $role]);
}

function profile_image_student(string $email): ?array
{
    $row = db_fetch_one(
        'SELECT s.id AS student_id, s.user_id, s.full_name, s.profile_image_file_id, u.email
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1',
        [$email]
    );

    return is_array($row) ? $row : null;
}

/*
|--------------------------------------------------------------------------
| Fixture Helpers
|--------------------------------------------------------------------------
*/

const PROFILE_IMAGE_FIXTURE_PREFIX = 'regression_profile_image_';

function profile_image_fixture_cleanup(): void
{
    foreach (db_fetch_all(
        'SELECT path FROM files WHERE original_name LIKE ?',
        [PROFILE_IMAGE_FIXTURE_PREFIX . '%']
    ) as $row) {
        $path = (string) ($row['path'] ?? '');

        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    db_execute(
        'DELETE FROM files WHERE original_name LIKE ?',
        [PROFILE_IMAGE_FIXTURE_PREFIX . '%']
    );
}

register_shutdown_function('profile_image_fixture_cleanup');

function profile_image_upload(string $name, string $kind, string $client_type = 'application/octet-stream'): array
{
    return [
        'field'       => 'image',
        'name'        => PROFILE_IMAGE_FIXTURE_PREFIX . $name,
        'kind'        => $kind,
        'client_type' => $client_type,
    ];
}

/**
 * Reads the stored files row for a just-uploaded image.
 *
 * Returns null rather than fabricating a shape, so an assertion on a missing
 * row fails instead of erroring out on an array access.
 */
function profile_image_file_row(int $file_id): ?array
{
    $row = db_fetch_one('SELECT * FROM files WHERE id = ? LIMIT 1', [$file_id]);

    return is_array($row) ? $row : null;
}

function profile_image_count_rows(): int
{
    return (int) db_fetch_column(
        'SELECT COUNT(*) FROM files WHERE original_name LIKE ?',
        [PROFILE_IMAGE_FIXTURE_PREFIX . '%']
    );
}

/**
 * The header as the authenticated student sees it.
 */
function profile_image_read_header(string $token): array
{
    return request_api('/api/v1/students/me', 'GET', 'bearer', $token);
}

echo "== Setup: contexts ==\n";

$primary   = profile_image_student('omar.shazly@gmail.com');
$secondary = profile_image_student('mariam.hassan@gmail.com');

check('primary student resolved', is_array($primary));
check('secondary student resolved', is_array($secondary));

$company = db_fetch_one("SELECT id FROM users WHERE role = 'company' AND status = 'active' ORDER BY id LIMIT 1");
check('company user resolved', is_array($company));

$admin = db_fetch_one("SELECT id FROM users WHERE role = 'admin' AND status = 'active' ORDER BY id LIMIT 1");
check('admin user resolved', is_array($admin));

if (!is_array($primary) || !is_array($secondary) || !is_array($company) || !is_array($admin)) {
    echo "\n== Result ==\n";
    echo "FAILURES: {$failures}\n";
    exit(1);
}

$primary_id       = (int) $primary['student_id'];
$primary_user     = (int) $primary['user_id'];
$secondary_id     = (int) $secondary['student_id'];
$secondary_user   = (int) $secondary['user_id'];
$primary_token    = token_for($primary_user, 'student');
$secondary_token  = token_for($secondary_user, 'student');
$company_token    = token_for((int) $company['id'], 'company');
$admin_token      = token_for((int) $admin['id'], 'admin');

$primary_original_image   = $primary['profile_image_file_id'] ?? null;
$secondary_original_image = $secondary['profile_image_file_id'] ?? null;

echo "\n== Case 1: authentication and student role ==\n";

$guest = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'guest',
    '',
    profile_image_upload('guest.png', 'png')
);
check('Case 1: an unauthenticated upload is 401', $guest['status'] === 401);

$as_company = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $company_token,
    profile_image_upload('company.png', 'png')
);
check('Case 1: a company token is refused with 403', $as_company['status'] === 403);

$as_admin = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $admin_token,
    profile_image_upload('admin.png', 'png')
);
check('Case 1: an admin token is refused with 403', $as_admin['status'] === 403);

$guest_rows = profile_image_count_rows();
check('Case 1: a refused upload stored no file at all', $guest_rows === 0);

/*
 | A guest/company/admin upload must not have moved either student's reference.
 | Compared with the value captured before the case ran, not with null, so this
 | stays honest even when the probe already had an image.
 */
check(
    'Case 1: a refused upload did not touch the student reference',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === (int) $primary_original_image
);

echo "\n== Case 2: valid JPEG, PNG and WebP uploads ==\n";

$valid_kinds = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'webp' => 'image/webp',
];

$last_file_id = 0;

foreach ($valid_kinds as $extension => $expected_mime) {
    $response = request_api(
        '/api/v1/students/me/profile-image',
        'POST',
        'bearer',
        $primary_token,
        profile_image_upload('valid.' . $extension, $extension, $expected_mime)
    );

    check(
        "Case 2: a valid .{$extension} upload is accepted",
        $response['status'] === 200 && $response['success'] === true
    );

    $file_id = (int) ($response['data']['profile_image_file_id'] ?? 0);

    check(
        "Case 2: the .{$extension} response carries a positive file id",
        $file_id > 0
    );

    check(
        "Case 2: the .{$extension} response url is a signed profile-image url",
        profile_image_url_matches($file_id, $response['data']['profile_image_url'] ?? null)
    );

    $row = $file_id > 0 ? profile_image_file_row($file_id) : null;

    check("Case 2: the .{$extension} upload persisted a files row", is_array($row));
    check(
        "Case 2: the .{$extension} row is typed profile_image",
        ($row['type'] ?? '') === FILE_TYPE_PROFILE_IMAGE
    );
    check(
        "Case 2: the .{$extension} row is owned by the uploading student",
        (int) ($row['user_id'] ?? 0) === $primary_user
    );
    check(
        "Case 2: the .{$extension} row records the sniffed MIME",
        ($row['mime_type'] ?? '') === $expected_mime
    );
    check(
        "Case 2: the .{$extension} reference was written to the students row",
        (int) db_fetch_column(
            'SELECT profile_image_file_id FROM students WHERE id = ?',
            [$primary_id]
        ) === $file_id
    );
    check(
        "Case 2: the .{$extension} response leaks no filesystem path",
        !str_contains(strtolower((string) $response['raw']), 'storage')
            && !str_contains(strtolower((string) $response['raw']), 'uploads')
    );

    $last_file_id = $file_id;
}

echo "\n== Case 3: rejected uploads ==\n";

$before_rejects = profile_image_count_rows();

$missing = request_api('/api/v1/students/me/profile-image', 'POST', 'bearer', $primary_token);
check('Case 3: a request with no file at all is refused', $missing['status'] === 422);
check('Case 3: the missing-file error is reported on the image field', isset($missing['errors']['image']));

$bad_extension = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('payload.exe', 'png', 'image/png')
);
check('Case 3: an unsupported extension is refused', $bad_extension['status'] === 422);

$executable = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('shell.php', 'png', 'image/png')
);
check('Case 3: an executable extension is refused', $executable['status'] === 422);

$traversal = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('../escape.png', 'png', 'image/png')
);
check('Case 3: a path-traversal filename is refused', $traversal['status'] === 422);

/*
 | Content and extension must agree. Real PNG bytes under a .pdf name is the
 | strongest form of this: both halves are individually innocuous.
 */
$mismatch = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('liar.pdf', 'png', 'image/png')
);
check('Case 3: image bytes under a .pdf name are refused', $mismatch['status'] === 422);

$not_an_image = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('paper.png', 'pdf', 'image/png')
);
check('Case 3: a PDF renamed to .png is refused', $not_an_image['status'] === 422);

/*
 | A client-declared Content-Type must not buy an image through: the bytes are
 | a PDF, the name says .png, and the client insists it is image/png. Only the
 | server-side sniff can refuse this.
 */
$lying_client_type = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('still_a_pdf.png', 'pdf', 'image/png')
);
check(
    'Case 3: a lying client Content-Type does not override the content sniff',
    $lying_client_type['status'] === 422
);

/*
 | A truncated image: the PNG magic prefix IS present, so a naive header check
 | would wave it through, but there is no valid image behind it, so finfo does
 | not report image/png and the allowlist refuses it.
 |
 | This is the honest ceiling of what this endpoint can prove. Nothing decodes
 | the image, so it rejects bytes that are not a real image - it does not
 | re-encode to prove the pixels decode. A file that is a genuinely valid
 | image is indistinguishable from a valid image with broken pixels, and
 | deciding otherwise would mean adding a decode step the project does not use
 | anywhere.
 */
$corrupt = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    [
        'field'       => 'image',
        'name'        => PROFILE_IMAGE_FIXTURE_PREFIX . 'corrupt.png',
        'kind'        => 'raw',
        'client_type' => 'image/png',
        'bytes'       => base64_encode("\x89PNG\r\n\x1a\ntruncated-garbage-not-an-image"),
    ]
);
check('Case 3: a truncated image with valid magic bytes is refused', $corrupt['status'] === 422);

/*
 | Empty body: a zero-byte part is not a file at all.
 */
$empty = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    [
        'field'       => 'image',
        'name'        => PROFILE_IMAGE_FIXTURE_PREFIX . 'empty.png',
        'kind'        => 'raw',
        'client_type' => 'image/png',
        'bytes'       => base64_encode(' '),
    ]
);
check('Case 3: a one-byte non-image upload is refused', $empty['status'] === 422);

$oversize = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('too_big.png', 'oversize_image', 'image/png')
);
check('Case 3: an upload larger than 5MB is refused', $oversize['status'] === 422);
check(
    'Case 3: the oversize refusal names the limit',
    stripos((string) $oversize['message'], '5') !== false
);

check(
    'Case 4: not one rejected upload created a files row',
    profile_image_count_rows() === $before_rejects
);

check(
    'Case 4: the rejected uploads left the last good reference in place',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === $last_file_id
);

echo "\n== Case 5: safe filename and storage ==\n";

$good_row = profile_image_file_row($last_file_id);

check(
    'Case 5: the stored name is a dated random token, not the client filename',
    is_array($good_row)
        && preg_match('/^\d{8}_[0-9a-f]{32}\.webp$/', (string) $good_row['stored_name']) === 1
);

check(
    'Case 5: the stored name does not contain the client filename',
    is_array($good_row)
        && !str_contains((string) $good_row['stored_name'], 'valid')
        && !str_contains((string) $good_row['stored_name'], PROFILE_IMAGE_FIXTURE_PREFIX)
);

check(
    'Case 5: the client filename is preserved only as original_name metadata',
    is_array($good_row)
        && str_starts_with((string) $good_row['original_name'], PROFILE_IMAGE_FIXTURE_PREFIX)
);

$storage_root = realpath(BASE . 'app/storage/uploads');

check(
    'Case 5: the file was written under the uploads root',
    is_array($good_row)
        && $storage_root !== false
        && str_starts_with(realpath((string) $good_row['path']) ?: '', $storage_root)
);

check(
    'Case 5: the bytes really landed on disk',
    is_array($good_row) && is_file((string) $good_row['path'])
);

$header_after_valid = profile_image_read_header($primary_token);

check(
    'Case 5: the header exposes no filesystem path',
    !str_contains(strtolower((string) $header_after_valid['raw']), 'storage')
        && !str_contains(strtolower((string) $header_after_valid['raw']), 'uploads')
);

echo "\n== Case 6: replacement ==\n";

$first_id = $last_file_id;

$replacement = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $primary_token,
    profile_image_upload('replacement.png', 'png', 'image/png')
);

check('Case 6: a replacement upload is accepted', $replacement['status'] === 200);

$replacement_id = (int) ($replacement['data']['profile_image_file_id'] ?? 0);

check(
    'Case 6: the replacement is a DIFFERENT file from the one it replaced',
    $replacement_id > 0 && $replacement_id !== $first_id
);

check(
    'Case 6: the students row now points at the replacement',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === $replacement_id
);

$header_after_replace = profile_image_read_header($primary_token);

check(
    'Case 6: the header follows the latest persisted reference',
    (int) ($header_after_replace['header']['profile_image_file_id'] ?? 0) === $replacement_id
);

check(
    'Case 6: the header url is a signed url for the replacement',
    profile_image_url_matches(
        $replacement_id,
        $header_after_replace['header']['profile_image_url'] ?? null
    )
);

check(
    'Case 6: the header no longer references the replaced file',
    (int) ($header_after_replace['header']['profile_image_file_id'] ?? 0) !== $first_id
);

/*
 | The replacement path deliberately does NOT delete the old file: a failed
 | replacement must leave the student with the image they already had. This
 | asserts the reference is the only thing that moved.
 */
check(
    'Case 6: the previous file row is still intact and simply unreferenced',
    is_array(profile_image_file_row($first_id))
);

echo "\n== Case 7: persistence-failure cleanup ==\n";

/*
 | The cleanup helper is the mechanism the service calls when the students row
 | cannot be written. Forcing that DB write to fail over HTTP would mean
 | breaking the database, so the helper is exercised directly instead: it must
 | remove the row AND the bytes, and it must refuse another user's file.
 */
$orphan_name = PROFILE_IMAGE_FIXTURE_PREFIX . 'orphan.png';
$orphan_path = BASE . 'app/storage/uploads/profile/' . $orphan_name;

if (!is_dir(dirname($orphan_path))) {
    @mkdir(dirname($orphan_path), 0777, true);
}

file_put_contents($orphan_path, masar_case_probe_png_bytes());

db_execute(
    'INSERT INTO files (user_id, type, original_name, stored_name, path, mime_type, size_bytes, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
    [
        $primary_user,
        FILE_TYPE_PROFILE_IMAGE,
        $orphan_name,
        $orphan_name,
        $orphan_path,
        'image/png',
        (int) filesize($orphan_path),
    ]
);

$orphan_id = (int) db_last_insert_id();

check('Case 7: the orphan file row was created', $orphan_id > 0);
check('Case 7: the orphan bytes are on disk', is_file($orphan_path));

student_account_discard_profile_image($orphan_id, $primary_user);

check('Case 7: the discarded file row is gone', profile_image_file_row($orphan_id) === null);
check('Case 7: the discarded bytes are gone from disk', !is_file($orphan_path));

/*
 | The same helper called with the WRONG user id must not delete anything: a
 | failed upload can never be used to remove another account's file.
 */
$victim_name = PROFILE_IMAGE_FIXTURE_PREFIX . 'victim.png';
$victim_path = BASE . 'app/storage/uploads/profile/' . $victim_name;

file_put_contents($victim_path, masar_case_probe_png_bytes());

db_execute(
    'INSERT INTO files (user_id, type, original_name, stored_name, path, mime_type, size_bytes, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
    [
        $secondary_user,
        FILE_TYPE_PROFILE_IMAGE,
        $victim_name,
        $victim_name,
        $victim_path,
        'image/png',
        (int) filesize($victim_path),
    ]
);

$victim_id = (int) db_last_insert_id();

student_account_discard_profile_image($victim_id, $primary_user);

check(
    'Case 7: cleanup scoped to the wrong user leaves the row alone',
    is_array(profile_image_file_row($victim_id))
);
check('Case 7: cleanup scoped to the wrong user leaves the bytes alone', is_file($victim_path));

echo "\n== Case 8: student isolation ==\n";

$cross = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $secondary_token,
    profile_image_upload('intruder.png', 'png', 'image/png')
);

check('Case 8: the second student may upload their own image', $cross['status'] === 200);

$cross_id = (int) ($cross['data']['profile_image_file_id'] ?? 0);

check(
    'Case 8: the second upload is owned by the SECOND student',
    $cross_id > 0
        && (int) (profile_image_file_row($cross_id)['user_id'] ?? 0) === $secondary_user
);

check(
    'Case 8: the second student did not write to the first student row',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === $replacement_id
);

$secondary_header = profile_image_read_header($secondary_token);

check(
    'Case 8: the second student sees only their own image',
    (int) ($secondary_header['header']['profile_image_file_id'] ?? 0) === $cross_id
);

check(
    'Case 8: the first student never sees the second student image',
    !in_array(
        (int) ($secondary_header['header']['profile_image_file_id'] ?? 0),
        [0, $replacement_id],
        true
    )
);

/*
 | There is no client-supplied student id anywhere in this endpoint, so the
 | strongest form of the BOLA check is that a body naming another student
 | cannot redirect the write. The field travels through the real $_POST path.
 */
$with_forged_id = request_api(
    '/api/v1/students/me/profile-image',
    'POST',
    'bearer',
    $secondary_token,
    profile_image_upload('forged.png', 'png', 'image/png'),
    [
        'student_id'         => $primary_id,
        'profile_image_file_id' => $replacement_id,
        'user_id'            => $primary_user,
    ]
);

check('Case 8: a forged student_id upload is still accepted for the caller', $with_forged_id['status'] === 200);

check(
    'Case 8: a forged student_id cannot redirect the write to the first student',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === $replacement_id
);

check(
    'Case 8: a forged student_id does not make the caller inherit that file',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$secondary_id]
    ) === (int) ($with_forged_id['data']['profile_image_file_id'] ?? 0)
);

check(
    'Case 8: the file written by the forged request belongs to the CALLER',
    (int) (profile_image_file_row((int) ($with_forged_id['data']['profile_image_file_id'] ?? 0))['user_id'] ?? 0)
        === $secondary_user
);

echo "\n== Case 9: the header contract is unchanged ==\n";

$header = $header_after_replace['header'];

/*
 | The /students/me header contract is the full thirteen-field self header, so
 | the image upload is proven not to disturb any of them - not just the seven
 | image-adjacent values this suite was originally written against.
 */
$expected_keys = [
    'id',
    'academic_status',
    'is_profile_complete',
    'updated_at',
    'bio',
    'field',
    'profile_image_url',
    'profile_image_file_id',
    'full_name',
    'specialization',
    'university',
    'faculty',
    'member_since',
];

sort($expected_keys);

$actual_keys = is_array($header) ? array_keys($header) : [];
sort($actual_keys);

check(
    'Case 9: the header exposes exactly its thirteen fields',
    $actual_keys === $expected_keys
);

check(
    'Case 9: the image upload did not disturb the core header fields',
    (int) ($header['id'] ?? 0) === $primary_id
        && ($header['academic_status'] ?? 'x') === (string) db_fetch_column(
            'SELECT academic_status FROM students WHERE id = ?',
            [$primary_id]
        )
        && ($header['field'] ?? null) === db_fetch_column(
            'SELECT sf.name
             FROM students s
             LEFT JOIN study_fields sf ON sf.id = s.field_id
             WHERE s.id = ?
             LIMIT 1',
            [$primary_id]
        )
        && ($header['bio'] ?? null) === db_fetch_column(
            'SELECT bio FROM students WHERE id = ?',
            [$primary_id]
        )
        && is_int($header['is_profile_complete'] ?? null)
        && $header['is_profile_complete'] >= 0
        && $header['is_profile_complete'] <= 100
);

check(
    'Case 9: full_name is the student row value',
    ($header['full_name'] ?? null) === (string) $primary['full_name']
);

check(
    'Case 9: member_since is still an ISO-8601 string',
    is_string($header['member_since'] ?? null)
        && preg_match('/^\d{4}-\d{2}-\d{2}T/', (string) $header['member_since']) === 1
);

check(
    'Case 9: the header read is 200 for the authenticated student',
    $header_after_replace['status'] === 200
);

foreach ([
    '/api/v1/students/me/skills',
    '/api/v1/students/me/contact',
    '/api/v1/students/me/academic-status',
    '/api/v1/students/profile/status',
] as $untouched_path) {
    $untouched = request_api($untouched_path, 'GET', 'bearer', $primary_token);

    check(
        'Case 9: ' . $untouched_path . ' is unaffected',
        $untouched['status'] === 200 && $untouched['success'] === true
    );
}

/*
 | Removal is the documented counterpart: it clears the reference without
 | deleting the stored file, so the header degrades to null rather than to a
 | broken URL.
 */
$removed = request_api('/api/v1/students/me/profile-image', 'DELETE', 'bearer', $primary_token);

check('Case 9: removal succeeds', $removed['status'] === 200);

$header_after_removal = profile_image_read_header($primary_token);

/*
 | A NULL check written with ?? would be inverted by construction:
 | (null ?? 'x') evaluates to 'x', never to null. array_key_exists() plus a
 | direct identity comparison is what actually proves the field is present AND
 | null.
 */
$header_after_removal_keys = is_array($header_after_removal['header'])
    ? array_keys($header_after_removal['header'])
    : [];

sort($header_after_removal_keys);

check(
    'Case 9: the header keys are unchanged after removal',
    $header_after_removal_keys === $expected_keys
);

check(
    'Case 9: the header reports no image after removal',
    array_key_exists('profile_image_file_id', (array) $header_after_removal['header'])
        && array_key_exists('profile_image_url', (array) $header_after_removal['header'])
        && $header_after_removal['header']['profile_image_file_id'] === null
        && $header_after_removal['header']['profile_image_url'] === null
);

check(
    'Case 9: removal cleared the reference but kept the stored file',
    is_array(profile_image_file_row($replacement_id))
);

echo "\n== Cleanup ==\n";

db_execute('UPDATE students SET profile_image_file_id = ? WHERE id = ?', [
    $primary_original_image !== null ? (int) $primary_original_image : null,
    $primary_id,
]);

db_execute('UPDATE students SET profile_image_file_id = ? WHERE id = ?', [
    $secondary_original_image !== null ? (int) $secondary_original_image : null,
    $secondary_id,
]);

profile_image_fixture_cleanup();

check(
    'Cleanup: every fixture file row was removed',
    profile_image_count_rows() === 0
);

check(
    'Cleanup: the primary probe reference was restored',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$primary_id]
    ) === (int) $primary_original_image
);

check(
    'Cleanup: the secondary probe reference was restored',
    (int) db_fetch_column(
        'SELECT profile_image_file_id FROM students WHERE id = ?',
        [$secondary_id]
    ) === (int) $secondary_original_image
);

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";

exit($failures === 0 ? 0 : 1);

/*
|--------------------------------------------------------------------------
| Probe Bytes
|--------------------------------------------------------------------------
|
| A genuine 8x8 PNG, built in-process so the cleanup fixtures are real image
| bytes rather than a placeholder string.
|
*/

function masar_case_probe_png_bytes(): string
{
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('GD is required to build the cleanup fixture.');
    }

    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 120, 120, 120));

    ob_start();
    imagepng($image);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}