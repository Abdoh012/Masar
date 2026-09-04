<?php

/**
 * MASAR - Training Session Service
 *
 * Business logic for training sessions.
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

require_once __DIR__ . '/../repositories/training_session_repository.php';
require_once __DIR__ . '/../repositories/training_repository.php';
require_once __DIR__ . '/../repositories/application_repository.php';

require_once __DIR__ . '/../validators/training_validator.php';

require_once __DIR__ . '/../../../core/database/transaction.php';


/*
|--------------------------------------------------------------------------
| Create Training Session
|--------------------------------------------------------------------------
|
| A company can create a session for one of its training
| opportunities.
|
*/

function training_session_service_create(
    int $user_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Basic Data
    |--------------------------------------------------------------------------
    */

    $training_id =
        isset($data['training_id'])
            ? (int) $data['training_id']
            : 0;


    if (
        $training_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'A valid training ID is required.',

            'errors' => [

                'training_id' =>
                    'Training ID is required.'

            ],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
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
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to create sessions for this training.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Training Status
    |--------------------------------------------------------------------------
    */

    $allowed_statuses = [

        'published',

        'open',

        'active'

    ];


    if (
        isset($training['status'])
        &&
        !in_array(
            $training['status'],
            $allowed_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'Sessions cannot be created for this training at its current status.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Session Data
    |--------------------------------------------------------------------------
    */

    $validation =
        training_session_service_validate_data(
            $data
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Training session data is invalid.',

            'errors' =>
                $validation['errors'],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Session
    |--------------------------------------------------------------------------
    */

    $existing =
        training_session_repository_find_duplicate(
            $training_id,
            $data['session_date'] ?? null,
            $data['start_time'] ?? null
        );


    if ($existing) {

        return [

            'success' => false,

            'message' =>
                'A training session already exists at this date and time.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Data
    |--------------------------------------------------------------------------
    */

    $session_data = [

        'training_id' =>
            $training_id,

        'session_date' =>
            $data['session_date'] ?? null,

        'start_time' =>
            $data['start_time'] ?? null,

        'end_time' =>
            $data['end_time'] ?? null,

        'title' =>
            isset($data['title'])
                ? trim(
                    (string)
                    $data['title']
                )
                : null,

        'description' =>
            isset($data['description'])
                ? trim(
                    (string)
                    $data['description']
                )
                : null,

        'location' =>
            isset($data['location'])
                ? trim(
                    (string)
                    $data['location']
                )
                : null,

        'meeting_url' =>
            isset($data['meeting_url'])
                ? trim(
                    (string)
                    $data['meeting_url']
                )
                : null,

        'status' =>
            'scheduled',

        'trial_start_date' =>
            isset($data['trial_start_date'])
                ? $data['trial_start_date']
                : null,

        'trial_end_date' =>
            isset($data['trial_end_date'])
                ? $data['trial_end_date']
                : null

    ];


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    $session_id =
        training_session_repository_create(
            $session_data
        );


    if (
        !$session_id
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to create training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Created Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            (int) $session_id
        );


    return [

        'success' => true,

        'message' =>
            'Training session created successfully.',

        'data' =>
            $session,

        'status_code' => 201

    ];
}


/*
|--------------------------------------------------------------------------
| Find Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_find(
    int $session_id,
    int $user_id,
    ?string $role = null
): array {

    if (
        $session_id <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'Invalid training session ID.',

            'errors' => [],

            'status_code' => 422

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Access
    |--------------------------------------------------------------------------
    */

    if (
        $role === 'admin'
    ) {

        return [

            'success' => true,

            'message' =>
                'Training session retrieved successfully.',

            'data' =>
                $session,

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
            training_session_repository_find_student_by_user_id(
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
        | Student Must Have Accepted Application
        |--------------------------------------------------------------------------
        */

        $application =
            training_session_repository_find_accepted_application(
                (int) $student['student_id'],
                (int) $session['training_id']
            );


        if (!$application) {

            return [

                'success' => false,

                'message' =>
                    'You are not enrolled in this training.',

                'errors' => [],

                'status_code' => 403

            ];
        }


        return [

            'success' => true,

            'message' =>
                'Training session retrieved successfully.',

            'data' =>
                $session,

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
            training_session_repository_find_company_by_user_id(
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
            isset($session['company_id'])
            &&
            (int) $session['company_id']
            !==
            (int) $company['company_id']
        ) {

            return [

                'success' => false,

                'message' =>
                    'You are not allowed to view this session.',

                'errors' => [],

                'status_code' => 403

            ];
        }


        return [

            'success' => true,

            'message' =>
                'Training session retrieved successfully.',

            'data' =>
                $session,

            'status_code' => 200

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Public Access
    |--------------------------------------------------------------------------
    */

    if (
        !$role
    ) {

        if (
            isset($session['status'])
            &&
            in_array(
                $session['status'],
                [
                    'cancelled',
                    'deleted'
                ],
                true
            )
        ) {

            return [

                'success' => false,

                'message' =>
                    'Training session is not available.',

                'errors' => [],

                'status_code' => 404

            ];
        }


        return [

            'success' => true,

            'message' =>
                'Training session retrieved successfully.',

            'data' =>
                $session,

            'status_code' => 200

        ];
    }


    return [

        'success' => false,

        'message' =>
            'You are not allowed to view this session.',

        'errors' => [],

        'status_code' => 403

    ];
}


/*
|--------------------------------------------------------------------------
| List Training Sessions
|--------------------------------------------------------------------------
*/

function training_session_service_list(
    int $training_id,
    int $user_id,
    ?string $role = null,
    int $page = 1,
    int $limit = 20
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Training
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
    | Access Control
    |--------------------------------------------------------------------------
    */

    if (
        $role === 'company'
    ) {

        $company =
            training_session_repository_find_company_by_user_id(
                $user_id
            );


        if (
            !$company
            ||
            (int) $training['company_id']
            !==
            (int) $company['company_id']
        ) {

            return [

                'success' => false,

                'message' =>
                    'You are not allowed to view these sessions.',

                'errors' => [],

                'status_code' => 403

            ];
        }
    }


    if (
        $role === 'student'
    ) {

        $student =
            training_session_repository_find_student_by_user_id(
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


        $application =
            training_session_repository_find_accepted_application(
                (int) $student['student_id'],
                $training_id
            );


        if (!$application) {

            return [

                'success' => false,

                'message' =>
                    'You are not enrolled in this training.',

                'errors' => [],

                'status_code' => 403

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

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
    | Get Sessions
    |--------------------------------------------------------------------------
    */

    $items =
        training_session_repository_get_training_sessions(
            $training_id,
            $limit,
            $offset
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        training_session_repository_count_training_sessions(
            $training_id
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
            'Training sessions retrieved successfully.',

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
| Update Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_update(
    int $user_id,
    int $session_id,
    array $data
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
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
    | Ownership
    |--------------------------------------------------------------------------
    */

    if (
        isset($session['company_id'])
        &&
        (int) $session['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to update this session.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Cannot Update Completed / Cancelled
    |--------------------------------------------------------------------------
    */

    $locked_statuses = [

        'completed',

        'cancelled',

        'deleted'

    ];


    if (
        isset($session['status'])
        &&
        in_array(
            $session['status'],
            $locked_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training session can no longer be updated.',

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
        training_session_service_validate_data(
            $data,
            true
        );


    if (
        !$validation['valid']
    ) {

        return [

            'success' => false,

            'message' =>
                'Training session data is invalid.',

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

    $allowed_fields = [

        'session_date',

        'start_time',

        'end_time',

        'title',

        'description',

        'location',

        'meeting_url'

    ];


    $update_data = [];


    foreach (
        $allowed_fields as $field
    ) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $update_data[$field] =
                $data[$field];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Strings
    |--------------------------------------------------------------------------
    */

    $string_fields = [

        'title',

        'description',

        'location',

        'meeting_url'

    ];


    foreach (
        $string_fields as $field
    ) {

        if (
            array_key_exists(
                $field,
                $update_data
            )
            &&
            $update_data[$field] !== null
        ) {

            $update_data[$field] =
                trim(
                    (string)
                    $update_data[$field]
                );
        }
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
                'No session data was provided for update.',

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
        training_session_repository_update(
            $session_id,
            $update_data
        );


    if (
        !$updated
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to update training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated
    |--------------------------------------------------------------------------
    */

    $result =
        training_session_repository_find_by_id(
            $session_id
        );


    return [

        'success' => true,

        'message' =>
            'Training session updated successfully.',

        'data' =>
            $result,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Start Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_start(
    int $user_id,
    int $session_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
            $user_id
        );


    if (
        !$company
        ||
        (
            isset($session['company_id'])
            &&
            (int) $session['company_id']
            !==
            (int) $company['company_id']
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to start this session.',

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
        isset($session['status'])
        &&
        $session['status'] !== 'scheduled'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only scheduled sessions can be started.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Start
    |--------------------------------------------------------------------------
    */

    $started =
        training_session_repository_start(
            $session_id
        );


    if (
        !$started
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to start training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated
    |--------------------------------------------------------------------------
    */

    $updated =
        training_session_repository_find_by_id(
            $session_id
        );


    return [

        'success' => true,

        'message' =>
            'Training session started successfully.',

        'data' =>
            $updated,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Complete Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_complete(
    int $user_id,
    int $session_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
            $user_id
        );


    if (
        !$company
        ||
        (
            isset($session['company_id'])
            &&
            (int) $session['company_id']
            !==
            (int) $company['company_id']
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to complete this session.',

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
        isset($session['status'])
        &&
        $session['status'] !== 'in_progress'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only sessions in progress can be completed.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Complete
    |--------------------------------------------------------------------------
    */

    $completed =
        training_session_repository_complete(
            $session_id
        );


    if (
        !$completed
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to complete training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated
    |--------------------------------------------------------------------------
    */

    $updated =
        training_session_repository_find_by_id(
            $session_id
        );


    return [

        'success' => true,

        'message' =>
            'Training session completed successfully.',

        'data' =>
            $updated,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Cancel Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_cancel(
    int $user_id,
    int $session_id,
    array $data = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
            $user_id
        );


    if (
        !$company
        ||
        (
            isset($session['company_id'])
            &&
            (int) $session['company_id']
            !==
            (int) $company['company_id']
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to cancel this session.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Already Finished
    |--------------------------------------------------------------------------
    */

    $locked_statuses = [

        'completed',

        'cancelled',

        'deleted'

    ];


    if (
        isset($session['status'])
        &&
        in_array(
            $session['status'],
            $locked_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training session can no longer be cancelled.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Cancellation Reason
    |--------------------------------------------------------------------------
    */

    $reason =
        isset($data['cancellation_reason'])
            ? trim(
                (string)
                $data['cancellation_reason']
            )
            : null;


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    $cancelled =
        training_session_repository_cancel(
            $session_id,
            $reason
        );


    if (
        !$cancelled
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to cancel training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return Updated
    |--------------------------------------------------------------------------
    */

    $updated =
        training_session_repository_find_by_id(
            $session_id
        );


    return [

        'success' => true,

        'message' =>
            'Training session cancelled successfully.',

        'data' =>
            $updated,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Delete Training Session
|--------------------------------------------------------------------------
*/

function training_session_service_delete(
    int $user_id,
    int $session_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Find Session
    |--------------------------------------------------------------------------
    */

    $session =
        training_session_repository_find_by_id(
            $session_id
        );


    if (!$session) {

        return [

            'success' => false,

            'message' =>
                'Training session not found.',

            'errors' => [],

            'status_code' => 404

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_session_repository_find_company_by_user_id(
            $user_id
        );


    if (
        !$company
        ||
        (
            isset($session['company_id'])
            &&
            (int) $session['company_id']
            !==
            (int) $company['company_id']
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to delete this session.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Scheduled Sessions
    |--------------------------------------------------------------------------
    */

    if (
        isset($session['status'])
        &&
        $session['status'] !== 'scheduled'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only scheduled sessions can be deleted.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    $deleted =
        training_session_repository_delete(
            $session_id
        );


    if (
        !$deleted
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to delete training session.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    return [

        'success' => true,

        'message' =>
            'Training session deleted successfully.',

        'data' => null,

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Validate Session Data
|--------------------------------------------------------------------------
|
| Kept inside the service as a compatibility layer.
| Final field-level validation belongs to the validator.
|
*/

function training_session_service_validate_data(
    array $data,
    bool $is_update = false
): array {

    /*
    |--------------------------------------------------------------------------
    | Use Dedicated Validator If Available
    |--------------------------------------------------------------------------
    */

    if (
        function_exists(
            'training_validator_session'
        )
    ) {

        return training_validator_session(
            $data,
            $is_update
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Fallback Validation
    |--------------------------------------------------------------------------
    */

    $errors = [];


    if (
        !$is_update
        &&
        empty($data['session_date'])
    ) {

        $errors['session_date'] =
            'Session date is required.';
    }


    if (
        !$is_update
        &&
        empty($data['start_time'])
    ) {

        $errors['start_time'] =
            'Session start time is required.';
    }


    if (
        !empty($data['start_time'])
        &&
        !empty($data['end_time'])
        &&
        $data['end_time']
        <=
        $data['start_time']
    ) {

        $errors['end_time'] =
            'End time must be later than start time.';
    }


    if (
        isset($data['meeting_url'])
        &&
        $data['meeting_url'] !== null
        &&
        $data['meeting_url'] !== ''
        &&
        !filter_var(
            $data['meeting_url'],
            FILTER_VALIDATE_URL
        )
    ) {

        $errors['meeting_url'] =
            'Meeting URL is invalid.';
    }


    /*
    |--------------------------------------------------------------------------
    | Trial Period Validation
    |--------------------------------------------------------------------------
    */

    $minimum_trial_days =
        defined('MIN_TRIAL_PERIOD_DAYS')
            ? MIN_TRIAL_PERIOD_DAYS
            : 7;


    if (
        !$is_update
        &&
        !empty($data['training_id'])
    ) {

        $training =
            training_repository_find_by_id(
                (int) $data['training_id']
            );

        if ($training) {

            if (
                isset($training['trial_period'])
                &&
                $training['trial_period'] !== null
            ) {

                $trial_days =
                    isset($data['trial_days'])
                        ? (int) $data['trial_days']
                        : (int) $training['trial_period'];

                if ($trial_days < $minimum_trial_days) {

                    $errors['trial_days'] =
                        sprintf(
                            'Trial period must be at least %d days.',
                            $minimum_trial_days
                        );
                }

                if (!isset($data['trial_start_date']) || empty($data['trial_start_date'])) {

                    $trial_start =
                        isset($data['session_date'])
                            ? $data['session_date']
                            : date('Y-m-d');

                    if (!empty($errors)) {

                        $trial_end =
                            date_add_days(
                                $trial_start,
                                $trial_days
                            );

                    } else {

                        $trial_end = null;
                    }

                    if (!isset($data['trial_end_date']) || empty($data['trial_end_date'])) {

                        $data['trial_end_date'] = $trial_end;
                    }
                }
            }
        }
    }


    return [

        'valid' =>
            empty($errors),

        'errors' =>
            $errors

    ];
}
