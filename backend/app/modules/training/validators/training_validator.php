<?php

/**
 * MASAR - Training Validator
 *
 * Responsible for validating training data
 * before it reaches the service/repository layer.
 */


/*
|--------------------------------------------------------------------------
| Validation Result
|--------------------------------------------------------------------------
*/

function training_validator_result(
    bool $valid,
    array $errors = []
): array {

    return [
        'valid'  => $valid,
        'errors' => $errors
    ];
}


/*
|--------------------------------------------------------------------------
| Required String
|--------------------------------------------------------------------------
*/

function training_validator_required_string(
    array &$errors,
    array $data,
    string $field,
    string $label,
    int $min_length = 1,
    ?int $max_length = null
): void {

    $value =
        $data[$field]
        ?? null;

    if (
        $value === null
        ||
        !is_string($value)
        ||
        trim($value) === ''
    ) {

        $errors[$field] =
            "{$label} is required.";

        return;
    }

    $length =
        mb_strlen(
            trim($value)
        );

    if ($length < $min_length) {

        $errors[$field] =
            "{$label} must be at least {$min_length} characters.";
    }

    if (
        $max_length !== null
        &&
        $length > $max_length
    ) {

        $errors[$field] =
            "{$label} must not exceed {$max_length} characters.";
    }
}


/*
|--------------------------------------------------------------------------
| Validate Training Creation
|--------------------------------------------------------------------------
*/

