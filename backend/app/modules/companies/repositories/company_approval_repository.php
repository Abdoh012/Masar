<?php

/**
 * MASAR - Company Approval Repository
 *
 * Responsible only for database operations related
 * to company approval / rejection history.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No business logic.
 * - No HTTP logic.
 * - Uses PDO through the core database connection.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/database/connection.php';


/*
|--------------------------------------------------------------------------
| Create Approval History Record
|--------------------------------------------------------------------------
|
| Expected data:
|
| [
|     'company_id'    => int,
|     'admin_user_id' => int,
|     'action'        => 'approved' | 'rejected',
|     'reason'        => string|null
| ]
|
*/

function company_approval_repository_create(
    array $data
) {

    $pdo =
        db_connection();


    /*
    |--------------------------------------------------------------------------
    | SQL
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO company_approval_history
        (
            company_id,
            admin_user_id,
            action,
            reason,
            created_at
        )
        VALUES
        (
            :company_id,
            :admin_user_id,
            :action,
            :reason,
            NOW()
        )
    ";


    /*
    |--------------------------------------------------------------------------
    | Prepare
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare($sql);


    /*
    |--------------------------------------------------------------------------
    | Execute
    |--------------------------------------------------------------------------
    */

    $success =
        $stmt->execute(
            [

                ':company_id' =>
                    (int) $data['company_id'],

                ':admin_user_id' =>
                    (int) $data['admin_user_id'],

                ':action' =>
                    $data['action'],

                ':reason' =>
                    $data['reason'] ?? null,

            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    if (!$success) {

        return false;
    }


    return (int)
        $pdo->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| Find Approval Record By ID
|--------------------------------------------------------------------------
*/

function company_approval_repository_find_by_id(
    int $approval_id
) {

    $pdo =
        db_connection();


    $sql = "
        SELECT
            id,
            company_id,
            admin_user_id,
            action,
            reason,
            created_at
        FROM company_approval_history
        WHERE id = :id
        LIMIT 1
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        [

            ':id' =>
                $approval_id,

        ]
    );


    $record =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$record) {

        return null;
    }


    return $record;
}


/*
|--------------------------------------------------------------------------
| Get Company Approval History
|--------------------------------------------------------------------------
|
| Returns the complete history for one company.
|
*/

function company_approval_repository_get_by_company_id(
    int $company_id
): array {

    $pdo =
        db_connection();


    $sql = "
        SELECT
            h.id,
            h.company_id,
            h.admin_user_id,
            h.action,
            h.reason,
            h.created_at,

            u.name AS admin_name

        FROM company_approval_history h

        LEFT JOIN users u
            ON u.id = h.admin_user_id

        WHERE h.company_id = :company_id

        ORDER BY
            h.created_at DESC,
            h.id DESC
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        [

            ':company_id' =>
                $company_id,

        ]
    );


    return
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}


/*
|--------------------------------------------------------------------------
| Get Latest Approval Action
|--------------------------------------------------------------------------
*/

function company_approval_repository_get_latest(
    int $company_id
) {

    $pdo =
        db_connection();


    $sql = "
        SELECT
            h.id,
            h.company_id,
            h.admin_user_id,
            h.action,
            h.reason,
            h.created_at,

            u.name AS admin_name

        FROM company_approval_history h

        LEFT JOIN users u
            ON u.id = h.admin_user_id

        WHERE h.company_id = :company_id

        ORDER BY
            h.created_at DESC,
            h.id DESC

        LIMIT 1
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        [

            ':company_id' =>
                $company_id,

        ]
    );


    $record =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$record) {

        return null;
    }


    return $record;
}


/*
|--------------------------------------------------------------------------
| Get Approval History By Admin
|--------------------------------------------------------------------------
|
| Returns all approval/rejection actions
| performed by a specific admin.
|
*/

