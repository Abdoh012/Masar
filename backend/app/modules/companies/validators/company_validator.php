<?php

/**
 * MASAR - Company Validator
 *
 * Responsible for validating company-related input data.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No database queries.
 * - No HTTP handling.
 * - No business logic.
 */


/*
|--------------------------------------------------------------------------
| Create Company Validation
|--------------------------------------------------------------------------
|
| Required fields:
| - company_name
| - industry
|
| Optional:
| - description
|
*/

function company_validator_create(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Company Name
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['company_name'])
        ||
        trim(
            (string) $data['company_name']
        ) === ''
    ) {

        $errors['company_name'] =
            'Company name is required.';

    } else {

        $company_name =
            trim(
                (string) $data['company_name']
            );


        if (
            strlen($company_name) < 2
        ) {

            $errors['company_name'] =
                'Company name must be at least 2 characters.';

        } elseif (
            strlen($company_name) > 255
        ) {

            $errors['company_name'] =
                'Company name must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Industry
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['industry'])
        ||
        trim(
            (string) $data['industry']
        ) === ''
    ) {

        $errors['industry'] =
            'Industry is required.';

    } else {

        $industry =
            trim(
                (string) $data['industry']
            );


        if (
            strlen($industry) < 2
        ) {

            $errors['industry'] =
                'Industry must be at least 2 characters.';

        } elseif (
            strlen($industry) > 255
        ) {

            $errors['industry'] =
                'Industry must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['description'])
        &&
        $data['description'] !== null
    ) {

        $description =
            trim(
                (string) $data['description']
            );


        if (
            strlen($description) > 5000
        ) {

            $errors['description'] =
                'Description must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Update Company Validation
|--------------------------------------------------------------------------
|
| All fields are optional because this validator
| is used for partial profile updates.
|
*/

function company_validator_update(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Check At Least One Field
    |--------------------------------------------------------------------------
    */

    $allowed_fields = [

        'company_name',

        'description',

        'industry',

    ];


    $has_allowed_field =
        false;


    foreach (
        $allowed_fields
        as $field
    ) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $has_allowed_field =
                true;

            break;
        }
    }


    if (
        !$has_allowed_field
    ) {

        return [

            'valid' =>
                false,

            'errors' => [

                'general' =>
                    'At least one valid field is required.',

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company Name
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'company_name',
            $data
        )
    ) {

        $company_name =
            trim(
                (string) $data['company_name']
            );


        if (
            $company_name === ''
        ) {

            $errors['company_name'] =
                'Company name cannot be empty.';

        } elseif (
            strlen($company_name) < 2
        ) {

            $errors['company_name'] =
                'Company name must be at least 2 characters.';

        } elseif (
            strlen($company_name) > 255
        ) {

            $errors['company_name'] =
                'Company name must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Industry
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'industry',
            $data
        )
    ) {

        $industry =
            trim(
                (string) $data['industry']
            );


        if (
            $industry === ''
        ) {

            $errors['industry'] =
                'Industry cannot be empty.';

        } elseif (
            strlen($industry) < 2
        ) {

            $errors['industry'] =
                'Industry must be at least 2 characters.';

        } elseif (
            strlen($industry) > 255
        ) {

            $errors['industry'] =
                'Industry must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'description',
            $data
        )
    ) {

        if (
            $data['description'] !== null
        ) {

            $description =
                trim(
                    (string) $data['description']
                );


            if (
                strlen($description) > 5000
            ) {

                $errors['description'] =
                    'Description must not exceed 5000 characters.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Company ID
|--------------------------------------------------------------------------
*/

function company_validator_id(
    $company_id
): array {

    $errors = [];


    if (
        !is_numeric($company_id)
    ) {

        $errors['company_id'] =
            'Company ID must be a valid number.';

    } elseif (
        (int) $company_id <= 0
    ) {

        $errors['company_id'] =
            'Company ID must be greater than zero.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate User ID
|--------------------------------------------------------------------------
*/

function company_validator_user_id(
    $user_id
): array {

    $errors = [];


    if (
        !is_numeric($user_id)
    ) {

        $errors['user_id'] =
            'User ID must be a valid number.';

    } elseif (
        (int) $user_id <= 0
    ) {

        $errors['user_id'] =
            'User ID must be greater than zero.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Approval Status
|--------------------------------------------------------------------------
*/

function company_validator_approval_status(
    string $status
): array {

    $allowed_statuses = [

        'pending',

        'approved',

        'rejected',

    ];


    $errors = [];


    if (
        !in_array(
            $status,
            $allowed_statuses,
            true
        )
    ) {

        $errors['approval_status'] =
            'Invalid company approval status.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Rejection Reason
|--------------------------------------------------------------------------
*/

function company_validator_rejection_reason(
    $reason
): array {

    $errors = [];


    if (
        $reason === null
        ||
        trim(
            (string) $reason
        ) === ''
    ) {

        $errors['reason'] =
            'Rejection reason is required.';

    } else {

        $reason =
            trim(
                (string) $reason
            );


        if (
            strlen($reason) > 1000
        ) {

            $errors['reason'] =
                'Rejection reason must not exceed 1000 characters.';
        }
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Pagination
|--------------------------------------------------------------------------
*/

function company_validator_pagination(
    $page = 1,
    $limit = 20
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    if (
        !is_numeric($page)
        ||
        (int) $page < 1
    ) {

        $errors['page'] =
            'Page must be a positive integer.';
    }


    /*
    |--------------------------------------------------------------------------
    | Limit
    |--------------------------------------------------------------------------
    */

    if (
        !is_numeric($limit)
        ||
        (int) $limit < 1
    ) {

        $errors['limit'] =
            'Limit must be a positive integer.';

    } elseif (
        (int) $limit > 100
    ) {

        $errors['limit'] =
            'Limit must not exceed 100.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Company Search Query
|--------------------------------------------------------------------------
*/

function company_validator_search_query(
    $query
): array {

    $errors = [];


    $query =
        trim(
            (string) $query
        );


    if (
        $query === ''
    ) {

        $errors['query'] =
            'Search query is required.';

    } elseif (
        strlen($query) < 2
    ) {

        $errors['query'] =
            'Search query must be at least 2 characters.';

    } elseif (
        strlen($query) > 255
    ) {

        $errors['query'] =
            'Search query must not exceed 255 characters.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}
