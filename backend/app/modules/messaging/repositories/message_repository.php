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

require_once __DIR__ . '/../../../core/database/query.php';
require_once __DIR__ . '/../../../core/logging/logger.php';
require_once __DIR__ . '/message_event_repository.php';


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


    /*
     * Cursor pagination is keyed on the auto-increment id, which is the
     * deterministic chronological ordering for this table. The composite
     * (conversation_id, id) index serves every branch of the query. Only the
     * requested page (+1 look-ahead row) is ever read into PHP memory.
     */

    $sql = "
        SELECT
            m.id,
            m.conversation_id,
            m.sender_user_id,
            m.sender_user_id AS sender_id,
            m.body,
            m.is_read,
            m.is_edited,
            m.read_at,
            m.created_at,
            m.updated_at
        FROM messages m
        WHERE m.conversation_id = :conversation_id
    ";

    $params = [
        ':conversation_id' =>
            $conversation_id
    ];


    $has_before =
        isset($filters['before_id']) &&
        (int) $filters['before_id'] > 0;

    $has_after =
        isset($filters['after_id']) &&
        (int) $filters['after_id'] > 0;


    /*
     * Before message ID (exclusive).
     */

    if ($has_before) {

        $sql .= "
            AND m.id < :before_id
        ";

        $params[':before_id'] =
            (int) $filters['before_id'];
    }


    /*
     * After message ID (exclusive).
     */

    if ($has_after) {

        $sql .= "
            AND m.id > :after_id
        ";

        $params[':after_id'] =
            (int) $filters['after_id'];
    }


    /*
     * Legacy filters remain supported for existing clients.
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


    $limit =
        isset($filters['limit'])
            ? (int) $filters['limit']
            : MESSAGE_PAGE_DEFAULT_LIMIT;


    $limit =
        max(
            1,
            min(
                $limit,
                MESSAGE_PAGE_MAX_LIMIT
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


    /*
     * Newest page and older pages scan DESC (no filesort); newer pages scan
     * ASC. One extra row is fetched only to determine whether another page
     * exists and is never returned.
     */

    $descending =
        !$has_after;


    $sql .=
        ' ORDER BY m.id '
        . ($descending ? 'DESC' : 'ASC')
        . ' LIMIT ' . ($limit + 1);


    if ($offset > 0) {
        $sql .= ' OFFSET ' . $offset;
    }


    $rows =
        message_repository_fetch_all(
            $sql,
            $params
        );


    $has_more =
        count($rows) > $limit;


    if ($has_more) {
        array_pop($rows);
    }


    if ($descending) {
        $rows =
            array_reverse($rows);
    }


    $ids =
        array_map(
            static function (array $row): int {
                return (int) $row['id'];
            },
            $rows
        );


    $next_before_id =
        $ids[0] ?? (
            $has_before
                ? (int) $filters['before_id']
                : null
        );

    $next_after_id =
        $ids !== []
            ? (int) end($ids)
            : (
                $has_after
                    ? (int) $filters['after_id']
                    : null
            );


    /*
     * `order=desc` is retained as a display-only legacy option. The underlying
     * cursor/page mechanics are identical in every mode; only presentation is
     * reversed. The default is chronological (oldest to newest).
     */

    if (
        strtolower(
            (string) ($filters['order'] ?? 'asc')
        ) === 'desc'
    ) {

        $rows =
            array_reverse($rows);
    }


    $messages =
        array_map(
            static function (array $row): array {

                $row['id'] =
                    (int) $row['id'];

                $row['conversation_id'] =
                    (int) $row['conversation_id'];

                $row['sender_user_id'] =
                    (int) $row['sender_user_id'];

                $row['sender_id'] =
                    (int) $row['sender_id'];

                $row['is_read'] =
                    (int) $row['is_read'];

                return $row;
            },
            $rows
        );


    return [
        'messages'   => $messages,
        'pagination' => [
            'limit'          => $limit,
            'has_more'       => $has_more,
            'next_before_id' => $next_before_id,
            'next_after_id'  => $next_after_id,
        ],
    ];
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
            m.sender_user_id AS sender_id
        FROM messages m
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
            m.sender_user_id AS sender_id
        FROM messages m

        INNER JOIN conversations c
            ON c.id = m.conversation_id

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


    /*
     * The insert and the conversation timestamp update must succeed or fail
     * together. db_transaction() rolls back and rethrows on any failure, so a
     * failed message never leaves a misleading conversation timestamp.
     */

    try {

        return db_transaction(
            static function (PDO $db) use (
                $conversation_id,
                $sender_id,
                $body
            ): array {

                $stmt =
                    $db->prepare(
                        "
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
                        "
                    );


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


                $id =
                    (int) $db->lastInsertId();


                if ($id <= 0) {

                    throw new RuntimeException(
                        'Message insert did not return an id.'
                    );
                }


                $stmt =
                    $db->prepare(
                        "
                        UPDATE conversations
                        SET updated_at = CURRENT_TIMESTAMP
                        WHERE id = :conversation_id
                        "
                    );


                $stmt->execute(
                    [
                        ':conversation_id' =>
                            $conversation_id
                    ]
                );


                $created =
                    message_repository_find($id)
                    ?? [
                        'id'            => $id,
                        'body'          => $body,
                        'sender_user_id'=> $sender_id,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ];


                /*
                 * The outbox row is written in this same transaction. A failed
                 * event insert rolls the message back; a WebSocket outage can
                 * never lose a persisted message.
                 */

                message_event_repository_create(
                    [
                        'message_id'      => $id,
                        'conversation_id' => $conversation_id,
                        'sender_user_id'  => $sender_id,
                        'body'            => $created['body'] ?? $body,
                        'created_at'      => $created['created_at'] ?? date('Y-m-d H:i:s'),
                    ]
                );


                return $created ?? [
                    'id' =>
                        $id,

                    'conversation_id' =>
                        $conversation_id,

                    'sender_id' =>
                        $sender_id,

                    'body' =>
                        $body,

                    'is_read' =>
                        0,

                    'read_at' =>
                        null,

                    'created_at' =>
                        date('Y-m-d H:i:s')
                ];
            }
        );

    } catch (Throwable $exception) {

        logger_database_error(
            'Message creation failed and was rolled back.',
            [
                'conversation_id' =>
                    $conversation_id,

                'sender_id' =>
                    $sender_id,

                'exception' =>
                    $exception->getMessage(),
            ]
        );


        throw $exception;
    }
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
| Edit Message
|--------------------------------------------------------------------------
|
| Updates the body of the caller's own message, marks it is_edited and
| records updated_at. The id and created_at are never modified. The
| message.updated outbox event is written inside the same transaction so the
| WebSocket relay can never broadcast an edit before it is committed.
|
*/

