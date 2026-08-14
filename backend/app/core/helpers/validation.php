<?php

/**
 * MASAR Validation Helper
 *
 * Provides convenient helper functions for working
 * with validation results.
 */


/*
|--------------------------------------------------------------------------
| Load Validator
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../validation/validator.php';


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
|
| Shortcut for validate().
|
*/

function validate_input(
    array $data,
    array $rules
): array {

    return validate(
        $data,
        $rules
    );
}


/*
|--------------------------------------------------------------------------
| Check Input Valid
|--------------------------------------------------------------------------
*/

function input_is_valid(
    array $data,
    array $rules
): bool {

    return is_valid(
        $data,
        $rules
    );
}


/*
|--------------------------------------------------------------------------
| Has Validation Errors
|--------------------------------------------------------------------------
*/

function has_validation_errors(): bool
{
    return validation_fails();
}


/*
|--------------------------------------------------------------------------
| Get All Validation Errors
|--------------------------------------------------------------------------
*/

function get_validation_errors(): array
{
    return validation_errors();
}


/*
|--------------------------------------------------------------------------
| Get Field Validation Errors
|--------------------------------------------------------------------------
*/

function get_field_errors(
    string $field
): array {

    return validation_field_errors(
        $field
    );
}


/*
|--------------------------------------------------------------------------
| Get First Validation Error
|--------------------------------------------------------------------------
*/

function get_first_error(
    string $field
): ?string {

    return validation_first_error(
        $field
    );
}


/*
|--------------------------------------------------------------------------
| Field Has Error
|--------------------------------------------------------------------------
*/

function field_has_error(
    string $field
): bool {

    return validation_has_error(
        $field
    );
}


/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
|
| Useful for simple endpoints where we only
| need to check that specific fields exist.
|
*/

function validate_required_fields(
    array $data,
    array $fields
): array {

    $rules = [];

    foreach ($fields as $field) {

        $rules[$field] = [
            'required'
        ];
    }

    return validate(
        $data,
        $rules
    );
}


/*
|--------------------------------------------------------------------------
| Get Missing Required Fields
|--------------------------------------------------------------------------
*/

function get_missing_fields(
    array $data,
    array $fields
): array {

    $missing = [];

    foreach ($fields as $field) {

        if (
            !array_key_exists(
                $field,
                $data
            )
            ||
            $data[$field] === null
            ||
            (
                is_string($data[$field])
                &&
                trim($data[$field]) === ''
            )
        ) {

            $missing[] = $field;
        }
    }

    return $missing;
}


/*
|--------------------------------------------------------------------------
| Check Required Fields
|--------------------------------------------------------------------------
*/

function has_missing_fields(
    array $data,
    array $fields
): bool {

    return !empty(
        get_missing_fields(
            $data,
            $fields
        )
    );
}


/*
|--------------------------------------------------------------------------
| Sanitize String
|--------------------------------------------------------------------------
|
| This is NOT a replacement for validation.
| SQL injection is prevented through prepared
| statements, not this function.
|
*/

function validation_clean_string(
    mixed $value
): string {

    if ($value === null) {
        return '';
    }

    return trim(
        (string) $value
    );
}


/*
|--------------------------------------------------------------------------
| Normalize Email
|--------------------------------------------------------------------------
*/

function validation_normalize_email(
    mixed $email
): string {

    return strtolower(
        trim(
            (string) $email
        )
    );
}


/*
|--------------------------------------------------------------------------
| Normalize String
|--------------------------------------------------------------------------
*/

function validation_normalize_string(
    mixed $value
): string {

    $value = trim(
        (string) $value
    );

    /*
    |--------------------------------------------------------------------------
    | Normalize Multiple Spaces
    |--------------------------------------------------------------------------
    */

    $value = preg_replace(
        '/\s+/u',
        ' ',
        $value
    );

    return $value ?? '';
}


/*
|--------------------------------------------------------------------------
| Normalize Array Values
|--------------------------------------------------------------------------
*/

function validation_normalize_array(
    mixed $values
): array {

    if (!is_array($values)) {
        return [];
    }

    $result = [];

    foreach ($values as $value) {

        if (
            is_string($value)
        ) {

            $value = trim($value);
        }

        $result[] = $value;
    }

    return $result;
}


/*
|--------------------------------------------------------------------------
| Validate ID
|--------------------------------------------------------------------------
*/

function validation_valid_id(
    mixed $id
): bool {

    return filter_var(
        $id,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| Validate Multiple IDs
|--------------------------------------------------------------------------
*/

function validation_valid_ids(
    mixed $ids
): bool {

    if (!is_array($ids)) {
        return false;
    }

    foreach ($ids as $id) {

        if (!validation_valid_id($id)) {
            return false;
        }
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Validate Enum Value
|--------------------------------------------------------------------------
|
| Example:
|
| validation_valid_enum(
|     $status,
|     ['pending', 'approved', 'rejected']
| );
|
*/

function validation_valid_enum(
    mixed $value,
    array $allowed_values
): bool {

    return in_array(
        $value,
        $allowed_values,
        true
    );
}


/*
|--------------------------------------------------------------------------
| Validate Date Range
|--------------------------------------------------------------------------
|
| Returns true when start date is before
| or equal to end date.
|
*/

function validation_valid_date_range(
    string $start_date,
    string $end_date
): bool {

    $start = strtotime(
        $start_date
    );

    $end = strtotime(
        $end_date
    );

    if (
        $start === false ||
        $end === false
    ) {

        return false;
    }

    return $start <= $end;
}


/*
|--------------------------------------------------------------------------
| Validate Pagination
|--------------------------------------------------------------------------
*/

function validation_pagination(
    mixed $page,
    mixed $per_page
): array {

    $errors = [];

    if (
        !validation_valid_id($page)
    ) {

        $errors['page'][] =
            'Page must be a positive integer.';
    }

    if (
        !validation_valid_id($per_page)
    ) {

        $errors['per_page'][] =
            'Per page must be a positive integer.';
    }

    return $errors;
}
