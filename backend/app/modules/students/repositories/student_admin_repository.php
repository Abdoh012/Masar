<?php

/**
 * MASAR - Student Admin Repository
 *
 * Responsible only for database operations related to the
 * admin-driven permanent deletion of a student account.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 *
 * Database:
 * students, users, payments, certificates, training_sessions,
 * training_applications, conversations, student_skills, files, companies
 */

require_once __DIR__ . '/../../../core/database/query.php';

function student_admin_repository_find_student( int $student_id ): ?array {
    $sql = " SELECT * FROM students WHERE id = ? LIMIT 1 ";
    $row = db_fetch_one( $sql, [$student_id] );

    return is_array($row) ? $row : null;
}

function student_admin_repository_find_user( int $user_id ): ?array {
    $sql = " SELECT id, role, email, status FROM users WHERE id = ? LIMIT 1 ";
    $row = db_fetch_one( $sql, [$user_id] );

    return is_array($row) ? $row : null;
}

function student_admin_repository_user_owns_company( int $user_id ): bool {
    $sql = " SELECT COUNT(*) AS total FROM companies WHERE user_id = ? ";
    $row = db_fetch_one( $sql, [$user_id] );

    return (int) ( $row['total'] ?? 0 ) > 0;
}

function student_admin_repository_collect_file_paths( int $user_id ): array {
    $sql = " SELECT path FROM files WHERE user_id = ? ";
    $rows = db_fetch_all( $sql, [$user_id] );

    $paths = [];

    foreach ( $rows as $row ) {
        $path = trim( (string) ( $row['path'] ?? '' ) );

        if ( $path !== '' ) {
            $paths[] = $path;
        }
    }

    return $paths;
}

function student_admin_repository_delete_student_owned_rows( int $student_id ): void {
    $tables = [
        'payments',
        'certificates',
        'training_sessions',
        'training_applications',
        'conversations',
        'student_skills',
    ];

    foreach ( $tables as $table ) {
        db_execute( "DELETE FROM `$table` WHERE student_id = ?", [$student_id] );
    }
}

function student_admin_repository_delete_user_tokens( int $user_id ): void {
    $tables = [
        'auth_tokens',
        'refresh_tokens',
        'revoked_access_tokens',
        'verification_tokens',
        'password_resets',
    ];

    foreach ( $tables as $table ) {
        db_execute( "DELETE FROM `$table` WHERE user_id = ?", [$user_id] );
    }
}

function student_admin_repository_delete_student_record( int $student_id ): bool {
    $statement = db_execute( " DELETE FROM students WHERE id = ? LIMIT 1 ", [$student_id] );

    return $statement->rowCount() > 0;
}

function student_admin_repository_delete_user_record( int $user_id ): bool {
    $statement = db_execute( " DELETE FROM users WHERE id = ? LIMIT 1 ", [$user_id] );

    return $statement->rowCount() > 0;
}
