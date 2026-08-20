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
    */

    if (
        !empty($data['university_id'])
    ) {

        $university =
            application_repository_find_university_by_id(
                (int) $data['university_id']
            );

        if (!$university) {

            return [

                'success' => false,

                'message' =>
                    'Selected university was not found.',

                'errors' => [

                    'university_id' =>
                        'Selected university was not found.'

                ],

                'status_code' => 422

            ];
        }
    }

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
    */

    $questions =
        training_repository_get_questions(
            $training_id
        );

    $answers =
        $data['answers']
        ?? [];

    if (
        !is_array($answers)
    ) {

        $answers = [];
    }

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


    /*
    |--------------------------------------------------------------------------
    | Prepare Application
    |--------------------------------------------------------------------------
    */

    $application_data = [

        'training_id' =>
            $training_id,

        'student_id' =>
            (int) $student['student_id'],

        'cover_letter' =>
            isset($data['cover_letter'])
                ? trim(
                    $data['cover_letter']
                )
                : null,

        'motivation' =>
            isset($data['motivation'])
                ? trim(
                    $data['motivation']
                )
                : null,

        'cv_file_id' =>
            isset($data['cv_file_id'])
                ? (int) $data['cv_file_id']
                : null,

        'university_id' =>
            isset($data['university_id'])
                ? (int) $data['university_id']
                : null,

        'faculty_id' =>
            isset($data['faculty_id'])
                ? (int) $data['faculty_id']
                : null,

        'applicant_type' =>
            isset($data['applicant_type'])
                ? strtolower(
                    trim(
                        (string) $data['applicant_type']
                    )
                )
                : 'student',

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
| Adds answers, university/faculty names and a normalized status
| to an application record for API responses.
|
*/

function application_service_enrich_application(
    array $application
): array {

    $application['status'] =
        ($application['status'] ?? '') === 'submitted'
            ? 'pending'
            : ($application['status'] ?? null);

    $application['answers'] =
        application_repository_get_answers(
            (int) $application['id']
        );

    $application['university_name'] = null;
    $application['faculty_name'] = null;

    if (
        !empty($application['university_id'])
    ) {

        $university =
            application_repository_find_university_by_id(
                (int) $application['university_id']
            );

        if ($university) {
            $application['university_name'] =
                $university['name'];
        }
    }

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
    | Attach answers, university/faculty names and normalize
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
        $role === 'admin'
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
            $status
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        application_repository_count_by_student(
            (int) $student['student_id'],
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
    */

    if (
        (int) $application['company_id']
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
    */

    if (
        isset($application['status'])
        &&
        $application['status'] !== 'pending'
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
        isset($application['capacity'])
        &&
        $application['capacity'] !== null
    ) {

        $capacity =
            (int) $application['capacity'];


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
            'accepted'
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
/*

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
        (int) $application['student_user_id'],
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
    */

    if (
        (int) $application['company_id']
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
    */

    if (
        isset($application['status'])
        &&
        $application['status'] !== 'pending'
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
            $reason
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

    $notificationService =
        new \NotificationService();


    $notificationService->notifyUser(
        (int) $application['student_user_id'],
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

    if (
        isset($application['status'])
        &&
        $application['status'] !== 'pending'
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
        application_repository_update_status(
            $application_id,
            'withdrawn'
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


        'data' =>
            $updated,

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

    if (
        isset($application['status'])
        &&
        $application['status'] !== 'pending'
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
