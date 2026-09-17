<?php

/**
 * MASAR - Applications Issued Certificates Endpoint Case Runner (internal)
 *
 * One-shot subprocess executed by tests/applications_certificates_endpoint_regression.php.
 * NOT a standalone test.
 *
 * Usage: php tests/applications_certificates_endpoint_case.php <guest|bearer> <token> [request_uri]
 *
 * It boots the same request shim as public/index.php (minus static asset /
 * HTML handling), issues the given GET request (defaults to
 * /api/v1/applications/certificates), and prints:
 *
 *     STATUS=<http_code>
 *     BODY=<raw json body>
 *
 * The request_uri may carry a query string (e.g. ?student_id=1464); the query
 * is parsed into $_GET exactly like the real front controller does, so the
 * endpoint must prove it ignores client-supplied parameters.
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

$scenario    = trim((string) ($argv[1] ?? 'guest'));
$token       = (string) ($argv[2] ?? '');
$request_uri = (string) ($argv[3] ?? '/api/v1/applications/certificates');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = $request_uri;
$_GET = [];
$query_part = is_string($_SERVER['REQUEST_URI'])
    ? (string) (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '')
    : '';
if ($query_part !== '') {
    parse_str($query_part, $_GET);
}
$_SERVER['QUERY_STRING'] = $query_part;
$_COOKIE = [];

if ($scenario === 'bearer') {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
}

register_shutdown_function(static function (): void {
    echo "\nSTATUS=" . (http_response_code() ?: 200) . "\n";
    echo 'BODY=' . (ob_get_contents() ?: '') . "\n";
});

ob_start();

require BASE . 'routes/applications.php';