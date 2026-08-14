<?php

/**
 * MASAR - Message Controller
 *
 * Handles HTTP requests related to messages.
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

require_once __DIR__ . '/../services/message_service.php';


/*
|--------------------------------------------------------------------------
| Request Helpers
|--------------------------------------------------------------------------
*/

function message_controller_request_data(): array
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

        if (
            $raw !== false
            &&
            trim($raw) !== ''
        ) {

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


function message_controller_response(
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


function message_controller_success(
    $data = null,
    string $message = 'Success.',
    int $status = 200
): void {

    message_controller_response(
        [
            'success' => true,
            'message' => $message,
            'data'    => $data
        ],
        $status
    );
}


function message_controller_error(
    string $message,
    int $status = 400,
    array $errors = []
): void {

    message_controller_response(
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
| Authentication
|--------------------------------------------------------------------------
*/

function message_controller_user_id(): ?int
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
| List Messages
|--------------------------------------------------------------------------
*/

function message_controller_index(
    int $conversation_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


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

        message_controller_error(
            'Conversation ID is required.',
            422,
            [
                'conversation_id' =>
                    'Conversation ID must be a positive integer.'
            ]
        );
    }


    $result =
        message_service_list(
            $conversation_id,
            $user_id,
            $data
        );


    if ($result === false) {

        message_controller_error(
            'Unable to retrieve messages.',
            500
        );
    }


    message_controller_success(
        $result,
        'Messages retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Show Message
|--------------------------------------------------------------------------
*/

function message_controller_show(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422,
            [
                'message_id' =>
                    'Message ID must be a positive integer.'
            ]
        );
    }


    $result =
        message_service_find(
            $message_id,
            $user_id
        );


    if (!$result) {

        message_controller_error(
            'Message not found.',
            404
        );
    }


    message_controller_success(
        $result,
        'Message retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Send Message
|--------------------------------------------------------------------------
*/

function message_controller_create(): void
{
    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    /*
     * Sender must always be
     * the authenticated user.
     */

    $data['sender_id'] =
        $user_id;

    $data['user_id'] =
        $user_id;


    $result =
        message_service_create(
            $data
        );


    if (
        is_array($result)
        &&
        isset(
            $result['errors']
        )
        &&
        !empty(
            $result['errors']
        )
    ) {

        message_controller_error(
            $result['message']
                ?? 'Unable to send message.',
            422,
            $result['errors']
        );
    }


    if (!$result) {

        message_controller_error(
            'Unable to send message.',
            500
        );
    }


    message_controller_success(
        $result,
        'Message sent successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Update Message
|--------------------------------------------------------------------------
*/

function message_controller_update(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    /*
     * Never trust client-provided
     * sender identity.
     */

    unset(
        $data['sender_id'],
        $data['user_id'],
        $data['created_by']
    );


    $result =
        message_service_update(
            $message_id,
            $user_id,
            $data
        );


    if (
        is_array($result)
        &&
        isset(
            $result['errors']
        )
        &&
        !empty(
            $result['errors']
        )
    ) {

        message_controller_error(
            $result['message']
                ?? 'Unable to update message.',
            422,
            $result['errors']
        );
    }


    if (!$result) {

        message_controller_error(
            'Unable to update message.',
            500
        );
    }


    message_controller_success(
        $result,
        'Message updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

function message_controller_delete(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    $result =
        message_service_delete(
            $message_id,
            $user_id
        );


    if (!$result) {

        message_controller_error(
            'Unable to delete message.',
            500
        );
    }


    message_controller_success(
        null,
        'Message deleted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Mark Message As Read
|--------------------------------------------------------------------------
*/

function message_controller_mark_read(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    $result =
        message_service_mark_read(
            $message_id,
            $user_id
        );


    if (!$result) {

        message_controller_error(
            'Unable to mark message as read.',
            500
        );
    }


    message_controller_success(
        null,
        'Message marked as read.'
    );
}


/*
|--------------------------------------------------------------------------
| Mark Conversation Messages As Read
|--------------------------------------------------------------------------
*/

function message_controller_mark_conversation_read(
    int $conversation_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


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

        message_controller_error(
            'Conversation ID is required.',
            422
        );
    }


    $result =
        message_service_mark_conversation_read(
            $conversation_id,
            $user_id
        );


    if (!$result) {

        message_controller_error(
            'Unable to mark conversation messages as read.',
            500
        );
    }


    message_controller_success(
        null,
        'Conversation messages marked as read.'
    );
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
*/

function message_controller_unread_count(): void
{
    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    $conversation_id =
        isset(
            $data['conversation_id']
        )
        ? (int)
            $data['conversation_id']
        : null;


    $result =
        message_service_unread_count(
            $user_id,
            $conversation_id
        );


    if ($result === false) {

        message_controller_error(
            'Unable to retrieve unread message count.',
            500
        );
    }


    message_controller_success(
        [
            'count' =>
                (int) $result
        ],
        'Unread message count retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search Messages
|--------------------------------------------------------------------------
*/

function message_controller_search(): void
{
    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    $keyword =
        trim(
            (string) (
                $data['keyword']
                ?? $data['q']
                ?? ''
            )
        );


    if ($keyword === '') {

        message_controller_error(
            'Search keyword is required.',
            422,
            [
                'keyword' =>
                    'Search keyword cannot be empty.'
            ]
        );
    }


    $result =
        message_service_search(
            $user_id,
            $keyword,
            $data
        );


    if ($result === false) {

        message_controller_error(
            'Unable to search messages.',
            500
        );
    }


    message_controller_success(
        $result,
        'Message search completed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Reply To Message
|--------------------------------------------------------------------------
*/

function message_controller_reply(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    /*
     * Preserve the original
     * message reference.
     */

    $data['reply_to_id'] =
        $message_id;

    $data['sender_id'] =
        $user_id;

    $data['user_id'] =
        $user_id;


    $result =
        message_service_create(
            $data
        );


    if (
        is_array($result)
        &&
        isset(
            $result['errors']
        )
        &&
        !empty(
            $result['errors']
        )
    ) {

        message_controller_error(
            $result['message']
                ?? 'Unable to reply to message.',
            422,
            $result['errors']
        );
    }


    if (!$result) {

        message_controller_error(
            'Unable to reply to message.',
            500
        );
    }


    message_controller_success(
        $result,
        'Reply sent successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Forward Message
|--------------------------------------------------------------------------
*/

function message_controller_forward(): void
{
    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    $message_id =
        isset(
            $data['message_id']
        )
        ? (int)
            $data['message_id']
        : 0;


    $conversation_id =
        isset(
            $data['conversation_id']
        )
        ? (int)
            $data['conversation_id']
        : 0;


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    if ($conversation_id <= 0) {

        message_controller_error(
            'Destination conversation ID is required.',
            422
        );
    }


    $result =
        message_service_forward(
            $message_id,
            $conversation_id,
            $user_id
        );


    if (!$result) {

        message_controller_error(
            'Unable to forward message.',
            500
        );
    }


    message_controller_success(
        $result,
        'Message forwarded successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Attachments
|--------------------------------------------------------------------------
*/

function message_controller_attachments(
    int $message_id = 0
): void {

    $user_id =
        message_controller_user_id();

    if (!$user_id) {

        message_controller_error(
            'Authentication required.',
            401
        );
    }


    $data =
        message_controller_request_data();


    if ($message_id <= 0) {

        $message_id =
            isset(
                $data['message_id']
            )
            ? (int)
                $data['message_id']
            : 0;
    }


    if ($message_id <= 0) {

        message_controller_error(
            'Message ID is required.',
            422
        );
    }


    $result =
        message_service_attachments(
            $message_id,
            $user_id
        );


    if ($result === false) {

        message_controller_error(
            'Unable to retrieve message attachments.',
            500
        );
    }


    message_controller_success(
        $result,
        'Message attachments retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Dispatcher
|--------------------------------------------------------------------------
*/

function message_controller_dispatch(
    string $action,
    int $message_id = 0,
    int $conversation_id = 0
): void {

    switch (
        strtolower(
            trim($action)
        )
    ) {

        case 'index':
        case 'list':
            message_controller_index(
                $conversation_id
            );
            break;


        case 'show':
        case 'view':
            message_controller_show(
                $message_id
            );
            break;


        case 'create':
        case 'store':
        case 'send':
            message_controller_create();
            break;


        case 'update':
        case 'edit':
            message_controller_update(
                $message_id
            );
            break;


        case 'delete':
        case 'destroy':
            message_controller_delete(
                $message_id
            );
            break;


        case 'reply':
            message_controller_reply(
                $message_id
            );
            break;


        case 'forward':
            message_controller_forward();
            break;


        case 'mark_read':
            message_controller_mark_read(
                $message_id
            );
            break;


        case 'conversation_read':
        case 'mark_conversation_read':
            message_controller_mark_conversation_read(
                $conversation_id
            );
            break;


        case 'unread_count':
            message_controller_unread_count();
            break;


        case 'search':
            message_controller_search();
            break;


        case 'attachments':
            message_controller_attachments(
                $message_id
            );
            break;


        default:

            message_controller_error(
                'Unknown message action.',
                404
            );
    }
}
