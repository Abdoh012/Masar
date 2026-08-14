<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/certificates/controllers/certificate_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/certificates' && $method === 'GET') {
    certificate_controller_index();
    return;
}

if ($path === '/api/v1/certificates' && $method === 'POST') {
    certificate_controller_create();
    return;
}

response_not_found('Certificate endpoint not found.');
