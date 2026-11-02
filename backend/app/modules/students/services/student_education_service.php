<?php

/**
 * MASAR - Student Education Service
 *
 * Business rules for a student's education history.
 *
 * Responsibilities:
 * - List / create / update / delete education records.
 * - Guarantee that a caller can only ever touch their OWN records.
 * - Resolve free-text institution and degree names against the existing
 *   lookup tables, keeping the text as the source of truth.
 *
 * IMPORTANT:
 * - No SQL.
 * - No HTTP handling.
 * - Database access belongs to student_education_repository.php.
 */

require_once __DIR__ . '/../repositories/student_education_repository.php';
require_once __DIR__ . '/../repositories/student_repository.php';
require_once __DIR__ . '/../../../shared/functions/application_cards.php';


/*
|--------------------------------------------------------------------------
| List Education
|--------------------------------------------------------------------------
*/

function student_education_service_list( int $student_id ): array {

    if ( $student_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    if ( !student_repository_exists( $student_id ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $rows = student_education_repository_list( $student_id );

    return [
        'data' => [
            'items'      => array_map( 'student_education_present', $rows ),
            'total'      => count( $rows ),
            'max_items'  => STUDENT_MAX_EDUCATIONS,
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Create Education
|--------------------------------------------------------------------------
*/

function student_education_service_create( int $student_id, array $data ): array {

    if ( $student_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    if ( !student_repository_exists( $student_id ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    if ( student_education_repository_count( $student_id ) >= STUDENT_MAX_EDUCATIONS ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'You cannot add more than ' . STUDENT_MAX_EDUCATIONS . ' education records.',
        ];
    }

    $resolved = student_education_resolve_lookup_ids( $data );

    if ( !empty( $resolved['errors'] ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Education record is not valid.' ] + $resolved;
    }

    /*
     | The year rules are applied on create exactly as on update. They live here
     | rather than in the validator because is_current / end_year have to be
     | read together, and skipping them on create would let a nonsensical record
     | (a qualification that ends before it starts, or a "current" one with an
     | end date) be written and only be caught on a later edit.
     */
    $resolved['is_current'] = !empty( $data['is_current'] );

    $year_errors = student_education_validate_years(
        $resolved['start_year'] ?? null,
        $resolved['end_year'] ?? null,
        $resolved['is_current']
    );

    if ( !empty( $year_errors ) ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'Education record is not valid.',
            'errors'  => $year_errors,
        ];
    }

    $education_id = student_education_repository_create( $student_id, $resolved );

    if ( $education_id <= 0 ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to create the education record.' ];
    }

    $created = student_education_repository_find( $education_id, $student_id );

    return [
        'data'    => student_education_present( is_array( $created ) ? $created : [] ),
        'message' => 'Education record added successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Update Education
|--------------------------------------------------------------------------
*/

function student_education_service_update( int $student_id, int $education_id, array $data ): array {

    if ( $student_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    /*
     | Ownership is resolved BEFORE any write. Because the repository read is
     | scoped by student_id, an id belonging to another student is reported as
     | not found (404) instead of confirmed to exist and then refused (403),
     | so this endpoint cannot be used to discover which education ids exist
     | for other students.
     */
    $existing = student_education_repository_find( $education_id, $student_id );

    if ( !is_array( $existing ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Education record not found.' ];
    }

    /*
     | A partial update must not re-validate fields the caller did not send.
     | The existing row is merged underneath the payload so the year-order and
     | required-field rules are evaluated against the record's final state,
     | not against a half-filled array.
     */
    $merged = student_education_merge_for_update( $existing, $data );

    $errors = student_education_validate_years(
        $merged['start_year'] ?? null,
        $merged['end_year'] ?? null,
        !empty( $merged['is_current'] )
    );

    if ( !empty( $errors ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Education record is not valid.', 'errors' => $errors ];
    }

    $resolved = student_education_resolve_lookup_ids( $merged );

    if ( !empty( $resolved['errors'] ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Education record is not valid.' ] + $resolved;
    }

    /*
     | resolve_lookup_ids() deliberately knows nothing about is_current, so it
     | is carried across here. Without this an update could never set or clear
     | the flag, because the repository only writes keys it is handed.
     */
    $resolved['is_current'] = !empty( $merged['is_current'] );

    $updated = student_education_repository_update( $education_id, $student_id, $resolved );

    if ( !$updated ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the education record.' ];
    }

    $row = student_education_repository_find( $education_id, $student_id );

    return [
        'data'    => student_education_present( is_array( $row ) ? $row : [] ),
        'message' => 'Education record updated successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Delete Education
|--------------------------------------------------------------------------
*/

function student_education_service_delete( int $student_id, int $education_id ): array {

    if ( $student_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $existing = student_education_repository_find( $education_id, $student_id );

    if ( !is_array( $existing ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Education record not found.' ];
    }

    if ( !student_education_repository_delete( $education_id, $student_id ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to delete the education record.' ];
    }

    return [
        'data'    => [ 'id' => $education_id ],
        'message' => 'Education record deleted successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Merge Payload Onto The Existing Row
|--------------------------------------------------------------------------
|
| Used by the partial update so untouched columns keep their stored value.
*/

function student_education_merge_for_update( array $existing, array $data ): array {

    $merged = [];

    foreach ( [ 'degree_id', 'university_id', 'faculty_id', 'field_id', 'specialization_id', 'study_mode' ] as $field ) {
        $merged[ $field ] = array_key_exists( $field, $data )
            ? $data[ $field ]
            : ( $existing[ $field ] ?? null );
    }

    foreach ( [ 'degree_name', 'university_name', 'grade' ] as $field ) {
        if ( array_key_exists( $field, $data ) ) {
            $merged[ $field ] = $data[ $field ];
        }
    }

    foreach ( [ 'start_year', 'end_year' ] as $field ) {
        $merged[ $field ] = array_key_exists( $field, $data )
            ? $data[ $field ]
            : ( $existing[ $field ] ?? null );
    }

    $merged['is_current'] = array_key_exists( 'is_current', $data )
        ? !empty( $data['is_current'] )
        : !empty( $existing['is_current'] );

    return $merged;
}

/*
|--------------------------------------------------------------------------
| Resolve Lookup Ids
|--------------------------------------------------------------------------
|
| An institution or degree is matched against the existing lookup tables when
| it can be, and the typed text is always kept. The text is the source of truth
| because a student's school is frequently not in the lookups at all, and
| overwriting it with a NULL-because-unmatched id would lose real data.
|
| An id that is supplied but does not resolve is an error, not a silent
| fallback: a client pointing at degree 999999 is told so instead of getting a
| row with no degree.
|
*/

function student_education_resolve_lookup_ids( array $data ): array {

    $resolved = [];
    $errors   = [];

    /*
     | degree_name carries the education degree Lookup ID (integer). An
     | explicit degree_id is still accepted as a legacy alias. The degree is
     | resolved through the degrees lookup, and the resolved human-readable name
     | is kept in degree_name so the stored text and the id stay in sync.
     */
    $degree_name_value = isset( $data['degree_name'] ) ? $data['degree_name'] : null;

    if ( !empty( $data['degree_id'] ) ) {
        $degree_id = (int) $data['degree_id'];
        $degree    = student_repository_find_degree_by_id( $degree_id );

        if ( !is_array( $degree ) ) {
            $errors['degree_id'] = 'The selected degree is not recognized.';
        } else {
            $resolved['degree_id']   = $degree_id;
            $resolved['degree_name'] = (string) $degree['name'];
        }
    } elseif ( is_int( $degree_name_value ) && (int) $degree_name_value > 0 ) {
        $degree_id = (int) $degree_name_value;
        $degree    = student_repository_find_degree_by_id( $degree_id );

        if ( !is_array( $degree ) ) {
            $errors['degree_id'] = 'The selected degree is not recognized.';
        } else {
            $resolved['degree_id']   = $degree_id;
            $resolved['degree_name'] = (string) $degree['name'];
        }
    } elseif ( is_string( $degree_name_value ) && trim( (string) $degree_name_value ) !== '' ) {
        $resolved['degree_id']   = student_repository_resolve_degree_id( trim( (string) $degree_name_value ) );
        $resolved['degree_name'] = $degree_name_value;
    } else {
        $resolved['degree_id']   = null;
        $resolved['degree_name'] = null;
    }

    $university_name = isset( $data['university_name'] ) ? trim( (string) $data['university_name'] ) : null;

    if ( $university_name !== null && $university_name !== '' ) {
        $resolved['university_id']   = student_repository_resolve_university_id( $university_name );
        $resolved['university_name'] = $university_name;
    } elseif ( !empty( $data['university_id'] ) ) {
        $university_id = (int) $data['university_id'];
        $university    = student_repository_find_university_by_id( $university_id );

        if ( !is_array( $university ) ) {
            $errors['university_id'] = 'The selected university is not recognized.';
        } else {
            $resolved['university_id']   = $university_id;
            $resolved['university_name'] = (string) $university['name'];
        }
    } else {
        $resolved['university_id']   = null;
        $resolved['university_name'] = null;
    }

    /*
     | A faculty is scoped to a university: faculties belong to exactly one
     | university (faculties.university_id), so a faculty that belongs to a
     | different university than the record is a genuine mismatch and is
     | refused rather than stored as an inconsistent pair.
     */
    if ( !empty( $data['faculty_id'] ) ) {
        $faculty_id = (int) $data['faculty_id'];
        $faculty    = student_repository_find_faculty_by_id( $faculty_id );

        if ( !is_array( $faculty ) ) {
            $errors['faculty_id'] = 'The selected faculty is not recognized.';
        } else {
            $resolved['faculty_id'] = $faculty_id;

            $record_university_id = $resolved['university_id'] ?? null;

            if ( $record_university_id === null ) {
                $resolved['university_id']   = $faculty['university_id'];
                $resolved['university_name'] = null;
            } elseif ( (int) $faculty['university_id'] !== (int) $record_university_id ) {
                $errors['faculty_id'] = 'The selected faculty does not belong to the selected university.';
            }
        }
    } else {
        $resolved['faculty_id'] = null;
    }

    if ( !empty( $data['field_id'] ) ) {
        $field_id = (int) $data['field_id'];
        $field    = student_repository_find_field_by_id( $field_id );

        if ( !is_array( $field ) ) {
            $errors['field_id'] = 'The selected field of study is not recognized.';
        } else {
            $resolved['field_id'] = $field_id;
        }
    } elseif ( !empty( $data['field_study'] ) ) {
        // field_study is the free-text field of study; it resolves through the
        // study-fields lookup when the school is listed there.
        $field_study_name = trim( (string) $data['field_study'] );
        $resolved['field_id'] = student_repository_resolve_field_id( $field_study_name );
    } else {
        $resolved['field_id'] = null;
    }

    if ( !empty( $data['specialization_id'] ) ) {
        $specialization_id = (int) $data['specialization_id'];
        $specialization    = student_repository_find_specialization_by_id( $specialization_id );

        if ( !is_array( $specialization ) ) {
            $errors['specialization_id'] = 'The selected specialization is not recognized.';
        } elseif (
            !empty( $resolved['field_id'] )
            && !empty( $specialization['field_id'] )
            && (int) $specialization['field_id'] !== (int) $resolved['field_id']
        ) {
            $errors['specialization_id'] = 'The selected specialization does not belong to the selected field of study.';
        } else {
            $resolved['specialization_id'] = $specialization_id;

            if ( empty( $resolved['field_id'] ) && !empty( $specialization['field_id'] ) ) {
                $resolved['field_id'] = (int) $specialization['field_id'];
            }
        }
    } else {
        $resolved['specialization_id'] = null;
    }

    if ( isset( $data['study_mode'] ) && trim( (string) $data['study_mode'] ) !== '' ) {
        $resolved['study_mode'] = trim( (string) $data['study_mode'] );
    } else {
        $resolved['study_mode'] = null;
    }

    $resolved['start_year'] = student_education_normalize_year( $data['start_year'] ?? null );
    $resolved['end_year']   = student_education_normalize_year( $data['end_year'] ?? null );

    $grade = isset( $data['grade'] ) ? trim( (string) $data['grade'] ) : null;
    $resolved['grade'] = ( $grade === null || $grade === '' ) ? null : $grade;

    if ( !empty( $errors ) ) {
        return [ 'errors' => $errors ];
    }

    return $resolved;
}

/*
|--------------------------------------------------------------------------
| Year Rules
|--------------------------------------------------------------------------
*/

function student_education_normalize_year( mixed $value ): ?int {

    if ( $value === null || $value === '' ) {
        return null;
    }

    $year = filter_var( $value, FILTER_VALIDATE_INT );

    return ( $year === false ) ? null : (int) $year;
}

function student_education_validate_years( mixed $start_year, mixed $end_year, bool $is_current ): array {

    $errors = [];

    $current_year = (int) date( 'Y' );

    foreach ( [ 'start_year' => $start_year, 'end_year' => $end_year ] as $field => $value ) {
        if ( $value === null || $value === '' ) {
            continue;
        }

        $year = filter_var( $value, FILTER_VALIDATE_INT );

        if ( $year === false ) {
            $errors[ $field ] = ucfirst( str_replace( '_', ' ', $field ) ) . ' must be a valid year.';
            continue;
        }

        if ( (int) $year < 1950 || (int) $year > $current_year + 10 ) {
            $errors[ $field ] = ucfirst( str_replace( '_', ' ', $field ) ) . ' must be between 1950 and ' . ( $current_year + 10 ) . '.';
        }
    }

    if ( $is_current && $end_year !== null && $end_year !== '' ) {
        $errors['end_year'] = 'The current education record cannot have an end year.';
    }

    if (
        !$is_current
        &&
        $start_year !== null && $start_year !== ''
        &&
        $end_year !== null && $end_year !== ''
        &&
        (int) $end_year < (int) $start_year
    ) {
        $errors['end_year'] = 'The end year cannot be before the start year.';
    }

    return $errors;
}

/*
|--------------------------------------------------------------------------
| Present One Record
|--------------------------------------------------------------------------
|
| The stored text is preferred for display and the looked-up name is used only
| as a fallback, so a renamed or re-typed institution does not rewrite history
| the student already recorded. Timestamps go through application_iso8601()
| like every other timestamp in the codebase.
*/

function student_education_present( array $row ): array {

    if ( empty( $row ) ) {
        return [];
    }

    return [
        'id'                 => (int) ( $row['id'] ?? 0 ),
        'degree_id'         => ( $row['degree_id'] ?? null ) === null ? null : (int) $row['degree_id'],
        'degree'             => ( $row['degree_name'] ?? null ) ?: ( $row['degree_lookup_name'] ?? null ),
        'university'         => ( $row['university_name'] ?? null ) ?: ( $row['university_lookup_name'] ?? null ),
        'faculty'            => $row['faculty_name'] ?? null,
        'field'              => $row['field_name'] ?? null,
        'specialization'     => $row['specialization_name'] ?? null,
        'study_mode'         => $row['study_mode'] ?? null,
        'start_year'         => ( $row['start_year'] ?? null ) === null ? null : (int) $row['start_year'],
        'end_year'           => ( $row['end_year'] ?? null ) === null ? null : (int) $row['end_year'],
        'grade'              => $row['grade'] ?? null,
        'is_current'         => !empty( $row['is_current'] ),
        'created_at'         => application_iso8601( $row['created_at'] ?? null ),
        'updated_at'         => application_iso8601( $row['updated_at'] ?? null ),
    ];
}


