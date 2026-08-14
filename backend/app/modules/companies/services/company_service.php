<?php

/**
 * MASAR - Company Service
 *
 * Responsible for company business logic.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No direct SQL.
 * - Database operations must go through repositories.
 * - HTTP handling must stay inside controllers.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/company_repository.php';
require_once __DIR__ . '/../validators/company_validator.php';


/*
|--------------------------------------------------------------------------
| Get Company By ID
|--------------------------------------------------------------------------
*/

function company_service_get_by_id(
    int $company_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate ID
    |--------------------------------------------------------------------------
    */

    if ($company_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid company ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            $company_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'status' => 200,

        'data' => $company,

    ];
}


/*
|--------------------------------------------------------------------------
| Get Company By User ID
|--------------------------------------------------------------------------
*/

function company_service_get_by_user_id(
    int $user_id
): array {

    if ($user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_user_id(
            $user_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company profile not found.',

        ];
    }


    return [

        'success' => true,

        'status' => 200,

        'data' => $company,

    ];
}


/*
|--------------------------------------------------------------------------
| Create Company
|--------------------------------------------------------------------------
*/

function company_service_create(
    int $user_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate User
    |--------------------------------------------------------------------------
    */

    if ($user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Profile
    |--------------------------------------------------------------------------
    */

    $exists =
        company_repository_user_has_profile(
            $user_id
        );


    if ($exists) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Company profile already exists.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Data
    |--------------------------------------------------------------------------
    */

    $validation =
        company_validator_create(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'Validation failed.',

            'errors' =>
                $validation['errors'],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Data
    |--------------------------------------------------------------------------
    |
    | New companies must always start as pending.
    |
    */

    $company_data = [

        'user_id' =>
            $user_id,

        'company_name' =>
            trim(
                $data['company_name']
            ),

        'description' =>
            isset(
                $data['description']
            )
                ? trim(
                    $data['description']
                )
                : null,

        'industry' =>
            trim(
                $data['industry']
            ),

        'approval_status' =>
            'pending',

    ];


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    $company_id =
        company_repository_create(
            $company_data
        );


    if (
        $company_id === false
    ) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to create company profile.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Created Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            (int) $company_id
        );


    return [

        'success' => true,

        'status' => 201,

        'message' =>
            'Company profile created successfully.',

        'data' =>
            $company,

    ];
}


/*
|--------------------------------------------------------------------------
| Update Company By User ID
|--------------------------------------------------------------------------
*/

function company_service_update_by_user_id(
    int $user_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate User ID
    |--------------------------------------------------------------------------
    */

    if ($user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_user_id(
            $user_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company profile not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Update
    |--------------------------------------------------------------------------
    */

    $validation =
        company_validator_update(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'Validation failed.',

            'errors' =>
                $validation['errors'],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Allowed Data
    |--------------------------------------------------------------------------
    */

    $update_data = [];


    if (
        array_key_exists(
            'company_name',
            $data
        )
    ) {

        $update_data['company_name'] =
            trim(
                $data['company_name']
            );
    }


    if (
        array_key_exists(
            'description',
            $data
        )
    ) {

        $update_data['description'] =
            trim(
                $data['description']
            );
    }


    if (
        array_key_exists(
            'industry',
            $data
        )
    ) {

        $update_data['industry'] =
            trim(
                $data['industry']
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Nothing To Update
    |--------------------------------------------------------------------------
    */

    if (
        empty($update_data)
    ) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'No valid fields were provided for update.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $updated =
        company_repository_update(
            (int) $company['id'],
            $update_data
        );


    if (!$updated) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to update company profile.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated Company
    |--------------------------------------------------------------------------
    */

    $updated_company =
        company_repository_find_by_id(
            (int) $company['id']
        );


    return [

        'success' => true,

        'status' => 200,

        'message' =>
            'Company profile updated successfully.',

        'data' =>
            $updated_company,

    ];
}


/*
|--------------------------------------------------------------------------
| Get Approved Companies
|--------------------------------------------------------------------------
*/

function company_service_get_list(
    int $page = 1,
    int $limit = 20
): array {

    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

    if ($page < 1) {
        $page = 1;
    }


    if ($limit < 1) {
        $limit = 20;
    }


    if ($limit > 100) {
        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Get Companies
    |--------------------------------------------------------------------------
    */

    $companies =
        company_repository_get_approved(
            $limit,
            $offset
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        company_repository_count_by_status(
            'approved'
        );


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'status' => 200,

        'data' => [

            'companies' =>
                $companies,

            'pagination' => [

                'page' =>
                    $page,

                'limit' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

            ],

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Search Companies
|--------------------------------------------------------------------------
*/

function company_service_search(
    string $query,
    int $page = 1,
    int $limit = 20
): array {

    /*
    |--------------------------------------------------------------------------
    | Clean Query
    |--------------------------------------------------------------------------
    */

    $query =
        trim($query);


    if ($query === '') {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'Search query cannot be empty.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

    if ($page < 1) {
        $page = 1;
    }


    if ($limit < 1) {
        $limit = 20;
    }


    if ($limit > 100) {
        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Search By Name
    |--------------------------------------------------------------------------
    */

    $companies =
        company_repository_search_by_name(
            $query,
            $limit,
            $offset
        );


    /*
    |--------------------------------------------------------------------------
    | If No Results By Name
    |--------------------------------------------------------------------------
    |
    | Search industry as a fallback.
    |
    */

    if (
        empty($companies)
    ) {

        $companies =
            company_repository_search_by_industry(
                $query,
                $limit,
                $offset
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'status' => 200,

        'data' => [

            'query' =>
                $query,

            'companies' =>
                $companies,

            'pagination' => [

                'page' =>
                    $page,

                'limit' =>
                    $limit,

            ],

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Delete Company By User ID
|--------------------------------------------------------------------------
*/

function company_service_delete_by_user_id(
    int $user_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate User
    |--------------------------------------------------------------------------
    */

    if ($user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_user_id(
            $user_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company profile not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Company
    |--------------------------------------------------------------------------
    */

    $deleted =
        company_repository_delete(
            (int) $company['id']
        );


    if (!$deleted) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to delete company profile.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'status' => 200,

        'message' =>
            'Company profile deleted successfully.',

    ];
}
