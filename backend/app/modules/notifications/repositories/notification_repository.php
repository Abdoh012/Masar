<?php

/**
 * MASAR - Notification Repository
 *
 * Data-access layer for notifications.
 *
 * Service
 *     ↓
 * Repository
 *     ↓
 * Database
 *
 * This class is responsible only for database
 * operations related to notifications.
 */


/*
|--------------------------------------------------------------------------
| Database Dependency
|--------------------------------------------------------------------------
*/

$database_files = [
    dirname(__DIR__, 3) . '/core/database.php',
    dirname(__DIR__, 3) . '/config/database.php',
    dirname(__DIR__, 4) . '/core/database.php',
];


foreach ($database_files as $database_file) {

    if (file_exists($database_file)) {

        require_once $database_file;

        break;
    }
}


/*
|--------------------------------------------------------------------------
| Notification Repository
|--------------------------------------------------------------------------
*/

class NotificationRepository
{
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */

    protected function db(): mixed
    {
        /*
         * Database accessor used by the application.
         */

        if (
            function_exists('get_database_connection')
        ) {

            $connection = get_database_connection();

            if ($connection instanceof PDO) {
                return $connection;
            }
        }

        /*
         * PDO connection exposed by the application.
         */

        if (
            isset($GLOBALS['db']) &&
            $GLOBALS['db'] instanceof PDO
        ) {

            return $GLOBALS['db'];
        }


        if (
            isset($GLOBALS['pdo']) &&
            $GLOBALS['pdo'] instanceof PDO
        ) {

            return $GLOBALS['pdo'];
        }


        /*
         * Common database helper.
         */

        if (
            function_exists('db')
        ) {

            $connection = db();

            if (
                $connection instanceof PDO
            ) {

                return $connection;
            }
        }


        if (
            function_exists('database')
        ) {

            $connection = database();

            if (
                $connection instanceof PDO
            ) {

                return $connection;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    */

    protected function table(): string
    {
        return 'notifications';
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


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return [];
        }


        $conditions = [
            'user_id = :user_id'
        ];


        $params = [
            ':user_id' =>
                $user_id
        ];


        /*
         * Read/unread filter.
         */

        if (
            isset(
                $filters['unread']
            )
        ) {

            $unread =
                filter_var(
                    $filters['unread'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );


            if ($unread === true) {

                $conditions[] =
                    'read_at IS NULL';

            } elseif ($unread === false) {

                $conditions[] =
                    'read_at IS NOT NULL';
            }
        }


        /*
         * Type filter.
         */

        if (
            isset(
                $filters['type']
            ) &&
            trim(
                (string)
                $filters['type']
            ) !== ''
        ) {

            $conditions[] =
                'type = :type';


            $params[':type'] =
                trim(
                    (string)
                    $filters['type']
                );
        }


        /*
         * Date range.
         */

        if (
            !empty(
                $filters['from']
            )
        ) {

            $conditions[] =
                'created_at >= :from';


            $params[':from'] =
                $filters['from'];
        }


        if (
            !empty(
                $filters['to']
            )
        ) {

            $conditions[] =
                'created_at <= :to';


            $params[':to'] =
                $filters['to'];
        }


        /*
         * Search.
         */

        if (
            isset(
                $filters['search']
            ) &&
            trim(
                (string)
                $filters['search']
            ) !== ''
        ) {

            $conditions[] =
                '(
                    title LIKE :search_title
                    OR body LIKE :search_body
                )';


            $search_value =
                '%' .
                trim(
                    (string)
                    $filters['search']
                ) .
                '%';


            $params[':search_title'] =
                $search_value;


            $params[':search_body'] =
                $search_value;
        }


        /*
         * Pagination.
         */

        $limit =
            isset(
                $filters['limit']
            )
                ? (int)
                    $filters['limit']
                : 20;


        $limit =
            max(
                1,
                min(
                    $limit,
                    100
                )
            );


        $page =
            isset(
                $filters['page']
            )
                ? (int)
                    $filters['page']
                : 1;


        $page =
            max(
                1,
                $page
            );


        $offset =
            (
                $page - 1
            ) *
            $limit;


        /*
         * Ordering.
         */

        $order =
            strtolower(
                trim(
                    (string)
                    (
                        $filters['order']
                        ?? 'desc'
                    )
                )
            );


        $order =
            $order === 'asc'
                ? 'ASC'
                : 'DESC';


        $where =
            implode(
                ' AND ',
                $conditions
            );


        $sql = "
            SELECT
                *
            FROM {$this->table()}
            WHERE {$where}
            ORDER BY created_at {$order}
            LIMIT :limit
            OFFSET :offset
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            foreach (
                $params
                as $key => $value
            ) {

                $statement->bindValue(
                    $key,
                    $value
                );
            }


            $statement->bindValue(
                ':limit',
                $limit,
                PDO::PARAM_INT
            );


            $statement->bindValue(
                ':offset',
                $offset,
                PDO::PARAM_INT
            );


            $statement->execute();


            return
                $statement->fetchAll(
                    PDO::FETCH_ASSOC
                );

        } catch (Throwable $e) {

            return [];
        }
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
    | Find By ID
    |--------------------------------------------------------------------------
    */

    public function find(
        int $notification_id
    ): ?array {

        if ($notification_id <= 0) {
            return null;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return null;
        }


        $sql = "
            SELECT
                *
            FROM {$this->table()}
            WHERE id = :id
            LIMIT 1
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':id',
                $notification_id,
                PDO::PARAM_INT
            );


            $statement->execute();


            $result =
                $statement->fetch(
                    PDO::FETCH_ASSOC
                );


            return
                $result !== false
                    ? $result
                    : null;

        } catch (Throwable $e) {

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Find For User
    |--------------------------------------------------------------------------
    */

    public function findForUser(
        int $notification_id,
        int $user_id
    ): ?array {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {

            return null;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return null;
        }


        $sql = "
            SELECT
                *
            FROM {$this->table()}
            WHERE
                id = :id
                AND user_id = :user_id
            LIMIT 1
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':id',
                $notification_id,
                PDO::PARAM_INT
            );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            $statement->execute();


            $result =
                $statement->fetch(
                    PDO::FETCH_ASSOC
                );


            return
                $result !== false
                    ? $result
                    : null;

        } catch (Throwable $e) {

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data
    ): array|false {

        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return false;
        }


        $user_id =
            (int) (
                $data['user_id']
                ?? 0
            );


        if ($user_id <= 0) {
            return false;
        }


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


        $type =
            trim(
                (string) (
                    $data['type']
                    ?? 'system'
                )
            );


        /*
         * The canonical notifications table stores
         * structured metadata through the
         * entity_type/entity_id columns. Map the
         * supplied metadata onto those columns.
         */

        $entity =
            $this->resolveEntity(
                $data
            );


        $sql = "
            INSERT INTO {$this->table()}
            (
                user_id,
                type,
                title,
                body,
                entity_type,
                entity_id,
                created_at
            )
            VALUES
            (
                :user_id,
                :type,
                :title,
                :body,
                :entity_type,
                :entity_id,
                CURRENT_TIMESTAMP
            )
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            $statement->bindValue(
                ':type',
                $type
            );


            $statement->bindValue(
                ':title',
                $title
            );


            $statement->bindValue(
                ':body',
                $body
            );


            if (
                $entity['entity_type'] === null
            ) {

                $statement->bindValue(
                    ':entity_type',
                    null,
                    PDO::PARAM_NULL
                );

            } else {

                $statement->bindValue(
                    ':entity_type',
                    $entity['entity_type']
                );
            }


            if (
                $entity['entity_id'] === null
            ) {

                $statement->bindValue(
                    ':entity_id',
                    null,
                    PDO::PARAM_NULL
                );

            } else {

                $statement->bindValue(
                    ':entity_id',
                    $entity['entity_id'],
                    PDO::PARAM_INT
                );
            }


            $statement->execute();


            $id =
                (int)
                $db->lastInsertId();


            if ($id <= 0) {
                return false;
            }


            return
                $this->find(
                    $id
                );

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Entity Reference
    |--------------------------------------------------------------------------
    |
    | Maps structured notification metadata onto the
    | canonical entity_type/entity_id columns.
    |
    | Callers may supply an explicit entity_type/
    | entity_id pair or a metadata array containing
    | recognized entity identifier keys.
    |
    */

    protected function resolveEntity(
        array $data
    ): array {

        /*
         * Explicit entity reference.
         */

        if (
            isset(
                $data['entity_type']
            ) &&
            isset(
                $data['entity_id']
            )
        ) {

            return [
                'entity_type' =>
                    (string)
                    $data['entity_type'],

                'entity_id' =>
                    (int)
                    $data['entity_id']
            ];
        }


        /*
         * Metadata array supplied by callers.
         */

        $metadata =
            $data['data']
            ?? null;


        if (
            is_string($metadata)
        ) {

            $decoded =
                json_decode(
                    $metadata,
                    true
                );


            if (
                json_last_error() === JSON_ERROR_NONE &&
                is_array($decoded)
            ) {

                $metadata =
                    $decoded;
            }
        }


        if (
            !is_array($metadata)
        ) {

            return [
                'entity_type' =>
                    null,

                'entity_id' =>
                    null
            ];
        }


        /*
         * Recognized entity identifier keys in
         * priority order.
         */

        $entity_keys = [

            'application_id' =>
                'application',

            'appeal_id' =>
                'appeal',

            'certificate_id' =>
                'certificate',

            'training_session_id' =>
                'session',

            'training_id' =>
                'training',

            'conversation_id' =>
                'conversation',

            'message_id' =>
                'message'
        ];


        foreach (
            $entity_keys
            as $key => $entity_type
        ) {

            if (
                isset(
                    $metadata[$key]
                )
            ) {

                return [
                    'entity_type' =>
                        $entity_type,

                    'entity_id' =>
                        (int)
                        $metadata[$key]
                ];
            }
        }


        return [
            'entity_type' =>
                null,

            'entity_id' =>
                null
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Mark As Read
    |--------------------------------------------------------------------------
    */

    public function markAsRead(
        int $notification_id,
        int $user_id
    ): bool {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {

            return false;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return false;
        }


        $sql = "
            UPDATE {$this->table()}
            SET
                read_at = CURRENT_TIMESTAMP
            WHERE
                id = :id
                AND user_id = :user_id
                AND read_at IS NULL
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':id',
                $notification_id,
                PDO::PARAM_INT
            );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            return
                $statement->execute();

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */

    public function markAllAsRead(
        int $user_id
    ): bool {

        if ($user_id <= 0) {
            return false;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return false;
        }


        $sql = "
            UPDATE {$this->table()}
            SET
                read_at = CURRENT_TIMESTAMP
            WHERE
                user_id = :user_id
                AND read_at IS NULL
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            return
                $statement->execute();

        } catch (Throwable $e) {

            return false;
        }
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


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return 0;
        }


        $sql = "
            SELECT
                COUNT(*)
            FROM {$this->table()}
            WHERE
                user_id = :user_id
                AND read_at IS NULL
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            $statement->execute();


            return (int)
                $statement->fetchColumn();

        } catch (Throwable $e) {

            return 0;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $notification_id,
        int $user_id
    ): bool {

        if (
            $notification_id <= 0 ||
            $user_id <= 0
        ) {

            return false;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return false;
        }


        $sql = "
            DELETE FROM {$this->table()}
            WHERE
                id = :id
                AND user_id = :user_id
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':id',
                $notification_id,
                PDO::PARAM_INT
            );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            return
                $statement->execute();

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete All For User
    |--------------------------------------------------------------------------
    */

    public function deleteAll(
        int $user_id
    ): bool {

        if ($user_id <= 0) {
            return false;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return false;
        }


        $sql = "
            DELETE FROM {$this->table()}
            WHERE user_id = :user_id
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            $statement->bindValue(
                ':user_id',
                $user_id,
                PDO::PARAM_INT
            );


            return
                $statement->execute();

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    public function count(
        int $user_id,
        array $filters = []
    ): int {

        if ($user_id <= 0) {
            return 0;
        }


        $db =
            $this->db();


        if (
            !$db instanceof PDO
        ) {

            return 0;
        }


        $conditions = [
            'user_id = :user_id'
        ];


        $params = [
            ':user_id' =>
                $user_id
        ];


        if (
            isset(
                $filters['unread']
            )
        ) {

            $unread =
                filter_var(
                    $filters['unread'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );


            if ($unread === true) {

                $conditions[] =
                    'read_at IS NULL';

            } elseif ($unread === false) {

                $conditions[] =
                    'read_at IS NOT NULL';
            }
        }


        if (
            !empty(
                $filters['type']
            )
        ) {

            $conditions[] =
                'type = :type';


            $params[':type'] =
                trim(
                    (string)
                    $filters['type']
                );
        }


        $where =
            implode(
                ' AND ',
                $conditions
            );


        $sql = "
            SELECT
                COUNT(*)
            FROM {$this->table()}
            WHERE {$where}
        ";


        try {

            $statement =
                $db->prepare(
                    $sql
                );


            foreach (
                $params
                as $key => $value
            ) {

                $statement->bindValue(
                    $key,
                    $value
                );
            }


            $statement->execute();


            return (int)
                $statement->fetchColumn();

        } catch (Throwable $e) {

            return 0;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function notification_repository_list(
    int $user_id,
    array $filters = []
): array {

    return
        (new NotificationRepository())
            ->list(
                $user_id,
                $filters
            );
}


function notification_repository_get_user_notifications(
    int $user_id,
    array $filters = []
): array {

    return
        (new NotificationRepository())
            ->getUserNotifications(
                $user_id,
                $filters
            );
}


function notification_repository_find(
    int $notification_id
): ?array {

    return
        (new NotificationRepository())
            ->find(
                $notification_id
            );
}


function notification_repository_find_for_user(
    int $notification_id,
    int $user_id
): ?array {

    return
        (new NotificationRepository())
            ->findForUser(
                $notification_id,
                $user_id
            );
}


function notification_repository_create(
    array $data
): array|false {

    return
        (new NotificationRepository())
            ->create(
                $data
            );
}


function notification_repository_mark_as_read(
    int $notification_id,
    int $user_id
): bool {

    return
        (new NotificationRepository())
            ->markAsRead(
                $notification_id,
                $user_id
            );
}


function notification_repository_mark_all_as_read(
    int $user_id
): bool {

    return
        (new NotificationRepository())
            ->markAllAsRead(
                $user_id
            );
}


function notification_repository_unread_count(
    int $user_id
): int {

    return
        (new NotificationRepository())
            ->unreadCount(
                $user_id
            );
}


function notification_repository_delete(
    int $notification_id,
    int $user_id
): bool {

    return
        (new NotificationRepository())
            ->delete(
                $notification_id,
                $user_id
            );
}
