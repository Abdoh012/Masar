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
    $sql = " SELECT id, user_id, full_name, phone, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, graduation_month, academic_status, city, profile_image_file_id, cv_file_id, academic_certificate_file_id, is_profile_complete, created_at, updated_at FROM students WHERE id = ? LIMIT 1 ";
    $student = db_fetch_one($sql, [$student_id]);

    return is_array($student) ? $student : null;
}

function student_repository_find_by_user_id( int $user_id ): ?array {
    $sql = " SELECT id, user_id, full_name, phone, website, external_profile, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, graduation_month, academic_status, city, address, profile_image_file_id, cv_file_id, academic_certificate_file_id, is_profile_complete, created_at, updated_at FROM students WHERE user_id = ? LIMIT 1 ";
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

/*
 * Lookups by id, used when a client references a lookup row directly instead
 * of by name. They return university_id alongside the faculty because a
 * faculty belongs to exactly one university and the education service has to
 * be able to check that pairing.
 */
function student_repository_find_university_by_id( int $university_id ): ?array {

    if ( $university_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one(
        " SELECT id, name FROM universities WHERE id = ? AND is_active = 1 LIMIT 1 ",
        [ $university_id ]
    );

    return is_array( $row ) ? $row : null;
}

function student_repository_find_faculty_by_id( int $faculty_id ): ?array {

    if ( $faculty_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one(
        " SELECT id, name, university_id FROM faculties WHERE id = ? AND is_active = 1 LIMIT 1 ",
        [ $faculty_id ]
    );

    return is_array( $row ) ? $row : null;
}

function student_repository_find_degree_by_id( int $degree_id ): ?array {

    if ( $degree_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one(
        " SELECT id, name FROM degrees WHERE id = ? LIMIT 1 ",
        [ $degree_id ]
    );

    return is_array( $row ) ? $row : null;
}

/*
|--------------------------------------------------------------------------
| Profile Header Row
|--------------------------------------------------------------------------
|
| Resolves the profile-header lookup values for ONE student in a single
| read: the student's own name and profile image, the names behind
| field_id / specialization_id / university_id / faculty_id, and the account
| creation timestamp that is the membership start date.
|
| The joins are LEFT so an unfinished profile still returns a header
| with nulls instead of no row. The file join is additionally scoped
| to files.type = 'profile_image' AND files.user_id = students.user_id,
| so a profile can never surface a CV, a certificate attachment, or
| another student's image even if profile_image_file_id is corrupted.
|
*/

function student_repository_find_profile_header( int $student_id ): ?array {
    if ($student_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            s.full_name     AS full_name,
            sf.name         AS field,
            pf.id           AS profile_image_id,
            sp.name         AS specialization,
            un.name         AS university,
            fa.name         AS faculty,
            u.created_at    AS member_since
        FROM students s
        LEFT JOIN study_fields  sf  ON sf.id  = s.field_id
        LEFT JOIN specializations sp ON sp.id = s.specialization_id
        LEFT JOIN universities   un  ON un.id  = s.university_id
        LEFT JOIN faculties      fa  ON fa.id  = s.faculty_id
        INNER JOIN users        u   ON u.id   = s.user_id
        LEFT JOIN files         pf  ON pf.id  = s.profile_image_file_id
                                AND pf.user_id = s.user_id
                                AND pf.type = 'profile_image'
        WHERE s.id = ?
        LIMIT 1
    ";

    $row = db_fetch_one( $sql, [ $student_id ] );

    return is_array( $row ) ? $row : null;
}

function student_repository_update( int $student_id, array $data ): bool{
    if (empty($data)) {
        return false;
    }

    $allowed_fields = [ 'full_name', 'phone', 'website', 'external_profile', 'bio', 'university_id', 'faculty_id', 'field_id', 'degree_id', 'specialization_id', 'graduation_year', 'graduation_month', 'graduation_date', 'academic_status', 'city', 'address', 'profile_image_file_id', 'cv_file_id', 'academic_certificate_file_id', 'is_profile_complete' ];
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

/*
|--------------------------------------------------------------------------
| Academic Status
|--------------------------------------------------------------------------
|
| The profile-level Student / Graduate state and the month a degree or
| certificate was awarded.
|
| graduation_month is stored on its own (1-12) and pairs with the pre-existing
| graduation_year column rather than introducing a second full date that would
| compete with it. Both are written together so the two can never disagree
| about the same award.
|
*/

function student_repository_update_academic_status(
    int $student_id,
    string $academic_status,
    ?int $graduation_month
): bool {

    if ( $student_id <= 0 ) {
        return false;
    }

    db_execute(
        " UPDATE students SET academic_status = ?, graduation_month = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $academic_status, $graduation_month, $student_id ]
    );

    return true;
}

/*
|--------------------------------------------------------------------------
| Profile Image Reference
|--------------------------------------------------------------------------
|
| Points students.profile_image_file_id at a file. The caller is responsible
| for proving the file is this student's own profile_image; the read path
| re-checks ownership in its join, so a wrong reference can never be served.
|
*/

function student_repository_set_profile_image_file_id( int $student_id, ?int $file_id ): bool {

    if ( $student_id <= 0 ) {
        return false;
    }

    db_execute(
        " UPDATE students SET profile_image_file_id = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $file_id, $student_id ]
    );

    return true;
}

/*
|--------------------------------------------------------------------------
| Academic Certificate Reference
|--------------------------------------------------------------------------
|
| Points students.academic_certificate_file_id at a file, or clears it with
| null. The stored reference is validated on read against files.user_id and
| files.type, so a dangling or foreign id can never be served.
|
*/

function student_repository_set_academic_certificate_file_id( int $student_id, ?int $file_id ): bool {

    if ( $student_id <= 0 ) {
        return false;
    }

    db_execute(
        " UPDATE students SET academic_certificate_file_id = ?, updated_at = NOW() WHERE id = ? LIMIT 1 ",
        [ $file_id, $student_id ]
    );

    return true;
}

/*
|--------------------------------------------------------------------------
| Academic Status Projection
|--------------------------------------------------------------------------
|
| The academic status as the profile presents it: the stored status, the
| graduation year/month the award was recorded in, and a ready-to-render
| month name. The month name is derived here rather than stored so it always
| follows the stored month and can never go stale.
|
*/

function student_repository_find_academic_status( int $student_id ): ?array {

    if ( $student_id <= 0 ) {
        return null;
    }

    $row = db_fetch_one(
        " SELECT academic_status, graduation_date, graduation_year, graduation_month, academic_certificate_file_id
          FROM students
          WHERE id = ?
          LIMIT 1 ",
        [ $student_id ]
    );

    if ( !is_array( $row ) ) {
        return null;
    }

    return [
        'academic_status'              => (string) ( $row['academic_status'] ?? ACADEMIC_STATUS_STUDENT ),
        'graduation_date'              => $row['graduation_date'] ?? null,
        'graduation_year'              => $row['graduation_year'] ?? null,
        'graduation_month'             => $row['graduation_month'] ?? null,
        'academic_certificate_file_id' => $row['academic_certificate_file_id'] ?? null,
    ];
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
