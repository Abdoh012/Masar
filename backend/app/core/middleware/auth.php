<?php

/**
 * MASAR Authentication Middleware
 *
 * Responsible for protecting API endpoints that require
 * an authenticated user.
 */


/*
|--------------------------------------------------------------------------
| Load Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/token.php';
require_once __DIR__ . '/../http/response.php';
require_once __DIR__ . '/../../shared/functions/authorization.php';


/*
|--------------------------------------------------------------------------
| Authenticate Request
|--------------------------------------------------------------------------
|
| Validates the Bearer Token sent with the request.
|
*/

function middleware_auth(): array
{
    /*
    |--------------------------------------------------------------------------
    | Check Authentication Token
    |--------------------------------------------------------------------------
    */

    if (!token_authenticate_request()) {

        response_unauthorized(
            'Authentication required.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Authenticated User
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if ($user === null) {

        response_unauthorized(
            'Unable to authenticate user.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Active Account
    |--------------------------------------------------------------------------
    */

    if (!auth_user_is_active($user)) {

        response_forbidden(
            'Your account is not active.'
        );
    }


    return $user;
}


/*
|--------------------------------------------------------------------------
| Require Authentication
|--------------------------------------------------------------------------
|
| Alias for middleware_auth().
|
*/

function middleware_require_auth(): array
{
    return middleware_auth();
}