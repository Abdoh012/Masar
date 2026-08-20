<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/student.php';
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

if ($path === '/api/v1/trainings/saved' && $method === 'GET') {
    middleware_student();
    training_controller_saved();
    return;
}

if (preg_match('#^/api/v1/trainings/([0-9]+)/save$#', $path, $matches) && $method === 'POST') {
    middleware_student();
    training_controller_save((int) $matches[1]);
    return;
}

if (preg_match('#^/api/v1/trainings/([0-9]+)/save$#', $path, $matches) && $method === 'DELETE') {
    middleware_student();
    training_controller_unsave((int) $matches[1]);
    return;
}

response_not_found('Training endpoint not found.');
