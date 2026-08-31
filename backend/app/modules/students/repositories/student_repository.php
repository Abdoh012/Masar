<?php

/**
 * MASAR - Student Repository
 *
 * Responsible only for database operations related
 * to the students table.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 *
 * Database:
 * students
 */

require_once __DIR__ . '/../../../core/database/query.php';

function student_repository_find_by_id( int $student_id ): ?array {
    $sql = " SELECT id, user_id, full_name, phone, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, city, profile_image_file_id, cv_file_id, is_profile_complete, created_at, updated_at FROM students WHERE id = ? LIMIT 1 ";
    $student = db_fetch_one($sql, [$student_id]);

    return is_array($student) ? $student : null;
}

function student_repository_find_by_user_id( int $user_id ): ?array {
    $sql = " SELECT id, user_id, full_name, phone, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, city, profile_image_file_id, cv_file_id, is_profile_complete, created_at, updated_at FROM students WHERE user_id = ? LIMIT 1 ";
    $student = db_fetch_one($sql, [$user_id]);

    return is_array($student) ? $student : null;
}

function student_repository_create( array $data ): int|false {
    $sql = " INSERT INTO students ( user_id, full_name, university_id, faculty_id, field_id, specialization_id, degree_id, bio, phone, city, graduation_year, cv_file_id, created_at, updated_at ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() ) ";
    $success = db_execute( $sql, [
        $data['user_id'] ?? null,
        $data['full_name'] ?? null,
        $data['university_id'] ?? null,
        $data['faculty_id'] ?? null,
        $data['field_id'] ?? null,
        $data['specialization_id'] ?? null,
        $data['degree_id'] ?? null,
        $data['bio'] ?? null,
        $data['phone'] ?? null,
        $data['city'] ?? null,
        $data['graduation_year'] ?? null,
        $data['cv_file_id'] ?? null,
    ] );

    if (!$success) {
        return false;
    }

    return db_last_insert_id();
}

function student_repository_resolve_field_id( string $field ): ?int {
    $sql = " SELECT id FROM study_fields WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$field] );

    return is_array($row) ? (int) $row['id'] : null;
}

function student_repository_find_field_by_id( int $field_id ): ?array {
    if ($field_id <= 0) {
        return null;
    }

    $sql = " SELECT id, name FROM study_fields WHERE id = ? AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$field_id] );

    return is_array($row) ? $row : null;
}

function student_repository_resolve_specialization_id( string $specialization ): ?int {
    $sql = " SELECT id FROM specializations WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$specialization] );

    return is_array($row) ? (int) $row['id'] : null;
}

/**
 * Resolves a specialization by name scoped to the given study field.
 * The field -> specialization relationship (specializations.field_id)
 * is enforced: a specialization only resolves when it belongs to the
 * selected active study field.
 */
function student_repository_resolve_specialization_id_in_field( string $specialization, int $field_id ): ?int {
    if ($field_id <= 0) {
        return null;
    }

    $sql = " SELECT id FROM specializations WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND field_id = ? AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$specialization, $field_id] );

    return is_array($row) ? (int) $row['id'] : null;
}

function student_repository_find_specialization_by_id( int $specialization_id ): ?array {
    if ($specialization_id <= 0) {
        return null;
    }

    $sql = " SELECT id, name, field_id FROM specializations WHERE id = ? AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$specialization_id] );

    return is_array($row) ? $row : null;
}

function student_repository_resolve_degree_id( string $degree ): ?int {
    $sql = " SELECT id FROM degrees WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$degree] );

    return is_array($row) ? (int) $row['id'] : null;
}

function student_repository_resolve_university_id( string $university ): ?int {
    $sql = " SELECT id FROM universities WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ";
    $row = db_fetch_one( $sql, [$university] );

    return is_array($row) ? (int) $row['id'] : null;
}

function student_repository_update( int $student_id, array $data ): bool{
    if (empty($data)) {
        return false;
    }

    $allowed_fields = [ 'full_name', 'phone', 'bio', 'university_id', 'faculty_id', 'field_id', 'degree_id', 'specialization_id', 'graduation_year', 'city', 'profile_image_file_id', 'cv_file_id', 'is_profile_complete' ];
    $fields = [];
    $values = [];

    foreach ( $allowed_fields as $field ) {
        if ( array_key_exists( $field, $data ) ) {
            $fields[] = $field . ' = ?';
            $values[] = $data[$field];
        }
    }

    if (empty($fields)) {
        return false;
    }

    $fields[] = 'updated_at = NOW()';
    $values[] = $student_id;

    $sql = " UPDATE students SET " . implode( ', ', $fields ) . " WHERE id = ? LIMIT 1 ";

    $statement = db_execute( $sql, $values );
    return true;
}

function student_repository_delete( int $student_id ): bool {
    $sql = " DELETE FROM students WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$student_id] );
    return $statement->rowCount() > 0;
}

function student_repository_exists( int $student_id ): bool {
    $sql = " SELECT id FROM students WHERE id = ? LIMIT 1 ";
    $student = db_fetch_one( $sql, [$student_id] );
    return $student !== null;
}

function student_repository_user_has_profile( int $user_id ): bool {
    $sql = " SELECT id FROM students WHERE user_id = ? LIMIT 1 ";
    $student = db_fetch_one( $sql, [$user_id] );
    return $student !== null;
}

function student_repository_get_many( int $limit, int $offset ): array {
    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, field_id, degree_id, specialization_id, bio, created_at, updated_at FROM students ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    return db_fetch_all( $sql );
}

function student_repository_count(): int
{
    $sql = " SELECT COUNT(*) AS total FROM students ";
    $result = db_fetch_one( $sql );
    return (int) ( $result['total'] ?? 0 );
}

function student_repository_search_by_field( string $field, int $limit = 20, int $offset = 0 ): array {
    $field = trim($field);

    if ($field === '') {
        return [];
    }

    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, field_id, degree_id, specialization_id, bio, created_at, updated_at FROM students WHERE full_name LIKE ? OR bio LIKE ? ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    return db_fetch_all( $sql, [ '%' . $field . '%', '%' . $field . '%' ] );
}

function student_repository_search_by_specialization( string $specialization, int $limit = 20, int $offset = 0 ): array {
    $specialization = trim($specialization);

    if ( $specialization === '' ) {
        return [];
    }

    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, field_id, degree_id, specialization_id, bio, created_at, updated_at FROM students WHERE specialization_id = ? ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    return db_fetch_all( $sql, [ (int) $specialization ] );
}
