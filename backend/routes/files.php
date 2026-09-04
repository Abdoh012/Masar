<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/auth.php';
require_once __DIR__ . '/../app/modules/files/controllers/file_controller.php';

$path = request_path();
$method = request_method();

function file_route_respond( array $result, int $success_status = 200 ): void {
    if ( !empty( $result['download'] ) && !empty( $result['path'] ) && is_file( $result['path'] ) ) {
        header( 'Content-Type: ' . ( $result['mime_type'] ?? 'application/octet-stream' ) );
        header( 'Content-Length: ' . (int) ( $result['size'] ?? filesize( $result['path'] ) ) );
        header( 'Content-Disposition: attachment; filename="' . addslashes( $result['filename'] ?? basename( $result['path'] ) ) . '"' );
        readfile( $result['path'] );
        exit;
    }

    if ( !empty( $result['success'] ) ) {
        response_json( $result['data'] ?? null, $success_status, $result['message'] ?? 'Success.' );
    }

    response_error(
        $result['message'] ?? 'Unable to process request.',
        (int) ( $result['status'] ?? 400 )
    );
}

/*
 * Require authentication for all file endpoints.
 * Client-supplied user_id is never trusted.
 * The authenticated user ID is obtained from auth_user() in the controller.
 */

if ($path === '/api/v1/files' && $method === 'POST') {
    middleware_auth();
    $request_data = request_input();
    $files = request_files();
    if ( empty( $request_data['type'] ) ) {
        $request_data['type'] = 'other';
    }
    $result = file_controller_upload( $request_data, $files );
    file_route_respond( $result, 201 );
    return;
}

if ($path === '/api/v1/files/multiple' && $method === 'POST') {
    middleware_auth();
    $request_data = request_input();
    $files = request_files();
    if ( empty( $request_data['type'] ) ) {
        $request_data['type'] = 'other';
    }
    $result = file_controller_upload_multiple( $request_data, $files );
    file_route_respond( $result, 201 );
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)/download$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    $result = file_controller_download((int) $matches[1]);
    file_route_respond($result);
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    $file_id = (int) $matches[1];
    $download = request_get( 'download' );
    if ( $download === 'true' || $download === '1' ) {
        $result = file_controller_download( $file_id );
    } else {
        $result = file_controller_show( $file_id );
    }
    file_route_respond( $result );
    return;
}

if ($path === '/api/v1/files' && $method === 'GET') {
    middleware_auth();
    $result = file_controller_index( request_query() );
    file_route_respond( $result );
    return;
}

if (preg_match('#^/api/v1/files/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    middleware_auth();
    $file_id = (int) $matches[1];
    $result = file_controller_delete( $file_id );
    file_route_respond( $result );
    return;
}

response_not_found('File endpoint not found.');