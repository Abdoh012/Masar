<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/auth.php';
require_once __DIR__ . '/../app/core/middleware/csrf.php';
require_once __DIR__ . '/../app/modules/users/controllers/user_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/users/me' && $method === 'GET') {
    middleware_auth();
    user_me();
    return;
}

if ($path === '/api/v1/users/me' && $method === 'PUT') {
    middleware_auth();
    user_update_me();
    return;
}

if ($path === '/api/v1/users/me' && $method === 'DELETE') {
    middleware_auth();
    user_delete_me();
    return;
}

if (preg_match('#^/api/v1/users/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    user_show((int) $matches[1]);
    return;
}

response_not_found('User endpoint not found.');
