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


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/student_profile_repository.php';
require_once __DIR__ . '/../repositories/student_repository.php';


/*
|--------------------------------------------------------------------------
| Get Student Profile Data
|--------------------------------------------------------------------------
*/

function student_profile_get(
    int $student_id
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_id(
            $student_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    if (!$profile) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile data not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Skills
    |--------------------------------------------------------------------------
    */

    $profile['skills'] =
        student_profile_normalize_skills(
            $profile['skills']
                ?? []
        );


    /*
    |--------------------------------------------------------------------------
    | Normalize CV
    |--------------------------------------------------------------------------
    */

    $profile['cv'] =
        student_profile_normalize_cv(
            $profile
        );


    return [

        'data' => $profile,

    ];
}


/*
|--------------------------------------------------------------------------
| Update Student Skills
|--------------------------------------------------------------------------
*/

function student_profile_update_skills(
    int $student_id,
    array $skills
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Skills
    |--------------------------------------------------------------------------
    */

    $skills =
        student_profile_clean_skills(
            $skills
        );


    if (empty($skills)) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'At least one skill is required.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_id(
            $student_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Skills
    |--------------------------------------------------------------------------
    */

    $updated =
        student_profile_repository_update(
            $student_id,
            [
                'skills' => $skills
            ]
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to update student skills.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated Data
    |--------------------------------------------------------------------------
    */

    return student_profile_get(
        $student_id
    );
}


/*
|--------------------------------------------------------------------------
| Add Student Skill
|--------------------------------------------------------------------------
*/

function student_profile_add_skill(
    int $student_id,
    string $skill
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Clean Skill
    |--------------------------------------------------------------------------
    */

    $skill =
        trim($skill);


    if ($skill === '') {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Skill cannot be empty.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    if (!$profile) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Current Skills
    |--------------------------------------------------------------------------
    */

    $skills =
        student_profile_normalize_skills(
            $profile['skills']
                ?? []
        );


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Skills
    |--------------------------------------------------------------------------
    */

    foreach ($skills as $existing_skill) {

        if (
            strtolower($existing_skill)
            ===
            strtolower($skill)
        ) {

            return [
                'error' => true,
                'status' => 409,
                'message' =>
                    'This skill already exists.'
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Add Skill
    |--------------------------------------------------------------------------
    */

    $skills[] =
        $skill;


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $updated =
        student_profile_repository_update(
            $student_id,
            [
                'skills' => $skills
            ]
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to add skill.'
        ];
    }


    return [

        'data' => [

            'skills' =>
                $skills,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Remove Student Skill
|--------------------------------------------------------------------------
*/

function student_profile_remove_skill(
    int $student_id,
    string $skill
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    $skill =
        trim($skill);


    if ($skill === '') {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Skill cannot be empty.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    if (!$profile) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Current Skills
    |--------------------------------------------------------------------------
    */

    $skills =
        student_profile_normalize_skills(
            $profile['skills']
                ?? []
        );


    /*
    |--------------------------------------------------------------------------
    | Remove Skill
    |--------------------------------------------------------------------------
    */

    $new_skills = [];


    foreach ($skills as $existing_skill) {

        if (
            strtolower($existing_skill)
            !==
            strtolower($skill)
        ) {

            $new_skills[] =
                $existing_skill;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check If Skill Existed
    |--------------------------------------------------------------------------
    */

    if (
        count($skills)
        ===
        count($new_skills)
    ) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Skill not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $updated =
        student_profile_repository_update(
            $student_id,
            [
                'skills' =>
                    $new_skills
            ]
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to remove skill.'
        ];
    }


    return [

        'data' => [

            'skills' =>
                $new_skills,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Set Student CV
|--------------------------------------------------------------------------
|
| IMPORTANT:
| The actual file upload is NOT performed here.
|
| The files module uploads the physical file and
| returns the file ID/path.
|
*/

function student_profile_set_cv(
    int $student_id,
    int $file_id
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    if ($file_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid file ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_id(
            $student_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Save CV Reference
    |--------------------------------------------------------------------------
    */

    $updated =
        student_profile_repository_update(
            $student_id,
            [
                'cv_file_id' =>
                    $file_id
            ]
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to attach CV.'
        ];
    }


    return [

        'data' => [

            'message' =>
                'CV attached successfully.',

            'cv_file_id' =>
                $file_id,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Remove Student CV
|--------------------------------------------------------------------------
|
| This only removes the relationship between
| student profile and CV.
|
| Physical file deletion is handled by files module.
|
*/

function student_profile_remove_cv(
    int $student_id
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    if (!$profile) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    $updated =
        student_profile_repository_update(
            $student_id,
            [
                'cv_file_id' => null
            ]
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to remove CV.'
        ];
    }


    return [

        'data' => [

            'message' =>
                'CV removed successfully.',

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Check Profile Completion
|--------------------------------------------------------------------------
*/

function student_profile_is_complete(
    int $student_id
): array {

    if ($student_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid student ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student Data
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_id(
            $student_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Data
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    if (!$profile) {

        return [

            'data' => [

                'complete' =>
                    false,

                'missing' => [

                    'profile' => true,

                ],

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Required Fields
    |--------------------------------------------------------------------------
    */

    $missing = [];


    $required_student_fields = [

        'university',
        'degree',
        'field',
        'specialization',

    ];


    foreach (
        $required_student_fields
        as $field
    ) {

        if (
            !isset(
                $student[$field]
            )
            ||
            trim(
                (string) $student[$field]
            ) === ''
        ) {

            $missing[] =
                $field;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */

    $skills =
        student_profile_normalize_skills(
            $profile['skills']
                ?? []
        );


    if (empty($skills)) {

        $missing[] =
            'skills';
    }


    /*
    |--------------------------------------------------------------------------
    | CV
    |--------------------------------------------------------------------------
    */

    if (
        empty(
            $profile['cv_file_id']
                ?? null
        )
    ) {

        $missing[] =
            'cv';
    }


    return [

        'data' => [

            'complete' =>
                empty($missing),

            'missing' =>
                $missing,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Normalize Skills
|--------------------------------------------------------------------------
*/

function student_profile_normalize_skills(
    mixed $skills
): array {

    /*
    |--------------------------------------------------------------------------
    | Already Array
    |--------------------------------------------------------------------------
    */

    if (
        is_array($skills)
    ) {

        return student_profile_clean_skills(
            $skills
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON String
    |--------------------------------------------------------------------------
    */

    if (
        is_string($skills)
        &&
        trim($skills) !== ''
    ) {

        $decoded =
            json_decode(
                $skills,
                true
            );


        if (
            is_array($decoded)
        ) {

            return student_profile_clean_skills(
                $decoded
            );
        }
    }


    return [];
}


/*
|--------------------------------------------------------------------------
| Clean Skills
|--------------------------------------------------------------------------
*/

function student_profile_clean_skills(
    array $skills
): array {

    $clean = [];


    foreach ($skills as $skill) {

        if (
            !is_string($skill)
            &&
            !is_numeric($skill)
        ) {

            continue;
        }


        $skill =
            trim(
                (string) $skill
            );


        if ($skill === '') {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Maximum Skill Length
        |--------------------------------------------------------------------------
        */

        if (
            strlen($skill) > 100
        ) {

            $skill =
                substr(
                    $skill,
                    0,
                    100
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicates
        |--------------------------------------------------------------------------
        */

        $exists = false;


        foreach ($clean as $existing) {

            if (
                strtolower($existing)
                ===
                strtolower($skill)
            ) {

                $exists = true;

                break;
            }
        }


        if (!$exists) {

            $clean[] =
                $skill;
        }
    }


    return array_values(
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Normalize CV
|--------------------------------------------------------------------------
*/

function student_profile_normalize_cv(
    array $profile
): ?array {

    $file_id =
        $profile['cv_file_id']
            ?? null;


    if (
        empty($file_id)
    ) {

        return null;
    }


    return [

        'file_id' =>
            (int) $file_id,

    ];
}
