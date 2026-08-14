<?php

require_once __DIR__ . '/../http/request.php';
require_once __DIR__ . '/../http/response.php';

function csrf_cookie_name(): string
{
    return getenv('CSRF_COOKIE_NAME') ?: 'csrf_token';
}

function csrf_header_name(): string
{
    return getenv('CSRF_HEADER_NAME') ?: 'X-CSRF-Token';
}

function csrf_generate_token(): string
{
    return bin2hex(random_bytes(32));
}

function csrf_set_cookie(string $token): void
{
    response_set_cookie(
        csrf_cookie_name(),
        $token,
        time() + 60 * 60 * 12,
        '/',
        '',
        (bool) filter_var(getenv('SECURE_COOKIES'), FILTER_VALIDATE_BOOLEAN),
        false,
        'Lax'
    );
}

function csrf_validate_request(): bool
{
    $cookie_token = trim((string) (request_cookie(csrf_cookie_name()) ?? ''));
    $header_token = trim((string) (request_header(csrf_header_name()) ?? ''));

    if ($cookie_token === '' || $header_token === '') {
        return false;
    }

    return hash_equals($cookie_token, $header_token);
}

function csrf_require(): void
{
    if (!csrf_validate_request()) {
        response_error('CSRF token validation failed.', 403, [
            'csrf_token' => ['Invalid or missing CSRF token.'],
        ]);
    }
}
