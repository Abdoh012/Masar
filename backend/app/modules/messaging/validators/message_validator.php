<?php

/**
 * MASAR - Message Validator
 *
 * Validation layer for messaging operations.
 */


/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

if (!defined('MESSAGE_MAX_LENGTH')) {
    define(
        'MESSAGE_MAX_LENGTH',
        5000
    );
}


/*
|--------------------------------------------------------------------------
| Basic Helpers
|--------------------------------------------------------------------------
*/

/**
 * Return validation result.
 */
function message_validator_result(
    bool $valid,
    array $errors = [],
    array $data = []
): array {

    return [
        'valid'  => $valid,
        'errors' => $errors,
        'data'   => $data
    ];
}


/**
 * Normalize a string.
 */
function message_validator_string(
    mixed $value
): string {

    if (
        !is_string($value) &&
        !is_numeric($value)
    ) {
        return '';
    }

    return trim(
        (string) $value
    );
}


/**
 * Validate positive integer.
 */
function message_validator_positive_int(
    mixed $value
): bool {

    return filter_var(
        $value,
        FILTER_VALIDATE_INT
    ) !== false
    &&
    (int) $value > 0;
}


/*
|--------------------------------------------------------------------------
| Message Body
|--------------------------------------------------------------------------
*/

function message_validator_body(
    mixed $body,
    bool $required = true
): array {

    $body =
        message_validator_string(
            $body
        );


    $errors = [];


    if (
        $required &&
        $body === ''
    ) {

        $errors['body'] =
            'Message body is required.';
    }


    if (
        $body !== '' &&
        mb_strlen($body) >
        MESSAGE_MAX_LENGTH
    ) {

        $errors['body'] =
            'Message body is too long.';
    }


    return [
        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

        'value' =>
            $body
    ];
}


/*
|--------------------------------------------------------------------------
| Conversation ID
|--------------------------------------------------------------------------
*/

function message_validator_conversation_id(
    mixed $conversation_id
): array {

    $errors = [];


    if (
        !message_validator_positive_int(
            $conversation_id
        )
    ) {

        $errors['conversation_id'] =
            'A valid conversation ID is required.';
    }


    return [
        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

        'value' =>
            (int) $conversation_id
    ];
}


/*
|--------------------------------------------------------------------------
| Sender ID
|--------------------------------------------------------------------------
*/

function message_validator_sender_id(
    mixed $sender_id
): array {

    $errors = [];


    if (
        !message_validator_positive_int(
            $sender_id
        )
    ) {

        $errors['sender_id'] =
            'A valid sender ID is required.';
    }


    return [
        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

        'value' =>
            (int) $sender_id
    ];
}


/*
|--------------------------------------------------------------------------
| Message Type
|--------------------------------------------------------------------------
*/

function message_validator_type(
    mixed $message_type
): array {

    $type =
        message_validator_string(
            $message_type
        );


    if ($type === '') {
        $type = 'text';
    }


    $allowed = [
        'text',
        'file',
        'image',
        'document',
        'system'
    ];


    $errors = [];


    if (
        !in_array(
            $type,
            $allowed,
            true
        )
    ) {

        $errors['message_type'] =
            'Invalid message type.';
    }


    return [
        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

        'value' =>
            $type
    ];
}


/*
|--------------------------------------------------------------------------
| Reply To ID
|--------------------------------------------------------------------------
*/

function message_validator_reply_to_id(
    mixed $reply_to_id
): array {

    $errors = [];


    if (
        $reply_to_id !== null &&
        $reply_to_id !== '' &&
        !message_validator_positive_int(
            $reply_to_id
        )
    ) {

        $errors['reply_to_id'] =
            'Invalid reply message ID.';
    }


    return [
        'valid' =>
            empty($errors),

        'errors' =>
            $errors,

        'value' =>
            (
                $reply_to_id === null ||
                $reply_to_id === ''
            )
            ? null
            : (int) $reply_to_id
    ];
}


/*
|--------------------------------------------------------------------------
| Create Message
|--------------------------------------------------------------------------
*/

