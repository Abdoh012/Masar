<?php

/**
 * MASAR - Certificate Appeal Repository
 *
 * Database access layer for certificate appeals.
 *
 * Service
 *    ↓
 * Certificate Appeal Repository
 *    ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/database/connection.php';


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_db()
{
    if (function_exists('db')) {
        return db();
    }

    if (function_exists('database')) {
        return database();
    }

    if (function_exists('get_db')) {
        return get_db();
    }

    if (function_exists('get_database_connection')) {
        return get_database_connection();
    }

    throw new RuntimeException(
        'Database connection helper is not available.'
    );
}


/*
|--------------------------------------------------------------------------
| Find Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_find(
    int $appeal_id
): ?array {

    if ($appeal_id <= 0) {
        return null;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        SELECT
            ca.*
        FROM certificate_appeals ca
        WHERE ca.id = :appeal_id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':appeal_id' => $appeal_id
    ]);

    $appeal = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    return $appeal ?: null;
}


/*
|--------------------------------------------------------------------------
| Find By Certificate
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_find_by_certificate(
    int $certificate_id
): array {

    if ($certificate_id <= 0) {
        return [];
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        SELECT
            ca.*
        FROM certificate_appeals ca
        WHERE ca.certificate_id = :certificate_id
        ORDER BY
            ca.created_at DESC,
            ca.id DESC
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':certificate_id' => $certificate_id
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| List Appeals
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_list(
    array $filters = []
): array {

    $db = certificate_appeal_repository_db();

    $where = [];

    $params = [];


    /*
     * User
     */

    if (
        isset($filters['user_id'])
        &&
        $filters['user_id'] !== ''
    ) {

        $where[] =
            'ca.user_id = :user_id';

        $params[':user_id'] =
            (int) $filters['user_id'];
    }


    /*
     * Student
     */

    if (
        isset($filters['student_id'])
        &&
        $filters['student_id'] !== ''
    ) {

        $where[] =
            'ca.student_id = :student_id';

        $params[':student_id'] =
            (int) $filters['student_id'];
    }


    /*
     * Certificate
     */

    if (
        isset($filters['certificate_id'])
        &&
        $filters['certificate_id'] !== ''
    ) {

        $where[] =
            'ca.certificate_id = :certificate_id';

        $params[':certificate_id'] =
            (int) $filters['certificate_id'];
    }


    /*
     * Status
     */

    if (
        isset($filters['status'])
        &&
        $filters['status'] !== ''
    ) {

        $where[] =
            'ca.status = :status';

        $params[':status'] =
            (string) $filters['status'];
    }


    /*
     * Reviewer
     */

    if (
        isset($filters['reviewed_by'])
        &&
        $filters['reviewed_by'] !== ''
    ) {

        $where[] =
            'ca.reviewed_by = :reviewed_by';

        $params[':reviewed_by'] =
            (int) $filters['reviewed_by'];
    }


    /*
     * Date range
     */

    if (
        !empty($filters['created_from'])
    ) {

        $where[] =
            'ca.created_at >= :created_from';

        $params[':created_from'] =
            (string) $filters['created_from'];
    }

    if (
        !empty($filters['created_to'])
    ) {

        $where[] =
            'ca.created_at <= :created_to';

        $params[':created_to'] =
            (string) $filters['created_to'];
    }


    /*
     * Keyword
     */

    if (
        !empty($filters['keyword'])
    ) {

        $where[] = "
            (
                ca.reason LIKE :keyword
                OR ca.description LIKE :keyword
                OR ca.message LIKE :keyword
                OR ca.decision LIKE :keyword
                OR ca.review_notes LIKE :keyword
            )
        ";

        $params[':keyword'] =
            '%' .
            (string) $filters['keyword'] .
            '%';
    }


    $sql = "
        SELECT
            ca.*
        FROM certificate_appeals ca
    ";

    if (!empty($where)) {

        $sql .=
            ' WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }

    $sql .= "
        ORDER BY
            ca.created_at DESC,
            ca.id DESC
    ";


    /*
     * Pagination
     */

    $limit =
        isset($filters['limit'])
        ? (int) $filters['limit']
        : 50;

    $offset =
        isset($filters['offset'])
        ? (int) $filters['offset']
        : 0;

    $limit = max(
        1,
        min($limit, 100)
    );

    $offset = max(
        0,
        $offset
    );

    $sql .=
        " LIMIT {$limit} OFFSET {$offset}";


    $stmt = $db->prepare($sql);

    $stmt->execute(
        $params
    );

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Create Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_create(
    array $data
): ?array {

    if (empty($data)) {
        return null;
    }

    $db = certificate_appeal_repository_db();

    /*
     * Only repository-supported fields
     * are allowed into INSERT.
     */

    $allowed = [
        'certificate_id',
        'user_id',
        'student_id',
        'reason',
        'description',
        'message',
        'status',
        'metadata'
    ];

    $fields = [];

    $placeholders = [];

    $params = [];

    foreach ($allowed as $field) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $fields[] =
                $field;

            $placeholders[] =
                ':' . $field;

            $params[':' . $field] =
                $data[$field];
        }
    }


    if (empty($fields)) {
        return null;
    }


    /*
     * Default status.
     */

    if (
        !in_array(
            'status',
            $fields,
            true
        )
    ) {

        $fields[] =
            'status';

        $placeholders[] =
            ':status';

        $params[':status'] =
            'pending';
    }


    $sql = "
        INSERT INTO certificate_appeals
        (
            " .
            implode(
                ', ',
                $fields
            ) .
            "
        )
        VALUES
        (
            " .
            implode(
                ', ',
                $placeholders
            ) .
            "
        )
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute(
        $params
    );

    $id =
        (int) $db->lastInsertId();

    if ($id <= 0) {
        return null;
    }

    return certificate_appeal_repository_find(
        $id
    );
}


