<?php

/**
 * MASAR - Notification Dispatcher
 *
 * Responsible for dispatching notifications
 * through the available notification channels.
 *
 * Service
 *     ↓
 * Dispatcher
 *     ↓
 * Channels
 *
 * Supported channels:
 * - database
 * - email
 *
 * The dispatcher does not contain business rules
 * related to users or notification ownership.
 */


/*
|--------------------------------------------------------------------------
| Channel Dependencies
|--------------------------------------------------------------------------
*/

$database_channel =
    __DIR__ .
    '/../channels/database_channel.php';


$email_channel =
    __DIR__ .
    '/../channels/email_channel.php';


if (file_exists($database_channel)) {
    require_once $database_channel;
}


if (file_exists($email_channel)) {
    require_once $email_channel;
}


/*
|--------------------------------------------------------------------------
| Notification Dispatcher
|--------------------------------------------------------------------------
*/

class NotificationDispatcher
{
    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    |
    | Dispatch a notification through the
    | requested channels.
    |
    */

    public function dispatch(
        array $notification,
        array $options = []
    ): array {

        $results = [];


        /*
         * Default channel.
         */

        $channels =
            $options['channels']
            ?? $options['channel']
            ?? [
                'database'
            ];


        if (
            is_string($channels)
        ) {

            $channels = [
                $channels
            ];
        }


        if (
            !is_array($channels)
        ) {

            $channels = [
                'database'
            ];
        }


        /*
         * Remove duplicate channels.
         */

        $channels =
            array_values(
                array_unique(
                    array_map(
                        'strtolower',
                        $channels
                    )
                )
            );


        foreach (
            $channels
            as $channel
        ) {

            $results[$channel] =
                $this->dispatchChannel(
                    $channel,
                    $notification,
                    $options
                );
        }


        return $results;
    }


    /*
    |--------------------------------------------------------------------------
    | Dispatch Single Channel
    |--------------------------------------------------------------------------
    */

