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
 * - Check profile completeness.
 *
 * IMPORTANT:
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
    return [ 'data' => $profile, ];
}

function student_profile_update_skills( int $student_id, array $skills ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    $names = [];
    $unknown = [];

    foreach ( $skills as $skill_id ) {
        $resolved = student_profile_repository_find_skill_by_id( (int) $skill_id );

        if ( $resolved === null ) {
            $unknown[] = (int) $skill_id;
            continue;
        }

        $names[] = trim( (string) ( $resolved['name'] ?? '' ) );
    }

    if ( !empty( $unknown ) ) {
        return student_profile_unknown_skills_error( $unknown );
    }

    $skills = student_profile_clean_skills( $names );

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

function student_profile_add_skill( int $student_id, int $skill ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    /*
     | The caller sends the skill as its taxonomy lookup id. Resolve the id to
     | the human-readable name first, then run the existing name-based workflow
     | so the duplicate guard, the shared-taxonomy checks and the sync all keep
     | reading from the same source of truth.
     */

    $resolved = student_profile_repository_find_skill_by_id( $skill );

    if ( $resolved === null ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'The selected skill is not recognized.',
            'errors'  => [ 'skill' => 'Unknown skill.' ],
        ];
    }

    $skill = trim( (string) ( $resolved['name'] ?? '' ) );

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

    /*
     | The bulk PUT path caps the set, so the single add path is capped too.
     | Without this, repeated POSTs grow a student's skill list without limit
     | while the equivalent PUT would have been refused.
     */
    if ( count( $skills ) >= STUDENT_MAX_SKILLS ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'You cannot add more than ' . STUDENT_MAX_SKILLS . ' skills.',
        ];
    }

    $unknown = student_profile_unknown_skills( [ $skill ] );

    if ( !empty( $unknown ) ) {
        return student_profile_unknown_skills_error( $unknown );
    }

    $skills[] = $skill;
    $updated = student_profile_repository_update( $student_id, [ 'skills' => $skills ] );

    if (!$updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to add skill.' ];
    }

    return [ 'data' => [ 'skills' => $skills, ], ];
}

/*
|--------------------------------------------------------------------------
| Unknown Skills
|--------------------------------------------------------------------------
|
| A student's skills must come from the shared `skills` taxonomy, because that
| same list is what companies filter candidates by. A name that is not in the
| taxonomy is refused here with a 422 naming the offending skills, rather than
| being added to the shared table.
|
| The repository resolves rather than inserts as a second line of defence, so
| even a caller reaching sync_skills without going through here cannot write a
| new taxonomy row.
|
*/

function student_profile_unknown_skills( array $skills ): array {

    $names = [];

    foreach ( $skills as $skill ) {
        if ( is_string( $skill ) || is_numeric( $skill ) ) {
            $name = trim( (string) $skill );

            if ( $name !== '' ) {
                $names[] = $name;
            }
        }
    }

    if ( empty( $names ) ) {
        return [];
    }

    $resolved = student_profile_repository_find_skill_ids( $names );

    $unknown = [];

    foreach ( array_unique( $names ) as $name ) {
        if ( !isset( $resolved[ strtolower( $name ) ] ) ) {
            $unknown[] = $name;
        }
    }

    return $unknown;
}

function student_profile_unknown_skills_error( array $unknown ): array {

    return [
        'error'   => true,
        'status'  => 422,
        'message' => 'One or more skills are not in the available skills list.',
        'errors'  => [ 'skills' => 'Unknown skill: ' . implode( ', ', $unknown ) . '.' ],
    ];
}

function student_profile_remove_skill( int $student_id, int $skill ): array {

    if ($student_id <= 0) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    /*
     | The caller sends the skill as its taxonomy lookup id; resolve it to the
     | human-readable name so the removal can compare against the stored list.
     */
    $resolved = student_profile_repository_find_skill_by_id( $skill );

    if ( $resolved === null ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'The selected skill is not recognized.', 'errors' => [ 'skill' => 'Unknown skill.' ] ];
    }

    $skill = trim( (string) ( $resolved['name'] ?? '' ) );

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
