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

    if ( count($skills) > 50 ) {
        $errors['skills'] = 'You cannot add more than 50 skills.';
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

    if ( ! is_string($skill) && ! is_numeric($skill) ) {
        $errors['skill'] = 'Skill must be a valid string.';
        return [ 'valid'  => false, 'errors' => $errors, ];
    }

    $skill = trim( (string) $skill );

    if ($skill === '') {
        $errors['skill'] = 'Skill cannot be empty.';
    } elseif ( strlen($skill) > 100 ) {
        $errors['skill'] = 'Skill must not exceed 100 characters.';
    }

    return [ 'valid'  => empty($errors), 'errors' => $errors,];
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