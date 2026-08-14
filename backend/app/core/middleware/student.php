<?php

/**
 * MASAR Student Middleware
 *
 * Protects endpoints that are accessible only
 * to authenticated students.
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
| Require Student
|--------------------------------------------------------------------------
|
| The user must:
|
| 1. Be authenticated
| 2. Have the student role
|
*/

function middleware_student(): array
{
    /*
    |--------------------------------------------------------------------------
    | Authenticate User
    |--------------------------------------------------------------------------
    */

    $user = middleware_auth();


    /*
    |--------------------------------------------------------------------------
    | Check Student Role
    |--------------------------------------------------------------------------
    */

    if (!is_student_role($user['role'] ?? null)) {

        response_forbidden(
            'Student access required.'
        );
    }

    if (!auth_user_has_permission($user, 'student:read')) {
        response_forbidden('You do not have permission to access this student resource.');
    }

    return $user;
}


/*
|--------------------------------------------------------------------------
| Alias
|--------------------------------------------------------------------------
*/

function middleware_require_student(): array
{
    return middleware_student();
}