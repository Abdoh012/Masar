<?php

/**
 * MASAR - User Validator
 *
 * Responsible for validating general user account
 * update requests.
 *
 * IMPORTANT:
 * - No database queries.
 * - No business logic.
 * - No authentication logic.
 */

require_once __DIR__ . '/../../../shared/functions/email.php';

function user_validate_update( array $data ): array {

    $errors = [];

    if ( array_key_exists( 'email', $data ) ) {
        if ( !is_string( $data['email'] ) ) {
            $errors['email'][] = 'Email must be a valid string.';
        } else {
            $email = trim( $data['email'] );

            if ($email === '') {
                $errors['email'][] = 'Email cannot be empty.';
            } elseif ( strlen($email) < 5 ) {
                $errors['email'][] = 'Email must be at least 5 characters long.';
            } elseif ( strlen($email) > 254 ) {
                $errors['email'][] = 'Email must not exceed 254 characters.';
            } elseif ( !is_valid_email( $email ) ) {
                $errors['email'][] = 'Please provide a valid email address.';
            } elseif ( strpos( $email, '..' ) !== false ) {
                $errors['email'][] = 'Email cannot contain consecutive dots.';
            } elseif ( preg_match( '/^\\.|.*\\.@|@\\./', $email ) ) {
                $errors['email'][] = 'Email format is invalid (dot placement).';
            } elseif ( preg_match( '/\\s/', $email ) ) {
                $errors['email'][] = 'Email cannot contain whitespace.';
            } elseif ( !preg_match( '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $email ) ) {
                $errors['email'][] = 'Email contains invalid characters.';
            }
        }
    }

    // NOTE: full_name is NOT a column of the users table. It lives in the
    // role-specific profiles (students.full_name / companies.legal_name) and is
    // updated through the students/companies modules. It is intentionally NOT
    // allowed here, otherwise updating the profile name would attempt a write to a
    // non-existent users.full_name column and fail with a server error.

    $allowed_fields = [ 'email', ];

    foreach ( array_keys($data) as $field ) {
        if  (!in_array( $field, $allowed_fields, true ) ) {
            $ignored_fields = [ 'token', 'access_token', ];
            if ( !in_array( $field, $ignored_fields, true ) ) {
                $errors[$field][] = 'This field cannot be updated here.';
            }
        }
    }

    // Prevent manipulation of critical fields
    $protected_fields = [ 'id', 'role', 'status', 'password', 'password_hash', 'remember_token', 'verification_token', 'created_at', 'updated_at', ];
    foreach ( $protected_fields as $protected_field ) {
        if ( array_key_exists( $protected_field, $data ) ) {
            $errors[$protected_field][] = 'This field is protected and cannot be modified.';
        }
    }

    return $errors;
}
