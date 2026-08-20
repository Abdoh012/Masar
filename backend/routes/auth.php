<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/auth.php';
require_once __DIR__ . '/../app/core/middleware/jwt_auth.php';
require_once __DIR__ . '/../app/core/middleware/security_headers.php';
require_once __DIR__ . '/../app/core/middleware/csrf.php';
require_once __DIR__ . '/../app/shared/functions/audit.php';
require_once __DIR__ . '/../app/services/jwt_service.php';
require_once __DIR__ . '/../app/modules/auth/controllers/auth_controller.php';

middleware_security_headers();

$path = request_path();
$method = request_method();

if ($path === '/api/v1/auth/register' && $method === 'POST') {
    auth_handle_register();
    return;
}

if ($path === '/api/v1/auth/login' && $method === 'POST') {
    auth_handle_login();
    return;
}

if ($path === '/api/v1/auth/refresh' && $method === 'POST') {
    auth_handle_refresh();
    return;
}

if ($path === '/api/v1/auth/logout' && $method === 'POST') {
    middleware_jwt_auth();
    auth_handle_logout();
    return;
}

if ($path === '/api/v1/auth/me' && $method === 'GET') {
    middleware_auth();
    auth_handle_me();
    return;
}

if ($path === '/api/v1/auth/change-password' && $method === 'POST') {
    middleware_auth();
    auth_handle_change_password();
    return;
}

if ($path === '/api/v1/auth/forgot-password' && $method === 'POST') {
    auth_handle_forgot_password();
    return;
}

if ($path === '/api/v1/auth/resend-reset-otp' && ($method === 'GET' || $method === 'POST')) {
    auth_handle_resend_reset_otp();
    return;
}

if ($path === '/api/v1/auth/verify-reset-otp' && $method === 'POST') {
    auth_handle_verify_reset_otp();
    return;
}

if ($path === '/api/v1/auth/reset-password' && $method === 'POST') {
    auth_handle_reset_password();
    return;
}

if ($path === '/api/v1/auth/google' && $method === 'GET') {
    auth_handle_google_oauth();
    return;
}

if ($path === '/api/v1/auth/google/callback' && $method === 'GET') {
    auth_handle_google_oauth_callback();
    return;
}

response_not_found('Auth endpoint not found.');
