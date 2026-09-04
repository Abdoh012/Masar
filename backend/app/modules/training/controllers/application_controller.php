<?php

/**
 * MASAR - Application Controller
 *
 * Handles HTTP requests related to training applications.
 *
 * Native PHP - No OOP.
 *
 * Responsibilities:
 * - Receive request data.
 * - Check authentication and role.
 * - Call application service.
 * - Return API responses.
 *
 * Business logic belongs to application_service.php.
 * Database logic belongs to application_repository.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../services/application_service.php';


/*
|--------------------------------------------------------------------------
| Create Application
|--------------------------------------------------------------------------
|
| Student applies for a training opportunity.
|
*/

function application_controller_create(): void
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
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can apply for training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    |
    | The Create Application request is raw JSON (application/json). The CV
    | is not part of this request: it is uploaded separately through the
    | existing file upload endpoint and only its id (cv_file_id) is sent.
    |
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Create Application
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_create(
            (int) $user['id'],
            (int) (
                $data['training_id']
                ?? 0
            ),
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
            'Unable to submit application.',

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
        'Application submitted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Application By ID
|--------------------------------------------------------------------------
*/

function application_controller_show( int $application_id = 0 ): void
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
    | Application ID
    |--------------------------------------------------------------------------
    */

    if ($application_id <= 0) {

        $application_id =
            request_get_int(
                'id'
            );
    }


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Application
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_find(
            (int) $user['id'],
            $application_id,
            strtolower((string) ($user['role'] ?? ''))
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
            'Application not found.',

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
| Get My Applications
|--------------------------------------------------------------------------
|
| Returns applications submitted by the authenticated student.
|
*/

function application_controller_my_applications(): void
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
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'status' =>
            request_get(
                'status'
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
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_student(
            (int) $user['id'],
            $filters
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
            'Unable to retrieve applications.',

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
| Get Company Applications
|--------------------------------------------------------------------------
|
| Returns applications received for a company's
| training opportunity.
|
*/

function application_controller_company_applications(): void
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
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can access received applications.'
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
    | Filters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'status' =>
            request_get(
                'status'
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
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_company(
            (int) $user['id'],
            $training_id,
            $filters
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
            'Unable to retrieve applications.',

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
| Withdraw Application
|--------------------------------------------------------------------------
|
| Student can withdraw the application while it is pending.
|
*/

function application_controller_withdraw(): void
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
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can withdraw applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Withdraw
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_withdraw(
            (int) $user['id'],
            $application_id
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
            'Unable to withdraw application.',

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
        'Application withdrawn successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Accept Application
|--------------------------------------------------------------------------
|
| Company accepts a student application.
|
*/

function application_controller_accept(): void
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
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can accept applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Accept
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_accept(
            (int) $user['id'],
            $application_id
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
            'Unable to accept application.',

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
        'Application accepted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Reject Application
|--------------------------------------------------------------------------
|
| Company rejects a student application.
|
| Rejection reason is required by MASAR business rules.
|
*/

function application_controller_reject(): void
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
            'Only companies can reject applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

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
    | Reject
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_reject(
            (int) $user['id'],
            $application_id,
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
            'Unable to reject application.',

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
        'Application rejected successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Download Application CV
|--------------------------------------------------------------------------
|
| Authorized by the application service through Application → Training →
| Company ownership (or the owning student / an administrator). The route
| streams the physical file returned here.
|
*/

function application_controller_cv( int $application_id = 0 ): array
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

        return [];
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

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    if ($application_id <= 0) {

        $application_id =
            request_get_int(
                'id'
            );
    }


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve CV
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_cv(
            (int) $user['id'],
            $application_id,
            strtolower((string) ($user['role'] ?? ''))
        );


    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to download CV.',

            $result['status_code']
                ??
            404,

            $result['errors']
                ??
            []
        );

        return [];
    }

    return $result;
}
