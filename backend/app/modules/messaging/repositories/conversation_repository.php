<?php

/**
 * MASAR - Conversation Repository
 *
 * Database access layer for conversations.
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

function conversation_repository_db()
{
    if (
        function_exists(
            'db'
        )
    ) {
        return db();
    }


    if (
        function_exists(
            'database'
        )
    ) {
        return database();
    }


    if (
        function_exists(
            'get_database_connection'
        )
    ) {
        return get_database_connection();
    }


    if (
        isset(
            $GLOBALS['db']
        )
    ) {
        return $GLOBALS['db'];
    }


    if (
        isset(
            $GLOBALS['pdo']
        )
    ) {
        return $GLOBALS['pdo'];
    }


    throw new RuntimeException(
        'Database connection is not available.'
    );
}


/*
|--------------------------------------------------------------------------
| Query Helper
|--------------------------------------------------------------------------
*/

function conversation_repository_fetch_all(
    string $sql,
    array $params = []
): array {

    $db =
        conversation_repository_db();


    $stmt =
        $db->prepare(
            $sql
        );


    $stmt->execute(
        $params
    );


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Fetch One
|--------------------------------------------------------------------------
*/

function conversation_repository_fetch_one(
    string $sql,
    array $params = []
): ?array {

    $db =
        conversation_repository_db();


    $stmt =
        $db->prepare(
            $sql
        );


    $stmt->execute(
        $params
    );


    $row =
        $stmt->fetch(
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
|
| A user is a participant of a conversation when their student profile
| (students.user_id) or company profile (companies.user_id) is linked to
| the conversation.
*/

function conversation_repository_is_participant(
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

    $row =
        conversation_repository_fetch_one(
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
| List Conversations
|--------------------------------------------------------------------------
*/

function conversation_repository_list(
    int $user_id,
    array $filters = []
): array {

    if ($user_id <= 0) {
        return [];
    }


    $sql = "
        SELECT
            c.*,

            s.full_name AS student_name,
            co.legal_name AS company_name,
            t.title AS training_title,

            (
                SELECT COUNT(*)
                FROM messages m
                WHERE
                    m.conversation_id = c.id
                    AND m.sender_user_id <> :list_user_id
                    AND m.read_at IS NULL
            ) AS unread_count

        FROM conversations c

        LEFT JOIN students s
            ON s.id = c.student_id

        LEFT JOIN companies co
            ON co.id = c.company_id

        LEFT JOIN training_applications a
            ON a.id = c.application_id

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        WHERE (
            c.student_id IN (
                SELECT s2.id
                FROM students s2
                WHERE s2.user_id = :student_user_id
            )
            OR
            c.company_id IN (
                SELECT co2.id
                FROM companies co2
                WHERE co2.user_id = :company_user_id
            )
        )
    ";


    $params = [
        ':list_user_id' =>
            $user_id,

        ':student_user_id' =>
            $user_id,

        ':company_user_id' =>
            $user_id
    ];


    /*
     * Application filter.
     */

    if (
        isset(
            $filters['application_id']
        )
        &&
        (int) $filters['application_id'] > 0
    ) {

        $sql .= "
            AND c.application_id = :application_id
        ";


        $params[':application_id'] =
            (int) $filters['application_id'];
    }


    /*
     * Keyword search (participant name or linked training title).
     */

    if (
        isset(
            $filters['keyword']
        )
        &&
        trim(
            (string)
                $filters['keyword']
        ) !== ''
    ) {

        $sql .= "
            AND (
                s.full_name LIKE :list_keyword
                OR co.legal_name LIKE :list_keyword
                OR t.title LIKE :list_keyword
            )
        ";


        $params[':list_keyword'] =
            '%' .
            trim(
                (string)
                    $filters['keyword']
            ) .
            '%';
    }


    $sql .= "
        ORDER BY
            COALESCE(
                c.updated_at,
                c.created_at
            ) DESC,
            c.id DESC
    ";


    /*
     * Pagination.
     */

    $limit =
        isset(
            $filters['limit']
        )
        ? (int)
            $filters['limit']
        : 50;


    $offset =
        isset(
            $filters['offset']
        )
        ? (int)
            $filters['offset']
        : 0;


    $limit =
        max(
            1,
            min(
                $limit,
                100
            )
        );


    $offset =
        max(
            0,
            $offset
        );


    $sql .= "
        LIMIT {$limit}
        OFFSET {$offset}
    ";


    return conversation_repository_fetch_all(
        $sql,
        $params
    );
}


/*
|--------------------------------------------------------------------------
| Find Conversation
|--------------------------------------------------------------------------
*/

function conversation_repository_find(
    int $conversation_id
): ?array {

    if (
        $conversation_id <= 0
    ) {
        return null;
    }


    return conversation_repository_fetch_one(
        "
        SELECT
            c.*,

            s.full_name AS student_name,
            co.legal_name AS company_name,
            t.title AS training_title

        FROM conversations c

        LEFT JOIN students s
            ON s.id = c.student_id

        LEFT JOIN companies co
            ON co.id = c.company_id

        LEFT JOIN training_applications a
            ON a.id = c.application_id

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        WHERE c.id = :conversation_id
        LIMIT 1
        ",
        [
            ':conversation_id' =>
                $conversation_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Find Conversation For User
|--------------------------------------------------------------------------
*/

function conversation_repository_find_for_user(
    int $conversation_id,
    int $user_id
): ?array {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {
        return null;
    }


    return conversation_repository_fetch_one(
        "
        SELECT
            c.*,

            s.full_name AS student_name,
            co.legal_name AS company_name,
            t.title AS training_title

        FROM conversations c

        LEFT JOIN students s
            ON s.id = c.student_id

        LEFT JOIN companies co
            ON co.id = c.company_id

        LEFT JOIN training_applications a
            ON a.id = c.application_id

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        WHERE c.id = :conversation_id
          AND (
              c.student_id IN (
                  SELECT s2.id
                  FROM students s2
                  WHERE s2.user_id = :student_user_id
              )
              OR
              c.company_id IN (
                  SELECT co2.id
                  FROM companies co2
                  WHERE co2.user_id = :company_user_id
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
}


/*
|--------------------------------------------------------------------------
| Find Conversation By Application
|--------------------------------------------------------------------------
*/

function conversation_repository_find_by_application(
    int $application_id,
    int $user_id
): ?array {

    if (
        $application_id <= 0
        ||
        $user_id <= 0
    ) {
        return null;
    }


    return conversation_repository_fetch_one(
        "
        SELECT
            c.*
        FROM conversations c
        WHERE c.application_id = :application_id
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
            ':application_id' =>
                $application_id,

            ':student_user_id' =>
                $user_id,

            ':company_user_id' =>
                $user_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Create Conversation
|--------------------------------------------------------------------------
|
| A conversation is always created for an accepted training application.
| The student and company are derived from the application.
*/

function conversation_repository_create(
    array $data
): array|false {

    $db =
        conversation_repository_db();


    $application_id =
        isset(
            $data['application_id']
        )
        ? (int)
            $data['application_id']
        : 0;


    if ($application_id <= 0) {
        return false;
    }


    /*
     * Resolve the student and company from the application.
     */

    $application =
        conversation_repository_fetch_one(
            "
            SELECT
                a.student_id,
                a.training_id,
                t.company_id
            FROM training_applications a
            LEFT JOIN training_listings t
                ON t.id = a.training_id
            WHERE a.id = :application_id
            LIMIT 1
            ",
            [
                ':application_id' =>
                    $application_id
            ]
        );


    if (!$application) {
        return false;
    }


    $student_id =
        (int) (
            $application['student_id']
            ?? 0
        );


    $company_id =
        (int) (
            $application['company_id']
            ?? 0
        );


    if ($student_id <= 0 || $company_id <= 0) {
        return false;
    }


    /*
     * Return the existing conversation when one is already
     * linked to this application (application_id is unique).
     */

    $existing =
        conversation_repository_fetch_one(
            "
            SELECT
                id
            FROM conversations
            WHERE application_id = :application_id
            LIMIT 1
            ",
            [
                ':application_id' =>
                    $application_id
            ]
        );


    if ($existing) {

        return conversation_repository_find(
            (int) $existing['id']
        );
    }


    $sql = "
        INSERT INTO conversations
        (
            student_id,
            company_id,
            application_id,
            created_at,
            updated_at
        )
        VALUES
        (
            :student_id,
            :company_id,
            :application_id,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
        )
    ";


    try {

        $stmt =
            $db->prepare(
                $sql
            );


        $success =
            $stmt->execute(
                [
                    ':student_id' =>
                        $student_id,

                    ':company_id' =>
                        $company_id,

                    ':application_id' =>
                        $application_id
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


    return conversation_repository_find(
        $id
    );
}


/*
|--------------------------------------------------------------------------
| Update Conversation
|--------------------------------------------------------------------------
|
| The conversations table has no editable columns beyond timestamps,
| so updates only refresh updated_at for the participating user.
*/

function conversation_repository_update(
    int $conversation_id,
    int $user_id,
    array $data
): bool {

    if (
        $conversation_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }


    if (
        !conversation_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {
        return false;
    }


    $db =
        conversation_repository_db();


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


    return true;
}


/*
|--------------------------------------------------------------------------
| Archive
|--------------------------------------------------------------------------
*/

function conversation_repository_archive(
    int $conversation_id,
    int $user_id
): bool {

    return conversation_repository_update(
        $conversation_id,
        $user_id,
        []
    );
}


/*
|--------------------------------------------------------------------------
| Restore
|--------------------------------------------------------------------------
*/

function conversation_repository_restore(
    int $conversation_id,
    int $user_id
): bool {

    return conversation_repository_update(
        $conversation_id,
        $user_id,
        []
    );
}


/*
|--------------------------------------------------------------------------
| Delete Conversation
|--------------------------------------------------------------------------
*/

function conversation_repository_delete(
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


    if (
        !conversation_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {
        return false;
    }


    $db =
        conversation_repository_db();


    try {

        $db->beginTransaction();


        $stmt =
            $db->prepare(
                "
                DELETE FROM messages
                WHERE conversation_id = :conversation_id
                "
            );


        $stmt->execute(
            [
                ':conversation_id' =>
                    $conversation_id
            ]
        );


        $stmt =
            $db->prepare(
                "
                DELETE FROM conversations
                WHERE id = :conversation_id
                "
            );


        $stmt->execute(
            [
                ':conversation_id' =>
                    $conversation_id
            ]
        );


        $deleted =
            $stmt->rowCount() > 0;


        $db->commit();


        return $deleted;

    } catch (
        Throwable $e
    ) {

        if (
            $db->inTransaction()
        ) {

            $db->rollBack();
        }


        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Participants
|--------------------------------------------------------------------------
|
| A conversation has exactly two participants: the student's user and
| the company's user.
*/

function conversation_repository_participants(
    int $conversation_id
): array {

    if (
        $conversation_id <= 0
    ) {
        return [];
    }


    return conversation_repository_fetch_all(
        "
        SELECT
            u.id AS user_id,
            u.email,
            s.full_name AS name,
            'student' AS role
        FROM conversations c

        INNER JOIN students s
            ON s.id = c.student_id

        INNER JOIN users u
            ON u.id = s.user_id

        WHERE c.id = :conversation_id

        UNION ALL

        SELECT
            u.id AS user_id,
            u.email,
            co.legal_name AS name,
            'company' AS role
        FROM conversations c

        INNER JOIN companies co
            ON co.id = c.company_id

        INNER JOIN users u
            ON u.id = co.user_id

        WHERE c.id = :conversation_id
        ",
        [
            ':conversation_id' =>
                $conversation_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Participant Exists
|--------------------------------------------------------------------------
*/

function conversation_repository_participant_exists(
    int $conversation_id,
    int $user_id
): bool {

    return conversation_repository_is_participant(
        $conversation_id,
        $user_id
    );
}


/*
|--------------------------------------------------------------------------
| Add Participant
|--------------------------------------------------------------------------
|
| Participants are implicit from the linked student/company profiles,
| so this only verifies that the given user is one of them.
*/

function conversation_repository_add_participant(
    int $conversation_id,
    int $participant_id,
    int $added_by
): bool {

    if (
        $conversation_id <= 0
        ||
        $participant_id <= 0
    ) {
        return false;
    }


    return conversation_repository_is_participant(
        $conversation_id,
        $participant_id
    );
}


/*
|--------------------------------------------------------------------------
| Remove Participant
|--------------------------------------------------------------------------
*/

function conversation_repository_remove_participant(
    int $conversation_id,
    int $participant_id
): bool {

    return false;
}


/*
|--------------------------------------------------------------------------
| Mark Conversation As Read
|--------------------------------------------------------------------------
*/

function conversation_repository_mark_read(
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


    if (
        !conversation_repository_is_participant(
            $conversation_id,
            $user_id
        )
    ) {
        return false;
    }


    $db =
        conversation_repository_db();


    $stmt =
        $db->prepare(
            "
            UPDATE messages
            SET
                is_read = 1,
                read_at = CURRENT_TIMESTAMP
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

function conversation_repository_unread_count(
    int $user_id
): int {

    if ($user_id <= 0) {
        return 0;
    }


    $row =
        conversation_repository_fetch_one(
            "
            SELECT
                COUNT(DISTINCT c.id) AS unread_count
            FROM conversations c
            INNER JOIN messages m
                ON m.conversation_id = c.id
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
            ",
            [
                ':student_user_id' =>
                    $user_id,

                ':company_user_id' =>
                    $user_id,

                ':sender_user_id' =>
                    $user_id
            ]
        );


    return (int) (
        $row['unread_count']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

function conversation_repository_search(
    int $user_id,
    string $keyword,
    array $filters = []
): array {

    if (
        $user_id <= 0
        ||
        trim($keyword) === ''
    ) {
        return [];
    }


    $sql = "
        SELECT
            c.*,

            s.full_name AS student_name,
            co.legal_name AS company_name,
            t.title AS training_title

        FROM conversations c

        LEFT JOIN students s
            ON s.id = c.student_id

        LEFT JOIN companies co
            ON co.id = c.company_id

        LEFT JOIN training_applications a
            ON a.id = c.application_id

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        WHERE (
            c.student_id IN (
                SELECT s2.id
                FROM students s2
                WHERE s2.user_id = :student_user_id
            )
            OR
            c.company_id IN (
                SELECT co2.id
                FROM companies co2
                WHERE co2.user_id = :company_user_id
            )
        )
          AND (
              s.full_name LIKE :keyword_name
              OR co.legal_name LIKE :keyword_name
              OR t.title LIKE :keyword_name
          )
    ";


    $params = [
        ':student_user_id' =>
            $user_id,

        ':company_user_id' =>
            $user_id,

        ':keyword_name' =>
            '%' .
            trim($keyword) .
            '%'
    ];


    $sql .= "
        ORDER BY
            COALESCE(
                c.updated_at,
                c.created_at
            ) DESC
    ";


    $limit =
        isset(
            $filters['limit']
        )
        ? (int)
            $filters['limit']
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


    return conversation_repository_fetch_all(
        $sql,
        $params
    );
}
