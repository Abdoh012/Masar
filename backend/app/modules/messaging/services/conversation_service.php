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
): ?array {

    if ($conversation_id <= 0 || $user_id <= 0) {
        return null;
    }


    $role =
        auth_role();


    /*
     * If the conversation is linked to an application,
     * verify the user is authorized for that application.
     */

    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $user_id
        );


    if (
        $conversation
        &&
        isset($conversation['application_id'])
        &&
        $conversation['application_id'] > 0
    ) {

        $application_id =
            (int) $conversation['application_id'];


        /*
         * Check authorization based on user role.
         */

        $authorized =
            conversation_service_application_participant(
                $application_id,
                $user_id
            );


        if (!$authorized) {
            return null;
        }
    }


    return $conversation;
}


/*
|--------------------------------------------------------------------------
| Create Conversation
|--------------------------------------------------------------------------
*/

function conversation_service_create(
    array $data
) {

    $user_id =
        isset($data['user_id'])
        ? (int) $data['user_id']
        : 0;

    if ($user_id <= 0) {

        return [
            'message' => 'Invalid user.',
            'errors' => [
                'user_id' => 'A valid user ID is required.'
            ]
        ];
    }


    $title =
        trim(
            (string) (
                $data['title']
                ?? $data['name']
                ?? ''
            )
        );


    /*
     * A conversation may be created
     * without an explicit title.
     */

    if ($title === '') {
        $title = 'New Conversation';
    }


    $participant_ids =
        $data['participant_ids']
        ?? [];


    if (
        !is_array($participant_ids)
    ) {
        $participant_ids = [];
    }


    /*
     * Authenticated user must always
     * be included as a participant.
     */

    $participant_ids[] =
        $user_id;


    $participant_ids =
        array_map(
            'intval',
            $participant_ids
        );


    $participant_ids =
        array_filter(
            $participant_ids,
            static function ($id) {
                return $id > 0;
            }
        );


    $participant_ids =
        array_values(
            array_unique(
                $participant_ids
            )
        );


    /*
     * Determine the application_id from the data.
     * If application_id is provided, validate that
     * the authenticated user is linked to that application
     * and the application status is ACCEPTED.
     */

    $application_id =
        isset($data['application_id'])
        ? (int) $data['application_id']
        : 0;


    if ($application_id > 0) {


        $application =
            application_repository_find_by_id(
                $application_id
            );


        if (
            !$application
            ||
            !conversation_service_application_is_accepted(
                $application
            )
        ) {

            return [
                'message' =>
                    'Chat is not available for this application status.',
                'errors' => [
                    'application_id' =>
                        'Chat is only available for accepted training applications.'
                ]
            ];
        }


        /*
         * Verify the user is linked to this application.
         */
        $user_can_access =
            conversation_service_application_participant(
                $application_id,
                $user_id
            );


        if (!$user_can_access) {

            return [
                'message' =>
                    'You are not authorized to create a chat for this application.',
                'errors' => [
                    'application_id' =>
                        'You must be the student or company associated with this accepted application.'
                ]
            ];
        }
    }


    $conversation_data = [
        'title' =>
            $title,

        'created_by' =>
            $user_id,

        'user_id' =>
            $user_id,

        'application_id' =>
            $application_id
    ];


    $conversation =
        conversation_repository_create(
            $conversation_data
        );


    if (!$conversation) {
        return false;
    }


    $conversation_id =
        isset(
            $conversation['id']
        )
        ? (int)
            $conversation['id']
        : 0;


    /*
     * Add participants after the
     * conversation has been created.
     */

    if (
        $conversation_id > 0
        &&
        !empty($participant_ids)
    ) {

        foreach (
            $participant_ids
            as $participant_id
        ) {

            conversation_repository_add_participant(
                $conversation_id,
                $participant_id,
                $user_id
            );
        }
    }


    /*
     * Return the complete conversation.
     */

    if ($conversation_id > 0) {

        $complete =
            conversation_repository_find_for_user(
                $conversation_id,
                $user_id
            );

        if ($complete) {
            return $complete;
        }
    }


    return $conversation;
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
