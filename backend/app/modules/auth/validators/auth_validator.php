<?php

require_once __DIR__ . '/../../../shared/functions/security.php';

/**
 * MASAR - Auth Validator
 *
 * Responsible for validating authentication
 * request data.
 *
 * IMPORTANT:
 * - No database queries here.
 * - No business logic here.
 * - No password hashing here.
 * - Returns validation errors only.
 */


function auth_validate_register( array $data ): array {
    $errors = [];
    $email = trim( $data['email'] ?? '' );

    if ($email === '') {
        $errors['email'][] = 'Email is required.';
    } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL )) {
        $errors['email'][] = 'Please provide a valid email address.';
    }

    $password = $data['password'] ?? '';
    if ($password === '') {
        $errors['password'][] = 'Password is required.';
    } else {
        $password_issues = security_password_strength_errors($password);
        foreach ($password_issues as $issue) {
            $errors['password'][] = $issue;
        }
    }

    $password_confirmation = $data['password_confirmation'] ?? $data['password_confirm'] ?? '';

    if ($password_confirmation === '') {
        $errors['password_confirmation'][] = 'Password confirmation is required.';
    } elseif ($password !== $password_confirmation) {
        $errors['password_confirmation'][] = 'Password confirmation does not match.';
    }

    $termsAccepted = $data['accept_terms'] ?? $data['terms'] ?? null;
    $accepted = false;

    if ($termsAccepted === true || $termsAccepted === 'true' || $termsAccepted === '1' || $termsAccepted === 1 || $termsAccepted === 'on') {
        $accepted = true;
    }

    if (! $accepted) {
        $errors['accept_terms'][] = 'You must agree to the terms of service and privacy policy.';
    }

    $role = strtolower( trim( $data['role'] ?? '' ) );

    if ($role === '') {
        $errors['role'][] = 'Role is required.';
    } elseif ( !in_array( $role, [ USER_ROLE_STUDENT, USER_ROLE_COMPANY ], true ) ) {
        $errors['role'][] = 'Invalid registration role.';
    }

    if ($role === USER_ROLE_STUDENT) {
        $full_name = trim( $data['full_name'] ?? '' );

        if ($full_name === '') {
            $errors['full_name'][] = 'Full name is required for student registration.';
        } elseif ( strlen($full_name) < 2 ) {
            $errors['full_name'][] = 'Full name must be at least 2 characters.';
        } elseif ( strlen($full_name) > 255 ) {
            $errors['full_name'][] = 'Full name must not exceed 255 characters.';
        }

        // User Field: `field` (or legacy `faculty`) by name, or `field_id`.
        $has_field_id =
            isset($data['field_id'])
            && is_numeric($data['field_id'])
            && (int) $data['field_id'] > 0;

        $user_field_key = array_key_exists('field', $data) ? 'field' : 'faculty';
        $user_field = trim((string) ($data[$user_field_key] ?? ''));

        if ($user_field === '' && !$has_field_id) {
            $errors[$user_field_key][] = 'User field is required.';
        } elseif ($user_field !== '' && strlen($user_field) > 255) {
            $errors[$user_field_key][] = 'User field must not exceed 255 characters.';
        }

        // Specialization: by name, or `specialization_id`. It must belong to
        // the selected field; that relationship is enforced in the service.
        $has_specialization_id =
            isset($data['specialization_id'])
            && is_numeric($data['specialization_id'])
            && (int) $data['specialization_id'] > 0;

        $specialization = trim((string) ($data['specialization'] ?? ''));

        if ($specialization === '' && !$has_specialization_id) {
            $errors['specialization'][] = 'Specialization is required.';
        } elseif ($specialization !== '' && strlen($specialization) > 255) {
            $errors['specialization'][] = 'Specialization must not exceed 255 characters.';
        }
    }

    if ($role === 'company') {
        $company_name = trim( $data['company_name'] ?? $data['legal_name'] ?? '' );

        if ($company_name === '') {
            $errors['company_name'][] = 'Company legal name is required for company registration.';
        } elseif ( strlen($company_name) < 2 ) {
            $errors['company_name'][] = 'Company legal name must be at least 2 characters.';
        } elseif ( strlen($company_name) > 255 ) {
            $errors['company_name'][] = 'Company legal name must not exceed 255 characters.';
        }

        // Industry: a specialization ID (int or numeric string), a single
        // specialization name, an array of IDs and/or names, or absent
        // (specialization_ids may be supplied instead). The specialization
        // reference is resolved against the shared specializations lookup
        // table in the service layer.
        $industry = $data['industry'] ?? '';
        $industry_values = is_array($industry) ? $industry : [ $industry ];

        $has_industries = false;

        foreach ($industry_values as $industry_value) {
            if (is_int($industry_value) || (is_string($industry_value) && ctype_digit($industry_value))) {
                if ((int) $industry_value > 0) {
                    $has_industries = true;
                }
                continue;
            }

            if (!is_string($industry_value)) {
                continue;
            }

            if (trim($industry_value) !== '') {
                $has_industries = true;
            }

            if (strlen(trim($industry_value)) > 255) {
                $errors['industry'][] = 'Each industry must not exceed 255 characters.';
                break;
            }
        }

        $has_work_fields =
            array_key_exists('work_field_ids', $data)
            ||
            $has_industries;

        // Transition: specialization_ids (company_specializations) may be
        // supplied instead of work fields during company registration.
        $has_specialization_ids =
            array_key_exists('specialization_ids', $data)
            && is_array($data['specialization_ids']);

        if (!$has_work_fields && !$has_specialization_ids) {
            $errors['industry'][] = 'Industry is required for company registration.';
        } else {
            if ( array_key_exists('work_field_ids', $data) ) {
                if ( !is_array($data['work_field_ids']) || empty($data['work_field_ids']) ) {
                    $errors['work_field_ids'][] = 'Work fields must be a non-empty array of study field IDs.';
                } else {
                    foreach ($data['work_field_ids'] as $field_id) {
                        if ( !is_numeric($field_id) || (int) $field_id <= 0 ) {
                            $errors['work_field_ids'][] = 'Each work field must be a positive study field ID.';
                            break;
                        }
                    }
                }
            }
        }
    }

    return $errors;
}

