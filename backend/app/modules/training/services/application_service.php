<?php

/**
 * MASAR - Training Application Service
 *
 * Business logic for training applications.
 *
 * Native PHP - No OOP.
 *
 * Controller
 *     ↓
 * Service
 *     ↓
 * Repository
 *     ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/application_repository.php';
require_once __DIR__ . '/../repositories/training_repository.php';

require_once __DIR__ . '/../validators/application_validator.php';

require_once __DIR__ . '/../../files/repositories/file_repository.php';

require_once __DIR__ . '/../../notifications/services/notification_service.php';

require_once __DIR__ . '/../../../core/database/transaction.php';


/*
|--------------------------------------------------------------------------
| Apply For Training
|--------------------------------------------------------------------------
|
| Student submits an application for a training opportunity.
|
*/

function application_service_create(
    int $user_id,
    int $training_id,
    array $data = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Training ID
    |--------------------------------------------------------------------------
    */

    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Training
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Training Status
    |--------------------------------------------------------------------------
    */

    $available_statuses = [

        'published',

        'open',

        'active'

    ];


    if (
        isset($training['status'])
        &&
        !in_array(
            $training['status'],
            $available_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training opportunity is not accepting applications.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Deadline
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $training['application_deadline']
        )
    ) {

        $deadline =
            strtotime(
                $training['application_deadline']
            );


        if (
            $deadline !== false
            &&
            $deadline < time()
        ) {

            return [

                'success' => false,

                'message' =>
                    'The application deadline has passed.',

                'errors' => [],

                'status_code' => 409

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Capacity
    |--------------------------------------------------------------------------
    */

    if (
        isset($training['capacity'])
        &&
        $training['capacity'] !== null
    ) {

        $capacity =
            (int) $training['capacity'];


        if (
            $capacity > 0
        ) {

            $accepted_count =
                application_repository_count_accepted(
                    $training_id
                );


            if (
                $accepted_count >= $capacity
            ) {

                return [

                    'success' => false,

                    'message' =>
                        'This training opportunity has reached its capacity.',

                    'errors' => [],

                    'status_code' => 409

                ];
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Application
    |--------------------------------------------------------------------------
    */

    $existing =
        application_repository_exists(
            (int) $student['student_id'],
            $training_id
        );

    /*
    | When the previous application was rejected, the student may re-apply.
    | The unique (training_id, student_id) index prevents a second INSERT,
    | so a re-application reuses the existing rejected row instead.
    */

    $reapplication_id = 0;


    if ($existing) {

        $existingApp =
            application_repository_find_student_application(
                (int) $student['student_id'],
                $training_id
            );

        if (
            $existingApp
            &&
            strtolower($existingApp['status']) !== 'rejected'
        ) {

            return [

                'success' => false,

                'message' =>
                    'You have already applied for this training opportunity.',


                'errors' => [],

                'status_code' => 409

            ];
        }

        if (
            $existingApp
            &&
            strtolower($existingApp['status']) === 'rejected'
        ) {

            $reapplication_id =
                (int) $existingApp['id'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Application Data
    |--------------------------------------------------------------------------
    */

    $validation =
        application_validator_create(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Application data is invalid.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate University / Faculty
    |--------------------------------------------------------------------------
    |
    | The university is free text and needs no existence check against a
    | lookup table; it is stored as-is on the application record. Faculty
    | remains an ID reference and is validated below.
    |
    */

    if (
        !empty($data['faculty_id'])
    ) {

        $faculty =
            application_repository_find_faculty_by_id(
                (int) $data['faculty_id']
            );

        if (!$faculty) {

            return [

                'success' => false,

                'message' =>
                    'Selected faculty was not found.',

                'errors' => [

                    'faculty_id' =>
                        'Selected faculty was not found.'

                ],

                'status_code' => 422

            ];
        }
    }


/*
    |--------------------------------------------------------------------------
    | Validate Answers Against Training Questions
    |--------------------------------------------------------------------------
    |
    | Answers are only validated when the client sends an "answers" property.
    | If answers are absent, validation is skipped and answers are treated
    | as empty (not saved).
    |
    | When answers are present, the existing validation (required questions,
    | question ownership, answer format, option constraints) is fully preserved.
*/

    $answers = [];

    if (
        isset($data['answers'])
        &&
        is_array($data['answers'])
    ) {
        $answers = $data['answers'];

        $questions =
            training_repository_get_questions(
                $training_id
            );

        $answers_validation =
            application_validator_answers(
                $answers,
                $questions
            );

        if (
            !$answers_validation['valid']
        ) {

            return [

                'success' => false,

                'message' =>
                    'Application answers are invalid.',

                'errors' =>
                    $answers_validation['errors'],

                'status_code' => 422

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate CV Ownership
    |--------------------------------------------------------------------------
    |
    | The CV is uploaded separately through the existing file upload endpoint
    | (POST /api/v1/files). Here only its id (cv_file_id) is accepted and it
    | must reference a file record owned by the authenticated student. The
    | file repository enforces ownership, so a student can never attach
    | another user's file.
    |
    */

    $cv_file_id =
        isset($data['cv_file_id'])
            ? (int) $data['cv_file_id']
            : 0;

    if ($cv_file_id > 0) {

        $cv_file =
            file_repository_find_for_user(
                $cv_file_id,
                $user_id
            );

        if (!$cv_file) {

            return [

                'success' => false,

                'message' =>
                    'Selected CV file was not found.',

                'errors' => [

                    'cv_file_id' =>
                        'Selected CV file was not found.'

                ],

                'status_code' => 422

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Application
    |--------------------------------------------------------------------------
    */

    $applicant_type =
        isset($data['current_status'])
            ? strtolower(
                trim(
                    (string) $data['current_status']
                )
            )
            : (
                isset($data['applicant_type'])
                    ? strtolower(
                        trim(
                            (string) $data['applicant_type']
                        )
                    )
                    : 'student'
            );

    $application_data = [

        'training_id' =>
            $training_id,

        'student_id' =>
            (int) $student['student_id'],

        /*
        | The company is derived from the selected training and never trusted
        | from the client payload. A client-supplied company_id is overwritten
        | with the real training company.
        */
        'company_id' =>
            isset($training['company_id'])
                ? (int) $training['company_id']
                : null,

        'cover_letter' =>
            isset($data['cover_letter'])
                ? trim(
                    $data['cover_letter']
                )
                : null,

        'message' =>
            isset($data['message'])
                ? trim(
                    (string) $data['message']
                )
                : null,

        'motivation' =>
            isset($data['motivation'])
                ? trim(
                    $data['motivation']
                )
                : null,

        'full_name' =>
            isset($data['full_name'])
                ? trim(
                    (string) $data['full_name']
                )
                : (
                    $student['full_name']
                    ?? null
                ),

        'email' =>
            isset($data['email'])
                ? trim(
                    (string) $data['email']
                )
                : (
                    $student['user_email']
                    ?? null
                ),

        'phone' =>
            isset($data['phone'])
                ? trim(
                    (string) $data['phone']
                )
                : (
                    $student['phone']
                    ?? null
                ),

        'city' =>
            isset($data['city'])
                ? trim(
                    (string) $data['city']
                )
                : (
                    $student['city']
                    ?? null
                ),

        'address' =>
            isset($data['address'])
                ? trim(
                    (string) $data['address']
                )
                : null,

        'why_interested' =>
            isset($data['why_interested'])
                ? trim(
                    (string) $data['why_interested']
                )
                : null,

        'what_to_learn' =>
            isset($data['what_to_learn'])
                ? trim(
                    (string) $data['what_to_learn']
                )
                : null,

        'skills' =>
            isset($data['skills'])
                ? $data['skills']
                : null,

        'cv_file_id' =>
            $cv_file_id > 0
                ? $cv_file_id
                : null,

        'university' =>
            isset($data['university'])
                ? trim(
                    (string) $data['university']
                )
                : null,

        'faculty_id' =>
            isset($data['faculty_id'])
                ? (int) $data['faculty_id']
                : null,

        'applicant_type' =>
            $applicant_type,

        'academic_year' =>
            isset($data['academic_year'])
                ? trim(
                    (string) $data['academic_year']
                )
                : null,

        'graduation_year' =>
            isset($data['graduation_year'])
                ? (int) $data['graduation_year']
                : null,

        'status' =>
            'pending'

    ];


    /*
    |--------------------------------------------------------------------------
    | Create Application (Transactional)
    |--------------------------------------------------------------------------
    */

    try {

        db_begin_transaction();

        if ($reapplication_id > 0) {

            $application_id = $reapplication_id;

            $reapplied =
                application_repository_reapply(
                    $application_id,
                    $application_data
                );

            if (!$reapplied) {

                db_rollback();

                return [

                    'success' => false,

                    'message' =>
                        'Unable to submit application.',

                    'errors' => [],

                    'status_code' => 500

                ];
            }

            /*
            | A rejected student may have answered the questions before; the
            | re-application replaces those answers with the new submission.
            */

            application_repository_delete_answers(
                $application_id
            );

        } else {

            $application_id =
                application_repository_create(
                    $application_data
                );

            if (!$application_id) {

                db_rollback();

                return [

                    'success' => false,

                    'message' =>
                        'Unable to submit application.',

                    'errors' => [],

                    'status_code' => 500

                ];
            }
        }

        if (!empty($answers)) {

            $answers_saved =
                application_repository_save_answers(
                    (int) $application_id,
                    $answers
                );

            if (!$answers_saved) {

                db_rollback();

                return [

                    'success' => false,

                    'message' =>
                        'Unable to save application answers.',

                    'errors' => [],

                    'status_code' => 500

                ];
            }
        }

        db_commit();

    } catch (Throwable $exception) {

        if (db_in_transaction()) {
            db_rollback();
        }

        return [

            'success' => false,

            'message' =>
                'Unable to submit application.',

            'errors' => [],

            'status_code' => 500

        ];
    }

/*
    |--------------------------------------------------------------------------
    | Get Created Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_id(
            (int) $application_id
        );

    if ($application) {
        $application = application_service_enrich_application(
            $application
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notify Student
    |--------------------------------------------------------------------------
    */

    $notificationService =
        new \NotificationService();


    $notificationService->notifyUser(
        (int) $student['user_id'],
        'Application Submitted',
        'Your application has been submitted successfully.',
        'application',
        [
            'application_id' => (int) $application_id,
            'training_id' => $training_id
        ]
    );


    return [

        'success' => true,

        'message' =>
            'Application submitted successfully.',


        'data' =>
            $application,

        'status_code' => 201

    ];
}


/*
|--------------------------------------------------------------------------
| Enrich Application
|--------------------------------------------------------------------------
|
| Adds answers, the resolved faculty name and a normalized status
| to an application record for API responses. The university is stored
| as free text (university), so it needs no lookup.
|
*/

function application_service_enrich_application(
    array $application
): array {

    $application['status'] =
        ($application['status'] ?? '') === 'submitted'
            ? 'pending'
            : ($application['status'] ?? null);

    if (
        !empty($application['skills'])
        &&
        is_string($application['skills'])
    ) {

        $decoded_skills =
            json_decode(
                $application['skills'],
                true
            );

        $application['skills'] =
            is_array($decoded_skills)
                ? $decoded_skills
                : [];
    }

    $application['answers'] =
        application_repository_get_answers(
            (int) $application['id']
        );

    $application['faculty_name'] = null;

    if (
        !empty($application['faculty_id'])
    ) {

        $faculty =
            application_repository_find_faculty_by_id(
                (int) $application['faculty_id']
            );

        if ($faculty) {
            $application['faculty_name'] =
                $faculty['name'];
        }
    }

    return $application;
}


/*
|--------------------------------------------------------------------------
| Get Application
|--------------------------------------------------------------------------
*/

function application_service_find(
    int $user_id,
    int $application_id,
    ?string $role = null
): array {

    if (
        $application_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid application ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_with_details(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Enrich Application
    |--------------------------------------------------------------------------
    |
    | Attach answers, resolve the faculty name and normalize
    | the status for the response.
    |
    */

    $application = application_service_enrich_application(
        $application
    );


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    if (
        is_admin_role($role)
    ) {

        return [

            'success' => true,

            'message' =>
                'Application retrieved successfully.',

            'data' =>
                $application,

            'status_code' => 200

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Student Access
    |--------------------------------------------------------------------------
    */

    if (
        $role === 'student'
    ) {

        $student =
            application_repository_find_student_by_user_id(
                $user_id
            );


        if (
            !$student
            ||
            (int) $application['student_id']
            !==
            (int) $student['student_id']
        ) {

            return [

                'success' => false,

                'message' =>
                    'You are not allowed to view this application.',

                'errors' => [],

                'status_code' => 403

            ];
        }


        return [

            'success' => true,

            'message' =>
                'Application retrieved successfully.',

            'data' =>
                $application,

            'status_code' => 200

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company Access
    |--------------------------------------------------------------------------
    */

    if (
        $role === 'company'
    ) {

        $company =
            application_repository_find_company_by_user_id(
                $user_id
            );


        if (!$company) {

            return [

                'success' => false,

                'message' =>
                    'Company profile was not found.',

                'errors' => [],

                'status_code' => 404

            ];
        }


        if (
            (int) ($application['training_company_id'] ?? 0)
            !==
            (int) ($company['company_id'] ?? 0)
        ) {

            return [

                'success' => false,

                'message' =>
                    'You are not allowed to view this application.',

                'errors' => [],

                'status_code' => 403

            ];
        }


        return [

            'success' => true,

            'message' =>
                'Application retrieved successfully.',

            'data' =>
                $application,

            'status_code' => 200

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Unknown Role
    |--------------------------------------------------------------------------
    */

    return [

        'success' => false,

        'message' =>
            'You are not allowed to view this application.',

        'errors' => [],

        'status_code' => 403

    ];
}


/*
|--------------------------------------------------------------------------
| Download Application CV
|--------------------------------------------------------------------------
|
| Returns a streamable download payload for the application's CV. Access
| is authorized through the application: the owning student, the company
| that owns the training, or an administrator. This keeps a company from
| reaching arbitrary student files while allowing it to download the CV of
| an applicant to its own training.
|
*/

function application_service_cv(
    int $user_id,
    int $application_id,
    ?string $role = null
): array {

    if (
        $application_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid application ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    $application =
        application_repository_find_with_details(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Authorize
    |--------------------------------------------------------------------------
    */

    $authorized = false;

    if (is_admin_role($role)) {

        $authorized = true;

    } elseif ($role === 'student') {

        $student =
            application_repository_find_student_by_user_id(
                $user_id
            );

        $authorized =
            $student
            &&
            (int) $application['student_id']
            ===
            (int) $student['student_id'];

    } elseif ($role === 'company') {

        $company =
            application_repository_find_company_by_user_id(
                $user_id
            );

        $authorized =
            $company
            &&
            (int) ($application['training_company_id'] ?? 0)
            ===
            (int) ($company['company_id'] ?? 0);
    }

    if (!$authorized) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to access this CV.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve CV File
    |--------------------------------------------------------------------------
    */

    $cv_file_id =
        (int) ($application['cv_file_id'] ?? 0);

    if ($cv_file_id <= 0) {

        return [

            'success' => false,

            'message' =>
                'This application has no CV.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    $file =
        file_repository_find(
            $cv_file_id
        );

    if (!$file) {

        return [

            'success' => false,

            'message' =>
                'CV file was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    $path =
        $file['path']
        ?? $file['storage_path']
        ?? null;

    if (
        !$path
        ||
        !is_file($path)
    ) {

        return [

            'success' => false,

            'message' =>
                'Physical CV file was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    return [

        'success' => true,

        'message' =>
            'CV file ready.',

        'download' =>
            true,

        'path' =>
            $path,

        'filename' =>
            $file['original_name']
            ?? $file['stored_name']
            ?? basename($path),

        'mime_type' =>
            $file['mime_type']
            ?? 'application/octet-stream',

        'size' =>
            (int) ($file['size_bytes'] ?? filesize($path)),

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Student Applications
|--------------------------------------------------------------------------
*/

function application_service_list_student(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Specialization Scoping
    |--------------------------------------------------------------------------
    |
    | When the caller sets scope_to_specialization (the Applied endpoint),
    | results are restricted to trainings whose single primary specialization
    | matches the student's own specialization, mirroring the Trainings List
    | and Saved Trainings behavior. A student with no specialization set gets
    | an empty result set (success, no error).
    |
    */

    $match_specialization_ids = null;

    if (
        !empty($filters['scope_to_specialization'])
    ) {

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student['student_id']
            );

        if ($student_specialization_id === null) {

            return [

                'success' => true,

                'message' =>
                    'Applications retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $match_specialization_ids =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

    $status =
        !empty($filters['status'])
            ? trim(
                $filters['status']
            )
            : null;


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_by_student(
            (int) $student['student_id'],
            $limit,
            $offset,
            $status,
            $match_specialization_ids
        );

    foreach ($items as &$item) {
        $item['status'] =
            ($item['status'] ?? '') === 'submitted'
                ? 'pending'
                : ($item['status'] ?? null);
    }
    unset($item);


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_by_student(
            (int) $student['student_id'],
            $status,
            $match_specialization_ids
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Student Accepted Applications
|--------------------------------------------------------------------------
|
| Returns only the authenticated student's accepted applications, scoped to
| the student's own specialization (the same specialization matching used by
| the Trainings List and the Applied endpoint). A student with no
| specialization set gets an empty result set (success, no error).
|
*/

function application_service_list_accepted(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Specialization Scoping
    |--------------------------------------------------------------------------
    |
    | The Accepted endpoint always scopes results to the student's own
    | specialization. A student with no specialization set gets an empty
    | result set (success, no error).
    |
    */

    $match_specialization_ids = null;

    if (
        !empty($filters['scope_to_specialization'])
    ) {

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student['student_id']
            );

        if ($student_specialization_id === null) {

            return [

                'success' => true,

                'message' =>
                    'Applications retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $match_specialization_ids =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_accepted_by_student(
            (int) $student['student_id'],
            $limit,
            $offset,
            $match_specialization_ids
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_accepted_by_student(
            (int) $student['student_id'],
            $match_specialization_ids
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Student Rejected Applications
|--------------------------------------------------------------------------
|
| Returns only the authenticated student's rejected applications, scoped to
| the student's own specialization (the same specialization matching used by
| the Trainings List, the Applied and the Accepted endpoints). A student with
| no specialization set gets an empty result set (success, no error). The
| items carry only the columns needed to build the Rejected Card DTO.
|
*/

function application_service_list_rejected(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Specialization Scoping
    |--------------------------------------------------------------------------
    |
    | The Rejected endpoint always scopes results to the student's own
    | specialization. A student with no specialization set gets an empty
    | result set (success, no error).
    |
    */

    $match_specialization_ids = null;

    if (
        !empty($filters['scope_to_specialization'])
    ) {

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student['student_id']
            );

        if ($student_specialization_id === null) {

            return [

                'success' => true,

                'message' =>
                    'Applications retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $match_specialization_ids =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_rejected_by_student(
            (int) $student['student_id'],
            $limit,
            $offset,
            $match_specialization_ids
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_rejected_by_student(
            (int) $student['student_id'],
            $match_specialization_ids
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Student Withdrawn Applications
|--------------------------------------------------------------------------
*/

function application_service_list_withdrawn(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Specialization Scoping
    |--------------------------------------------------------------------------
    |
    | The Withdrawn endpoint always scopes results to the student's own
    | specialization. A student with no specialization set gets an empty
    | result set (success, no error).
    |
    */

    $match_specialization_ids = null;

    if (
        !empty($filters['scope_to_specialization'])
    ) {

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student['student_id']
            );

        if ($student_specialization_id === null) {

            return [

                'success' => true,

                'message' =>
                    'Applications retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $match_specialization_ids =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_withdrawn_by_student(
            (int) $student['student_id'],
            $limit,
            $offset,
            $match_specialization_ids
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_withdrawn_by_student(
            (int) $student['student_id'],
            $match_specialization_ids
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Student All Applications
|--------------------------------------------------------------------------
|
| Returns EVERY application of the authenticated student across the four tab
| states (Applied, Accepted, Rejected, Withdrawn) in one unified paginated
| list, scoped to the student's own specialization (the same specialization
| matching used by the Trainings List and all four tab endpoints). A student
| with no specialization set gets an empty result set (success, no error).
|
*/

function application_service_list_all(
    int $user_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Specialization Scoping
    |--------------------------------------------------------------------------
    |
    | The All endpoint always scopes results to the student's own
    | specialization, exactly like the Applied/Accepted/Rejected/Withdrawn
    | tabs. A student with no specialization set gets an empty result set
    | (success, no error).
    |
    */

    $match_specialization_ids = null;

    if (
        !empty($filters['scope_to_specialization'])
    ) {

        $student_specialization_id =
            training_repository_get_student_specialization_id(
                (int) $student['student_id']
            );

        if ($student_specialization_id === null) {

            return [

                'success' => true,

                'message' =>
                    'Applications retrieved successfully.',

                'data' => [

                    'items' => [],

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $limit,

                        'total' => 0,

                        'total_pages' => 0,

                        'has_next_page' => false,

                        'has_previous_page' => false

                    ]

                ],

                'status_code' => 200

            ];
        }


        $match_specialization_ids =
            [$student_specialization_id];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_all_by_student(
            (int) $student['student_id'],
            $limit,
            $offset,
            $match_specialization_ids
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_all_by_student(
            (int) $student['student_id'],
            $match_specialization_ids
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| List Company Applications
|--------------------------------------------------------------------------
*/

function application_service_list_company(
    int $user_id,
    int $training_id,
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        application_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Training Ownership
    |--------------------------------------------------------------------------
    */

    $training =
        training_repository_find_by_id(
            $training_id
        );


    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to view applications for this training.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $page =
        isset($filters['page'])
            ? (int) $filters['page']
            : 1;


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 20;


    if (
        $page < 1
    ) {

        $page = 1;
    }


    if (
        $limit < 1
    ) {

        $limit = 20;
    }


    if (
        $limit > 100
    ) {

        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $status =
        !empty($filters['status'])
            ? trim(
                $filters['status']
            )
            : null;


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $items =
        application_repository_get_by_training_paginated(
            $training_id,
            $limit,
            $offset,
            $status
        );

    foreach ($items as &$item) {
        $item['status'] =
            ($item['status'] ?? '') === 'submitted'
                ? 'pending'
                : ($item['status'] ?? null);
    }
    unset($item);


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_by_training(
            $training_id,
            $status
        );


    $total_pages =
        $limit > 0
            ? (int) ceil(
                $total / $limit
            )
            : 0;


    return [

        'success' => true,

        'message' =>
            'Training applications retrieved successfully.',

        'data' => [

            'items' =>
                $items,

            'pagination' => [

                'current_page' =>
                    $page,

                'per_page' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

                'has_next_page' =>
                    $page < $total_pages,

                'has_previous_page' =>
                    $page > 1

            ]

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Accept Application
|--------------------------------------------------------------------------
|
| Company accepts a student's application.
|
*/

function application_service_accept(
    int $user_id,
    int $application_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        application_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    |
    | The application row has no company_id; ownership is resolved through
    | the training that owns the application (Application → Training →
    | Company). A company can never manage another company's application.
    |
    */

    $training =
        training_repository_find_by_id(
            (int) $application['training_id']
        );

    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to manage this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    |
    | The database stores submitted; the API exposes it as pending. Both
    | representations are accepted when checking the transition source.
    |
    */

    $current_status =
        strtolower(
            (string) (
                $application['status']
                ?? ''
            )
        );

    if (
        !in_array(
            $current_status,
            ['submitted', 'pending'],
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Only pending applications can be accepted.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Training Capacity
    |--------------------------------------------------------------------------
    */

    if (
        isset($training['capacity'])
        &&
        $training['capacity'] !== null
    ) {

        $capacity =
            (int) $training['capacity'];

        if (
            $capacity > 0
        ) {

            $accepted_count =
                application_repository_count_accepted(
                    (int) $application['training_id']
                );

            if (
                $accepted_count >= $capacity
            ) {

                return [

                    'success' => false,

                    'message' =>
                        'Training capacity has been reached.',

                    'errors' => [],

                    'status_code' => 409

                ];
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Accept
    |--------------------------------------------------------------------------
    */

    $accepted =
        application_repository_update_status(
            $application_id,
            'accepted',
            $user_id
        );


    if (
        !$accepted
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to accept application.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Application
    |--------------------------------------------------------------------------
    */

    $updated =
        application_repository_find_by_id(
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Notify Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_id(
            (int) $application['student_id']
        );

    $notificationService =
        new \NotificationService();


    $notificationService->notifyUser(
        (int) ($student['user_id'] ?? 0),
        'Application Accepted',
        'Your application has been accepted for the training opportunity.',
        'application',
        [
            'application_id' => $application_id,
            'training_id' => $application['training_id']
        ]
    );


    return [

        'success' => true,

        'message' =>
            'Application accepted successfully.',


        'data' =>
            $updated,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Confirm Manual Payment
|--------------------------------------------------------------------------
|
| Company confirms that the manual (bank transfer) payment for an accepted
| paid training has been received. This endpoint is the only writer of the
| manual-payment lifecycle state:
|
|     free training              -> NOT_REQUIRED (no payment, nothing to do)
|     paid training, not paid    -> PENDING (trial still running)
|     paid + company confirms    -> PAID
|
| The application status is NEVER changed here: a student who used up their
| trial without paying is blocked from continuing by the session/trial logic
| and is never auto-withdrawn by the payment machine.
|
| Authorization (all enforced): company-only, the owning company of the
| training, the application must already be accepted, and the training must
| be paid. A second confirm for the same application is idempotent: it
| returns the existing paid payment without creating a duplicate row.
|
*/

function application_service_confirm_payment(
    int $user_id,
    int $application_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        application_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_with_details(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Accepted Only
    |--------------------------------------------------------------------------
    */

    if (
        strtolower(
            (string) ($application['status'] ?? '')
        )
        !== 'accepted'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only accepted applications can be confirmed for payment.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($application['training_company_id'] ?? 0)
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to confirm payment for this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    $training_id =
        (int) ($application['training_id'] ?? 0);

    $student_id =
        (int) ($application['student_id'] ?? 0);


    if (
        $training_id <= 0
        ||
        $student_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Application is missing training or student information.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Paid Training Only
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($application['training_is_paid'] ?? 0)
        !== 1
    ) {

        return [

            'success' => false,

            'message' =>
                'This training does not require payment.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Existing Payment (Idempotency)
    |--------------------------------------------------------------------------
    */

    $existing =
        application_repository_find_payment_for_application(
            $training_id,
            $student_id
        );


    if (
        $existing !== null
        &&
        (string) ($existing['status'] ?? '')
        === 'paid'
    ) {

        return [

            'success' => true,

            'message' =>
                'Payment already confirmed.',

            'data' => [

                'application_id' =>
                    $application_id,

                'training_id' =>
                    $training_id,

                'payment_id' =>
                    (int) ($existing['id'] ?? 0),

                'status' =>
                    (string) ($existing['status'] ?? 'paid'),

                'paid_at' =>
                    $existing['paid_at'] ?? null,

                'already_confirmed' =>
                    true

            ],

            'status_code' => 200

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Fee Validation
    |--------------------------------------------------------------------------
    */

    $amount =
        (float) ($application['training_compensation_amount'] ?? 0);


    if ($amount <= 0) {

        return [

            'success' => false,

            'message' =>
                'This training has no fee set.',

            'errors' => [

                'training_id' =>
                    'The paid training fee is not configured.'

            ],

            'status_code' => 422

        ];
    }

    $currency =
        strtoupper(
            trim(
                (string) (
                    $application['training_compensation_currency']
                    ?? 'EGP'
                )
            )
        );

    if ($currency === '') {
        $currency = 'EGP';
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm (Transaction)
    |--------------------------------------------------------------------------
    |
    | A second confirmation on a row that exists but is NOT paid simply
    | promotes that row to paid. When no row exists yet it is created
    | directly as paid (manual = the company received the transfer).
    |
    */

    db_begin_transaction();

    try {

        if ($existing !== null) {

            $confirmed =
                application_repository_confirm_payment(
                    (int) $existing['id']
                );

        } else {

            $payment_payload = [

                'training_id' =>
                    $training_id,

                'training_session_id' =>
                    null,

                'student_id' =>
                    $student_id,

                'company_id' =>
                    (int) $company['company_id'],

                'amount' =>
                    round($amount, 2),

                'currency' =>
                    $currency,

                'platform_commission_rate' =>
                    0.00,

                'platform_commission_amount' =>
                    0.00,

                'company_amount' =>
                    round($amount, 2),

                'payment_method' =>
                    'manual',

                'status' =>
                    'paid',

                'paid_at' =>
                    date('Y-m-d H:i:s')

            ];

            $payment_id =
                application_repository_create_payment(
                    $payment_payload
                );

            $confirmed =
                $payment_id > 0
                && $payment_id !== false;
        }


        if (!$confirmed) {

            db_rollback();

            return [

                'success' => false,

                'message' =>
                    'Unable to confirm payment.',

                'errors' => [],

                'status_code' => 500

            ];
        }

        $payment =
            application_repository_find_payment_for_application(
                $training_id,
                $student_id
            );

    } catch (Throwable $exception) {

        db_rollback();

        return [

            'success' => false,

            'message' =>
                'Unable to confirm payment.',

            'errors' => [],

            'status_code' => 500

        ];
    }

    db_commit();


    return [

        'success' => true,

        'message' =>
            'Payment confirmed successfully.',

        'data' => [

            'application_id' =>
                $application_id,

            'training_id' =>
                $training_id,

            'payment_id' =>
                (int) ($payment['id'] ?? 0),

            'status' =>
                (string) ($payment['status'] ?? 'paid'),

            'payment_method' =>
                (string) ($payment['payment_method'] ?? 'manual'),

            'amount' =>
                (float) ($payment['amount'] ?? 0),

            'currency' =>
                (string) ($payment['currency'] ?? $currency),

            'paid_at' =>
                $payment['paid_at'] ?? null,

            'already_confirmed' =>
                false

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Submit Payment Reference
|--------------------------------------------------------------------------
|
| Student side of the manual (bank transfer) lifecycle for a paid accepted
| training. After transferring the fee to the owning company's bank account,
| the student submits the transfer reference on the application. This creates
| the payments row as status 'pending' (or updates the reference on an
| existing pending row) and NEVER marks it paid — only the owning company's
| confirm endpoint promotes pending to paid.
|
| Authorization (all enforced): student-only (the acting user resolves to a
| student profile), the application must belong to that student, it must be
| accepted, and the training must be paid with a configured fee. The write is
| idempotent: submitting the same or a new reference for a pending row updates
| that single row — a refresh never duplicates it. Once the row is paid, the
| reference can no longer be changed.
|
*/

function application_service_submit_payment_reference(
    int $user_id,
    int $application_id,
    string $reference
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reference
    |--------------------------------------------------------------------------
    */

    $reference = trim($reference);


    if ($reference === '') {

        return [

            'success' => false,

            'message' =>
                'A payment reference is required.',

            'errors' => [

                'reference' =>
                    'A payment reference is required.'

            ],

            'status_code' => 422

        ];
    }


    if (mb_strlen($reference) > 255) {

        return [

            'success' => false,

            'message' =>
                'The payment reference cannot exceed 255 characters.',

            'errors' => [

                'reference' =>
                    'The payment reference cannot exceed 255 characters.'

            ],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_with_details(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Accepted Only
    |--------------------------------------------------------------------------
    */

    if (
        strtolower(
            (string) ($application['status'] ?? '')
        )
        !== 'accepted'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only accepted applications can submit a payment reference.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($application['student_id'] ?? 0)
        !==
        (int) $student['student_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to submit a payment reference for this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    $training_id =
        (int) ($application['training_id'] ?? 0);


    if ($training_id <= 0) {

        return [

            'success' => false,

            'message' =>
                'Application is missing training information.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Paid Training Only
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($application['training_is_paid'] ?? 0)
        !== 1
    ) {

        return [

            'success' => false,

            'message' =>
                'This training does not require payment.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Fee Validation
    |--------------------------------------------------------------------------
    */

    $amount =
        (float) ($application['training_compensation_amount'] ?? 0);


    if ($amount <= 0) {

        return [

            'success' => false,

            'message' =>
                'This training has no fee set.',

            'errors' => [

                'training_id' =>
                    'The paid training fee is not configured.'

            ],

            'status_code' => 422

        ];
    }

    $currency =
        strtoupper(
            trim(
                (string) (
                    $application['training_compensation_currency']
                    ?? 'EGP'
                )
            )
        );

    if ($currency === '') {
        $currency = 'EGP';
    }


    /*
    |--------------------------------------------------------------------------
    | Existing Payment (Idempotency)
    |--------------------------------------------------------------------------
    */

    $existing =
        application_repository_find_payment_for_application(
            $training_id,
            (int) $student['student_id']
        );


    if (
        $existing !== null
        &&
        (string) ($existing['status'] ?? '')
        === 'paid'
    ) {

        return [

            'success' => false,

            'message' =>
                'Payment has already been confirmed.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Submit (Transaction)
    |--------------------------------------------------------------------------
    |
    | No payment row yet -> create one as pending with the reference. A row
    | that exists but is pending -> update its reference only. Both paths run
    | in one transaction and leave the row pending until the company confirms.
    |
    */

    db_begin_transaction();

    try {

        if ($existing !== null) {

            $updated =
                application_repository_update_payment_reference(
                    (int) $existing['id'],
                    $reference
                );

        } else {

            $payment_payload = [

                'training_id' =>
                    $training_id,

                'training_session_id' =>
                    null,

                'student_id' =>
                    (int) $student['student_id'],

                'company_id' =>
                    (int) ($application['training_company_id'] ?? 0),

                'amount' =>
                    round($amount, 2),

                'currency' =>
                    $currency,

                'platform_commission_rate' =>
                    0.00,

                'platform_commission_amount' =>
                    0.00,

                'company_amount' =>
                    round($amount, 2),

                'payment_method' =>
                    'manual',

                'status' =>
                    'pending',

                'external_reference' =>
                    $reference,

                'paid_at' =>
                    null

            ];

            $payment_id =
                application_repository_create_payment(
                    $payment_payload
                );

            $updated =
                $payment_id > 0
                && $payment_id !== false;
        }


        if (!$updated) {

            db_rollback();

            return [

                'success' => false,

                'message' =>
                    'Unable to submit payment reference.',

                'errors' => [],

                'status_code' => 500

            ];
        }

        $payment =
            application_repository_find_payment_for_application(
                $training_id,
                (int) $student['student_id']
            );

    } catch (Throwable $exception) {

        db_rollback();

        return [

            'success' => false,

            'message' =>
                'Unable to submit payment reference.',

            'errors' => [],

            'status_code' => 500

        ];
    }

    db_commit();


    return [

        'success' => true,

        'message' =>
            'Payment reference submitted successfully. Payment is pending verification.',

        'data' => [

            'application_id' =>
                $application_id,

            'training_id' =>
                $training_id,

            'payment_id' =>
                (int) ($payment['id'] ?? 0),

            'status' =>
                (string) ($payment['status'] ?? 'pending'),

            'payment_method' =>
                (string) ($payment['payment_method'] ?? 'manual'),

            'reference' =>
                isset($payment['external_reference'])
                && trim((string) $payment['external_reference']) !== ''
                    ? (string) $payment['external_reference']
                    : null,

            'amount' =>
                (float) ($payment['amount'] ?? $amount),

            'currency' =>
                (string) ($payment['currency'] ?? $currency),

            'paid_at' =>
                $payment['paid_at'] ?? null,

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Show Payment
|--------------------------------------------------------------------------
|
| Student reads the manual payment lifecycle state for one of their
| accepted paid applications. Returns the latest payments row for the
| application's training when one exists (pending or paid); before the
| student submits any reference the row does not exist yet and the state
| is reported as pending with no reference, matching the Accepted ledger.
|
*/

function application_service_show_payment(
    int $user_id,
    int $application_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_with_details(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($application['student_id'] ?? 0)
        !==
        (int) $student['student_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to view this payment.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    $training_id =
        (int) ($application['training_id'] ?? 0);


    if ($training_id <= 0) {

        return [

            'success' => false,

            'message' =>
                'Application is missing training information.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Row
    |--------------------------------------------------------------------------
    */

    $payment =
        application_repository_find_payment_for_application(
            $training_id,
            (int) $student['student_id']
        );


    return [

        'success' => true,

        'message' =>
            'Payment retrieved successfully.',

        'data' => [

            'application_id' =>
                $application_id,

            'training_id' =>
                $training_id,

            'payment_id' =>
                $payment !== null
                    ? (int) ($payment['id'] ?? 0)
                    : 0,

            'status' =>
                $payment !== null
                    ? (string) ($payment['status'] ?? 'pending')
                    : 'pending',

            'payment_method' =>
                $payment !== null
                    ? (string) ($payment['payment_method'] ?? 'manual')
                    : 'manual',

            'reference' =>
                $payment !== null
                && isset($payment['external_reference'])
                && trim((string) $payment['external_reference']) !== ''
                    ? (string) $payment['external_reference']
                    : null,

            'amount' =>
                $payment !== null
                    ? (float) ($payment['amount'] ?? 0)
                    : 0.0,

            'currency' =>
                $payment !== null
                    ? (string) ($payment['currency'] ?? 'EGP')
                    : 'EGP',

            'paid_at' =>
                $payment !== null
                    ? ($payment['paid_at'] ?? null)
                    : null,

            'submitted' =>
                $payment !== null,

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Reject Application
|--------------------------------------------------------------------------
*/

function application_service_reject(
    int $user_id,
    int $application_id,
    array $data = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        application_repository_find_company_by_user_id(
            $user_id
        );


    if (!$company) {

        return [

            'success' => false,

            'message' =>
                'Company profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    |
    | Ownership is resolved through the owning training (Application →
    | Training → Company).
    |
    */

    $training =
        training_repository_find_by_id(
            (int) $application['training_id']
        );

    if (!$training) {

        return [

            'success' => false,

            'message' =>
                'Training opportunity not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to manage this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    |
    | The database stores submitted; the API exposes it as pending. Both
    | representations are accepted when checking the transition source.
    |
    */

    $current_status =
        strtolower(
            (string) (
                $application['status']
                ?? ''
            )
        );

    if (
        !in_array(
            $current_status,
            ['submitted', 'pending'],
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Only pending applications can be rejected.',

            'errors' => [],

            'status_code' => 409

        ];
    }


/*
    |--------------------------------------------------------------------------
    | Allowed Rejection Reasons
    |--------------------------------------------------------------------------
    */

    $allowedReasons = [
        'Candidate did not meet minimum requirements',
        'Position already filled',
        'Insufficient capacity in training',
        'Application incomplete',
        'Candidate withdrew consideration',
        'Training program discontinued'
    ];


/*
    |--------------------------------------------------------------------------
    | Validate Rejection Reason
    |--------------------------------------------------------------------------
    */

    if (
        !isset($data['rejection_reason'])
        ||
        trim($data['rejection_reason']) === ''
    ) {

        return [

            'success' => false,

            'message' =>
                'Rejection reason is required.',


            'errors' => [

                'rejection_reason' =>
                    'Rejection reason is required.'

            ],

            'status_code' => 422

        ];
    }

    $reason = trim($data['rejection_reason']);

    if (
        !in_array(
            $reason,
            $allowedReasons,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid rejection reason.',


            'errors' => [

                'rejection_reason' =>
                    'Rejection reason must be one of the preset values.'

            ],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    $rejected =
        application_repository_reject(
            $application_id,
            $user_id,
            $reason,
            trim((string) ($data['rejection_note'] ?? '')) ?: null
        );


    if (
        !$rejected
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to reject application.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Application
    |--------------------------------------------------------------------------
    */

    $updated =
        application_repository_find_by_id(
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Notify Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_id(
            (int) $application['student_id']
        );

    $notificationService =
        new \NotificationService();


    $notificationService->notifyUser(
        (int) ($student['user_id'] ?? 0),
        'Application Rejected',
        'Your application has been rejected for the training opportunity.',
        'application',
        [
            'application_id' => $application_id,
            'training_id' => $application['training_id'],
            'rejection_reason' => $reason
        ]
    );


    return [

        'success' => true,

        'message' =>
            'Application rejected successfully.',


        'data' =>
            $updated,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Withdraw Application
|--------------------------------------------------------------------------
|
| Student withdraws their pending application.
|
*/

function application_service_withdraw(
    int $user_id,
    int $application_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $application['student_id']
        !==
        (int) $student['student_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to withdraw this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Pending
    |--------------------------------------------------------------------------
    */

    $current_status =
        strtolower(
            (string) (
                $application['status']
                ?? ''
            )
        );

    if (
        !in_array(
            $current_status,
            ['submitted', 'pending'],
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Only pending applications can be withdrawn.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Withdraw
    |--------------------------------------------------------------------------
    */

    $withdrawn =
        application_repository_withdraw(
            $application_id
        );


    if (
        !$withdrawn
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to withdraw application.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Application
    |--------------------------------------------------------------------------
    */
$updated =
        application_repository_find_by_id(
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Notify Student
    |--------------------------------------------------------------------------
    */

    $notificationService =
        new \NotificationService();


    $notificationService->notifyUser(
        (int) $student['user_id'],
        'Application Withdrawn',
        'Your application has been withdrawn.',
        'application',
        [
            'application_id' => $application_id,
            'training_id' => $application['training_id']
        ]
    );


    return [

        'success' => true,

        'message' =>
            'Application withdrawn successfully.',


        'data' => [

            'id' =>
                (int) ($updated['id'] ?? $application_id),

            'status' =>
                (string) ($updated['status'] ?? 'withdrawn')

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Update Application
|--------------------------------------------------------------------------
|
| Allows the student to update a pending application.
|
*/

function application_service_update(
    int $user_id,
    int $application_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {

        return [

            'success' => false,

            'message' =>
                'Application not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $application['student_id']
        !==
        (int) $student['student_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to update this application.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Pending
    |--------------------------------------------------------------------------
    */

    $current_status =
        strtolower(
            (string) (
                $application['status']
                ?? ''
            )
        );

    if (
        !in_array(
            $current_status,
            ['submitted', 'pending'],
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Only pending applications can be updated.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $validation =
        application_validator_update(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Application data is invalid.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Fields
    |--------------------------------------------------------------------------
    */

    $update_data = [];


    if (
        array_key_exists(
            'cover_letter',
            $data
        )
    ) {

        $update_data['cover_letter'] =
            $data['cover_letter'] !== null
                ? trim(
                    (string)
                    $data['cover_letter']
                )
                : null;
    }


    if (
        array_key_exists(
            'cv_file_id',
            $data
        )
    ) {

        $update_data['cv_file_id'] =
            $data['cv_file_id'] !== null
                ? (int) $data['cv_file_id']
                : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Nothing To Update
    |--------------------------------------------------------------------------
    */

    if (
        empty($update_data)
    ) {

        return [

            'success' => false,

            'message' =>
                'No application data was provided for update.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $updated =
        application_repository_update(
            $application_id,
            $update_data
        );


    if (
        !$updated
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to update application.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Application
    |--------------------------------------------------------------------------
    */

    $result =
        application_repository_find_by_id(
            $application_id
        );


    return [

        'success' => true,

        'message' =>
            'Application updated successfully.',

        'data' =>
            $result,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Check If Student Applied
|--------------------------------------------------------------------------
*/

function application_service_check(
    int $user_id,
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $student =
        application_repository_find_student_by_user_id(
            $user_id
        );


    if (!$student) {

        return [

            'success' => false,

            'message' =>
                'Student profile was not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Application
    |--------------------------------------------------------------------------
    */

    $application =
        application_repository_find_by_student_and_training(
            (int) $student['student_id'],
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            'Application status retrieved successfully.',

        'data' => [

            'applied' =>
                $application !== null,

            'application' =>
                $application

        ],

        'status_code' => 200

    ];
}
