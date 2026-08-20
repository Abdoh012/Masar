<?php

/**
 * MASAR - Student Profile Service
 *
 * Handles the business logic of the extended student profile.
 *
 * Responsibilities:
 * - Manage student skills.
 * - Manage profile information.
 * - Prepare profile data for display.
 * - Handle CV reference information.
 * - Check profile completeness.
 *
 * IMPORTANT:
 * - CV file storage/upload is handled by the files module.
 * - Database operations are handled by student_profile_repository.php.
 * - No HTTP logic belongs here.
 */

require_once __DIR__ . '/../repositories/student_profile_repository.php';
require_once __DIR__ . '/../repositories/student_repository.php';

require_once __DIR__ . '/../../../core/database/transaction.php';

require_once __DIR__ . '/../../files/repositories/file_repository.php';
require_once __DIR__ . '/../../files/services/file_upload_service.php';

function student_profile_get( int $student_id ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $profile = student_profile_repository_find_by_student_id( $student_id );

    if (!$profile) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile data not found.' ];
    }

    $profile['skills'] = student_profile_normalize_skills( $profile['skills'] ?? [] );
    $profile['cv'] = student_profile_normalize_cv( $profile );
    return [ 'data' => $profile, ];
}

function student_profile_update_skills( int $student_id, array $skills ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $skills = student_profile_clean_skills( $skills );

    if (empty($skills)) {
        return [ 'error' => true, 'status' => 422, 'message' => 'At least one skill is required.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $updated = student_profile_repository_update( $student_id, [ 'skills' => $skills ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update student skills.' ];
    }

    return student_profile_get( $student_id );
}

