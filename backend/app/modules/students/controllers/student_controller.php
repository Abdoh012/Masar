<?php

/**
 * MASAR - Student Controller
 *
 * Handles student-related HTTP requests.
 *
 * Responsibilities:
 * - Get authenticated student's profile.
 * - Create student profile.
 * - Update student profile.
 * - View student profiles.
 *
 * Business logic belongs to student_service.php.
 * Database operations belong to student_repository.php.
 * Validation belongs to student_validator.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';

require_once __DIR__ . '/../services/student_service.php';
require_once __DIR__ . '/../services/student_profile_service.php';

require_once __DIR__ . '/../validators/student_validator.php';

require_once __DIR__ . '/../../../modules/files/repositories/file_repository.php';


/*
|--------------------------------------------------------------------------
| Get My Student Profile
|--------------------------------------------------------------------------
|
| GET /api/students/me
|
*/

function student_me(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $result = student_get_profile( (int) $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to retrieve student profile.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? $result);
}


/*
|--------------------------------------------------------------------------
| Create Student Profile
|--------------------------------------------------------------------------
|
| POST /api/students/profile
|
*/

function student_create_profile(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can create a student profile.' );
        return;
    }

    $data = request_input();
    $errors = student_validate_profile( $data );

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = student_service_create_profile( (int) $user['id'], $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to create student profile.', $result['status'] ?? 400 );
        return;
    }

    response_created($result['data'] ?? $result);
}


/*
|--------------------------------------------------------------------------
| Update Student Profile
|--------------------------------------------------------------------------
|
| PUT /api/students/profile
|
*/

function student_update_profile(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can update a student profile.' );
        return;
    }

    $data = request_input();
    $errors = student_validate_profile_update( $data );

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = student_service_update_profile( (int) $user['id'], $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to update student profile.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Get Student Profile By ID
|--------------------------------------------------------------------------
|
| GET /api/students/{id}
|
*/

function student_show( int $student_id ): void {

    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ($student_id <= 0) {
        response_error( 'Invalid student ID.', 422 );
        return;
    }

    $result = student_get_public_profile( $student_id, $user );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to retrieve student profile.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Complete Student Profile
|--------------------------------------------------------------------------
|
| POST /api/students/profile/complete
|
*/

function student_complete_profile(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can complete a student profile.' );
        return;
    }

    $data = request_input();
    $errors = student_validate_complete_profile( $data );

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = student_complete_profile_data( (int) $user['id'], $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to complete student profile.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Student Profile Status
|--------------------------------------------------------------------------
|
| GET /api/students/profile/status
||
*/

function student_profile_status(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $result = student_get_profile_status( (int) $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to retrieve profile status.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Get My Skills
|--------------------------------------------------------------------------
|
| GET /api/students/me/skills
|
*/

function student_skills_index(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $profile = student_profile_repository_find_by_student_id( (int) $student['id'] );

    response_success( [ 'skills' => is_array( $profile['skills'] ?? null ) ? $profile['skills'] : [], ] );
}


/*
|--------------------------------------------------------------------------
| Add Skill
|--------------------------------------------------------------------------
|
| POST /api/students/me/skills
|
*/

function student_skill_add(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $data = request_input();
    $skill = trim( (string) ( $data['skill'] ?? '' ) );
    $validation = student_validator_add_skill( $skill );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_add_skill( (int) $student['id'], $skill );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to add skill.', $result['status'] ?? 400 );
        return;
    }

    response_created( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Update Skills (Replace All)
|--------------------------------------------------------------------------
|
| PUT /api/students/me/skills
|
*/

function student_skills_update(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $data = request_input();
    $skills = $data['skills'] ?? null;
    $validation = student_validator_skills( $skills );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_update_skills( (int) $student['id'], $skills );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to update skills.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Remove Skill
|--------------------------------------------------------------------------
|
| DELETE /api/students/me/skills
|
*/

function student_skill_remove(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $data = request_input();
    $skill = trim( (string) ( $data['skill'] ?? '' ) );
    $validation = student_validator_add_skill( $skill );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_remove_skill( (int) $student['id'], $skill );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to remove skill.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Get My CV
|--------------------------------------------------------------------------
|
| GET /api/students/me/cv
|
*/

function student_cv_show(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $file_id = student_profile_repository_get_cv_file_id( (int) $student['id'] );

    response_success( [ 'cv_file_id' => $file_id, ] );
}


/*
|--------------------------------------------------------------------------
| Set CV
|--------------------------------------------------------------------------
|
| POST /api/students/me/cv
|
*/

function student_cv_set(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $data = request_input();
    $file_id = (int) ( $data['file_id'] ?? 0 );
    $validation = student_validator_cv_file_id( $file_id );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $file = file_repository_find_for_user( $file_id, (int) $user['id'] );

    if (!$file) {
        response_error( 'File not found or does not belong to you.', 422 );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_set_cv( (int) $student['id'], $file_id );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to attach CV.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Remove CV
|--------------------------------------------------------------------------
|
| DELETE /api/students/me/cv
|
*/

function student_cv_remove(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ( ($user['role'] ?? '') !== 'student' ) {
        response_forbidden( 'Only students can access this resource.' );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_remove_cv( (int) $student['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to remove CV.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}
