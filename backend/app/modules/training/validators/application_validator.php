<?php

/**
 * MASAR - Application Validator
 *
 * Responsible for validating training application data
 * before it reaches the service/repository layer.
 */


/*
|--------------------------------------------------------------------------
| Validation Result
|--------------------------------------------------------------------------
*/

function application_validator_result(
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
| Allowed Application Statuses
|--------------------------------------------------------------------------
*/

function application_validator_allowed_statuses(): array {

    return [

        'pending',

        'accepted',

        'rejected',

        'withdrawn',

        'cancelled',

        'completed'

    ];
}


/*
|--------------------------------------------------------------------------
| Check Application Status
|--------------------------------------------------------------------------
*/

function application_validator_is_valid_status(
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
        application_validator_allowed_statuses(),
        true
    );
}


/*
|--------------------------------------------------------------------------
| Validate Application ID
|--------------------------------------------------------------------------
*/

function application_validator_id(
    mixed $application_id
): array {

    $errors = [];

    if (
        filter_var(
            $application_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $application_id <= 0
    ) {

        $errors['application_id'] =
            'Valid application_id is required.';
    }

    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Student ID
|--------------------------------------------------------------------------
*/

function application_validator_student_id(
    mixed $student_id
): array {

    $errors = [];

    if (
        filter_var(
            $student_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $student_id <= 0
    ) {

        $errors['student_id'] =
            'Valid student_id is required.';
    }

    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Training ID
|--------------------------------------------------------------------------
*/

function application_validator_training_id(
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

    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Create Application
|--------------------------------------------------------------------------
*/

function application_validator_create(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Student ID
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['student_id'])
        ||
        filter_var(
            $data['student_id'],
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $data['student_id'] <= 0
    ) {

        $errors['student_id'] =
            'Valid student_id is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['training_id'])
        ||
        filter_var(
            $data['training_id'],
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $data['training_id'] <= 0
    ) {

        $errors['training_id'] =
            'Valid training_id is required.';
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
        &&
        $data['status'] !== ''
    ) {

        if (
            !application_validator_is_valid_status(
                $data['status']
            )
        ) {

            $errors['status'] =
                'Invalid application status.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Cover Letter
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['cover_letter'])
        &&
        $data['cover_letter'] !== null
    ) {

        if (
            !is_string(
                $data['cover_letter']
            )
        ) {

            $errors['cover_letter'] =
                'Cover letter must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['cover_letter']
                )
            ) > 10000
        ) {

            $errors['cover_letter'] =
                'Cover letter must not exceed 10000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['notes'])
        &&
        $data['notes'] !== null
    ) {

        if (
            !is_string(
                $data['notes']
            )
        ) {

            $errors['notes'] =
                'Notes must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['notes']
                )
            ) > 5000
        ) {

            $errors['notes'] =
                'Notes must not exceed 5000 characters.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Application Update
|--------------------------------------------------------------------------
*/

function application_validator_update(
    array $data
): array {

    $errors = [];


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
    ) {

        if (
            !application_validator_is_valid_status(
                $data['status']
            )
        ) {

            $errors['status'] =
                'Invalid application status.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Cover Letter
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'cover_letter',
            $data
        )
        &&
        $data['cover_letter'] !== null
    ) {

        if (
            !is_string(
                $data['cover_letter']
            )
        ) {

            $errors['cover_letter'] =
                'Cover letter must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['cover_letter']
                )
            ) > 10000
        ) {

            $errors['cover_letter'] =
                'Cover letter must not exceed 10000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'notes',
            $data
        )
        &&
        $data['notes'] !== null
    ) {

        if (
            !is_string(
                $data['notes']
            )
        ) {

            $errors['notes'] =
                'Notes must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['notes']
                )
            ) > 5000
        ) {

            $errors['notes'] =
                'Notes must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Review Note
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'review_note',
            $data
        )
        &&
        $data['review_note'] !== null
    ) {

        if (
            !is_string(
                $data['review_note']
            )
        ) {

            $errors['review_note'] =
                'Review note must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['review_note']
                )
            ) > 5000
        ) {

            $errors['review_note'] =
                'Review note must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Rejection Reason
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'rejection_reason',
            $data
        )
        &&
        $data['rejection_reason'] !== null
    ) {

        if (
            !is_string(
                $data['rejection_reason']
            )
        ) {

            $errors['rejection_reason'] =
                'Rejection reason must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['rejection_reason']
                )
            ) > 5000
        ) {

            $errors['rejection_reason'] =
                'Rejection reason must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Withdrawal Reason
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'withdrawal_reason',
            $data
        )
        &&
        $data['withdrawal_reason'] !== null
    ) {

        if (
            !is_string(
                $data['withdrawal_reason']
            )
        ) {

            $errors['withdrawal_reason'] =
                'Withdrawal reason must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['withdrawal_reason']
                )
            ) > 5000
        ) {

            $errors['withdrawal_reason'] =
                'Withdrawal reason must not exceed 5000 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewed By
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'reviewed_by',
            $data
        )
        &&
        $data['reviewed_by'] !== null
    ) {

        if (
            filter_var(
                $data['reviewed_by'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['reviewed_by'] <= 0
        ) {

            $errors['reviewed_by'] =
                'Reviewed by must be a valid user ID.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Status Change
|--------------------------------------------------------------------------
*/

function application_validator_status_change(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['status'])
        ||
        !application_validator_is_valid_status(
            $data['status']
        )
    ) {

        $errors['status'] =
            'Valid application status is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['reviewed_by'])
        &&
        $data['reviewed_by'] !== null
    ) {

        if (
            filter_var(
                $data['reviewed_by'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['reviewed_by'] <= 0
        ) {

            $errors['reviewed_by'] =
                'Reviewed by must be a valid user ID.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Review Note
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['review_note'])
        &&
        $data['review_note'] !== null
    ) {

        if (
            !is_string(
                $data['review_note']
            )
        ) {

            $errors['review_note'] =
                'Review note must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['review_note']
                )
            ) > 5000
        ) {

            $errors['review_note'] =
                'Review note must not exceed 5000 characters.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Rejection
|--------------------------------------------------------------------------
*/

function application_validator_rejection(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Reason
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['reason'])
        ||
        !is_string(
            $data['reason']
        )
        ||
        trim(
            $data['reason']
        ) === ''
    ) {

        $errors['reason'] =
            'Rejection reason is required.';

    } elseif (
        mb_strlen(
            trim(
                $data['reason']
            )
        ) > 5000
    ) {

        $errors['reason'] =
            'Rejection reason must not exceed 5000 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['reviewed_by'])
        &&
        $data['reviewed_by'] !== null
    ) {

        if (
            filter_var(
                $data['reviewed_by'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['reviewed_by'] <= 0
        ) {

            $errors['reviewed_by'] =
                'Reviewed by must be a valid user ID.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Withdrawal
|--------------------------------------------------------------------------
*/

function application_validator_withdrawal(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Reason
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['reason'])
        &&
        $data['reason'] !== null
    ) {

        if (
            !is_string(
                $data['reason']
            )
        ) {

            $errors['reason'] =
                'Withdrawal reason must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['reason']
                )
            ) > 5000
        ) {

            $errors['reason'] =
                'Withdrawal reason must not exceed 5000 characters.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Pagination
|--------------------------------------------------------------------------
*/

function application_validator_pagination(
    mixed $limit,
    mixed $offset
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Limit
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Offset
    |--------------------------------------------------------------------------
    */

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


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Filter Status
|--------------------------------------------------------------------------
*/

function application_validator_filter_status(
    mixed $status
): array {

    $errors = [];


    if (
        $status !== null
        &&
        $status !== ''
        &&
        !application_validator_is_valid_status(
            $status
        )
    ) {

        $errors['status'] =
            'Invalid application status.';
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Application Ownership
|--------------------------------------------------------------------------
*/

function application_validator_ownership(
    mixed $student_id,
    mixed $training_id
): array {

    $errors = [];


    if (
        filter_var(
            $student_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $student_id <= 0
    ) {

        $errors['student_id'] =
            'Valid student_id is required.';
    }


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


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Review Data
|--------------------------------------------------------------------------
*/

function application_validator_review(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['reviewed_by'])
        ||
        filter_var(
            $data['reviewed_by'],
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $data['reviewed_by'] <= 0
    ) {

        $errors['reviewed_by'] =
            'Valid reviewer ID is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Note
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['review_note'])
        &&
        $data['review_note'] !== null
    ) {

        if (
            !is_string(
                $data['review_note']
            )
        ) {

            $errors['review_note'] =
                'Review note must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['review_note']
                )
            ) > 5000
        ) {

            $errors['review_note'] =
                'Review note must not exceed 5000 characters.';
        }
    }


    return application_validator_result(
        empty($errors),
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Validate Search Keyword
|--------------------------------------------------------------------------
*/

function application_validator_search(
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


    return application_validator_result(
        empty($errors),
        $errors
    );
}