function student_profile_add_skill( int $student_id, string $skill ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $skill = trim($skill);

    if ($skill === '') {
        return [ 'error' => true, 'status' => 422, 'message' => 'Skill cannot be empty.' ];
    }

    $profile = student_profile_repository_find_by_student_id( $student_id );

    if (!$profile) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $skills = student_profile_normalize_skills( $profile['skills'] ?? [] );

    foreach ($skills as $existing_skill) {
        if ( strtolower($existing_skill) === strtolower($skill) ) {
            return [ 'error' => true, 'status' => 409, 'message' => 'This skill already exists.' ];
        }
    }

    $skills[] = $skill;
    $updated = student_profile_repository_update( $student_id, [ 'skills' => $skills ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to add skill.' ];
    }

    return [ 'data' => [ 'skills' => $skills, ], ];
}

function student_profile_remove_skill( int $student_id, string $skill ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $skill = trim($skill);

    if ($skill === '') {
        return [ 'error' => true, 'status' => 422, 'message' => 'Skill cannot be empty.' ];
    }

    $profile = student_profile_repository_find_by_student_id( $student_id );

    if (!$profile) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $skills = student_profile_normalize_skills( $profile['skills'] ?? [] );
    $new_skills = [];

    foreach ($skills as $existing_skill) {
        if ( strtolower($existing_skill) !== strtolower($skill) ) {
            $new_skills[] = $existing_skill;
        }
    }

    if ( count($skills) === count($new_skills) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Skill not found.' ];
    }

    $updated = student_profile_repository_update( $student_id, [ 'skills' => $new_skills ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500,  'message' => 'Unable to remove skill.' ];
    }

    return [ 'data' => [ 'skills' => $new_skills, ], ];
}

function student_profile_set_cv( int $student_id, int $file_id ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    if ($file_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid file ID.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $updated = student_profile_repository_update( $student_id, [ 'cv_file_id' => $file_id ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to attach CV.' ];
    }

    return [ 'data' => [ 'message' => 'CV attached successfully.', 'cv_file_id' => $file_id, ], ];
}

function student_profile_remove_cv( int $student_id ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $file_id = student_profile_repository_get_cv_file_id( $student_id );

    if (!$file_id) {
        return [ 'error' => true, 'status' => 404, 'message' => 'No CV found.' ];
    }

    $user_id = (int) ( $student['user_id'] ?? 0 );

    /*
     * The file record is resolved from the student's stored CV reference and is
     * scoped to the authenticated user, so a student can never touch another
     * user's file and the path is never taken from the client.
     */

    $file = file_repository_find_for_user( $file_id, $user_id );

    /*
     * Stale reference: the file row is missing or does not belong to this user.
     * Clear the reference so the profile no longer points at a phantom file.
     * Nothing is deleted because we cannot attribute the file to this user.
     */

    if (!$file) {
        student_profile_repository_remove_cv( $student_id );
        return [ 'data' => [ 'message' => 'CV removed successfully.', ], ];
    }

    /*
     * Security: never unlink a path that is not stored inside the application's
     * upload/storage directory. An unsafe (relative, traversal or otherwise
     * out-of-tree) stored path is logged and left untouched.
     */

    $path = (string) ( $file['path'] ?? '' );
    $safe = file_upload_service_is_safe_storage_path( $path );

    if (!$safe) {
        error_log( 'MASAR CV removal: refusing to delete unsafe stored path: ' . $path );
    } elseif (is_file( $path )) {

        if (!@unlink( $path )) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to delete the CV file.' ];
        }
    }

    /*
     * If the physical file is already missing, continue and clean the metadata.
     * The filesystem and the database cannot be rolled back together, so the two
     * database writes (delete the file record, clear the CV reference) run inside
     * one transaction after the physical file is removed. If the database part
     * fails we do not silently report success.
     */

    db_begin_transaction();

    try {

        $cv_cleared = student_profile_repository_remove_cv( $student_id );
        $file_deleted = file_repository_delete( $file_id, $user_id );

        if ( !$cv_cleared || !$file_deleted ) {
            db_rollback();
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove CV metadata.' ];
        }

        db_commit();

    } catch (Throwable $e) {

        db_rollback();
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove CV metadata.' ];
    }

    return [ 'data' => [ 'message' => 'CV removed successfully.', ], ];
}


function student_profile_is_complete( int $student_id ): array {
    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $student = student_repository_find_by_id( $student_id );

    if (!$student) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student not found.' ];
    }

    $profile = student_profile_repository_find_by_student_id( $student_id );

    if (!$profile) {
        return [ 'data' => [ 'complete' => false, 'missing' => [ 'profile' => true, ], ], ];
    }

    $missing = [];
    $required_student_fields = [ 'field_id', 'specialization_id',];

    foreach ( $required_student_fields as $field ) {
        if ( empty( $student[$field] ?? null ) ) {
            $missing[] = $field;
        }
    }

    $skills = student_profile_normalize_skills( $profile['skills'] ?? [] );

    if (empty($skills)) {
        $missing[] = 'skills';
    }

    if ( empty( $profile['cv_file_id'] ?? null ) ) {
        $missing[] = 'cv';
    }

    return [ 'data' => [ 'complete' => empty($missing), 'missing' => $missing, ], ];
}

function student_profile_normalize_skills( mixed $skills ): array {

    if ( is_array($skills) ) {
        return student_profile_clean_skills( $skills );
    }

    if ( is_string($skills) && trim($skills) !== '' ) {
        $decoded = json_decode( $skills, true );

        if ( is_array($decoded) ) {
            return student_profile_clean_skills( $decoded );
        }
    }

    return [];
}

function student_profile_clean_skills( array $skills ): array {

    $clean = [];

    foreach ($skills as $skill) {
        if ( !is_string($skill) && !is_numeric($skill) ) {
            continue;
        }

        $skill = trim( (string) $skill );

        if ($skill === '') {
            continue;
        }

        if ( strlen($skill) > 100 ) {
            $skill = substr( $skill, 0, 100 );
        }

        $exists = false;

        foreach ($clean as $existing) {
            if ( strtolower($existing) === strtolower($skill) ) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $clean[] = $skill;
        }
    }

    return array_values( $clean );
}

function student_profile_normalize_cv( array $profile ): ?array {
    $file_id = $profile['cv_file_id'] ?? null;

    if ( empty($file_id) ) {
        return null;
    }

    return [ 'file_id' => (int) $file_id, ];
}
