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
require_once __DIR__ . '/../services/student_account_service.php';
require_once __DIR__ . '/../services/student_education_service.php';
require_once __DIR__ . '/../services/student_profile_view_service.php';

require_once __DIR__ . '/../validators/student_validator.php';

require_once __DIR__ . '/../../../modules/files/repositories/file_repository.php';


/*
|--------------------------------------------------------------------------
| Authenticated Student Context
|--------------------------------------------------------------------------
|
| Every "me" endpoint below resolves its target through this one helper. The
| user id comes from the auth context and the student row is then looked up by
| that id, so no endpoint in this file can be pointed at another student: a
| client-supplied student_id is never read.
|
| The 401/403 split mirrors the existing endpoints in this file - no session
| is 401, and a session that is not a student is 403 - and middleware_student()
| has already run, so in practice this is the second, defence-in-depth check.
|
*/
function student_controller_auth_student(): ?array
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized('Authentication required.');
        return null;
    }

    if (($user['role'] ?? '') !== 'student') {
        response_forbidden('Only students can access this resource.');
        return null;
    }

    $student = student_repository_find_by_user_id((int) $user['id']);

    if (!$student) {
        response_not_found('Student profile not found.');
        return null;
    }

    return ['user' => $user, 'student' => $student];
}

/*
| Student Controller Response
|
| The shared pass-through for the service results below. `created` selects
| response_created() (201) for the endpoints that add a resource, matching how
| the existing create endpoints in this file answer.
*/
function student_controller_respond(array $result, bool $created = false): void
{
    if (isset($result['error']) && $result['error'] === true) {
        response_error(
            $result['message'] ?? 'Unable to process the request.',
            $result['status'] ?? 400
        );
        return;
    }

    if (!empty($result['errors']) && is_array($result['errors'])) {
        response_validation_error($result['errors']);
        return;
    }

    $data = $result['data'] ?? null;
    $message = $result['message'] ?? null;

    if ($created) {
        response_created($data, $message);
        return;
    }

    response_success($data, $message);
}


/*
|--------------------------------------------------------------------------
| Get My Student Dashboard
|--------------------------------------------------------------------------
|
| GET /api/v1/students/dashboard
|
*/

function student_dashboard(): void
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

    $result = student_service_dashboard( (int) $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to load dashboard.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? []);
}


/*
|--------------------------------------------------------------------------
| Get My Student Profile
|--------------------------------------------------------------------------
|
| GET /api/students/me
|
| Returns ONLY `data.header`, the thirteen-field student self header.
|
| The `student` and `profile` objects this endpoint used to expose were removed
| from the contract, so it no longer builds them. The service call was switched
| rather than the old response being post-processed, because doing the work and
| then dropping it would keep two queries alive for nothing.
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

    $result = student_service_me_header( (int) $user['id'] );

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
    $validation = student_validator_add_skill( $data['skill'] ?? null );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_add_skill( (int) $student['id'], (int) ( $data['skill'] ?? 0 ) );

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
    $validation = student_validator_skill_ids( $skills );

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
    $validation = student_validator_add_skill( $data['skill'] ?? null );

    if ( !$validation['valid'] ) {
        response_validation_error( $validation['errors'] );
        return;
    }

    $student = student_repository_find_by_user_id( (int) $user['id'] );

    if (!$student) {
        response_not_found( 'Student profile not found.' );
        return;
    }

    $result = student_profile_remove_skill( (int) $student['id'], (int) ( $data['skill'] ?? 0 ) );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to remove skill.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
|
| GET  /api/v1/students/me/full-name
| PUT  /api/v1/students/me/full-name
|
| The account's real name (students.full_name), not the public username. The
| student is resolved from the auth context only; no client-supplied id is read.
|
*/

function student_full_name_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_full_name($context['user']));
}

function student_full_name_update(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_update_full_name($context['user'], request_input()));
}


