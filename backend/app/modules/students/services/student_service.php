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

/*
|--------------------------------------------------------------------------
| Dashboard Dependencies
|--------------------------------------------------------------------------
|
| The dashboard aggregation reuses the existing training / certificate /
| search / notification modules end-to-end. Every dependency below is
| loaded with require_once, so no module is double-declared.
*/

require_once __DIR__ . '/../../training/services/training_service.php';
require_once __DIR__ . '/../../training/repositories/training_repository.php';
require_once __DIR__ . '/../../training/repositories/application_repository.php';
require_once __DIR__ . '/../../../shared/functions/application_cards.php';
require_once __DIR__ . '/../../../shared/functions/training_cards.php';
require_once __DIR__ . '/../../certificates/services/certificate_service.php';
require_once __DIR__ . '/../../search/services/search_service.php';

if (file_exists(__DIR__ . '/../../notifications/services/notification_service.php')) {
    require_once __DIR__ . '/../../notifications/services/notification_service.php';
}

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
 * A numeric `field_id` is also accepted. The `field` value itself may be a
 * numeric study_fields.id (dropdown) or a name. Specialist is read from the
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

    // Resolve the study field. `field_id`, a numeric `field` value, or a
    // field name are accepted; the ID forms are resolved strictly against
    // the active study_fields table.
    $field_id = null;

    if (isset($data['field_id']) && (int) $data['field_id'] > 0) {
        $field = student_repository_find_field_by_id((int) $data['field_id']);
        $field_id = is_array($field) ? (int) $field['id'] : null;
    } elseif ($user_field !== '' && ctype_digit($user_field)) {
        $field = student_repository_find_field_by_id((int) $user_field);
        $field_id = is_array($field) ? (int) $field['id'] : null;
    } elseif ($user_field !== '') {
        $field_id = student_repository_resolve_field_id( $user_field );
    }

    if ($field_id === null) {
        return null;
    }

    // Resolve the specialization strictly inside the selected field.
    // `specialization_id`, a numeric `specialization` value, or a name
    // scoped to the field are accepted.
    $specialization_id = null;

    if (isset($data['specialization_id']) && (int) $data['specialization_id'] > 0) {
        $specialization_row = student_repository_find_specialization_by_id((int) $data['specialization_id']);

        if (
            $specialization_row === null
            || (int) ($specialization_row['field_id'] ?? 0) !== $field_id
        ) {
            return null;
        }

        $specialization_id = (int) $specialization_row['id'];
    } elseif ($specialization !== '' && ctype_digit($specialization)) {
        $specialization_row = student_repository_find_specialization_by_id((int) $specialization);

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


/*
|--------------------------------------------------------------------------
| Student Dashboard (Read-only Aggregation)
|--------------------------------------------------------------------------
|
| GET /api/v1/students/dashboard
|
| Aggregates every dashboard section from the EXISTING domain services and
| repositories. Nothing here stores new state, creates new tables or
| duplicates business rules: counts come from the application/certificate
| repositories, the active training from the latest accepted application,
| recommendations from the search filter pipeline (specialization-scoped,
| published + not expired), notifications from the notification service,
| certificates from the certificate repository counters plus a preview of the
| single latest ISSUED certificate through the certificate presenter, and the
| next action from a deterministic decision chain over the student's own state.
|
| The authenticated user is always derived from the auth context; a
| client-supplied student_id is never accepted here.
|
*/

function student_service_dashboard( int $user_id ): array {

    if ($user_id <= 0) {
        return [ 'error' => true, 'status' => 401, 'message' => 'Authentication required.' ];
    }

    $student = student_repository_find_by_user_id( $user_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $student_id = (int) $student['id'];
    $profile    = student_profile_repository_find_by_student_id( $student_id );
    $profile    = is_array( $profile ) ? $profile : [];

    $user_context = [ 'id' => $user_id, 'role' => 'student' ];

    /*
    |--------------------------------------------------------------------------
    | Student section + profile completion
    |--------------------------------------------------------------------------
    */

    $field_name      = student_service_dashboard_lookup_name( 'study_fields', (int) ( $student['field_id'] ?? 0 ) );
    $faculty_name    = student_service_dashboard_lookup_name( 'faculties', (int) ( $student['faculty_id'] ?? 0 ) );
    $university_name = student_service_dashboard_lookup_name( 'universities', (int) ( $student['university_id'] ?? 0 ) );

    $completion_percentage = student_calculate_completion_percentage( $student, $profile );

    $student_presented = [
        'id'                   => $student_id,
        'full_name'            => (string) ( $student['full_name'] ?? '' ),
        'field'                => $field_name,
        'faculty'              => $faculty_name,
        'university'           => $university_name,
        'profile_image_file_id'=> (
            isset( $student['profile_image_file_id'] )
            && $student['profile_image_file_id'] !== null
            && $student['profile_image_file_id'] !== ''
        )
            ? (int) $student['profile_image_file_id']
            : null,
        'profile_completion'   => [
            'percentage' => $completion_percentage,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Applications snapshot
    |--------------------------------------------------------------------------
    |
    | Counts reuse the canonical per-status counters of the Applications
    | module (submitted/pending map to "applied") and are unaffected by the
    | preview size below. recent[] reuses the All-tab card DTO so tab cards
    | and the dashboard stay identical, and it is the same unified
    | application_repository_get_all_by_student() query the All tab runs, so
    | the dashboard inherits its established "latest lifecycle action"
    | ordering instead of re-deriving it here:
    |
    |   withdrawn_at  (withdrawn) / reviewed_at (accepted + rejected) /
    |   applied_at    (still submitted), maxed per row, then id DESC.
    |
    | That means an older application that was recently accepted / rejected /
    | withdrawn sorts above a newer application with no newer action, and an
    | untouched Applied application is placed by its submission timestamp.
    |
    | The preview is capped at the 3 most recently acted-on applications.
    |
    */

    $applications_total     = application_repository_count_all_by_student( $student_id );
    $applications_applied   = application_repository_count_by_student( $student_id, 'submitted' );
    $applications_accepted  = application_repository_count_accepted_by_student( $student_id );
    $applications_rejected  = application_repository_count_rejected_by_student( $student_id );
    $applications_withdrawn = application_repository_count_withdrawn_by_student( $student_id );

    $acceptance_rate =
        $applications_total > 0
            ? (int) round( ( $applications_accepted / $applications_total ) * 100 )
            : 0;

    $recent_applications = application_all_cards(
        application_repository_get_all_by_student( $student_id, 3 )
    );

    /*
    |--------------------------------------------------------------------------
    | Active training (latest accepted application)
    |--------------------------------------------------------------------------
    */

    $active_training = null;

    $accepted_rows = application_repository_get_accepted_by_student( $student_id, 1 );

    if ( is_array( $accepted_rows ) && isset( $accepted_rows[0] ) && is_array( $accepted_rows[0] ) ) {

        $training_id = (int) ( $accepted_rows[0]['training_id'] ?? 0 );

        if ($training_id > 0) {

            $training = training_repository_find_with_company( $training_id );

            if ( is_array( $training ) ) {

                $active_training = [
                    'id'             => (int) ( $training['id'] ?? 0 ),
                    'title'          => (string) ( $training['title'] ?? '' ),
                    'company'        => $training['company_name'] ?? null,
                    'type'           => (string) ( $training['training_type'] ?? '' ),
                    'mode'           => (string) ( $training['mode'] ?? '' ),
                    'is_paid'        => (bool) ( $training['is_paid'] ?? false ),
                    'trial_days'     => (
                        isset( $training['trial_period_days'] )
                        &&
                        $training['trial_period_days'] !== null
                        &&
                        $training['trial_period_days'] !== ''
                    )
                        ? (int) $training['trial_period_days']
                        : null,
                    'starts_at'      => application_iso8601( $training['starts_at'] ?? null ),
                    'ends_at'        => application_iso8601( $training['ends_at'] ?? null ),
                    'remaining_days' => training_calculate_remaining_days( $training['ends_at'] ?? null ),
                ];
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Certificates section
    |--------------------------------------------------------------------------
    |
    | Counts come from the certificate repository counters; issued sums the
    | three lifecycle statuses the presenter treats as issued (issued /
    | active / valid).
    |
    | recent[] is a PREVIEW of the single latest ISSUED certificate only: the
    | same list query /certificates/issued uses, scoped to the authenticated
    | student with the status filter pinned to `issued` and limit 1, so the
    | repository's established issued ordering (approved_at DESC, id DESC -
    | approved_at is the column the presenter maps to issued_at) is reused
    | as-is instead of duplicating it. pending / revoked / eligible rows can
    | therefore never appear here and there is no revoked/pending fallback:
    | a student without an issued certificate gets an empty array.
    |
    | The counts above are intentionally NOT filtered by that preview: the
    | eligible / pending / issued / revoked summary stays exactly as before.
    |
    */

    $certificate_scope = [ 'student_id' => $student_id ];

    $certificates_issued = (
        certificate_repository_count( array_merge( $certificate_scope, [ 'status' => 'issued' ] ) )
        + certificate_repository_count( array_merge( $certificate_scope, [ 'status' => 'active' ] ) )
        + certificate_repository_count( array_merge( $certificate_scope, [ 'status' => 'valid' ] ) )
    );

    $certificates_pending  = certificate_repository_count( array_merge( $certificate_scope, [ 'status' => 'pending' ] ) );
    $certificates_revoked  = certificate_repository_count( array_merge( $certificate_scope, [ 'status' => 'revoked' ] ) );
    $certificates_eligible = count( certificate_repository_eligible( $certificate_scope ) );

    $certificate_recent = [];

    foreach (
        certificate_repository_list(
            array_merge(
                $certificate_scope,
                [ 'status' => 'issued', 'limit' => 1 ]
            )
        ) as $row
    ) {
        if ( is_array( $row ) ) {
            $certificate_recent[] = certificate_service_present( $row, $user_context );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Recommended trainings (specialization-scoped, published, not expired)
    |--------------------------------------------------------------------------
    */

    $recommended_trainings = student_service_dashboard_recommendations( $user_id );

    /*
    |--------------------------------------------------------------------------
    | Recent notifications (up to 5, from the existing notification service)
    |--------------------------------------------------------------------------
    */

    $recent_notifications = [];

    if ( class_exists( 'NotificationService' ) ) {

        $notification_rows = ( new NotificationService() )->list( $user_id, [ 'limit' => 5 ] );

        foreach ( $notification_rows as $row ) {
            if ( is_array( $row ) ) {
                $recent_notifications[] = student_service_dashboard_notification( $row );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Next action (deterministic decision chain)
    |--------------------------------------------------------------------------
    */

    $next_action = student_service_dashboard_next_action( [
        'profile_complete'        => ( $student['is_profile_complete'] ?? 0 ) == 1,
        'completion_percentage'   => $completion_percentage,
        'applications_total'      => $applications_total,
        'applications_applied'    => $applications_applied,
        'certificates_eligible'   => $certificates_eligible,
        'active_training'         => $active_training,
    ] );

    return [ 'data' => [
        'student'                 => $student_presented,
        'quick_stats'             => [
            'active_trainings'    => $applications_accepted,
            'applications'        => $applications_total,
            'issued_certificates' => $certificates_issued,
            'profile_completion'  => $completion_percentage,
        ],
        'active_training'         => $active_training,
        'applications_snapshot'   => [
            'total'           => $applications_total,
            'applied'         => $applications_applied,
            'accepted'        => $applications_accepted,
            'rejected'        => $applications_rejected,
            'withdrawn'       => $applications_withdrawn,
            'acceptance_rate' => $acceptance_rate,
            'recent'          => $recent_applications,
        ],
        'certificates'            => [
            'eligible' => $certificates_eligible,
            'pending'  => $certificates_pending,
            'issued'   => $certificates_issued,
            'revoked'  => $certificates_revoked,
            'recent'   => $certificate_recent,
        ],
        'recommended_trainings'   => $recommended_trainings,
        'recent_notifications'    => $recent_notifications,
        'next_action'             => $next_action,
    ] ];
}


/*
|--------------------------------------------------------------------------
| Dashboard Academic Lookup
|--------------------------------------------------------------------------
|
| Resolves a single academic entity name (study field, faculty or university)
| from its id through the same lookups tables joined by the rest of the app.
| Returns null when the id is missing or the row is unknown, keeping the
| dashboard student section free of ids-only placeholders.
|
*/

function student_service_dashboard_lookup_name( string $table, int $id ): ?string {

    if ($id <= 0) {
        return null;
    }

    $allowed = [ 'study_fields', 'faculties', 'universities' ];

    if ( !in_array( $table, $allowed, true ) ) {
        return null;
    }

    $row = db_fetch_one(
        "SELECT name FROM {$table} WHERE id = ? LIMIT 1",
        [ $id ]
    );

    if ( !is_array( $row ) || empty( $row['name'] ) ) {
        return null;
    }

    return (string) $row['name'];
}


/*
|--------------------------------------------------------------------------
| Dashboard Recommended Trainings
|--------------------------------------------------------------------------
|
| Returns AT MOST 2 recommendations for the authenticated student:
|
|   1. the most-applied PAID training  (is_paid = true)
|   2. the most-applied FREE training  (is_paid = false)
|
| Eligibility is NOT re-implemented here. Each category is built by the same
| search filter pipeline the dashboard already used
| (search_service_trainings_filters), only with the price dimension pinned, so
| every pre-existing rule is preserved as-is:
|
|   - t.status = 'published'
|   - not expired: (t.ends_at IS NULL OR t.ends_at >= NOW())
|   - the student's own specialization, resolved SQL-level from
|     students.specialization_id == training_listings.specialization_id
|
| plus the three canonical training types (Shadowing / Hands-on /
| Project-based) this dashboard has always trimmed to.
|
| "Most applied" is the real number of application RECORDS for the training,
| taken from the existing application_repository_count_by_training() counter
| (status-agnostic, so every actual application counts). Saved trainings,
| views, certificates and notifications are deliberately not involved, and no
| new metric or column is introduced.
|
| Ranking is application count DESC with training id DESC as the deterministic
| tie-breaker, so a full tie always resolves the same way. Each category
| contributes at most one card, so the section can never contain two paid or
| two free trainings, and a category with no eligible training simply
| contributes nothing (both missing -> an empty array).
|
| Every card value still comes from the existing training card query; nothing
| here re-invents discovery logic.
|
*/

function student_service_dashboard_recommendations( int $user_id ): array {

    $allowed_types = [
        TRAINING_TYPE_SHADOWING,
        TRAINING_TYPE_HANDS_ON,
        TRAINING_TYPE_PROJECT_BASED,
    ];

    $recommended = [];

    /*
    | Paid first, then free: each iteration pins the price dimension and keeps
    | the single best candidate of that category.
    */

    foreach ( [ 1, 0 ] as $is_paid ) {

        $result = search_service_trainings_filters( [
            'user_id' => $user_id,
            'role'    => 'student',
            'page'    => 1,
            'limit'   => 100,
            'sort'    => 'newest',
            'paid'    => $is_paid,
        ] );

        $candidates = [];

        foreach ( (array) ( $result['items'] ?? [] ) as $item ) {

            if ( !is_array( $item ) ) {
                continue;
            }

            $training_id = (int) ( $item['id'] ?? 0 );

            if ( $training_id <= 0 ) {
                continue;
            }

            $type = strtolower( trim( (string) ( $item['training_type'] ?? '' ) ) );

            if ( !in_array( $type, $allowed_types, true ) ) {
                continue;
            }

            $candidates[] = [
                'item'         => $item,
                'training_id'  => $training_id,
                'type'         => $type,
                'applications' => application_repository_count_by_training( $training_id ),
            ];
        }

        usort(
            $candidates,
            static function ( array $a, array $b ): int {

                if ( (int) $a['applications'] === (int) $b['applications'] ) {
                    return (int) $b['training_id'] <=> (int) $a['training_id'];
                }

                return (int) $b['applications'] <=> (int) $a['applications'];
            }
        );

        if ( !empty( $candidates ) ) {

            $recommended[] = student_service_dashboard_recommendation_card(
                $candidates[0]['item'],
                $candidates[0]['type']
            );
        }
    }

    return $recommended;
}


/*
|--------------------------------------------------------------------------
| Dashboard Recommendation Card
|--------------------------------------------------------------------------
|
| Maps one training card row (as returned by the search pipeline) into the
| compact dashboard recommendation card. The pre-existing fields are returned
| exactly as before; three commercial/audit fields were added:
|
|   price           training_listings.compensation_amount, the project's single
|                   price column (the same column the price_asc / price_desc
|                   search sorts and the payment fee validation read). Cast to
|                   float, matching the existing money convention in
|                   ApplicationService, so a whole amount serialises as 3600
|                   rather than the DECIMAL(12,2) string "3600.00". Forced to
|                   null for a free training: the gate is is_paid, NOT the
|                   column being NULL.
|
|   free_trial_days training_listings.trial_period_days, the column the
|                   repository already writes on create/update as
|                   max(7, <configured days>) and null for a free training.
|                   Exposed under the same key application_cards.php already
|                   uses in the student-facing application DTO, and gated on
|                   is_paid for the same reason: seeded free trainings can carry
|                   a non-null trial_period_days, which must not leak here.
|
|   created_at      training_listings.created_at. This is the column the whole
|                   codebase orders recency by (search sort "newest" is
|                   ORDER BY t.created_at DESC, as are the public list and the
|                   company list), so the exposed value stays consistent with
|                   the ordering these recommendations were selected from.
|                   training_listings.published_at also exists and is never
|                   null for a published row, but the project consistently
|                   treats created_at - not published_at - as the recency
|                   source, so created_at is the correct one to expose.
|
|   The value is not derived, re-scaled or invented: it is the stored column,
|   formatted with the same application_iso8601() helper every other timestamp
|   in this dashboard uses, so the section keeps a single timestamp format.
|
|   application_deadline  training_listings.application_deadline: the last
|       moment a student may SUBMIT an application, which is a different date
|       from the training's start or end. It is the same column the apply
|       guard in ApplicationService reads ("The application deadline has
|       passed.") and the same column the deadline_asc search sort orders by,
|       so the DTO reports exactly the value the business rules enforce.
|       There is deliberately NO fallback: when the column is NULL the card
|       reports null, which application_iso8601() already does for a NULL or
|       empty input. It never substitutes ends_at, starts_at or any derived
|       date, because that would invent a deadline the training does not have
|       and would silently diverge from the rule the Applications API applies.
|       The deadline is not a discovery or eligibility condition anywhere in
|       the project (search_repository and training_repository both document
|       that explicitly), so exposing the value cannot change which trainings
|       are selected. Only the stored value is surfaced; no deadline business
|       rule is read, evaluated or modified here.
|
*/

function student_service_dashboard_recommendation_card( array $item, string $type ): array {

    $is_paid = (bool) ( $item['is_paid'] ?? false );

    /*
    | Free trainings never expose a price or a trial allowance. Both are gated
    | on is_paid rather than on a NULL column, because a free training row can
    | legitimately still carry a compensation_amount / trial_period_days value
    | and leaking it would misrepresent the training as paid.
    */

    $price = null;

    if ( $is_paid ) {

        $amount = $item['compensation_amount'] ?? null;

        if ( $amount !== null && $amount !== '' && is_numeric( $amount ) ) {
            $price = (float) $amount;
        }
    }

    $free_trial_days = null;

    if ( $is_paid ) {

        $trial_period_days = $item['trial_period_days'] ?? null;

        if ( $trial_period_days !== null && $trial_period_days !== '' ) {
            $free_trial_days = (int) $trial_period_days;
        }
    }

    return [
        'id'                => (int) ( $item['id'] ?? 0 ),
        'title'             => (string) ( $item['title'] ?? '' ),
        'company'           => $item['company_name'] ?? null,
        'company_logo'      => $item['company_logo'] ?? null,
        'type'              => $type,
        'mode'              => (string) ( $item['mode'] ?? '' ),
        'is_paid'           => $is_paid,
        'price'             => $price,
        'free_trial_days'   => $free_trial_days,
        'created_at'        => application_iso8601( ( $item['created_at'] ?? null ) ),
        'application_deadline' => application_iso8601( ( $item['application_deadline'] ?? null ) ),
        'starts_at'         => application_iso8601( ( $item['starts_at'] ?? null ) ),
        'ends_at'           => application_iso8601( ( $item['ends_at'] ?? null ) ),
        'specialization'    => training_card_specialization( $item ),
        'duration'          => training_calculate_duration( $item['starts_at'] ?? null, $item['ends_at'] ?? null ),
        'remaining_days'    => training_calculate_remaining_days( $item['ends_at'] ?? null ),
    ];
}


/*
|--------------------------------------------------------------------------
| Dashboard Notification DTO
|--------------------------------------------------------------------------
|
| Maps a raw notification row (as returned by NotificationService::list)
| into the compact dashboard notification card. Only read state and safe
| display fields are exposed.
|
*/

function student_service_dashboard_notification( array $row ): array {

    return [
        'id'         => (int) ( $row['id'] ?? 0 ),
        'type'       => (string) ( $row['type'] ?? '' ),
        'title'      => (string) ( $row['title'] ?? '' ),
        'body'       => (string) ( $row['body'] ?? '' ),
        'read'       => !empty( $row['read_at'] ),
        'created_at' => application_iso8601( $row['created_at'] ?? null ),
    ];
}


/*
|--------------------------------------------------------------------------
| Dashboard Next Action
|--------------------------------------------------------------------------
|
| Deterministic decision chain that always returns one actionable step
| derived purely from the student's own state:
|
|   1. Incomplete profile      -> complete it first.
|   2. No applications yet     -> discover trainings.
|   3. Training still running  -> continue it.
|   4. Eligible certificate(s) -> request one.
|   5. Pending applications    -> track them.
|   6. Fallback                -> discover trainings.
|
*/

function student_service_dashboard_next_action( array $state ): array {

    if ( empty( $state['profile_complete'] ) ) {
        return [
            'type'    => 'complete_profile',
            'title'   => 'Complete your profile',
            'message' => 'Add your field, specialization, skills or CV to unlock personalized training recommendations.',
            'target'  => '/student/profile',
        ];
    }

    if ( (int) ( $state['applications_total'] ?? 0 ) === 0 ) {
        return [
            'type'    => 'explore_trainings',
            'title'   => 'Discover trainings',
            'message' => 'Start exploring training opportunities that match your specialization.',
            'target'  => '/student/search/trainings',
        ];
    }

    $active_training = is_array( $state['active_training'] ?? null ) ? $state['active_training'] : null;

    if (
        $active_training !== null
        &&
        ( (int) ( $active_training['remaining_days'] ?? 0 ) > 0 )
    ) {
        return [
            'type'    => 'continue_training',
            'title'   => 'Continue your training',
            'message' => 'You have an ongoing training - keep progressing until it ends.',
            'target'  => '/student/training/' . (int) ( $active_training['id'] ?? 0 ),
        ];
    }

    if ( (int) ( $state['certificates_eligible'] ?? 0 ) > 0 ) {
        return [
            'type'    => 'request_certificate',
            'title'   => 'Request a certificate',
            'message' => 'You are eligible for ' . (int) ( $state['certificates_eligible'] ?? 0 ) . ' certificate(s) for a completed training.',
            'target'  => '/student/certificates',
        ];
    }

    if ( (int) ( $state['applications_applied'] ?? 0 ) > 0 ) {
        return [
            'type'    => 'track_applications',
            'title'   => 'Track your applications',
            'message' => 'Some of your applications are still waiting for review.',
            'target'  => '/student/applications',
        ];
    }

    return [
        'type'    => 'explore_trainings',
        'title'   => 'Discover trainings',
        'message' => 'Look for new training opportunities that fit your goals.',
        'target'  => '/student/search/trainings',
    ];
}