<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/student.php';
require_once __DIR__ . '/../app/core/middleware/company.php';
require_once __DIR__ . '/../app/modules/training/controllers/application_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/applications' && $method === 'POST') {
    application_controller_create();
    return;
}

if ($path === '/api/v1/applications/my' && $method === 'GET') {
    middleware_student();
    application_controller_my_applications();
    return;
}

if (preg_match('#^/api/v1/applications/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_student();
    application_controller_show((int) $matches[1]);
    return;
}

if ($path === '/api/v1/applications' && $method === 'GET') {
    middleware_company();
    application_controller_company_applications();
    return;
}

if ($path === '/api/v1/applications/withdraw' && $method === 'POST') {
    middleware_student();
    application_controller_withdraw();
    return;
}

if ($path === '/api/v1/applications/accept' && $method === 'POST') {
    middleware_company();
    application_controller_accept();
    return;
}

if ($path === '/api/v1/applications/reject' && $method === 'POST') {
    middleware_company();
    application_controller_reject();
    return;
}

response_not_found('Application endpoint not found.');