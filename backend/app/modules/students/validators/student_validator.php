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


/*
|--------------------------------------------------------------------------
| Validate Student Creation
|--------------------------------------------------------------------------
|
| Used when creating the student profile for the first time.
|
*/

function student_validator_create(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | University
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['university'])
        ||
        trim(
            (string) $data['university']
        ) === ''
    ) {

        $errors['university'] =
            'University is required.';

    } elseif (
        strlen(
            trim(
                (string) $data['university']
            )
        ) > 255
    ) {

        $errors['university'] =
            'University must not exceed 255 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Degree
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['degree'])
        ||
        trim(
            (string) $data['degree']
        ) === ''
    ) {

        $errors['degree'] =
            'Degree is required.';

    } elseif (
        strlen(
            trim(
                (string) $data['degree']
            )
        ) > 255
    ) {

        $errors['degree'] =
            'Degree must not exceed 255 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Field
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['field'])
        ||
        trim(
            (string) $data['field']
        ) === ''
    ) {

        $errors['field'] =
            'Field is required.';

    } elseif (
        strlen(
            trim(
                (string) $data['field']
            )
        ) > 255
    ) {

        $errors['field'] =
            'Field must not exceed 255 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['specialization'])
        ||
        trim(
            (string) $data['specialization']
        ) === ''
    ) {

        $errors['specialization'] =
            'Specialization is required.';

    } elseif (
        strlen(
            trim(
                (string) $data['specialization']
            )
        ) > 255
    ) {

        $errors['specialization'] =
            'Specialization must not exceed 255 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Bio
    |--------------------------------------------------------------------------
    */

    if (
        isset($data['bio'])
        &&
        strlen(
            trim(
                (string) $data['bio']
            )
        ) > 2000
    ) {

        $errors['bio'] =
            'Bio must not exceed 2000 characters.';
    }


    /*
    |--------------------------------------------------------------------------
    | Return Result
    |--------------------------------------------------------------------------
    */

    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Student Update
|--------------------------------------------------------------------------
|
| All fields are optional during update.
| If a field is supplied, it must be valid.
|
*/

function student_validator_update(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Prevent Empty Update
    |--------------------------------------------------------------------------
    */

    if (empty($data)) {

        return [

            'valid' => false,

            'errors' => [

                'general' =>
                    'No data was provided for update.',

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | University
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'university',
            $data
        )
    ) {

        $university =
            trim(
                (string) $data['university']
            );


        if ($university === '') {

            $errors['university'] =
                'University cannot be empty.';

        } elseif (
            strlen($university) > 255
        ) {

            $errors['university'] =
                'University must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Degree
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'degree',
            $data
        )
    ) {

        $degree =
            trim(
                (string) $data['degree']
            );


        if ($degree === '') {

            $errors['degree'] =
                'Degree cannot be empty.';

        } elseif (
            strlen($degree) > 255
        ) {

            $errors['degree'] =
                'Degree must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Field
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'field',
            $data
        )
    ) {

        $field =
            trim(
                (string) $data['field']
            );


        if ($field === '') {

            $errors['field'] =
                'Field cannot be empty.';

        } elseif (
            strlen($field) > 255
        ) {

            $errors['field'] =
                'Field must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Specialization
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'specialization',
            $data
        )
    ) {

        $specialization =
            trim(
                (string) $data['specialization']
            );


        if ($specialization === '') {

            $errors['specialization'] =
                'Specialization cannot be empty.';

        } elseif (
            strlen($specialization) > 255
        ) {

            $errors['specialization'] =
                'Specialization must not exceed 255 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Bio
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'bio',
            $data
        )
    ) {

        if (
            strlen(
                trim(
                    (string) $data['bio']
                )
            ) > 2000
        ) {

            $errors['bio'] =
                'Bio must not exceed 2000 characters.';
        }
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Skills
|--------------------------------------------------------------------------
*/

function student_validator_skills(
    mixed $skills
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Must Be Array
    |--------------------------------------------------------------------------
    */

    if (
        !is_array($skills)
    ) {

        return [

            'valid' => false,

            'errors' => [

                'skills' =>
                    'Skills must be an array.',

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | At Least One Skill
    |--------------------------------------------------------------------------
    */

    if (
        empty($skills)
    ) {

        $errors['skills'] =
            'At least one skill is required.';

        return [

            'valid' =>
                false,

            'errors' =>
                $errors,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Maximum Number Of Skills
    |--------------------------------------------------------------------------
    */

    if (
        count($skills) > 50
    ) {

        $errors['skills'] =
            'You cannot add more than 50 skills.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Each Skill
    |--------------------------------------------------------------------------
    */

    foreach (
        $skills as $index => $skill
    ) {

        if (
            !is_string($skill)
            &&
            !is_numeric($skill)
        ) {

            $errors[
                'skills.' . $index
            ] =
                'Skill must be a valid string.';

            continue;
        }


        $skill =
            trim(
                (string) $skill
            );


        if ($skill === '') {

            $errors[
                'skills.' . $index
            ] =
                'Skill cannot be empty.';

            continue;
        }


        if (
            strlen($skill) > 100
        ) {

            $errors[
                'skills.' . $index
            ] =
                'Skill must not exceed 100 characters.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Skills
    |--------------------------------------------------------------------------
    */

    $normalized = [];


    foreach ($skills as $skill) {

        if (
            !is_string($skill)
            &&
            !is_numeric($skill)
        ) {

            continue;
        }


        $normalized[] =
            strtolower(
                trim(
                    (string) $skill
                )
            );
    }


    if (
        count($normalized)
        !==
        count(
            array_unique(
                $normalized
            )
        )
    ) {

        $errors['skills'] =
            'Duplicate skills are not allowed.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Add Skill
|--------------------------------------------------------------------------
*/

function student_validator_add_skill(
    mixed $skill
): array {

    $errors = [];


    if (
        !is_string($skill)
        &&
        !is_numeric($skill)
    ) {

        $errors['skill'] =
            'Skill must be a valid string.';

        return [

            'valid' => false,

            'errors' =>
                $errors,

        ];
    }


    $skill =
        trim(
            (string) $skill
        );


    if ($skill === '') {

        $errors['skill'] =
            'Skill cannot be empty.';

    } elseif (
        strlen($skill) > 100
    ) {

        $errors['skill'] =
            'Skill must not exceed 100 characters.';
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Student ID
|--------------------------------------------------------------------------
*/

function student_validator_student_id(
    mixed $student_id
): array {

    if (
        filter_var(
            $student_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $student_id <= 0
    ) {

        return [

            'valid' => false,

            'errors' => [

                'student_id' =>
                    'Invalid student ID.',

            ],

        ];
    }


    return [

        'valid' => true,

        'errors' => [],

    ];
}


/*
|--------------------------------------------------------------------------
| Validate CV File ID
|--------------------------------------------------------------------------
*/

function student_validator_cv_file_id(
    mixed $file_id
): array {

    if (
        filter_var(
            $file_id,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $file_id <= 0
    ) {

        return [

            'valid' => false,

            'errors' => [

                'file_id' =>
                    'Invalid CV file ID.',

            ],

        ];
    }


    return [

        'valid' => true,

        'errors' => [],

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Profile Data
|--------------------------------------------------------------------------
|
| Used when the complete student profile is submitted.
|
*/

function student_validator_profile(
    array $data
): array {

    $errors = [];


    /*
    |--------------------------------------------------------------------------
    | Basic Student Fields
    |--------------------------------------------------------------------------
    */

    $student_validation =
        student_validator_create(
            $data
        );


    if (
        !$student_validation['valid']
    ) {

        $errors =
            array_merge(
                $errors,
                $student_validation['errors']
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'skills',
            $data
        )
    ) {

        $skills_validation =
            student_validator_skills(
                $data['skills']
            );


        if (
            !$skills_validation['valid']
        ) {

            $errors =
                array_merge(
                    $errors,
                    $skills_validation['errors']
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CV
    |--------------------------------------------------------------------------
    |
    | The CV is required according to the MVP
    | student profile requirements.
    |
    */

    if (
        !array_key_exists(
            'cv_file_id',
            $data
        )
        ||
        empty(
            $data['cv_file_id']
        )
    ) {

        $errors['cv_file_id'] =
            'CV is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate CV ID
    |--------------------------------------------------------------------------
    */

    if (
        array_key_exists(
            'cv_file_id',
            $data
        )
        &&
        !empty(
            $data['cv_file_id']
        )
    ) {

        $cv_validation =
            student_validator_cv_file_id(
                $data['cv_file_id']
            );


        if (
            !$cv_validation['valid']
        ) {

            $errors =
                array_merge(
                    $errors,
                    $cv_validation['errors']
                );
        }
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

    ];
}