/*
|--------------------------------------------------------------------------
| Update Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_update(
    int $appeal_id,
    array $data
): bool {

    if (
        $appeal_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $allowed = [
        'reason',
        'description',
        'message',
        'status',
        'decision',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'withdrawn_at',
        'cancelled_at',
        'metadata'
    ];

    $sets = [];

    $params = [
        ':appeal_id' => $appeal_id
    ];

    foreach ($allowed as $field) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $sets[] =
                "{$field} = :{$field}";

            $params[":{$field}"] =
                $data[$field];
        }
    }

    if (empty($sets)) {
        return false;
    }

    /*
     * Expected to exist in the schema.
     */

    $sets[] =
        'updated_at = NOW()';

    $sql = "
        UPDATE certificate_appeals
        SET
            " .
            implode(
                ', ',
                $sets
            ) .
            "
        WHERE id = :appeal_id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    return $stmt->execute(
        $params
    );
}


/*
|--------------------------------------------------------------------------
| Certificate Exists
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_certificate_exists(
    int $certificate_id
): bool {

    if ($certificate_id <= 0) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        SELECT id
        FROM certificates
        WHERE id = :certificate_id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':certificate_id' =>
            $certificate_id
    ]);

    return (bool) $stmt->fetch(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Student Belongs To User
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_student_belongs_to_user(
    int $student_id,
    int $user_id
): bool {

    if (
        $student_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        SELECT id
        FROM students
        WHERE
            id = :student_id
            AND user_id = :user_id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':student_id' =>
            $student_id,

        ':user_id' =>
            $user_id
    ]);

    return (bool) $stmt->fetch(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Active Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_has_active_appeal(
    int $certificate_id,
    int $user_id
): bool {

    if (
        $certificate_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        SELECT id
        FROM certificate_appeals
        WHERE
            certificate_id = :certificate_id
            AND user_id = :user_id
            AND status IN (
                'pending',
                'under_review',
                'reviewing'
            )
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':certificate_id' =>
            $certificate_id,

        ':user_id' =>
            $user_id
    ]);

    return (bool) $stmt->fetch(
        PDO::FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Withdraw Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_withdraw(
    int $appeal_id,
    int $user_id
): bool {

    if (
        $appeal_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        UPDATE certificate_appeals
        SET
            status = 'withdrawn',
            withdrawn_at = NOW(),
            updated_at = NOW()
        WHERE
            id = :appeal_id
            AND user_id = :user_id
            AND status IN (
                'pending',
                'under_review',
                'reviewing'
            )
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    return $stmt->execute([
        ':appeal_id' =>
            $appeal_id,

        ':user_id' =>
            $user_id
    ]);
}


/*
|--------------------------------------------------------------------------
| Review Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_review(
    int $appeal_id,
    array $data
): bool {

    if (
        $appeal_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sets = [
        "status = 'under_review'"
    ];

    $params = [
        ':appeal_id' =>
            $appeal_id
    ];

    if (
        array_key_exists(
            'review_notes',
            $data
        )
    ) {

        $sets[] =
            'review_notes = :review_notes';

        $params[':review_notes'] =
            $data['review_notes'];
    }

    if (
        array_key_exists(
            'reviewed_by',
            $data
        )
    ) {

        $sets[] =
            'reviewed_by = :reviewed_by';

        $params[':reviewed_by'] =
            $data['reviewed_by'];
    }

    if (
        array_key_exists(
            'reviewed_at',
            $data
        )
    ) {

        $sets[] =
            'reviewed_at = :reviewed_at';

        $params[':reviewed_at'] =
            $data['reviewed_at'];
    } else {

        $sets[] =
            'reviewed_at = NOW()';
    }

    $sets[] =
        'updated_at = NOW()';

    $sql = "
        UPDATE certificate_appeals
        SET
            " .
            implode(
                ', ',
                $sets
            ) .
            "
        WHERE
            id = :appeal_id
            AND status NOT IN (
                'approved',
                'rejected',
                'withdrawn',
                'cancelled'
            )
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    return $stmt->execute(
        $params
    );
}


/*
|--------------------------------------------------------------------------
| Approve Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_approve(
    int $appeal_id,
    array $data = []
): bool {

    if ($appeal_id <= 0) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    try {

        $db->beginTransaction();


        /*
         * Lock the appeal row.
         */

        $lockSql = "
            SELECT *
            FROM certificate_appeals
            WHERE id = :appeal_id
            FOR UPDATE
        ";

        $lockStmt =
            $db->prepare($lockSql);

        $lockStmt->execute([
            ':appeal_id' =>
                $appeal_id
        ]);

        $appeal =
            $lockStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$appeal) {
            $db->rollBack();
            return false;
        }


        $status =
            strtolower(
                (string) (
                    $appeal['status']
                    ?? ''
                )
            );

        if (
            in_array(
                $status,
                [
                    'approved',
                    'rejected',
                    'withdrawn',
                    'cancelled'
                ],
                true
            )
        ) {
            $db->rollBack();
            return false;
        }


        /*
         * Approve appeal.
         */

        $sets = [
            "status = 'approved'"
        ];

        $params = [
            ':appeal_id' =>
                $appeal_id
        ];


        if (
            array_key_exists(
                'decision',
                $data
            )
        ) {

            $sets[] =
                'decision = :decision';

            $params[':decision'] =
                $data['decision'];
        }


        if (
            array_key_exists(
                'reviewed_by',
                $data
            )
        ) {

            $sets[] =
                'reviewed_by = :reviewed_by';

            $params[':reviewed_by'] =
                $data['reviewed_by'];
        }


        $sets[] =
            'reviewed_at = ' .
            (
                array_key_exists(
                    'reviewed_at',
                    $data
                )
                ? ':reviewed_at'
                : 'NOW()'
            );

        if (
            array_key_exists(
                'reviewed_at',
                $data
            )
        ) {
            $params[':reviewed_at'] =
                $data['reviewed_at'];
        }

        $sets[] =
            'updated_at = NOW()';


        $sql = "
            UPDATE certificate_appeals
            SET
                " .
                implode(
                    ', ',
                    $sets
                ) .
                "
            WHERE id = :appeal_id
            LIMIT 1
        ";

        $stmt =
            $db->prepare($sql);

        $stmt->execute(
            $params
        );


        /*
         * If the schema supports certificate status,
         * restore/activate the certificate after approval.
         */

        if (
            !empty(
                $appeal['certificate_id']
            )
        ) {

            $certificate_id =
                (int) $appeal['certificate_id'];

            $certificateSql = "
                UPDATE certificates
                SET
                    status = CASE
                        WHEN status = 'revoked'
                        THEN 'issued'
                        ELSE status
                    END,
                    updated_at = NOW()
                WHERE id = :certificate_id
                LIMIT 1
            ";

            $certificateStmt =
                $db->prepare(
                    $certificateSql
                );

            $certificateStmt->execute([
                ':certificate_id' =>
                    $certificate_id
            ]);
        }


        $db->commit();

        return true;

    } catch (Throwable $e) {

        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Reject Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_reject(
    int $appeal_id,
    array $data
): bool {

    if ($appeal_id <= 0) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $reason =
        trim(
            (string) (
                $data['reason']
                ?? ''
            )
        );

    if ($reason === '') {
        return false;
    }


    $sets = [
        "status = 'rejected'",
        'decision = :reason'
    ];

    $params = [
        ':appeal_id' =>
            $appeal_id,

        ':reason' =>
            $reason
    ];


    if (
        array_key_exists(
            'reviewed_by',
            $data
        )
    ) {

        $sets[] =
            'reviewed_by = :reviewed_by';

        $params[':reviewed_by'] =
            $data['reviewed_by'];
    }


    if (
        array_key_exists(
            'reviewed_at',
            $data
        )
    ) {

        $sets[] =
            'reviewed_at = :reviewed_at';

        $params[':reviewed_at'] =
            $data['reviewed_at'];

    } else {

        $sets[] =
            'reviewed_at = NOW()';
    }


    $sets[] =
        'updated_at = NOW()';


    $sql = "
        UPDATE certificate_appeals
        SET
            " .
            implode(
                ', ',
                $sets
            ) .
            "
        WHERE
            id = :appeal_id
            AND status NOT IN (
                'approved',
                'rejected',
                'withdrawn',
                'cancelled'
            )
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    return $stmt->execute(
        $params
    );
}


/*
|--------------------------------------------------------------------------
| Cancel Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_cancel(
    int $appeal_id,
    string $reason,
    ?int $reviewed_by = null
): bool {

    if (
        $appeal_id <= 0
        ||
        trim($reason) === ''
    ) {
        return false;
    }

    $db = certificate_appeal_repository_db();

    $sql = "
        UPDATE certificate_appeals
        SET
            status = 'cancelled',
            decision = :reason,
            reviewed_by = :reviewed_by,
            reviewed_at = NOW(),
            cancelled_at = NOW(),
            updated_at = NOW()
        WHERE
            id = :appeal_id
            AND status NOT IN (
                'approved',
                'rejected',
                'withdrawn',
                'cancelled'
            )
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    return $stmt->execute([
        ':reason' =>
            $reason,

        ':reviewed_by' =>
            $reviewed_by,

        ':appeal_id' =>
            $appeal_id
    ]);
}


/*
|--------------------------------------------------------------------------
| Count
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_count(
    array $filters = []
): int {

    $db = certificate_appeal_repository_db();

    $where = [];

    $params = [];


    if (
        isset($filters['user_id'])
        &&
        $filters['user_id'] !== ''
    ) {

        $where[] =
            'ca.user_id = :user_id';

        $params[':user_id'] =
            (int) $filters['user_id'];
    }


    if (
        isset($filters['student_id'])
        &&
        $filters['student_id'] !== ''
    ) {

        $where[] =
            'ca.student_id = :student_id';

        $params[':student_id'] =
            (int) $filters['student_id'];
    }


    if (
        isset($filters['certificate_id'])
        &&
        $filters['certificate_id'] !== ''
    ) {

        $where[] =
            'ca.certificate_id = :certificate_id';

        $params[':certificate_id'] =
            (int) $filters['certificate_id'];
    }


    if (
        isset($filters['status'])
        &&
        $filters['status'] !== ''
    ) {

        $where[] =
            'ca.status = :status';

        $params[':status'] =
            (string) $filters['status'];
    }


    $sql = "
        SELECT COUNT(*) AS total
        FROM certificate_appeals ca
    ";

    if (!empty($where)) {

        $sql .=
            ' WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }

    $stmt = $db->prepare($sql);

    $stmt->execute(
        $params
    );

    return (int) (
        $stmt->fetchColumn()
        ?: 0
    );
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_statistics(
    array $filters = []
): array {

    $statuses = [
        'pending',
        'under_review',
        'reviewing',
        'approved',
        'rejected',
        'withdrawn',
        'cancelled'
    ];

    $statistics = [
        'total' => 0
    ];

    $statistics['total'] =
        certificate_appeal_repository_count(
            $filters
        );

    foreach ($statuses as $status) {

        $statistics[$status] =
            certificate_appeal_repository_count(
                array_merge(
                    $filters,
                    [
                        'status' =>
                            $status
                    ]
                )
            );
    }

    return $statistics;
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

function certificate_appeal_repository_search(
    array $filters = []
): array {

    if (
        empty(
            $filters['keyword']
        )
    ) {
        return [];
    }

    return certificate_appeal_repository_list(
        $filters
    );
}
