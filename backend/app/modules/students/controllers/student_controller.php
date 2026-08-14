<?php

/**
 * MASAR - Student Controller
 *
 * Handles student-related HTTP requests.
 *
 * Responsibilities:
 * - Get authenticated student's profile.
 * - Create student profile.
 * - Update student profile.
 * - View student profiles.
 *
 * Business logic belongs to student_service.php.
 * Database operations belong to student_repository.php.
 * Validation belongs to student_validator.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';

require_once __DIR__ . '/../services/student_service.php';
require_once __DIR__ . '/../services/student_profile_service.php';

require_once __DIR__ . '/../validators/student_validator.php';


/*
|--------------------------------------------------------------------------
| Get My Student Profile
|--------------------------------------------------------------------------
|
| GET /api/students/me
|
*/

function student_me(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Check
    |--------------------------------------------------------------------------
    */

    if (
        ($user['role'] ?? '')
        !== 'student'
    ) {

        response_forbidden(
            'Only students can access this resource.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Profile
    |--------------------------------------------------------------------------
    */

    $result =
        student_get_profile(
            (int) $user['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to retrieve student profile.',
            $result['status']
                ?? 404
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? $result
    );
}


/*
|--------------------------------------------------------------------------
| Create Student Profile
|--------------------------------------------------------------------------
|
| POST /api/students/profile
|
*/

function student_create_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Check
    |--------------------------------------------------------------------------
    */

    if (
        ($user['role'] ?? '')
        !== 'student'
    ) {

        response_forbidden(
            'Only students can create a student profile.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_input();


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $errors =
        student_validate_profile(
            $data
        );


    if (!empty($errors)) {

        response_validation_error(
            $errors
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Profile
    |--------------------------------------------------------------------------
    */

    $result =
        student_service_create_profile(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to create student profile.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_created(
        $result['data']
            ?? $result
    );
}


/*
|--------------------------------------------------------------------------
| Update Student Profile
|--------------------------------------------------------------------------
|
| PUT /api/students/profile
|
*/

function student_update_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Check
    |--------------------------------------------------------------------------
    */

    if (
        ($user['role'] ?? '')
        !== 'student'
    ) {

        response_forbidden(
            'Only students can update a student profile.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_input();


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $errors =
        student_validate_profile_update(
            $data
        );


    if (!empty($errors)) {

        response_validation_error(
            $errors
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */

    $result =
        student_service_update_profile(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to update student profile.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? $result
    );
}


/*
|--------------------------------------------------------------------------
| Get Student Profile By ID
|--------------------------------------------------------------------------
|
| GET /api/students/{id}
|
| Used by:
| - Companies
| - Admin
| - Other authorized users
|
*/

function student_show(
    int $student_id
): void {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate ID
    |--------------------------------------------------------------------------
    */

    if ($student_id <= 0) {

        response_error(
            'Invalid student ID.',
            422
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Profile
    |--------------------------------------------------------------------------
    */

    $result =
        student_get_public_profile(
            $student_id,
            $user
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to retrieve student profile.',
            $result['status']
                ?? 404
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? $result
    );
}


/*
|--------------------------------------------------------------------------
| Complete Student Profile
|--------------------------------------------------------------------------
|
| POST /api/students/profile/complete
|
| This endpoint is useful for the first-time
| profile setup flow.
|
*/

function student_complete_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Check
    |--------------------------------------------------------------------------
    */

    if (
        ($user['role'] ?? '')
        !== 'student'
    ) {

        response_forbidden(
            'Only students can complete a student profile.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_input();


    /*
    |--------------------------------------------------------------------------
    | Validate Complete Profile
    |--------------------------------------------------------------------------
    */

    $errors =
        student_validate_complete_profile(
            $data
        );


    if (!empty($errors)) {

        response_validation_error(
            $errors
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Profile
    |--------------------------------------------------------------------------
    */

    $result =
        student_complete_profile_data(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to complete student profile.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? $result
    );
}


/*
|--------------------------------------------------------------------------
| Student Profile Status
|--------------------------------------------------------------------------
|
| GET /api/students/profile/status
|
| Returns whether the student has completed
| all required profile information.
|
*/

function student_profile_status(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Role Check
    |--------------------------------------------------------------------------
    */

    if (
        ($user['role'] ?? '')
        !== 'student'
    ) {

        response_forbidden(
            'Only students can access this resource.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Status
    |--------------------------------------------------------------------------
    */

    $result =
        student_get_profile_status(
            (int) $user['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        isset($result['error'])
        &&
        $result['error'] === true
    ) {

        response_error(
            $result['message']
                ?? 'Unable to retrieve profile status.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? $result
    );
}
