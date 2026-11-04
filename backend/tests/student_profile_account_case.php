<?php

/**
 * MASAR - Student Profile Account Case Runner (internal)
 *
 * One-shot subprocess executed by
 * tests/student_profile_account_regression.php. NOT a standalone test.
 *
 * Usage:
 *   php tests/student_profile_account_case.php <route-file> <path> <method> <guest|bearer> <token>
 *
 * It boots the same request shim as public/index.php (minus static asset /
 * HTML handling), issues the given request against the given route file, and
 * prints:
 *
 *     STATUS=<http_code>
 *     BODY=<raw json body>
 *
 * The request body and the synthetic upload are passed through the ENVIRONMENT
 * rather than as extra argv entries, because JSON on a Windows command line
 * loses its double quotes to the shell's native-argument quoting and arrives
 * as invalid JSON. Environment variables are passed literally.
 *
 *   MASAR_CASE_BODY    JSON object applied to $_POST (request_input() merges
 *                       $_POST under the parsed body, so a write endpoint can
 *                       be driven without a real HTTP body)
 *   MASAR_CASE_UPLOAD  JSON object: {"field":..,"name":..,"kind":..,
 *                       "client_type":..,"bytes":<base64>}. `bytes` is only
 *                       honoured by kind "raw", for a fixture whose CONTENT has
 *                       to be malformed in a specific way.
 *
 |--------------------------------------------------------------------------
 | Why The Fixtures Look Like This
 |--------------------------------------------------------------------------
 |
 | The upload tests need to prove three separate things, and each can be proven
 | without embedding a single hostile byte in this file:
 |
 |   1. An unsupported EXTENSION is refused. The extension is read from the
 |      submitted FILENAME, and that check runs before any content sniffing, so
 |      the bytes can be an ordinary PDF. Only the name has to be hostile
 |      (payload.exe, shell.php), and a name is just text.
 |
  |   2. The CONTENT must match the extension. That needs a file whose bytes
  |      disagree with its name, which is produced by submitting real PNG bytes
  |      under a .pdf name and vice versa. Both payloads are legitimate images.
  |
  |   3. The file must be REAL, not just a plausible header. A minimal PDF
  |      template and GD-generated PNG/JPEG give genuine files, so a passing
  |      upload proves the sniffer accepted actual image/document content.
  |
  | An earlier revision of this shim embedded a webshell body and a Windows PE
  | header as literal byte strings. Endpoint antivirus identified the source
  | file itself as malware, locked it, and made it unreadable and unrunnable -
  | taking the suite with it. Deriving the hostile cases from benign bytes
  | removes that failure mode entirely and keeps the assertions just as strong.
  |
  */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

$app_config = require_once BASE . 'app/config/app.php';
require_once BASE . 'app/config/constants.php';
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/core/errors/error_handler.php';
require_once BASE . 'app/core/errors/exception_handler.php';
require_once BASE . 'app/core/middleware/cors.php';
require_once BASE . 'app/shared/functions/security.php';
require_once BASE . 'app/shared/functions/audit.php';

register_error_handler();
register_exception_handler();
security_apply_http_headers();
cors_handle();

$route_file = (string) ($argv[1] ?? 'students.php');
$path       = (string) ($argv[2] ?? '/api/v1/students/me');
$method     = strtoupper((string) ($argv[3] ?? 'GET'));
$scenario   = (string) ($argv[4] ?? 'guest');
$token      = (string) ($argv[5] ?? '');

$body_json  = (string) (getenv('MASAR_CASE_BODY') ?: '');
$upload_raw = (string) (getenv('MASAR_CASE_UPLOAD') ?: '');

$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['REQUEST_URI']    = $path;
$_SERVER['QUERY_STRING']   = (string) (parse_url($path, PHP_URL_QUERY) ?? '');
$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['SCRIPT_NAME']    = '/Masar/backend/index.php';
$_GET    = [];
$_POST   = [];
$_COOKIE = [];
$_FILES  = [];

