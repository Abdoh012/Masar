<?php

/**
 * MASAR - Training Session Controller
 *
 * Handles HTTP requests related to training sessions.
 *
 * Native PHP - No OOP.
 *
 * Responsibilities:
 * - Receive request data.
 * - Check authentication and role.
 * - Validate basic request parameters.
 * - Call training session service.
 * - Return API responses.
 *
 * Business logic belongs to training_session_service.php.
 * Database logic belongs to training_session_repository.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../services/training_session_service.php';


/*
|--------------------------------------------------------------------------
| Create Training Session
|--------------------------------------------------------------------------
|
| Creates a session for an accepted training application.
|
*/

function training_session_controller_create(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can create training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Create Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_create(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to create training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        null,

        $result['message']
            ??
        'Training session created successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Training Session
|--------------------------------------------------------------------------
*/

function training_session_controller_show(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_find(
            $session_id,
            (int) $user['id'],
            $user['role'] ?? null
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Training session not found.',

            $result['status_code']
                ??
            404,

            $result['errors']
                ??
            []
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
            ??
        null
    );
}


/*
|--------------------------------------------------------------------------
| Get Training Sessions
|--------------------------------------------------------------------------
|
| Returns sessions belonging to a training opportunity.
|
*/

function training_session_controller_index(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'training_id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'training_id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        request_get_int(
            'page',
            1
        );

    $limit =
        request_get_int(
            'limit',
            20
        );


    /*
    |--------------------------------------------------------------------------
    | Get Sessions
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_list(
            $training_id,
            (int) $user['id'],
            $user['role'] ?? null,
            $page,
            $limit
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve training sessions.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        []
    );
}


/*
|--------------------------------------------------------------------------
| Update Training Session
|--------------------------------------------------------------------------
*/

function training_session_controller_update(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method =
        request_method();


    if (
        $method !== 'PUT'
        &&
        $method !== 'PATCH'
    ) {

        response_method_not_allowed(
            'Only PUT or PATCH method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can update training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Update Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_update(
            (int) $user['id'],
            $session_id,
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to update training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        null,

        $result['message']
            ??
        'Training session updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Start Training Session
|--------------------------------------------------------------------------
|
| Marks the session as started.
|
*/

function training_session_controller_start(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can start training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Start Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_start(
            (int) $user['id'],
            $session_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to start training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        null,

        $result['message']
            ??
        'Training session started successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Complete Training Session
|--------------------------------------------------------------------------
|
| Marks the current training session as completed.
|
*/

function training_session_controller_complete(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can complete training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_complete(
            (int) $user['id'],
            $session_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to complete training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        null,

        $result['message']
            ??
        'Training session completed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Cancel Training Session
|--------------------------------------------------------------------------
*/

function training_session_controller_cancel(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can cancel training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Cancel Session
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_cancel(
            (int) $user['id'],
            $session_id,
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to cancel training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
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
            ??
        null,

        $result['message']
            ??
        'Training session cancelled successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete Training Session
|--------------------------------------------------------------------------
|
| Deletes a session that has not started yet.
|
*/

function training_session_controller_delete(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'DELETE'
    ) {

        response_method_not_allowed(
            'Only DELETE method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();

    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can delete training sessions.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Session ID
    |--------------------------------------------------------------------------
    */

    $session_id =
        request_get_int(
            'id'
        );


    if (
        $session_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training session ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_service_delete(
            (int) $user['id'],
            $session_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to delete training session.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        null,

        $result['message']
            ??
        'Training session deleted successfully.'
    );
}
