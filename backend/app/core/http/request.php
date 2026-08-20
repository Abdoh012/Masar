<?php

/**
 * MASAR HTTP Request Helper
 *
 * Provides a unified way to read HTTP request data.
 */


/*
|--------------------------------------------------------------------------
| Get HTTP Method
|--------------------------------------------------------------------------
*/

function request_method(): string
{
    return strtoupper(
        $_SERVER['REQUEST_METHOD'] ?? 'GET'
    );
}


/*
|--------------------------------------------------------------------------
| Check HTTP Method
|--------------------------------------------------------------------------
*/

function request_is_method(string $method): bool
{
    return request_method() === strtoupper($method);
}


/*
|--------------------------------------------------------------------------
| Get Request URI
|--------------------------------------------------------------------------
*/

function request_uri(): string
{
    return $_SERVER['REQUEST_URI'] ?? '/';
}


/*
|--------------------------------------------------------------------------
| Get Request Path
|--------------------------------------------------------------------------
*/

function request_path(): string
{
    $uri = request_uri();

    $path = parse_url($uri, PHP_URL_PATH) ?: '/';

    if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
        $path = $_SERVER['PATH_INFO'];
    }

    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_dir = rtrim(str_replace('\\', '/', dirname($script_name)), '/');

    $base_candidates = [];

    if ($script_dir !== '' && $script_dir !== '.' && $script_dir !== '/') {
        $base_candidates[] = $script_dir;
    }

    $parent_dir = rtrim(str_replace('\\', '/', dirname($script_dir)), '/');

    if ($parent_dir !== '' && $parent_dir !== '.' && $parent_dir !== '/' && $parent_dir !== $script_dir) {
        $base_candidates[] = $parent_dir;
    }

    foreach ($base_candidates as $base_dir) {
        if (
            $path === $base_dir ||
            str_starts_with($path, $base_dir . '/')
        ) {
            $path = substr($path, strlen($base_dir));
            break;
        }
    }

    $path = rtrim($path, '/');

    return $path === '' ? '/' : $path;
}


/*
|--------------------------------------------------------------------------
| Get Base URL
|--------------------------------------------------------------------------
|
| Builds the application base URL from the current request (scheme, host
| and base directory). This is portable: it never depends on a hardcoded
| host or a machine-specific Apache configuration.
|
*/

function request_base_url(): string
{
    $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

    return $scheme . '://' . $host . request_base_path();
}


/*
|--------------------------------------------------------------------------
| Get Base Path
|--------------------------------------------------------------------------
|
| Returns the URL path prefix (e.g. "/Masar/backend") that points to the
| project root, derived from SCRIPT_NAME. Empty string when the project is
| served from the web root.
|
*/

function request_base_path(): string
{
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '/';
    $script_dir = rtrim(str_replace('\\', '/', dirname($script_name)), '/');

    if ($script_dir === '' || $script_dir === '.' || $script_dir === '/') {
        return '';
    }

    $base_path = $script_dir;

    if (substr($base_path, -7) === '/public') {
        $base_path = substr($base_path, 0, -7);
    }

    return $base_path;
}


/*
|--------------------------------------------------------------------------
| Get Query Parameter
|--------------------------------------------------------------------------
*/

