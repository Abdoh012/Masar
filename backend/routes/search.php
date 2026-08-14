<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/search/controllers/search_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/search' && $method === 'GET') {
    search_controller_search();
    return;
}

response_not_found('Search endpoint not found.');
