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

/*
|--------------------------------------------------------------------------
| Resolve Skills Against The Taxonomy
|--------------------------------------------------------------------------
|
| The `skills` table is a SHARED taxonomy: training_skills, the company
| filters and the dashboard all read it. A student must therefore pick from
| that taxonomy rather than inventing an entry, otherwise one student can
| write arbitrary rows into a list every other user filters by.
|
| These helpers are the only way a skill name becomes a skill id now. The
| lookup is case-insensitive because the column's collation already is, so
| `php` and `PHP` resolve to the same taxonomy row.
|
*/

function student_profile_repository_find_skill_id( string $name ): ?int {

    $name = trim( $name );

    if ( $name === '' ) {
        return null;
    }

    $row = db_fetch_one(
        ' SELECT id FROM skills WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ',
        [ $name ]
    );

    return is_array( $row ) ? (int) $row['id'] : null;
}

/*
|--------------------------------------------------------------------------
| Find Skill By Id
|--------------------------------------------------------------------------
|
| The by-id counterpart of find_skill_id(): the education degrees moved to
| numeric lookup ids, so the skills the student may attach now arrive as their
| taxonomy id. The lookup stays the source of truth - a profile can never
| write a new taxonomy row, only reference an existing one.
|
*/

function student_profile_repository_find_skill_by_id( int $skill_id ): ?array {

    if ( $skill_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one(
        ' SELECT id, name FROM skills WHERE id = ? AND is_active = 1 LIMIT 1 ',
        [ $skill_id ]
    );

    return is_array( $row ) ? $row : null;
}

/*
| Maps every name that exists in the taxonomy to its id. Names that do not
| resolve are simply absent from the result, which is how the service reports
| them as unknown. One query for the whole list, so a 50-skill bulk update is
| a single round trip rather than 50.
*/
function student_profile_repository_find_skill_ids( array $names ): array {

    $names = array_values( array_unique( array_filter( array_map(
        static fn( $name ) => is_string( $name ) || is_numeric( $name ) ? trim( (string) $name ) : '',
        $names
    ), static fn( $name ) => $name !== '' ) ) );

    if ( empty( $names ) ) {
        return [];
    }

    $placeholders = implode( ', ', array_fill( 0, count( $names ), '?' ) );

    $rows = db_fetch_all(
        " SELECT id, name FROM skills
          WHERE is_active = 1
            AND LOWER(TRIM(name)) IN ( " . $placeholders . " ) ",
        array_map( 'strtolower', $names )
    );

    $map = [];

    foreach ( $rows as $row ) {
        $map[ strtolower( trim( (string) $row['name'] ) ) ] = (int) $row['id'];
    }

    return $map;
}

function student_profile_repository_count_skills( int $student_id ): int {

    $row = db_fetch_one(
        ' SELECT COUNT(*) AS total FROM student_skills WHERE student_id = ? ',
        [ $student_id ]
    );

    return (int) ( $row['total'] ?? 0 );
}

/*
|--------------------------------------------------------------------------
| Sync Skills
|--------------------------------------------------------------------------
|
| Replaces the student's whole skill set.
|
| This resolves each name against the taxonomy and LINKS the ones that exist.
| It deliberately does NOT insert into `skills`: the taxonomy is shared
| reference data, and a profile edit must never be able to add a row to it.
| An unresolvable name is skipped rather than created, so even a caller that
| bypassed the service-level validation cannot pollute the shared list.
|
| The validate call in the service runs first and rejects unknown names with a
| 422, so a client is told which skill was not recognised instead of quietly
| losing it. The skip here is the second line of defence, not the primary
| behaviour.
|
*/

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

    $skill_ids = student_profile_repository_find_skill_ids( $names );

    if ( empty( $skill_ids ) ) {
        return true;
    }

    // INSERT IGNORE: the composite PRIMARY KEY (student_id, skill_id) is the
    // real duplicate guard, so a name that resolved twice cannot double-link.
    $link = $db->prepare(" INSERT IGNORE INTO student_skills (student_id, skill_id) VALUES (?, ?) " );

    foreach ( $names as $name ) {
        $skill_id = $skill_ids[ strtolower( $name ) ] ?? null;

        if ( $skill_id === null ) {
            continue;
        }

        $link->execute( [ $student_id, $skill_id ] );
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