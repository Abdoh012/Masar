<?php

/**
 * MASAR - Student Account Service
 *
 * Business rules for the Student Profile sections that are about the
 * ACCOUNT rather than the public profile: the public username, the contact
 * details, the academic status, and the two document uploads (profile image
 * and academic certificate).
 *
 * Responsibilities:
 * - Read / update the student's full name (students.full_name).
 * - Read / update the public username (users.username).
 * - Read / update the contact details.
 * - Read / update the academic status and graduation month.
 * - Upload / clear the profile image and the academic certificate.
 *
 * IMPORTANT:
 * - No SQL.
 * - No HTTP handling.
 * - Every mutation is keyed on the authenticated user id that the controller
 *   derives from the auth context. A client-supplied student_id or user_id is
 *   never read here, so no caller can aim a write at another account.
 * - Database access belongs to the repositories.
 * - No application, training, payment or certificate record is ever touched.
 */

require_once __DIR__ . '/../repositories/student_repository.php';
require_once __DIR__ . '/../../files/repositories/file_repository.php';
require_once __DIR__ . '/../../files/services/file_upload_service.php';
require_once __DIR__ . '/../../users/repositories/user_repository.php';
require_once __DIR__ . '/../../../shared/functions/application_cards.php';
require_once __DIR__ . '/../../../shared/functions/email.php';
require_once __DIR__ . '/../../../shared/functions/file_urls.php';


/*
|--------------------------------------------------------------------------
| Resolve The Authenticated Student
|--------------------------------------------------------------------------
|
| Every function in this file starts here. The authenticated user id comes
| from the auth context only; the student row is then looked up by user_id, so
| the whole file is structurally incapable of operating on a student the
| caller did not authenticate as.
|
*/
function student_account_require_student( array $auth_user ): ?array {

    $user_id = (int) ( $auth_user['id'] ?? 0 );

    if ( $user_id <= 0 ) {
        return null;
    }

    $student = student_repository_find_by_user_id( $user_id );

    return is_array( $student ) ? $student : null;
}


/*
|--------------------------------------------------------------------------
| Username
|--------------------------------------------------------------------------
*/

/*
 | The public handle is a SEPARATE identity from the account email. It is read
 | straight from users.username and is never derived from, or exposed as, the
 | email: the product states companies see the handle next to the name, not
 | the email.
 */
function student_account_username( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $user_id = (int) $auth_user['id'];

    return [
        'data' => [
            'user_id'  => $user_id,
            'username' => user_repository_find_username( $user_id ),
        ],
    ];
}

/*
 | Rules (the policy agreed for this previously non-existent field):
 |   3-30 characters, must start with a letter, then letters / digits / _ / .
 |   compared case-insensitively, unique across all accounts.
 |
 | The uniqueness conflict is reported as 409, which is the existing conflict
 | convention in this codebase, and it is checked before the write so the
 | caller gets a useful message instead of a driver error from the UNIQUE
 | index. Case-insensitive both ways, so `Nour` is refused when `nour` exists.
 */
