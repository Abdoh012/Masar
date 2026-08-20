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
    header('Content-Type: text/html; charset=UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASAR Backend</title>
    <style>
        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --get: #059669;
            --get-bg: #ecfdf5;
            --post: #b45309;
            --post-bg: #fffbeb;
            --put: #7c3aed;
            --put-bg: #f5f3ff;
            --patch: #d97706;
            --patch-bg: #fff7ed;
            --delete: #dc2626;
            --delete-bg: #fef2f2;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        .container { max-width: 1080px; margin: 0 auto; padding: 48px 24px 64px; }
        .hero {
            text-align: center;
            padding: 40px 32px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .hero h1 { margin: 0 0 8px; font-size: 32px; color: var(--text); }
        .hero h1 span { color: var(--brand); }
        .hero p { margin: 0; color: var(--muted); font-size: 16px; }
        .docs-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 48px 0 6px;
            font-size: 22px;
            font-weight: 700;
        }
        .docs-title::before {
            content: "";
            width: 8px;
            height: 24px;
            border-radius: 4px;
            background: var(--brand);
        }
        .docs-sub { color: var(--muted); margin: 0 0 28px 18px; font-size: 14px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .category {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 20px 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }
        .category h3 {
            margin: 0 0 14px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }
        .endpoint {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 6px;
            border-bottom: 1px dashed var(--border);
        }
        .endpoint:last-child { border-bottom: none; }
        .badge {
            flex: 0 0 auto;
            min-width: 58px;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        }
        .badge.get { color: var(--get); background: var(--get-bg); }
        .badge.post { color: var(--post); background: var(--post-bg); }
        .badge.put { color: var(--put); background: var(--put-bg); }
        .badge.patch { color: var(--patch); background: var(--patch-bg); }
        .badge.delete { color: var(--delete); background: var(--delete-bg); }
        .path {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 13px;
            color: var(--text);
            word-break: break-all;
            background: var(--bg);
            padding: 3px 8px;
            border-radius: 6px;
            flex: 1 1 auto;
        }
        .footer-note {
            margin-top: 32px;
            padding: 18px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--muted);
            font-size: 14px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }
        .count-pill {
            display: inline-block;
            margin-left: 8px;
            background: var(--brand);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            vertical-align: middle;
        }
        @media (max-width: 640px) {
            .container { padding: 24px 14px 48px; }
            .hero h1 { font-size: 26px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>MASAR <span>Backend</span></h1>
            <p>The project is now ready to begin work on the main pages and core services.</p>
        </div>

        <h2 class="docs-title">API Endpoints<span class="count-pill">52</span></h2>
        <p class="docs-sub">REST API &mdash; base URL: <code>/api/v1</code> &middot; JSON responses &middot; JWT authentication</p>

        <div class="grid">

            <section class="category">
                <h3>Health / System</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/health</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/health</span></div>
            </section>

            <section class="category">
                <h3>Authentication</h3>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/register</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/login</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/refresh</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/logout</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/auth/me</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/change-password</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/forgot-password</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/auth/resend-reset-otp</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/verify-reset-otp</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/auth/reset-password</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/auth/google</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/auth/google/callback</span></div>
            </section>

            <section class="category">
                <h3>Users</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/users/me</span></div>
                <div class="endpoint"><span class="badge put">PUT</span><span class="path">/api/v1/users/me</span></div>
                <div class="endpoint"><span class="badge delete">DELETE</span><span class="path">/api/v1/users/me</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/users/{id}</span></div>
            </section>

            <section class="category">
                <h3>Students</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/students/me</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/students/profile</span></div>
                <div class="endpoint"><span class="badge put">PUT</span><span class="path">/api/v1/students/profile</span></div>
            </section>

            <section class="category">
                <h3>Companies</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/companies/me</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/companies</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/companies/{id}</span></div>
            </section>

            <section class="category">
                <h3>Trainings</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/trainings</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/trainings</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/trainings/{id}</span></div>
            </section>

            <section class="category">
                <h3>Certificates</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/certificates</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/certificates</span></div>
            </section>

            <section class="category">
                <h3>Search</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/search</span></div>
            </section>

            <section class="category">
                <h3>Notifications</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/notifications</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/notifications/{id}</span></div>
            </section>

            <section class="category">
                <h3>Applications</h3>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/applications</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/applications/my</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/applications/{id}</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/applications</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/applications/withdraw</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/applications/accept</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/applications/reject</span></div>
            </section>

            <section class="category">
                <h3>Messaging</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/conversations</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/conversations</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/conversations/{id}</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/conversations/{id}/messages</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/conversations/{id}/messages</span></div>
            </section>

            <section class="category">
                <h3>Files</h3>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/api/v1/files</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/files</span></div>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/files/{id}</span></div>
                <div class="endpoint"><span class="badge delete">DELETE</span><span class="path">/api/v1/files/{id}</span></div>
            </section>

            <section class="category">
                <h3>Admin</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/api/v1/admin/dashboard</span></div>
            </section>

            <section class="category">
                <h3>Cron</h3>
                <div class="endpoint"><span class="badge get">GET</span><span class="path">/cron</span></div>
                <div class="endpoint"><span class="badge post">POST</span><span class="path">/cron/run</span></div>
            </section>

        </div>

        <div class="footer-note">
            <p style="margin:0;">If you want, in the next step I can complete advanced pages such as the admin dashboard and a basic frontend.</p>
        </div>
    </div>
</body>
</html>
HTML;
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
