<?php

/**
 * MASAR CORS Middleware
 *
 * Handles Cross-Origin Resource Sharing (CORS)
 * between the frontend application and PHP backend API.
 */


/*
|--------------------------------------------------------------------------
| Load Configuration
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../config/cors.php';

function middleware_cors(): void
{
    $cors_config = require __DIR__ . '/../../config/cors.php';
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed_origins = $cors_config['allowed_origins'] ?? [];
    $allow_credentials = !empty($cors_config['allow_credentials']);

    if ($origin !== '' && in_array($origin, $allowed_origins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }

    $allowed_methods = implode(', ', $cors_config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
    $allowed_headers = implode(', ', $cors_config['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-CSRF-Token', 'X-Requested-With']);
    $max_age = (int) ($cors_config['max_age'] ?? 86400);

    header('Access-Control-Allow-Methods: ' . $allowed_methods);
    header('Access-Control-Allow-Headers: ' . $allowed_headers);
    header('Access-Control-Expose-Headers: Content-Type, Authorization, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: ' . ($allow_credentials ? 'true' : 'false'));
    header('Access-Control-Max-Age: ' . $max_age);

    if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS')) {
        http_response_code(204);
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Handle CORS
|--------------------------------------------------------------------------
*/

function cors_handle(): void
{
    middleware_cors();
}