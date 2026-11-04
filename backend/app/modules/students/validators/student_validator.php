<?php

/**
 * MASAR - Student Validator
 *
 * Responsible for validating student data before it reaches
 * the service/repository layer.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No database queries.
 * - No HTTP handling.
 * - No business operations.
 */

require_once __DIR__ . '/../../../core/validation/rules.php';

function student_validator_create(array $data): array
{
    $errors = [];

    $user_field_key = array_key_exists( 'field', $data ) ? 'field' : 'faculty';
    $user_field = trim( (string) ( $data[$user_field_key] ?? '' ) );

    if ($user_field === '') {
        $errors[$user_field_key] = 'User field is required.';
    } elseif (strlen($user_field) > 255) {
        $errors[$user_field_key] = 'User field must not exceed 255 characters.';
    }

    if (array_key_exists('degree', $data) && trim((string) $data['degree']) !== '') {
        if (strlen(trim((string) $data['degree'])) > 255) {
            $errors['degree'] = 'Degree must not exceed 255 characters.';
        }
    }

    $specialization = trim( (string) ( $data['specialization'] ?? '' ) );

    if ($specialization === '') {
        $errors['specialization'] = 'Specialization is required.';
    } elseif (strlen($specialization) > 255) {
        $errors['specialization'] = 'Specialization must not exceed 255 characters.';
    }

    if (isset($data['bio']) && strlen(trim((string) $data['bio'])) > 200) {
        $errors['bio'] = 'Bio must not exceed 2000 characters.';
    }

    return ['valid' => empty($errors), 'errors' => $errors];
}

function student_validator_update(array $data): array
{
    $errors = [];

    if (empty($data)) {
        return ['valid' => false, 'errors' => ['general' => 'No data was provided for update.']];
    }

    $user_field_key = array_key_exists( 'field', $data ) ? 'field' : 'faculty';

    if (array_key_exists($user_field_key, $data)) {
        $user_field = trim((string) $data[$user_field_key]);
        if ($user_field === '') {
            $errors[$user_field_key] = 'User field cannot be empty.';
        } elseif (strlen($user_field) > 255) {
            $errors[$user_field_key] = 'User field must not exceed 255 characters.';
        }
    }

    if (array_key_exists('specialization', $data)) {
        $specialization = trim((string) $data['specialization']);
        if ($specialization === '') {
            $errors['specialization'] = 'Specialization cannot be empty.';
        } elseif (strlen($specialization) > 255) {
            $errors['specialization'] = 'Specialization must not exceed 255 characters.';
        }
    }

    if (array_key_exists('bio', $data)) {
        if (strlen(trim((string) $data['bio'])) > 2000) {
            $errors['bio'] = 'Bio must not exceed 2000 characters.';
        }
    }

    return ['valid' => empty($errors), 'errors' => $errors];
}

function student_validator_skills(mixed $skills): array
{

    $errors = [];
    if ( ! is_array($skills) ) {
        return [ 'valid'  => false, 'errors' => [ 'skills' => 'Skills must be an array.', ], ];
    }

    if ( empty($skills) ) {
        $errors['skills'] = 'At least one skill is required.';
        return [ 'valid'  => false, 'errors' => $errors, ];
    }

    if ( count($skills) > STUDENT_MAX_SKILLS ) {
        $errors['skills'] = 'You cannot add more than ' . STUDENT_MAX_SKILLS . ' skills.';
    }

    foreach ( $skills as $index => $skill ) {
        if ( ! is_string($skill) && ! is_numeric($skill) ) {
            $errors[ 'skills.' . $index ] = 'Skill must be a valid string.';
            continue;
        }
        $skill = trim( (string) $skill );
        if ($skill === '') {
            $errors[ 'skills.' . $index ] = 'Skill cannot be empty.';
            continue;
        }
        if ( strlen($skill) > 100 ) {
            $errors[ 'skills.' . $index ] = 'Skill must not exceed 100 characters.';
        }
    }

    $normalized = [];

    foreach ($skills as $skill) {
        if ( ! is_string($skill) && ! is_numeric($skill) ) {
            continue;
        }
        $normalized[] = strtolower( trim( (string) $skill ) );
    }

    if ( count($normalized) !== count( array_unique( $normalized ) ) ) {
        $errors['skills'] = 'Duplicate skills are not allowed.';
    }

    return [ 'valid'  => empty($errors), 'errors' => $errors, ];
}

