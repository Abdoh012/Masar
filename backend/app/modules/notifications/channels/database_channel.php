<?php

/**
 * MASAR - Database Notification Channel
 *
 * Responsible for delivering notifications
 * to the application's database channel.
 *
 * Dispatcher
 *     ↓
 * DatabaseChannel
 *     ↓
 * NotificationRepository
 */


/*
|--------------------------------------------------------------------------
| Repository Dependency
|--------------------------------------------------------------------------
*/

$repository_file =
    __DIR__ .
    '/../repositories/notification_repository.php';


if (file_exists($repository_file)) {
    require_once $repository_file;
}


/*
|--------------------------------------------------------------------------
| Database Channel
|--------------------------------------------------------------------------
*/

class DatabaseChannel
{
    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    */

    protected function repository(): ?NotificationRepository
    {
        if (
            class_exists(
                'NotificationRepository'
            )
        ) {

            return new NotificationRepository();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Send
    |--------------------------------------------------------------------------
    |
    | Store the notification in the database.
    |
    */

    public function send(
        array $notification,
        array $options = []
    ): array|false {

        $repository =
            $this->repository();


        if (
            $repository === null
        ) {

            return false;
        }


        /*
         * If the notification already exists
         * and has an ID, do not create a duplicate.
         */

        if (
            !empty(
                $notification['id']
            )
        ) {

            $existing =
                $repository->find(
                    (int)
                    $notification['id']
                );


            if ($existing) {
                return $existing;
            }
        }


        /*
         * Normalize notification payload.
         */

        $payload =
            $this->normalize(
                $notification
            );


        if (
            $payload === false
        ) {

            return false;
        }


        /*
         * Persist notification.
         */

        return
            $repository->create(
                $payload
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Alias
    |--------------------------------------------------------------------------
    */

    public function handle(
        array $notification,
        array $options = []
    ): array|false {

        return $this->send(
            $notification,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deliver Alias
    |--------------------------------------------------------------------------
    */

    public function deliver(
        array $notification,
        array $options = []
    ): array|false {

        return $this->send(
            $notification,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize
    |--------------------------------------------------------------------------
    */

    protected function normalize(
        array $notification
    ): array|false {

        $user_id =
            (int) (
                $notification['user_id']
                ?? 0
            );


        if ($user_id <= 0) {
            return false;
        }


        $title =
            trim(
                (string) (
                    $notification['title']
                    ?? ''
                )
            );


        $body =
            trim(
                (string) (
                    $notification['body']
                    ?? ''
                )
            );


        /*
         * A notification must contain at least
         * a title or body.
         */

        if (
            $title === '' &&
            $body === ''
        ) {

            return false;
        }


        $type =
            trim(
                (string) (
                    $notification['type']
                    ?? 'system'
                )
            );


        if ($type === '') {
            $type = 'system';
        }


        $data =
            $notification['data']
            ?? null;


        /*
         * Keep metadata as an array when possible.
         * The repository handles JSON serialization.
         */

        if (
            is_string($data) &&
            $data !== ''
        ) {

            $decoded =
                json_decode(
                    $data,
                    true
                );


            if (
                json_last_error() === JSON_ERROR_NONE
            ) {

                $data = $decoded;
            }
        }


        return [
            'user_id' =>
                $user_id,

            'title' =>
                $title,

            'body' =>
                $body,

            'type' =>
                $type,

            'data' =>
                $data
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Is Available
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return
            class_exists(
                'NotificationRepository'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Channel Name
    |--------------------------------------------------------------------------
    */

    public function name(): string
    {
        return 'database';
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function database_channel_send(
    array $notification,
    array $options = []
): array|false {

    return
        (new DatabaseChannel())
            ->send(
                $notification,
                $options
            );
}


function database_channel_handle(
    array $notification,
    array $options = []
): array|false {

    return
        (new DatabaseChannel())
            ->handle(
                $notification,
                $options
            );
}


function database_channel_deliver(
    array $notification,
    array $options = []
): array|false {

    return
        (new DatabaseChannel())
            ->deliver(
                $notification,
                $options
            );
}
