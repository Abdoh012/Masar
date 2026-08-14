<?php

/**
 * MASAR Password Helper
 *
 * Responsible for password hashing, verification,
 * password generation and password strength validation.
 */

function password_hash_value(string $password): string
{
    return password_hash( $password, PASSWORD_DEFAULT );
}

function password_verify_value( string $password, string $password_hash ): bool {

    return password_verify( $password, $password_hash );
}

function password_validate( string $password ): array {

    $errors = [];

    if ($password === '') {
        $errors[] = 'Password is required.';
        return $errors;
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (strlen($password) > 72) {
        $errors[] = 'Password must not exceed 72 characters.';
    }

    return $errors;
}

function password_is_valid( string $password ): bool {
    return empty( password_validate($password) );
}

function password_change( int $user_id, string $current_password, string $new_password ): bool {

    require_once __DIR__ . '/../database/query.php';

    if (!password_is_valid($new_password)) {
        throw new InvalidArgumentException( implode( ' ', password_validate($new_password) ) );
    }

    $user = db_fetch_one( ' SELECT id, password_hash FROM users WHERE id = :id LIMIT 1 ', [ 'id' => $user_id ] );

    if ($user === null) {
        throw new RuntimeException( 'User not found.' );
    }

    if ( !password_verify_value( $current_password, $user['password_hash'] ) ) {
        throw new RuntimeException( 'Current password is incorrect.' );
    }

    if ( password_verify_value( $new_password, $user['password_hash'] ) ) {
        throw new InvalidArgumentException( 'New password must be different from the current password.' );
    }

    $new_hash = password_hash_value(  $new_password );
    $statement = db_execute(' UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id ', [ 'password_hash' => $new_hash, 'id' => $user_id ] );

    return $statement->rowCount() > 0;
}

function password_set( int $user_id, string $new_password ): bool {

    require_once __DIR__ . '/../database/query.php';
    $errors = password_validate( $new_password );

    if (!empty($errors)) {
        throw new InvalidArgumentException( implode( ' ', $errors ) );
    }

    $password_hash = password_hash_value( $new_password );
    $statement = db_execute(' UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id ', [ 'password_hash' => $password_hash, 'id' => $user_id ] );

    return $statement->rowCount() > 0;
}

function password_generate_temporary( int $length = 16 ): string {

    if ($length < 8) {
        $length = 8;
    }

    $characters ='ABCDEFGHJKLMNPQRSTUVWXYZ' . 'abcdefghijkmnopqrstuvwxyz' . '23456789';
    $characters_length = strlen($characters);
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[ random_int( 0, $characters_length - 1 ) ];
    }

    return $password;
}