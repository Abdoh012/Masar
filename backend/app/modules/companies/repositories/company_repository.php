<?php

/**
 * MASAR - Company Repository
 *
 * Responsible only for database operations related
 * to the companies table.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/database/query.php';


/*
|--------------------------------------------------------------------------
| Find Company By ID
|--------------------------------------------------------------------------
*/

function company_repository_find_by_id(
    int $company_id
): ?array {

    $sql = "
        SELECT
            id,
            user_id,
            legal_name AS company_name,
            description,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$company_id]
    );
}


/*
|--------------------------------------------------------------------------
| Find Company By User ID
|--------------------------------------------------------------------------
*/

function company_repository_find_by_user_id(
    int $user_id
): ?array {

    $sql = "
        SELECT
            id,
            user_id,
            legal_name AS company_name,
            description,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE user_id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$user_id]
    );
}


/*
|--------------------------------------------------------------------------
| Create Company
|--------------------------------------------------------------------------
*/

function company_repository_create(
    array $data
): int|false {

    $sql = "
        INSERT INTO companies (
            user_id,
            legal_name,
            description,
            approval_status,
            created_at,
            updated_at
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            NOW(),
            NOW()
        )
    ";


    $success =
        db_execute(
            $sql,
            [

                $data['user_id']
                    ?? null,

                $data['company_name']
                    ?? null,

                $data['description']
                    ?? null,

                $data['approval_status']
                    ?? 'pending',

            ]
        );


    if (!$success) {
        return false;
    }


    return db_last_insert_id();
}


/*
|--------------------------------------------------------------------------
| Update Company
|--------------------------------------------------------------------------
*/