/*
|--------------------------------------------------------------------------
| Username
|--------------------------------------------------------------------------
|
| GET  /api/v1/students/me/username
| PUT  /api/v1/students/me/username
|
| The public handle. It is a separate identity from the account email: the
| handle is what is shown next to the student's name, and the email is never
| returned as the handle.
|
*/

function student_username_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_username($context['user']));
}

function student_username_update(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_update_username($context['user'], request_input()));
}


/*
|--------------------------------------------------------------------------
| Contact Info
|--------------------------------------------------------------------------
|
| GET  /api/v1/students/me/contact
| PUT  /api/v1/students/me/contact
|
| Reuses users.email and the students contact columns. This endpoint does not
| verify a password; password changes remain the separate auth flow.
|
*/

function student_contact_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_contact($context['user']));
}

function student_contact_update(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_update_contact($context['user'], request_input()));
}


/*
|--------------------------------------------------------------------------
| Academic Status
|--------------------------------------------------------------------------
|
| GET  /api/v1/students/me/academic-status
| PUT  /api/v1/students/me/academic-status
|
*/

function student_academic_status_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_academic_status($context['user']));
}

function student_academic_status_update(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    $data = request_input();
    $file = request_file('graduation_certificate');

    student_controller_respond(student_account_update_academic_status($context['user'], $data, $file));
}

function student_academic_status_remove_certificate(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_remove_graduation_certificate($context['user']));
}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
|
| POST   /api/v1/students/me/profile-image   (multipart, field `image`)
| DELETE /api/v1/students/me/profile-image
|
| This writes the field the profile header already reads, so the header image
| becomes a real value instead of a permanent null.
|
| The upload goes through the shared upload service. This controller only
| resolves WHICH file and reports the result; every content check lives in
| student_account_validate_upload() before the upload service is called.
|
*/

function student_profile_image_store(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    $file = request_file('image') ?? request_file('file') ?? request_file('profile_image');

    if (empty($file)) {
        response_validation_error(['image' => 'An image file is required.']);
        return;
    }

    student_controller_respond(student_account_upload_profile_image($context['user'], $file));
}

function student_profile_image_remove(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_account_remove_profile_image($context['user']));
}


/*
|--------------------------------------------------------------------------
| Profile Views
|--------------------------------------------------------------------------
|
| GET /api/v1/students/me/profile-views
|
| The "N people viewed your profile" total and the recent viewers. The total
| is a count of real rows; nothing here is a placeholder number.
|
*/

function student_profile_views_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    $limit = request_get_int('limit', PROFILE_VIEW_RECENT_LIMIT);

    student_controller_respond(
        student_profile_view_summary((int) $context['student']['id'], $limit === null ? null : $limit)
    );
}


/*
|--------------------------------------------------------------------------
| Education
|--------------------------------------------------------------------------
|
| GET    /api/v1/students/me/education
| POST   /api/v1/students/me/education
| PUT    /api/v1/students/me/education/{id}
| DELETE /api/v1/students/me/education/{id}
|
| Every read and write is scoped to the authenticated student, so an id
| belonging to another student is reported as not found rather than acted on.
|
*/

function student_education_index(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(student_education_service_list((int) $context['student']['id']));
}

function student_education_store(): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    $data = request_input();

    $errors = student_validate_education($data);

    if (!empty($errors)) {
        response_validation_error($errors);
        return;
    }

    student_controller_respond(
        student_education_service_create((int) $context['student']['id'], $data),
        true
    );
}

function student_update_education(int $education_id): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    $data = request_input();

    $errors = student_validate_education($data, true);

    if (!empty($errors)) {
        response_validation_error($errors);
        return;
    }

    student_controller_respond(
        student_education_service_update((int) $context['student']['id'], $education_id, $data)
    );
}

function student_education_destroy(int $education_id): void
{
    $context = student_controller_auth_student();

    if ($context === null) {
        return;
    }

    student_controller_respond(
        student_education_service_delete((int) $context['student']['id'], $education_id)
    );
}
