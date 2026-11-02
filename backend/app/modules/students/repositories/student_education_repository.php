<?php

/**
 * MASAR - Student Education Repository
 *
 * Responsible only for database operations related to a
 * student's education history.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 *
 * Database:
 * student_educations
 */

require_once __DIR__ . '/../../../core/database/query.php';

/*
|--------------------------------------------------------------------------
| Select Columns
|--------------------------------------------------------------------------
|
| One place to define the education projection, so the read, the create echo
| and the ownership lookup can never drift apart.
|
*/

function student_education_repository_columns(): string
{
    return "
            e.id,
            e.student_id,
            e.degree_id,
            e.degree_name,
            e.university_id,
            e.university_name,
            e.faculty_id,
            e.field_id,
            e.specialization_id,
            e.study_mode,
            e.start_year,
            e.end_year,
            e.grade,
            e.is_current,
            e.created_at,
            e.updated_at,
            d.name AS degree_lookup_name,
            un.name AS university_lookup_name,
            fa.name AS faculty_name,
            sf.name AS field_name,
            sp.name AS specialization_name
    ";
}

function student_education_repository_joins(): string
{
    return "
        FROM student_educations e
        LEFT JOIN degrees         d  ON d.id  = e.degree_id
        LEFT JOIN universities   un  ON un.id = e.university_id
        LEFT JOIN faculties      fa  ON fa.id = e.faculty_id
        LEFT JOIN study_fields   sf  ON sf.id = e.field_id
        LEFT JOIN specializations sp ON sp.id = e.specialization_id
    ";
}

/*
|--------------------------------------------------------------------------
| List A Student's Education
|--------------------------------------------------------------------------
|
| Most recent qualification first, so the current or most recently finished
| one leads the profile.
|
| Ordering is is_current DESC, then end_year DESC, then id DESC. is_current
| first means an in-progress qualification always sorts above a finished one
| regardless of year; end_year DESC puts the newest finished qualification
| next; id DESC is the deterministic tie-break for rows that share an end_year
| (or both have none), so the order is stable across calls.
|
*/

function student_education_repository_list( int $student_id ): array
{
    if ( $student_id <= 0 ) {
        return [];
    }

    $sql = "
        SELECT
    " . student_education_repository_columns() . student_education_repository_joins() . "
        WHERE e.student_id = ?
        ORDER BY e.is_current DESC, e.end_year DESC, e.id DESC
    ";

    return db_fetch_all( $sql, [ $student_id ] );
}

/*
|--------------------------------------------------------------------------
| Find One Education Owned By A Student
|--------------------------------------------------------------------------
|
| The single read used for every fetch, update and delete. The student_id is
| part of the WHERE clause rather than checked afterwards, so a record owned by
| somebody else is simply not found: ownership cannot be forgotten, and there
| is no window in which another student's row has been read and is about to be
| treated as the caller's.
|
*/

function student_education_repository_find( int $education_id, int $student_id ): ?array
{
    if ( $education_id <= 0 || $student_id <= 0 ) {
        return null;
    }

    $sql = "
        SELECT
    " . student_education_repository_columns() . student_education_repository_joins() . "
        WHERE e.id = ?
          AND e.student_id = ?
        LIMIT 1
    ";

    return db_fetch_one( $sql, [ $education_id, $student_id ] );
}

function student_education_repository_count( int $student_id ): int
{
    if ( $student_id <= 0 ) {
        return 0;
    }

    $row = db_fetch_one(
        " SELECT COUNT(*) AS total FROM student_educations WHERE student_id = ? ",
        [ $student_id ]
    );

    return (int) ( $row['total'] ?? 0 );
}

/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
|
| Every column is bound explicitly. The column list is never built from the
| caller's array keys, so a key the service did not intend to write cannot
| reach the SQL.
|
*/

function student_education_repository_create( int $student_id, array $data ): int
{
    $sql = "
        INSERT INTO student_educations (
            student_id,
            degree_id,
            degree_name,
            university_id,
            university_name,
            faculty_id,
            field_id,
            specialization_id,
            study_mode,
            start_year,
            end_year,
            grade,
            is_current,
            created_at,
            updated_at
        )
        VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() )
    ";

    db_execute( $sql, [
        $student_id,
        $data['degree_id'] ?? null,
        $data['degree_name'] ?? null,
        $data['university_id'] ?? null,
        $data['university_name'] ?? null,
        $data['faculty_id'] ?? null,
        $data['field_id'] ?? null,
        $data['specialization_id'] ?? null,
        $data['study_mode'] ?? null,
        $data['start_year'] ?? null,
        $data['end_year'] ?? null,
        $data['grade'] ?? null,
        !empty( $data['is_current'] ) ? 1 : 0,
    ] );

    return (int) db_last_insert_id();
}

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
|
| Scoped by BOTH id and student_id. A crafted request naming another student's
| education id therefore updates zero rows rather than that student's row.
|
*/

function student_education_repository_update( int $education_id, int $student_id, array $data ): bool
{
    if ( $education_id <= 0 || $student_id <= 0 ) {
        return false;
    }

    $allowed = [
        'degree_id',
        'degree_name',
        'university_id',
        'university_name',
        'faculty_id',
        'field_id',
        'specialization_id',
        'study_mode',
        'start_year',
        'end_year',
        'grade',
        'is_current',
    ];

    $fields = [];
    $values = [];

    foreach ( $allowed as $field ) {
        if ( !array_key_exists( $field, $data ) ) {
            continue;
        }

        $fields[] = $field . ' = ?';
        $values[] = ( $field === 'is_current' ) ? ( !empty( $data[$field] ) ? 1 : 0 ) : $data[$field];
    }

    if ( empty( $fields ) ) {
        return false;
    }

    $fields[] = 'updated_at = NOW()';

    $values[] = $education_id;
    $values[] = $student_id;

    db_execute(
        " UPDATE student_educations SET " . implode( ', ', $fields ) . " WHERE id = ? AND student_id = ? LIMIT 1 ",
        $values
    );

    return true;
}

function student_education_repository_delete( int $education_id, int $student_id ): bool
{
    if ( $education_id <= 0 || $student_id <= 0 ) {
        return false;
    }

    $statement = db_execute(
        " DELETE FROM student_educations WHERE id = ? AND student_id = ? LIMIT 1 ",
        [ $education_id, $student_id ]
    );

    return db_row_count( $statement ) > 0;
}
