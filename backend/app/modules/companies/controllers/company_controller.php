<?php

/**
 * MASAR - Company Controller
 *
 * Responsible for handling company-related HTTP requests.
 *
 * Responsibilities:
 * - Receive request data.
 * - Identify the authenticated user.
 * - Call the appropriate service.
 * - Return HTTP responses.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No SQL queries.
 * - No direct database operations.
 * - No business logic.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';
require_once __DIR__ . '/../../../core/auth/auth.php';
require_once __DIR__ . '/../../../shared/functions/authorization.php';

require_once __DIR__ . '/../services/company_service.php';


/*
|--------------------------------------------------------------------------
| Get Current Company Profile
|--------------------------------------------------------------------------
|
| GET /companies/me
|
| Returns the company profile belonging to the
| currently authenticated user.
|
*/

function company_controller_get_my_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Get Authenticated User
    |--------------------------------------------------------------------------
    */

    $user =
        request_authenticated_user();


    if (
        empty($user)
        ||
        empty($user['id'])
    ) {

        response_error(
            'Authentication required.',
            401
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_get_by_user_id(
            (int) $user['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to retrieve company profile.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? null,
        'Company profile retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Company By ID
|--------------------------------------------------------------------------
|
| GET /companies/{id}
|
| Returns a company profile.
|
*/

function company_controller_get_by_id(
    int $company_id
): void {

    /*
    |--------------------------------------------------------------------------
    | Validate ID
    |--------------------------------------------------------------------------
    */

    if (
        $company_id <= 0
    ) {

        response_error(
            'Invalid company ID.',
            400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_get_by_id(
            $company_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    $current_user = auth_user();
    if (!$current_user) {
        response_unauthorized('Authentication required.');
    }

    if (!auth_user_has_role($current_user, ROLE_ADMIN) && !auth_user_can_access_resource($current_user, (int) ($result['data']['user_id'] ?? 0), 'company')) {
        response_forbidden('You do not have permission to access this company profile.');
    }

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Company not found.',
            $result['status']
                ?? 404
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? null,
        'Company retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Create Company Profile
|--------------------------------------------------------------------------
|
| POST /companies
|
| Creates a company profile for the authenticated user.
|
*/

function company_controller_create(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user =
        request_authenticated_user();


    if (
        empty($user)
        ||
        empty($user['id'])
    ) {

        response_error(
            'Authentication required.',
            401
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


    if (
        !is_array($data)
    ) {

        response_error(
            'Invalid request data.',
            400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_create(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to create company profile.',
            $result['status']
                ?? 400,
            $result['errors']
                ?? []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? null,
        'Company profile created successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Update My Company Profile
|--------------------------------------------------------------------------
|
| PUT /companies/me
|
*/

function company_controller_update_my_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user =
        request_authenticated_user();


    if (
        empty($user)
        ||
        empty($user['id'])
    ) {

        response_error(
            'Authentication required.',
            401
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


    if (
        !is_array($data)
    ) {

        response_error(
            'Invalid request data.',
            400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_update_by_user_id(
            (int) $user['id'],
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to update company profile.',
            $result['status']
                ?? 400,
            $result['errors']
                ?? []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? null,
        'Company profile updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Company List
|--------------------------------------------------------------------------
|
| GET /companies
|
| Returns approved companies with pagination.
|
*/

function company_controller_get_list(): void
{
    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    $page =
        request_query_int(
            'page',
            1
        );


    $limit =
        request_query_int(
            'limit',
            20
        );


    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_get_list(
            $page,
            $limit
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to retrieve companies.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? [],
        'Companies retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search Companies
|--------------------------------------------------------------------------
|
| GET /companies/search
|
| Search by company name or industry.
|
*/

function company_controller_search(): void
{
    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    $query =
        request_query(
            'q'
        );


    $page =
        request_query_int(
            'page',
            1
        );


    $limit =
        request_query_int(
            'limit',
            20
        );


    /*
    |--------------------------------------------------------------------------
    | Validate Search Query
    |--------------------------------------------------------------------------
    */

    if (
        !is_string($query)
        ||
        trim($query) === ''
    ) {

        response_error(
            'Search query is required.',
            400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination Limits
    |--------------------------------------------------------------------------
    */

    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_search(
            trim($query),
            $page,
            $limit
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to search companies.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ?? [],
        'Companies found successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete My Company Account
|--------------------------------------------------------------------------
|
| DELETE /companies/me
|
| This operation is delegated to the service because
| company deletion has business consequences.
|
*/

function company_controller_delete_my_profile(): void
{
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user =
        request_authenticated_user();


    if (
        empty($user)
        ||
        empty($user['id'])
    ) {

        response_error(
            'Authentication required.',
            401
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Call Service
    |--------------------------------------------------------------------------
    */

    $result =
        company_service_delete_by_user_id(
            (int) $user['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Result
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ?? 'Unable to delete company account.',
            $result['status']
                ?? 400
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    response_success(
        null,
        'Company account deleted successfully.'
    );
}
