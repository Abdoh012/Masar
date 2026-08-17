<?php

/**
 * MASAR - Notification Controller
 *
 * Handles HTTP requests related to notifications.
 *
 * Controller
 *     ↓
 * Service
 *     ↓
 * Repository
 *
 * The controller is responsible for:
 * - Reading request data
 * - Resolving the authenticated user
 * - Calling the notification service
 * - Returning a consistent response
 *
 * Business logic must remain inside the service layer.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

$base_path =
    dirname(
        __DIR__,
        3
    );


$service_file =
    __DIR__ .
    '/../services/notification_service.php';


$validator_file =
    $base_path .
    '/modules/notifications/validators/notification_validator.php';


if (file_exists($service_file)) {
    require_once $service_file;
}


if (file_exists($validator_file)) {
    require_once $validator_file;
}


/*
|--------------------------------------------------------------------------
| Request Helpers
|--------------------------------------------------------------------------
*/

function notification_controller_request_method(): string
{
    return strtoupper(
        $_SERVER['REQUEST_METHOD']
        ?? 'GET'
    );
}


function notification_controller_input(): array
{
    $content_type =
        strtolower(
            $_SERVER['CONTENT_TYPE']
            ?? ''
        );


    if (
        str_contains(
            $content_type,
            'application/json'
        )
    ) {

        $raw =
            file_get_contents(
                'php://input'
            );


        if (
            $raw === false ||
            trim($raw) === ''
        ) {
            return [];
        }


        $decoded =
            json_decode(
                $raw,
                true
            );


        return
            is_array($decoded)
                ? $decoded
                : [];
    }


    return $_POST;
}


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

function notification_controller_user_id(): int
{
    /*
     * JWT-based authentication used by the application.
     */

    if (function_exists('auth_user')) {
        $user = auth_user();

        if (
            is_array($user)
            &&
            !empty($user['id'])
        ) {

            return (int) $user['id'];
        }
    }

    /*
     * Support the common authentication
     * locations used by the application.
     */

    if (
        isset(
            $_SESSION['user_id']
        )
    ) {

        return (int)
            $_SESSION['user_id'];
    }


    if (
        isset(
            $_SESSION['user']['id']
        )
    ) {

        return (int)
            $_SESSION['user']['id'];
    }


    if (
        isset(
            $_SESSION['auth']['user_id']
        )
    ) {

        return (int)
            $_SESSION['auth']['user_id'];
    }


    if (
        isset(
            $GLOBALS['current_user_id']
        )
    ) {

        return (int)
            $GLOBALS['current_user_id'];
    }


    return 0;
}


/*
|--------------------------------------------------------------------------
| Response Helpers
|--------------------------------------------------------------------------
*/

function notification_controller_response(
    bool $success,
    string $message = '',
    mixed $data = null,
    array $errors = [],
    int $status = 200
): array {

    return [
        'success' =>
            $success,

        'message' =>
            $message,

        'data' =>
            $data,

        'errors' =>
            $errors,

        'status' =>
            $status
    ];
}


