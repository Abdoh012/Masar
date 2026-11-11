<?php

/**
 * MASAR - Student Contact Update Case Runner (internal)
 *
 * One-shot subprocess executed by
 * tests/student_contact_update_regression.php. NOT a standalone test.
 *
 * Usage:
 *   php tests/student_contact_update_case.php <route-file> <path> <method> <guest|bearer> <token>
 *
 * It boots the same request shim as public/index.php (minus static asset /
 * HTML handling), issues the given request against the given route file, and
 * prints:
 *
 *     STATUS=<http_code>
 *     BODY=<raw json body>
 *
 * The request body travels through the ENVIRONMENT (MASAR_CASE_BODY) rather
 * than argv: JSON on a Windows command line loses its double quotes to the
 * shell's native-argument quoting and arrives as invalid JSON. Environment
 * variables are passed literally.
 *
 * This suite only exercises JSON contact updates, so it deliberately carries
 * no upload fixtures; the shared case runner for the profile suite owns those.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

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
$path       = (string) ($argv[2] ?? '/api/v1/students/me/contact');
$method     = strtoupper((string) ($argv[3] ?? 'GET'));
$scenario   = (string) ($argv[4] ?? 'guest');
$token      = (string) ($argv[5] ?? '');

$body_json = (string) (getenv('MASAR_CASE_BODY') ?: '');

$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['REQUEST_URI']    = $path;
$_SERVER['QUERY_STRING']   = (string) (parse_url($path, PHP_URL_QUERY) ?? '');
$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['SCRIPT_NAME']    = '/Masar/backend/index.php';
$_GET    = [];
$_POST   = [];
$_COOKIE = [];
$_FILES  = [];

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
        // request_input() merges $_POST under the parsed body, so a PUT
        // payload is applied here instead of through php://input.
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

require BASE . 'routes/' . $route_file;
