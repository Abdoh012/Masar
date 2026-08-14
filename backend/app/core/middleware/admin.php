<?php

/**
 * MASAR Admin Middleware
 *
 * Protects endpoints that are accessible only
 * to authenticated administrators.
 */


/*
|--------------------------------------------------------------------------
| Load Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../http/response.php';
require_once __DIR__ . '/../../shared/functions/authorization.php';


/*
|--------------------------------------------------------------------------
| Require Admin
|--------------------------------------------------------------------------
|
| The user must:
|
| 1. Be authenticated
| 2. Have the admin role
|
*/

function middleware_admin(): array
{
    /*
    |--------------------------------------------------------------------------
    | Authenticate User
    |--------------------------------------------------------------------------
    */

    $user = middleware_auth();


    /*
    |--------------------------------------------------------------------------
    | Check Admin Role
    |--------------------------------------------------------------------------
    */

    if (!is_admin_role($user['role'] ?? null)) {

        response_forbidden(
            'Admin access required.'
        );
    }

    if (!auth_user_has_permission($user, 'admin:read')) {
        response_forbidden('You do not have permission to access this admin resource.');
    }

    return $user;
}


/*
|--------------------------------------------------------------------------
| Alias
|--------------------------------------------------------------------------
*/

function middleware_require_admin(): array
{
    return middleware_admin();
}