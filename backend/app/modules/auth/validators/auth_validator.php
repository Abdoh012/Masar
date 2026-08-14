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

        foreach (['university', 'faculty', 'specialization'] as $academic_field) {
            $academic_value = trim((string) ($data[$academic_field] ?? ''));
            if ($academic_value === '') {
                $errors[$academic_field][] = ucfirst($academic_field) . ' is required.';
            } elseif (strlen($academic_value) > 255) {
                $errors[$academic_field][] = ucfirst($academic_field) . ' must not exceed 255 characters.';
            }
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

        $industry = trim( $data['industry'] ?? '' );

        if ($industry === '') {
            $errors['industry'][] = 'Industry is required for company registration.';
        } elseif ( strlen($industry) < 2 ) {
            $errors['industry'][] = 'Industry must be at least 2 characters.';
        } elseif ( strlen($industry) > 255 ) {
            $errors['industry'][] = 'Industry must not exceed 255 characters.';
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
