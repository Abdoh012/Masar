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
    | Work Fields / Industry
    |--------------------------------------------------------------------------
    |
    | Work fields must reference the study_fields lookup table via positive
    | study field IDs. The industry holds specialization name(s): either a
    | single name or an array of names, resolved against the specializations
    | lookup table in the service layer. At least one work field or one
    | industry is required.
    |
    */

    $industry_input =
        isset($data['industry'])
            ? $data['industry']
            : null;

    $has_industry_names = false;

    if (is_string($industry_input)) {

        if (
            trim($industry_input) !== ''
        ) {

            $has_industry_names = true;

            if (
                strlen(trim($industry_input)) > 255
            ) {

                $errors['industry'] =
                    'Each industry must not exceed 255 characters.';
            }
        }
    } elseif (is_array($industry_input)) {

        foreach ($industry_input as $industry_name) {

            if (
                !is_string($industry_name)
            ) {
                continue;
            }

            if (
                trim($industry_name) !== ''
            ) {
                $has_industry_names = true;
            }

            if (
                strlen(trim($industry_name)) > 255
            ) {

                $errors['industry'] =
                    'Each industry must not exceed 255 characters.';

                break;
            }
        }
    }

    $has_work_fields =
        array_key_exists('work_field_ids', $data)
        ||
        $has_industry_names;

    $has_specializations =
        array_key_exists('specialization_ids', $data);

    if (!$has_work_fields && !$has_specializations) {

        $errors['work_field_ids'] =
            'At least one work field is required.';

    } else {

        if (array_key_exists('work_field_ids', $data)) {

            if (
                !is_array($data['work_field_ids'])
                ||
                empty($data['work_field_ids'])
            ) {

                $errors['work_field_ids'] =
                    'Work fields must be a non-empty array of study field IDs.';

            } else {

                foreach ($data['work_field_ids'] as $field_id) {

                    if (
                        !is_numeric($field_id)
                        ||
                        (int) $field_id <= 0
                    ) {

                        $errors['work_field_ids'] =
                            'Each work field must be a positive study field ID.';

                        break;
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization IDs (Industry)
    |--------------------------------------------------------------------------
    |
    | Optional during the transition from work fields. When supplied, the
    | IDs must be positive specialization IDs. Existence against the
    | specializations lookup table is checked in the service layer.
    |
    */

    if (
        array_key_exists(
            'specialization_ids',
            $data
        )
    ) {

        if (
            !is_array($data['specialization_ids'])
        ) {

            $errors['specialization_ids'] =
                'Specializations must be an array of specialization IDs.';

        } else {

            $seen_specialization_ids = [];

            foreach ($data['specialization_ids'] as $specialization_id) {

                if (
                    !is_numeric($specialization_id)
                    ||
                    (int) $specialization_id <= 0
                ) {

                    $errors['specialization_ids'] =
                        'Each specialization must be a positive specialization ID.';

                    break;
                }

                if (
                    in_array(
                        (int) $specialization_id,
                        $seen_specialization_ids,
                        true
                    )
                ) {

                    $errors['specialization_ids'] =
                        'Specialization IDs must not contain duplicates.';

                    break;
                }

                $seen_specialization_ids[] =
                    (int) $specialization_id;
            }
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
    | Work Field IDs
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'work_field_ids',
            $data
        )
    ) {

        if (
            !is_array($data['work_field_ids'])
        ) {

            $errors['work_field_ids'] =
                'Work fields must be an array of study field IDs.';

        } else {

            foreach ($data['work_field_ids'] as $field_id) {

                if (
                    !is_numeric($field_id)
                    ||
                    (int) $field_id <= 0
                ) {

                    $errors['work_field_ids'] =
                        'Each work field must be a positive study field ID.';

                    break;
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization IDs
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'specialization_ids',
            $data
        )
    ) {

        if (
            !is_array($data['specialization_ids'])
        ) {

            $errors['specialization_ids'] =
                'Specializations must be an array of specialization IDs.';

        } else {

            $seen_specialization_ids = [];

            foreach ($data['specialization_ids'] as $specialization_id) {

                if (
                    !is_numeric($specialization_id)
                    ||
                    (int) $specialization_id <= 0
                ) {

                    $errors['specialization_ids'] =
                        'Each specialization must be a positive specialization ID.';

                    break;
                }

                if (
                    in_array(
                        (int) $specialization_id,
                        $seen_specialization_ids,
                        true
                    )
                ) {

                    $errors['specialization_ids'] =
                        'Specialization IDs must not contain duplicates.';

                    break;
                }

                $seen_specialization_ids[] =
                    (int) $specialization_id;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    $allowed_fields = [

        'company_name',

        'description',

        'industry',

        'work_field_ids',

        'specialization_ids',

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
    | Industry (Specialization Name(s))
    |--------------------------------------------------------------------------
    |
    | The industry holds specialization name(s): a single name or an array
    | of names. Existence against the specializations lookup table is
    | checked in the service layer.
    |
    */

    if (
        array_key_exists(
            'industry',
            $data
        )
    ) {

        $industry_input =
            $data['industry'];

        $industry_names =
            is_array($industry_input)
                ? $industry_input
                : [ $industry_input ];

        foreach ($industry_names as $industry_name) {

            if (
                !is_string($industry_name)
            ) {

                $errors['industry'] =
                    'Each industry must be a specialization name.';

                break;
            }

            $industry =
                trim($industry_name);


            if (
                $industry === ''
            ) {

                $errors['industry'] =
                    'Industry cannot be empty.';

                break;
            }

            if (
                strlen($industry) < 2
            ) {

                $errors['industry'] =
                    'Industry must be at least 2 characters.';

                break;
            }

            if (
                strlen($industry) > 255
            ) {

                $errors['industry'] =
                    'Industry must not exceed 255 characters.';

                break;
            }
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