function student_validator_add_skill( mixed $skill ): array {
    $errors = [];

    /*
     | The skill is sent as its taxonomy lookup id, exactly like the education
     | degree lookup id. A free-text name is therefore refused: the shared
     | `skills` taxonomy is the source of truth and ids resolve through it.
     */
    if ( ! is_int($skill) || (int) $skill <= 0 ) {
        $errors['skill'] = 'Skill must be a valid skill ID.';
        return [ 'valid'  => false, 'errors' => $errors, ];
    }

    return [ 'valid'  => true, 'errors' => $errors, ];
}

/*
|--------------------------------------------------------------------------
| Validate Skill Ids (Replace All)
|--------------------------------------------------------------------------

| The bulk PUT /students/me/skills payload carries the whole skill set as
| taxonomy lookup ids. Numeric ids only - a free-text name is refused here.
| (The profile create/update validators keep accepting names: that flow still
| stores the academic profile fields, not the /me/skills replace contract.)
*/

function student_validator_skill_ids( mixed $skills ): array {
    $errors = [];

    if ( ! is_array($skills) ) {
        return [ 'valid'  => false, 'errors' => [ 'skills' => 'Skills must be an array.' ], ];
    }

    if ( empty($skills) ) {
        $errors['skills'] = 'At least one skill is required.';
        return [ 'valid'  => false, 'errors' => $errors, ];
    }

    if ( count($skills) > STUDENT_MAX_SKILLS ) {
        $errors['skills'] = 'You cannot add more than ' . STUDENT_MAX_SKILLS . ' skills.';
    }

    $normalized = [];

    foreach ( $skills as $index => $skill ) {
        if ( ! is_int($skill) || (int) $skill <= 0 ) {
            $errors[ 'skills.' . $index ] = 'Skill must be a valid skill ID.';
            continue;
        }

        $normalized[] = (int) $skill;
    }

    if ( count($normalized) !== count( array_unique( $normalized ) ) ) {
        $errors['skills'] = 'Duplicate skills are not allowed.';
    }

    return [ 'valid'  => empty($errors), 'errors' => $errors, ];
}

function student_validator_student_id( mixed $student_id ): array {

    if ( filter_var( $student_id, FILTER_VALIDATE_INT ) === false || (int) $student_id <= 0 ) {
        return [ 'valid'  => false, 'errors' => [ 'student_id' => 'Invalid student ID.', ], ];
    }

    return [ 'valid'  => true, 'errors' => [], ];
}

function student_validator_cv_file_id( mixed $file_id ): array {
    if ( filter_var( $file_id, FILTER_VALIDATE_INT ) === false || (int) $file_id <= 0 ) {
        return [ 'valid'  => false, 'errors' => [ 'file_id' => 'Invalid CV file ID.', ], ];
    }

    return [ 'valid'  => true, 'errors' => [], ];
}

/*
|--------------------------------------------------------------------------
| Public Username
|--------------------------------------------------------------------------
|
| Structure only: no database query and no uniqueness check, so the service
| stays responsible for the conflict. The rules are the policy agreed for this
| field, which did not previously exist in the schema or the validators:
|
|   3-30 characters, must begin with a letter, then letters / digits / _ / .
|
| The pattern is the single source of truth; the length messages are derived
| from USERNAME_MIN_LENGTH / USERNAME_MAX_LENGTH rather than being written out
| again, so the two can never disagree.
|
*/

