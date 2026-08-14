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


/*
|--------------------------------------------------------------------------
| Find Student By ID
|--------------------------------------------------------------------------
*/

function student_repository_find_by_id( int $student_id ): ?array {
    $sql = " SELECT id, user_id, full_name, phone, bio, university_id, faculty_id, degree_id, specialization_id, graduation_year, city, profile_image_file_id, cv_file_id, is_profile_complete, created_at, updated_at FROM students WHERE id = ? LIMIT 1 ";
    $student = db_fetch_one($sql, [$student_id]);

    return is_array($student) ? $student : null;
}

/*
|--------------------------------------------------------------------------
| Find Student By User ID
|--------------------------------------------------------------------------
|
| Every student belongs to exactly one user account.
|
*/

function student_repository_find_by_user_id( int $user_id ): ?array {
    $sql = " SELECT id, user_id, full_name, phone, bio, university_id, faculty_id, degree_id, specialization_id, graduation_year, city, profile_image_file_id, cv_file_id, is_profile_complete, created_at, updated_at FROM students WHERE user_id = ? LIMIT 1 ";
    $student = db_fetch_one($sql, [$user_id]);

    return is_array($student) ? $student : null;
}

/*
|--------------------------------------------------------------------------
| Create Student
|--------------------------------------------------------------------------
*/

function student_repository_create( array $data ): int|false {
    $sql = " INSERT INTO students ( user_id, full_name, university_id, faculty_id, specialization_id, created_at, updated_at ) VALUES ( ?, ?, ?, ?, ?, NOW(), NOW() ) ";
    $success = db_execute( $sql, [
        $data['user_id'] ?? null,
        $data['full_name'] ?? null,
        $data['university_id'] ?? null,
        $data['faculty_id'] ?? null,
        $data['specialization_id'] ?? null,
    ] );

    if (!$success) {
        return false;
    }

    return db_last_insert_id();
}

function student_repository_academic_data_exists(
    int $university_id,
    int $faculty_id,
    int $specialization_id
): bool {
    $sql = "
        SELECT
            u.id AS university_id,
            f.id AS faculty_id,
            s.id AS specialization_id
        FROM universities u
        INNER JOIN faculties f
            ON f.university_id = u.id
        INNER JOIN specializations s
            ON s.id = ?
        WHERE u.id = ?
          AND f.id = ?
          AND u.is_active = 1
          AND f.is_active = 1
          AND s.is_active = 1
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$specialization_id, $university_id, $faculty_id]
    ) !== false;
}

function student_repository_resolve_academic_data(
    string $university,
    string $faculty,
    string $specialization
): ?array {
    $sql = "
        SELECT
            u.id AS university_id,
            f.id AS faculty_id,
            s.id AS specialization_id
        FROM universities u
        INNER JOIN faculties f
            ON f.university_id = u.id
           AND LOWER(TRIM(f.name)) = LOWER(TRIM(?))
        INNER JOIN specializations s
            ON LOWER(TRIM(s.name)) = LOWER(TRIM(?))
        WHERE LOWER(TRIM(u.name)) = LOWER(TRIM(?))
          AND u.is_active = 1
          AND f.is_active = 1
          AND s.is_active = 1
        LIMIT 1
    ";

    $row = db_fetch_one(
        $sql,
        [$faculty, $specialization, $university]
    );

    return is_array($row)
        ? [
            'university_id' => (int) $row['university_id'],
            'faculty_id' => (int) $row['faculty_id'],
            'specialization_id' => (int) $row['specialization_id'],
        ]
        : null;
}


/*
|--------------------------------------------------------------------------
| Update Student
|--------------------------------------------------------------------------
*/

function student_repository_update( int $student_id, array $data ): bool{

    if (empty($data)) {
        return false;
    }

    $allowed_fields = [ 'full_name', 'phone', 'bio', 'university_id', 'faculty_id', 'degree_id', 'specialization_id', 'graduation_year', 'city', 'profile_image_file_id', 'cv_file_id', 'is_profile_complete' ];
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
    return $statement->rowCount() > 0;
}

/*
|--------------------------------------------------------------------------
| Delete Student
|--------------------------------------------------------------------------
|
| This is mainly intended for rollback operations.
|
| Normal account deletion should be handled
| through the user/account lifecycle.
|
*/

function student_repository_delete( int $student_id ): bool {
    $sql = " DELETE FROM students WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$student_id] );
    return $statement->rowCount() > 0;
}

/*
|--------------------------------------------------------------------------
| Check Student Exists
|--------------------------------------------------------------------------
*/

function student_repository_exists( int $student_id ): bool {
    $sql = " SELECT id FROM students WHERE id = ? LIMIT 1 ";
    $student = db_fetch_one( $sql, [$student_id] );
    return $student !== null;
}

/*
|--------------------------------------------------------------------------
| Check User Has Student Profile
|--------------------------------------------------------------------------
*/

function student_repository_user_has_profile( int $user_id ): bool {
    $sql = " SELECT id FROM students WHERE user_id = ? LIMIT 1 ";
    $student = db_fetch_one( $sql, [$user_id] );
    return $student !== null;
}


/*
|--------------------------------------------------------------------------
| Get Students
|--------------------------------------------------------------------------
|
| Used later by:
| - Search
| - Company discovery
| - Admin
|
*/

function student_repository_get_many( int $limit, int $offset ): array {
    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, degree_id, specialization_id, bio, created_at, updated_at FROM students ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    return db_fetch_all( $sql );
}


/*
|--------------------------------------------------------------------------
| Count Students
|--------------------------------------------------------------------------
*/

function student_repository_count(): int
{
    $sql = " SELECT COUNT(*) AS total FROM students ";
    $result = db_fetch_one( $sql );
    return (int) ( $result['total'] ?? 0 );
}


/*
|--------------------------------------------------------------------------
| Search Students By Field
|--------------------------------------------------------------------------
|
| Basic search.
|
| Advanced student/company discovery will later
| be handled by the search module.
|
*/

function student_repository_search_by_field( string $field, int $limit = 20, int $offset = 0 ): array {
    $field = trim($field);

    if ($field === '') {
        return [];
    }

    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, degree_id, specialization_id, bio, created_at, updated_at FROM students WHERE full_name LIKE ? OR bio LIKE ? ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

    return db_fetch_all( $sql, [ '%' . $field . '%', '%' . $field . '%' ] );
}

/*
|--------------------------------------------------------------------------
| Search Students By Specialization
|--------------------------------------------------------------------------
*/

function student_repository_search_by_specialization( string $specialization, int $limit = 20, int $offset = 0 ): array {
    $specialization = trim($specialization);

    if ( $specialization === '' ) {
        return [];
    }

    $sql = " SELECT id, user_id, full_name, university_id, faculty_id, degree_id, specialization_id, bio, created_at, updated_at FROM students WHERE specialization_id = ? ORDER BY id DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    return db_fetch_all( $sql, [ (int) $specialization ] );
}
