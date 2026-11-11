<?php

/**
 * MASAR - Messaging Phase 1 Case Runner (internal)
 *
 * One-shot subprocess executed by tests/messaging_phase1_regression.php.
 * NOT a standalone test.
 *
 * Usage:
 *   php tests/messaging_phase1_case.php <path> <method> <guest|bearer> <token>
 *
 * The JSON body travels through the environment (MASAR_CASE_BODY) because JSON
 * on a Windows command line loses its double quotes to native-argument quoting.
 *
 * It boots the API request the way public/index.php does, prints:
 *
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

$app_config = require BASE . 'app/config/app.php';
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

/*
 * The path travels through the ENVIRONMENT as well. On Windows,
 * escapeshellarg() replaces percent signs with spaces, which destroys
 * URL-encoded query values such as %20 before PHP ever sees them.
 */
$env_path  = (string) (getenv('MASAR_CASE_PATH') ?: '');
$path      = $env_path !== '' ? $env_path : (string) ($argv[1] ?? '/api/v1/conversations');
$method   = strtoupper((string) ($argv[2] ?? 'GET'));
$scenario = (string) ($argv[3] ?? 'guest');
$token    = (string) ($argv[4] ?? '');
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
        foreach ($decoded as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

/*
 * The messaging route file relies on the authenticated user being populated
 * before the controller runs (public/index.php does this centrally).
 */
token_authenticate_request();

register_shutdown_function(static function (): void {
    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY=' . (ob_get_contents() ?: '') . "\n";
});

ob_start();

require BASE . 'routes/messaging.php';