function student_validator_username( mixed $username ): array {

    if ( !is_string( $username ) && !is_numeric( $username ) ) {
        return [ 'valid' => false, 'errors' => [ 'username' => 'Username must be a valid string.' ] ];
    }

    $username = trim( (string) $username );

    if ( $username === '' ) {
        return [ 'valid' => false, 'errors' => [ 'username' => 'Username is required.' ] ];
    }

    $length = mb_strlen( $username );

    if ( $length < USERNAME_MIN_LENGTH || $length > USERNAME_MAX_LENGTH ) {
        return [
            'valid'  => false,
            'errors' => [
                'username' => 'Username must be between ' . USERNAME_MIN_LENGTH . ' and ' . USERNAME_MAX_LENGTH . ' characters.',
            ],
        ];
    }

    if ( !preg_match( USERNAME_PATTERN, $username ) ) {
        return [
            'valid'  => false,
            'errors' => [
                'username' => 'Username must start with a letter and may only contain letters, numbers, underscores and dots.',
            ],
        ];
    }

    return [ 'valid' => true, 'errors' => [] ];
}

/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
|
| students.full_name is varchar(255), so the cap is 255 CHARACTERS (mb_strlen),
| not bytes: a 200-character Arabic name must not be rejected for being 400
| bytes. This mirrors the existing full-name rule used by the profile update
| endpoint, but is exposed as a standalone validator for the Account endpoint.
|
*/

function student_validator_full_name( mixed $full_name ): array {

    /*
     * The contract is a JSON string. Numeric values are deliberately refused
     * here rather than cast: the Account field is a name, and accepting 123
     * would make the endpoint disagree with the documented `type: string`.
     */
    if ( !is_string( $full_name ) ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name must be a valid string.' ] ];
    }

    $full_name = trim( $full_name );

    if ( $full_name === '' ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name is required.' ] ];
    }

    if ( !mb_check_encoding( $full_name, 'UTF-8' ) ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name must be valid UTF-8 text.' ] ];
    }

    /*
     * trim() only removes ASCII whitespace. Reject a value that consists only
     * of Unicode separators (for example a non-breaking-space-only name).
     */
    if ( preg_match( '/[^\s\p{Z}]/u', $full_name ) !== 1 ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name is required.' ] ];
    }

    if ( mb_strlen( $full_name ) > 255 ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name must not exceed 255 characters.' ] ];
    }

    /*
     * \p{Cc} covers C0 controls, DEL and the C1 control block, not just the
     * ASCII range the old profile rule happened to list.
     */
    if ( preg_match( '/\p{Cc}/u', $full_name ) === 1 ) {
        return [ 'valid' => false, 'errors' => [ 'full_name' => 'Full name contains invalid characters.' ] ];
    }

    return [ 'valid' => true, 'errors' => [] ];
}

/*
|--------------------------------------------------------------------------
| Phone
|--------------------------------------------------------------------------
|
| The codebase has no phone format rule anywhere - only two inconsistent
| length caps (50 on the profile, 20 on an application). The looser existing
| profile cap is used so this endpoint does not reject a phone the profile
| already accepts. The real reason a phone is rarely rejected on format is
| that the product is Egyptian and numbers are written in several shapes
| (01xxxxxxxxx, +201xxxxxxxxx, 002010xxxxxxxxx); anything stricter would
| refuse valid local numbers. The length cap and a digits/symbols sanity
| check are what is actually enforced.
|
*/

