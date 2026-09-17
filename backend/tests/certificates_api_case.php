<?php

/**
 * MASAR - Certificates API Case Runner (internal)
 *
 * One-shot subprocess executed by tests/certificates_lifecycle_regression.php.
 * NOT a standalone test.
 *
 * Usage:
 *   php tests/certificates_api_case.php <guest|bearer> <token> <METHOD> <request_uri> [body_file]
 *
 * body_file (optional) is a path to a file containing the raw JSON request
 * body. A file is used instead of an inline argument so the JSON survives
 * Windows quote handling in the parent regression harness.
 *
 * It boots the same request shim as public/index.php (minus static asset /
 * HTML handling), issues the given HTTP request against routes/certificates.php
 * and prints:
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
require_once BASE . 'app/modules/notifications/services/notification_service.php';

register_error_handler();
register_exception_handler();
security_apply_http_headers();
cors_handle();

$scenario     = trim((string) ($argv[1] ?? 'guest'));
$token        = (string) ($argv[2] ?? '');
$method       = strtoupper(trim((string) ($argv[3] ?? 'GET')));
$request_uri  = (string) ($argv[4] ?? '/api/v1/certificates');
$body_file    = (string) ($argv[5] ?? '');

$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['REQUEST_URI']    = $request_uri;
$_GET = [];
if (is_string($_SERVER['REQUEST_URI'])) {
    $query_part = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '';
    $_SERVER['QUERY_STRING'] = $query_part;
    if ($query_part !== '') {
        parse_str($query_part, $_GET);
    } else {
        $_SERVER['QUERY_STRING'] = '';
    }
} else {
    $_SERVER['QUERY_STRING'] = '';
}
$_POST = [];
$_COOKIE = [];

$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
$_SERVER['CONTENT_TYPE']      = 'application/json';

if ($body_file !== '' && is_file($body_file)) {
    $raw = (string) file_get_contents($body_file);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        foreach ($decoded as $key => $value) {
            if (is_scalar($value)) {
                $_POST[$key] = is_bool($value)
                    ? ($value ? '1' : '0')
                    : (string) $value;
            }
        }
    }
}

if ($scenario === 'bearer') {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
}

register_shutdown_function(static function (): void {
    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY=' . (ob_get_contents() ?: '') . "\n";
});

ob_start();

require BASE . 'routes/certificates.php';