<?php

/**
 * MASAR Validation Rules
 *
 * Contains the actual validation rules used by validator.php.
 */


/*
|--------------------------------------------------------------------------
| Required
|--------------------------------------------------------------------------
*/

function validation_rule_required(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null) {
        return false;
    }

    if (is_string($value) && trim($value) === '') {
        return false;
    }

    if (is_array($value) && empty($value)) {
        return false;
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| String
|--------------------------------------------------------------------------
*/

function validation_rule_string(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return is_string($value);
}


/*
|--------------------------------------------------------------------------
| Integer
|--------------------------------------------------------------------------
*/

function validation_rule_integer(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return filter_var(
        $value,
        FILTER_VALIDATE_INT
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| Numeric
|--------------------------------------------------------------------------
*/

function validation_rule_numeric(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return is_numeric($value);
}


/*
|--------------------------------------------------------------------------
| Boolean
|--------------------------------------------------------------------------
*/

function validation_rule_boolean(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return in_array(
        $value,
        [
            true,
            false,
            0,
            1,
            '0',
            '1',
            'true',
            'false'
        ],
        true
    );
}


/*
|--------------------------------------------------------------------------
| Array
|--------------------------------------------------------------------------
*/

function validation_rule_array(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return is_array($value);
}


/*
|--------------------------------------------------------------------------
| Email
|--------------------------------------------------------------------------
*/

function validation_rule_email(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return filter_var(
        $value,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| Minimum Length / Value
|--------------------------------------------------------------------------
*/

function validation_rule_min(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    $minimum = (int) $parameter;

    if (is_string($value)) {
        return mb_strlen($value) >= $minimum;
    }

    if (is_array($value)) {
        return count($value) >= $minimum;
    }

    if (is_numeric($value)) {
        return $value >= $minimum;
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| Maximum Length / Value
|--------------------------------------------------------------------------
*/

function validation_rule_max(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    $maximum = (int) $parameter;

    if (is_string($value)) {
        return mb_strlen($value) <= $maximum;
    }

    if (is_array($value)) {
        return count($value) <= $maximum;
    }

    if (is_numeric($value)) {
        return $value <= $maximum;
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| Exact Length
|--------------------------------------------------------------------------
*/

function validation_rule_length(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if (!is_string($value)) {
        return false;
    }

    return mb_strlen($value) === (int) $parameter;
}


/*
|--------------------------------------------------------------------------
| In
|--------------------------------------------------------------------------
|
| Example:
|
| in:student,company,admin
|
*/

function validation_rule_in(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if ($parameter === null) {
        return false;
    }

    $allowed = explode(
        ',',
        $parameter
    );

    return in_array(
        (string) $value,
        $allowed,
        true
    );
}


/*
|--------------------------------------------------------------------------
| URL
|--------------------------------------------------------------------------
*/

function validation_rule_url(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return filter_var(
        $value,
        FILTER_VALIDATE_URL
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function validation_rule_date(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if (!is_string($value)) {
        return false;
    }

    $date = DateTime::createFromFormat(
        'Y-m-d',
        $value
    );

    return $date !== false
        && $date->format('Y-m-d') === $value;
}


/*
|--------------------------------------------------------------------------
| DateTime
|--------------------------------------------------------------------------
*/

function validation_rule_datetime(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if (!is_string($value)) {
        return false;
    }

    return strtotime($value) !== false;
}


/*
|--------------------------------------------------------------------------
| Date After
|--------------------------------------------------------------------------
|
| Example:
|
| date_after:start_date
|
*/

function validation_rule_date_after(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if (
        $parameter === null ||
        !isset($data[$parameter])
    ) {
        return false;
    }

    $value_date = strtotime($value);
    $compare_date = strtotime(
        $data[$parameter]
    );

    if (
        $value_date === false ||
        $compare_date === false
    ) {
        return false;
    }

    return $value_date > $compare_date;
}


/*
|--------------------------------------------------------------------------
| Date Before
|--------------------------------------------------------------------------
*/

function validation_rule_date_before(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if (
        $parameter === null ||
        !isset($data[$parameter])
    ) {
        return false;
    }

    $value_date = strtotime($value);
    $compare_date = strtotime(
        $data[$parameter]
    );

    if (
        $value_date === false ||
        $compare_date === false
    ) {
        return false;
    }

    return $value_date < $compare_date;
}


/*
|--------------------------------------------------------------------------
| Same
|--------------------------------------------------------------------------
|
| Example:
|
| same:password
|
*/

function validation_rule_same(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if (
        $parameter === null ||
        !array_key_exists(
            $parameter,
            $data
        )
    ) {
        return false;
    }

    return $value === $data[$parameter];
}


/*
|--------------------------------------------------------------------------
| Different
|--------------------------------------------------------------------------
*/

function validation_rule_different(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if (
        $parameter === null ||
        !array_key_exists(
            $parameter,
            $data
        )
    ) {
        return false;
    }

    return $value !== $data[$parameter];
}


/*
|--------------------------------------------------------------------------
| Regex
|--------------------------------------------------------------------------
|
| Example:
|
| regex:/^[0-9]+$/
|
*/

function validation_rule_regex(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    if ($parameter === null) {
        return false;
    }

    $result = preg_match(
        $parameter,
        (string) $value
    );

    return $result === 1;
}


/*
|--------------------------------------------------------------------------
| Alpha
|--------------------------------------------------------------------------
*/

function validation_rule_alpha(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return preg_match(
        '/^[\p{L}]+$/u',
        (string) $value
    ) === 1;
}


/*
|--------------------------------------------------------------------------
| Alpha Numeric
|--------------------------------------------------------------------------
*/

function validation_rule_alpha_num(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    if ($value === null || $value === '') {
        return true;
    }

    return preg_match(
        '/^[\p{L}\p{N}]+$/u',
        (string) $value
    ) === 1;
}


/*
|--------------------------------------------------------------------------
| Password Confirmation
|--------------------------------------------------------------------------
*/

function validation_rule_confirmed(
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    $confirmation_field =
        $parameter ?? 'password_confirmation';

    if (
        !array_key_exists(
            $confirmation_field,
            $data
        )
    ) {
        return false;
    }

    return $value ===
        $data[$confirmation_field];
}