function message_repository_edit(
    int $conversation_id,
    int $message_id,
    int $sender_id,
    string $body
): array|false {

    if (
        $conversation_id <= 0 ||
        $message_id <= 0 ||
        $sender_id <= 0 ||
        trim($body) === ''
    ) {
        return false;
    }


    try {

        return
            db_transaction(
                static function (PDO $db) use (
                    $conversation_id,
                    $message_id,
                    $sender_id,
                    $body
                ): array {

                    $stmt =
                        $db->prepare(
                            "
                            UPDATE messages
                            SET
                                body = :body,
                                is_edited = 1,
                                updated_at = NOW()
                            WHERE id = :message_id
                              AND conversation_id = :conversation_id
                              AND sender_user_id = :sender_id
                            "
                        );


                    $stmt->execute(
                        [
                            ':body'           => $body,
                            ':message_id'     => $message_id,
                            ':conversation_id' => $conversation_id,
                            ':sender_id'      => $sender_id,
                        ]
                    );


                    if ($stmt->rowCount() !== 1) {
                        throw new RuntimeException(
                            'Message edit affected no rows.'
                        );
                    }


                    $updated =
                        message_repository_find($message_id);


                    if (!$updated) {
                        throw new RuntimeException(
                            'Edited message not found.'
                        );
                    }


                    message_event_repository_create(
                        [
                            'event_type'      => 'message.updated',
                            'message_id'      => $message_id,
                            'conversation_id' => $conversation_id,
                            'sender_user_id'  => $sender_id,
                            'body'            => (string) ($updated['body'] ?? $body),
                            'created_at'      => (string) ($updated['created_at'] ?? date('Y-m-d H:i:s')),
                        ]
                    );


                    return $updated;
                }
            );

    } catch (Throwable $exception) {

        logger_database_error(
            'Message edit failed and was rolled back.',
            [
                'conversation_id' => $conversation_id,
                'message_id'      => $message_id,
                'sender_id'       => $sender_id,
                'exception'       => $exception->getMessage(),
            ]
        );

        throw $exception;
    }
}


/*
|--------------------------------------------------------------------------
| Delete Message (hard delete, scoped to conversation)
|--------------------------------------------------------------------------
|
| Physically deletes the message row. No soft delete, placeholder or archive.
| The message.deleted outbox event is written in the same transaction so the
| relay can only broadcast the deletion after commit.
|
*/

function message_repository_delete_for_conversation(
    int $conversation_id,
    int $message_id,
    int $sender_id
): bool {

    if (
        $conversation_id <= 0 ||
        $message_id <= 0 ||
        $sender_id <= 0
    ) {
        return false;
    }


    try {

        $deleted =
            db_transaction(
                static function (PDO $db) use (
                    $conversation_id,
                    $message_id,
                    $sender_id
                ): bool {

                    $stmt =
                        $db->prepare(
                            "
                            DELETE FROM messages
                            WHERE id = :message_id
                              AND conversation_id = :conversation_id
                              AND sender_user_id = :sender_id
                            "
                        );


                    $stmt->execute(
                        [
                            ':message_id'     => $message_id,
                            ':conversation_id' => $conversation_id,
                            ':sender_id'      => $sender_id,
                        ]
                    );


                    if (!($stmt->rowCount() > 0)) {
                        throw new RuntimeException(
                            'Message delete affected no rows.'
                        );
                    }


                    /*
                     * Do not include the deleted body here: the event must
                     * never leak the content that was deleted.
                     */

                    message_event_repository_create(
                        [
                            'event_type'      => 'message.deleted',
                            'message_id'      => $message_id,
                            'conversation_id' => $conversation_id,
                            'sender_user_id'  => $sender_id,
                            'body'            => '',
                            'created_at'      => date('Y-m-d H:i:s'),
                        ]
                    );


                    return true;
                }
            );

        return (bool) $deleted;

    } catch (Throwable $exception) {

        logger_database_error(
            'Message delete failed and was rolled back.',
            [
                'conversation_id' => $conversation_id,
                'message_id'      => $message_id,
                'exception'       => $exception->getMessage(),
            ]
        );

        throw $exception;
    }
}


/*
|--------------------------------------------------------------------------
| Delete Message (legacy sender-only delete, used by old update/delete flow)
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
            m.sender_user_id AS sender_id
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
