<?php

/**
 * MASAR - Certificate Validator
 *
 * Responsible for validating certificate-related input.
 *
 * Controller
 *     ↓
 * Validator
 *     ↓
 * Service
 */


/*
|--------------------------------------------------------------------------
| Certificate Statuses
|--------------------------------------------------------------------------
*/

function certificate_validator_statuses(): array
{
    return [
        'pending',
        'issued',
        'active',
        'valid',
        'revoked',
        'expired'
    ];
}


/*
|--------------------------------------------------------------------------
| Validate Certificate ID
|--------------------------------------------------------------------------
*/

function certificate_validator_id(
    $certificate_id
): array {

    $errors = [];

    if (
        $certificate_id === null
        ||
        $certificate_id === ''
    ) {
        $errors['certificate_id'] =
            'Certificate ID is required.';

        return $errors;
    }

    if (
        filter_var(
            $certificate_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $certificate_id <= 0
    ) {
        $errors['certificate_id'] =
            'Certificate ID must be a positive integer.';
    }

    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Certificate Number
|--------------------------------------------------------------------------
*/

function certificate_validator_number(
    $certificate_number,
    bool $required = true
): array {

    $errors = [];

    $certificate_number =
        trim(
            (string) $certificate_number
        );


    if (
        $required
        &&
        $certificate_number === ''
    ) {

        $errors['certificate_number'] =
            'Certificate number is required.';

        return $errors;
    }


    if (
        $certificate_number !== ''
        &&
        strlen($certificate_number) > 100
    ) {

        $errors['certificate_number'] =
            'Certificate number must not exceed 100 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

function certificate_validator_status(
    $status,
    bool $required = true
): array {

    $errors = [];

    $status =
        strtolower(
            trim(
                (string) $status
            )
        );


    if (
        $required
        &&
        $status === ''
    ) {

        $errors['status'] =
            'Certificate status is required.';

        return $errors;
    }


    if (
        $status !== ''
        &&
        !in_array(
            $status,
            certificate_validator_statuses(),
            true
        )
    ) {

        $errors['status'] =
            'Invalid certificate status.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate User ID
|--------------------------------------------------------------------------
*/

function certificate_validator_user_id(
    $user_id,
    bool $required = false
): array {

    $errors = [];

    if (
        !$required
        &&
        (
            $user_id === null
            ||
            $user_id === ''
        )
    ) {
        return $errors;
    }


    if (
        filter_var(
            $user_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $user_id <= 0
    ) {

        $errors['user_id'] =
            'User ID must be a positive integer.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Student ID
|--------------------------------------------------------------------------
*/

function certificate_validator_student_id(
    $student_id,
    bool $required = false
): array {

    $errors = [];

    if (
        !$required
        &&
        (
            $student_id === null
            ||
            $student_id === ''
        )
    ) {
        return $errors;
    }


    if (
        filter_var(
            $student_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $student_id <= 0
    ) {

        $errors['student_id'] =
            'Student ID must be a positive integer.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Training ID
|--------------------------------------------------------------------------
*/

function certificate_validator_training_id(
    $training_id,
    bool $required = false
): array {

    $errors = [];

    if (
        !$required
        &&
        (
            $training_id === null
            ||
            $training_id === ''
        )
    ) {
        return $errors;
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
            'Training ID must be a positive integer.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Company ID
|--------------------------------------------------------------------------
*/

function certificate_validator_company_id(
    $company_id,
    bool $required = false
): array {

    $errors = [];

    if (
        !$required
        &&
        (
            $company_id === null
            ||
            $company_id === ''
        )
    ) {
        return $errors;
    }


    if (
        filter_var(
            $company_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $company_id <= 0
    ) {

        $errors['company_id'] =
            'Company ID must be a positive integer.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Title
|--------------------------------------------------------------------------
*/

function certificate_validator_title(
    $title,
    bool $required = true
): array {

    $errors = [];

    $title =
        trim(
            (string) $title
        );


    if (
        $required
        &&
        $title === ''
    ) {

        $errors['title'] =
            'Certificate title is required.';

        return $errors;
    }


    if (
        strlen($title) > 255
    ) {

        $errors['title'] =
            'Certificate title must not exceed 255 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Description
|--------------------------------------------------------------------------
*/

function certificate_validator_description(
    $description,
    bool $required = false
): array {

    $errors = [];

    $description =
        trim(
            (string) $description
        );


    if (
        $required
        &&
        $description === ''
    ) {

        $errors['description'] =
            'Certificate description is required.';

        return $errors;
    }


    if (
        strlen($description) > 5000
    ) {

        $errors['description'] =
            'Certificate description must not exceed 5000 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Reason
|--------------------------------------------------------------------------
*/

function certificate_validator_reason(
    $reason,
    bool $required = false
): array {

    $errors = [];

    $reason =
        trim(
            (string) $reason
        );


    if (
        $required
        &&
        $reason === ''
    ) {

        $errors['reason'] =
            'Reason is required.';

        return $errors;
    }


    if (
        strlen($reason) > 2000
    ) {

        $errors['reason'] =
            'Reason must not exceed 2000 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Date
|--------------------------------------------------------------------------
*/

function certificate_validator_date(
    $date,
    string $field,
    bool $required = false
): array {

    $errors = [];

    $date =
        trim(
            (string) $date
        );


    if (
        $required
        &&
        $date === ''
    ) {

        $errors[$field] =
            "{$field} is required.";

        return $errors;
    }


    if ($date === '') {
        return $errors;
    }


    $timestamp =
        strtotime($date);


    if ($timestamp === false) {

        $errors[$field] =
            "{$field} must be a valid date.";
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Issued / Expiration Dates
|--------------------------------------------------------------------------
*/

function certificate_validator_dates(
    array $data
): array {

    $errors = [];


    if (
        array_key_exists(
            'issued_at',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_date(
                    $data['issued_at'],
                    'issued_at',
                    false
                )
            );
    }


    if (
        array_key_exists(
            'expires_at',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_date(
                    $data['expires_at'],
                    'expires_at',
                    false
                )
            );
    }


    /*
     * Compare dates when both exist.
     */

    if (
        empty($errors)
        &&
        !empty($data['issued_at'])
        &&
        !empty($data['expires_at'])
    ) {

        $issued_at =
            strtotime(
                $data['issued_at']
            );

        $expires_at =
            strtotime(
                $data['expires_at']
            );


        if (
            $issued_at !== false
            &&
            $expires_at !== false
            &&
            $expires_at <= $issued_at
        ) {

            $errors['expires_at'] =
                'Expiration date must be after the issue date.';
        }
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate File
|--------------------------------------------------------------------------
*/

function certificate_validator_file(
    array $file,
    bool $required = false
): array {

    $errors = [];


    if (empty($file)) {

        if ($required) {

            $errors['file'] =
                'Certificate file is required.';
        }

        return $errors;
    }


    /*
     * Upload error.
     */

    if (
        isset($file['error'])
        &&
        (int) $file['error'] !== UPLOAD_ERR_OK
    ) {

        $errors['file'] =
            'Certificate file upload failed.';

        return $errors;
    }


    /*
     * Size.
     *
     * 10 MB maximum.
     */

    if (
        isset($file['size'])
        &&
        (int) $file['size'] > 10 * 1024 * 1024
    ) {

        $errors['file'] =
            'Certificate file must not exceed 10 MB.';
    }


    /*
     * MIME type.
     */

    $allowed_mimes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];


    if (
        !empty($file['type'])
        &&
        !in_array(
            $file['type'],
            $allowed_mimes,
            true
        )
    ) {

        $errors['file'] =
            'Invalid certificate file type.';
    }


    /*
     * Extension.
     */

    if (
        !empty($file['name'])
    ) {

        $extension =
            strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );

        $allowed_extensions = [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ];


        if (
            !in_array(
                $extension,
                $allowed_extensions,
                true
            )
        ) {

            $errors['file'] =
                'Invalid certificate file extension.';
        }
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Create Data
|--------------------------------------------------------------------------
*/

function certificate_validator_create(
    array $data
): array {

    $errors = [];


    /*
     * Student
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_student_id(
                $data['student_id'] ?? null,
                true
            )
        );


    /*
     * Training
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_training_id(
                $data['training_id'] ?? null,
                true
            )
        );


    /*
     * Company
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_company_id(
                $data['company_id'] ?? null,
                false
            )
        );


    /*
     * User
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_user_id(
                $data['user_id'] ?? null,
                false
            )
        );


    /*
     * Number
     *
     * Usually generated by the service,
     * therefore not required here.
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_number(
                $data['certificate_number'] ?? null,
                false
            )
        );


    /*
     * Title
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_title(
                $data['title'] ?? null,
                true
            )
        );


    /*
     * Description
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_description(
                $data['description'] ?? null,
                false
            )
        );


    /*
     * Status
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_status(
                $data['status'] ?? 'issued',
                false
            )
        );


    /*
     * Dates
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_dates(
                $data
            )
        );


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Update Data
|--------------------------------------------------------------------------
*/

function certificate_validator_update(
    array $data
): array {

    $errors = [];


    if (empty($data)) {

        $errors['certificate'] =
            'At least one certificate field must be provided.';

        return $errors;
    }


    /*
     * Optional IDs.
     */

    if (
        array_key_exists(
            'user_id',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_user_id(
                    $data['user_id'],
                    false
                )
            );
    }


    if (
        array_key_exists(
            'student_id',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_student_id(
                    $data['student_id'],
                    false
                )
            );
    }


    if (
        array_key_exists(
            'training_id',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_training_id(
                    $data['training_id'],
                    false
                )
            );
    }


    if (
        array_key_exists(
            'company_id',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_company_id(
                    $data['company_id'],
                    false
                )
            );
    }


    /*
     * Text fields.
     */

    if (
        array_key_exists(
            'certificate_number',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_number(
                    $data['certificate_number'],
                    false
                )
            );
    }


    if (
        array_key_exists(
            'title',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_title(
                    $data['title'],
                    false
                )
            );
    }


    if (
        array_key_exists(
            'description',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_description(
                    $data['description'],
                    false
                )
            );
    }


    /*
     * Status.
     */

    if (
        array_key_exists(
            'status',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_status(
                    $data['status'],
                    false
                )
            );
    }


    /*
     * Dates.
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_dates(
                $data
            )
        );


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Revoke
|--------------------------------------------------------------------------
*/

function certificate_validator_revoke(
    array $data
): array {

    $errors = [];


    $errors =
        array_merge(
            $errors,
            certificate_validator_reason(
                $data['reason'] ?? null,
                true
            )
        );


    if (
        isset($data['revoked_by'])
        &&
        $data['revoked_by'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_user_id(
                    $data['revoked_by'],
                    false
                )
            );
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Certificate Verification
|--------------------------------------------------------------------------
*/

function certificate_validator_verify(
    array $data
): array {

    return certificate_validator_id(
        $data['certificate_id'] ?? null
    );
}


/*
|--------------------------------------------------------------------------
| Validate Search
|--------------------------------------------------------------------------
*/

function certificate_validator_search(
    array $data
): array {

    $errors = [];


    if (
        isset($data['student_id'])
        &&
        $data['student_id'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_student_id(
                    $data['student_id'],
                    false
                )
            );
    }


    if (
        isset($data['training_id'])
        &&
        $data['training_id'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_training_id(
                    $data['training_id'],
                    false
                )
            );
    }


    if (
        isset($data['company_id'])
        &&
        $data['company_id'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_company_id(
                    $data['company_id'],
                    false
                )
            );
    }


    if (
        isset($data['status'])
        &&
        $data['status'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_status(
                    $data['status'],
                    false
                )
            );
    }


    if (
        isset($data['keyword'])
        &&
        strlen(
            trim(
                (string) $data['keyword']
            )
        ) > 255
    ) {

        $errors['keyword'] =
            'Search keyword must not exceed 255 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Appeal
|--------------------------------------------------------------------------
*/

function certificate_validator_appeal(
    array $data
): array {

    $errors = [];


    /*
     * Certificate
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_id(
                $data['certificate_id'] ?? null
            )
        );


    /*
     * Student
     */

    if (
        array_key_exists(
            'student_id',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_student_id(
                    $data['student_id'],
                    false
                )
            );
    }


    /*
     * Reason
     */

    $errors =
        array_merge(
            $errors,
            certificate_validator_reason(
                $data['reason'] ?? null,
                true
            )
        );


    /*
     * Description
     */

    if (
        array_key_exists(
            'description',
            $data
        )
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_description(
                    $data['description'],
                    false
                )
            );
    }


    /*
     * Message
     */

    if (
        array_key_exists(
            'message',
            $data
        )
    ) {

        $message =
            trim(
                (string) $data['message']
            );


        if (
            strlen($message) > 5000
        ) {

            $errors['message'] =
                'Appeal message must not exceed 5000 characters.';
        }
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Appeal Review
|--------------------------------------------------------------------------
*/

function certificate_validator_appeal_review(
    array $data
): array {

    $errors = [];


    if (
        isset($data['reviewed_by'])
        &&
        $data['reviewed_by'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_user_id(
                    $data['reviewed_by'],
                    false
                )
            );
    }


    if (
        isset($data['review_notes'])
        &&
        strlen(
            trim(
                (string) $data['review_notes']
            )
        ) > 5000
    ) {

        $errors['review_notes'] =
            'Review notes must not exceed 5000 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate Appeal Decision
|--------------------------------------------------------------------------
*/

function certificate_validator_appeal_decision(
    array $data
): array {

    $errors = [];

    $decision =
        strtolower(
            trim(
                (string) (
                    $data['decision']
                    ?? ''
                )
            )
        );


    if ($decision === '') {

        $errors['decision'] =
            'Decision is required.';

        return $errors;
    }


    if (
        !in_array(
            $decision,
            [
                'approved',
                'rejected'
            ],
            true
        )
    ) {

        $errors['decision'] =
            'Invalid appeal decision.';
    }


    if (
        isset($data['reviewed_by'])
        &&
        $data['reviewed_by'] !== ''
    ) {

        $errors =
            array_merge(
                $errors,
                certificate_validator_user_id(
                    $data['reviewed_by'],
                    false
                )
            );
    }


    if (
        isset($data['review_notes'])
        &&
        strlen(
            trim(
                (string) $data['review_notes']
            )
        ) > 5000
    ) {

        $errors['review_notes'] =
            'Review notes must not exceed 5000 characters.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Validate ID List
|--------------------------------------------------------------------------
*/

function certificate_validator_id_list(
    $ids
): array {

    $errors = [];


    if (!is_array($ids)) {

        $errors['ids'] =
            'IDs must be provided as an array.';

        return $errors;
    }


    foreach ($ids as $index => $id) {

        if (
            filter_var(
                $id,
                FILTER_VALIDATE_INT
            ) === false
            ||
            (int) $id <= 0
        ) {

            $errors["ids.{$index}"] =
                'Each ID must be a positive integer.';
        }
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Has Errors
|--------------------------------------------------------------------------
*/

function certificate_validator_has_errors(
    array $errors
): bool {

    return !empty($errors);
}


/*
|--------------------------------------------------------------------------
| Validate Create Request
|--------------------------------------------------------------------------
*/

function certificate_validator_validate_create(
    array $data
): array {

    return certificate_validator_create(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Validate Update Request
|--------------------------------------------------------------------------
*/

function certificate_validator_validate_update(
    array $data
): array {

    return certificate_validator_update(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Validate Appeal Request
|--------------------------------------------------------------------------
*/

function certificate_validator_validate_appeal(
    array $data
): array {

    return certificate_validator_appeal(
        $data
    );
}
