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
    int $status_code = HTTP_OK,
    ?string $message = null,
    array $errors = [],
    array $meta = []
): never {

    if (function_exists('security_apply_http_headers')) {
        security_apply_http_headers();
    }

    http_response_code($status_code);

    header('Content-Type: application/json; charset=UTF-8');

    global $app_config;

    $response_config = $app_config['response'] ?? [];

    $response = [
        ($response_config['success_key'] ?? 'success') => ($status_code >= 200 && $status_code < 300),
        ($response_config['message_key'] ?? 'message') => $message,
        ($response_config['data_key'] ?? 'data') => $data,
        ($response_config['errors_key'] ?? 'errors') => empty($errors) ? null : $errors,
    ];

    /*
     * Additive, opt-in top-level metadata (e.g. messaging pagination). Existing
     * callers keep the exact same four-key envelope.
     */
    foreach ($meta as $key => $value) {
        if (is_string($key) && !array_key_exists($key, $response)) {
            $response[$key] = $value;
        }
    }

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
    int $status_code = HTTP_OK,
    array $meta = []
): never {

    response_json(
        $data,
        $status_code,
        $message,
        [],
        $meta
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
        HTTP_CREATED,
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
    http_response_code(HTTP_NO_CONTENT);

    exit;
}


/*
|--------------------------------------------------------------------------
| Error Response
|--------------------------------------------------------------------------
*/

function response_error(
    string $message,
    int $status_code = HTTP_BAD_REQUEST,
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
        HTTP_UNPROCESSABLE_ENTITY,
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
        HTTP_UNAUTHORIZED
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
        HTTP_FORBIDDEN
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
        HTTP_NOT_FOUND
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
        HTTP_METHOD_NOT_ALLOWED
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
        HTTP_TOO_MANY_REQUESTS
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
        HTTP_INTERNAL_SERVER_ERROR
    );
}