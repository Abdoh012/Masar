<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/student.php';
require_once __DIR__ . '/../app/modules/students/controllers/student_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/students/me' && $method === 'GET') {
    middleware_student();
    student_me();
    return;
}

if ($path === '/api/v1/students/profile' && $method === 'POST') {
    middleware_student();
    student_create_profile();
    return;
}

if ($path === '/api/v1/students/profile' && $method === 'PUT') {
    middleware_student();
    student_update_profile();
    return;
}

response_not_found('Student endpoint not found.');
