<?php

/**
 * MASAR - User Repository
 *
 * Responsible for database operations related
 * to the general users table.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 */

require_once __DIR__ . '/../../../core/database/query.php';

function user_repository_find_by_id( int $user_id ): ?array {
    $sql = "SELECT id, email, role, status, created_at, updated_at, last_login_at FROM users WHERE id = ? LIMIT 1";
    return db_fetch_one( $sql, [$user_id] );
}

function user_repository_email_exists_for_other_user( string $email, int $user_id ): bool {
    $sql = " SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1 ";
    $user = db_fetch_one( $sql, [ $email, $user_id ] );
    return $user !== null;
}

/*
|--------------------------------------------------------------------------
| Public Username
|--------------------------------------------------------------------------
|
| The public handle (users.username). It is a separate identity from the
| account email, so it has its own read / uniqueness / write helpers rather
| than being folded into the generic user update.
|
| Uniqueness is checked with LOWER() on both sides so the service reports a
| clean conflict for `Nour` vs `nour`. That is belt-and-braces: the column's
| collation is already case-insensitive, so the UNIQUE index would reject it
| too. The service check exists to return a 409 with a useful message instead
| of letting the index raise a driver error.
|
*/

function user_repository_find_username( int $user_id ): ?string {

    if ( $user_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one( " SELECT username FROM users WHERE id = ? LIMIT 1 ", [ $user_id ] );

    if ( !is_array( $row ) || !array_key_exists( 'username', $row ) ) {
        return null;
    }

    $username = $row['username'];

    if ( $username === null || trim( (string) $username ) === '' ) {
        return null;
    }

    return trim( (string) $username );
}

function user_repository_username_exists( string $username, int $except_user_id = 0 ): bool {

    $sql = " SELECT id FROM users WHERE LOWER(username) = LOWER(?) ";

    $params = [ $username ];

    if ( $except_user_id > 0 ) {
        $sql .= " AND id != ? ";
        $params[] = $except_user_id;
    }

    $sql .= " LIMIT 1 ";

    return db_fetch_one( $sql, $params ) !== null;
}

function user_repository_update_username( int $user_id, string $username ): bool {

    if ( $user_id <= 0 || trim( $username ) === '' ) {
        return false;
    }

    db_execute(
        " UPDATE users SET username = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $username, $user_id ]
    );

    return true;
}

/*
|--------------------------------------------------------------------------
| Contact Details
|--------------------------------------------------------------------------
|
| Reads and writes for the account's own email address. There is no separate
| contact-email column: users.email is the account identity, so it is reused
| rather than duplicated.
|
*/

function user_repository_find_email( int $user_id ): ?array {

    if ( $user_id <= 0 ) {
        return null;
    }

    return db_fetch_one( " SELECT id, email FROM users WHERE id = ? LIMIT 1 ", [ $user_id ] );
}

function user_repository_update_email( int $user_id, string $email ): bool {

    if ( $user_id <= 0 || trim( $email ) === '' ) {
        return false;
    }

    db_execute(
        " UPDATE users SET email = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $email, $user_id ]
    );

    return true;
}

/*
| A changed address has not been proven, so the existing verification stamp is
| cleared. This reuses the verification model rather than bypassing it: the
| account keeps authenticating on its password, but the new address is no
| longer treated as verified.
*/
function user_repository_clear_email_verified_at( int $user_id ): bool {

    if ( $user_id <= 0 ) {
        return false;
    }

    db_execute(
        " UPDATE users SET email_verified_at = NULL, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $user_id ]
    );

    return true;
}

function user_repository_update( int $user_id, array $data ): bool {

    if (empty($data)) {
        return false;
    }

    // NOTE: full_name is NOT a users table column (it lives in the role-specific
    // profiles). Only email is updatable at the users level.
    $allowed_fields = [ 'email', ];
    $fields = [];
    $values = [];

    foreach ($allowed_fields as $field) {
        if ( array_key_exists( $field, $data ) ) {
            // Validate that data doesn't contain null values unexpectedly
            if ( $data[$field] === null || (is_string($data[$field]) && trim($data[$field]) === '') ) {
                return false;
            }
            $fields[] = $field . ' = ?';
            $values[] = $data[$field];
        }
    }

    if (empty($fields)) {
        return false;
    }

    $fields[] = 'updated_at = NOW()';
    $values[] = $user_id;
    $sql = " UPDATE users SET " . implode( ', ', $fields ) . " WHERE id = ? LIMIT 1 ";

    $result = db_execute( $sql, $values );
    
    // Verify update was successful by checking affected rows
    if (!$result) {
        return false;
    }
    
    return true;
}

function user_repository_deactivate( int $user_id ): bool {
    
    // Validate user exists first
    $user = user_repository_find_by_id( $user_id );
    if (!$user) {
        return false;
    }
    
    // Prevent deactivating already deleted users
    if (isset($user['status']) && $user['status'] === 'deleted') {
        return false;
    }
    
    $sql = " UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$user_id] );
    return $statement->rowCount() > 0;
}

function user_repository_update_status( int $user_id, string $status ): bool {

    $allowed_statuses = [ 'active', 'inactive', 'suspended', 'deleted', ];

    if ( !in_array( $status, $allowed_statuses, true ) ) {
        return false;
    }

    // Validate user exists
    $user = user_repository_find_by_id( $user_id );
    if (!$user) {
        return false;
    }

    // Prevent status transitions on already deleted users
    if (isset($user['status']) && $user['status'] === 'deleted' && $status !== 'deleted') {
        return false;
    }

    $sql = " UPDATE users SET status = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ";

    $statement = db_execute( $sql, [ $status, $user_id ] );
    return $statement->rowCount() > 0;
}

function user_repository_count( ?string $role = null, ?string $status = null ): int {

    $conditions = [];
    $params = [];

    if ($role !== null) {
        $conditions[] = 'role = ?';
        $params[] = $role;
    }

    if ($status !== null) {
        $conditions[] = 'status = ?';
        $params[] = $status;
    }

    $sql = " SELECT COUNT(*) AS total FROM users ";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode( ' AND ', $conditions );
    }

    $result = db_fetch_one( $sql, $params );

    return (int) ( $result['total'] ?? 0 );
}

function user_repository_get_many( int $limit, int $offset, ?string $role = null, ?string $status = null ): array {

    $conditions = [];
    $params = [];

    if ($role !== null) {
        $conditions[] = 'role = ?';
        $params[] = $role;
    }

    if ($status !== null) {
        $conditions[] = 'status = ?';
        $params[] = $status;
    }

    $sql =  "SELECT id, email, role, status, created_at, updated_at, last_login_at FROM users ";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode( ' AND ', $conditions );
    }

    $sql .= " ORDER BY id DESC ";
    $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

    return db_fetch_all( $sql, $params );
}