function message_validator_create(
    array $data
): array {

    $errors = [];
    $clean = [];


    /*
     * Conversation.
     */

    $conversation =
        message_validator_conversation_id(
            $data['conversation_id']
            ?? null
        );


    if (!$conversation['valid']) {

        $errors =
            array_merge(
                $errors,
                $conversation['errors']
            );

    } else {

        $clean['conversation_id'] =
            $conversation['value'];
    }


    /*
     * Sender.
     */

    $sender =
        message_validator_sender_id(
            $data['sender_id']
            ?? null
        );


    if (!$sender['valid']) {

        $errors =
            array_merge(
                $errors,
                $sender['errors']
            );

    } else {

        $clean['sender_id'] =
            $sender['value'];
    }


    /*
     * Body.
     */

    $body =
        message_validator_body(
            $data['body']
            ?? null
        );


    if (!$body['valid']) {

        $errors =
            array_merge(
                $errors,
                $body['errors']
            );

    } else {

        $clean['body'] =
            $body['value'];
    }


    /*
     * Type.
     */

    $type =
        message_validator_type(
            $data['message_type']
            ?? 'text'
        );


    if (!$type['valid']) {

        $errors =
            array_merge(
                $errors,
                $type['errors']
            );

    } else {

        $clean['message_type'] =
            $type['value'];
    }


    /*
     * Reply.
     */

    $reply =
        message_validator_reply_to_id(
            $data['reply_to_id']
            ?? null
        );


    if (!$reply['valid']) {

        $errors =
            array_merge(
                $errors,
                $reply['errors']
            );

    } else {

        $clean['reply_to_id'] =
            $reply['value'];
    }


    return message_validator_result(
        empty($errors),
        $errors,
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Update Message
|--------------------------------------------------------------------------
*/

function message_validator_update(
    array $data
): array {

    $errors = [];
    $clean = [];


    /*
     * Body is optional during update,
     * but if supplied it must be valid.
     */

    if (
        array_key_exists(
            'body',
            $data
        )
    ) {

        $body =
            message_validator_body(
                $data['body'],
                false
            );


        if (!$body['valid']) {

            $errors =
                array_merge(
                    $errors,
                    $body['errors']
                );

        } else {

            $clean['body'] =
                $body['value'];
        }
    }


    /*
     * Type is optional.
     */

    if (
        array_key_exists(
            'message_type',
            $data
        )
    ) {

        $type =
            message_validator_type(
                $data['message_type']
            );


        if (!$type['valid']) {

            $errors =
                array_merge(
                    $errors,
                    $type['errors']
                );

        } else {

            $clean['message_type'] =
                $type['value'];
        }
    }


    /*
     * Do not allow an empty update.
     */

    if (
        empty($clean) &&
        empty($errors)
    ) {

        $errors['message'] =
            'At least one field is required.';
    }


    /*
     * Do not allow an explicitly
     * empty body on update.
     */

    if (
        array_key_exists(
            'body',
            $clean
        ) &&
        $clean['body'] === ''
    ) {

        $errors['body'] =
            'Message body cannot be empty.';
    }


    return message_validator_result(
        empty($errors),
        $errors,
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

function message_validator_delete(
    mixed $message_id,
    mixed $user_id
): array {

    $errors = [];


    if (
        !message_validator_positive_int(
            $message_id
        )
    ) {

        $errors['message_id'] =
            'A valid message ID is required.';
    }


    if (
        !message_validator_positive_int(
            $user_id
        )
    ) {

        $errors['user_id'] =
            'A valid user ID is required.';
    }


    return message_validator_result(
        empty($errors),
        $errors,
        [
            'message_id' =>
                (int) $message_id,

            'user_id' =>
                (int) $user_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Read Message
|--------------------------------------------------------------------------
*/

function message_validator_read(
    mixed $message_id,
    mixed $user_id
): array {

    $errors = [];


    if (
        !message_validator_positive_int(
            $message_id
        )
    ) {

        $errors['message_id'] =
            'A valid message ID is required.';
    }


    if (
        !message_validator_positive_int(
            $user_id
        )
    ) {

        $errors['user_id'] =
            'A valid user ID is required.';
    }


    return message_validator_result(
        empty($errors),
        $errors,
        [
            'message_id' =>
                (int) $message_id,

            'user_id' =>
                (int) $user_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Conversation Read
|--------------------------------------------------------------------------
*/

function message_validator_conversation_read(
    mixed $conversation_id,
    mixed $user_id
): array {

    $errors = [];


    if (
        !message_validator_positive_int(
            $conversation_id
        )
    ) {

        $errors['conversation_id'] =
            'A valid conversation ID is required.';
    }


    if (
        !message_validator_positive_int(
            $user_id
        )
    ) {

        $errors['user_id'] =
            'A valid user ID is required.';
    }


    return message_validator_result(
        empty($errors),
        $errors,
        [
            'conversation_id' =>
                (int) $conversation_id,

            'user_id' =>
                (int) $user_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

function message_validator_search(
    array $data
): array {

    $errors = [];
    $clean = [];


    $keyword =
        message_validator_string(
            $data['keyword']
            ?? ''
        );


    if ($keyword === '') {

        $errors['keyword'] =
            'Search keyword is required.';

    } elseif (
        mb_strlen($keyword) < 2
    ) {

        $errors['keyword'] =
            'Search keyword must contain at least 2 characters.';

    } elseif (
        mb_strlen($keyword) > 255
    ) {

        $errors['keyword'] =
            'Search keyword is too long.';

    } else {

        $clean['keyword'] =
            $keyword;
    }


    /*
     * Optional conversation.
     */

    if (
        isset(
            $data['conversation_id']
        ) &&
        $data['conversation_id'] !== ''
    ) {

        if (
            !message_validator_positive_int(
                $data['conversation_id']
            )
        ) {

            $errors['conversation_id'] =
                'Invalid conversation ID.';

        } else {

            $clean['conversation_id'] =
                (int)
                    $data['conversation_id'];
        }
    }


    /*
     * Optional sender.
     */

    if (
        isset(
            $data['sender_id']
        ) &&
        $data['sender_id'] !== ''
    ) {

        if (
            !message_validator_positive_int(
                $data['sender_id']
            )
        ) {

            $errors['sender_id'] =
                'Invalid sender ID.';

        } else {

            $clean['sender_id'] =
                (int)
                    $data['sender_id'];
        }
    }


    /*
     * Limit.
     */

    if (
        isset(
            $data['limit']
        )
    ) {

        $limit =
            filter_var(
                $data['limit'],
                FILTER_VALIDATE_INT
            );


        if (
            $limit === false ||
            $limit < 1 ||
            $limit > 100
        ) {

            $errors['limit'] =
                'Limit must be between 1 and 100.';

        } else {

            $clean['limit'] =
                $limit;
        }
    }


    return message_validator_result(
        empty($errors),
        $errors,
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function message_validator_pagination(
    array $data
): array {

    $errors = [];
    $clean = [];


    /*
     * Limit.
     */

    if (
        isset(
            $data['limit']
        )
    ) {

        $limit =
            filter_var(
                $data['limit'],
                FILTER_VALIDATE_INT
            );


        if (
            $limit === false ||
            $limit < 1 ||
            $limit > 100
        ) {

            $errors['limit'] =
                'Limit must be between 1 and 100.';

        } else {

            $clean['limit'] =
                $limit;
        }
    }


    /*
     * Offset.
     */

    if (
        isset(
            $data['offset']
        )
    ) {

        $offset =
            filter_var(
                $data['offset'],
                FILTER_VALIDATE_INT
            );


        if (
            $offset === false ||
            $offset < 0
        ) {

            $errors['offset'] =
                'Offset must be zero or greater.';

        } else {

            $clean['offset'] =
                $offset;
        }
    }


    /*
     * Before ID.
     */

    if (
        isset(
            $data['before_id']
        ) &&
        $data['before_id'] !== ''
    ) {

        if (
            !message_validator_positive_int(
                $data['before_id']
            )
        ) {

            $errors['before_id'] =
                'Invalid before message ID.';

        } else {

            $clean['before_id'] =
                (int)
                    $data['before_id'];
        }
    }


    /*
     * After ID.
     */

    if (
        isset(
            $data['after_id']
        ) &&
        $data['after_id'] !== ''
    ) {

        if (
            !message_validator_positive_int(
                $data['after_id']
            )
        ) {

            $errors['after_id'] =
                'Invalid after message ID.';

        } else {

            $clean['after_id'] =
                (int)
                    $data['after_id'];
        }
    }


    /*
     * Order.
     */

    if (
        isset(
            $data['order']
        )
    ) {

        $order =
            strtolower(
                message_validator_string(
                    $data['order']
                )
            );


        if (
            !in_array(
                $order,
                [
                    'asc',
                    'desc'
                ],
                true
            )
        ) {

            $errors['order'] =
                'Order must be asc or desc.';

        } else {

            $clean['order'] =
                $order;
        }
    }


    return message_validator_result(
        empty($errors),
        $errors,
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Attachment
|--------------------------------------------------------------------------
*/

function message_validator_attachment(
    array $data
): array {

    $errors = [];
    $clean = [];


    if (
        !message_validator_positive_int(
            $data['message_id']
            ?? null
        )
    ) {

        $errors['message_id'] =
            'A valid message ID is required.';

    } else {

        $clean['message_id'] =
            (int)
                $data['message_id'];
    }


    if (
        !message_validator_positive_int(
            $data['file_id']
            ?? null
        )
    ) {

        $errors['file_id'] =
            'A valid file ID is required.';

    } else {

        $clean['file_id'] =
            (int)
                $data['file_id'];
    }


    if (
        !message_validator_positive_int(
            $data['user_id']
            ?? null
        )
    ) {

        $errors['user_id'] =
            'A valid user ID is required.';

    } else {

        $clean['user_id'] =
            (int)
                $data['user_id'];
    }


    return message_validator_result(
        empty($errors),
        $errors,
        $clean
    );
}


/*
|--------------------------------------------------------------------------
| Sanitize Message Data
|--------------------------------------------------------------------------
*/

function message_validator_sanitize(
    array $data
): array {

    $clean = [];


    if (
        array_key_exists(
            'body',
            $data
        )
    ) {

        $clean['body'] =
            trim(
                (string)
                    $data['body']
            );
    }


    if (
        array_key_exists(
            'message_type',
            $data
        )
    ) {

        $clean['message_type'] =
            trim(
                (string)
                    $data['message_type']
            );
    }


    if (
        array_key_exists(
            'reply_to_id',
            $data
        )
    ) {

        $clean['reply_to_id'] =
            (
                $data['reply_to_id'] === null ||
                $data['reply_to_id'] === ''
            )
            ? null
            : (int)
                $data['reply_to_id'];
    }


    return $clean;
}


/*
|--------------------------------------------------------------------------
| Convenience Aliases
|--------------------------------------------------------------------------
*/

function validate_message_create(
    array $data
): array {

    return message_validator_create(
        $data
    );
}


function validate_message_update(
    array $data
): array {

    return message_validator_update(
        $data
    );
}


function validate_message_search(
    array $data
): array {

    return message_validator_search(
        $data
    );
}
