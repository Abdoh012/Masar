<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/training/controllers/training_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/trainings' && $method === 'GET') {
    training_controller_index();
    return;
}

if ($path === '/api/v1/trainings' && $method === 'POST') {
    training_controller_create();
    return;
}

if (preg_match('#^/api/v1/trainings/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    training_controller_show((int) $matches[1]);
    return;
}

response_not_found('Training endpoint not found.');
