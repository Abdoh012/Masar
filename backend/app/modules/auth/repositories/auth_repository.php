<?php

/**
 * MASAR - Auth Repository
 *
 * Responsible for authentication-related
 * database operations.
 *
 * IMPORTANT:
 * - No business logic here.
 * - No HTTP handling here.
 * - No validation here.
 * - Uses the database/query layer.
 */



require_once __DIR__ . '/../../../core/database/query.php';

function auth_repository_email_exists( string $email ): bool {
    $sql = " SELECT id FROM users WHERE email = ? LIMIT 1 ";
    $user = db_fetch_one( $sql, [$email] );
    return $user !== null;
}

function auth_repository_find_user_by_email( string $email ): ?array {
    $sql = " SELECT id, email, password_hash, role, status, email_verified_at, created_at, updated_at, last_login_at FROM users WHERE email = ? LIMIT 1 ";
    return db_fetch_one( $sql, [$email] );
}

function auth_repository_find_user_by_id( int $user_id ): ?array {
    $sql = " SELECT id, email, password_hash, role, status, email_verified_at, created_at, updated_at, last_login_at FROM users WHERE id = ? LIMIT 1 ";
    return db_fetch_one( $sql, [$user_id] );
}

function auth_repository_create_user( array $data ): int | false {
    $sql = " INSERT INTO users ( email, password_hash, role, status, created_at, updated_at ) VALUES ( ?, ?, ?, ?, NOW(), NOW() ) ";
    $result = db_execute( $sql, [ $data['email'], $data['password_hash'], $data['role'], $data['status'], ] );

    if (!$result) {
        return false;
    }

    return db_last_insert_id();
}

function auth_repository_delete_user( int $user_id ): bool {
    $sql = " DELETE FROM users WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$user_id] );
    return db_row_count($statement) > 0;
}

function auth_repository_create_company_approval( int $user_id ): bool {
    $sql = " INSERT INTO company_approvals ( user_id, status, created_at, updated_at ) VALUES ( ?, 'pending', NOW(), NOW() ) ";
    $statement = db_execute( $sql, [$user_id] );
    return db_row_count($statement) > 0;
}

function auth_repository_update_last_login( int $user_id ): bool {
    $sql = " UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$user_id] );
    return db_row_count($statement) > 0;
}

function auth_repository_update_password( int $user_id, string $password_hash ): bool {
    $sql = " UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [ $password_hash, $user_id ] );
    return db_row_count($statement) > 0;
}

function auth_repository_update_status( int $user_id, string $status ): bool {
    $allowed_statuses = [ USER_STATUS_ACTIVE, USER_STATUS_PENDING, USER_STATUS_SUSPENDED, USER_STATUS_REJECTED, USER_STATUS_DELETED ];

    if (! in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $sql = " UPDATE users SET status = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [ $status, $user_id ] );
    return db_row_count($statement) > 0;
}


/*
|--------------------------------------------------------------------------
| Update User Email
|--------------------------------------------------------------------------
*/

function auth_repository_update_email( int $user_id, string $new_email ): bool {

    $new_email = strtolower(trim($new_email));

    $sql = " UPDATE users SET email = ?, email_verified_at = NULL, updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [ $new_email, $user_id ] );
    return db_row_count($statement) > 0;
}


/*
|--------------------------------------------------------------------------
| Verification Token Methods
|--------------------------------------------------------------------------
*/

function auth_repository_set_email_verified_at( int $user_id ): bool {

    $sql = " UPDATE users SET email_verified_at = NOW( ), updated_at = NOW( ) WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [ $user_id ] );
    return db_row_count($statement) > 0;
}


/*
|--------------------------------------------------------------------------
| Verification Token Methods
|--------------------------------------------------------------------------
*/

function auth_repository_create_verification_token( int $user_id ): string {

    $plain_token = token_generate();
    $token_hash = token_hash( $plain_token );
    $expires_seconds = 60 * 60 * 24; // 24 hours
    $expires_at = date( 'Y-m-d H:i:s', time() + $expires_seconds );

    $sql = " INSERT INTO verification_tokens ( user_id, token_hash, expires_at, created_at ) VALUES ( ?, ?, NOW(), NOW() ) ";
    db_execute( $sql, [ $user_id, $token_hash ] );

    return $plain_token;
}

function auth_repository_find_verification_token( string $token_hash ): ?array {

    $sql = " SELECT id, user_id, token_hash, expires_at, used_at FROM verification_tokens WHERE token_hash = ? LIMIT 1 ";
    return db_fetch_one( $sql, [ $token_hash ] );
}

function auth_repository_mark_verification_used( int $token_id ): bool {

    $sql = " UPDATE verification_tokens SET used_at = NOW() WHERE id = ? AND used_at IS NULL ";
    $statement = db_execute( $sql, [ $token_id ] );
    return db_row_count($statement) > 0;
}