function company_repository_update(
    int $company_id,
    array $data
): bool {

    if (empty($data)) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Fields
    |--------------------------------------------------------------------------
    */

    $allowed_fields = [

        'company_name',
        'description',
        'industry',

    ];


    $fields = [];
    $values = [];


    foreach (
        $allowed_fields
        as $field
    ) {

        if (
            array_key_exists(
                $field,
                $data
            )
        ) {

            $column = $field === 'company_name' ? 'legal_name' : $field;
            $fields[] =
                $column . ' = ?';

            $values[] =
                $data[$field];
        }
    }


    if (empty($fields)) {
        return false;
    }


    $fields[] =
        'updated_at = NOW()';


    $values[] =
        $company_id;


    $sql = "
        UPDATE companies
        SET "
        . implode(
            ', ',
            $fields
        )
        . "
        WHERE id = ?
        LIMIT 1
    ";


    $statement = db_execute(
        $sql,
        $values
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Update Approval Status
|--------------------------------------------------------------------------
*/

function company_repository_update_approval_status(
    int $company_id,
    string $status
): bool {

    $sql = "
        UPDATE companies
        SET
            approval_status = ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";


    $statement = db_execute(
        $sql,
        [
            $status,
            $company_id
        ]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Delete Company
|--------------------------------------------------------------------------
*/

function company_repository_delete(
    int $company_id
): bool {

    $sql = "
        DELETE FROM companies
        WHERE id = ?
        LIMIT 1
    ";


    $statement = db_execute(
        $sql,
        [$company_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Company Exists
|--------------------------------------------------------------------------
*/

function company_repository_exists(
    int $company_id
): bool {

    $sql = "
        SELECT
            id
        FROM companies
        WHERE id = ?
        LIMIT 1
    ";


    $company =
        db_fetch_one(
            $sql,
            [$company_id]
        );


    return is_array($company);
}


/*
|--------------------------------------------------------------------------
| User Has Company Profile
|--------------------------------------------------------------------------
*/

function company_repository_user_has_profile(
    int $user_id
): bool {

    $sql = "
        SELECT
            id
        FROM companies
        WHERE user_id = ?
        LIMIT 1
    ";


    $company =
        db_fetch_one(
            $sql,
            [$user_id]
        );


    return is_array($company);
}


/*
|--------------------------------------------------------------------------
| Get Companies
|--------------------------------------------------------------------------
*/

function company_repository_get_many(
    int $limit,
    int $offset
): array {

    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        ORDER BY id DESC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql
    );
}


/*
|--------------------------------------------------------------------------
| Get Approved Companies
|--------------------------------------------------------------------------
*/

function company_repository_get_approved(
    int $limit = 20,
    int $offset = 0
): array {

    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE approval_status = 'approved'
        ORDER BY id DESC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql
    );
}


/*
|--------------------------------------------------------------------------
| Get Pending Companies
|--------------------------------------------------------------------------
*/

function company_repository_get_pending(
    int $limit = 20,
    int $offset = 0
): array {

    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE approval_status = 'pending'
        ORDER BY id ASC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql
    );
}


/*
|--------------------------------------------------------------------------
| Get Rejected Companies
|--------------------------------------------------------------------------
*/

function company_repository_get_rejected(
    int $limit = 20,
    int $offset = 0
): array {

    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE approval_status = 'rejected'
        ORDER BY id DESC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql
    );
}


/*
|--------------------------------------------------------------------------
| Count Companies
|--------------------------------------------------------------------------
*/

function company_repository_count(): int
{
    $sql = "
        SELECT
            COUNT(*) AS total
        FROM companies
    ";


    $result =
        db_fetch_one(
            $sql
        );


    return (int) (
        $result['total']
            ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Count Companies By Status
|--------------------------------------------------------------------------
*/

function company_repository_count_by_status(
    string $status
): int {

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM companies
        WHERE approval_status = ?
    ";


    $result =
        db_fetch_one(
            $sql,
            [$status]
        );


    return (int) (
        $result['total']
            ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Search Companies By Name
|--------------------------------------------------------------------------
*/

function company_repository_search_by_name(
    string $name,
    int $limit = 20,
    int $offset = 0
): array {

    $name =
        trim($name);


    if ($name === '') {
        return [];
    }


    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE
            company_name LIKE ?
            AND approval_status = 'approved'
        ORDER BY company_name ASC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql,
        [
            '%' . $name . '%'
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Search Companies By Industry
|--------------------------------------------------------------------------
*/

function company_repository_search_by_industry(
    string $industry,
    int $limit = 20,
    int $offset = 0
): array {

    $industry =
        trim($industry);


    if ($industry === '') {
        return [];
    }


    $sql = "
        SELECT
            id,
            user_id,
            company_name,
            description,
            industry,
            approval_status,
            created_at,
            updated_at
        FROM companies
        WHERE
            industry LIKE ?
            AND approval_status = 'approved'
        ORDER BY company_name ASC
        LIMIT "
        . (int) $limit
        . "
        OFFSET "
        . (int) $offset;


    return db_fetch_all(
        $sql,
        [
            '%' . $industry . '%'
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Get Company With User Data
|--------------------------------------------------------------------------
|
| Used when the application needs company profile
| together with basic account information.
|
*/

function company_repository_get_with_user(
    int $company_id
): ?array {

    $sql = "
        SELECT

            c.id AS company_id,
            c.user_id,
            c.company_name,
            c.description,
            c.industry,
            c.approval_status,
            c.created_at,
            c.updated_at,

            u.email,
            u.role,
            u.status AS user_status

        FROM companies c

        INNER JOIN users u
            ON u.id = c.user_id

        WHERE c.id = ?

        LIMIT 1
    ";


    return db_fetch_one(
        $sql,
        [$company_id]
    );
}


/*
|--------------------------------------------------------------------------
| Get Company By ID For Public View
|--------------------------------------------------------------------------
|
| Only approved companies should be exposed publicly.
|
*/

function company_repository_get_public(
    int $company_id
): ?array {

    $sql = "
        SELECT

            id,
            company_name,
            description,
            industry,
            approval_status,
            created_at

        FROM companies

        WHERE
            id = ?
            AND approval_status = 'approved'

        LIMIT 1
    ";


    return db_fetch_one(
        $sql,
        [$company_id]
    );
}

/**
 * MASAR - Company Repository
 *
 * Responsible only for database operations related
 * to companies.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No business logic.
 * - No HTTP logic.
 * - All queries use PDO prepared statements.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/database/connection.php';


/*
|--------------------------------------------------------------------------
| Find Company By ID
|--------------------------------------------------------------------------
*/

// function company_repository_find_by_id(
//     int $company_id
// ) {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE c.id = :company_id

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [

//             ':company_id' =>
//                 $company_id,

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


/*
|--------------------------------------------------------------------------
| Find Company By User ID
|--------------------------------------------------------------------------
*/

// function company_repository_find_by_user_id(
//     int $user_id
// ) {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE c.user_id = :user_id

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [

//             ':user_id' =>
//                 $user_id,

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


/*
|--------------------------------------------------------------------------
| Check If User Has Company Profile
|--------------------------------------------------------------------------
*/

// function company_repository_user_has_profile(
//     int $user_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             id

//         FROM companies

//         WHERE user_id = :user_id

//         LIMIT 1
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [

//             ':user_id' =>
//                 $user_id,

//         ]
//     );


//     $result =
//         $stmt->fetch(
//             PDO::FETCH_ASSOC
//         );


//     return $result !== false;
// }


// /*
// |--------------------------------------------------------------------------
// | Create Company
// |--------------------------------------------------------------------------
// */

// function company_repository_create(
//     array $data
// ) {

//     $pdo = db_connection();


//     $sql = "
//         INSERT INTO companies
//         (
//             user_id,
//             company_name,
//             description,
//             industry,
//             approval_status,
//             created_at,
//             updated_at
//         )
//         VALUES
//         (
//             :user_id,
//             :company_name,
//             :description,
//             :industry,
//             :approval_status,
//             NOW(),
//             NOW()
//         )
//     ";


//     $stmt = $pdo->prepare($sql);


//     $success =
//         $stmt->execute(
//             [

//                 ':user_id' =>
//                     (int) $data['user_id'],

//                 ':company_name' =>
//                     $data['company_name'],

//                 ':description' =>
//                     $data['description'] ?? null,

//                 ':industry' =>
//                     $data['industry'],

//                 ':approval_status' =>
//                     $data['approval_status']
//                         ?? 'pending',

//             ]
//         );


//     if (!$success) {

//         return false;
//     }


//     return (int)
//         $pdo->lastInsertId();
// }


// /*
// |--------------------------------------------------------------------------
// | Update Company
// |--------------------------------------------------------------------------
// */

// function company_repository_update(
//     int $company_id,
//     array $data
// ): bool {

//     $pdo = db_connection();


//     $allowed_fields = [

//         'company_name',

//         'description',

//         'industry',

//     ];


//     $fields = [];


//     $params = [

//         ':company_id' =>
//             $company_id,

//     ];


//     foreach (
//         $allowed_fields
//         as $field
//     ) {

//         if (
//             array_key_exists(
//                 $field,
//                 $data
//             )
//         ) {

//             $fields[] =
//                 "{$field} = :{$field}";

//             $params[
//                 ":{$field}"
//             ] =
//                 $data[$field];
//         }
//     }


//     if (
//         empty($fields)
//     ) {

//         return false;
//     }


//     $fields[] =
//         "updated_at = NOW()";


//     $sql = "
//         UPDATE companies

//         SET
//             " .
//             implode(
//                 ", ",
//                 $fields
//             ) .

//         "

//         WHERE id = :company_id
//     ";


//     $stmt = $pdo->prepare($sql);


//     return $stmt->execute(
//         $params
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Update Approval Status
// |--------------------------------------------------------------------------
// */

// function company_repository_update_approval_status(
//     int $company_id,
//     string $status
// ): bool {

//     $allowed_statuses = [

//         'pending',

//         'approved',

//         'rejected',

//     ];


//     if (
//         !in_array(
//             $status,
//             $allowed_statuses,
//             true
//         )
//     ) {

//         return false;
//     }


//     $pdo = db_connection();


//     $sql = "
//         UPDATE companies

//         SET
//             approval_status = :approval_status,
//             updated_at = NOW()

//         WHERE id = :company_id
//     ";


//     $stmt = $pdo->prepare($sql);


//     return $stmt->execute(
//         [

//             ':approval_status' =>
//                 $status,

//             ':company_id' =>
//                 $company_id,

//         ]
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Get Approved Companies
// |--------------------------------------------------------------------------
// */

// function company_repository_get_approved(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE c.approval_status = 'approved'

//         ORDER BY
//             c.created_at DESC,
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
// | Get Pending Companies
// |--------------------------------------------------------------------------
// */

// function company_repository_get_pending(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

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
// | Get Rejected Companies
// |--------------------------------------------------------------------------
// */

// function company_repository_get_rejected(
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

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
// | Count Companies By Status
// |--------------------------------------------------------------------------
// */

// function company_repository_count_by_status(
//     string $status
// ): int {

//     $allowed_statuses = [

//         'pending',

//         'approved',

//         'rejected',

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
//                 $status,

//         ]
//     );


//     return (int)
//         $stmt->fetchColumn();
// }


// /*
// |--------------------------------------------------------------------------
// | Search Companies By Name
// |--------------------------------------------------------------------------
// */

// function company_repository_search_by_name(
//     string $query,
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE
//             c.approval_status = 'approved'

//             AND c.company_name LIKE :query

//         ORDER BY
//             c.company_name ASC,
//             c.id DESC

//         LIMIT :limit
//         OFFSET :offset
//     ";


//     $stmt = $pdo->prepare($sql);


//     $search =
//         '%' .
//         $query .
//         '%';


//     $stmt->bindValue(
//         ':query',
//         $search,
//         PDO::PARAM_STR
//     );


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
// | Search Companies By Industry
// |--------------------------------------------------------------------------
// */

// function company_repository_search_by_industry(
//     string $query,
//     int $limit = 20,
//     int $offset = 0
// ): array {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE
//             c.approval_status = 'approved'

//             AND c.industry LIKE :query

//         ORDER BY
//             c.company_name ASC,
//             c.id DESC

//         LIMIT :limit
//         OFFSET :offset
//     ";


//     $stmt = $pdo->prepare($sql);


//     $search =
//         '%' .
//         $query .
//         '%';


//     $stmt->bindValue(
//         ':query',
//         $search,
//         PDO::PARAM_STR
//     );


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
// | Count Search Results By Name
// |--------------------------------------------------------------------------
// */

// function company_repository_count_search_by_name(
//     string $query
// ): int {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             COUNT(*)

//         FROM companies

//         WHERE
//             approval_status = 'approved'

//             AND company_name LIKE :query
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [

//             ':query' =>
//                 '%' .
//                 $query .
//                 '%',

//         ]
//     );


//     return (int)
//         $stmt->fetchColumn();
// }


// /*
// |--------------------------------------------------------------------------
// | Count Search Results By Industry
// |--------------------------------------------------------------------------
// */

// function company_repository_count_search_by_industry(
//     string $query
// ): int {

//     $pdo = db_connection();


//     $sql = "
//         SELECT
//             COUNT(*)

//         FROM companies

//         WHERE
//             approval_status = 'approved'

//             AND industry LIKE :query
//     ";


//     $stmt = $pdo->prepare($sql);


//     $stmt->execute(
//         [

//             ':query' =>
//                 '%' .
//                 $query .
//                 '%',

//         ]
//     );


//     return (int)
//         $stmt->fetchColumn();
// }


// /*
// |--------------------------------------------------------------------------
// | Get Companies By User IDs
// |--------------------------------------------------------------------------
// |
// | Useful for administrative operations.
// |
// */

// function company_repository_get_by_user_ids(
//     array $user_ids
// ): array {

//     if (
//         empty($user_ids)
//     ) {

//         return [];
//     }


//     $pdo = db_connection();


//     /*
//     |--------------------------------------------------------------------------
//     | Sanitize IDs
//     |--------------------------------------------------------------------------
//     */

//     $user_ids = array_map(
//         'intval',
//         $user_ids
//     );


//     $user_ids =
//         array_values(
//             array_unique(
//                 $user_ids
//             )
//         );


//     if (
//         empty($user_ids)
//     ) {

//         return [];
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | Build Placeholders
//     |--------------------------------------------------------------------------
//     */

//     $placeholders = [];


//     $params = [];


//     foreach (
//         $user_ids
//         as $index => $user_id
//     ) {

//         $placeholder =
//             ':user_id_' .
//             $index;


//         $placeholders[] =
//             $placeholder;


//         $params[$placeholder] =
//             $user_id;
//     }


//     /*
//     |--------------------------------------------------------------------------
//     | SQL
//     |--------------------------------------------------------------------------
//     */

//     $sql = "
//         SELECT
//             c.id,
//             c.user_id,
//             c.company_name,
//             c.description,
//             c.industry,
//             c.approval_status,
//             c.created_at,
//             c.updated_at

//         FROM companies c

//         WHERE c.user_id IN
//         (
//             " .
//             implode(
//                 ', ',
//                 $placeholders
//             ) .
//         "

//         )

//         ORDER BY
//             c.created_at DESC
//     ";


//     $stmt =
//         $pdo->prepare($sql);


//     foreach (
//         $params
//         as $placeholder =>
//         $value
//     ) {

//         $stmt->bindValue(
//             $placeholder,
//             $value,
//             PDO::PARAM_INT
//         );
//     }


//     $stmt->execute();


//     return $stmt->fetchAll(
//         PDO::FETCH_ASSOC
//     );
// }


// /*
// |--------------------------------------------------------------------------
// | Delete Company
// |--------------------------------------------------------------------------
// |
// | Normally company deletion should be handled carefully
// | because training records and certificates may depend on it.
// |
// */

// function company_repository_delete(
//     int $company_id
// ): bool {

//     $pdo = db_connection();


//     $sql = "
//         DELETE FROM companies

//         WHERE id = :company_id
//     ";


//     $stmt = $pdo->prepare($sql);


//     return $stmt->execute(
//         [

//             ':company_id' =>
//                 $company_id,

//         ]
//     );
// }
