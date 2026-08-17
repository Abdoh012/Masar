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

if ($path === '/api/v1/students/profile/status' && $method === 'GET') {
    middleware_student();
    student_profile_status();
    return;
}

if ($path === '/api/v1/students/profile/complete' && $method === 'POST') {
    middleware_student();
    student_complete_profile();
    return;
}

if ($path === '/api/v1/students/me/skills' && $method === 'GET') {
    middleware_student();
    student_skills_index();
    return;
}

if ($path === '/api/v1/students/me/skills' && $method === 'POST') {
    middleware_student();
    student_skill_add();
    return;
}

if ($path === '/api/v1/students/me/skills' && $method === 'PUT') {
    middleware_student();
    student_skills_update();
    return;
}

if ($path === '/api/v1/students/me/skills' && $method === 'DELETE') {
    middleware_student();
    student_skill_remove();
    return;
}

if ($path === '/api/v1/students/me/cv' && $method === 'GET') {
    middleware_student();
    student_cv_show();
    return;
}

if ($path === '/api/v1/students/me/cv' && $method === 'POST') {
    middleware_student();
    student_cv_set();
    return;
}

if ($path === '/api/v1/students/me/cv' && $method === 'DELETE') {
    middleware_student();
    student_cv_remove();
    return;
}

if (preg_match('#^/api/v1/students/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    student_show((int) $matches[1]);
    return;
}

response_not_found('Student endpoint not found.');
