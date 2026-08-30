<?php

/**
 * MASAR - Student Service
 *
 * Contains business logic related to students.
 *
 * Responsibilities:
 * - Create student profile.
 * - Retrieve student profile.
 * - Update student profile.
 * - Check profile completion.
 * - Control access to student profile data.
 *
 * Database operations belong to student_repository.php.
 * Profile-specific operations belong to student_profile_service.php.
 */

require_once __DIR__ . '/../repositories/student_repository.php';
require_once __DIR__ . '/../repositories/student_profile_repository.php';
require_once __DIR__ . '/../../files/repositories/file_repository.php';

/**
 * Verifies that a client-supplied CV file ID belongs to the authenticated user.
 *
 * Returns ['cv_file_id' => int] on success, or an error result otherwise.
 * The authenticated user is derived from the request context, so a
 * client-supplied file ID is never trusted.
 */
function student_service_validate_cv_file_ownership( int $user_id, mixed $cv_file_id ): array {
    $cv_file_id = (int) $cv_file_id;

    if ( $cv_file_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid CV file ID.', ];
    }

    $file = file_repository_find_for_user( $cv_file_id, $user_id );

    if (!$file) {
        return [ 'error' => true, 'status' => 422, 'message' => 'The selected CV file does not belong to your account.', ];
    }

    return [ 'cv_file_id' => $cv_file_id, ];
}

/**
 * Resolves the student's academic choices (User Field + Specialist) to
 * database IDs.
 *
 * The User Field is read from the `field` payload key when present, falling
 * back to the legacy `faculty` key used by the current registration form.
 * A numeric `field_id` is also accepted. Specialist is read from the
 * `specialization` key (or numeric `specialization_id`) and MUST belong to
 * the selected field: the field -> specialization relationship
 * (specializations.field_id) is enforced here. University is accepted
 * separately by name in student_service_create_profile and stored as the
 * numeric university_id (the students.university_id FK is preserved).
 *
 * Returns null when the field or specialization is unknown/inactive or when
 * the specialization does not belong to the selected field.
 */
function student_service_resolve_academic_data( array $data ): ?array {
    $user_field = trim((string) ($data['field'] ?? ''));

    if ($user_field === '') {
        $user_field = trim((string) ($data['faculty'] ?? ''));
    }

    $specialization = trim((string) ($data['specialization'] ?? ''));

    // Resolve the study field (by ID when provided, otherwise by name).
    if (isset($data['field_id']) && (int) $data['field_id'] > 0) {
        $field = student_repository_find_field_by_id((int) $data['field_id']);
        $field_id = is_array($field) ? (int) $field['id'] : null;
    } else {
        $field_id = student_repository_resolve_field_id( $user_field );
    }

    if ($field_id === null) {
        return null;
    }

    // Resolve the specialization strictly inside the selected field.
    if (isset($data['specialization_id']) && (int) $data['specialization_id'] > 0) {
        $specialization_row = student_repository_find_specialization_by_id((int) $data['specialization_id']);

        if (
            $specialization_row === null
            || (int) ($specialization_row['field_id'] ?? 0) !== $field_id
        ) {
            return null;
        }

        $specialization_id = (int) $specialization_row['id'];
    } else {
        $specialization_id = student_repository_resolve_specialization_id_in_field( $specialization, $field_id );
    }

    if ($specialization_id === null) {
        return null;
    }

    return [
        'field_id' => $field_id,
        'specialization_id' => $specialization_id,
    ];
}