/*
 | request_query() reads $_GET, which PHP normally populates from the query
 | string. Under the CLI shim nothing does that, so a ?limit=1 style
 | parameter would be silently dropped and the endpoint would fall back to its
 | default - making a limit assertion pass or fail for the wrong reason.
 */
if ($_SERVER['QUERY_STRING'] !== '') {
    parse_str($_SERVER['QUERY_STRING'], $parsed_query);

    if (is_array($parsed_query)) {
        $_GET = $parsed_query;
    }
}

if ($scenario === 'bearer') {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
}

if (trim($body_json) !== '') {
    $decoded = json_decode($body_json, true);

    if (is_array($decoded)) {
        // request_input() merges $_POST under the parsed body, so a PUT/POST
        // payload is applied here instead of through php://input.
        foreach ($decoded as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

if (trim($upload_raw) !== '') {
    $spec = json_decode($upload_raw, true);

    if (is_array($spec) && !empty($spec['kind'])) {
        $kind = (string) $spec['kind'];

        $bytes = masar_case_upload_bytes($kind, (string) ($spec['bytes'] ?? ''));

        $tmp_dir = sys_get_temp_dir() . '/masar_case_upload';
        @mkdir($tmp_dir, 0777, true);

        $tmp_path = $tmp_dir . '/case_' . md5($kind . '|' . ($spec['name'] ?? '')) . '.bin';

        file_put_contents($tmp_path, $bytes);

        $_FILES[(string) ($spec['field'] ?? 'file')] = [
            'name'     => (string) ($spec['name'] ?? ('upload.bin')),
            'type'     => (string) ($spec['client_type'] ?? 'application/octet-stream'),
            'tmp_name' => $tmp_path,
            'error'    => UPLOAD_ERR_OK,
            'size'     => strlen($bytes),
        ];
    }
}

register_shutdown_function(static function (): void {
    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY=' . (ob_get_contents() ?: '') . "\n";

    foreach ($_FILES as $file) {
        if (is_array($file) && !empty($file['tmp_name']) && is_file($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        }
    }
});

ob_start();

require BASE . 'routes/' . $route_file;

/*
|--------------------------------------------------------------------------
| Fixture Bytes
|--------------------------------------------------------------------------
|
| `kind` selects the CONTENT; the `name` field of the upload spec independently
| selects the FILENAME. Keeping them separate is what lets the same legitimate
| bytes be submitted under several names to prove the extension/content
| cross-checks.
|
*/

function masar_case_upload_bytes(string $kind, string $raw_bytes = ''): string
{
    switch ($kind) {
        // Real documents and images.
        case 'pdf':
            return masar_case_pdf();

        case 'png':
            return masar_case_png();

        case 'jpg':
        case 'jpeg':
            return masar_case_jpg();

        case 'webp':
            return masar_case_webp();

        // Real bytes, deliberately LARGER than the 5MB certificate limit.
        // The size check runs first, so the content here is irrelevant; plain
        // filler keeps the fixture inert.
        case 'oversize':
            return str_repeat('M', ACADEMIC_CERTIFICATE_MAX_SIZE + 1024);

        // Real bytes, deliberately LARGER than the 5MB profile-image limit.
        // This is a distinct kind from `oversize` because the two limits are
        // separate constants: a single shared fixture would only prove that
        // SOME cap exists, not that the profile image is capped at 5MB.
        case 'oversize_image':
            return masar_case_oversize_png();

        /*
         | Bytes supplied verbatim by the caller, for the case where the
         | CONTENT must be malformed in a specific way. The caller passes them
         | base64-encoded because the value travels through an environment
         | variable, where raw binary would not survive intact.
         */
        case 'raw':
            $decoded = base64_decode($raw_bytes, true);

            if ($decoded === false || $decoded === '') {
                throw new RuntimeException('A raw upload fixture requires non-empty base64 bytes.');
            }

            return $decoded;

        default:
            return masar_case_pdf();
    }
}

function masar_case_pdf(): string
{
    $lines = [
        '%PDF-1.4',
        '1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj',
        '2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj',
        '3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj',
        'trailer<</Root 1 0 R>>',
        '%%EOF',
        '',
    ];

    return implode(chr(10), $lines);
}

function masar_case_png(): string
{
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('GD is required to build the PNG fixture.');
    }

    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 10, 120, 200));

    ob_start();
    imagepng($image);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}

function masar_case_jpg(): string
{
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('GD is required to build the JPEG fixture.');
    }

    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));

    ob_start();
    imagejpeg($image, null, 90);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}

