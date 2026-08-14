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
): array|false {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }


    /*
     * Verify user is a participant in the conversation.
     */

    $participant =
        message_repository_is_participant(
            $conversation_id,
            $user_id
        );


    if (!$participant) {
        return false;
    }


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
            return false;
        }
    }


    return message_repository_list(
        $conversation_id,
        $user_id,
        $filters
    );
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
) {

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
            'message' =>
                'Invalid sender.',
            'errors' => [
                'sender_id' =>
                    'A valid sender ID is required.'
            ]
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
            'message' =>
                'Conversation is required.',
            'errors' => [
                'conversation_id' =>
                    'A valid conversation ID is required.'
            ]
        ];
    }


    /*
     * Verify that sender belongs
     * to the conversation and check application authorization.
     */

    $conversation =
        conversation_repository_find_for_user(
            $conversation_id,
            $sender_id
        );


    if (!$conversation) {

        return [
            'message' =>
                'Conversation not found or you are not a participant.',
            'errors' => [
                'conversation_id' =>
                    'The conversation does not exist or you are not a participant.'
            ]
        ];
    }


    /*
     * If the conversation is linked to an application,
     * verify the sender is authorized for that application.
     */

    if (
        isset($conversation['application_id'])
        &&
        (int) $conversation['application_id'] > 0
    ) {

        $application_id =
            (int) $conversation['application_id'];


        /*
         * Check authorization based on sender role.
         */

        $authorized =
            conversation_service_application_participant(
                $application_id,
                $sender_id
            );


        if (!$authorized) {

            return [
                'message' =>
                    'You are not authorized to send messages to this conversation.',
                'errors' => [
                    'conversation_id' =>
                        'You must be the student or company associated with this accepted application.'
                ]
            ];
        }
    }


    /*
     * Message body.
     */

    $body =
        trim(
            (string) (
                $data['body']
                ?? $data['message']
                ?? $data['content']
                ?? ''
            )
        );


    if ($body === '') {

        return [
            'message' =>
                'Message content is required.',
            'errors' => [
                'body' =>
                    'Message content cannot be empty.'
            ]
        ];
    }


    /*
     * Verify that sender belongs
     * to the conversation.
     */

    $participant =
        message_repository_is_participant(
            $conversation_id,
            $sender_id
        );


    if (!$participant) {

        return [
            'message' =>
                'User is not a participant in this conversation.',
            'errors' => [
                'conversation_id' =>
                    'The authenticated user cannot send messages to this conversation.'
            ]
        ];
    }


    $message_data = [
        'conversation_id' =>
            $conversation_id,

        'sender_id' =>
            $sender_id,

        'body' =>
            $body
    ];


    /*
     * Optional reply reference.
     */

    if (
        isset(
            $data['reply_to_id']
        )
        &&
        (int)
            $data['reply_to_id'] > 0
    ) {

        $reply_to_id =
            (int)
                $data['reply_to_id'];


        $reply_message =
            message_repository_find_for_user(
                $reply_to_id,
                $sender_id
            );


        if (
            !$reply_message
            ||
            (
                isset(
                    $reply_message['conversation_id']
                )
                &&
                (int)
                    $reply_message['conversation_id']
                    !==
                    $conversation_id
            )
        ) {

            return [
                'message' =>
                    'Invalid reply message.',
                'errors' => [
                    'reply_to_id' =>
                        'The reply message does not belong to this conversation.'
                ]
            ];
        }


        $message_data['reply_to_id'] =
            $reply_to_id;
    }


    /*
     * Optional message type.
     */

    if (
        isset(
            $data['message_type']
        )
    ) {

        $message_data['message_type'] =
            trim(
                (string)
                    $data['message_type']
            );
    }


    /*
     * Create message.
     */

    $message =
        message_repository_create(
            $message_data
        );


    if (!$message) {
        return false;
    }


    /*
     * Attachments, if supported
     * by the repository layer.
     */

    if (
        isset(
            $data['attachment_ids']
        )
        &&
        is_array(
            $data['attachment_ids']
        )
    ) {

        foreach (
            $data['attachment_ids']
            as $attachment_id
        ) {

            $attachment_id =
                (int)
                    $attachment_id;


            if (
                $attachment_id <= 0
            ) {
                continue;
            }


            if (
                function_exists(
                    'message_repository_attach_file'
                )
            ) {

                message_repository_attach_file(
                    (int) $message['id'],
                    $attachment_id,
                    $sender_id
                );
            }
        }
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