function student_account_update_username( array $auth_user, array $data ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $user_id   = (int) $auth_user['id'];
    $username  = trim( (string) ( $data['username'] ?? '' ) );

    /*
     | The validator returns ['valid' => bool, 'errors' => array]. Reading the
     | whole return as the errors map would be a bug: the wrapper array is
     | always non-empty, so `!empty($result)` would reject EVERY username.
     */
    $validation = student_validator_username( $username );

    if ( empty( $validation['valid'] ) ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'Username is not valid.',
            'errors'  => $validation['errors'] ?? [ 'username' => 'Username is not valid.' ],
        ];
    }

    $current = user_repository_find_username( $user_id );

    if ( $current !== null && strcasecmp( $current, $username ) === 0 ) {
        return [
            'data'    => [ 'user_id' => $user_id, 'username' => $current ],
            'message' => 'Username is unchanged.',
        ];
    }

    if ( user_repository_username_exists( $username, $user_id ) ) {
        return [
            'error'   => true,
            'status'  => 409,
            'message' => 'That username is already taken.',
        ];
    }

    if ( !user_repository_update_username( $user_id, $username ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the username.' ];
    }

    return [
        'data'    => [ 'user_id' => $user_id, 'username' => $username ],
        'message' => 'Username updated successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
|
| The Account field is the student's real name (students.full_name), not the
| public handle. The student is resolved from the auth context only; no
| student_id or user_id from the request is ever read, so this cannot be
| aimed at another account.
|
| The public username endpoint remains in place for the features that show a
| handle next to the name, and users.username is untouched here.
|
*/

function student_account_full_name( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $full_name = trim( (string) ( $student['full_name'] ?? '' ) );

    return [
        'data' => [
            'full_name' => $full_name === '' ? null : $full_name,
        ],
    ];
}

function student_account_update_full_name( array $auth_user, array $data ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $full_name = $data['full_name'] ?? null;
    $validation = student_validator_full_name( $full_name );

    if ( empty( $validation['valid'] ) ) {
        /*
         * Return the errors map WITHOUT the `error` flag: student_controller_respond()
         * checks `error` first, so setting it would route this through
         * response_error() and drop the field-level errors. The errors-only shape
         * reaches response_validation_error(), which is the standard 422 envelope.
         */
        return [
            'errors' => $validation['errors'] ?? [ 'full_name' => 'Full name is not valid.' ],
        ];
    }

    $full_name = trim( (string) $full_name );

    if ( !student_repository_update( (int) $student['id'], [ 'full_name' => $full_name ] ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the full name.' ];
    }

    return [
        'data'    => [ 'full_name' => $full_name ],
        'message' => 'Full name updated successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Contact Info
|--------------------------------------------------------------------------
|
| email lives on the account (users.email); phone, website, external_profile and
| address live on the student profile (students.*). These are the existing
| storage locations for the five-field contact contract: no duplicate contact
| columns are introduced.
|
| Changing the email uses the existing email format and uniqueness checks.
| This endpoint does not accept or verify a password; password changes remain
| the separate authentication flow. It also does NOT change how the account
| authenticates: the identity model is email + password, and that is left
| exactly as it is.
|
| Contact details are only ever returned to the authenticated student here.
| The company-facing exposure rules live in the Applications and Certificates
| flows and are deliberately not widened by this endpoint.
|
*/
function student_account_contact( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $user_id = (int) $auth_user['id'];
    $user    = user_repository_find_email( $user_id );

    return [
        'data' => [
            'email'            => is_array( $user ) ? ( $user['email'] ?? null ) : null,
            'phone'            => student_account_normalize_phone( $student['phone'] ?? null ),
            'website'          => student_account_normalize_optional_text( $student['website'] ?? null ),
            'external_profile' => student_account_normalize_optional_text( $student['external_profile'] ?? null ),
            'address'          => student_account_normalize_optional_text( $student['address'] ?? null ),
        ],
    ];
}

function student_account_update_contact( array $auth_user, array $data ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $user_id = (int) $auth_user['id'];
    $errors  = [];

    $updates = [];

    if ( array_key_exists( 'phone', $data ) ) {
        $phone = student_account_normalize_phone( $data['phone'] );

        /* Same ['valid','errors'] shape as the username validator above. */
        $phone_validation = student_validator_phone( $phone );

        if ( empty( $phone_validation['valid'] ) ) {
            $errors['phone'] = $phone_validation['errors']['phone'] ?? 'Phone is not valid.';
        } else {
            $updates['phone'] = $phone;
        }
    }

    if ( array_key_exists( 'website', $data ) ) {
        $website_validation = student_validator_website( $data['website'] );

        if ( empty( $website_validation['valid'] ) ) {
            $errors['website'] = $website_validation['errors']['website'] ?? 'Website is not valid.';
        } else {
            $updates['website'] = student_account_normalize_optional_text( $data['website'] );
        }
    }

    if ( array_key_exists( 'external_profile', $data ) ) {
        $external_profile_validation = student_validator_external_profile( $data['external_profile'] );

        if ( empty( $external_profile_validation['valid'] ) ) {
            $errors['external_profile'] = $external_profile_validation['errors']['external_profile'] ?? 'External profile is not valid.';
        } else {
            $updates['external_profile'] = student_account_normalize_optional_text( $data['external_profile'] );
        }
    }

    if ( array_key_exists( 'address', $data ) ) {
        $address_validation = student_validator_address( $data['address'] );

        if ( empty( $address_validation['valid'] ) ) {
            $errors['address'] = $address_validation['errors']['address'] ?? 'Address is not valid.';
        } else {
            $updates['address'] = student_account_normalize_optional_text( $data['address'] );
        }
    }

    /*
     | The email is the account identity (users.email), so it is only read from
     | the request when the client actually sends the field - an omitted email
     | is never validated and never written. An address identical to the one
     | already stored is a NO-OP, not an error: clients resubmit the whole
     | contact form, and an unchanged email must not stop the other fields from
     | being saved. Uniqueness excludes this account's own user id, so only an
     | address owned by a DIFFERENT user is refused.
     |
     | $email stays null in both the omitted and the unchanged case, which is
     | what makes the "nothing to write, nothing to re-verify" branch below
     | skip user_repository_update_email() and
     | user_repository_clear_email_verified_at().
     */
    $email          = null;
    $email_provided = array_key_exists( 'email', $data );

    if ( $email_provided ) {
        $email = normalize_email( $data['email'] );

        if ( $email === null ) {
            $errors['email'] = 'Enter a valid email address.';
        } else {
            $user = user_repository_find_email( $user_id );

            if ( !is_array( $user ) ) {
                return [ 'error' => true, 'status' => 500, 'message' => 'Unable to load the account.' ];
            }

            if ( strcasecmp( (string) ( $user['email'] ?? '' ), $email ) === 0 ) {
                // Already this account's address: allow the request, write nothing.
                $email = null;
            } elseif ( user_repository_email_exists_for_other_user( $email, $user_id ) ) {
                $errors['email'] = 'That email address is already in use.';
            }
        }
    }

    /*
     * Return the errors map WITHOUT the `error` flag: student_controller_respond()
     * checks `error` first, so setting it would route this through response_error()
     * and drop the field-level errors. The errors-only shape reaches
     * response_validation_error(), which is the standard 422 envelope.
     */
    if ( !empty( $errors ) ) {
        return [ 'errors' => $errors ];
    }

    if ( empty( $updates ) && $email === null && !$email_provided ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'No contact information was provided.' ];
    }

    if ( !empty( $updates ) ) {
        if ( !student_repository_update( (int) $student['id'], $updates ) ) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the contact information.' ];
        }
    }

    if ( $email !== null ) {
        if ( !user_repository_update_email( $user_id, $email ) ) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the email address.' ];
        }

        /*
         | A new address has not been verified, so email_verified_at is cleared.
         | That is the existing verification model being used as intended rather
         | than bypassed: the account keeps working on its password, but the
         | address is no longer treated as proven.
         */
        user_repository_clear_email_verified_at( $user_id );
    }

    $result = student_account_contact( $auth_user );

    return [
        'data'    => $result['data'] ?? [],
        'message' => 'Contact information updated successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Academic Status
|--------------------------------------------------------------------------
|
| The profile-level Student / Graduate state, plus the month the degree or
| certificate was awarded (graduation_month, pairing with the pre-existing
| graduation_year).
|
| This writes ONLY students.academic_status / students.graduation_month /
| students.graduation_year. Training eligibility never reads any students
| column, so this cannot allow or disallow a training application, and no
| application, training, session or certificate row is touched.
|
*/
function student_account_academic_status( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $status = student_repository_find_academic_status( (int) $student['id'] );

    if ( !is_array( $status ) ) {
        $status = [ 'academic_status' => ACADEMIC_STATUS_STUDENT ];
    }

    $academic_status = (string) ( $status['academic_status'] ?? ACADEMIC_STATUS_STUDENT );

    $data = [ 'academic_status' => $academic_status ];

    if ( academic_status_is_graduate( $academic_status ) ) {
        $graduation_date = $status['graduation_date'] ?? null;
        if ( $graduation_date !== null && $graduation_date !== '' ) {
            $data['graduation_date'] = (string) $graduation_date;
        }

        // Resolve the graduation certificate file info (existing file response shape).
        $file_id = (int) ( $status['academic_certificate_file_id'] ?? 0 );
        if ( $file_id > 0 ) {
            $file = file_repository_find_for_user( $file_id, (int) ( $student['user_id'] ?? 0 ) );
            if ( is_array( $file ) && ( $file['type'] ?? '' ) === FILE_TYPE_ACADEMIC_CERTIFICATE ) {
                $data['graduation_certificate'] = [
                    'id'        => $file_id,
                    'file_name' => $file['original_name'] ?? null,
                    'file_url'  => file_public_url( $file_id ),
                ];
            }
        }
    }

    return [ 'data' => $data ];
}

function student_account_update_academic_status( array $auth_user, array $data, ?array $file = null ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $status = isset( $data['academic_status'] ) ? trim( (string) $data['academic_status'] ) : '';

    if ( $status === '' ) {
        $status = (string) ( $data['status'] ?? '' );
        $status = trim( $status );
    }

    $errors = student_validator_academic_status( $status, $data );

    if ( !empty( $errors ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Academic status is not valid.', 'errors' => $errors ];
    }

    $status = strtolower( $status );

    $stored_month = ( $student['graduation_month'] ?? null ) === null ? null : (int) $student['graduation_month'];
    $stored_year  = ( $student['graduation_year'] ?? null ) === null ? null : (int) $student['graduation_year'];

    /*
     | Resolve the graduation date. The new contract sends `graduation_date`
     | (YYYY-MM-DD); the legacy month/year fields are still honoured as a
     | fallback so a caller that already recorded them keeps working.
     */
    $graduation_date = isset( $data['graduation_date'] ) ? trim( (string) $data['graduation_date'] ) : '';

    $month = null;
    $year  = null;

    if ( $graduation_date !== '' ) {
        $dt = new DateTime( $graduation_date );
        $year  = (int) $dt->format( 'Y' );
        $month = (int) $dt->format( 'n' );
    } else {
        $month = student_account_normalize_month( $data['graduation_month'] ?? null );
        $year  = student_account_normalize_year( $data['graduation_year'] ?? null );

        if ( !array_key_exists( 'graduation_month', $data ) ) {
            $month = $stored_month;
        }

        if ( $year === null ) {
            $year = $stored_year;
        }
    }

    /*
     | New contract: a GRADUATE must supply a graduation date AND a graduation
     | certificate file. The certificate is uploaded through the existing
     | academic-certificate flow, so validation, randomized storage names and
     | the files table are all reused (no duplicate upload logic).
     |
     | A status-only graduate request (no date, no file) keeps the previously
     | stored month/year and does not touch the certificate, preserving the
     | legacy behaviour for callers that already recorded everything.
     */
    if ( academic_status_is_graduate( $status ) ) {
        if ( $graduation_date === '' ) {
            return [
                'error'   => true,
                'status'  => 422,
                'message' => 'Academic status is not valid.',
                'errors'  => [ 'graduation_date' => 'Graduation date is required when the academic status is graduate.' ],
            ];
        }

        if ( empty( $file ) ) {
            return [
                'error'   => true,
                'status'  => 422,
                'message' => 'Academic status is not valid.',
                'errors'  => [ 'graduation_certificate' => 'A graduation certificate is required when the academic status is graduate.' ],
            ];
        }

        $upload = student_account_upload_graduation_certificate( $auth_user, $file );

        if ( isset( $upload['error'] ) && $upload['error'] === true ) {
            return $upload;
        }
    }

    // Keep a partial update that omits the year from erasing the stored one.
    if ( $year === null ) {
        $year = $stored_year;
    }

    if ( !student_repository_update_academic_status( (int) $student['id'], $status, $month ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update the academic status.' ];
    }

    if ( $year !== null && (int) ( $student['graduation_year'] ?? 0 ) !== $year ) {
        student_repository_update( (int) $student['id'], [ 'graduation_year' => $year ] );
    }

    // Persist the graduation date (new contract field) through the allowlisted updater.
    if ( academic_status_is_graduate( $status ) && $graduation_date !== '' ) {
        student_repository_update( (int) $student['id'], [ 'graduation_date' => $graduation_date ] );
    }

    $result = student_account_academic_status( $auth_user );

    return [
        'data'    => $result['data'] ?? [],
        'message' => 'Academic status updated successfully.',
    ];
}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
|
 | Writes students.profile_image_file_id, the field the profile header already
 | reads. The file itself goes through the EXISTING upload service, so
 | validation, randomized storage names and the files table are all reused.
|
| The stored reference is only ever a file this student owns and that is typed
| profile_image. That is proven twice: here before the write, and again in the
| profile-header read join, so a corrupted reference still cannot be served.
|
*/
function student_account_upload_profile_image( array $auth_user, array $file ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $check = student_account_validate_upload( $file, PROFILE_IMAGE_EXTENSIONS, PROFILE_IMAGE_MIMES, PROFILE_IMAGE_MAX_SIZE, 'image' );

    if ( !empty( $check['errors'] ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => $check['message'] ] + $check;
    }

    $user_id = (int) $auth_user['id'];

    $stored = file_upload_service_upload( $file, [
        'user_id' => $user_id,
        'folder'  => 'profile',
        'type'    => FILE_TYPE_PROFILE_IMAGE,
    ] );

    if ( !is_array( $stored ) || empty( $stored['id'] ) ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'The profile image could not be stored.',
            'errors'  => [ 'image' => 'The profile image could not be stored.' ],
        ];
    }

    $file_id = (int) $stored['id'];

    /*
     | Belt and braces: the upload service was told to type this file
     | profile_image, but the reference is re-resolved through the ownership
     | -scoped read before it is written, so the students row can only ever
     | point at a file this account owns and is allowed to render.
     */
    $owned = file_repository_find_for_user( $file_id, $user_id );

    if ( !is_array( $owned ) || ( $owned['type'] ?? '' ) !== FILE_TYPE_PROFILE_IMAGE ) {
        student_account_discard_profile_image( $file_id, $user_id );

        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'The profile image could not be stored.',
            'errors'  => [ 'image' => 'The profile image could not be stored.' ],
        ];
    }

    if ( !student_repository_set_profile_image_file_id( (int) $student['id'], $file_id ) ) {
        student_account_discard_profile_image( $file_id, $user_id );

        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to attach the profile image.' ];
    }

    return [
        'data'    => [
            'profile_image_url'     => file_signed_url( $file_id ),
            'profile_image_file_id' => $file_id,
        ],
        'message' => 'Profile image updated successfully.',
    ];
}

/*
|--------------------------------------------------------------------------
| Discard An Unattached Profile Image
|--------------------------------------------------------------------------
|
| Removes a file that was stored but never attached to the student.
|
| The students row is written last, so the window between "the bytes are on
| disk and the files row exists" and "the students row points at it" can fail.
| Without this the upload would leave an unreferenced row and an orphaned file
| that no profile_image_file_id would ever reach.
|
| Scoped to $user_id so a failed upload can only ever discard this account's
| own file. The PREVIOUS image is deliberately left alone: the replacement is
| only published once its own write has succeeded, so a failed replacement
| leaves the student with the image they already had.
|
*/
function student_account_discard_profile_image( int $file_id, int $user_id ): void {

    if ( $file_id <= 0 ) {
        return;
    }

    file_upload_service_delete( $file_id, $user_id );
}

function student_account_remove_profile_image( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    if ( !student_repository_set_profile_image_file_id( (int) $student['id'], null ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove the profile image.' ];
    }

    return [
        'data'    => [ 'profile_image_url' => null, 'profile_image_file_id' => null ],
        'message' => 'Profile image removed successfully.',
    ];
}


function student_account_upload_graduation_certificate( array $auth_user, array $file ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $check = student_account_validate_upload(
        $file,
        ACADEMIC_CERTIFICATE_EXTENSIONS,
        ACADEMIC_CERTIFICATE_MIMES,
        ACADEMIC_CERTIFICATE_MAX_SIZE,
        'certificate'
    );

    if ( !empty( $check['errors'] ) ) {
        return [ 'error' => true, 'status' => 422, 'message' => $check['message'] ] + $check;
    }

    $user_id = (int) $auth_user['id'];

    $stored = file_upload_service_upload( $file, [
        'user_id' => $user_id,
        'folder'  => 'certificates',
        'type'    => FILE_TYPE_ACADEMIC_CERTIFICATE,
    ] );

    if ( !is_array( $stored ) || empty( $stored['id'] ) ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'The academic certificate could not be stored.',
            'errors'  => [ 'certificate' => 'The academic certificate could not be stored.' ],
        ];
    }

    $file_id = (int) $stored['id'];

    $owned = file_repository_find_for_user( $file_id, $user_id );

    if ( !is_array( $owned ) || ( $owned['type'] ?? '' ) !== FILE_TYPE_ACADEMIC_CERTIFICATE ) {
        return [
            'error'   => true,
            'status'  => 422,
            'message' => 'The academic certificate could not be stored.',
            'errors'  => [ 'certificate' => 'The academic certificate could not be stored.' ],
        ];
    }

    if ( !student_repository_set_academic_certificate_file_id( (int) $student['id'], $file_id ) ) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to attach the academic certificate.' ];
    }

    $resolved = student_account_resolve_graduation_certificate(
        student_repository_find_by_id( (int) $student['id'] ) ?? []
    );

    return [
        'data'    => $resolved,
        'message' => 'Academic certificate uploaded successfully.',
    ];
}

function student_account_remove_graduation_certificate( array $auth_user ): array {

    $student = student_account_require_student( $auth_user );

    if ( !is_array( $student ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $file_id = (int) ( $student['academic_certificate_file_id'] ?? 0 );

    if ( $file_id <= 0 ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'No graduation certificate found.' ];
    }

    $user_id = (int) ( $auth_user['id'] ?? 0 );

    /*
     | Verify ownership before touching anything: the file must belong to this
     | student and be typed as an academic certificate.
     */
    $file = file_repository_find_for_user( $file_id, $user_id );

    if ( !is_array( $file ) || ( $file['type'] ?? '' ) !== FILE_TYPE_ACADEMIC_CERTIFICATE ) {
        // Stale reference: clear it without deleting (cannot attribute the file).
        student_repository_set_academic_certificate_file_id( (int) $student['id'], null );
        return [ 'error' => true, 'status' => 404, 'message' => 'No graduation certificate found.' ];
    }

    $path = (string) ( $file['path'] ?? '' );
    $safe = file_upload_service_is_safe_storage_path( $path );

    if ( !$safe ) {
        error_log( 'MASAR graduation certificate removal: refusing to delete unsafe stored path: ' . $path );
    } elseif ( is_file( $path ) ) {
        if ( !@unlink( $path ) ) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to delete the graduation certificate file.' ];
        }
    }

    /*
     | Clear the reference and delete the file record in one transaction after
     | the physical file is removed.
     */
    db_begin_transaction();

    try {
        $cleared = student_repository_set_academic_certificate_file_id( (int) $student['id'], null );
        $deleted = file_repository_delete( $file_id, $user_id );

        if ( !$cleared || !$deleted ) {
            db_rollback();
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove the graduation certificate.' ];
        }

        db_commit();
    } catch ( Throwable $e ) {
        db_rollback();
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to remove the graduation certificate.' ];
    }

    return [
        'data'    => [ 'message' => 'Graduation certificate removed successfully.' ],
        'message' => 'Graduation certificate removed successfully.',
    ];
}

/*
 | Resolves the stored academic-certificate reference into a client-safe DTO.
 | The file is only exposed when the reference still resolves to a file this
 | student owns AND is typed as an academic certificate, so a dangling or
 | foreign id degrades to null instead of producing a URL that would 403.
 */
function student_account_resolve_graduation_certificate( array $student ): array {

    $file_id = (int) ( $student['academic_certificate_file_id'] ?? 0 );

    if ( $file_id <= 0 ) {
        return [
            'has_certificate'      => false,
            'file_id'              => null,
            'url'                  => null,
            'original_name'        => null,
            'mime_type'            => null,
            'size_bytes'           => null,
            'uploaded_at'          => null,
        ];
    }

    $file = file_repository_find_for_user( $file_id, (int) ( $student['user_id'] ?? 0 ) );

    if ( !is_array( $file ) || ( $file['type'] ?? '' ) !== FILE_TYPE_ACADEMIC_CERTIFICATE ) {
        return [
            'has_certificate'      => false,
            'file_id'              => null,
            'url'                  => null,
            'original_name'        => null,
            'mime_type'            => null,
            'size_bytes'           => null,
            'uploaded_at'          => null,
        ];
    }

    return [
        'has_certificate' => true,
        'file_id'         => $file_id,
        'url'             => file_public_url( $file_id ),
        'original_name'   => $file['original_name'] ?? null,
        'mime_type'       => $file['mime_type'] ?? null,
        'size_bytes'      => isset( $file['size_bytes'] ) ? (int) $file['size_bytes'] : null,
        'uploaded_at'     => application_iso8601( $file['created_at'] ?? null ),
    ];
}


/*
|--------------------------------------------------------------------------
| Upload Validation
|--------------------------------------------------------------------------
|
| A feature-specific policy applied BEFORE the shared upload service, so a
| rejection is reported as a real 422 with a specific reason instead of the
| shared service's generic "could not be stored".
|
| The MIME type is read from the file's own bytes with finfo. The
| client-supplied $_FILES['type'] is never used as the deciding value; at
| most it is a fallback, and a mismatch between the declared extension and the
| sniffed content is a rejection. That is what stops a PHP script or an HTML
| page renamed to certificate.pdf from passing.
|
| The extension and the sniffed content must agree, so neither one alone is
| enough to pass.
|
*/
function student_account_validate_upload(
    array $file,
    array $allowed_extensions,
    array $allowed_mimes,
    int $max_size,
    string $field
): array {

    if ( empty( $file ) || !isset( $file['error'] ) ) {
        return [
            'message' => 'No file was received.',
            'errors'  => [ $field => 'No file was received.' ],
        ];
    }

    if ( (int) $file['error'] !== UPLOAD_ERR_OK ) {
        return [
            'message' => student_account_upload_error_message( (int) $file['error'] ),
            'errors'  => [ $field => student_account_upload_error_message( (int) $file['error'] ) ],
        ];
    }

    $tmp_name = (string) ( $file['tmp_name'] ?? '' );

    if ( $tmp_name === '' || !is_file( $tmp_name ) ) {
        return [
            'message' => 'The uploaded file is missing.',
            'errors'  => [ $field => 'The uploaded file is missing.' ],
        ];
    }

    $size = (int) ( $file['size'] ?? filesize( $tmp_name ) );

    if ( $size <= 0 ) {
        return [
            'message' => 'The uploaded file is empty.',
            'errors'  => [ $field => 'The uploaded file is empty.' ],
        ];
    }

    if ( $max_size > 0 && $size > $max_size ) {
        $limit = round( $max_size / ( 1024 * 1024 ), 1 );

        return [
            'message' => 'The file exceeds the maximum allowed size of ' . $limit . 'MB.',
            'errors'  => [ $field => 'The file must not exceed ' . $limit . 'MB.' ],
        ];
    }

    $original_name = (string) ( $file['name'] ?? '' );
    $extension     = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );

    if ( !in_array( $extension, $allowed_extensions, true ) ) {
        return [
            'message' => 'Unsupported file type. Allowed: ' . implode( ', ', $allowed_extensions ) . '.',
            'errors'  => [ $field => 'Unsupported file type. Allowed: ' . implode( ', ', $allowed_extensions ) . '.' ],
        ];
    }

    if ( str_contains( $original_name, '..' ) || str_contains( $original_name, '/' ) || str_contains( $original_name, '\\' ) ) {
        return [
            'message' => 'Invalid file name.',
            'errors'  => [ $field => 'Invalid file name.' ],
        ];
    }

    if ( student_account_has_executable_extension( $original_name ) ) {
        return [
            'message' => 'Executable file types are not allowed.',
            'errors'  => [ $field => 'Executable file types are not allowed.' ],
        ];
    }

    $detected = student_account_detect_mime_type( $tmp_name );

    if ( !in_array( $detected, $allowed_mimes, true ) ) {
        return [
            'message' => 'The file content does not match the allowed types.',
            'errors'  => [ $field => 'The file content is not a valid ' . implode( '/', $allowed_extensions ) . ' file.' ],
        ];
    }

    /*
     | The sniffed content must also agree with the extension. Without this a
     | real PNG (a sniffed image/png) could be uploaded as .pdf, or a real PDF
     | as .png, because both are individually allowed.
     */
    if ( !student_account_extension_matches_mime( $extension, $detected ) ) {
        return [
            'message' => 'The file content does not match its extension.',
            'errors'  => [ $field => 'The file content does not match the ' . $extension . ' extension.' ],
        ];
    }

    if ( !student_account_magic_bytes_match( $tmp_name, $detected ) ) {
        return [
            'message' => 'The file content does not match its declared type.',
            'errors'  => [ $field => 'The file content does not match its declared type.' ],
        ];
    }

    return [ 'message' => null, 'errors' => [] ];
}

/*
 | Detects the MIME type from the file's own bytes. The client-declared type
 | is never the deciding value: finfo reads the content. If finfo is somehow
 | unavailable the client's type is still NOT trusted, because every supported
 | format has a magic-byte check below that would then fail.
 */
function student_account_detect_mime_type( string $path ): string {

    if ( function_exists( 'finfo_open' ) ) {
        $finfo = finfo_open( FILEINFO_MIME_TYPE );

        if ( $finfo ) {
            $mime = finfo_file( $finfo, $path );
            finfo_close( $finfo );

            if ( is_string( $mime ) && $mime !== '' ) {
                return $mime;
            }
        }
    }

    return 'application/octet-stream';
}

/*
 | Confirms the sniffed content is really the format it claims, by reading the
 | file's leading bytes. A polyglot file that finfo classifies as an allowed
 | type but whose bytes do not match is refused here.
 */
function student_account_magic_bytes_match( string $path, string $mime ): bool {

    $head = @file_get_contents( $path, false, null, 0, 512 );

    if ( $head === false || $head === '' ) {
        return false;
    }

    if ( $mime === 'application/pdf' ) {
        return str_starts_with( $head, '%PDF-' );
    }

    if ( $mime === 'image/png' ) {
        return str_starts_with( $head, "\x89PNG\r\n\x1a\n" );
    }

    if ( $mime === 'image/jpeg' ) {
        return str_starts_with( $head, "\xFF\xD8\xFF" );
    }

    if ( $mime === 'image/webp' ) {
        return str_starts_with( $head, 'RIFF' ) && substr( $head, 8, 4 ) === 'WEBP';
    }

    return false;
}

/*
 | The extension and the sniffed MIME must describe the same format. .jpg and
 | .jpeg are the same format and are treated as equivalent.
 */
function student_account_extension_matches_mime( string $extension, string $mime ): bool {

    $map = [
        'pdf'  => [ 'application/pdf' ],
        'png'  => [ 'image/png' ],
        'jpg'  => [ 'image/jpeg' ],
        'jpeg' => [ 'image/jpeg' ],
        'webp' => [ 'image/webp' ],
    ];

    if ( !isset( $map[ $extension ] ) ) {
        return false;
    }

    return in_array( $mime, $map[ $extension ], true );
}

function student_account_has_executable_extension( string $filename ): bool {

    $dangerous = [ 'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'scr', 'bat', 'cmd', 'com', 'js', 'vbs', 'jar' ];

    $extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

    return in_array( $extension, $dangerous, true );
}

function student_account_upload_error_message( int $error ): string {

    switch ( $error ) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file is too large.';
        case UPLOAD_ERR_PARTIAL:
            return 'The file was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was received.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'The server has no temporary folder for uploads.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'The server could not write the uploaded file.';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload.';
        default:
            return 'The file could not be uploaded.';
    }
}


/*
|--------------------------------------------------------------------------
| Small Normalizers
|--------------------------------------------------------------------------
*/

function student_account_normalize_phone( mixed $value ): ?string {

    if ( $value === null ) {
        return null;
    }

    $phone = trim( (string) $value );

    return $phone === '' ? null : $phone;
}

function student_account_normalize_optional_text( mixed $value ): ?string {

    if ( $value === null ) {
        return null;
    }

    $text = trim( (string) $value );

    return $text === '' ? null : $text;
}

function student_account_normalize_month( mixed $value ): ?int {

    if ( $value === null || ( is_string( $value ) && trim( $value ) === '' ) ) {
        return null;
    }

    return academic_month_number( $value );
}

function student_account_normalize_year( mixed $value ): ?int {

    if ( $value === null || ( is_string( $value ) && trim( $value ) === '' ) ) {
        return null;
    }

    $year = filter_var( $value, FILTER_VALIDATE_INT );

    return ( $year === false ) ? null : (int) $year;
}