function company_approval_repository_get_by_admin_id(
    int $admin_user_id,
    int $limit = 20,
    int $offset = 0
): array {

    $pdo =
        db_connection();


    $sql = "
        SELECT
            h.id,
            h.company_id,
            h.admin_user_id,
            h.action,
            h.reason,
            h.created_at,

            c.company_name,

            u.name AS admin_name

        FROM company_approval_history h

        INNER JOIN companies c
            ON c.id = h.company_id

        LEFT JOIN users u
            ON u.id = h.admin_user_id

        WHERE h.admin_user_id = :admin_user_id

        ORDER BY
            h.created_at DESC,
            h.id DESC

        LIMIT :limit
        OFFSET :offset
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->bindValue(
        ':admin_user_id',
        $admin_user_id,
        PDO::PARAM_INT
    );


    $stmt->bindValue(
        ':limit',
        $limit,
        PDO::PARAM_INT
    );


    $stmt->bindValue(
        ':offset',
        $offset,
        PDO::PARAM_INT
    );


    $stmt->execute();


    return
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}


/*
|--------------------------------------------------------------------------
| Count Actions By Admin
|--------------------------------------------------------------------------
*/

function company_approval_repository_count_by_admin_id(
    int $admin_user_id
): int {

    $pdo =
        db_connection();


    $sql = "
        SELECT COUNT(*)
        FROM company_approval_history
        WHERE admin_user_id = :admin_user_id
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        [

            ':admin_user_id' =>
                $admin_user_id,

        ]
    );


    return (int)
        $stmt->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Count Company Approval Actions
|--------------------------------------------------------------------------
|
| Optional filtering by action:
|
| approved
| rejected
|
*/

function company_approval_repository_count_by_action(
    string $action
): int {

    $pdo =
        db_connection();


    $sql = "
        SELECT COUNT(*)
        FROM company_approval_history
        WHERE action = :action
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        [

            ':action' =>
                $action,

        ]
    );


    return (int)
        $stmt->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Get Approval Records By Action
|--------------------------------------------------------------------------
*/

function company_approval_repository_get_by_action(
    string $action,
    int $limit = 20,
    int $offset = 0
): array {

    $pdo =
        db_connection();


    $sql = "
        SELECT
            h.id,
            h.company_id,
            h.admin_user_id,
            h.action,
            h.reason,
            h.created_at,

            c.company_name,

            u.name AS admin_name

        FROM company_approval_history h

        INNER JOIN companies c
            ON c.id = h.company_id

        LEFT JOIN users u
            ON u.id = h.admin_user_id

        WHERE h.action = :action

        ORDER BY
            h.created_at DESC,
            h.id DESC

        LIMIT :limit
        OFFSET :offset
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->bindValue(
        ':action',
        $action,
        PDO::PARAM_STR
    );


    $stmt->bindValue(
        ':limit',
        $limit,
        PDO::PARAM_INT
    );


    $stmt->bindValue(
        ':offset',
        $offset,
        PDO::PARAM_INT
    );


    $stmt->execute();


    return
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}


/*
|--------------------------------------------------------------------------
| Delete Approval History For Company
|--------------------------------------------------------------------------
|
| This should normally NOT be used because approval history
| is important for auditing.
|
| Kept here only for controlled administrative operations.
|
*/

function company_approval_repository_delete_by_company_id(
    int $company_id
): bool {

    $pdo =
        db_connection();


    $sql = "
        DELETE FROM company_approval_history
        WHERE company_id = :company_id
    ";


    $stmt =
        $pdo->prepare($sql);


    return $stmt->execute(
        [

            ':company_id' =>
                $company_id,

        ]
    );
}

// /**
//  * MASAR - Company Approval Repository
//  *
//  * Responsible only for database operations related
//  * to company approval and rejection.
//  *
//  * Native PHP - No OOP.
//  */


// /*
// |--------------------------------------------------------------------------
// | Dependencies
// |--------------------------------------------------------------------------
// */

// require_once __DIR__ . '/../../../core/database/connection.php';


