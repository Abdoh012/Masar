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
