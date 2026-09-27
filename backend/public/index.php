<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../')->safeLoad();
}

require_once __DIR__ . '/../app/shared/functions/email.php';

// Log basic request info for debugging rewrite/routing issues.
// SECURITY: only the PATH is logged; the query string is never written,
// because the Google callback query contains the single-use authorization
// code and the OAuth state (both must never be logged).
$reqLog = __DIR__ . '/../storage/logs/request_debug.log';
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$logLine = sprintf(
    "[%s] METHOD=%s PATH=%s SCRIPT_NAME=%s PATH_INFO=%s",
    date('c'),
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $reqPath,
    $_SERVER['SCRIPT_NAME'] ?? '-',
    $_SERVER['PATH_INFO'] ?? '-'
);
if (str_ends_with($reqPath, '/auth/google/callback')) {
    $callback_code = trim($_GET['code'] ?? '');
    $callback_state = trim($_GET['state'] ?? '');
    $logLine .= sprintf(
        ' CALLBACK=1 HAS_CODE=%s CODE_LEN=%d HAS_STATE=%s STATE_LEN=%d REDIRECT_URI=%s',
        $callback_code !== '' ? '1' : '0',
        strlen($callback_code),
        $callback_state !== '' ? '1' : '0',
        strlen($callback_state),
        getenv('GOOGLE_REDIRECT_URI') ?: '(not set)'
    );
}
@file_put_contents($reqLog, $logLine . "\n", FILE_APPEND);

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/token.php';
require_once __DIR__ . '/../app/core/errors/error_handler.php';
require_once __DIR__ . '/../app/core/errors/exception_handler.php';
require_once __DIR__ . '/../app/core/middleware/cors.php';
require_once __DIR__ . '/../app/shared/functions/security.php';
require_once __DIR__ . '/../app/shared/functions/audit.php';

register_error_handler();
register_exception_handler();
security_apply_http_headers();
cors_handle();

// Attempt to authenticate the request from Bearer token or remember cookie.
token_authenticate_request();

$path = request_path();

if ($path !== '/' && $path !== '') {
    $requested_file = ltrim($path, '/');
    $candidate_paths = [];

    if ($requested_file !== '') {
        $candidate_paths[] = __DIR__ . '/' . $requested_file;
    }

    foreach ($candidate_paths as $candidate_path) {
        if (is_file($candidate_path)) {
            $extension = strtolower(pathinfo($candidate_path, PATHINFO_EXTENSION));
            $mime_types = [
                'css' => 'text/css; charset=UTF-8',
                'html' => 'text/html; charset=UTF-8',
                'js' => 'application/javascript; charset=UTF-8',
                'json' => 'application/json; charset=UTF-8',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'txt' => 'text/plain; charset=UTF-8',
                'ico' => 'image/x-icon',
            ];
            $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime_type);
            readfile($candidate_path);
            exit;
        }
    }
}

if ($path === '/' || $path === '') {
    require_once __DIR__ . '/../app/shared/functions/api_docs_page.php';
    header('Content-Type: text/html; charset=UTF-8');
    api_docs_render($app_config);
    exit;
}

if ($path === '/health') {
    response_success(['status' => 'ok'], 'Service is healthy.');
}

if ($path === '/cron' || $path === '/cron/run') {
    require_once __DIR__ . '/../routes/cron.php';
    return;
}

if (str_starts_with($path, '/api/v1/auth')) {
    require_once __DIR__ . '/../routes/auth.php';
    return;
}

if (str_starts_with($path, '/api/v1/lookups')) {
    require_once __DIR__ . '/../routes/lookups.php';
    return;
}

if (str_starts_with($path, '/api/v1/users')) {
    require_once __DIR__ . '/../routes/users.php';
    return;
}

if (str_starts_with($path, '/api/v1/students')) {
    require_once __DIR__ . '/../routes/students.php';
    return;
}

if (str_starts_with($path, '/api/v1/companies')) {
    require_once __DIR__ . '/../routes/companies.php';
    return;
}

if (str_starts_with($path, '/api/v1/trainings')) {
    require_once __DIR__ . '/../routes/trainings.php';
    return;
}

if (str_starts_with($path, '/api/v1/certificates')) {
    require_once __DIR__ . '/../routes/certificates.php';
    return;
}

if (str_starts_with($path, '/api/v1/search')) {
    require_once __DIR__ . '/../routes/search.php';
    return;
}

if (str_starts_with($path, '/api/v1/notifications')) {
    require_once __DIR__ . '/../routes/notifications.php';
    return;
}

if (str_starts_with($path, '/api/v1/applications')) {
    require_once __DIR__ . '/../routes/applications.php';
    return;
}

if (str_starts_with($path, '/api/v1/conversations') || str_starts_with($path, '/api/v1/messages')) {
    require_once __DIR__ . '/../routes/messaging.php';
    return;
}

if (str_starts_with($path, '/api/v1/files')) {
    require_once __DIR__ . '/../routes/files.php';
    return;
}

if (str_starts_with($path, '/api/v1/admin')) {
    require_once __DIR__ . '/../app/core/middleware/admin.php';
    $admin_user = middleware_admin();

    if (in_array(request_method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        require_once __DIR__ . '/../app/core/middleware/csrf.php';
        csrf_require();
    }

    $sensitive_admin_path = preg_match(
        '#/(delete|suspend|approve|reject|revoke|restore|issue|status)#i',
        $path
    ) === 1;

    if ($sensitive_admin_path && in_array(request_method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $admin_context = request_input();
        $admin_context['user_id'] = (int) ($admin_user['id'] ?? 0);
        $admin_context['role'] = $admin_user['role'] ?? null;
        security_require_admin_reauth($admin_user, $admin_context, 'admin_route_sensitive_action');
    }

    require_once __DIR__ . '/../routes/admin.php';
    return;
}

if ($path === '/api/v1' || $path === '/api/v1/health') {
    require_once __DIR__ . '/../routes/api.php';
    return;
}

response_not_found('Page not found.');
