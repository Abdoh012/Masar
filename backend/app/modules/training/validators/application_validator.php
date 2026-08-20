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
    |
    | The authenticated student is resolved server-side, so student_id
    | is only validated for format when it is provided in the payload.
    |
    */

    if (
        isset($data['student_id'])
        &&
        $data['student_id'] !== null
        &&
        $data['student_id'] !== ''
    ) {

        if (
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
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    |
    | training_id is also passed explicitly to the service, so it is
    | only validated for format when provided in the payload.
    |
    */

    if (
        isset($data['training_id'])
        &&
        $data['training_id'] !== null
        &&
        $data['training_id'] !== ''
    ) {

        if (
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


    /*
    |--------------------------------------------------------------------------
    | CV File ID
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['cv_file_id'])
        &&
        $data['cv_file_id'] !== null
        &&
        $data['cv_file_id'] !== ''
    ) {

        if (
            filter_var(
                $data['cv_file_id'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['cv_file_id'] <= 0
        ) {

            $errors['cv_file_id'] =
                'Valid cv_file_id is required.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Applicant Type
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['applicant_type'])
        &&
        $data['applicant_type'] !== null
        &&
        $data['applicant_type'] !== ''
    ) {

        $applicant_type =
            strtolower(
                trim(
                    (string) $data['applicant_type']
                )
            );

        if (
            !in_array(
                $applicant_type,
                ['student', 'graduated'],
                true
            )
        ) {

            $errors['applicant_type'] =
                'Applicant type must be student or graduated.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | University ID
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['university_id'])
        &&
        $data['university_id'] !== null
        &&
        $data['university_id'] !== ''
    ) {

        if (
            filter_var(
                $data['university_id'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['university_id'] <= 0
        ) {

            $errors['university_id'] =
                'Valid university_id is required.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Faculty ID
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['faculty_id'])
        &&
        $data['faculty_id'] !== null
        &&
        $data['faculty_id'] !== ''
    ) {

        if (
            filter_var(
                $data['faculty_id'],
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $data['faculty_id'] <= 0
        ) {

            $errors['faculty_id'] =
                'Valid faculty_id is required.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Academic Year
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['academic_year'])
        &&
        $data['academic_year'] !== null
        &&
        $data['academic_year'] !== ''
    ) {

        if (
            !is_string(
                $data['academic_year']
            )
        ) {

            $errors['academic_year'] =
                'Academic year must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['academic_year']
                )
            ) > 20
        ) {

            $errors['academic_year'] =
                'Academic year must not exceed 20 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Graduation Year
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['graduation_year'])
        &&
        $data['graduation_year'] !== null
        &&
        $data['graduation_year'] !== ''
    ) {

        $graduation_year =
            filter_var(
                $data['graduation_year'],
                FILTER_VALIDATE_INT
            );

        if (
            $graduation_year === false
            ||
            $graduation_year < 1950
            ||
            $graduation_year > 2100
        ) {

            $errors['graduation_year'] =
                'Graduation year must be a valid year.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Motivation
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['motivation'])
        &&
        $data['motivation'] !== null
    ) {

        if (
            !is_string(
                $data['motivation']
            )
        ) {

            $errors['motivation'] =
                'Motivation must be a string.';

        } elseif (
            mb_strlen(
                trim(
                    $data['motivation']
                )
            ) > 5000
        ) {

            $errors['motivation'] =
                'Motivation must not exceed 5000 characters.';
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


/*
|--------------------------------------------------------------------------
| Validate Application Answers
|--------------------------------------------------------------------------
|
| Validates submitted answers against the training's configured
| questions (required questions must be answered).
|
*/

function application_validator_answers(
    array $answers,
    array $questions
): array {

    $errors = [];

    if (
        !is_array($answers)
        ||
        empty($questions)
    ) {
        return application_validator_result(
            empty($errors),
            $errors
        );
    }

    $answers_by_question = [];

    foreach ($answers as $answer) {

        if (
            !is_array($answer)
        ) {
            continue;
        }

        $question_id =
            isset($answer['question_id'])
                ? (int) $answer['question_id']
                : 0;

        if ($question_id <= 0) {
            continue;
        }

        $value =
            $answer['answer']
            ?? $answer['value']
            ?? '';

        if (
            !is_string($value)
        ) {

            $errors["answers.{$question_id}"] =
                'Answer must be a string.';

            continue;
        }

        $answers_by_question[$question_id] =
            trim($value);
    }

    foreach ($questions as $question) {

        $question_id =
            (int) $question['id'];

        $answered =
            array_key_exists(
                $question_id,
                $answers_by_question
            );

        if (
            !empty($question['required'])
            &&
            (
                !$answered
                ||
                $answers_by_question[$question_id] === ''
            )
        ) {

            $errors["answers.{$question_id}"] =
                'This question is required.';

            continue;
        }

        if (!$answered) {
            continue;
        }

        $value =
            $answers_by_question[$question_id];

        if (
            mb_strlen($value) > 10000
        ) {

            $errors["answers.{$question_id}"] =
                'Answer must not exceed 10000 characters.';

            continue;
        }

        if (
            in_array(
                $question['question_type'] ?? '',
                ['select', 'radio'],
                true
            )
            &&
            !empty($question['options'])
        ) {

            $allowed_options = $question['options'];

            if (
                !in_array(
                    $value,
                    $allowed_options,
                    true
                )
            ) {

                $errors["answers.{$question_id}"] =
                    'Selected option is not valid.';
            }
        }
    }

    return application_validator_result(
        empty($errors),
        $errors
    );
}