function student_service_create_profile( int $user_id, array $data ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid user ID.' ];
    }

    $existing = student_repository_find_by_user_id( $user_id );

    if ($existing) {
        return [ 'error' => true, 'status' => 409, 'message' => 'Student profile already exists.' ];
    }

    $academic_data = student_service_resolve_academic_data( $data );

    if ($academic_data === null) {
        return [ 'error' => true, 'status' => 422, 'message' => 'User field or specialization is incorrect.' ];
    }

    $student_data = [
        'user_id' => $user_id,
        'full_name' => trim( $data['full_name'] ?? '' ),
        'field_id' => $academic_data['field_id'],
        'specialization_id' => $academic_data['specialization_id'],
    ];

    if (!empty($data['degree'] ?? null)) {
        $degree_id = student_repository_resolve_degree_id( trim((string) $data['degree']) );
        if ($degree_id === null) {
            return [ 'error' => true, 'status' => 422, 'message' => 'Degree is not recognized.' ];
        }
        $student_data['degree_id'] = $degree_id;
    }

    if ( array_key_exists( 'university', $data ) ) {
        $university_value = $data['university'] ?? '';

        if ( is_array( $university_value ) ) {
            return [ 'error' => true, 'status' => 422, 'message' => 'University must be a single name string.' ];
        }

        $university_name = is_string( $university_value ) ? trim( $university_value ) : '';

        if ( $university_name !== '' ) {
            $university_id = student_repository_resolve_university_id( $university_name );

            if ( $university_id === null ) {
                return [ 'error' => true, 'status' => 422, 'message' => 'University is not recognized.' ];
            }

            $student_data['university_id'] = $university_id;
        }
    }

    foreach ( [ 'bio', 'phone', 'city', ] as $field ) {
        if ( array_key_exists( $field, $data ) ) {
            $student_data[$field] = trim( (string) $data[$field] );
        }
    }

    if ( array_key_exists( 'graduation_year', $data ) && (int) $data['graduation_year'] > 0 ) {
        $student_data['graduation_year'] = (int) $data['graduation_year'];
    }

    if ( array_key_exists( 'cv_file_id', $data ) && (int) $data['cv_file_id'] > 0 ) {
        $cv_result = student_service_validate_cv_file_ownership( $user_id, $data['cv_file_id'] );
        if ( !empty( $cv_result['error'] ) ) {
            return $cv_result;
        }
        $student_data['cv_file_id'] = $cv_result['cv_file_id'];
    }

    $student_id = student_repository_create( $student_data );

    if (!$student_id) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to create student profile.' ];
    }

    if ( array_key_exists( 'skills', $data ) && is_array( $data['skills'] ) ) {
        student_profile_repository_update( (int) $student_id, [ 'skills' => $data['skills'] ] );
    }

    $student = student_repository_find_by_id($student_id);

    return [ 'data' => [ 'student' => $student, ], ];
}

function student_get_profile( int $user_id ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid user ID.' ];
    }

    $student = student_repository_find_by_user_id( $user_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $profile = student_profile_repository_find_by_student_id( (int) $student['id'] );

    return ['data' => [ 'student' => $student, 'profile' => $profile, ], ];
}

function student_service_update_profile( int $user_id, array $data ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid user ID.' ];
    }

    $student =
        student_repository_find_by_user_id(
            $user_id
        );


    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $student_id = (int) $student['id'];
    $student_data = [];

    if ( array_key_exists( 'field', $data ) || array_key_exists( 'faculty', $data ) || array_key_exists( 'specialization', $data ) ) {
        $academic_data = student_service_resolve_academic_data( $data );

        if ($academic_data === null) {
            return [ 'error' => true, 'status' => 422, 'message' => 'User field and specialization are incorrect.' ];
        }

        $student_data['field_id'] = $academic_data['field_id'];
        $student_data['specialization_id'] = $academic_data['specialization_id'];
    }

    if ( array_key_exists( 'degree', $data ) ) {
        $degree = trim( (string) $data['degree'] );

        if ($degree === '') {
            $student_data['degree_id'] = null;
        } else {
            $degree_id = student_repository_resolve_degree_id( $degree );

            if ($degree_id === null) {
                return [ 'error' => true, 'status' => 422, 'message' => 'Degree is not recognized.' ];
            }

            $student_data['degree_id'] = $degree_id;
        }
    }

    foreach ( [ 'full_name', 'phone', 'bio', 'city', ] as $field ) {
        if ( array_key_exists( $field, $data ) ) {
            $student_data[$field] = trim( (string) $data[$field] );
        }
    }

    if ( array_key_exists( 'graduation_year', $data ) ) {
        $student_data['graduation_year'] = (int) $data['graduation_year'];
    }

    if ( array_key_exists( 'cv_file_id', $data ) ) {
        $cv_file_id = (int) $data['cv_file_id'];

        if ( $cv_file_id > 0 ) {
            $cv_result = student_service_validate_cv_file_ownership( $user_id, $cv_file_id );
            if ( !empty( $cv_result['error'] ) ) {
                return $cv_result;
            }
            $student_data['cv_file_id'] = $cv_result['cv_file_id'];
        } else {
            $student_data['cv_file_id'] = null;
        }
    }

    if (!empty($student_data)) {
        $updated = student_repository_update( $student_id, $student_data );
        if (!$updated) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update student profile.' ];
        }
    }

    $profile_data = [];

    if ( array_key_exists( 'skills', $data ) ) {
        $profile_data['skills'] = $data['skills'];
    }

    if (!empty($profile_data)) {
        $profile_updated = student_profile_repository_update( $student_id, $profile_data );
        if (!$profile_updated) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update student profile data.' ];
        }
    }

    return student_get_profile( $user_id );
}