function training_validator_create(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['company_id'])
        ||
        !filter_var(
            $data['company_id'],
            FILTER_VALIDATE_INT
        )
        ||
        (int) $data['company_id'] <= 0
    ) {

        $errors['company_id'] =
            'Valid company_id is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['specialization_id'])
        ||
        !filter_var(
            $data['specialization_id'],
            FILTER_VALIDATE_INT
        )
        ||
        (int) $data['specialization_id'] <= 0
    ) {

        $errors['specialization_id'] =
            'Valid specialization_id is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    training_validator_required_string(
        $errors,
        $data,
        'title',
        'Training title',
        3,
        255
    );


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

        if (
            !is_string(
                $data['description']
            )
        ) {

            $errors['description'] =
                'Description must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['description']
                )
            ) > 5000
        ) {

            $errors['description'] =
                'Description must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['requirements'])
        &&
        $data['requirements'] !== null
    ) {

        if (
            !is_string(
                $data['requirements']
            )
        ) {

            $errors['requirements'] =
                'Requirements must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['requirements']
                )
            ) > 5000
        ) {

            $errors['requirements'] =
                'Requirements must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['location'])
        &&
        $data['location'] !== null
    ) {

        if (
            !is_string(
                $data['location']
            )
        ) {

            $errors['location'] =
                'Location must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['location']
                )
            ) > 255
        ) {

            $errors['location'] =
                'Location must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Training Type
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['training_type'])
        &&
        $data['training_type'] !== null
    ) {

        if (
            !is_string(
                $data['training_type']
            )
        ) {

            $errors['training_type'] =
                'Training type must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['training_type']
                )
            ) > 100
        ) {

            $errors['training_type'] =
                'Training type must not exceed 100 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Start Date
    |--------------------------------------------------------------------------
    */

    if (
        !empty($data['start_date'])
        &&
        !training_validator_is_date(
            $data['start_date']
        )
    ) {

        $errors['start_date'] =
            'Start date must be a valid date.';
    }


    /*
    |--------------------------------------------------------------------------
    | End Date
    |--------------------------------------------------------------------------
    */

    if (
        !empty($data['end_date'])
        &&
        !training_validator_is_date(
            $data['end_date']
        )
    ) {

        $errors['end_date'] =
            'End date must be a valid date.';
    }


    /*
    |--------------------------------------------------------------------------
    | Date Order
    |--------------------------------------------------------------------------
    */

    if (
        !empty($data['start_date'])
        &&
        !empty($data['end_date'])
        &&
        training_validator_is_date(
            $data['start_date']
        )
        &&
        training_validator_is_date(
            $data['end_date']
        )
    ) {

        if (
            strtotime(
                $data['end_date']
            )
            <
            strtotime(
                $data['start_date']
            )
        ) {

            $errors['end_date'] =
                'End date must be after or equal to start date.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Capacity
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['capacity'])
        &&
        $data['capacity'] !== null
        &&
        $data['capacity'] !== ''
    ) {

        if (
            filter_var(
                $data['capacity'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['capacity'] <= 0
        ) {

            $errors['capacity'] =
                'Capacity must be a positive integer.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['status'])
        &&
        $data['status'] !== null
    ) {

        if (
            !training_validator_is_valid_status(
                $data['status']
            )
        ) {

            $errors['status'] =
                'Invalid training status.';
        }
    }


    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Training Update
|--------------------------------------------------------------------------
*/

function training_validator_update(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'title',
            $data
        )
    ) {

        training_validator_required_string(
            $errors,
            $data,
            'title',
            'Training title',
            3,
            255
        );
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
        &&
        $data['description'] !== null
    ) {

        if (
            !is_string(
                $data['description']
            )
        ) {

            $errors['description'] =
                'Description must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['description']
                )
            ) > 5000
        ) {

            $errors['description'] =
                'Description must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'requirements',
            $data
        )
        &&
        $data['requirements'] !== null
    ) {

        if (
            !is_string(
                $data['requirements']
            )
        ) {

            $errors['requirements'] =
                'Requirements must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['requirements']
                )
            ) > 5000
        ) {

            $errors['requirements'] =
                'Requirements must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'location',
            $data
        )
        &&
        $data['location'] !== null
    ) {

        if (
            !is_string(
                $data['location']
            )
        ) {

            $errors['location'] =
                'Location must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['location']
                )
            ) > 255
        ) {

            $errors['location'] =
                'Location must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Training Type
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'training_type',
            $data
        )
        &&
        $data['training_type'] !== null
    ) {

        if (
            !is_string(
                $data['training_type']
            )
        ) {

            $errors['training_type'] =
                'Training type must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['training_type']
                )
            ) > 100
        ) {

            $errors['training_type'] =
                'Training type must not exceed 100 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Dates
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'start_date',
            $data
        )
        &&
        !empty($data['start_date'])
        &&
        !training_validator_is_date(
            $data['start_date']
        )
    ) {

        $errors['start_date'] =
            'Start date must be a valid date.';
    }


    if (
        array_key_exists(
            'end_date',
            $data
        )
        &&
        !empty($data['end_date'])
        &&
        !training_validator_is_date(
            $data['end_date']
        )
    ) {

        $errors['end_date'] =
            'End date must be a valid date.';
    }


    /*
    |--------------------------------------------------------------------------
    | Date Order
    |--------------------------------------------------------------------------
    */

    $start_date =
        $data['start_date']
        ?? null;

    $end_date =
        $data['end_date']
        ?? null;

    if (
        !empty($start_date)
        &&
        !empty($end_date)
        &&
        training_validator_is_date(
            $start_date
        )
        &&
        training_validator_is_date(
            $end_date
        )
    ) {

        if (
            strtotime($end_date)
            <
            strtotime($start_date)
        ) {

            $errors['end_date'] =
                'End date must be after or equal to start date.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Capacity
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'capacity',
            $data
        )
        &&
        $data['capacity'] !== null
        &&
        $data['capacity'] !== ''
    ) {

        if (
            filter_var(
                $data['capacity'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['capacity'] <= 0
        ) {

            $errors['capacity'] =
                'Capacity must be a positive integer.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'status',
            $data
        )
        &&
        !training_validator_is_valid_status(
            $data['status']
        )
    ) {

        $errors['status'] =
            'Invalid training status.';
    }


    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Training ID
|--------------------------------------------------------------------------
*/

function training_validator_id(
    mixed $training_id
): array {

    $errors = [];

    if (
        filter_var(
            $training_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $training_id <= 0
    ) {

        $errors['training_id'] =
            'Valid training_id is required.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Company Ownership Data
|--------------------------------------------------------------------------
*/

function training_validator_company_id(
    mixed $company_id
): array {

    $errors = [];

    if (
        filter_var(
            $company_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $company_id <= 0
    ) {

        $errors['company_id'] =
            'Valid company_id is required.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

function training_validator_status(
    string $status
): array {

    $errors = [];

    if (
        !training_validator_is_valid_status(
            $status
        )
    ) {

        $errors['status'] =
            'Invalid training status.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Allowed Training Statuses
|--------------------------------------------------------------------------
*/

function training_validator_allowed_statuses(): array {

    return [

        'draft',

        'published',

        'open',

        'active',

        'closed',

        'cancelled',

        'completed'

    ];
}


/*
|--------------------------------------------------------------------------
| Check Training Status
|--------------------------------------------------------------------------
*/

function training_validator_is_valid_status(
    mixed $status
): bool {

    if (
        !is_string($status)
        ||
        trim($status) === ''
    ) {

        return false;
    }

    return in_array(
        strtolower(
            trim($status)
        ),
        training_validator_allowed_statuses(),
        true
    );
}


/*
|--------------------------------------------------------------------------
| Validate Date
|--------------------------------------------------------------------------
*/

function training_validator_is_date(
    mixed $date
): bool {

    if (
        !is_string($date)
        ||
        trim($date) === ''
    ) {

        return false;
    }

    $date =
        trim($date);

    $formats = [

        'Y-m-d',

        'Y-m-d H:i:s',

        'Y-m-d H:i'

    ];

    foreach (
        $formats as $format
    ) {

        $date_object =
            DateTime::createFromFormat(
                $format,
                $date
            );

        if (
            $date_object !== false
            &&
            $date_object->format(
                $format
            ) === $date
        ) {

            return true;
        }
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| Validate Pagination
|--------------------------------------------------------------------------
*/

function training_validator_pagination(
    mixed $limit,
    mixed $offset
): array {

    $errors = [];

    if (
        filter_var(
            $limit,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $limit < 1
        ||
        (int) $limit > 100
    ) {

        $errors['limit'] =
            'Limit must be an integer between 1 and 100.';
    }

    if (
        filter_var(
            $offset,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $offset < 0
    ) {

        $errors['offset'] =
            'Offset must be a non-negative integer.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Search Keyword
|--------------------------------------------------------------------------
*/

function training_validator_search(
    mixed $keyword
): array {

    $errors = [];

    if (
        !is_string($keyword)
        ||
        trim($keyword) === ''
    ) {

        $errors['keyword'] =
            'Search keyword is required.';

    } elseif (
        mb_strlen(
            trim($keyword)
        ) < 2
    ) {

        $errors['keyword'] =
            'Search keyword must be at least 2 characters.';

    } elseif (
        mb_strlen(
            trim($keyword)
        ) > 255
    ) {

        $errors['keyword'] =
            'Search keyword must not exceed 255 characters.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Closing Data
|--------------------------------------------------------------------------
*/

function training_validator_close(
    array $data
): array {

    $errors = [];

    if (
        isset($data['closing_note'])
        &&
        $data['closing_note'] !== null
    ) {

        if (
            !is_string(
                $data['closing_note']
            )
        ) {

            $errors['closing_note'] =
                'Closing note must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['closing_note']
                )
            ) > 5000
        ) {

            $errors['closing_note'] =
                'Closing note must not exceed 5000 characters.';
        }
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Training Publish
|--------------------------------------------------------------------------
*/

function training_validator_publish(
    array $data = []
): array {

    $errors = [];

    if (
        isset($data['status'])
        &&
        $data['status'] !== 'published'
    ) {

        $errors['status'] =
            'Training must be published with published status.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Training Session Reference
|--------------------------------------------------------------------------
*/

function training_validator_session_id(
    mixed $session_id
): array {

    $errors = [];

    if (
        filter_var(
            $session_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $session_id <= 0
    ) {

        $errors['session_id'] =
            'Valid session_id is required.';
    }

    return training_validator_result(
        empty($errors),
        $errors
    );
}