/*
|--------------------------------------------------------------------------
| Login Validation
|--------------------------------------------------------------------------
*/

function auth_validate_login( array $data ): array {
    $errors = [];
    $email = trim( $data['email'] ?? '' );

    if ($email === '') {
        $errors['email'][] = 'Email is required.';
    } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        $errors['email'][] = 'Please provide a valid email address.';
    }

    $password = $data['password'] ?? '';

    if ($password === '') {
        $errors['password'][] = 'Password is required.';
    }

    return $errors;
}


/*
|--------------------------------------------------------------------------
| Change Password Validation
|--------------------------------------------------------------------------
*/

function auth_validate_change_password( array $data ): array {
    $errors = [];

    $current_password = $data['current_password'] ?? '';

    if ($current_password === '') {
        $errors['current_password'][] = 'Current password is required.';
    }

    $new_password = $data['new_password'] ?? '';

    if ($new_password === '') {
        $errors['new_password'][] = 'New password is required.';
    } else {
        $password_issues = security_password_strength_errors($new_password);
        foreach ($password_issues as $issue) {
            $errors['new_password'][] = $issue;
        }
    }

    $confirmation = $data['new_password_confirmation'] ?? '';

    if ($confirmation === '') {
        $errors['new_password_confirmation'][] = 'Password confirmation is required.';
    } elseif ( $new_password !== $confirmation ) {
        $errors['new_password_confirmation'][] = 'Password confirmation does not match.';
    }

    return $errors;
}

function auth_validate_forgot_password( array $data ): array {
    $errors = [];
    $email = trim( $data['email'] ?? '' );

    if ($email === '') {
        $errors['email'][] = 'Email is required.';
    } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        $errors['email'][] = 'Please provide a valid email address.';
    }

    return $errors;
}

function auth_validate_reset_password( array $data ): array {
    $errors = [];
    $email = trim( $data['email'] ?? '' );

    if ($email === '') {
        $errors['email'][] = 'Email is required.';
    } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        $errors['email'][] = 'Please provide a valid email address.';
    }

    $token = trim( $data['token'] ?? '' );
    if ($token === '') {
        $errors['token'][] = 'Reset token is required.';
    }

    $password = $data['password'] ?? $data['new_password'] ?? '';
    if ($password === '') {
        $errors['password'][] = 'Password is required.';
    } elseif ( strlen($password) < 8 ) {
        $errors['password'][] = 'Password must be at least 8 characters.';
    }

    $confirmation = $data['password_confirmation'] ?? $data['new_password_confirmation'] ?? '';
    if ($confirmation === '') {
        $errors['password_confirmation'][] = 'Password confirmation is required.';
    } elseif ($password !== $confirmation) {
        $errors['password_confirmation'][] = 'Password confirmation does not match.';
    }

    return $errors;
}

function auth_validate_verify_reset_otp( array $data ): array {
    $errors = [];
    $email = trim( $data['email'] ?? '' );

    if ($email === '') {
        $errors['email'][] = 'Email is required.';
    } elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        $errors['email'][] = 'Please provide a valid email address.';
    }

    $token = trim( $data['token'] ?? '' );
    if ($token === '') {
        $errors['token'][] = 'OTP token is required.';
    }

    return $errors;
}
