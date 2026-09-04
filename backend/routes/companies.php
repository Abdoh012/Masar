<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/company.php';
require_once __DIR__ . '/../app/modules/companies/controllers/company_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/companies/me' && $method === 'GET') {
    middleware_company();
    company_controller_get_my_profile();
    return;
}

if ($path === '/api/v1/companies/me' && $method === 'PUT') {
    middleware_company();
    company_controller_update_my_profile();
    return;
}

if ($path === '/api/v1/companies/me/logo' && $method === 'POST') {
    middleware_company();
    company_controller_update_logo();
    return;
}

if ($path === '/api/v1/companies' && $method === 'POST') {
    company_controller_create();
    return;
}

if (preg_match('#^/api/v1/companies/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_company();
    company_controller_get_by_id((int) $matches[1]);
    return;
}

response_not_found('Company endpoint not found.');
