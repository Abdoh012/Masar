<?php

/**
 * MASAR Validation Helper
 *
 * Responsible for validating input data using
 * validation rules defined in rules.php.
 */


/*
|--------------------------------------------------------------------------
| Load Validation Rules
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/rules.php';


/*
|--------------------------------------------------------------------------
| Validation Errors
|--------------------------------------------------------------------------
*/

$GLOBALS['validation_errors'] = [];


/*
|--------------------------------------------------------------------------
| Validate Data
|--------------------------------------------------------------------------
|
| Example:
|
| $errors = validate($data, [
|     'email' => ['required', 'email'],
|     'password' => ['required', 'min:8'],
| ]);
|
*/

function validate(
    array $data,
    array $rules
): array {

    $errors = [];

    foreach ($rules as $field => $field_rules) {

        /*
        |--------------------------------------------------------------------------
        | Normalize Rules
        |--------------------------------------------------------------------------
        */

        if (is_string($field_rules)) {

            $field_rules = explode(
                '|',
                $field_rules
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Field Value
        |--------------------------------------------------------------------------
        */

        $value = $data[$field] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Run Rules
        |--------------------------------------------------------------------------
        */

        foreach ($field_rules as $rule) {

            $rule_name = $rule;
            $rule_parameter = null;

            /*
            |--------------------------------------------------------------------------
            | Parse Rule Parameter
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | min:8
            | max:255
            |
            */

            if (str_contains($rule, ':')) {

                [
                    $rule_name,
                    $rule_parameter
                ] = explode(
                    ':',
                    $rule,
                    2
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Check Rule Exists
            |--------------------------------------------------------------------------
            */

            if (!validation_rule_exists($rule_name)) {

                $errors[$field][] =
                    "Unknown validation rule: {$rule_name}";

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Execute Rule
            |--------------------------------------------------------------------------
            */

            $result = validation_apply_rule(
                $rule_name,
                $value,
                $rule_parameter,
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | Rule Failed
            |--------------------------------------------------------------------------
            */

            if ($result !== true) {

                $errors[$field][] =
                    is_string($result)
                        ? $result
                        : validation_rule_message(
                            $rule_name,
                            $field,
                            $rule_parameter
                        );
            }

            /*
            |--------------------------------------------------------------------------
            | Stop On Required Failure
            |--------------------------------------------------------------------------
            */

            if (
                $rule_name === 'required'
                && $result !== true
            ) {
                break;
            }
        }
    }

    $GLOBALS['validation_errors'] = $errors;

    return $errors;
}


/*
|--------------------------------------------------------------------------
| Check Validation
|--------------------------------------------------------------------------
*/

function is_valid(
    array $data,
    array $rules
): bool {

    return empty(
        validate(
            $data,
            $rules
        )
    );
}


/*
|--------------------------------------------------------------------------
| Check Has Validation Errors
|--------------------------------------------------------------------------
*/

function validation_fails(): bool
{
    return !empty(
        $GLOBALS['validation_errors']
    );
}


/*
|--------------------------------------------------------------------------
| Get Validation Errors
|--------------------------------------------------------------------------
*/

function validation_errors(): array
{
    return $GLOBALS['validation_errors'];
}


/*
|--------------------------------------------------------------------------
| Get Field Errors
|--------------------------------------------------------------------------
*/

function validation_field_errors(
    string $field
): array {

    return $GLOBALS['validation_errors'][$field]
        ?? [];
}


/*
|--------------------------------------------------------------------------
| Get First Field Error
|--------------------------------------------------------------------------
*/

function validation_first_error(
    string $field
): ?string {

    $errors =
        validation_field_errors($field);

    return $errors[0] ?? null;
}


/*
|--------------------------------------------------------------------------
| Check Field Has Error
|--------------------------------------------------------------------------
*/

function validation_has_error(
    string $field
): bool {

    return !empty(
        validation_field_errors($field)
    );
}


/*
|--------------------------------------------------------------------------
| Apply Validation Rule
|--------------------------------------------------------------------------
*/

function validation_apply_rule(
    string $rule,
    mixed $value,
    ?string $parameter = null,
    array $data = []
): bool|string {

    $function_name =
        'validation_rule_' . $rule;

    if (!function_exists($function_name)) {

        return "Validation rule '{$rule}' does not exist.";
    }

    return $function_name(
        $value,
        $parameter,
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Check Rule Exists
|--------------------------------------------------------------------------
*/

function validation_rule_exists(
    string $rule
): bool {

    return function_exists(
        'validation_rule_' . $rule
    );
}


/*
|--------------------------------------------------------------------------
| Get Rule Message
|--------------------------------------------------------------------------
*/

function validation_rule_message(
    string $rule,
    string $field,
    ?string $parameter = null
): string {

    $messages = [

        'required' =>
            "{$field} is required.",

        'email' =>
            "{$field} must be a valid email address.",

        'string' =>
            "{$field} must be a string.",

        'integer' =>
            "{$field} must be an integer.",

        'numeric' =>
            "{$field} must be a number.",

        'boolean' =>
            "{$field} must be true or false.",

        'array' =>
            "{$field} must be an array.",

        'min' =>
            "{$field} must be at least {$parameter}.",

        'max' =>
            "{$field} must not exceed {$parameter}.",

        'length' =>
            "{$field} must contain exactly {$parameter} characters.",

        'in' =>
            "{$field} contains an invalid value.",

        'url' =>
            "{$field} must be a valid URL.",

        'date' =>
            "{$field} must be a valid date.",

        'date_after' =>
            "{$field} must be after {$parameter}.",

        'date_before' =>
            "{$field} must be before {$parameter}.",

        'same' =>
            "{$field} must match {$parameter}.",

        'different' =>
            "{$field} must be different from {$parameter}.",
    ];

    return $messages[$rule]
        ?? "{$field} is invalid.";
}