function request_query(
    ?string $key = null,
    mixed $default = null
): mixed {

    if ($key === null) {
        return $_GET;
    }

    return $_GET[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Get Query Parameter (Alias)
|--------------------------------------------------------------------------
|
| Convenience alias used by controllers for GET query parameters.
|
*/

function request_get(
    ?string $key = null,
    mixed $default = null
): mixed {

    return request_query(
        $key,
        $default
    );
}


/*
|--------------------------------------------------------------------------
| Get Integer Query Parameter
|--------------------------------------------------------------------------
*/

function request_get_int(
    ?string $key = null,
    int $default = 0
): int {

    $value =
        request_query(
            $key
        );

    if ($value === null || $value === '') {
        return $default;
    }

    $value =
        filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

    return $value === false
        ? $default
        : (int) $value;
}


/*
|--------------------------------------------------------------------------
| Get POST Parameter
|--------------------------------------------------------------------------
*/

function request_post(
    ?string $key = null,
    mixed $default = null
): mixed {

    if ($key === null) {
        return $_POST;
    }

    return $_POST[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Get JSON Body
|--------------------------------------------------------------------------
|
| Reads application/json request body.
|
*/

function request_json(): array
{
    static $body = null;

    if ($body !== null) {
        return $body;
    }

    $raw_input = file_get_contents('php://input');

    if ($raw_input === false || trim($raw_input) === '') {
        $body = [];

        return $body;
    }

    $decoded = json_decode($raw_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $decoded = null;
    }

    if (!is_array($decoded)) {
        $decoded = [];
    }

    $body = $decoded;

    return $body;
}


/*
|--------------------------------------------------------------------------
| Get Input Value
|--------------------------------------------------------------------------
|
| Automatically checks JSON body first, then POST data.
|
*/

function request_input(
    ?string $key = null,
    mixed $default = null
): mixed {

    $json = [];

    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $raw_input = file_get_contents('php://input');

    if (
        $raw_input !== false &&
        trim($raw_input) !== '' &&
        (
            str_contains(strtolower($content_type), 'application/json') ||
            str_contains(strtolower($content_type), 'application/x-www-form-urlencoded') ||
            str_contains(strtolower($content_type), 'multipart/form-data')
        )
    ) {
        $json = request_json();
    } elseif ($raw_input !== false && trim($raw_input) !== '') {
        $json = request_json();
    }

    if ($key === null) {

        return array_merge(
            $_POST,
            $json
        );
    }

    if (array_key_exists($key, $json)) {
        return $json[$key];
    }

    return $_POST[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Check Input
|--------------------------------------------------------------------------
*/

function request_has(string $key): bool
{
    $value = request_input(
        $key,
        null
    );

    return $value !== null;
}


/*
|--------------------------------------------------------------------------
| Get Header
|--------------------------------------------------------------------------
*/

function request_header(
    string $name,
    mixed $default = null
): mixed {

    $server_key = 'HTTP_' . strtoupper(
        str_replace(
            '-',
            '_',
            $name
        )
    );

    /*
    | Content-Type and Content-Length
    | are handled differently by PHP.
    */

    if (strtolower($name) === 'content-type') {
        $server_key = 'CONTENT_TYPE';
    }

    if (strtolower($name) === 'content-length') {
        $server_key = 'CONTENT_LENGTH';
    }

    return $_SERVER[$server_key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Get Authorization Header
|--------------------------------------------------------------------------
*/

function request_authorization(): ?string
{
    $authorization = request_header(
        'Authorization'
    );

    if (
        !is_string($authorization) ||
        trim($authorization) === ''
    ) {
        return null;
    }

    return $authorization;
}


/*
|--------------------------------------------------------------------------
| Get Bearer Token
|--------------------------------------------------------------------------
*/

function request_bearer_token(): ?string
{
    $authorization = request_authorization();

    if ($authorization === null) {
        return null;
    }

    if (
        !preg_match(
            '/^Bearer\s+(.+)$/i',
            trim($authorization),
            $matches
        )
    ) {
        return null;
    }

    return trim($matches[1]);
}


/*
|--------------------------------------------------------------------------
| Get Request Cookie
|--------------------------------------------------------------------------
*/

function request_cookie(
    ?string $key = null,
    mixed $default = null
): mixed {
    if ($key === null) {
        return $_COOKIE;
    }

    return $_COOKIE[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Get Uploaded File
|--------------------------------------------------------------------------
*/

function request_file(
    string $key
): ?array {

    if (!isset($_FILES[$key])) {
        return null;
    }

    return $_FILES[$key];
}


/*
|--------------------------------------------------------------------------
| Get All Uploaded Files
|--------------------------------------------------------------------------
*/

function request_files(): array
{
    return $_FILES;
}


/*
|--------------------------------------------------------------------------
| Check Uploaded File
|--------------------------------------------------------------------------
*/

function request_has_file(string $key): bool
{
    return isset($_FILES[$key])
        && is_array($_FILES[$key])
        && (
            ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_NO_FILE
        );
}


/*
|--------------------------------------------------------------------------
| Get IP Address
|--------------------------------------------------------------------------
*/

function request_ip(): ?string
{
    return $_SERVER['REMOTE_ADDR'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Get User Agent
|--------------------------------------------------------------------------
*/

function request_user_agent(): ?string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Get Content Type
|--------------------------------------------------------------------------
*/

function request_content_type(): ?string
{
    return request_header(
        'Content-Type'
    );
}


/*
|--------------------------------------------------------------------------
| Check JSON Request
|--------------------------------------------------------------------------
*/

function request_is_json(): bool
{
    $content_type = request_content_type();

    if (!is_string($content_type)) {
        return false;
    }

    return str_contains(
        strtolower($content_type),
        'application/json'
    );
}


/*
|--------------------------------------------------------------------------
| Get Route Parameter
|--------------------------------------------------------------------------
|
| Route parameters will be populated by the router later.
|
*/

function request_route(
    ?string $key = null,
    mixed $default = null
): mixed {

    $route_parameters = $GLOBALS['route_parameters'] ?? [];

    if ($key === null) {
        return $route_parameters;
    }

    return $route_parameters[$key] ?? $default;
}


/*
|--------------------------------------------------------------------------
| Get Raw Request Body
|--------------------------------------------------------------------------
*/

function request_raw_body(): string
{
    $body = file_get_contents('php://input');

    return $body !== false
        ? $body
        : '';
}