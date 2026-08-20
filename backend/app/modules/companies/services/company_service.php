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


    $company['work_fields'] =
        company_repository_get_work_fields(
            (int) $company['id']
        );


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


    $company['work_fields'] =
        company_repository_get_work_fields(
            (int) $company['id']
        );


    return [

        'success' => true,

        'status' => 200,

        'data' => $company,

    ];
}


/*
|--------------------------------------------------------------------------
| Resolve Work Field IDs
|--------------------------------------------------------------------------
|
| Collects work field inputs (study field IDs from `work_field_ids` and/or
| the legacy `industry` name) and resolves them against the study_fields
| lookup table. Returns null when any input does not match an active
| study field. study_fields is the single source of truth for work fields.
|
*/

function company_service_resolve_work_field_ids(
    array $data
): ?array {

    $inputs = [];

    if (
        isset($data['work_field_ids'])
        &&
        is_array($data['work_field_ids'])
    ) {

        $inputs =
            array_merge(
                $inputs,
                $data['work_field_ids']
            );
    }


    if (
        isset($data['industry'])
        &&
        trim((string) $data['industry']) !== ''
    ) {

        $inputs[] =
            trim(
                (string) $data['industry']
            );
    }


    return company_repository_resolve_work_field_ids(
        $inputs
    );
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

        'approval_status' =>
            'pending',

    ];


    /*
    |--------------------------------------------------------------------------
    | Resolve Work Fields
    |--------------------------------------------------------------------------
    |
    | Work fields are referenced from the study_fields lookup table and must
    | match an active study field. The endpoint accepts study field IDs
    | (work_field_ids) or the legacy industry name, resolved against
    | study_fields.
    |
    */

    $work_field_ids =
        company_service_resolve_work_field_ids(
            $data
        );


    if ($work_field_ids === null) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'One or more work fields are not recognized.',

            'errors' => [

                'work_field_ids' =>
                    'One or more work fields are not recognized.',

            ],

        ];
    }


    if (empty($work_field_ids)) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'At least one work field is required.',

            'errors' => [

                'work_field_ids' =>
                    'At least one work field is required.',

            ],

        ];
    }


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
    | Attach Work Fields
    |--------------------------------------------------------------------------
    */

    $work_fields_replaced =
        company_repository_replace_work_fields(
            (int) $company_id,
            $work_field_ids
        );


    if (!$work_fields_replaced) {

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


    $company['work_fields'] =
        company_repository_get_work_fields(
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


    /*
    |--------------------------------------------------------------------------
    | Resolve Work Fields
    |--------------------------------------------------------------------------
    |
    | When work_field_ids (study field IDs) or the legacy industry name are
    | provided they are resolved against study_fields. An empty array clears
    | the company's work fields.
    |
    */

    $update_work_fields = false;
    $work_field_ids = [];

    if (
        array_key_exists(
            'work_field_ids',
            $data
        )
        ||
        array_key_exists(
            'industry',
            $data
        )
    ) {

        $resolved_work_field_ids =
            company_service_resolve_work_field_ids(
                $data
            );

        if ($resolved_work_field_ids === null) {

            return [

                'success' => false,

                'status' => 422,

                'message' =>
                    'One or more work fields are not recognized.',

                'errors' => [

                    'work_field_ids' =>
                        'One or more work fields are not recognized.',

                ],

            ];
        }

        $work_field_ids =
            $resolved_work_field_ids;

        $update_work_fields = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Nothing To Update
    |--------------------------------------------------------------------------
    */

    if (
        empty($update_data)
        &&
        !$update_work_fields
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

    if (!empty($update_data)) {

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
    }


    if ($update_work_fields) {

        $work_fields_replaced =
            company_repository_replace_work_fields(
                (int) $company['id'],
                $work_field_ids
            );


        if (!$work_fields_replaced) {

            return [

                'success' => false,

                'status' => 500,

                'message' =>
                    'Unable to update company work fields.',

            ];
        }
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


    $updated_company['work_fields'] =
        company_repository_get_work_fields(
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
