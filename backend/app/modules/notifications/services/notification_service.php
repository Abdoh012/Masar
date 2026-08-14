<?php

/**
 * MASAR - Notification Service
 *
 * Business logic layer for notifications.
 *
 * Controller
 *     ↓
 * Service
 *     ↓
 * Repository
 */


/*
|--------------------------------------------------------------------------
| Dependencies
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
| Notification Service Class
|--------------------------------------------------------------------------
*/

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Repository Resolver
    |--------------------------------------------------------------------------
    */

    protected function repository(): mixed
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
    | List Notifications
    |--------------------------------------------------------------------------
    */

    public function list(
        int $user_id,
        array $filters = []
    ): array {

        if ($user_id <= 0) {
            return [];
        }


        $repository =
            $this->repository();


        /*
         * Class-based repository.
         */

        if (
            $repository !== null &&
            method_exists(
                $repository,
                'list'
            )
        ) {

            return (array)
                $repository->list(
                    $user_id,
                    $filters
                );
        }


        /*
         * Function-based repository.
         */

        if (
            function_exists(
                'notification_repository_list'
            )
        ) {

            return (array)
                notification_repository_list(
                    $user_id,
                    $filters
                );
        }


        if (
            function_exists(
                'notification_repository_get_user_notifications'
            )
        ) {

            return (array)
                notification_repository_get_user_notifications(
                    $user_id,
                    $filters
                );
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Get User Notifications
    |--------------------------------------------------------------------------
    */

    public function getUserNotifications(
        int $user_id,
        array $filters = []
    ): array {

        return $this->list(
            $user_id,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find Notification
    |--------------------------------------------------------------------------
    */

    public function find(
        int $notification_id,
        int $user_id
    ): ?array {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {
            return null;
        }


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'findForUser'
            )
        ) {

            $result =
                $repository->findForUser(
                    $notification_id,
                    $user_id
                );


            return
                $result !== false
                    ? $result
                    : null;
        }


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'find'
            )
        ) {

            $result =
                $repository->find(
                    $notification_id
                );


            if (
                !$result ||
                (int) (
                    $result['user_id']
                    ?? 0
                ) !== $user_id
            ) {
                return null;
            }


            return $result;
        }


        if (
            function_exists(
                'notification_repository_find_for_user'
            )
        ) {

            $result =
                notification_repository_find_for_user(
                    $notification_id,
                    $user_id
                );


            return
                $result !== false
                    ? $result
                    : null;
        }


        if (
            function_exists(
                'notification_repository_find'
            )
        ) {

            $result =
                notification_repository_find(
                    $notification_id
                );


            if (
                !$result ||
                (int) (
                    $result['user_id']
                    ?? 0
                ) !== $user_id
            ) {
                return null;
            }


            return $result;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Notification
    |--------------------------------------------------------------------------
    */

    public function get(
        int $notification_id,
        int $user_id
    ): ?array {

        return $this->find(
            $notification_id,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark As Read
    |--------------------------------------------------------------------------
    */

    public function markAsRead(
        int $notification_id,
        int $user_id
    ): bool|array {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {
            return false;
        }


        /*
         * Verify ownership before changing
         * notification state.
         */

        $notification =
            $this->find(
                $notification_id,
                $user_id
            );


        if (!$notification) {
            return false;
        }


        /*
         * Already read.
         */

        if (
            !empty(
                $notification['read_at']
            )
        ) {
            return true;
        }


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'markAsRead'
            )
        ) {

            return
                (bool)
                    $repository->markAsRead(
                        $notification_id,
                        $user_id
                    );
        }


        if (
            function_exists(
                'notification_repository_mark_as_read'
            )
        ) {

            return
                (bool)
                    notification_repository_mark_as_read(
                        $notification_id,
                        $user_id
                    );
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Read Alias
    |--------------------------------------------------------------------------
    */

    public function read(
        int $notification_id,
        int $user_id
    ): bool|array {

        return $this->markAsRead(
            $notification_id,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */

    public function markAllAsRead(
        int $user_id
    ): bool|array {

        if ($user_id <= 0) {
            return false;
        }


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'markAllAsRead'
            )
        ) {

            return
                (bool)
                    $repository->markAllAsRead(
                        $user_id
                    );
        }


        if (
            function_exists(
                'notification_repository_mark_all_as_read'
            )
        ) {

            return
                (bool)
                    notification_repository_mark_all_as_read(
                        $user_id
                    );
        }


        /*
         * Fallback:
         * Fetch unread notifications and
         * mark them one by one.
         */

        $notifications =
            $this->list(
                $user_id,
                [
                    'unread' =>
                        true
                ]
            );


        $success = true;


        foreach (
            $notifications
            as $notification
        ) {

            $notification_id =
                (int) (
                    $notification['id']
                    ?? 0
                );


            if (
                $notification_id <= 0
            ) {
                continue;
            }


            if (
                !$this->markAsRead(
                    $notification_id,
                    $user_id
                )
            ) {

                $success = false;
            }
        }


        return $success;
    }


    /*
    |--------------------------------------------------------------------------
    | Read All Alias
    |--------------------------------------------------------------------------
    */

    public function readAll(
        int $user_id
    ): bool|array {

        return $this->markAllAsRead(
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Unread Count
    |--------------------------------------------------------------------------
    */

    public function unreadCount(
        int $user_id
    ): int {

        if ($user_id <= 0) {
            return 0;
        }


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'unreadCount'
            )
        ) {

            return (int)
                $repository->unreadCount(
                    $user_id
                );
        }


        if (
            function_exists(
                'notification_repository_unread_count'
            )
        ) {

            return (int)
                notification_repository_unread_count(
                    $user_id
                );
        }


        /*
         * Fallback.
         */

        $notifications =
            $this->list(
                $user_id,
                [
                    'unread' =>
                        true
                ]
            );


        return count(
            $notifications
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Unread Count Alias
    |--------------------------------------------------------------------------
    */

    public function getUnreadCount(
        int $user_id
    ): int {

        return $this->unreadCount(
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Notification
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $notification_id,
        int $user_id
    ): bool|array {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {
            return false;
        }


        /*
         * Ownership check.
         */

        $notification =
            $this->find(
                $notification_id,
                $user_id
            );


        if (!$notification) {
            return false;
        }


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'delete'
            )
        ) {

            return
                (bool)
                    $repository->delete(
                        $notification_id,
                        $user_id
                    );
        }


        if (
            function_exists(
                'notification_repository_delete'
            )
        ) {

            return
                (bool)
                    notification_repository_delete(
                        $notification_id,
                        $user_id
                    );
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Alias
    |--------------------------------------------------------------------------
    */

    public function remove(
        int $notification_id,
        int $user_id
    ): bool|array {

        return $this->delete(
            $notification_id,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Notification
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data
    ): array|false {

        $user_id =
            (int) (
                $data['user_id']
                ?? 0
            );


        $title =
            trim(
                (string) (
                    $data['title']
                    ?? ''
                )
            );


        $body =
            trim(
                (string) (
                    $data['body']
                    ?? ''
                )
            );


        if (
            $user_id <= 0 ||
            (
                $title === '' &&
                $body === ''
            )
        ) {
            return false;
        }


        $payload = [
            'user_id' =>
                $user_id,

            'title' =>
                $title,

            'body' =>
                $body,

            'type' =>
                trim(
                    (string) (
                        $data['type']
                        ?? 'system'
                    )
                ),

            'data' =>
                $data['data']
                ?? null
        ];


        $repository =
            $this->repository();


        if (
            $repository !== null &&
            method_exists(
                $repository,
                'create'
            )
        ) {

            return
                $repository->create(
                    $payload
                );
        }


        if (
            function_exists(
                'notification_repository_create'
            )
        ) {

            return
                notification_repository_create(
                    $payload
                );
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Send Notification
    |--------------------------------------------------------------------------
    |
    | Creates the database notification and,
    | when available, dispatches it through
    | the notification dispatcher.
    |
    */

    public function send(
        array $data
    ): array|false {

        $notification =
            $this->create(
                $data
            );


        if (
            $notification === false
        ) {
            return false;
        }


        /*
         * Dispatcher is optional here.
         * The dispatcher is responsible for
         * email/external channels.
         */

        if (
            class_exists(
                'NotificationDispatcher'
            )
        ) {

            try {

                $dispatcher =
                    new NotificationDispatcher();


                if (
                    method_exists(
                        $dispatcher,
                        'dispatch'
                    )
                ) {

                    $dispatcher->dispatch(
                        $notification,
                        $data
                    );
                }

            } catch (Throwable $e) {

                /*
                 * The notification itself has
                 * already been stored successfully.
                 */
            }
        }


        return $notification;
    }


    /*
    |--------------------------------------------------------------------------
    | Notify User
    |--------------------------------------------------------------------------
    */

    public function notifyUser(
        int $user_id,
        string $title,
        string $body = '',
        string $type = 'system',
        array $data = []
    ): array|false {

        if ($user_id <= 0) {
            return false;
        }


        return $this->send(
            [
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
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notify Multiple Users
    |--------------------------------------------------------------------------
    */

    public function notifyUsers(
        array $user_ids,
        string $title,
        string $body = '',
        string $type = 'system',
        array $data = []
    ): array {

        $results = [];


        /*
         * Remove duplicates and invalid IDs.
         */

        $user_ids =
            array_unique(
                array_map(
                    'intval',
                    $user_ids
                )
            );


        foreach (
            $user_ids
            as $user_id
        ) {

            if ($user_id <= 0) {
                continue;
            }


            $result =
                $this->notifyUser(
                    $user_id,
                    $title,
                    $body,
                    $type,
                    $data
                );


            if (
                $result !== false
            ) {

                $results[] =
                    $result;
            }
        }


        return $results;
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Preferences
    |--------------------------------------------------------------------------
    */

    public function shouldNotify(
        int $user_id,
        string $channel = 'database'
    ): bool {

        if ($user_id <= 0) {
            return false;
        }


        /*
         * If a preference service exists,
         * delegate to it.
         */

        if (
            class_exists(
                'NotificationPreferenceService'
            )
        ) {

            try {

                $service =
                    new NotificationPreferenceService();


                if (
                    method_exists(
                        $service,
                        'isEnabled'
                    )
                ) {

                    return (bool)
                        $service->isEnabled(
                            $user_id,
                            $channel
                        );
                }

            } catch (Throwable $e) {

                /*
                 * Fall through to default.
                 */
            }
        }


        /*
         * Database notifications are enabled
         * by default.
         */

        return true;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
|
| These wrappers allow the rest of the
| application to use the service without
| depending directly on the class name.
|
*/


function notification_service_list(
    int $user_id,
    array $filters = []
): array {

    return (new NotificationService())
        ->list(
            $user_id,
            $filters
        );
}


function notification_service_get_user_notifications(
    int $user_id,
    array $filters = []
): array {

    return (new NotificationService())
        ->getUserNotifications(
            $user_id,
            $filters
        );
}


function notification_service_find(
    int $notification_id,
    int $user_id
): ?array {

    return (new NotificationService())
        ->find(
            $notification_id,
            $user_id
        );
}


function notification_service_mark_as_read(
    int $notification_id,
    int $user_id
): bool|array {

    return (new NotificationService())
        ->markAsRead(
            $notification_id,
            $user_id
        );
}


function notification_service_mark_all_as_read(
    int $user_id
): bool|array {

    return (new NotificationService())
        ->markAllAsRead(
            $user_id
        );
}


function notification_service_unread_count(
    int $user_id
): int {

    return (new NotificationService())
        ->unreadCount(
            $user_id
        );
}


function notification_service_delete(
    int $notification_id,
    int $user_id
): bool|array {

    return (new NotificationService())
        ->delete(
            $notification_id,
            $user_id
        );
}


function notification_service_create(
    array $data
): array|false {

    return (new NotificationService())
        ->create(
            $data
        );
}


function notification_service_send(
    array $data
): array|false {

    return (new NotificationService())
        ->send(
            $data
        );
}


function notification_service_notify_user(
    int $user_id,
    string $title,
    string $body = '',
    string $type = 'system',
    array $data = []
): array|false {

    return (new NotificationService())
        ->notifyUser(
            $user_id,
            $title,
            $body,
            $type,
            $data
        );
}
