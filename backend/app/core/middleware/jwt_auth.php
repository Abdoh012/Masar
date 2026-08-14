<?php

require_once __DIR__ . '/../../services/jwt_service.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../http/response.php';

function middleware_jwt_auth(): array
{
    $token = jwt_current_bearer_token();

    if (!is_string($token) || $token === '') {
        response_unauthorized('Authentication required.');
    }

    $payload = jwt_validate_access_token($token);

    if ($payload === false) {
        response_unauthorized('Invalid or expired access token.');
    }

    $user_id = (int) ($payload['sub'] ?? 0);
    $user = auth_find_user_by_id($user_id);

    if ($user === null) {
        response_unauthorized('User not found.');
    }

    if (!user_status_allows_login($user['status'] ?? null)) {
        response_forbidden('Your account is not active.');
    }

    auth_set_user($user);

    return $user;
}

function middleware_require_jwt_auth(): array
{
    return middleware_jwt_auth();
}
