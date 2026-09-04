<?php

/**
 * MASAR - Message Repository
 *
 * Database access layer for messages.
 *
 * Service
 *    ↓
 * Repository
 *    ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Database Dependency
|--------------------------------------------------------------------------
*/

if (
    file_exists(
        __DIR__ . '/../../../core/database.php'
    )
) {
    require_once
        __DIR__ . '/../../../core/database.php';
}

if (
    file_exists(
        __DIR__ . '/../../../core/database/connection.php'
    )
) {
    require_once
        __DIR__ . '/../../../core/database/connection.php';
}


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

function message_repository_db()
{
    if (function_exists('db')) {
        return db();
    }

    if (function_exists('database')) {
        return database();
    }

    if (function_exists('get_database_connection')) {
        return get_database_connection();
    }

    if (isset($GLOBALS['db'])) {
        return $GLOBALS['db'];
    }

    if (isset($GLOBALS['pdo'])) {
        return $GLOBALS['pdo'];
    }

    throw new RuntimeException(
        'Database connection is not available.'
    );
}


/*
|--------------------------------------------------------------------------
| Fetch All
|--------------------------------------------------------------------------
*/

function message_repository_fetch_all(
    string $sql,
    array $params = []
): array {

    $db = message_repository_db();

    $stmt = $db->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Fetch One
|--------------------------------------------------------------------------
*/

function message_repository_fetch_one(
    string $sql,
    array $params = []
): ?array {

    $db = message_repository_db();

    $stmt = $db->prepare($sql);

    $stmt->execute($params);

    $row = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    return $row !== false
        ? $row
        : null;
}


/*
|--------------------------------------------------------------------------
| Is Participant
|--------------------------------------------------------------------------
*/

function message_repository_is_participant(
    int $conversation_id,
    int $user_id
): bool {

    if (
        $conversation_id <= 0 ||
        $user_id <= 0
    ) {
        return false;
    }


    $row =
        message_repository_fetch_one(
            "
            SELECT
                c.id
            FROM conversations c
            WHERE c.id = :conversation_id
              AND (
                  c.student_id IN (
                      SELECT s.id
                      FROM students s
                      WHERE s.user_id = :student_user_id
                  )
                  OR
                  c.company_id IN (
                      SELECT co.id
                      FROM companies co
                      WHERE co.user_id = :company_user_id
                  )
              )
            LIMIT 1
            ",
            [
                ':conversation_id' =>
                    $conversation_id,

                ':student_user_id' =>
                    $user_id,

                ':company_user_id' =>
                    $user_id
            ]
        );


    return $row !== null;
}


/*
|--------------------------------------------------------------------------
| List Messages
|--------------------------------------------------------------------------
*/

function message_repository_list(
    int $conversation_id,
    int $user_id,
    array $filters = []
): array|false {

    if (
        $conversation_id <= 0 ||
        $user_id <= 0
    ) {
        return false;
    }


    /*
     * Verify conversation membership.
     */

    if (
        !message_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {
        return false;
    }


    $sql = "
        SELECT
            m.*,
            m.sender_user_id AS sender_id,
            u.email AS sender_email
        FROM messages m
        LEFT JOIN users u
            ON u.id = m.sender_user_id
        WHERE m.conversation_id = :conversation_id
    ";

    $params = [
        ':conversation_id' =>
            $conversation_id
    ];


    /*
     * Before message ID.
     */

    if (
        isset($filters['before_id']) &&
        (int) $filters['before_id'] > 0
    ) {

        $sql .= "
            AND m.id < :before_id
        ";

        $params[':before_id'] =
            (int) $filters['before_id'];
    }


    /*
     * After message ID.
     */

    if (
        isset($filters['after_id']) &&
        (int) $filters['after_id'] > 0
    ) {

        $sql .= "
            AND m.id > :after_id
        ";

        $params[':after_id'] =
            (int) $filters['after_id'];
    }


    /*
     * Sender filter.
     */

    if (
        isset($filters['sender_id']) &&
        (int) $filters['sender_id'] > 0
    ) {

        $sql .= "
            AND m.sender_user_id = :sender_id
        ";

        $params[':sender_id'] =
            (int) $filters['sender_id'];
    }


    /*
     * Date filters.
     */

    if (
        isset($filters['from_date']) &&
        trim(
            (string) $filters['from_date']
        ) !== ''
    ) {

        $sql .= "
            AND m.created_at >= :from_date
        ";

        $params[':from_date'] =
            $filters['from_date'];
    }


    if (
        isset($filters['to_date']) &&
        trim(
            (string) $filters['to_date']
        ) !== ''
    ) {

        $sql .= "
            AND m.created_at <= :to_date
        ";

        $params[':to_date'] =
            $filters['to_date'];
    }


    /*
     * Default order.
     */

    $order =
        strtolower(
            (string) (
                $filters['order']
                ?? 'asc'
            )
        );


    $order =
        $order === 'desc'
            ? 'DESC'
            : 'ASC';


    $sql .= "
        ORDER BY m.created_at {$order}, m.id {$order}
    ";


    /*
     * Pagination.
     */

    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 50;


    $limit =
        max(
            1,
            min(
                $limit,
                100
            )
        );


    $offset =
        isset($filters['offset'])
            ? (int) $filters['offset']
            : 0;


    $offset =
        max(
            0,
            $offset
        );


    $sql .= "
        LIMIT {$limit}
        OFFSET {$offset}
    ";


    return message_repository_fetch_all(
        $sql,
        $params
    );
}


/*
|--------------------------------------------------------------------------
| Find Message
|--------------------------------------------------------------------------
*/

function message_repository_find(
    int $message_id
): ?array {

    if ($message_id <= 0) {
        return null;
    }


    return message_repository_fetch_one(
        "
        SELECT
            m.*,
            m.sender_user_id AS sender_id,
            u.email AS sender_email
        FROM messages m
        LEFT JOIN users u
            ON u.id = m.sender_user_id
        WHERE m.id = :message_id
        LIMIT 1
        ",
        [
            ':message_id' =>
                $message_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Find Message For User
|--------------------------------------------------------------------------
*/

function message_repository_find_for_user(
    int $message_id,
    int $user_id
): ?array {

    if (
        $message_id <= 0 ||
        $user_id <= 0
    ) {
        return null;
    }


    return message_repository_fetch_one(
        "
        SELECT
            m.*,
            m.sender_user_id AS sender_id,
            u.email AS sender_email
        FROM messages m

        INNER JOIN conversations c
            ON c.id = m.conversation_id

        LEFT JOIN users u
            ON u.id = m.sender_user_id

        WHERE m.id = :message_id
          AND (
              c.student_id IN (
                  SELECT s.id
                  FROM students s
                  WHERE s.user_id = :student_user_id
              )
              OR
              c.company_id IN (
                  SELECT co.id
                  FROM companies co
                  WHERE co.user_id = :company_user_id
              )
          )
        LIMIT 1
        ",
        [
            ':message_id' =>
                $message_id,

            ':student_user_id' =>
                $user_id,

            ':company_user_id' =>
                $user_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Create Message
|--------------------------------------------------------------------------
*/

function message_repository_create(
    array $data
): array|false {

    $db = message_repository_db();


    $conversation_id =
        isset($data['conversation_id'])
            ? (int) $data['conversation_id']
            : 0;


    $sender_id =
        isset($data['sender_id'])
            ? (int) $data['sender_id']
            : 0;


    $body =
        trim(
            (string) (
                $data['body']
                ?? ''
            )
        );


    if (
        $conversation_id <= 0 ||
        $sender_id <= 0 ||
        $body === ''
    ) {
        return false;
    }


    $sql = "
        INSERT INTO messages
        (
            conversation_id,
            sender_user_id,
            body,
            is_read,
            created_at
        )
        VALUES
        (
            :conversation_id,
            :sender_id,
            :body,
            0,
            CURRENT_TIMESTAMP
        )
    ";


    $stmt =
        $db->prepare($sql);


    try {

        $success =
            $stmt->execute(
                [
                    ':conversation_id' =>
                        $conversation_id,

                    ':sender_id' =>
                        $sender_id,

                    ':body' =>
                        $body
                ]
            );

    } catch (Throwable $e) {

        return false;
    }


    if (!$success) {
        return false;
    }


    $id =
        (int)
            $db->lastInsertId();


    if ($id <= 0) {
        return false;
    }


    /*
     * Update conversation timestamp.
     */

    try {

        $stmt =
            $db->prepare(
                "
                UPDATE conversations
                SET
                    updated_at =
                        CURRENT_TIMESTAMP
                WHERE id = :conversation_id
                "
            );


        $stmt->execute(
            [
                ':conversation_id' =>
                    $conversation_id
            ]
        );

    } catch (Throwable $e) {
        // Message was already created.
    }


    return message_repository_find(
        $id
    ) ?? [
        'id' =>
            $id,

        'conversation_id' =>
            $conversation_id,

        'sender_id' =>
            $sender_id,

        'body' =>
            $body
    ];
}


/*
|--------------------------------------------------------------------------
| Update Message
|--------------------------------------------------------------------------
*/

function message_repository_update(
    int $message_id,
    int $user_id,
    array $data
): bool {

    if (
        $message_id <= 0 ||
        $user_id <= 0 ||
        empty($data)
    ) {
        return false;
    }


    $allowed = [
        'body'
    ];


    $fields = [];


    $params = [
        ':message_id' =>
            $message_id,

        ':user_id' =>
            $user_id
    ];


    foreach (
        $allowed
        as $field
    ) {

        if (
            !array_key_exists(
                $field,
                $data
            )
        ) {
            continue;
        }


        $fields[] =
            "{$field} = :{$field}";


        $params[
            ":{$field}"
        ] =
            $data[$field];
    }


    if (empty($fields)) {
        return false;
    }


    $sql = "
        UPDATE messages m
        SET
            " .
            implode(
                ', ',
                $fields
            ) .
        "
        WHERE m.id = :message_id
          AND m.sender_user_id = :user_id
    ";


    $db =
        message_repository_db();


    try {

        $stmt =
            $db->prepare($sql);

        $stmt->execute($params);

        return
            $stmt->rowCount() > 0;

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

function message_repository_delete(
    int $message_id,
    int $user_id
): bool {

    if (
        $message_id <= 0 ||
        $user_id <= 0
    ) {
        return false;
    }


    $db =
        message_repository_db();


    try {

        $stmt =
            $db->prepare(
                "
                DELETE FROM messages
                WHERE id = :message_id
                  AND sender_user_id = :user_id
                "
            );


        $stmt->execute(
            [
                ':message_id' =>
                    $message_id,

                ':user_id' =>
                    $user_id
            ]
        );


        return
            $stmt->rowCount() > 0;

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Mark One Message As Read
|--------------------------------------------------------------------------
*/

function message_repository_mark_read(
    int $message_id,
    int $user_id
): bool {

    if (
        $message_id <= 0 ||
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


    $db =
        message_repository_db();


    $stmt =
        $db->prepare(
            "
            UPDATE messages
            SET
                is_read = 1,
                read_at =
                    CURRENT_TIMESTAMP
            WHERE id = :message_id
              AND sender_user_id <> :user_id
            "
        );


    $stmt->execute(
        [
            ':message_id' =>
                $message_id,

            ':user_id' =>
                $user_id
        ]
    );


    return true;
}


/*
|--------------------------------------------------------------------------
| Mark Conversation As Read
|--------------------------------------------------------------------------
*/

function message_repository_mark_conversation_read(
    int $conversation_id,
    int $user_id
): bool {

    if (
        $conversation_id <= 0 ||
        $user_id <= 0
    ) {
        return false;
    }


    if (
        !message_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {
        return false;
    }


    $db =
        message_repository_db();


    $stmt =
        $db->prepare(
            "
            UPDATE messages
            SET
                is_read = 1,
                read_at =
                    CURRENT_TIMESTAMP
            WHERE conversation_id = :conversation_id
              AND sender_user_id <> :user_id
              AND read_at IS NULL
            "
        );


    $stmt->execute(
        [
            ':conversation_id' =>
                $conversation_id,

            ':user_id' =>
                $user_id
        ]
    );


    return true;
}


/*
|--------------------------------------------------------------------------
| Unread Count
|--------------------------------------------------------------------------
*/

function message_repository_unread_count(
    int $user_id,
    ?int $conversation_id = null
): int {

    if ($user_id <= 0) {
        return 0;
    }


    $sql = "
        SELECT
            COUNT(*) AS unread_count
        FROM messages m
        INNER JOIN conversations c
            ON c.id = m.conversation_id
        WHERE (
            c.student_id IN (
                SELECT s.id
                FROM students s
                WHERE s.user_id = :student_user_id
            )
            OR
            c.company_id IN (
                SELECT co.id
                FROM companies co
                WHERE co.user_id = :company_user_id
            )
        )
          AND m.sender_user_id <> :sender_user_id
          AND m.read_at IS NULL
    ";


    $params = [
        ':student_user_id' =>
            $user_id,

        ':company_user_id' =>
            $user_id,

        ':sender_user_id' =>
            $user_id
    ];


    if (
        $conversation_id !== null
    ) {

        if (
            $conversation_id <= 0
        ) {
            return 0;
        }


        $sql .= "
            AND m.conversation_id = :conversation_id
        ";


        $params[':conversation_id'] =
            $conversation_id;
    }


    $row =
        message_repository_fetch_one(
            $sql,
            $params
        );


    return (int) (
        $row['unread_count']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Search Messages
|--------------------------------------------------------------------------
*/

function message_repository_search(
    int $user_id,
    string $keyword,
    array $filters = []
): array {

    if (
        $user_id <= 0 ||
        trim($keyword) === ''
    ) {
        return [];
    }


    $sql = "
        SELECT
            m.*,
            m.sender_user_id AS sender_id,
            u.email AS sender_email
        FROM messages m

        INNER JOIN conversations c
            ON c.id = m.conversation_id

        LEFT JOIN users u
            ON u.id = m.sender_user_id

        WHERE (
            c.student_id IN (
                SELECT s.id
                FROM students s
                WHERE s.user_id = :student_user_id
            )
            OR
            c.company_id IN (
                SELECT co.id
                FROM companies co
                WHERE co.user_id = :company_user_id
            )
        )
          AND m.body LIKE :keyword
    ";


    $params = [
        ':student_user_id' =>
            $user_id,

        ':company_user_id' =>
            $user_id,

        ':keyword' =>
            '%' .
            trim($keyword) .
            '%'
    ];


    if (
        isset($filters['conversation_id']) &&
        (int) $filters['conversation_id'] > 0
    ) {

        $sql .= "
            AND m.conversation_id = :conversation_id
        ";


        $params[':conversation_id'] =
            (int)
                $filters['conversation_id'];
    }


    if (
        isset($filters['sender_id']) &&
        (int) $filters['sender_id'] > 0
    ) {

        $sql .= "
            AND m.sender_user_id = :sender_id
        ";


        $params[':sender_id'] =
            (int)
                $filters['sender_id'];
    }


    $sql .= "
        ORDER BY m.created_at DESC, m.id DESC
    ";


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : 50;


    $limit =
        max(
            1,
            min(
                $limit,
                100
            )
        );


    $sql .=
        " LIMIT {$limit}";


    return message_repository_fetch_all(
        $sql,
        $params
    );
}
