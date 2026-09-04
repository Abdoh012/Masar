<?php

/**
 * MASAR Company Middleware
 *
 * Protects endpoints that are accessible only
 * to authenticated company users.
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
| Require Company
|--------------------------------------------------------------------------
|
| The user must:
|
| 1. Be authenticated
| 2. Have the company role
|
*/

function middleware_company(): array
{
    /*
    |--------------------------------------------------------------------------
    | Authenticate User
    |--------------------------------------------------------------------------
    */

    $user = middleware_auth();


    /*
    |--------------------------------------------------------------------------
    | Check Company Role
    |--------------------------------------------------------------------------
    */

    if (!is_company_role($user['role'] ?? null)) {

        response_forbidden(
            'Company access required.'
        );
    }

    if (!auth_user_has_permission($user, 'company:read')) {
        response_forbidden('You do not have permission to access this company resource.');
    }

    return $user;
}


/*
|--------------------------------------------------------------------------
| Alias
|--------------------------------------------------------------------------
*/

function middleware_require_company(): array
{
    return middleware_company();
}