    public function dispatchChannel(
        string $channel,
        array $notification,
        array $options = []
    ): bool|array {

        $channel =
            strtolower(
                trim($channel)
            );


        switch ($channel) {

            /*
             * Database notification.
             */

            case 'database':

            case 'db':

                return $this->database(
                    $notification,
                    $options
                );


            /*
             * Email notification.
             */

            case 'email':

            case 'mail':

                return $this->email(
                    $notification,
                    $options
                );


            default:

                return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Database Channel
    |--------------------------------------------------------------------------
    */

    public function database(
        array $notification,
        array $options = []
    ): bool|array {

        /*
         * Prefer the dedicated channel class.
         */

        if (
            class_exists(
                'DatabaseChannel'
            )
        ) {

            try {

                $channel =
                    new DatabaseChannel();


                if (
                    method_exists(
                        $channel,
                        'send'
                    )
                ) {

                    return
                        $channel->send(
                            $notification,
                            $options
                        );
                }


                if (
                    method_exists(
                        $channel,
                        'handle'
                    )
                ) {

                    return
                        $channel->handle(
                            $notification,
                            $options
                        );
                }

            } catch (Throwable $e) {

                return false;
            }
        }


        /*
         * Function-based fallback.
         */

        if (
            function_exists(
                'database_channel_send'
            )
        ) {

            try {

                return
                    database_channel_send(
                        $notification,
                        $options
                    );

            } catch (Throwable $e) {

                return false;
            }
        }


        /*
         * The notification service normally
         * creates the database notification.
         *
         * Therefore, when no dedicated channel
         * implementation exists, consider the
         * database dispatch successful if the
         * notification already has an ID.
         */

        return !empty(
            $notification['id']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Email Channel
    |--------------------------------------------------------------------------
    */

    public function email(
        array $notification,
        array $options = []
    ): bool|array {

        /*
         * Respect an explicit disable flag.
         */

        if (
            isset(
                $options['email']
            ) &&
            $options['email'] === false
        ) {
            return false;
        }


        /*
         * Prefer the dedicated email channel.
         */

        if (
            class_exists(
                'EmailChannel'
            )
        ) {

            try {

                $channel =
                    new EmailChannel();


                if (
                    method_exists(
                        $channel,
                        'send'
                    )
                ) {

                    return
                        $channel->send(
                            $notification,
                            $options
                        );
                }


                if (
                    method_exists(
                        $channel,
                        'handle'
                    )
                ) {

                    return
                        $channel->handle(
                            $notification,
                            $options
                        );
                }

            } catch (Throwable $e) {

                return false;
            }
        }


        /*
         * Function-based fallback.
         */

        if (
            function_exists(
                'email_channel_send'
            )
        ) {

            try {

                return
                    email_channel_send(
                        $notification,
                        $options
                    );

            } catch (Throwable $e) {

                return false;
            }
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Broadcast
    |--------------------------------------------------------------------------
    |
    | Dispatch the same notification to
    | multiple users.
    |
    */

    public function broadcast(
        array $user_ids,
        array $notification,
        array $options = []
    ): array {

        $results = [];


        $user_ids =
            array_values(
                array_unique(
                    array_map(
                        'intval',
                        $user_ids
                    )
                )
            );


        foreach (
            $user_ids
            as $user_id
        ) {

            if ($user_id <= 0) {
                continue;
            }


            $user_notification =
                $notification;


            $user_notification['user_id'] =
                $user_id;


            $results[$user_id] =
                $this->dispatch(
                    $user_notification,
                    $options
                );
        }


        return $results;
    }


    /*
    |--------------------------------------------------------------------------
    | Dispatch By User
    |--------------------------------------------------------------------------
    */

    public function dispatchToUser(
        int $user_id,
        string $title,
        string $body = '',
        string $type = 'system',
        array $data = [],
        array $options = []
    ): array|false {

        if ($user_id <= 0) {
            return false;
        }


        $notification = [
            'user_id' =>
                $user_id,

            'title' =>
                trim($title),

            'body' =>
                trim($body),

            'type' =>
                trim($type),

            'data' =>
                $data
        ];


        return $this->dispatch(
            $notification,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Channels
    |--------------------------------------------------------------------------
    */

    public function resolveChannels(
        array $notification,
        array $options = []
    ): array {

        if (
            isset(
                $options['channels']
            )
        ) {

            $channels =
                $options['channels'];

        } elseif (
            isset(
                $options['channel']
            )
        ) {

            $channels = [
                $options['channel']
            ];

        } else {

            $channels = [
                'database'
            ];
        }


        if (
            is_string($channels)
        ) {

            $channels = [
                $channels
            ];
        }


        if (
            !is_array($channels)
        ) {

            return [
                'database'
            ];
        }


        return array_values(
            array_unique(
                array_map(
                    'strtolower',
                    array_filter(
                        array_map(
                            'trim',
                            $channels
                        )
                    )
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Channel Availability
    |--------------------------------------------------------------------------
    */

    public function channelAvailable(
        string $channel
    ): bool {

        $channel =
            strtolower(
                trim($channel)
            );


        switch ($channel) {

            case 'database':

            case 'db':

                return
                    class_exists(
                        'DatabaseChannel'
                    ) ||
                    function_exists(
                        'database_channel_send'
                    );


            case 'email':

            case 'mail':

                return
                    class_exists(
                        'EmailChannel'
                    ) ||
                    function_exists(
                        'email_channel_send'
                    );


            default:

                return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Available Channels
    |--------------------------------------------------------------------------
    */

    public function availableChannels(): array
    {
        $channels = [];


        if (
            $this->channelAvailable(
                'database'
            )
        ) {

            $channels[] =
                'database';
        }


        if (
            $this->channelAvailable(
                'email'
            )
        ) {

            $channels[] =
                'email';
        }


        return $channels;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function notification_dispatcher_dispatch(
    array $notification,
    array $options = []
): array {

    return
        (new NotificationDispatcher())
            ->dispatch(
                $notification,
                $options
            );
}


function notification_dispatcher_dispatch_channel(
    string $channel,
    array $notification,
    array $options = []
): bool|array {

    return
        (new NotificationDispatcher())
            ->dispatchChannel(
                $channel,
                $notification,
                $options
            );
}


function notification_dispatcher_database(
    array $notification,
    array $options = []
): bool|array {

    return
        (new NotificationDispatcher())
            ->database(
                $notification,
                $options
            );
}


function notification_dispatcher_email(
    array $notification,
    array $options = []
): bool|array {

    return
        (new NotificationDispatcher())
            ->email(
                $notification,
                $options
            );
}


function notification_dispatcher_broadcast(
    array $user_ids,
    array $notification,
    array $options = []
): array {

    return
        (new NotificationDispatcher())
            ->broadcast(
                $user_ids,
                $notification,
                $options
            );
}
