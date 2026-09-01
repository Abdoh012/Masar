<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/student.php';
require_once __DIR__ . '/../app/modules/search/controllers/search_controller.php';

$path = request_path();
$method = request_method();

function search_route_respond( array $result, int $success_status = 200 ): void {
    if ( !empty( $result['success'] ) ) {
        response_success(
            $result['data'] ?? null,
            $result['message'] ?? 'Success.'
        );
    }

    response_error(
        $result['message'] ?? 'Unable to process search request.',
        (int) ( $result['status'] ?? 400 )
    );
}

if ($path === '/api/v1/search' && $method === 'GET') {
    search_route_respond(search_controller_search(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/users' && $method === 'GET') {
    search_route_respond(search_controller_users(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/companies' && $method === 'GET') {
    search_route_respond(search_controller_companies(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/trainings/filters' && $method === 'GET') {
    middleware_student();
    search_route_respond(search_controller_trainings_filters(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/trainings' && $method === 'GET') {
    middleware_student();
    search_route_respond(search_controller_trainings(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/students' && $method === 'GET') {
    search_route_respond(search_controller_students(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/certificates' && $method === 'GET') {
    search_route_respond(search_controller_certificates(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/suggestions' && $method === 'GET') {
    search_route_respond(search_controller_suggestions(request_query(), search_controller_user_id()));
    return;
}

if ($path === '/api/v1/search/recent' && $method === 'GET') {
    middleware_auth();
    $user_id = search_controller_user_id();
    search_route_respond(search_controller_recent($user_id, request_query()));
    return;
}

if ($path === '/api/v1/search/recent' && $method === 'DELETE') {
    middleware_auth();
    search_route_respond(search_controller_clear_recent(search_controller_user_id()));
    return;
}

response_not_found('Search endpoint not found.');
