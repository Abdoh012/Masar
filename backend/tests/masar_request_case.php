<?php

/**
 * MASAR - Generic Request Case Runner (internal)
 *
 * One-shot subprocess driven by a *_regression.php driver. NOT a standalone
 * test. It exists because the authenticated contact endpoint and the password
 * endpoint live in two different route files, and the existing per-suite shims
 * are hardcoded to one path.
 *
 * Usage:
 *   php tests/masar_request_case.php <route-file> <path> <method> <guest|bearer> <token> [body-json]
 *
 * The optional body-json is an object applied to $_POST, which request_input()
 * merges over the parsed body, so a write endpoint can be driven without a real
 * HTTP body. It travels through the ENVIRONMENT (MASAR_CASE_BODY) rather than
 * argv because JSON loses its double quotes to native-argument quoting on
 * Windows.
 *
 * It prints:
 *     STATUS=<http_code>
 *     BODY=<raw json body>
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

$route_basename = basename($route_file);

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
 | Populate $_GET from the query string.
 |
 | request_path() strips the query off REQUEST_URI, and request_query() reads
 | $_GET directly, so a shim that only sets QUERY_STRING silently hands the
 | routes an empty $_GET - which made every filtered request look like it had no
 | parameters at all. parse_str() reproduces what PHP does for a real request,
 | including the `a[]=1&a[]=2` array form.
 */
if ($_SERVER['QUERY_STRING'] !== '') {
    parse_str($_SERVER['QUERY_STRING'], $parsed_query);
    $_GET = is_array($parsed_query) ? $parsed_query : [];
}

if ($scenario === 'bearer') {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
}

if (trim($body_json) !== '') {
    $decoded = json_decode($body_json, true);

    if (is_array($decoded)) {
        foreach ($decoded as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

register_shutdown_function(static function (): void {
    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY=' . (ob_get_contents() ?: '') . "\n";
});

ob_start();

require BASE . 'routes/' . $route_basename;
