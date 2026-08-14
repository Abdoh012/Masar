<?php

/**
 * MASAR - Conversation Controller
 *
 * Handles HTTP requests related to conversations.
 *
 * Request
 *    ↓
 * Controller
 *    ↓
 * Validator
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

require_once __DIR__ . '/../services/conversation_service.php';


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function conversation_controller_request_data(): array
{
    $data = [];

    if (
        isset($_POST)
        &&
        is_array($_POST)
    ) {
        $data = $_POST;
    }

    /*
     * Support JSON requests.
     */

    $content_type =
        $_SERVER['CONTENT_TYPE']
        ?? '';

    if (
        stripos(
            $content_type,
            'application/json'
        ) !== false
    ) {

        $raw =
            file_get_contents(
                'php://input'
            );

        if ($raw !== false && trim($raw) !== '') {

            $json =
                json_decode(
                    $raw,
                    true
                );

            if (
                is_array($json)
            ) {
                $data =
                    array_merge(
                        $data,
                        $json
                    );
            }
        }
    }

    return $data;
}


function conversation_controller_response(
    array $data,
    int $status = 200
): void {

    http_response_code($status);

    header(
        'Content-Type: application/json; charset=utf-8'
    );

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


function conversation_controller_success(
    $data = null,
    string $message = 'Success.',
    int $status = 200
): void {

    conversation_controller_response(
        [
            'success' => true,
            'message' => $message,
            'data'    => $data
        ],
        $status
    );
}


function conversation_controller_error(
    string $message,
    int $status = 400,
    array $errors = []
): void {

    conversation_controller_response(
        [
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ],
        $status
    );
}


/*
|--------------------------------------------------------------------------
| Authentication Helper
|--------------------------------------------------------------------------
*/

function conversation_controller_user_id(): ?int
{
    if (
        function_exists(
            'auth_id'
        )
    ) {
        $id =
            auth_id();

        return $id
            ? (int) $id
            : null;
    }

    if (
        function_exists(
            'current_user_id'
        )
    ) {
        $id =
            current_user_id();

        return $id
            ? (int) $id
            : null;
    }

    if (
        isset(
            $_SESSION['user_id']
        )
    ) {
        return (int)
            $_SESSION['user_id'];
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

function conversation_controller_index(): void
{
    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }

    $data =
        conversation_controller_request_data();

    $result =
        conversation_service_list(
            $user_id,
            $data
        );

    conversation_controller_success(
        $result,
        'Conversations retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| List Conversations
|--------------------------------------------------------------------------
*/

function conversation_controller_list(): void
{
    conversation_controller_index();
}


/*
|--------------------------------------------------------------------------
| Show Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_show(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    if ($conversation_id <= 0) {

        $data =
            conversation_controller_request_data();

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422,
            [
                'conversation_id' =>
                    'Conversation ID must be a positive integer.'
            ]
        );
    }


    $conversation =
        conversation_service_find(
            $conversation_id,
            $user_id
        );


    if (!$conversation) {

        conversation_controller_error(
            'Conversation not found.',
            404
        );
    }


    conversation_controller_success(
        $conversation,
        'Conversation retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Create Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_create(): void
{
    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    /*
     * Always use authenticated user
     * as the conversation owner.
     */

    $data['user_id'] =
        $user_id;


    $result =
        conversation_service_create(
            $data
        );


    if (
        is_array($result)
        &&
        isset($result['errors'])
        &&
        !empty($result['errors'])
    ) {

        conversation_controller_error(
            $result['message']
                ?? 'Unable to create conversation.',
            422,
            $result['errors']
        );
    }


    if (!$result) {

        conversation_controller_error(
            'Unable to create conversation.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation created successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Update Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_update(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422,
            [
                'conversation_id' =>
                    'Conversation ID must be a positive integer.'
            ]
        );
    }


    /*
     * Do not allow the client
     * to change ownership.
     */

    unset(
        $data['user_id'],
        $data['owner_id'],
        $data['created_by']
    );


    $result =
        conversation_service_update(
            $conversation_id,
            $user_id,
            $data
        );


    if (
        is_array($result)
        &&
        isset($result['errors'])
        &&
        !empty($result['errors'])
    ) {

        conversation_controller_error(
            $result['message']
                ?? 'Unable to update conversation.',
            422,
            $result['errors']
        );
    }


    if (!$result) {

        conversation_controller_error(
            'Unable to update conversation.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Rename Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_rename(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $title =
        trim(
            (string) (
                $data['title']
                ?? $data['name']
                ?? ''
            )
        );


    if ($title === '') {

        conversation_controller_error(
            'Conversation title is required.',
            422,
            [
                'title' =>
                    'Title cannot be empty.'
            ]
        );
    }


    $result =
        conversation_service_rename(
            $conversation_id,
            $user_id,
            $title
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to rename conversation.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation renamed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Archive Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_archive(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        conversation_service_archive(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to archive conversation.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation archived successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Restore Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_restore(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        conversation_service_restore(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to restore conversation.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation restored successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete Conversation
|--------------------------------------------------------------------------
*/

function conversation_controller_delete(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        conversation_service_delete(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to delete conversation.',
            500
        );
    }


    conversation_controller_success(
        null,
        'Conversation deleted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Participants
|--------------------------------------------------------------------------
*/

function conversation_controller_participants(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        conversation_service_participants(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to retrieve conversation participants.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation participants retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Add Participant
|--------------------------------------------------------------------------
*/

function conversation_controller_add_participant(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    $participant_id =
        isset(
            $data['participant_id']
        )
        ? (int)
            $data['participant_id']
        : (
            isset(
                $data['user_id']
            )
            ? (int)
                $data['user_id']
            : 0
        );


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    if ($participant_id <= 0) {

        conversation_controller_error(
            'Participant ID is required.',
            422,
            [
                'participant_id' =>
                    'Participant ID must be a positive integer.'
            ]
        );
    }


    $result =
        conversation_service_add_participant(
            $conversation_id,
            $user_id,
            $participant_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to add participant.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Participant added successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Remove Participant
|--------------------------------------------------------------------------
*/

function conversation_controller_remove_participant(
    int $conversation_id = 0,
    int $participant_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($participant_id <= 0) {

        $participant_id =
            isset(
                $data['participant_id']
            )
            ? (int)
                $data['participant_id']
            : 0;
    }


    if (
        $conversation_id <= 0
        ||
        $participant_id <= 0
    ) {

        conversation_controller_error(
            'Conversation ID and participant ID are required.',
            422
        );
    }


    $result =
        conversation_service_remove_participant(
            $conversation_id,
            $user_id,
            $participant_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to remove participant.',
            500
        );
    }


    conversation_controller_success(
        null,
        'Participant removed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Mark As Read
|--------------------------------------------------------------------------
*/

function conversation_controller_mark_read(
    int $conversation_id = 0
): void {

    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    if ($conversation_id <= 0) {

        $conversation_id =
            isset(
                $data['conversation_id']
            )
            ? (int)
                $data['conversation_id']
            : 0;
    }


    if ($conversation_id <= 0) {

        conversation_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        conversation_service_mark_read(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        conversation_controller_error(
            'Unable to mark conversation as read.',
            500
        );
    }


    conversation_controller_success(
        null,
        'Conversation marked as read.'
    );
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
*/

function conversation_controller_unread_count(): void
{
    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $result =
        conversation_service_unread_count(
            $user_id
        );


    if ($result === false) {

        conversation_controller_error(
            'Unable to retrieve unread count.',
            500
        );
    }


    conversation_controller_success(
        [
            'count' =>
                (int) $result
        ],
        'Unread conversation count retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search Conversations
|--------------------------------------------------------------------------
*/

function conversation_controller_search(): void
{
    $user_id =
        conversation_controller_user_id();

    if (!$user_id) {

        conversation_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        conversation_controller_request_data();


    $keyword =
        trim(
            (string) (
                $data['keyword']
                ?? $data['q']
                ?? ''
            )
        );


    if ($keyword === '') {

        conversation_controller_error(
            'Search keyword is required.',
            422,
            [
                'keyword' =>
                    'Search keyword cannot be empty.'
            ]
        );
    }


    $result =
        conversation_service_search(
            $user_id,
            $keyword,
            $data
        );


    if ($result === false) {

        conversation_controller_error(
            'Unable to search conversations.',
            500
        );
    }


    conversation_controller_success(
        $result,
        'Conversation search completed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Dispatcher
|--------------------------------------------------------------------------
*/

function conversation_controller_dispatch(
    string $action,
    int $conversation_id = 0,
    int $participant_id = 0
): void {

    switch (
        strtolower(
            trim($action)
        )
    ) {

        case 'index':
        case 'list':
            conversation_controller_list();
            break;


        case 'show':
        case 'view':
            conversation_controller_show(
                $conversation_id
            );
            break;


        case 'create':
        case 'store':
            conversation_controller_create();
            break;


        case 'update':
        case 'edit':
            conversation_controller_update(
                $conversation_id
            );
            break;


        case 'rename':
            conversation_controller_rename(
                $conversation_id
            );
            break;


        case 'archive':
            conversation_controller_archive(
                $conversation_id
            );
            break;


        case 'restore':
            conversation_controller_restore(
                $conversation_id
            );
            break;


        case 'delete':
        case 'destroy':
            conversation_controller_delete(
                $conversation_id
            );
            break;


        case 'participants':
            conversation_controller_participants(
                $conversation_id
            );
            break;


        case 'add_participant':
            conversation_controller_add_participant(
                $conversation_id
            );
            break;


        case 'remove_participant':
            conversation_controller_remove_participant(
                $conversation_id,
                $participant_id
            );
            break;


        case 'mark_read':
            conversation_controller_mark_read(
                $conversation_id
            );
            break;


        case 'unread_count':
            conversation_controller_unread_count();
            break;


        case 'search':
            conversation_controller_search();
            break;


        default:

            conversation_controller_error(
                'Unknown conversation action.',
                404
            );
    }
}
