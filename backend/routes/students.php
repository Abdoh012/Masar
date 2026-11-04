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

if ($path === '/api/v1/students/me/full-name' && $method === 'GET') {
    middleware_student();
    student_full_name_index();
    return;
}

if ($path === '/api/v1/students/me/full-name' && $method === 'PUT') {
    middleware_student();
    student_full_name_update();
    return;
}

if ($path === '/api/v1/students/me/username' && $method === 'GET') {
    middleware_student();
    student_username_index();
    return;
}

if ($path === '/api/v1/students/me/username' && $method === 'PUT') {
    middleware_student();
    student_username_update();
    return;
}

if ($path === '/api/v1/students/me/contact' && $method === 'GET') {
    middleware_student();
    student_contact_index();
    return;
}

if ($path === '/api/v1/students/me/contact' && $method === 'PUT') {
    middleware_student();
    student_contact_update();
    return;
}

if ($path === '/api/v1/students/me/academic-status' && $method === 'GET') {
    middleware_student();
    student_academic_status_index();
    return;
}

if ($path === '/api/v1/students/me/academic-status' && $method === 'PUT') {
    middleware_student();
    student_academic_status_update();
    return;
}

if ($path === '/api/v1/students/me/academic-status/certificate' && $method === 'DELETE') {
    middleware_student();
    student_academic_status_remove_certificate();
    return;
}

if ($path === '/api/v1/students/me/profile-image' && $method === 'POST') {
    middleware_student();
    student_profile_image_store();
    return;
}

if ($path === '/api/v1/students/me/profile-image' && $method === 'DELETE') {
    middleware_student();
    student_profile_image_remove();
    return;
}

if ($path === '/api/v1/students/me/profile-views' && $method === 'GET') {
    middleware_student();
    student_profile_views_index();
    return;
}

if ($path === '/api/v1/students/me/education' && $method === 'GET') {
    middleware_student();
    student_education_index();
    return;
}

if ($path === '/api/v1/students/me/education' && $method === 'POST') {
    middleware_student();
    student_education_store();
    return;
}

if (preg_match('#^/api/v1/students/me/education/([0-9]+)$#', $path, $matches) && $method === 'PUT') {
    middleware_student();
    student_update_education((int) $matches[1]);
    return;
}

if (preg_match('#^/api/v1/students/me/education/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    middleware_student();
    student_education_destroy((int) $matches[1]);
    return;
}

if ($path === '/api/v1/students/dashboard' && $method === 'GET') {
    middleware_student();
    student_dashboard();
    return;
}

response_not_found('Student endpoint not found.');