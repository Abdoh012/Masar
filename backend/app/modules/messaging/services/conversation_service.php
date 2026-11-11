<?php

/**
 * MASAR - Conversation Service
 *
 * Business logic for conversations.
 *
 * Controller
 *    ↓
 * Service
 *    ↓
 * Repository
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/conversation_repository.php';

require_once __DIR__ . '/../../training/repositories/application_repository.php';
require_once __DIR__ . '/../../training/repositories/training_repository.php';
require_once __DIR__ . '/../../students/repositories/student_repository.php';
require_once __DIR__ . '/../../companies/repositories/company_repository.php';


/*
|--------------------------------------------------------------------------
| Application Access Helper
|--------------------------------------------------------------------------
|
| Determines whether a user is the student or the company associated
| with a training application.
*/

function conversation_service_application_participant(
    int $application_id,
    int $user_id
): bool {

    if ($application_id <= 0 || $user_id <= 0) {
        return false;
    }

    $application =
        application_repository_find_by_id(
            $application_id
        );

    if (!$application) {
        return false;
    }

    $role =
        auth_role();

    if ($role === ROLE_STUDENT) {

        $student =
            student_repository_find_by_user_id(
                $user_id
            );

        if (!$student) {
            return false;
        }

        return
            (int) $application['student_id']
            ===
            (int) $student['id'];
    }

    if ($role === ROLE_COMPANY) {

        $company =
            company_repository_find_by_user_id(
                $user_id
            );

        if (!$company) {
            return false;
        }

        $training =
            training_repository_find_by_id(
                (int) (
                    $application['training_id']
                    ?? 0
                )
            );

        if (!$training) {
            return false;
        }

        return
            (int) $training['company_id']
            ===
            (int) $company['id'];
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| Application Accepted Helper
|--------------------------------------------------------------------------
|
| The training_applications table stores 'accepted' as the approved status.
*/

function conversation_service_application_is_accepted(
    array $application
): bool {

    $status =
        strtolower(
            trim(
                (string) (
                    $application['status']
                    ?? ''
                )
            )
        );

    return $status === 'accepted';
}


/*
|--------------------------------------------------------------------------
| Conversation Access Helper
|--------------------------------------------------------------------------
|
| Single authorization boundary for conversation-scoped operations:
|   404 when the conversation does not exist
|   403 when it exists but the authenticated user may not access it
|
| Keeping this in the service layer prevents the controllers and the message
| service from drifting apart on the disclosure policy.
*/

function conversation_service_access(
    int $conversation_id,
    int $user_id
): array {

    if (
        $conversation_id <= 0 ||
        $user_id <= 0
    ) {

        return [
            'ok'      => false,
            'status'  => 404,
            'message' => 'Conversation not found.',
            'errors'  => [],
        ];
    }


    $conversation =
        conversation_repository_find(
            $conversation_id
        );


    if (!$conversation) {

        return [
            'ok'      => false,
            'status'  => 404,
            'message' => 'Conversation not found.',
            'errors'  => [],
        ];
    }


    if (
        !conversation_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {

        return [
            'ok'      => false,
            'status'  => 403,
            'message' => 'You are not a participant in this conversation.',
            'errors'  => [],
        ];
    }


    $application_id =
        (int) (
            $conversation['application_id']
            ?? 0
        );


    if (
        $application_id > 0 &&
        !conversation_service_application_participant(
            $application_id,
            $user_id
        )
    ) {

        return [
            'ok'      => false,
            'status'  => 403,
            'message' => 'You are not authorized to access this conversation.',
            'errors'  => [],
        ];
    }


    return [
        'ok'           => true,
        'conversation' => $conversation,
    ];
}


/*
|--------------------------------------------------------------------------
| Application Messaging Status
|--------------------------------------------------------------------------
|
| Read fresh from the database on every call. The withdrawal policy is a
| business rule, so it must never rely on a status cached in the conversation
| row or in the request.
*/

function conversation_service_messaging_blocked_reason(
    int $application_id
): ?string {

    if ($application_id <= 0) {
        return null;
    }


    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {
        return null;
    }


    $status =
        strtolower(
            trim(
                (string) (
                    $application['status']
                    ?? ''
                )
            )
        );


    if ($status === APPLICATION_STATUS_WITHDRAWN) {

        return 'Messaging is disabled because the training application was withdrawn.';
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| List Conversations
|--------------------------------------------------------------------------
*/

function conversation_service_list(
    int $user_id,
    array $filters = []
): array {

    if ($user_id <= 0) {
        return [];
    }


    $role =
        auth_role();


    /*
     * If filtering by application_id, validate authorization.
     */

    if (
        isset(
            $filters['application_id']
        )
    ) {

        $application_id =
            (int) $filters['application_id'];


        /*
         * Check that the user is authorized for this application.
         */

        $authorized =
            conversation_service_application_participant(
                $application_id,
                $user_id
            );


        if (!$authorized) {

            return [];
        }
    }


    return conversation_repository_list(
        $user_id,
        $filters
    );
}


/*
 |--------------------------------------------------------------------------
 | Find Conversation
 |--------------------------------------------------------------------------
 */

function conversation_service_find(
    int $conversation_id,
    int $user_id
): array {

    $access =
        conversation_service_access(
            $conversation_id,
            $user_id
        );


    if (empty($access['ok'])) {
        return $access;
    }


    /*
     * Re-read with the joined display fields used by the API response.
     */

    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {

        return [
            'ok'      => false,
            'status'  => 404,
            'message' => 'Conversation not found.',
            'errors'  => [],
        ];
    }


    return [
        'ok'           => true,
        'conversation' => $conversation,
    ];
}


/*
|--------------------------------------------------------------------------
| Create Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_create(
    array $data
): array {

    $user_id =
        isset($data['user_id'])
        ? (int) $data['user_id']
        : 0;

    if ($user_id <= 0) {

        return [
            'ok'      => false,
            'status'  => 401,
            'message' => 'Authentication required.',
            'errors'  => [],
        ];
    }


    $errors = [];


    /*
     * The conversations table has no title and no participant list. Reject
     * these instead of accepting data that would never be persisted.
     */

    foreach (
        [
            'title',
            'name',
            'participant_id',
            'participant_ids',
            'participants',
        ]
        as $field
    ) {

        if (!array_key_exists($field, $data)) {
            continue;
        }


        $value = $data[$field];


        if (
            $value === null ||
            $value === '' ||
            $value === []
        ) {
            continue;
        }


        $errors[$field] =
            str_replace('_', ' ', $field)
            . ' is not supported by the conversations API.';
    }


    /*
     * application_id is required and must be a positive integer before any
     * database access happens.
     */

    $application_id =
        filter_var(
            $data['application_id']
            ?? null,
            FILTER_VALIDATE_INT
        );


    if (
        $application_id === false ||
        (int) $application_id <= 0
    ) {

        $errors['application_id'] =
            'A valid application ID is required.';
    }


    if (!empty($errors)) {

        return [
            'ok'      => false,
            'status'  => 422,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ];
    }


    $application_id =
        (int) $application_id;


    $application =
        application_repository_find_by_id(
            $application_id
        );


    if (!$application) {

        return [
            'ok'      => false,
            'status'  => 404,
            'message' => 'Training application not found.',
            'errors'  => [
                'application_id' =>
                    'Training application not found.',
            ],
        ];
    }


    /*
     * Preserve the existing rule: a conversation may only be created for an
     * accepted application.
     */

    if (
        !conversation_service_application_is_accepted(
            $application
        )
    ) {

        return [
            'ok'      => false,
            'status'  => 422,
            'message' => 'Chat is not available for this application status.',
            'errors'  => [
                'application_id' =>
                    'Chat is only available for accepted training applications.',
            ],
        ];
    }


    /*
     * The authenticated user must be the student or the company on the
     * application. The student and company are always derived from the
     * application, never accepted from the client.
     */

    if (
        !conversation_service_application_participant(
            $application_id,
            $user_id
        )
    ) {

        return [
            'ok'      => false,
            'status'  => 403,
            'message' => 'You are not authorized to create a chat for this application.',
            'errors'  => [
                'application_id' =>
                    'You must be the student or company associated with this accepted application.',
            ],
        ];
    }


    /*
     * Idempotent duplicate handling. The unique index on application_id is the
     * final guard against the concurrent path inside the repository.
     */

    $existing =
        conversation_repository_find_by_application(
            $application_id,
            $user_id
        );


    if ($existing) {

        $conversation =
            conversation_repository_find(
                (int) $existing['id']
            );


        if ($conversation) {

            return [
                'ok'           => true,
                'conversation' => $conversation,
                'created'      => false,
            ];
        }
    }


    try {

        $created =
            conversation_repository_create(
                [
                    'application_id' =>
                        $application_id,
                ]
            );

    } catch (Throwable $exception) {

        if (function_exists('logger_exception')) {
            logger_exception($exception);
        }

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to create conversation.',
            'errors'  => [],
        ];
    }


    if (!$created) {

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to create conversation.',
            'errors'  => [],
        ];
    }


    return [
        'ok'           => true,
        'conversation' => $created['conversation'],
        'created'      => (bool) $created['created'],
    ];
}


/*
|--------------------------------------------------------------------------
| Update Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_update(
    int $conversation_id,
    int $user_id,
    array $data
) {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return [
            'message' =>
                'Invalid conversation or user.',
            'errors' => [
                'conversation_id' =>
                    'A valid conversation ID is required.'
            ]
        ];
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    $allowed = [
        'title',
        'name',
        'status',
        'is_archived',
        'archived_at'
    ];


    $update = [];


    foreach (
        $allowed
        as $field
    ) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $update[$field] =
                $data[$field];
        }
    }


    if (isset($update['name'])) {

        $update['title'] =
            $update['name'];

        unset(
            $update['name']
        );
    }


    if (
        isset(
            $update['title']
        )
    ) {

        $update['title'] =
            trim(
                (string)
                    $update['title']
            );


        if (
            $update['title'] === ''
        ) {

            return [
                'message' =>
                    'Conversation title cannot be empty.',
                'errors' => [
                    'title' =>
                        'Title cannot be empty.'
                ]
            ];
        }
    }


    if (
        empty($update)
    ) {

        return [
            'message' =>
                'No fields to update.',
            'errors' => []
        ];
    }


    $updated =
        conversation_repository_update(
            $conversation_id,
            $user_id,
            $update
        );


    if (!$updated) {
        return false;
    }


    return conversation_repository_find_for_user(
        $conversation_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Rename Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_rename(
    int $conversation_id,
    int $user_id,
    string $title
) {

    $title =
        trim($title);


    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    if ($title === '') {

        return [
            'message' =>
                'Conversation title cannot be empty.',
            'errors' => [
                'title' =>
                    'Title cannot be empty.'
            ]
        ];
    }


    $updated =
        conversation_repository_update(
            $conversation_id,
            $user_id,
            [
                'title' =>
                    $title
            ]
        );


    if (!$updated) {
        return false;
    }


    return conversation_repository_find_for_user(
        $conversation_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Archive Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_archive(
    int $conversation_id,
    int $user_id
) {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    $updated =
        conversation_repository_archive(
            $conversation_id,
            $user_id
        );


    if (!$updated) {
        return false;
    }


    return conversation_repository_find_for_user(
        $conversation_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Restore Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_restore(
    int $conversation_id,
    int $user_id
) {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    $updated =
        conversation_repository_restore(
            $conversation_id,
            $user_id
        );


    if (!$updated) {
        return false;
    }


    return conversation_repository_find_for_user(
        $conversation_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Delete Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_delete(
    int $conversation_id,
    int $user_id
): bool {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    return (bool)
        conversation_repository_delete(
            $conversation_id,
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Participants
|--------------------------------------------------------------------------
*/

function conversation_service_participants(
    int $conversation_id,
    int $user_id
): array|false {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    return conversation_repository_participants(
        $conversation_id
    );
}


/*
|--------------------------------------------------------------------------
| Add Participant
|--------------------------------------------------------------------------
*/

function conversation_service_add_participant(
    int $conversation_id,
    int $user_id,
    int $participant_id
) {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
        ||
        $participant_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    /*
     * Avoid duplicate participants.
     */

    $existing =
        conversation_repository_participant_exists(
            $conversation_id,
            $participant_id
        );


    if ($existing) {

        return [
            'id' =>
                $participant_id,
            'conversation_id' =>
                $conversation_id,
            'user_id' =>
                $participant_id
        ];
    }


    $added =
        conversation_repository_add_participant(
            $conversation_id,
            $participant_id,
            $user_id
        );


    if (!$added) {
        return false;
    }


    return [
        'conversation_id' =>
            $conversation_id,

        'user_id' =>
            $participant_id
    ];
}


/*
|--------------------------------------------------------------------------
| Remove Participant
|--------------------------------------------------------------------------
*/

function conversation_service_remove_participant(
    int $conversation_id,
    int $user_id,
    int $participant_id
): bool {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
        ||
        $participant_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    /*
     * Do not allow the last/owner
     * participant to be removed
     * through the generic method.
     */

    $owner_id =
        isset(
            $conversation['created_by']
        )
        ? (int)
            $conversation['created_by']
        : 0;


    if (
        $owner_id > 0
        &&
        $owner_id === $participant_id
    ) {

        return false;
    }


    return (bool)
        conversation_repository_remove_participant(
            $conversation_id,
            $participant_id
        );
}


/*
|--------------------------------------------------------------------------
| Mark Conversation As Read
|--------------------------------------------------------------------------
*/

function conversation_service_mark_read(
    int $conversation_id,
    int $user_id
): bool {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {
        return false;
    }


    return (bool)
        conversation_repository_mark_read(
            $conversation_id,
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
*/

function conversation_service_unread_count(
    int $user_id
): int|false {

    if ($user_id <= 0) {
        return false;
    }


    return (int)
        conversation_repository_unread_count(
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Search Conversations
|--------------------------------------------------------------------------
*/

function conversation_service_search(
    int $user_id,
    string $keyword,
    array $filters = []
): array|false {

    if ($user_id <= 0) {
        return false;
    }


    $keyword =
        trim($keyword);


    if ($keyword === '') {
        return [];
    }


    return conversation_repository_search(
        $user_id,
        $keyword,
        $filters
    );
}