// /*
// |--------------------------------------------------------------------------
// | Get Company Approval Data
// |--------------------------------------------------------------------------
// |
// | Returns the company together with the related user information.
// |
// */

// function company_approval_repository_find_by_company_id(
//     int $company_id
// ) {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id AS company_id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at,

//             u.id AS account_user_id,
//             u.email,
//             u.status AS user_status,
//             u.created_at AS user_created_at

//         FROM companies c

//         INNER JOIN users u
//             ON u.id = c.user_id

//         WHERE c.id = :company_id

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     $company =
//         $stmt->fetch(
//             PDO::FETCH_ASSOC
//         );


//     if (!$company) {
//         return null;
//     }


//     return $company;
// }


// /*
// |--------------------------------------------------------------------------
// | Get Pending Company Approvals
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_get_pending(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id AS company_id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at,

//             u.email,
//             u.status AS user_status

//         FROM companies c

//         INNER JOIN users u
//             ON u.id = c.user_id

//         WHERE c.approval_status = 'pending'

//         ORDER BY
//             c.created_at ASC,
//             c.id ASC

//         LIMIT :limit
//         OFFSET :offset
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->bindValue(
//         ':limit',
//         $limit,
//         PDO::PARAM_INT
//     );


//     $stmt->bindValue(
//         ':offset',
//         $offset,
//         PDO::PARAM_INT
//     );


//     $stmt->execute();


//     return $stmt->fetchAll(
//         PDO::FETCH_ASSOC
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Count Pending Company Approvals
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_count_pending(): int
// {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             COUNT(*)

//         FROM companies

//         WHERE approval_status = 'pending'
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute();


//     return (int)
//         $stmt->fetchColumn();
// }


// /*
// |--------------------------------------------------------------------------
// | Approve Company
// |--------------------------------------------------------------------------
// |
// | Changes company approval status to approved.
// |
// */

// function company_approval_repository_approve(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         UPDATE companies

//         SET
//             approval_status = 'approved',
//             updated_at = NOW()

//         WHERE
//             id = :company_id

//             AND approval_status = 'pending'
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     return $stmt->rowCount() > 0;
// }


// /*
// |--------------------------------------------------------------------------
// | Reject Company
// |--------------------------------------------------------------------------
// |
// | Changes company approval status to rejected.
// |
// | The rejection reason is stored separately if the
// | database contains a rejection_reason column.
// |
// */

// function company_approval_repository_reject(
//     int $company_id,
//     string $rejection_reason
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         UPDATE companies

//         SET
//             approval_status = 'rejected',
//             rejection_reason = :rejection_reason,
//             updated_at = NOW()

//         WHERE
//             id = :company_id

//             AND approval_status = 'pending'
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' =>
//                 $company_id,

//             ':rejection_reason' =>
//                 $rejection_reason
//         ]
//     );


//     return $stmt->rowCount() > 0;
// }


// /*
// |--------------------------------------------------------------------------
// | Reset Company Approval
// |--------------------------------------------------------------------------
// |
// | Used when an administrator wants to send a previously
// | rejected company back for review.
// |
// */

// function company_approval_repository_reset(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         UPDATE companies

//         SET
//             approval_status = 'pending',
//             rejection_reason = NULL,
//             updated_at = NOW()

//         WHERE
//             id = :company_id

//             AND approval_status = 'rejected'
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     return $stmt->rowCount() > 0;
// }


// /*
// |--------------------------------------------------------------------------
// | Get Approved Companies
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_get_approved(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id AS company_id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at,

//             u.email,
//             u.status AS user_status

//         FROM companies c

//         INNER JOIN users u
//             ON u.id = c.user_id

//         WHERE c.approval_status = 'approved'

//         ORDER BY
//             c.updated_at DESC,
//             c.id DESC

//         LIMIT :limit
//         OFFSET :offset
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->bindValue(
//         ':limit',
//         $limit,
//         PDO::PARAM_INT
//     );


//     $stmt->bindValue(
//         ':offset',
//         $offset,
//         PDO::PARAM_INT
//     );


//     $stmt->execute();


//     return $stmt->fetchAll(
//         PDO::FETCH_ASSOC
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Get Rejected Companies
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_get_rejected(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id AS company_id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.rejection_reason,
//             c.created_at,
//             c.updated_at,

//             u.email,
//             u.status AS user_status

//         FROM companies c

//         INNER JOIN users u
//             ON u.id = c.user_id

//         WHERE c.approval_status = 'rejected'

//         ORDER BY
//             c.updated_at DESC,
//             c.id DESC

//         LIMIT :limit
//         OFFSET :offset
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->bindValue(
//         ':limit',
//         $limit,
//         PDO::PARAM_INT
//     );


//     $stmt->bindValue(
//         ':offset',
//         $offset,
//         PDO::PARAM_INT
//     );


//     $stmt->execute();


//     return $stmt->fetchAll(
//         PDO::FETCH_ASSOC
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Count Companies By Approval Status
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_count_by_status(
//     string $status
// ): int {

//     $allowed_statuses = [
//         'pending',
//         'approved',
//         'rejected'
//     ];


//     if (
//         !in_array(
//             $status,
//             $allowed_statuses,
//             true
//         )
//     ) {

//         return 0;
//     }


//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             COUNT(*)

//         FROM companies

//         WHERE approval_status = :approval_status
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':approval_status' =>
//                 $status
//         ]
//     );


//     return (int)
//         $stmt->fetchColumn();
// }


// /*
// |--------------------------------------------------------------------------
// | Check If Company Is Pending
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_is_pending(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             id

//         FROM companies

//         WHERE
//             id = :company_id

//             AND approval_status = 'pending'

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     return $stmt->fetch(
//         PDO::FETCH_ASSOC
//     ) !== false;
// }


// /*
// |--------------------------------------------------------------------------
// | Check If Company Is Approved
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_is_approved(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             id

//         FROM companies

//         WHERE
//             id = :company_id

//             AND approval_status = 'approved'

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     return $stmt->fetch(
//         PDO::FETCH_ASSOC
//     ) !== false;
// }


// /*
// |--------------------------------------------------------------------------
// | Check If Company Is Rejected
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_is_rejected(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             id

//         FROM companies

//         WHERE
//             id = :company_id

//             AND approval_status = 'rejected'

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     return $stmt->fetch(
//         PDO::FETCH_ASSOC
//     ) !== false;
// }


// /*
// |--------------------------------------------------------------------------
// | Get Rejection Reason
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_get_rejection_reason(
//     int $company_id
// ) {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             rejection_reason

//         FROM companies

//         WHERE id = :company_id

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [
//             ':company_id' => $company_id
//         ]
//     );


//     $result =
//         $stmt->fetch(
//             PDO::FETCH_ASSOC
//         );


//     if (!$result) {
//         return null;
//     }


//     return $result['rejection_reason'];
// }


// /*
// |--------------------------------------------------------------------------
// | Get Approval Statistics
// |--------------------------------------------------------------------------
// */

// function company_approval_repository_get_statistics(): array
// {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             approval_status,
//             COUNT(*) AS total

//         FROM companies

//         GROUP BY approval_status
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute();


//     $rows =
//         $stmt->fetchAll(
//             PDO::FETCH_ASSOC
//         );


//     $statistics = [

//         'pending' => 0,

//         'approved' => 0,

//         'rejected' => 0,

//     ];


//     foreach (
//         $rows as $row
//     ) {

//         $status =
//             $row['approval_status'];


//         if (
//             array_key_exists(
//                 $status,
//                 $statistics
//             )
//         ) {

//             $statistics[$status] =
//                 (int) $row['total'];
//         }
//     }


//     $statistics['total'] =
//         $statistics['pending']
//         +
//         $statistics['approved']
//         +
//         $statistics['rejected'];


//     return $statistics;
// }