function student_complete_profile_data( int $user_id, array $data ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid user ID.' ];
    }

    $student = student_repository_find_by_user_id( $user_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $academic_data = student_service_resolve_academic_data( $data );

    if ($academic_data === null) {
        return [ 'error' => true, 'status' => 422, 'message' => 'User field or specialization is incorrect.' ];
    }

    $student_data = [
        'field_id' => $academic_data['field_id'],
        'specialization_id' => $academic_data['specialization_id'],
        'is_profile_complete' => 1,
    ];

    if ( array_key_exists( 'degree', $data ) && trim( (string) $data['degree'] ) !== '' ) {
        $degree_id = student_repository_resolve_degree_id( trim( (string) $data['degree'] ) );

        if ($degree_id === null) {
            return [ 'error' => true, 'status' => 422, 'message' => 'Degree is not recognized.' ];
        }

        $student_data['degree_id'] = $degree_id;
    }

    if ( array_key_exists( 'bio', $data ) ) {
        $student_data['bio'] = trim( (string) $data['bio'] );
    }

    if ( array_key_exists( 'cv_file_id', $data ) && (int) $data['cv_file_id'] > 0 ) {
        $cv_result = student_service_validate_cv_file_ownership( $user_id, $data['cv_file_id'] );
        if ( !empty( $cv_result['error'] ) ) {
            return $cv_result;
        }
        $student_data['cv_file_id'] = $cv_result['cv_file_id'];
    }

    $updated = student_repository_update( (int) $student['id'], $student_data );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update student information.' ];
    }

    $profile_data = [ 'skills' => $data['skills'] ?? [], ];
    $profile_updated = student_profile_repository_update( (int) $student['id'], $profile_data );

    if (!$profile_updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update student profile data.' ];
    }

    return student_get_profile( $user_id );
}

function student_get_public_profile( int $student_id, array $current_user ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $role = $current_user['role'] ?? '';
    $allowed_roles = [ 'student', 'company', 'admin', ];

    if ( !in_array( $role, $allowed_roles, true ) ) {
        return [ 'error' => true, 'status' => 403, 'message' => 'You are not allowed to view student profiles.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $profile = student_profile_repository_find_by_student_id( $student_id );
    unset( $student['user_id'] );

    if ( is_array($profile) ) {
        unset( $profile['user_id'] );
        unset( $profile['phone'] );
        unset( $profile['cv_file_id'] );
        unset( $profile['profile_image_file_id'] );
    }

    return [ 'data' => [ 'student' => $student, 'profile' => $profile, ], ];
}

function student_get_profile_status( int $user_id ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid user ID.' ];
    }

    $student = student_repository_find_by_user_id( $user_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.'];
    }

    $required_fields = [ 'field_id' => $student['field_id'] ?? null, 'specialization_id' => $student['specialization_id'] ?? null ];
    $missing_fields = [];

    foreach ( $required_fields as $field => $value ) {
        if ( empty( $value ) ) {
            $missing_fields[] = $field;
        }
    }

    $profile = student_profile_repository_find_by_student_id( (int) $student['id'] );
    $skills = is_array( $profile['skills'] ?? null ) ? $profile['skills'] : [];

    if ( empty($skills) ) {
        $missing_fields[] = 'skills';
    }

    if ( empty( $profile['cv_file_id'] ?? null ) ) {
        $missing_fields[] = 'cv';
    }

    $completed = empty($missing_fields);
    return [ 'data' => [ 'completed' => $completed, 'missing_fields' => $missing_fields, 'completion_percentage' => student_calculate_completion_percentage( $student, $profile ), ], ];
}

function student_calculate_completion_percentage( array $student, ?array $profile ): int {
    $total = 4;
    $completed = 0;
    $fields = [ 'field_id', 'specialization_id',];

    foreach ( $fields as $field ) {
        if ( !empty( $student[$field] ?? null ) ) {
            $completed++;
        }
    }

    $skills = is_array( $profile['skills'] ?? null ) ? $profile['skills'] : [];

    if ( !empty( $skills ) ) {
        $completed++;
    }

    if ( !empty( $profile['cv_file_id'] ?? null ) ) {
        $completed++;
    }

    return (int) round( ( $completed / $total ) * 100 );
}