<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/health') {
    response_success([
        'app' => 'MASAR',
        'status' => 'ok',
        'time' => date('c'),
        'environment' => $app_config['environment'] ?? 'development',
    ], 'API is healthy.');
}

if ($path === '/api/v1') {
    response_success([
        'name' => 'MASAR API',
        'version' => 'v1',
        'documentation' => '/api/v1/health',
    ], 'MASAR API is ready.');
}

response_not_found('API endpoint not found.');
