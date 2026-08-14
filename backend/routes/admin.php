<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/admin.php';
require_once __DIR__ . '/../app/modules/admin/controllers/user_admin_controller.php';
require_once __DIR__ . '/../app/modules/admin/controllers/company_admin_controller.php';
require_once __DIR__ . '/../app/modules/admin/controllers/training_admin_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/admin/dashboard' && $method === 'GET') {
    middleware_admin();
    response_success([
        'module' => 'admin',
        'message' => 'Admin dashboard is ready.',
        'available_sections' => ['users', 'companies', 'trainings']
    ]);
    return;
}

response_not_found('Admin endpoint not found.');
