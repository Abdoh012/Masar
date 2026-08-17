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

    $profile = student_profile_repository_find_by_student_id( $student_id );

    if (!$profile) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $updated = student_profile_repository_update( $student_id, [ 'cv_file_id' => null ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove CV.' ];
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
    $required_student_fields = [ 'university_id', 'faculty_id', 'specialization_id',];

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
