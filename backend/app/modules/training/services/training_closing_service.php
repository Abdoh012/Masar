<?php

/**
 * MASAR - Training Closing Service
 *
 * Handles the business logic required to close a training.
 *
 * Flow:
 *
 * Controller
 *     ↓
 * Closing Service
 *     ↓
 * Training Repository
 *     ↓
 * Application Repository
 *     ↓
 * Certificate / Notification logic
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/training_repository.php';
require_once __DIR__ . '/../repositories/application_repository.php';
require_once __DIR__ . '/../repositories/training_session_repository.php';


/*
|--------------------------------------------------------------------------
| Close Training
|--------------------------------------------------------------------------
|
| Closes a training after all required sessions have been completed.
|
*/

function training_closing_service_close(
    int $user_id,
    int $training_id,
    array $data = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate IDs
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
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
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
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to close this training.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Already Closed
    |--------------------------------------------------------------------------
    */

    $closed_statuses = [

        'closed',

        'completed',

        'cancelled',

        'deleted'

    ];


    if (
        isset($training['status'])
        &&
        in_array(
            $training['status'],
            $closed_statuses,
            true
        )
    ) {

        return [

            'success' => false,

            'message' =>
                'This training is already closed or unavailable.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Training Sessions
    |--------------------------------------------------------------------------
    */

    $session_stats =
        training_closing_service_get_session_stats(
            $training_id
        );


    if (
        !$session_stats['success']
    ) {

        return [

            'success' => false,

            'message' =>
                $session_stats['message'],

            'errors' =>
                $session_stats['errors'],

            'status_code' =>
                $session_stats['status_code']

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Require At Least One Session
    |--------------------------------------------------------------------------
    */

    if (
        $session_stats['data']['total'] <= 0
    ) {

        return [

            'success' => false,

            'message' =>
                'The training cannot be closed because it has no sessions.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | All Sessions Must Be Completed
    |--------------------------------------------------------------------------
    */

    if (
        $session_stats['data']['completed']
        !==
        $session_stats['data']['total']
    ) {

        return [

            'success' => false,

            'message' =>
                'All training sessions must be completed before closing the training.',

            'errors' => [

                'sessions' => [

                    'total' =>
                        $session_stats['data']['total'],

                    'completed' =>
                        $session_stats['data']['completed'],

                    'remaining' =>
                        $session_stats['data']['total']
                        -
                        $session_stats['data']['completed']

                ]

            ],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Applications
    |--------------------------------------------------------------------------
    */

    $application_stats =
        training_closing_service_get_application_stats(
            $training_id
        );


    if (
        !$application_stats['success']
    ) {

        return [

            'success' => false,

            'message' =>
                $application_stats['message'],

            'errors' =>
                $application_stats['errors'],

            'status_code' =>
                $application_stats['status_code']

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Optional Closing Note
    |--------------------------------------------------------------------------
    */

    $closing_note =
        isset($data['closing_note'])
            ? trim(
                (string)
                $data['closing_note']
            )
            : null;


    /*
    |--------------------------------------------------------------------------
    | Close Training
    |--------------------------------------------------------------------------
    */

    $closed =
        training_repository_close(
            $training_id,
            $closing_note
        );


    if (
        !$closed
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to close training.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Training
    |--------------------------------------------------------------------------
    */

    $updated_training =
        training_repository_find_by_id(
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Return Result
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>
            'Training closed successfully.',

        'data' => [

            'training' =>
                $updated_training,

            'sessions' =>
                $session_stats['data'],

            'applications' =>
                $application_stats['data']

        ],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Get Session Statistics
|--------------------------------------------------------------------------
*/

function training_closing_service_get_session_stats(
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Sessions
    |--------------------------------------------------------------------------
    */

    $sessions =
        training_session_repository_get_all_by_training(
            $training_id
        );


    if (
        !is_array($sessions)
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to retrieve training sessions.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Counters
    |--------------------------------------------------------------------------
    */

    $total = 0;

    $scheduled = 0;

    $in_progress = 0;

    $completed = 0;

    $cancelled = 0;


    /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    */

    foreach (
        $sessions as $session
    ) {

        $total++;


        $status =
            $session['status']
            ?? null;


        switch ($status) {

            case 'scheduled':

                $scheduled++;

                break;


            case 'in_progress':

                $in_progress++;

                break;


            case 'completed':

                $completed++;

                break;


            case 'cancelled':

                $cancelled++;

                break;

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>
            'Training session statistics retrieved successfully.',

        'data' => [

            'total' =>
                $total,

            'scheduled' =>
                $scheduled,

            'in_progress' =>
                $in_progress,

            'completed' =>
                $completed,

            'cancelled' =>
                $cancelled

        ],

        'errors' => [],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Get Application Statistics
|--------------------------------------------------------------------------
*/

function training_closing_service_get_application_stats(
    int $training_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $applications =
        application_repository_get_by_training(
            $training_id
        );


    if (
        !is_array($applications)
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to retrieve training applications.',

            'errors' => [],

            'status_code' => 500

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Counters
    |--------------------------------------------------------------------------
    */

    $total = 0;

    $pending = 0;

    $accepted = 0;

    $rejected = 0;

    $withdrawn = 0;


    /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    */

    foreach (
        $applications as $application
    ) {

        $total++;


        $status =
            $application['status']
            ?? null;


        switch ($status) {

            case 'pending':

                $pending++;

                break;


            case 'accepted':

                $accepted++;

                break;


            case 'rejected':

                $rejected++;

                break;


            case 'withdrawn':

                $withdrawn++;

                break;

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>
            'Training application statistics retrieved successfully.',

        'data' => [

            'total' =>
                $total,

            'pending' =>
                $pending,

            'accepted' =>
                $accepted,

            'rejected' =>
                $rejected,

            'withdrawn' =>
                $withdrawn

        ],

        'errors' => [],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Preview Training Closing
|--------------------------------------------------------------------------
|
| Returns everything required before the company actually closes
| the training.
|
*/

function training_closing_service_preview(
    int $user_id,
    int $training_id
): array {

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
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
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
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to access this training.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    $sessions =
        training_closing_service_get_session_stats(
            $training_id
        );


    $applications =
        training_closing_service_get_application_stats(
            $training_id
        );


    /*
    |--------------------------------------------------------------------------
    | Can Close
    |--------------------------------------------------------------------------
    */

    $can_close = true;

    $blocking_reasons = [];


    if (
        !$sessions['success']
    ) {

        $can_close = false;

        $blocking_reasons[] =
            'Unable to verify training sessions.';

    } else {

        if (
            $sessions['data']['total'] <= 0
        ) {

            $can_close = false;

            $blocking_reasons[] =
                'The training has no sessions.';

        }


        if (
            $sessions['data']['completed']
            !==
            $sessions['data']['total']
        ) {

            $can_close = false;

            $blocking_reasons[] =
                'Not all training sessions are completed.';

        }
    }


    if (
        !$applications['success']
    ) {

        $can_close = false;

        $blocking_reasons[] =
            'Unable to verify training applications.';
    }


    /*
    |--------------------------------------------------------------------------
    | Return Preview
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>
            'Training closing preview generated successfully.',

        'data' => [

            'training' =>
                $training,

            'sessions' =>
                $sessions['data']
                ?? null,

            'applications' =>
                $applications['data']
                ?? null,

            'can_close' =>
                $can_close,

            'blocking_reasons' =>
                $blocking_reasons

        ],

        'errors' => [],

        'status_code' => 200

    ];
}


/*
|--------------------------------------------------------------------------
| Reopen Training
|--------------------------------------------------------------------------
|
| Allows an authorized company to reopen a training if the
| application/business rules allow it.
|
*/

function training_closing_service_reopen(
    int $user_id,
    int $training_id
): array {

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
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        training_repository_find_company_by_user_id(
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
        (int) $training['company_id']
        !==
        (int) $company['company_id']
    ) {

        return [

            'success' => false,

            'message' =>
                'You are not allowed to reopen this training.',

            'errors' => [],

            'status_code' => 403

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Closed Training
    |--------------------------------------------------------------------------
    */

    if (
        !isset($training['status'])
        ||
        $training['status'] !== 'closed'
    ) {

        return [

            'success' => false,

            'message' =>
                'Only closed trainings can be reopened.',

            'errors' => [],

            'status_code' => 409

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reopen
    |--------------------------------------------------------------------------
    */

    $reopened =
        training_repository_reopen(
            $training_id
        );


    if (
        !$reopened
    ) {

        return [

            'success' => false,

            'message' =>
                'Unable to reopen training.',

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
        training_repository_find_by_id(
            $training_id
        );


    return [

        'success' => true,

        'message' =>
            'Training reopened successfully.',

        'data' =>
            $updated,

        'status_code' => 200

    ];
}