function student_validator_phone( mixed $phone ): array {

    if ( $phone === null || $phone === '' ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( !is_string( $phone ) && !is_numeric( $phone ) ) {
        return [ 'valid' => false, 'errors' => [ 'phone' => 'Phone must be a valid string.' ] ];
    }

    $phone = trim( (string) $phone );

    if ( $phone === '' ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( mb_strlen( $phone ) > 50 ) {
        return [ 'valid' => false, 'errors' => [ 'phone' => 'Phone must not exceed 50 characters.' ] ];
    }

    if ( !preg_match( '/^[0-9+()\-.\s]{5,50}$/', $phone ) ) {
        return [
            'valid'  => false,
            'errors' => [ 'phone' => 'Phone may only contain digits, spaces and the symbols + ( ) - .' ],
        ];
    }

    if ( preg_match( '/\d/', $phone ) !== 1 ) {
        return [ 'valid' => false, 'errors' => [ 'phone' => 'Phone must contain at least one digit.' ] ];
    }

    return [ 'valid' => true, 'errors' => [] ];
}

/*
|--------------------------------------------------------------------------
| Contact URLs and Address
|--------------------------------------------------------------------------
|
| website and external_profile are optional URLs; address is optional text.
| A null or blank value means "clear", matching the existing phone
| convention. URL shape is delegated to the project's validation_rule_url()
| so this endpoint uses the same rule as the shared validator.
|
*/

function student_validator_contact_url( mixed $value, string $field, int $max_length, string $label ): array {

    if ( $value === null ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( !is_string( $value ) ) {
        return [ 'valid' => false, 'errors' => [ $field => $label . ' must be a valid URL.' ] ];
    }

    $value = trim( $value );

    if ( $value === '' ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( mb_strlen( $value ) > $max_length ) {
        return [
            'valid'  => false,
            'errors' => [ $field => $label . ' must not exceed ' . $max_length . ' characters.' ],
        ];
    }

    if ( validation_rule_url( $value ) !== true ) {
        return [ 'valid' => false, 'errors' => [ $field => $label . ' must be a valid URL.' ] ];
    }

    return [ 'valid' => true, 'errors' => [] ];
}

function student_validator_website( mixed $value ): array {
    return student_validator_contact_url( $value, 'website', 255, 'Website' );
}

function student_validator_external_profile( mixed $value ): array {
    return student_validator_contact_url( $value, 'external_profile', 255, 'External profile' );
}

function student_validator_address( mixed $value ): array {

    if ( $value === null ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( !is_string( $value ) ) {
        return [ 'valid' => false, 'errors' => [ 'address' => 'Address must be a valid string.' ] ];
    }

    $value = trim( $value );

    if ( $value === '' ) {
        return [ 'valid' => true, 'errors' => [] ];
    }

    if ( mb_strlen( $value ) > 500 ) {
        return [ 'valid' => false, 'errors' => [ 'address' => 'Address must not exceed 500 characters.' ] ];
    }

    return [ 'valid' => true, 'errors' => [] ];
}

/*
|--------------------------------------------------------------------------
| Academic Status
|--------------------------------------------------------------------------
|
| Structure only. The status must be one of the enum members, a supplied
| graduation month must name a real month, and a supplied graduation year must
| be in range.
|
| The product rule that a GRADUATE must have a graduation month is NOT checked
| here, because whether a month is already on file is a fact only the service
| can see: a student who is already a graduate and sends only
| {"academic_status":"graduate"} must not be told they are missing a month
| they already recorded. The service resolves the effective month (incoming or
| stored) and enforces that rule. Keeping it there also keeps this validator
| free of database knowledge.
|
| graduation_month is read with array_key_exists rather than ?? so a caller
| can genuinely clear the month by sending null, and so a present but
| unparseable month is reported rather than silently dropped.
|
*/

function student_validator_academic_status( string $status, array $data ): array {

    $errors = [];

    if ( $status === '' ) {
        $errors['academic_status'] = 'Academic status is required.';

        return $errors;
    }

    $status = strtolower( $status );

    if ( !in_array( $status, academic_statuses_all(), true ) ) {
        $errors['academic_status'] = 'Academic status must be one of: ' . implode( ', ', academic_statuses_all() ) . '.';

        return $errors;
    }

    if ( array_key_exists( 'graduation_month', $data ) ) {
        $raw = $data['graduation_month'];

        if ( $raw !== null && trim( (string) $raw ) !== '' && !academic_month_is_valid( $raw ) ) {
            $errors['graduation_month'] = 'Graduation month must be a month between 1 and 12, or a month name.';
        }
    }

    if ( array_key_exists( 'graduation_year', $data ) ) {
        $raw = $data['graduation_year'];

        if ( $raw !== null && trim( (string) $raw ) !== '' ) {
            $year = filter_var( $raw, FILTER_VALIDATE_INT );

            $current_year = (int) date( 'Y' );

            if ( $year === false || (int) $year < 1950 || (int) $year > $current_year + 10 ) {
                $errors['graduation_year'] = 'Graduation year must be between 1950 and ' . ( $current_year + 10 ) . '.';
            }
        }
    }

    if ( array_key_exists( 'graduation_date', $data ) ) {
        $raw = $data['graduation_date'];

        if ( $raw !== null && trim( (string) $raw ) !== '' ) {
            $parsed = DateTime::createFromFormat( 'Y-m-d', trim( (string) $raw ) );
            if ( $parsed === false || $parsed->format( 'Y-m-d' ) !== trim( (string) $raw ) ) {
                $errors['graduation_date'] = 'Graduation date must be a valid date (YYYY-MM-DD).';
            }
        }
    }

    return $errors;
}

function student_validator_profile( array $data ): array {
    $errors = [];
    $student_validation = student_validator_create( $data );

    if ( ! $student_validation['valid'] ) {
        $errors = array_merge( $errors, $student_validation['errors'] );
    }

    if ( array_key_exists( 'skills', $data ) ) {
        $skills_validation = student_validator_skills( $data['skills'] );
        if ( ! $skills_validation['valid'] ) {
            $errors = array_merge( $errors, $skills_validation['errors'] );
        }
    }

    if ( ! array_key_exists( 'cv_file_id', $data ) || empty( $data['cv_file_id'] ) ) {
        $errors['cv_file_id'] = 'CV is required.';
    }

    if ( array_key_exists( 'cv_file_id', $data ) && ! empty( $data['cv_file_id'] ) ) {
        $cv_validation = student_validator_cv_file_id( $data['cv_file_id'] );

        if ( ! $cv_validation['valid'] ) {
            $errors = array_merge( $errors, $cv_validation['errors'] );
        }
    }

    return [ 'valid'  => empty($errors), 'errors' => $errors,];
}

/**
 * Validates a create-profile payload (name-based academic fields).
 *
 * Returns the errors array directly. An empty array means the payload is valid.
 */
function student_validate_profile( array $data ): array {
    $errors = [];

    if ( !isset($data['full_name']) || trim( (string) $data['full_name'] ) === '' ) {
        $errors['full_name'] = 'Full name is required.';
    } elseif ( strlen( trim( (string) $data['full_name'] ) ) > 255 ) {
        $errors['full_name'] = 'Full name must not exceed 255 characters.';
    }

    $user_field_key = array_key_exists( 'field', $data ) ? 'field' : 'faculty';
    $user_field = trim( (string) ( $data[$user_field_key] ?? '' ) );

    if ( $user_field === '' ) {
        $errors[$user_field_key] = 'User field is required.';
    } elseif ( strlen( $user_field ) > 255 ) {
        $errors[$user_field_key] = 'User field must not exceed 255 characters.';
    }

    $specialization = trim( (string) ( $data['specialization'] ?? '' ) );

    if ( $specialization === '' ) {
        $errors['specialization'] = 'Specialization is required.';
    } elseif ( strlen( $specialization ) > 255 ) {
        $errors['specialization'] = 'Specialization must not exceed 255 characters.';
    }

    if ( array_key_exists( 'degree', $data ) && trim( (string) $data['degree'] ) !== '' ) {
        if ( strlen( trim( (string) $data['degree'] ) ) > 150 ) {
            $errors['degree'] = 'Degree must not exceed 150 characters.';
        }
    }

    if ( isset( $data['bio'] ) && strlen( trim( (string) $data['bio'] ) ) > 2000 ) {
        $errors['bio'] = 'Bio must not exceed 2000 characters.';
    }

    if ( array_key_exists( 'skills', $data ) ) {
        $skills_validation = student_validator_skills( $data['skills'] );
        if ( !$skills_validation['valid'] ) {
            $errors = array_merge( $errors, $skills_validation['errors'] );
        }
    }

    if ( array_key_exists( 'cv_file_id', $data ) && !empty( $data['cv_file_id'] ) ) {
        $cv_validation = student_validator_cv_file_id( $data['cv_file_id'] );
        if ( !$cv_validation['valid'] ) {
            $errors = array_merge( $errors, $cv_validation['errors'] );
        }
    }

    return $errors;
}

/**
 * Validates an update-profile payload (name-based academic fields).
 *
 * Returns the errors array directly. An empty array means the payload is valid.
 */
function student_validate_profile_update( array $data ): array {
    if ( empty( $data ) ) {
        return [ 'general' => 'No data was provided for update.', ];
    }

    $errors = [];

    if ( array_key_exists( 'full_name', $data ) ) {
        $full_name = trim( (string) $data['full_name'] );
        if ( $full_name === '' ) {
            $errors['full_name'] = 'Full name cannot be empty.';
        } elseif ( strlen( $full_name ) > 255 ) {
            $errors['full_name'] = 'Full name must not exceed 255 characters.';
        }
    }

    $user_field_key = array_key_exists( 'field', $data ) ? 'field' : 'faculty';

    if ( array_key_exists( $user_field_key, $data ) ) {
        $user_field = trim( (string) $data[$user_field_key] );
        if ( $user_field === '' ) {
            $errors[$user_field_key] = 'User field cannot be empty.';
        } elseif ( strlen( $user_field ) > 255 ) {
            $errors[$user_field_key] = 'User field must not exceed 255 characters.';
        }
    }

    if ( array_key_exists( 'specialization', $data ) ) {
        $specialization = trim( (string) $data['specialization'] );
        if ( $specialization === '' ) {
            $errors['specialization'] = 'Specialization cannot be empty.';
        } elseif ( strlen( $specialization ) > 255 ) {
            $errors['specialization'] = 'Specialization must not exceed 255 characters.';
        }
    }

    if ( array_key_exists( 'degree', $data ) && trim( (string) $data['degree'] ) !== '' ) {
        if ( strlen( trim( (string) $data['degree'] ) ) > 150 ) {
            $errors['degree'] = 'Degree must not exceed 150 characters.';
        }
    }

    if ( array_key_exists( 'bio', $data ) && strlen( trim( (string) $data['bio'] ) ) > 2000 ) {
        $errors['bio'] = 'Bio must not exceed 2000 characters.';
    }

    if ( array_key_exists( 'phone', $data ) && strlen( trim( (string) $data['phone'] ) ) > 50 ) {
        $errors['phone'] = 'Phone must not exceed 50 characters.';
    }

    if ( array_key_exists( 'city', $data ) && strlen( trim( (string) $data['city'] ) ) > 100 ) {
        $errors['city'] = 'City must not exceed 100 characters.';
    }

    if ( array_key_exists( 'graduation_year', $data ) ) {
        $year = (int) $data['graduation_year'];
        $current_year = (int) date( 'Y' );
        if ( $year < 1900 || $year > $current_year + 10 ) {
            $errors['graduation_year'] = 'Graduation year is invalid.';
        }
    }

    if ( array_key_exists( 'skills', $data ) ) {
        $skills_validation = student_validator_skills( $data['skills'] );
        if ( !$skills_validation['valid'] ) {
            $errors = array_merge( $errors, $skills_validation['errors'] );
        }
    }

    if ( array_key_exists( 'cv_file_id', $data ) && !empty( $data['cv_file_id'] ) ) {
        $cv_validation = student_validator_cv_file_id( $data['cv_file_id'] );
        if ( !$cv_validation['valid'] ) {
            $errors = array_merge( $errors, $cv_validation['errors'] );
        }
    }

    return $errors;
}

/**
 * Validates a complete-profile payload (name-based academic fields,
 * skills and CV are required).
 *
 * Returns the errors array directly. An empty array means the payload is valid.
 */
function student_validate_complete_profile( array $data ): array {
    $errors = student_validate_profile( $data );

    if ( !array_key_exists( 'skills', $data ) || !is_array( $data['skills'] ) || empty( $data['skills'] ) ) {
        $errors['skills'] = 'At least one skill is required.';
    }

    if ( !array_key_exists( 'cv_file_id', $data ) || empty( $data['cv_file_id'] ) ) {
        $errors['cv_file_id'] = 'CV is required.';
    }

    return $errors;
}


/*
|--------------------------------------------------------------------------
| Education
|--------------------------------------------------------------------------
|
| Structure only: no database query and no lookup resolution, so the service
| stays responsible for whether a referenced id actually exists.
|
| $partial is set for PUT, where a field the caller omits keeps its stored
| value. On a create, at least one of the two institution/degree fields is
| required - an entirely empty record would be meaningless - but on a partial
| update omitting everything is legitimate, so the requirement is enforced
| only for a full payload.
|
| Ids are checked for shape here and resolved for existence in the service, so
| a bad id produces "not recognized" rather than "must be a positive integer".
|
*/
function student_validate_education( array $data, bool $partial = false ): array {

    $errors = [];

    if ( empty( $data ) ) {
        return [ 'general' => 'No data was provided for update.' ];
    }

    $id_fields = [ 'degree_id', 'university_id', 'faculty_id', 'field_id', 'specialization_id' ];

    foreach ( $id_fields as $field ) {
        if ( !array_key_exists( $field, $data ) ) {
            continue;
        }

        $value = $data[ $field ];

        if ( $value === null || $value === '' ) {
            continue;
        }

        if ( filter_var( $value, FILTER_VALIDATE_INT ) === false || (int) $value <= 0 ) {
            $errors[ $field ] = 'The selected ' . str_replace( '_', ' ', $field ) . ' is not valid.';
        }
    }

    /*
     | degree_name is the education degree Lookup ID (integer). A string such
     | as "Bachelor of Science" is therefore rejected: the degree is referenced
     | by its stable lookup id, which the service resolves through the degrees
     | lookup. The resolved human-readable name is what gets persisted alongside
     | the numeric id in degree_id.
     */
    if ( array_key_exists( 'degree_name', $data ) ) {
        $degree_name_value = $data[ 'degree_name' ];

        if ( $degree_name_value !== null ) {
            if ( !is_int( $degree_name_value ) || (int) $degree_name_value <= 0 ) {
                $errors[ 'degree_name' ] = 'Degree must be a valid education degree ID.';
            }
        }
    }

    /*
     | university_name, field_study and grade are free-text fields. degree_name
     | moved to the integer lookup-id path above.
     */
    $text_fields = [ 'university_name', 'field_study', 'grade' ];

    foreach ( $text_fields as $field ) {
        if ( !array_key_exists( $field, $data ) ) {
            continue;
        }

        $value = $data[ $field ];

        if ( $value === null ) {
            continue;
        }

        if ( !is_string( $value ) && !is_numeric( $value ) ) {
            $errors[ $field ] = ucfirst( str_replace( '_', ' ', $field ) ) . ' must be a valid string.';
            continue;
        }

        $length = mb_strlen( trim( (string) $value ) );

        $limit = ( $field === 'grade' ) ? 100 : 255;

        if ( $length > $limit ) {
            $errors[ $field ] = ucfirst( str_replace( '_', ' ', $field ) ) . ' must not exceed ' . $limit . ' characters.';
        }
    }

    if ( array_key_exists( 'study_mode', $data ) && $data['study_mode'] !== null && trim( (string) $data['study_mode'] ) !== '' ) {
        $mode = strtolower( trim( (string) $data['study_mode'] ) );

        if ( !in_array( $mode, study_modes_all(), true ) ) {
            $errors['study_mode'] = 'Study mode must be one of: ' . implode( ', ', study_modes_all() ) . '.';
        }
    }

    if ( array_key_exists( 'is_current', $data ) ) {
        $is_current = $data['is_current'];

        $accepted = [ true, false, 1, 0, '1', '0', 'true', 'false' ];

        if ( !in_array( $is_current, $accepted, true ) ) {
            $errors['is_current'] = 'is_current must be a boolean.';
        }
    }

    if ( !$partial ) {
        $has_subject = false;

        foreach ( [ 'degree_id', 'degree_name', 'university_id', 'university_name' ] as $field ) {
            if ( !empty( $data[ $field ] ) ) {
                $has_subject = true;
                break;
            }
        }

        if ( !$has_subject ) {
            $errors['general'] = 'An education record needs a degree or a university.';
        }
    }

    return $errors;
}