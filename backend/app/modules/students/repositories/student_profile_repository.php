<?php

/**
 * MASAR - Student Profile Repository
 *
 * Responsible only for database operations related
 * to the extended student profile.
 *
 * Responsibilities:
 * - Read student profile data.
 * - Update profile data.
 * - Manage skills.
 * - Manage CV file reference.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No file upload handling.
 */

require_once __DIR__ . '/../../../core/database/query.php';

function student_profile_repository_sync_skills( PDO $db, int $student_id, array $skills ): bool {

    $names = [];
    foreach ($skills as $skill) {
        if ( is_string($skill) || is_numeric($skill) ) {
            $skill = trim( (string) $skill );
            if ($skill !== '') {
                $names[] = $skill;
            }
        }
    }

    $names = array_values( array_unique( $names ) );
    $clear = $db->prepare(" DELETE FROM student_skills WHERE student_id = ? " );
    $clear->execute( [$student_id] );

    if (empty($names)) {
        return true;
    }

    $insert_skill = $db->prepare(" INSERT IGNORE INTO skills (name) VALUES (?) " );
    $find_skill = $db->prepare(" SELECT id FROM skills WHERE name = ? LIMIT 1 " );
    $link = $db->prepare(" INSERT INTO student_skills (student_id, skill_id) VALUES (?, ?) " );

    foreach ($names as $name) {
        $insert_skill->execute( [$name] );
        $find_skill->execute( [$name] );
        $row = $find_skill->fetch();

        if ( $row && !empty($row['id']) ) {
            $link->execute( [ $student_id, (int) $row['id'] ] );
        }
    }

    return true;
}

function student_profile_repository_get_skills_ids( PDO $db, int $student_id ): array {
    $skills = [];
    $statement = $db->prepare( "SELECT sk.name FROM student_skills ss INNER JOIN skills sk ON sk.id = ss.skill_id WHERE ss.student_id = ? ORDER BY sk.name ASC ");
    $statement->execute( [$student_id] );

    foreach ( $statement->fetchAll() as $row ) {
        $skills[] = $row['name'];
    }

    return $skills;
}

function student_profile_repository_find_by_student_id( int $student_id ): ?array {
    $sql = " SELECT id, id AS student_id, user_id, full_name, phone, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, city, profile_image_file_id, cv_file_id, is_profile_complete, created_at, updated_at
        FROM students WHERE id = ? LIMIT 1 ";
    $row = db_fetch_one( $sql, [$student_id] );

    if (!$row) {
        return null;
    }

    $row['skills'] = student_profile_repository_get_skills_ids( get_database_connection(), (int) $row['id']);
    return $row;
}

function student_profile_repository_find_by_id( int $profile_id ): ?array {
    return student_profile_repository_find_by_student_id( $profile_id );
}

function student_profile_repository_create( array $data ): int|false {
    $student_id = (int) ( $data['student_id'] ?? 0 );

    if ($student_id <= 0) {
        return false;
    }

    $student = student_profile_repository_find_by_id( $student_id );

    if (!$student) {
        return false;
    }

    $db = get_database_connection();
    $skills = $data['skills'] ?? [];

    if (is_string($skills)) {
        $decoded = json_decode( $skills, true );
        if (is_array($decoded)) {
            $skills = $decoded;
        }
    }

    if (is_array($skills)) {
        student_profile_repository_sync_skills( $db, $student_id, $skills );
    }

    if (array_key_exists('cv_file_id', $data)) {
        $db->prepare( " UPDATE students SET cv_file_id = ?, updated_at = NOW() WHERE id = ? LIMIT 1" )->execute( [ $data['cv_file_id'], $student_id ] );
    }

    return $student_id;
}

function student_profile_repository_update( int $student_id, array $data ): bool {
    if (empty($data)) {
        return false;
    }

    $db = get_database_connection();

    if (array_key_exists('skills', $data)) {
        $skills = $data['skills'];

        if (is_string($skills)) {
            $decoded = json_decode( $skills, true );
            if (is_array($decoded)) {
                $skills = $decoded;
            }
        }

        $skills_updated = is_array($skills) ? student_profile_repository_sync_skills( $db, $student_id, $skills ) : false;

        if ( !$skills_updated || count(array_diff_key($data, ['skills' => true])) === 0 ) {
            return $skills_updated;
        }
    }

    $allowed_fields = [ 'full_name', 'phone', 'bio', 'university_id', 'faculty_id', 'field_id', 'degree_id', 'specialization_id', 'graduation_year', 'city', 'profile_image_file_id', 'cv_file_id', ];
    $fields = [];
    $values = [];

    foreach ( $allowed_fields as $field ) {
        if ( !array_key_exists( $field, $data ) ) {
            continue;
        }
        $fields[] = $field . ' = ?';
        $values[] = $data[$field];
    }

    if (empty($fields)) {
        return true;
    }

    $fields[] = 'updated_at = NOW()';

    $values[] = $student_id;

    $sql = " UPDATE students SET " . implode( ', ', $fields ) . " WHERE id = ? LIMIT 1";
    $statement = db_execute( $sql, $values );
    return true;
}

function student_profile_repository_delete( int $student_id ): bool {
    $statement = db_execute( " DELETE FROM student_skills WHERE student_id = ? ", [$student_id] );
    return $statement->rowCount() > 0;
}

function student_profile_repository_exists( int $student_id ): bool {
    $sql = " SELECT id FROM students WHERE id = ? LIMIT 1 ";
    $profile = db_fetch_one( $sql, [$student_id] );
    return $profile !== null;
}

function student_profile_repository_update_skills( int $student_id, array $skills ): bool {
    return student_profile_repository_sync_skills( get_database_connection(), $student_id, $skills );
}

function student_profile_repository_get_skills( int $student_id ): array {
    return student_profile_repository_get_skills_ids( get_database_connection(), $student_id );
}

function student_profile_repository_set_cv( int $student_id, int $file_id ): bool {
    $sql = " UPDATE students SET cv_file_id = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [ $file_id, $student_id ] );
    return $statement->rowCount() > 0;
}

function student_profile_repository_remove_cv( int $student_id ): bool {
    $sql = " UPDATE students SET cv_file_id = NULL, updated_at = NOW() WHERE id = ? LIMIT 1 ";
    $statement = db_execute( $sql, [$student_id] );
    return $statement->rowCount() > 0;
}

function student_profile_repository_get_cv_file_id( int $student_id ): ?int {
    $sql = " SELECT cv_file_id FROM students WHERE id = ? LIMIT 1 ";
    $profile = db_fetch_one( $sql, [$student_id] );

    if (!$profile) {
        return null;
    }

    if ( empty( $profile['cv_file_id'] ) ) {
        return null;
    }

    return (int) $profile['cv_file_id'];
}

function student_profile_repository_get_complete( int $student_id ): ?array {
    $sql = " SELECT s.id AS student_id, s.user_id, s.full_name, s.bio, s.cv_file_id, s.id AS profile_id, s.created_at, s.updated_at
        FROM students s WHERE s.id = ? LIMIT 1 ";
    $result = db_fetch_one( $sql, [$student_id] );

    if (!$result) {
        return null;
    }

    $result['skills'] = student_profile_repository_get_skills_ids( get_database_connection(), $student_id );
    return $result;
}