<?php

/**
 * MASAR - File Access Case Runner (internal)
 *
 * One-shot subprocess executed by tests/file_access_regression.php. NOT a
 * standalone test.
 *
 * Usage:
 *   php tests/file_access_case.php <path> <method> <guest|bearer> <token>
 *
 * It boots the same request shim as public/index.php (minus static asset /
 * HTML handling) and requires routes/files.php directly.
 *
 * The body is printed base64-encoded because a successful image response is
 * binary and can contain newlines that would corrupt the line-oriented
 * STATUS=/BODY= protocol used by the other case runners.
 *
 *     STATUS=<http_code>
 *     BODY_B64=<base64 of the raw response body>
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

$path     = (string) ($argv[1] ?? '/api/v1/files/1');
$method   = strtoupper((string) ($argv[2] ?? 'GET'));
$scenario = (string) ($argv[3] ?? 'guest');
$token    = (string) ($argv[4] ?? '');

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

register_shutdown_function(static function (): void {
    /*
     * Capture the buffer BEFORE echoing the protocol lines: echo() writes into
     * the same active buffer, so reading it afterwards would include STATUS=.
     */
    $body = (string) (ob_get_contents() ?: '');

    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY_B64=' . base64_encode($body) . "\n";
});

ob_start();

require BASE . 'routes/files.php';
