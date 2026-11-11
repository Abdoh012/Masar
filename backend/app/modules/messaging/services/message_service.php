<?php

/**
 * MASAR - Message Service
 *
 * Business logic for messages.
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

require_once __DIR__ . '/../repositories/message_repository.php';
require_once __DIR__ . '/../repositories/conversation_repository.php';
require_once __DIR__ . '/../validators/message_validator.php';
require_once __DIR__ . '/../services/conversation_service.php';


/*
|--------------------------------------------------------------------------
| List Messages
|--------------------------------------------------------------------------
*/

function message_service_list(
    int $conversation_id,
    int $user_id,
    array $filters = []
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


    /*
     * Existence + participation + application authorization.
     * Reading remains available after withdrawal.
     */

    $access =
        conversation_service_access(
            $conversation_id,
            $user_id
        );


    if (empty($access['ok'])) {
        return $access;
    }


    try {

        $messages =
            message_repository_list(
                $conversation_id,
                $user_id,
                $filters
            );

    } catch (Throwable $exception) {

        if (function_exists('logger_exception')) {
            logger_exception($exception);
        }

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to retrieve messages.',
            'errors'  => [],
        ];
    }


    if ($messages === false) {

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to retrieve messages.',
            'errors'  => [],
        ];
    }


    return [
        'ok'   => true,
        'data' => $messages,
    ];
}


/*
|--------------------------------------------------------------------------
| Find Message
|--------------------------------------------------------------------------
*/

function message_service_find(
    int $message_id,
    int $user_id
): ?array {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {
        return null;
    }


    $message =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$message) {
        return null;
    }


    /*
     * Check application authorization if the
     * conversation is linked to an application.
     */

    $conversation =
        conversation_repository_find_for_user(
            $message['conversation_id'],
            $user_id
        );


    if (
        $conversation
        &&
        isset($conversation['application_id'])
        &&
        (int) $conversation['application_id'] > 0
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


    return $message;
}


/*
|--------------------------------------------------------------------------
| Create / Send Message
|--------------------------------------------------------------------------
*/

function message_service_create(
    array $data
): array|false {

    $sender_id =
        isset($data['sender_id'])
        ? (int) $data['sender_id']
        : (
            isset($data['user_id'])
            ? (int) $data['user_id']
            : 0
        );


    if ($sender_id <= 0) {

        return [
            'ok'      => false,
            'status'  => 401,
            'message' => 'Authentication required.',
            'errors'  => [],
        ];
    }


    $conversation_id =
        isset(
            $data['conversation_id']
        )
        ? (int)
            $data['conversation_id']
        : 0;


    if ($conversation_id <= 0) {

        return [
            'ok'      => false,
            'status'  => 422,
            'message' => 'Conversation is required.',
            'errors'  => [
                'conversation_id' =>
                    'A valid conversation ID is required.',
            ],
        ];
    }


    /*
     * Existence + participation + application authorization.
     */

    $access =
        conversation_service_access(
            $conversation_id,
            $sender_id
        );


    if (empty($access['ok'])) {
        return $access;
    }


    $conversation =
        $access['conversation'];


    /*
     * Withdrawal policy: checked against the database on every send attempt,
     * never against a cached status. Reading is unaffected - this branch only
     * guards new messages.
     */

    $application_id =
        (int) (
            $conversation['application_id']
            ?? 0
        );


    $blocked_reason =
        conversation_service_messaging_blocked_reason(
            $application_id
        );


    if ($blocked_reason !== null) {

        return [
            'ok'      => false,
            'status'  => 403,
            'message' => $blocked_reason,
            'errors'  => [
                'application_id' =>
                    $blocked_reason,
            ],
        ];
    }


    /*
     * Body / unsupported field validation.
     */

    $payload =
        message_validator_create_payload(
            $data
        );


    if (!$payload['valid']) {

        return [
            'ok'      => false,
            'status'  => 422,
            'message' => 'Validation failed.',
            'errors'  => $payload['errors'],
        ];
    }


    $message_data = [
        'conversation_id' =>
            $conversation_id,

        'sender_id' =>
            $sender_id,

        'body' =>
            $payload['value']['body'],
    ];


    try {

        $message =
            message_repository_create(
                $message_data
            );

    } catch (Throwable $exception) {

        /*
         * The repository already logged the database diagnostics and rolled
         * the transaction back. Only the safe response is produced here.
         */

        if (function_exists('logger_exception')) {
            logger_exception($exception);
        }

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to send message.',
            'errors'  => [],
        ];
    }


    if (!$message) {

        return [
            'ok'      => false,
            'status'  => 500,
            'message' => 'Unable to send message.',
            'errors'  => [],
        ];
    }


    /*
     * Return the complete message.
     */

    $message_id =
        isset(
            $message['id']
        )
        ? (int)
            $message['id']
        : 0;


    if ($message_id > 0) {

        $complete =
            message_repository_find_for_user(
                $message_id,
                $sender_id
            );


        if ($complete) {
            return $complete;
        }
    }


    return $message;
}


/*
|--------------------------------------------------------------------------
| Update Message
|--------------------------------------------------------------------------
*/

