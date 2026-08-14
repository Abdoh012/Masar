<?php

/**
 * MASAR - Student Service
 *
 * Contains business logic related to students.
 *
 * Responsibilities:
 * - Create student profile.
 * - Retrieve student profile.
 * - Update student profile.
 * - Check profile completion.
 * - Control access to student profile data.
 *
 * Database operations belong to student_repository.php.
 * Profile-specific operations belong to student_profile_service.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/student_repository.php';
require_once __DIR__ . '/../repositories/student_profile_repository.php';


/*
|--------------------------------------------------------------------------
| Create Student Profile
|--------------------------------------------------------------------------
*/

function student_service_create_profile(
    int $user_id,
    array $data
): array {

    if ($user_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' => 'Invalid user ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Profile
    |--------------------------------------------------------------------------
    */

    $existing =
        student_repository_find_by_user_id(
            $user_id
        );


    if ($existing) {

        return [
            'error' => true,
            'status' => 409,
            'message' =>
                'Student profile already exists.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Data
    |--------------------------------------------------------------------------
    */

    $academic_data = student_repository_resolve_academic_data(
        trim((string) ($data['university'] ?? '')),
        trim((string) ($data['faculty'] ?? '')),
        trim((string) ($data['specialization'] ?? ''))
    );

    if ($academic_data === null) {
        return [
            'error' => true,
            'status' => 422,
            'message' => 'University, faculty, or specialization is incorrect.'
        ];
    }

    $student_data = [

        'user_id' =>
            $user_id,

        'full_name' =>
            trim(
                $data['full_name']
                    ?? ''
            ),

        'university_id' => $academic_data['university_id'],
        'faculty_id' => $academic_data['faculty_id'],
        'specialization_id' => $academic_data['specialization_id'],

    ];

    if (!student_repository_academic_data_exists(
        $student_data['university_id'],
        $student_data['faculty_id'],
        $student_data['specialization_id']
    )) {
        return [
            'error' => true,
            'status' => 422,
            'message' => 'University, faculty, or specialization is incorrect.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create Student
    |--------------------------------------------------------------------------
    */

    $student_id =
        student_repository_create(
            $student_data
        );


    if (!$student_id) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to create student profile.'
        ];
    }


    $student = student_repository_find_by_id($student_id);

    return [
        'data' => [
            'student' => $student,
        ],
    ];
}


/*
|--------------------------------------------------------------------------
| Get Student Profile
|--------------------------------------------------------------------------
*/

function student_get_profile(
    int $user_id
): array {

    if ($user_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid user ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_user_id(
            $user_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Profile Data
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            (int) $student['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Merge Profile
    |--------------------------------------------------------------------------
    */

    return [

        'data' => [

            'student' =>
                $student,

            'profile' =>
                $profile,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Update Student Profile
|--------------------------------------------------------------------------
*/

function student_service_update_profile(
    int $user_id,
    array $data
): array {

    if ($user_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid user ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_user_id(
            $user_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    $student_id =
        (int) $student['id'];


    /*
    |--------------------------------------------------------------------------
    | Allowed Student Fields
    |--------------------------------------------------------------------------
    */

    $student_data = [];


    if (
        array_key_exists(
            'university',
            $data
        )
    ) {

        $student_data['university'] =
            trim(
                $data['university']
            );
    }


    if (
        array_key_exists(
            'degree',
            $data
        )
    ) {

        $student_data['degree'] =
            trim(
                $data['degree']
            );
    }


    if (
        array_key_exists(
            'field',
            $data
        )
    ) {

        $student_data['field'] =
            trim(
                $data['field']
            );
    }


    if (
        array_key_exists(
            'specialization',
            $data
        )
    ) {

        $student_data['specialization'] =
            trim(
                $data['specialization']
            );
    }


    if (
        array_key_exists(
            'bio',
            $data
        )
    ) {

        $student_data['bio'] =
            trim(
                $data['bio']
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Student Table
    |--------------------------------------------------------------------------
    */

    if (!empty($student_data)) {

        $updated =
            student_repository_update(
                $student_id,
                $student_data
            );


        if (!$updated) {

            return [
                'error' => true,
                'status' => 500,
                'message' =>
                    'Unable to update student profile.'
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile Data
    |--------------------------------------------------------------------------
    */

    $profile_data = [];


    if (
        array_key_exists(
            'skills',
            $data
        )
    ) {

        $profile_data['skills'] =
            $data['skills'];
    }


    if (!empty($profile_data)) {

        $profile_updated =
            student_profile_repository_update(
                $student_id,
                $profile_data
            );


        if (!$profile_updated) {

            return [
                'error' => true,
                'status' => 500,
                'message' =>
                    'Unable to update student profile data.'
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated Profile
    |--------------------------------------------------------------------------
    */

    return student_get_profile(
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Complete Student Profile
|--------------------------------------------------------------------------
|
| Used when the student submits all required
| profile information for the first time.
|
*/

function student_complete_profile_data(
    int $user_id,
    array $data
): array {

    if ($user_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid user ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_user_id(
            $user_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Main Student Data
    |--------------------------------------------------------------------------
    */

    $student_data = [

        'university' =>
            trim(
                $data['university']
            ),

        'degree' =>
            trim(
                $data['degree']
            ),

        'field' =>
            trim(
                $data['field']
            ),

        'specialization' =>
            trim(
                $data['specialization']
            ),

        'bio' =>
            trim(
                $data['bio']
            ),

    ];


    $updated =
        student_repository_update(
            (int) $student['id'],
            $student_data
        );


    if (!$updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to update student information.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Profile Data
    |--------------------------------------------------------------------------
    */

    $profile_data = [

        'skills' =>
            $data['skills']
                ?? [],

    ];


    $profile_updated =
        student_profile_repository_update(
            (int) $student['id'],
            $profile_data
        );


    if (!$profile_updated) {

        return [
            'error' => true,
            'status' => 500,
            'message' =>
                'Unable to update student profile data.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated Profile
    |--------------------------------------------------------------------------
    */

    return student_get_profile(
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Get Public Student Profile
|--------------------------------------------------------------------------
|
| Companies can use this when searching for students.
|
*/

function student_get_public_profile(
    int $student_id,
    array $current_user
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
    | Check Current User
    |--------------------------------------------------------------------------
    */

    $role =
        $current_user['role']
            ?? '';


    /*
    |--------------------------------------------------------------------------
    | Allowed Roles
    |--------------------------------------------------------------------------
    */

    $allowed_roles = [
        'student',
        'company',
        'admin',
    ];


    if (
        !in_array(
            $role,
            $allowed_roles,
            true
        )
    ) {

        return [
            'error' => true,
            'status' => 403,
            'message' =>
                'You are not allowed to view student profiles.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
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
    | Get Public Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            $student_id
        );


    /*
    |--------------------------------------------------------------------------
    | Remove Private Data
    |--------------------------------------------------------------------------
    */

    unset(
        $student['user_id']
    );


    /*
    |--------------------------------------------------------------------------
    | Return Public Data
    |--------------------------------------------------------------------------
    */

    return [

        'data' => [

            'student' =>
                $student,

            'profile' =>
                $profile,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Get Profile Completion Status
|--------------------------------------------------------------------------
*/

function student_get_profile_status(
    int $user_id
): array {

    if ($user_id <= 0) {

        return [
            'error' => true,
            'status' => 422,
            'message' =>
                'Invalid user ID.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        student_repository_find_by_user_id(
            $user_id
        );


    if (!$student) {

        return [
            'error' => true,
            'status' => 404,
            'message' =>
                'Student profile not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Required Fields
    |--------------------------------------------------------------------------
    */

    $required_fields = [

        'university' =>
            $student['university']
                ?? null,

        'degree' =>
            $student['degree']
                ?? null,

        'field' =>
            $student['field']
                ?? null,

        'specialization' =>
            $student['specialization']
                ?? null,

    ];


    $missing_fields = [];


    foreach (
        $required_fields
        as $field => $value
    ) {

        if (
            $value === null
            ||
            trim(
                (string) $value
            ) === ''
        ) {

            $missing_fields[] =
                $field;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Data
    |--------------------------------------------------------------------------
    */

    $profile =
        student_profile_repository_find_by_student_id(
            (int) $student['id']
        );


    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */

    $skills =
        $profile['skills']
            ?? [];


    if (
        is_string($skills)
    ) {

        $decoded =
            json_decode(
                $skills,
                true
            );


        if (
            is_array($decoded)
        ) {

            $skills =
                $decoded;
        }
    }


    if (
        empty($skills)
    ) {

        $missing_fields[] =
            'skills';
    }


    /*
    |--------------------------------------------------------------------------
    | Completion
    |--------------------------------------------------------------------------
    */

    $completed =
        empty($missing_fields);


    return [

        'data' => [

            'completed' =>
                $completed,

            'missing_fields' =>
                $missing_fields,

            'completion_percentage' =>
                student_calculate_completion_percentage(
                    $student,
                    $profile
                ),

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Calculate Completion Percentage
|--------------------------------------------------------------------------
*/

function student_calculate_completion_percentage(
    array $student,
    ?array $profile
): int {

    $total = 5;
    $completed = 0;


    /*
    |--------------------------------------------------------------------------
    | Main Student Fields
    |--------------------------------------------------------------------------
    */

    $fields = [

        'university',
        'degree',
        'field',
        'specialization',
    ];


    foreach (
        $fields
        as $field
    ) {

        if (
            isset(
                $student[$field]
            )
            &&
            trim(
                (string) $student[$field]
            ) !== ''
        ) {

            $completed++;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $profile['skills']
                ?? null
        )
    ) {

        $completed++;
    }


    return (int) round(
        (
            $completed /
            $total
        ) * 100
    );
}
