<?php

/**
 * MASAR - Student Academic Status Regression Test
 *
 * Covers the profile-level Student / Graduate state end to end:
 *
 *   1  `student` is accepted and persisted
 *   2  `graduate` is accepted when the date and certificate are provided
 *   3  an unsupported value is rejected with a clear field error
 *   4  uppercase / padded values follow the documented normalization policy
 *   5  `student` does not require (and does not write) a graduation date
 *   6  `student` does not require, upload or replace a certificate
 *   7  `graduate` validates `graduation_date`
 *   8  `graduate` validates the certificate upload (missing, wrong bytes, too big)
 *   9  a PUT multipart/form-data body is parsed into $_POST / $_FILES
 *  10  an existing graduate record stays readable through GET
 *  11  the student response contains only `academic_status`
 *  12  the graduate response contains status, date and certificate
 *
 * Case 9 is the regression guard for the defect this suite was written for:
 * PHP parses multipart bodies into $_POST / $_FILES only for POST requests, so
 * a PUT with a certificate used to reach the service with no fields and no
 * file at all and answer 422 "Academic status is not valid.".
 *
 * Requests run through tests/student_profile_account_case.php, the same
 * in-process route shim the profile account suite uses, so every case goes
 * route -> controller -> service -> repository against the live database.
 * The upload path itself is driven by that shim's synthetic $_FILES entry,
 * which is exactly the shape request_parse_multipart_input() builds.
 *
 * Fixtures are tagged with a run prefix and removed by
 * student_academic_status_cleanup(), also registered as a shutdown handler so
 * an aborted run cannot leave a file or a graduate row behind.
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

/*
|--------------------------------------------------------------------------
| Fixtures
|--------------------------------------------------------------------------
*/

const ACADEMIC_FILE_PREFIX = 'student_academic_status_regression_';
const ACADEMIC_PROBE_EMAIL = 'youssef.farouk@outlook.com';

function academic_probe_context(string $email): ?array
{
    $row = db_fetch_one(
        'SELECT s.id AS student_id, s.user_id, u.email, s.academic_status,
                s.graduation_date, s.graduation_year, s.graduation_month,
                s.academic_certificate_file_id
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1',
        [$email]
    );

    return is_array($row) ? $row : null;
}

function academic_probe_row(int $student_id): ?array
{
    $row = db_fetch_one(
        'SELECT academic_status, graduation_date, graduation_year,
                graduation_month, academic_certificate_file_id
         FROM students
         WHERE id = ?
         LIMIT 1',
        [$student_id]
    );

    return is_array($row) ? $row : null;
}

/**
 * Files this suite created, by the tagged original name.
 */
function academic_probe_files(): array
{
    return db_fetch_all(
        'SELECT id, original_name, path, type FROM files WHERE original_name LIKE ?',
        [ACADEMIC_FILE_PREFIX . '%']
    );
}