function message_service_update(
    int $message_id,
    int $user_id,
    array $data
) {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {

        return [
            'message' =>
                'Invalid message or user.',
            'errors' => [
                'message_id' =>
                    'A valid message ID is required.'
            ]
        ];
    }


    $message =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$message) {
        return false;
    }


    /*
     * Only the sender should be
     * allowed to edit the message.
     */

    $sender_id =
        isset(
            $message['sender_id']
        )
        ? (int)
            $message['sender_id']
        : 0;


    if (
        $sender_id > 0
        &&
        $sender_id !== $user_id
    ) {

        return [
            'message' =>
                'You cannot edit this message.',
            'errors' => [
                'message_id' =>
                    'Only the message sender can edit it.'
            ]
        ];
    }


    $update = [];


    $body =
        array_key_exists(
            'body',
            $data
        )
        ? $data['body']
        : (
            array_key_exists(
                'message',
                $data
            )
            ? $data['message']
            : (
                array_key_exists(
                    'content',
                    $data
                )
                ? $data['content']
                : null
            )
        );


    if ($body !== null) {

        $body =
            trim(
                (string)
                    $body
            );


        if ($body === '') {

            return [
                'message' =>
                    'Message content cannot be empty.',
                'errors' => [
                    'body' =>
                        'Message content cannot be empty.'
                ]
            ];
        }


        $update['body'] =
            $body;
    }


    if (
        array_key_exists(
            'message_type',
            $data
        )
    ) {

        $update['message_type'] =
            trim(
                (string)
                    $data['message_type']
            );
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
        message_repository_update(
            $message_id,
            $user_id,
            $update
        );


    if (!$updated) {
        return false;
    }


    return message_repository_find_for_user(
        $message_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

function message_service_delete(
    int $message_id,
    int $user_id
): bool {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $message =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$message) {
        return false;
    }


    /*
     * Only the sender can delete
     * the message.
     */

    $sender_id =
        isset(
            $message['sender_id']
        )
        ? (int)
            $message['sender_id']
        : 0;


    if (
        $sender_id > 0
        &&
        $sender_id !== $user_id
    ) {

        return false;
    }


    return (bool)
        message_repository_delete(
            $message_id,
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Mark Message As Read
|--------------------------------------------------------------------------
*/

function message_service_mark_read(
    int $message_id,
    int $user_id
): bool {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $message =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$message) {
        return false;
    }


    return (bool)
        message_repository_mark_read(
            $message_id,
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Mark Conversation Messages As Read
|--------------------------------------------------------------------------
*/

function message_service_mark_conversation_read(
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


    $participant =
        message_repository_is_participant(
            $conversation_id,
            $user_id
        );


    if (!$participant) {
        return false;
    }


    return (bool)
        message_repository_mark_conversation_read(
            $conversation_id,
            $user_id
        );
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
*/

function message_service_unread_count(
    int $user_id,
    ?int $conversation_id = null
): int|false {

    if ($user_id <= 0) {
        return false;
    }


    if (
        $conversation_id !== null
        &&
        $conversation_id <= 0
    ) {

        return false;
    }


    return (int)
        message_repository_unread_count(
            $user_id,
            $conversation_id
        );
}


/*
|--------------------------------------------------------------------------
| Search Messages
|--------------------------------------------------------------------------
*/

function message_service_search(
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


    return message_repository_search(
        $user_id,
        $keyword,
        $filters
    );
}


/*
|--------------------------------------------------------------------------
| Reply To Message
|--------------------------------------------------------------------------
*/

function message_service_reply(
    int $message_id,
    int $user_id,
    string $body,
    array $extra = []
) {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $original =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$original) {
        return false;
    }


    $conversation_id =
        isset(
            $original['conversation_id']
        )
        ? (int)
            $original['conversation_id']
        : 0;


    if ($conversation_id <= 0) {
        return false;
    }


    $data =
        array_merge(
            $extra,
            [
                'conversation_id' =>
                    $conversation_id,

                'sender_id' =>
                    $user_id,

                'user_id' =>
                    $user_id,

                'body' =>
                    trim($body),

                'reply_to_id' =>
                    $message_id
            ]
        );


    return message_service_create(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Forward Message
|--------------------------------------------------------------------------
*/

function message_service_forward(
    int $message_id,
    int $conversation_id,
    int $user_id
) {

    if (
        $message_id <= 0
        ||
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    /*
     * Original message must be
     * accessible to the current user.
     */

    $original =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$original) {
        return false;
    }


    /*
     * User must belong to the
     * destination conversation.
     */

    $participant =
        message_repository_is_participant(
            $conversation_id,
            $user_id
        );


    if (!$participant) {
        return false;
    }


    $body =
        isset(
            $original['body']
        )
        ? trim(
            (string)
                $original['body']
        )
        : '';


    if ($body === '') {
        return false;
    }


    return message_service_create(
        [
            'conversation_id' =>
                $conversation_id,

            'sender_id' =>
                $user_id,

            'user_id' =>
                $user_id,

            'body' =>
                $body,

            'message_type' =>
                'forwarded'
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Message Attachments
|--------------------------------------------------------------------------
*/

function message_service_attachments(
    int $message_id,
    int $user_id
): array|false {

    if (
        $message_id <= 0
        ||
        $user_id <= 0
    ) {

        return false;
    }


    $message =
        message_repository_find_for_user(
            $message_id,
            $user_id
        );


    if (!$message) {
        return false;
    }


    return message_repository_attachments(
        $message_id,
        $user_id
    );
}
