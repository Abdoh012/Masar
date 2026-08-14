<?php

/**
 * MASAR HTTP Response Helper
 *
 * Provides a unified JSON response format for the API.
 */


/*
|--------------------------------------------------------------------------
| Send JSON Response
|--------------------------------------------------------------------------
*/

function response_json(
    mixed $data = null,
    int $status_code = 200,
    ?string $message = null,
    array $errors = []
): never {

    if (function_exists('security_apply_http_headers')) {
        security_apply_http_headers();
    }

    http_response_code($status_code);

    header('Content-Type: application/json; charset=UTF-8');

    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'data' => $data,
        'errors' => empty($errors) ? null : $errors,
    ];

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Set Cookie
|--------------------------------------------------------------------------
*/

function response_set_cookie(
    string $name,
    string $value,
    int $expires = 0,
    string $path = '/',
    string $domain = '',
    bool $secure = false,
    bool $http_only = true,
    string $same_site = 'Lax'
): void {
    setcookie(
        $name,
        $value,
        [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $http_only,
            'samesite' => $same_site,
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Delete Cookie
|--------------------------------------------------------------------------
*/

function response_delete_cookie(
    string $name,
    string $path = '/',
    string $domain = '',
    bool $secure = false,
    bool $http_only = true,
    string $same_site = 'Lax'
): void {
    response_set_cookie(
        $name,
        '',
        time() - 3600,
        $path,
        $domain,
        $secure,
        $http_only,
        $same_site
    );
}


function response_remember_cookie_settings(): array
{
    global $app_config;

    return [
        'name' => $app_config['auth']['cookie_name'] ?? 'MASAR_REMEMBER',
        'path' => $app_config['auth']['cookie_path'] ?? '/',
        'secure' => $app_config['auth']['cookie_secure'] ?? false,
        'http_only' => $app_config['auth']['cookie_httponly'] ?? true,
        'same_site' => $app_config['auth']['cookie_samesite'] ?? 'Lax',
    ];
}

function response_set_remember_cookie(
    string $value,
    int $expires
): void {
    $settings = response_remember_cookie_settings();

    response_set_cookie(
        $settings['name'],
        $value,
        $expires,
        $settings['path'],
        '',
        $settings['secure'],
        $settings['http_only'],
        $settings['same_site']
    );
}

function response_clear_remember_cookie(): void
{
    $settings = response_remember_cookie_settings();

    response_delete_cookie(
        $settings['name'],
        $settings['path'],
        '',
        $settings['secure'],
        $settings['http_only'],
        $settings['same_site']
    );
}


/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

function response_success(
    mixed $data = null,
    ?string $message = null,
    int $status_code = 200
): never {

    response_json(
        $data,
        $status_code,
        $message
    );
}


/*
|--------------------------------------------------------------------------
| Created Response
|--------------------------------------------------------------------------
*/

function response_created(
    mixed $data = null,
    ?string $message = null
): never {

    response_json(
        $data,
        201,
        $message
    );
}


/*
|--------------------------------------------------------------------------
| No Content Response
|--------------------------------------------------------------------------
*/

function response_no_content(): never
{
    http_response_code(204);

    exit;
}


/*
|--------------------------------------------------------------------------
| Error Response
|--------------------------------------------------------------------------
*/

function response_error(
    string $message,
    int $status_code = 400,
    array $errors = [],
    mixed $data = null
): never {

    response_json(
        $data,
        $status_code,
        $message,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validation Error Response
|--------------------------------------------------------------------------
*/

function response_validation_error(
    array $errors,
    string $message = 'Validation failed.'
): never {

    response_error(
        $message,
        422,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Unauthorized Response
|--------------------------------------------------------------------------
*/

function response_unauthorized(
    string $message = 'Authentication required.'
): never {

    response_error(
        $message,
        401
    );
}


/*
|--------------------------------------------------------------------------
| Forbidden Response
|--------------------------------------------------------------------------
*/

function response_forbidden(
    string $message = 'You do not have permission to perform this action.'
): never {

    response_error(
        $message,
        403
    );
}


/*
|--------------------------------------------------------------------------
| Not Found Response
|--------------------------------------------------------------------------
*/

function response_not_found(
    string $message = 'Resource not found.'
): never {

    response_error(
        $message,
        404
    );
}


/*
|--------------------------------------------------------------------------
| Method Not Allowed Response
|--------------------------------------------------------------------------
*/

function response_method_not_allowed(
    string $message = 'HTTP method not allowed.'
): never {

    response_error(
        $message,
        405
    );
}


/*
|--------------------------------------------------------------------------
| Too Many Requests Response
|--------------------------------------------------------------------------
*/

function response_too_many_requests(
    string $message = 'Too many requests.'
): never {

    response_error(
        $message,
        429
    );
}


/*
|--------------------------------------------------------------------------
| Server Error Response
|--------------------------------------------------------------------------
*/

function response_server_error(
    string $message = 'Internal server error.'
): never {

    response_error(
        $message,
        500
    );
}