<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/files/controllers/file_controller.php';

$path = request_path();
$method = request_method();

/*
 * Require authentication for all file endpoints.
 * Client-supplied user_id is never trusted.
 * The authenticated user ID is obtained from auth_user() in the controller.
 */

if ($path === '/api/v1/files' && $method === 'POST') {
    $request_data = request_json_body();
    $files = request_files();
    // Get user_id from the controller which calls auth_user()
    file_controller_upload($request_data, $files);
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    $file_id = (int) $matches[1];
    file_controller_show($file_id);
    return;
}

if ($path === '/api/v1/files' && $method === 'GET') {
    middleware_auth();
    file_controller_index(request_get_params());
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    middleware_auth();
    $file_id = (int) $matches[1];
    file_controller_delete($file_id);
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    if (str_contains(request_query('download'), 'true')) {
        middleware_auth();
        $file_id = (int) $matches[1];
        file_controller_download($file_id);
        return;
    }
}

response_not_found('File endpoint not found.');