function academic_cleanup_files(): void
{
    foreach (academic_probe_files() as $file) {
        $path = (string) ($file['path'] ?? '');

        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    db_execute('DELETE FROM files WHERE original_name LIKE ?', [ACADEMIC_FILE_PREFIX . '%']);
}

function academic_status_cleanup(?array $original): void
{
    academic_cleanup_files();

    if (!is_array($original)) {
        return;
    }

    db_execute(
        'UPDATE students
         SET academic_status = ?, graduation_date = ?, graduation_year = ?,
             graduation_month = ?, academic_certificate_file_id = ?
         WHERE id = ?',
        [
            $original['academic_status'],
            $original['graduation_date'],
            $original['graduation_year'],
            $original['graduation_month'],
            $original['academic_certificate_file_id'],
            $original['student_id'],
        ]
    );
}

/**
 * Issues a request through the shared case shim and returns the parsed reply.
 */
function request_api(
    string $path,
    string $method,
    string $token,
    ?array $body = null,
    ?array $upload = null
): array {
    $case_file = dirname(__FILE__) . '/student_profile_account_case.php';

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg('students.php')
        . ' ' . escapeshellarg($path)
        . ' ' . escapeshellarg($method)
        . ' ' . escapeshellarg('bearer')
        . ' ' . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_BODY'   => $body === null ? '' : (string) json_encode($body),
        'MASAR_CASE_UPLOAD' => $upload === null ? '' : (string) json_encode($upload),
    ];

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    // The inherited environment carries the database settings; merge, do not replace.
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

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

echo "== Setup ==\n";

academic_cleanup_files();

$probe = academic_probe_context(ACADEMIC_PROBE_EMAIL);

check('the probe student is resolved', is_array($probe));

if (!is_array($probe)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

$student_id = (int) $probe['student_id'];
$user_id    = (int) $probe['user_id'];
$token      = jwt_issue_access_token(['id' => $user_id, 'role' => 'student']);

$original = [
    'student_id'                   => $student_id,
    'academic_status'              => $probe['academic_status'],
    'graduation_date'              => $probe['graduation_date'],
    'graduation_year'              => $probe['graduation_year'],
    'graduation_month'             => $probe['graduation_month'],
    'academic_certificate_file_id' => $probe['academic_certificate_file_id'],
];

register_shutdown_function(static function () use ($original): void {
    academic_status_cleanup($original);
});

// Known-clean starting point for the run; the cleanup restores the captured values.
db_execute(
    "UPDATE students
     SET academic_status = 'student', graduation_date = NULL, graduation_month = NULL
     WHERE id = ?",
    [$student_id]
);

$certificate_before = academic_probe_row($student_id)['academic_certificate_file_id'] ?? null;

$endpoint = '/api/v1/students/me/academic-status';

/*
|--------------------------------------------------------------------------
| Case 9: multipart parsing for a non-POST method
|--------------------------------------------------------------------------
*/

echo "\n== Case 9: PUT multipart body parsing ==\n";

$boundary = '----masar-academic-status-regression';
$pdf_body = "%PDF-1.4\nprobe\n%%EOF\n";

$raw_multipart = "--{$boundary}\r\n"
    . "Content-Disposition: form-data; name=\"academic_status\"\r\n\r\n"
    . "graduate\r\n"
    . "--{$boundary}\r\n"
    . "Content-Disposition: form-data; name=\"graduation_date\"\r\n\r\n"
    . "2026-06-15\r\n"
    . "--{$boundary}\r\n"
    . "Content-Disposition: form-data; name=\"graduation_certificate\"; filename=\"probe.pdf\"\r\n"
    . "Content-Type: application/pdf\r\n\r\n"
    . $pdf_body . "\r\n"
    . "--{$boundary}--\r\n";

$saved = [
    'post'         => $_POST,
    'files'        => $_FILES,
    'method'       => $_SERVER['REQUEST_METHOD'] ?? null,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
];

$_POST   = [];
$_FILES  = [];
$_SERVER['REQUEST_METHOD'] = 'PUT';
$_SERVER['CONTENT_TYPE']   = 'multipart/form-data; boundary=' . $boundary;

$parsed = request_parse_multipart_input($raw_multipart, 'multipart/form-data; boundary=' . $boundary);

check('9: the parser reports it handled the body', $parsed === true);
check('9: a text field reaches $_POST', ($_POST['academic_status'] ?? null) === 'graduate');
check('9: a second text field reaches $_POST', ($_POST['graduation_date'] ?? null) === '2026-06-15');

$uploaded = $_FILES['graduation_certificate'] ?? null;

check('9: the file part reaches $_FILES', is_array($uploaded));
check('9: the original file name is preserved', is_array($uploaded) && $uploaded['name'] === 'probe.pdf');
check('9: the upload carries no error', is_array($uploaded) && (int) $uploaded['error'] === UPLOAD_ERR_OK);
check('9: the byte size is recorded', is_array($uploaded) && (int) $uploaded['size'] === strlen($pdf_body));
check(
    '9: the temporary file holds the exact bytes',
    is_array($uploaded)
        && is_file((string) $uploaded['tmp_name'])
        && file_get_contents((string) $uploaded['tmp_name']) === $pdf_body
);

check('9: request_input() sees the parsed field', request_input('academic_status') === 'graduate');
check('9: request_file() sees the parsed file', request_file('graduation_certificate') !== null);

$parsed_temp = is_array($uploaded) ? (string) $uploaded['tmp_name'] : '';
if ($parsed_temp !== '' && is_file($parsed_temp)) {
    @unlink($parsed_temp);
}

$_POST                     = $saved['post'];
$_FILES                    = $saved['files'];
$_SERVER['REQUEST_METHOD'] = $saved['method'] ?? 'GET';
if ($saved['content_type'] === null) {
    unset($_SERVER['CONTENT_TYPE']);
} else {
    $_SERVER['CONTENT_TYPE'] = $saved['content_type'];
}

check(
    '9: a JSON body is not treated as multipart',
    request_parse_multipart_input('{"academic_status":"student"}', 'application/json') === false
);

/*
|--------------------------------------------------------------------------
| Cases 1, 5, 6, 11: student
|--------------------------------------------------------------------------
*/

echo "\n== Cases 1, 5, 6, 11: student ==\n";

$student_reply = request_api($endpoint, 'PUT', $token, ['academic_status' => ACADEMIC_STATUS_STUDENT]);

check('1: student is accepted', $student_reply['status'] === 200);
check('1: the reply is a success envelope', $student_reply['success'] === true);
check('1: the stored status is student', (academic_probe_row($student_id)['academic_status'] ?? null) === 'student');

check(
    '5: student needs no graduation date',
    $student_reply['status'] === 200
        && array_key_exists('graduation_date', academic_probe_row($student_id) ?? [])
        && academic_probe_row($student_id)['graduation_date'] === null
);

$student_row_after = academic_probe_row($student_id) ?? [];

check(
    '6: student needs no certificate and uploads none',
    $student_reply['status'] === 200
        && count(academic_probe_files()) === 0
        && array_key_exists('academic_certificate_file_id', $student_row_after)
        && $student_row_after['academic_certificate_file_id'] === $certificate_before
);

$student_data = is_array($student_reply['data']) ? $student_reply['data'] : [];

check(
    '11: the student response contains only academic_status',
    array_keys($student_data) === ['academic_status']
        && $student_data['academic_status'] === 'student'
);

/*
|--------------------------------------------------------------------------
| Case 3: unsupported value
|--------------------------------------------------------------------------
*/

echo "\n== Case 3: unsupported value ==\n";

$before_invalid = academic_probe_row($student_id);

$invalid_reply = request_api($endpoint, 'PUT', $token, ['academic_status' => 'phd']);

check('3: an unsupported status is rejected with 422', $invalid_reply['status'] === 422);
check('3: the reject is a failure envelope', $invalid_reply['success'] === false);
check('3: the field error is present, not null', is_array($invalid_reply['errors']) && !empty($invalid_reply['errors']['academic_status']));
check('3: the field error names the accepted values', str_contains((string) ($invalid_reply['errors']['academic_status'] ?? ''), 'student') && str_contains((string) ($invalid_reply['errors']['academic_status'] ?? ''), 'graduate'));
check('3: the refused value did not change the row', academic_probe_row($student_id) === $before_invalid);

/*
|--------------------------------------------------------------------------
| Case 4: normalization policy (trim + case-insensitive)
|--------------------------------------------------------------------------
*/

echo "\n== Case 4: normalization ==\n";

$upper_reply = request_api($endpoint, 'PUT', $token, ['academic_status' => '  STUDENT  ']);

check('4: an uppercase, padded value is normalized, not rejected', $upper_reply['status'] === 200);
check('4: the stored value is the canonical lowercase member', (academic_probe_row($student_id)['academic_status'] ?? null) === 'student');

/*
|--------------------------------------------------------------------------
| Cases 7, 8: graduate validation
|--------------------------------------------------------------------------
*/

echo "\n== Cases 7, 8: graduate validation ==\n";

$graduate_no_date = request_api($endpoint, 'PUT', $token, ['academic_status' => 'graduate']);
check('7: graduate without a date is rejected', $graduate_no_date['status'] === 422);
check('7: the date error is reported', !empty($graduate_no_date['errors']['graduation_date']));

$graduate_bad_date = request_api(
    $endpoint,
    'PUT',
    $token,
    ['academic_status' => 'graduate', 'graduation_date' => 'not-a-date'],
    ['field' => 'graduation_certificate', 'name' => ACADEMIC_FILE_PREFIX . 'bad_date.pdf', 'kind' => 'pdf']
);
check('7: graduate with an invalid date is rejected', $graduate_bad_date['status'] === 422);
check('7: the date error is reported on the invalid date', !empty($graduate_bad_date['errors']['graduation_date']));
check('7: the rejected request uploaded no certificate', count(academic_probe_files()) === 0);

$graduate_no_cert = request_api(
    $endpoint,
    'PUT',
    $token,
    ['academic_status' => 'graduate', 'graduation_date' => '2026-06-15']
);
check('8: graduate without a certificate is rejected', $graduate_no_cert['status'] === 422);
check('8: the certificate error is reported', !empty($graduate_no_cert['errors']['graduation_certificate']));
check('8: the row stayed student', (academic_probe_row($student_id)['academic_status'] ?? null) === 'student');

$graduate_bad_mime = request_api(
    $endpoint,
    'PUT',
    $token,
    ['academic_status' => 'graduate', 'graduation_date' => '2026-06-15'],
    ['field' => 'graduation_certificate', 'name' => ACADEMIC_FILE_PREFIX . 'wrong.pdf', 'kind' => 'png']
);
check('8: a certificate whose content is not the declared type is rejected', $graduate_bad_mime['status'] === 422);
check('8: the content error is reported', !empty($graduate_bad_mime['errors']['graduation_certificate']));
check('8: the rejected upload left no file row', count(academic_probe_files()) === 0);

$graduate_oversize = request_api(
    $endpoint,
    'PUT',
    $token,
    ['academic_status' => 'graduate', 'graduation_date' => '2026-06-15'],
    ['field' => 'graduation_certificate', 'name' => ACADEMIC_FILE_PREFIX . 'huge.png', 'kind' => 'oversize']
);
check('8: an oversized certificate is rejected', $graduate_oversize['status'] === 422);
check('8: the size error is reported', !empty($graduate_oversize['errors']['graduation_certificate']));

/*
|--------------------------------------------------------------------------
| Cases 2, 10, 12: graduate success and read-back
|--------------------------------------------------------------------------
*/

echo "\n== Cases 2, 10, 12: graduate success and read-back ==\n";

$graduate_filename = ACADEMIC_FILE_PREFIX . 'cert.pdf';

$graduate_reply = request_api(
    $endpoint,
    'PUT',
    $token,
    ['academic_status' => 'GRADUATE', 'graduation_date' => '2026-06-15'],
    ['field' => 'graduation_certificate', 'name' => $graduate_filename, 'kind' => 'pdf']
);

check('2: graduate with date and certificate is accepted', $graduate_reply['status'] === 200);
check('2: the reply is a success envelope', $graduate_reply['success'] === true);

$graduate_row = academic_probe_row($student_id);

check('2: the stored status is graduate', ($graduate_row['academic_status'] ?? null) === 'graduate');
check('2: the graduation date is stored', ($graduate_row['graduation_date'] ?? null) === '2026-06-15');
check('2: the year is derived from the date', (int) ($graduate_row['graduation_year'] ?? 0) === 2026);
check('2: the month is derived from the date', (int) ($graduate_row['graduation_month'] ?? 0) === 6);
check('2: a certificate reference is attached', (int) ($graduate_row['academic_certificate_file_id'] ?? 0) > 0);

$stored_file = db_fetch_one(
    'SELECT id, original_name, path, type FROM files WHERE original_name = ? LIMIT 1',
    [$graduate_filename]
);

check('2: the certificate is recorded as an academic certificate', is_array($stored_file) && ($stored_file['type'] ?? '') === FILE_TYPE_ACADEMIC_CERTIFICATE);
check(
    '2: the certificate is stored in the academic certificate upload location',
    is_array($stored_file) && str_contains(str_replace('\\', '/', (string) ($stored_file['path'] ?? '')), '/storage/uploads/certificates/')
);

$graduate_data = is_array($graduate_reply['data']) ? $graduate_reply['data'] : [];

check(
    '12: the graduate response contains exactly status, date and certificate',
    array_keys($graduate_data) === ['academic_status', 'graduation_date', 'graduation_certificate']
);

check(
    '12: the graduate response carries the stored values',
    ($graduate_data['academic_status'] ?? null) === 'graduate'
        && ($graduate_data['graduation_date'] ?? null) === '2026-06-15'
        && is_array($graduate_data['graduation_certificate'] ?? null)
        && (int) ($graduate_data['graduation_certificate']['id'] ?? 0) === (int) ($stored_file['id'] ?? -1)
);

$graduate_certificate_id = (int) ($graduate_row['academic_certificate_file_id'] ?? 0);

$read_reply = request_api($endpoint, 'GET', $token);
$read_data  = is_array($read_reply['data']) ? $read_reply['data'] : [];

check('10: an existing graduate record is readable', $read_reply['status'] === 200);
check('10: the read-back status is graduate', ($read_data['academic_status'] ?? null) === 'graduate');
check('10: the read-back date matches the stored date', ($read_data['graduation_date'] ?? null) === '2026-06-15');
check('10: the read-back certificate resolves to the stored file', (int) ($read_data['graduation_certificate']['id'] ?? 0) === $graduate_certificate_id);
check(
    '10: no legacy graduation fields are exposed',
    !array_key_exists('is_graduate', $read_data)
        && !array_key_exists('graduation_year', $read_data)
        && !array_key_exists('graduation_month', $read_data)
        && !array_key_exists('graduation_month_name', $read_data)
);

/*
|--------------------------------------------------------------------------
| Case 6 (again, with a certificate on file): student must not touch it
|--------------------------------------------------------------------------
*/

echo "\n== Case 6: student after graduate keeps the certificate ==\n";

$back_to_student = request_api($endpoint, 'PUT', $token, ['academic_status' => 'student']);
$back_data = is_array($back_to_student['data']) ? $back_to_student['data'] : [];

check('1: student is accepted again', $back_to_student['status'] === 200);
check(
    '11: the student response is again only academic_status',
    array_keys($back_data) === ['academic_status']
);
check(
    '6: switching back to student does not remove the stored certificate',
    (int) (academic_probe_row($student_id)['academic_certificate_file_id'] ?? 0) === $graduate_certificate_id
);
check(
    '6: switching back to student uploaded no new file',
    count(academic_probe_files()) === 1
);

$student_read = request_api($endpoint, 'GET', $token);
$student_read_data = is_array($student_read['data']) ? $student_read['data'] : [];

check(
    '11: GET for a student exposes only academic_status',
    array_keys($student_read_data) === ['academic_status']
        && !array_key_exists('graduation_date', $student_read_data)
        && !array_key_exists('graduation_certificate', $student_read_data)
);

/*
|--------------------------------------------------------------------------
| Result
|--------------------------------------------------------------------------
*/

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
