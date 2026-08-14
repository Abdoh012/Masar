<?php

/**
 * MASAR - Training Controller
 *
 * Handles HTTP requests related to training listings.
 *
 * Native PHP - No OOP.
 *
 * Responsibilities:
 * - Receive request data.
 * - Check request method.
 * - Get authenticated user.
 * - Call training service.
 * - Return API response.
 *
 * Business logic belongs to training_service.php.
 * Database logic belongs to training_repository.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../services/training_service.php';


/*
|--------------------------------------------------------------------------
| Create Training
|--------------------------------------------------------------------------
|
| Company creates a new training opportunity.
|
*/

function training_controller_create(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticated User
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
    | Check User Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can create training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data = request_json();


    /*
    |--------------------------------------------------------------------------
    | Create Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_create(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to create training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_created(
        $result['data'],
        $result['message'] ?? 'Training opportunity created successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Training By ID
|--------------------------------------------------------------------------
*/

function training_controller_show(
    int $training_id = 0
): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'GET') {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    if ($training_id <= 0) {

        $training_id =
            request_get_int(
                'id'
            );
    }


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_find(
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Training opportunity not found.',
            $result['status_code'] ?? 404,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data']
    );
}


/*
|--------------------------------------------------------------------------
| Get Training List
|--------------------------------------------------------------------------
|
| Public listing of available training opportunities.
|
*/

function training_controller_index(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'GET') {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'specialization' =>
            request_get(
                'specialization'
            ),

        'training_type' =>
            request_get(
                'training_type'
            ),

        'work_mode' =>
            request_get(
                'work_mode'
            ),

        'paid' =>
            request_get(
                'paid'
            ),

        'employment_possible' =>
            request_get(
                'employment_possible'
            ),

        'company_id' =>
            request_get_int(
                'company_id'
            ),

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Training List
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_list(
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to retrieve training opportunities.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data']
    );
}


/*
|--------------------------------------------------------------------------
| Update Training
|--------------------------------------------------------------------------
|
| Company can update its own training opportunity.
|
*/

function training_controller_update(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'PUT' && $method !== 'PATCH') {

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
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can update training opportunities.'
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
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data = request_json();


    /*
    |--------------------------------------------------------------------------
    | Update Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_update(
            (int) $user['id'],
            $training_id,
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to update training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'],
        $result['message'] ?? 'Training opportunity updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Publish Training
|--------------------------------------------------------------------------
|
| Moves a training opportunity from draft to published.
|
*/

function training_controller_publish(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

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
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can publish training opportunities.'
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
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_publish(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to publish training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity published successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Close Training
|--------------------------------------------------------------------------
|
| Company manually closes a training opportunity.
|
| Pending applications will be handled by
| training_closing_service.php.
|
*/

function training_controller_close(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'POST') {

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
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can close training opportunities.'
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
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Close Training
    |--------------------------------------------------------------------------
    */

    $result =
        training_service_close(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to close training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        $result['data'] ?? null,
        $result['message'] ?? 'Training opportunity closed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete Training
|--------------------------------------------------------------------------
|
| Deletes a draft training opportunity.
|
*/

function training_controller_delete(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    $method = request_method();

    if ($method !== 'DELETE') {

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
    | Role
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can delete training opportunities.'
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
            'id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [
                'id' =>
                    'A valid training ID is required.'
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
        training_service_delete(
            (int) $user['id'],
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message'] ?? 'Unable to delete training opportunity.',
            $result['status_code'] ?? 400,
            $result['errors'] ?? []
        );

        return;
    }


    response_success(
        null,
        $result['message'] ?? 'Training opportunity deleted successfully.'
    );
}
