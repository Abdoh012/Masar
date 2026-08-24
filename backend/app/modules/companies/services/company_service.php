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
require_once __DIR__ . '/../../files/services/file_upload_service.php';


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


    $company['specializations'] =
        company_repository_get_specializations(
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


    $company['specializations'] =
        company_repository_get_specializations(
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
| Collects work field inputs (study field IDs from `work_field_ids`) and
| resolves them against the study_fields lookup table. Returns null when
| any input does not match an active study field. study_fields is the
| single source of truth for work fields.
|
| NOTE: the legacy `industry` name is no longer treated as a work field.
| Industry now means a specialization and is resolved by
| company_service_resolve_specialization_ids() into
| company_specializations.
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


    return company_repository_resolve_work_field_ids(
        $inputs
    );
}


/*
|--------------------------------------------------------------------------
| Resolve Specialization IDs
|--------------------------------------------------------------------------
|
| Collects specialization inputs (`specialization_ids` and/or the
| `industry` name(s)) and resolves them against the specializations
| lookup table. Returns null when any input does not match an active
| specialization. Specializations represent the company industry
| (company_specializations) and are matched against the student's
| specialization during training matching.
|
| NOTE: specialization IDs and study field IDs are different concepts
| and are never converted between each other.
|
*/

function company_service_resolve_specialization_ids(
    array $data
): ?array {

    $inputs = [];

    if (
        isset($data['specialization_ids'])
        &&
        is_array($data['specialization_ids'])
    ) {

        $inputs =
            array_merge(
                $inputs,
                $data['specialization_ids']
            );
    }


    if (
        isset($data['industry'])
    ) {

        /*
         * Industry accepts a single name string or a list of names.
         * Each entry is resolved against the specializations lookup
         * table (the same list students choose from).
         */

        if (
            is_array($data['industry'])
        ) {

            foreach ($data['industry'] as $industry_name) {

                if (
                    is_string($industry_name)
                    &&
                    trim($industry_name) !== ''
                ) {

                    $inputs[] =
                        trim($industry_name);
                }
            }
        } elseif (
            is_string($data['industry'])
            &&
            trim($data['industry']) !== ''
        ) {

            $inputs[] =
                trim($data['industry']);
        }
    }


    return company_repository_resolve_specialization_ids(
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
    | Resolve Specializations
    |--------------------------------------------------------------------------
    |
    | Specializations are referenced from the specializations lookup table
    | and represent the company industry used for training matching. The
    | industry is supplied as specialization name(s) (`industry`) or IDs
    | (`specialization_ids`). When they are supplied, work fields become
    | optional as well.
    |
    */

    $has_specializations =
        (
            array_key_exists(
                'specialization_ids',
                $data
            )
        )
        ||
        (
            isset($data['industry'])
            &&
            (
                (is_string($data['industry']) && trim($data['industry']) !== '')
                || is_array($data['industry'])
            )
        );

    $specialization_ids = [];

    if ($has_specializations) {

        $resolved_specialization_ids =
            company_service_resolve_specialization_ids(
                $data
            );

        if ($resolved_specialization_ids === null) {

            return [

                'success' => false,

                'status' => 422,

                'message' =>
                    'One or more specializations are not recognized.',

                'errors' => [

                    'specialization_ids' =>
                        'One or more specializations are not recognized.',

                ],

            ];
        }

        $specialization_ids =
            $resolved_specialization_ids;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Work Fields
    |--------------------------------------------------------------------------
    |
    | Work fields are referenced from the study_fields lookup table and must
    | match an active study field. Only study field IDs (work_field_ids) are
    | accepted; the industry name is a specialization now. Optional and kept
    | for backward compatibility with existing clients.
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


    if (
        empty($work_field_ids)
        &&
        !$has_specializations
    ) {

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
    |
    | The company row, its work fields and its specializations are written
    | atomically. When called during registration this joins the outer
    | registration transaction, so a failure rolls back everything.
    |
    */

    try {

        $company_id =
            db_transaction(function () use (
                $company_data,
                $work_field_ids,
                $has_specializations,
                $specialization_ids
            ): int {

                $new_company_id =
                    company_repository_create(
                        $company_data
                    );


                if (
                    $new_company_id === false
                    ||
                    (int) $new_company_id <= 0
                ) {

                    throw new RuntimeException(
                        'Unable to create company profile.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Attach Work Fields
                |--------------------------------------------------------------------------
                */

                if (!empty($work_field_ids)) {

                    $work_fields_replaced =
                        company_repository_replace_work_fields(
                            (int) $new_company_id,
                            $work_field_ids
                        );


                    if (!$work_fields_replaced) {

                        throw new RuntimeException(
                            'Unable to create company profile.'
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Attach Specializations
                |--------------------------------------------------------------------------
                */

                if ($has_specializations) {

                    $specializations_replaced =
                        company_repository_replace_specializations(
                            (int) $new_company_id,
                            $specialization_ids
                        );


                    if (!$specializations_replaced) {

                        throw new RuntimeException(
                            'Unable to create company profile.'
                        );
                    }
                }


                return (int) $new_company_id;

            });

    } catch (Throwable $exception) {

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


    $company['specializations'] =
        company_repository_get_specializations(
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
    | When work_field_ids (study field IDs) are provided they are resolved
    | against study_fields. An empty array clears the company's work fields.
    | The legacy industry name is no longer a work field.
    |
    */

    $update_work_fields = false;
    $work_field_ids = [];

    if (
        array_key_exists(
            'work_field_ids',
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
    | Resolve Specializations
    |--------------------------------------------------------------------------
    |
    | When specialization_ids or the industry name(s) are provided they are
    | resolved against specializations. An empty array clears the company's
    | specializations.
    |
    */

    $update_specializations = false;
    $specialization_ids = [];

    if (
        array_key_exists(
            'specialization_ids',
            $data
        )
        ||
        array_key_exists(
            'industry',
            $data
        )
    ) {

        $resolved_specialization_ids =
            company_service_resolve_specialization_ids(
                $data
            );

        if ($resolved_specialization_ids === null) {

            return [

                'success' => false,

                'status' => 422,

                'message' =>
                    'One or more specializations are not recognized.',

                'errors' => [

                    'specialization_ids' =>
                        'One or more specializations are not recognized.',

                ],

            ];
        }

        $specialization_ids =
            $resolved_specialization_ids;

        $update_specializations = true;
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
        &&
        !$update_specializations
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


    if ($update_specializations) {

        $specializations_replaced =
            company_repository_replace_specializations(
                (int) $company['id'],
                $specialization_ids
            );


        if (!$specializations_replaced) {

            return [

                'success' => false,

                'status' => 500,

                'message' =>
                    'Unable to update company specializations.',

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


    $updated_company['specializations'] =
        company_repository_get_specializations(
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

/*
|--------------------------------------------------------------------------
| Update Company Logo
|--------------------------------------------------------------------------
|
| Handles the multipart logo upload for the AUTHENTICATED company.
|
| - The company is resolved from the JWT user id, never from the
|   request body, so a company can only change its own logo.
| - The physical file is stored through the existing file upload
|   service (extension + MIME + magic-bytes + size validation,
|   generated unique filename, path-traversal-safe storage folder).
| - Only the relative storage path is persisted in
|   companies.company_logo; no binary data and no absolute paths.
|
| Expected $file shape: a single entry from $_FILES.
*/

function company_service_update_logo_by_user_id(
    int $user_id,
    array $file
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
    | Validate Upload Presence
    |--------------------------------------------------------------------------
    */

    if (
        empty($file)
        ||
        !is_array($file)
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'No logo file was provided.',

            'errors' => [

                'logo' =>
                    'A logo image file is required.'

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    |
    | The company must exist and belong to the authenticated user. Its
    | identity comes exclusively from the authentication context.
    |
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

    $company_id =
        (int) $company['id'];


    /*
    |--------------------------------------------------------------------------
    | Logo Must Be An Image
    |--------------------------------------------------------------------------
    |
    | The shared upload configuration also allows documents; a company
    | logo must be an actual image, so restrict extensions here before
    | delegating to the upload service (which still enforces MIME type,
    | magic bytes and size limits).
    |
    */

    $allowed_logo_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $logo_extension = strtolower(
        pathinfo(
            (string) ($file['name'] ?? ''),
            PATHINFO_EXTENSION
        )
    );

    if (
        !in_array(
            $logo_extension,
            $allowed_logo_extensions,
            true
        )
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'The logo must be an image (jpg, jpeg, png, gif, webp).',

            'errors' => [

                'logo' =>
                    'Unsupported logo image format.'

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Store File Through Existing Upload Architecture
    |--------------------------------------------------------------------------
    */

    $stored = file_upload_service_upload(
        $file,
        [

            'user_id' =>
                $user_id,

            'folder' =>
                'companies',

            'type' =>
                'profile_image',

            'visibility' =>
                'public'

        ]
    );

    if (
        $stored === false
        ||
        !is_array($stored)
        ||
        empty($stored['path'])
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'The logo could not be uploaded. Allowed types: jpg, jpeg, png, gif, webp. Maximum size: 10MB.',

            'errors' => [

                'logo' =>
                    'Invalid or unsupported image file.'

            ],

        ];
    }

    /*
    | The upload service returns the stored file record with an
    | absolute physical path; convert it to the project's relative
    | storage-path convention before persisting (no absolute paths
    | are ever exposed or stored).
    */

    $logo_path = file_upload_service_relative_path(
        (string) $stored['path']
    );

    /*
    | Normalize Windows directory separators so the stored reference
    | is always a clean, URL-friendly relative path.
    */

    $logo_path =
        trim(
            str_replace(
                '\\',
                '/',
                $logo_path
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Persist Relative Path On Company
    |--------------------------------------------------------------------------
    */

    $saved = company_repository_set_logo(
        $company_id,
        $logo_path
    );

    if (!$saved) {

        file_upload_service_delete(
            (int) $stored['id']
        );

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to update the company logo.',

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
            'Company logo updated successfully.',

        'data' => [

            'company_id' =>
                $company_id,

            'company_logo' =>
                $logo_path

        ],

    ];
}