/*
 |--------------------------------------------------------------------------
 | Oversized Image Fixture
 |--------------------------------------------------------------------------
 |
 | A GENUINE PNG that is deliberately larger than PROFILE_IMAGE_MAX_SIZE.
 |
 | Inert filler (str_repeat) is not usable here. It is not an image, so the
 | MIME allowlist refuses it on content grounds whatever the size policy says,
 | and an "oversize upload is rejected" assertion would then pass with the size
 | check removed entirely - proving nothing about the cap.
 |
 | The size must also land BETWEEN the feature cap and the shared upload
 | service's own limit. Above the shared limit the shared service rejects it
 | and the feature cap is never exercised; exactly at the boundary the test
 | becomes a coin flip. So the fixture targets the midpoint of that window.
 |
 | Pseudo-random pixels are used because they do not compress, which makes the
 | encoded size track the raw pixel count closely enough to predict. A seeded
 | generator keeps the file byte-identical between runs.
 |
 */

function masar_case_oversize_png(): string
{
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('GD is required to build the oversized PNG fixture.');
    }

    $cap = PROFILE_IMAGE_MAX_SIZE;

    $shared_max = function_exists('file_upload_service_config')
        ? (int) file_upload_service_config()['max_size']
        : 10 * 1024 * 1024;

    if ($shared_max <= $cap) {
        throw new RuntimeException(
            'The shared upload limit must exceed the profile-image cap for this fixture '
            . 'to be able to discriminate between them.'
        );
    }

    $target = (int) ($cap + (($shared_max - $cap) / 2));

    /*
     | RGB noise costs about 3 bytes per pixel before compression, so this
     | predicts an encoded size near the midpoint of the window. The loop
     | shrinks and retries if that prediction overshoots the shared limit.
     */
    $edge = (int) max(2, (int) ceil(sqrt($target / 3)));
    $bytes = masar_case_noise_png($edge);

    for ($attempt = 0; $attempt < 4 && strlen($bytes) >= $shared_max; $attempt++) {
        $edge = (int) max(2, (int) ceil($edge * 0.85));
        $bytes = masar_case_noise_png($edge);
    }

    /*
     | Fail loudly rather than shipping a fixture that no longer discriminates:
     | an arithmetic regression here must not turn into a green but meaningless
     | assertion.
     */
    if ($bytes === '' || strlen($bytes) <= $cap || strlen($bytes) >= $shared_max) {
        throw new RuntimeException(
            'The oversized PNG fixture must sit between PROFILE_IMAGE_MAX_SIZE and the '
            . 'shared upload limit; it is ' . strlen($bytes) . ' bytes.'
        );
    }

    return $bytes;
}

function masar_case_noise_png(int $edge): string
{
    $image = imagecreatetruecolor($edge, $edge);

    mt_srand(20260901);

    for ($y = 0; $y < $edge; $y++) {
        for ($x = 0; $x < $edge; $x++) {
            imagesetpixel($image, $x, $y, mt_rand(0, 16777215));
        }
    }

    ob_start();
    imagepng($image, null, 0);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}
{
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('GD is required to build the JPEG fixture.');
    }

    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));

    ob_start();
    imagejpeg($image, null, 90);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}

function masar_case_webp(): string
{
    if (!function_exists('imagewebp')) {
        throw new RuntimeException('GD WebP support is required to build the WebP fixture.');
    }

    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 170, 90));

    ob_start();
    imagewebp($image, null, 90);
    $bytes = (string) ob_get_clean();

    imagedestroy($image);

    return $bytes;
}