function notification_controller_send(
    array $response
): void {

    $status =
        (int) (
            $response['status']
            ?? 200
        );


    http_response_code(
        $status
    );


    header(
        'Content-Type: application/json; charset=utf-8'
    );


    echo json_encode(
        [
            'success' =>
                $response['success']
                ?? false,

            'message' =>
                $response['message']
                ?? '',

            'data' =>
                $response['data']
                ?? null,

            'errors' =>
                $response['errors']
                ?? []
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


    exit;
}


/*
|--------------------------------------------------------------------------
| Service Resolver
|--------------------------------------------------------------------------
*/

function notification_controller_service(): mixed
{
    /*
     * Support different service naming
     * conventions without putting business
     * logic inside the controller.
     */

    if (
        class_exists(
            'NotificationService'
        )
    ) {

        return new NotificationService();
    }


    if (
        function_exists(
            'notification_service'
        )
    ) {

        return 'notification_service';
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
|
| GET /notifications
|
| Returns notifications for the
| authenticated user.
|
*/

function notification_controller_index(): array
{
    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [
                'auth' =>
                    'User is not authenticated.'
            ],
            401
        );
    }


    $input =
        notification_controller_input();


    $filters =
        is_array($input)
            ? $input
            : [];


    try {

        /*
         * Class-based service.
         */

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'list'
                )
            ) {

                $data =
                    $service->list(
                        $user_id,
                        $filters
                    );

            } elseif (
                method_exists(
                    $service,
                    'getUserNotifications'
                )
            ) {

                $data =
                    $service->getUserNotifications(
                        $user_id,
                        $filters
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        /*
         * Function-based service.
         */

        } elseif (
            function_exists(
                'notification_service_list'
            )
        ) {

            $data =
                notification_service_list(
                    $user_id,
                    $filters
                );

        } elseif (
            function_exists(
                'notification_service_get_user_notifications'
            )
        ) {

            $data =
                notification_service_get_user_notifications(
                    $user_id,
                    $filters
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        return notification_controller_response(
            true,
            'Notifications retrieved successfully.',
            $data
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to retrieve notifications.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
|
| GET /notifications/{id}
|
*/

function notification_controller_show(
    int $notification_id
): array {

    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [],
            401
        );
    }


    if ($notification_id <= 0) {

        return notification_controller_response(
            false,
            'Invalid notification ID.',
            null,
            [
                'notification_id' =>
                    'A valid notification ID is required.'
            ],
            422
        );
    }


    try {

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'find'
                )
            ) {

                $data =
                    $service->find(
                        $notification_id,
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'get'
                )
            ) {

                $data =
                    $service->get(
                        $notification_id,
                        $user_id
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        } elseif (
            function_exists(
                'notification_service_find'
            )
        ) {

            $data =
                notification_service_find(
                    $notification_id,
                    $user_id
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        if (
            $data === null ||
            $data === false
        ) {

            return notification_controller_response(
                false,
                'Notification not found.',
                null,
                [],
                404
            );
        }


        return notification_controller_response(
            true,
            'Notification retrieved successfully.',
            $data
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to retrieve notification.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Mark As Read
|--------------------------------------------------------------------------
|
| POST /notifications/{id}/read
|
*/

function notification_controller_mark_read(
    int $notification_id
): array {

    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [],
            401
        );
    }


    if ($notification_id <= 0) {

        return notification_controller_response(
            false,
            'Invalid notification ID.',
            null,
            [
                'notification_id' =>
                    'A valid notification ID is required.'
            ],
            422
        );
    }


    try {

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'markAsRead'
                )
            ) {

                $result =
                    $service->markAsRead(
                        $notification_id,
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'read'
                )
            ) {

                $result =
                    $service->read(
                        $notification_id,
                        $user_id
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        } elseif (
            function_exists(
                'notification_service_mark_as_read'
            )
        ) {

            $result =
                notification_service_mark_as_read(
                    $notification_id,
                    $user_id
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        if ($result === false) {

            return notification_controller_response(
                false,
                'Unable to mark notification as read.',
                null,
                [],
                400
            );
        }


        return notification_controller_response(
            true,
            'Notification marked as read.',
            $result
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to mark notification as read.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Mark All As Read
|--------------------------------------------------------------------------
|
| POST /notifications/read-all
|
*/

function notification_controller_mark_all_read(): array
{
    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [],
            401
        );
    }


    try {

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'markAllAsRead'
                )
            ) {

                $result =
                    $service->markAllAsRead(
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'readAll'
                )
            ) {

                $result =
                    $service->readAll(
                        $user_id
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        } elseif (
            function_exists(
                'notification_service_mark_all_as_read'
            )
        ) {

            $result =
                notification_service_mark_all_as_read(
                    $user_id
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        return notification_controller_response(
            true,
            'All notifications marked as read.',
            $result
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to mark notifications as read.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
|
| GET /notifications/unread-count
|
*/

function notification_controller_unread_count(): array
{
    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [],
            401
        );
    }


    try {

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'unreadCount'
                )
            ) {

                $count =
                    $service->unreadCount(
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'getUnreadCount'
                )
            ) {

                $count =
                    $service->getUnreadCount(
                        $user_id
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        } elseif (
            function_exists(
                'notification_service_unread_count'
            )
        ) {

            $count =
                notification_service_unread_count(
                    $user_id
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        return notification_controller_response(
            true,
            'Unread count retrieved successfully.',
            [
                'count' =>
                    (int) $count
            ]
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to retrieve unread count.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
|
| DELETE /notifications/{id}
|
*/

function notification_controller_delete(
    int $notification_id
): array {

    $user_id =
        notification_controller_user_id();


    if ($user_id <= 0) {

        return notification_controller_response(
            false,
            'Authentication required.',
            null,
            [],
            401
        );
    }


    if ($notification_id <= 0) {

        return notification_controller_response(
            false,
            'Invalid notification ID.',
            null,
            [],
            422
        );
    }


    try {

        if (
            class_exists(
                'NotificationService'
            )
        ) {

            $service =
                new NotificationService();


            if (
                method_exists(
                    $service,
                    'delete'
                )
            ) {

                $result =
                    $service->delete(
                        $notification_id,
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'remove'
                )
            ) {

                $result =
                    $service->remove(
                        $notification_id,
                        $user_id
                    );

            } else {

                return notification_controller_response(
                    false,
                    'Notification service method is not available.',
                    null,
                    [],
                    500
                );
            }

        } elseif (
            function_exists(
                'notification_service_delete'
            )
        ) {

            $result =
                notification_service_delete(
                    $notification_id,
                    $user_id
                );

        } else {

            return notification_controller_response(
                false,
                'Notification service is not available.',
                null,
                [],
                500
            );
        }


        if ($result === false) {

            return notification_controller_response(
                false,
                'Unable to delete notification.',
                null,
                [],
                400
            );
        }


        return notification_controller_response(
            true,
            'Notification deleted successfully.',
            $result
        );

    } catch (Throwable $e) {

        return notification_controller_response(
            false,
            'Unable to delete notification.',
            null,
            [],
            500
        );
    }
}


/*
|--------------------------------------------------------------------------
| Main Dispatcher
|--------------------------------------------------------------------------
*/

function notification_controller_handle(
    ?string $action = null,
    ?int $notification_id = null
): array {

    $method =
        notification_controller_request_method();


    $action =
        $action !== null
            ? trim($action)
            : 'index';


    switch ($action) {

        case 'index':

        case 'list':

            if ($method !== 'GET') {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_index();


        case 'show':

            if ($method !== 'GET') {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_show(
                (int) $notification_id
            );


        case 'read':

        case 'mark-read':

            if (
                !in_array(
                    $method,
                    [
                        'POST',
                        'PATCH'
                    ],
                    true
                )
            ) {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_mark_read(
                (int) $notification_id
            );


        case 'read-all':

        case 'mark-all-read':

            if (
                !in_array(
                    $method,
                    [
                        'POST',
                        'PATCH'
                    ],
                    true
                )
            ) {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_mark_all_read();


        case 'unread-count':

            if ($method !== 'GET') {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_unread_count();


        case 'delete':

        case 'remove':

            if ($method !== 'DELETE') {

                return notification_controller_response(
                    false,
                    'Method not allowed.',
                    null,
                    [],
                    405
                );
            }


            return notification_controller_delete(
                (int) $notification_id
            );


        default:

            return notification_controller_response(
                false,
                'Unknown notification action.',
                null,
                [
                    'action' =>
                        'The requested notification action does not exist.'
                ],
                404
            );
    }
}


/*
|--------------------------------------------------------------------------
| Optional Direct Execution
|--------------------------------------------------------------------------
|
| The controller can be called directly when
| the application router sets the action and
| notification ID.
|
| This block does not execute when the file is
| included by another PHP file.
|
*/

if (
    basename(
        $_SERVER['SCRIPT_FILENAME']
        ?? ''
    ) === basename(
        __FILE__
    )
) {

    $action =
        $_GET['action']
        ?? $_POST['action']
        ?? 'index';


    $notification_id =
        isset(
            $_GET['id']
        )
            ? (int) $_GET['id']
            : (
                isset(
                    $_POST['id']
                )
                    ? (int) $_POST['id']
                    : null
            );


    $response =
        notification_controller_handle(
            $action,
            $notification_id
        );


    notification_controller_send(
        $response
    );
}
