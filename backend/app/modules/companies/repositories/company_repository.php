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
            company_logo,
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
| Set Company Logo
|--------------------------------------------------------------------------
|
| Stores the relative storage path of the company's logo image.
| The path follows the file upload service convention (e.g.
| "companies/20260821_a1b2c3d4.png"). Passing null clears the logo.
|
*/

function company_repository_set_logo(
    int $company_id,
    ?string $logo_path
): bool {

    if ($company_id <= 0) {
        return false;
    }

    $logo_path =
        $logo_path === null
            ? null
            : trim($logo_path);

    if ($logo_path === '') {
        $logo_path = null;
    }

    $statement = db_execute(
        "
            UPDATE companies
            SET
                company_logo = ?,
                updated_at = NOW()
            WHERE id = ?
            LIMIT 1
        ",
        [$logo_path, $company_id]
    );

    return true;
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
| Add Work Field
|--------------------------------------------------------------------------
*/

function company_repository_add_work_field(
    int $company_id,
    int $field_id
): bool {

    if (
        $company_id <= 0 ||
        $field_id <= 0
    ) {

        return false;
    }


    $sql = "
        INSERT IGNORE INTO company_work_fields (
            company_id,
            field_id,
            created_at
        )
        VALUES (
            ?,
            ?,
            NOW()
        )
    ";


    $statement =
        db_execute(
            $sql,
            [
                $company_id,
                $field_id,
            ]
        );

    return $statement !== false;
}


/*
|--------------------------------------------------------------------------
| Delete All Work Fields
|--------------------------------------------------------------------------
*/

function company_repository_delete_work_fields(
    int $company_id
): bool {

    if ($company_id <= 0) {
        return false;
    }


    $sql = "
        DELETE FROM company_work_fields
        WHERE company_id = ?
    ";


    $statement =
        db_execute(
            $sql,
            [$company_id]
        );

    return $statement !== false;
}


/*
|--------------------------------------------------------------------------
| Replace All Work Fields
|--------------------------------------------------------------------------
*/

function company_repository_replace_work_fields(
    int $company_id,
    array $field_ids
): bool {

    if ($company_id <= 0) {
        return false;
    }


    return db_transaction(function () use ($company_id, $field_ids): bool {
        $deleted =
            company_repository_delete_work_fields(
                $company_id
            );

        if (!$deleted) {
            throw new RuntimeException('Unable to replace company work fields.');
        }


        foreach ($field_ids as $field_id) {
            $added =
                company_repository_add_work_field(
                    $company_id,
                    (int) $field_id
                );

            if (!$added) {
                throw new RuntimeException('Unable to replace company work fields.');
            }
        }

        return true;
    });
}


/*
|--------------------------------------------------------------------------
| Get Work Fields
|--------------------------------------------------------------------------
*/

function company_repository_get_work_fields(
    int $company_id
): array {

    if ($company_id <= 0) {
        return [];
    }


    $sql = "
        SELECT
            cwf.field_id,
            sf.name AS field_name
        FROM company_work_fields cwf
        INNER JOIN study_fields sf
            ON sf.id = cwf.field_id
        WHERE cwf.company_id = ?
        ORDER BY sf.name ASC
    ";


    return db_fetch_all(
        $sql,
        [$company_id]
    );
}


/*
|--------------------------------------------------------------------------
| Resolve Work Field ID
|--------------------------------------------------------------------------
*/

function company_repository_resolve_work_field_id(
    mixed $input
): ?int {

    if (
        is_int($input)
        ||
        (
            is_string($input)
            &&
            ctype_digit($input)
        )
    ) {

        $row =
            db_fetch_one(
                " SELECT id FROM study_fields WHERE id = ? AND is_active = 1 LIMIT 1 ",
                [(int) $input]
            );

        return $row ? (int) $row['id'] : null;
    }


    if (
        is_string($input)
        &&
        trim($input) !== ''
    ) {

        $row =
            db_fetch_one(
                " SELECT id FROM study_fields WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ",
                [trim($input)]
            );

        return $row ? (int) $row['id'] : null;
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| Resolve Work Field IDs
|--------------------------------------------------------------------------
|
| Resolves a list of study field IDs and/or names against the study_fields
| lookup table. Returns null when any input does not match an active study
| field. study_fields is the single source of truth for work fields.
|
*/

function company_repository_resolve_work_field_ids(
    array $inputs
): ?array {

    $resolved = [];

    foreach ($inputs as $input) {
        $field_id =
            company_repository_resolve_work_field_id(
                $input
            );

        if ($field_id === null) {
            return null;
        }

        $resolved[$field_id] = $field_id;
    }

    return array_values($resolved);
}


/*
|--------------------------------------------------------------------------
| Add Specialization
|--------------------------------------------------------------------------
|
| Links a company to a specialization (industry) in the
| company_specializations pivot table. The composite primary key
| (company_id, specialization_id) makes INSERT IGNORE idempotent,
| so duplicate links are never created.
|
*/

function company_repository_add_specialization(
    int $company_id,
    int $specialization_id
): bool {

    if (
        $company_id <= 0 ||
        $specialization_id <= 0
    ) {

        return false;
    }


    $sql = "
        INSERT IGNORE INTO company_specializations (
            company_id,
            specialization_id,
            created_at
        )
        VALUES (
            ?,
            ?,
            NOW()
        )
    ";


    $statement =
        db_execute(
            $sql,
            [
                $company_id,
                $specialization_id,
            ]
        );

    return $statement !== false;
}


/*
|--------------------------------------------------------------------------
| Delete All Specializations
|--------------------------------------------------------------------------
*/

function company_repository_delete_specializations(
    int $company_id
): bool {

    if ($company_id <= 0) {
        return false;
    }


    $sql = "
        DELETE FROM company_specializations
        WHERE company_id = ?
    ";


    $statement =
        db_execute(
            $sql,
            [$company_id]
        );

    return $statement !== false;
}


/*
|--------------------------------------------------------------------------
| Replace All Specializations
|--------------------------------------------------------------------------
*/

function company_repository_replace_specializations(
    int $company_id,
    array $specialization_ids
): bool {

    if ($company_id <= 0) {
        return false;
    }


    return db_transaction(function () use ($company_id, $specialization_ids): bool {
        $deleted =
            company_repository_delete_specializations(
                $company_id
            );

        if (!$deleted) {
            throw new RuntimeException('Unable to replace company specializations.');
        }


        foreach ($specialization_ids as $specialization_id) {
            $added =
                company_repository_add_specialization(
                    $company_id,
                    (int) $specialization_id
                );

            if (!$added) {
                throw new RuntimeException('Unable to replace company specializations.');
            }
        }

        return true;
    });
}


/*
|--------------------------------------------------------------------------
| Get Specializations
|--------------------------------------------------------------------------
*/

function company_repository_get_specializations(
    int $company_id
): array {

    if ($company_id <= 0) {
        return [];
    }


    $sql = "
        SELECT
            cs.specialization_id AS id,
            s.name
        FROM company_specializations cs
        INNER JOIN specializations s
            ON s.id = cs.specialization_id
        WHERE cs.company_id = ?
        ORDER BY s.name ASC
    ";


    return db_fetch_all(
        $sql,
        [$company_id]
    );
}


/*
|--------------------------------------------------------------------------
| Resolve Specialization ID
|--------------------------------------------------------------------------
|
| Accepts a specialization ID (int / numeric string) or a specialization
| name (string). Names are matched case-insensitively against active rows
| of the specializations lookup table. specializations is the single
| source of truth for the company industry.
|
*/

function company_repository_resolve_specialization_id(
    mixed $input
): ?int {

    if (
        is_int($input)
        ||
        (
            is_string($input)
            &&
            ctype_digit($input)
        )
    ) {

        $row =
            db_fetch_one(
                " SELECT id FROM specializations WHERE id = ? AND is_active = 1 LIMIT 1 ",
                [(int) $input]
            );

        return $row ? (int) $row['id'] : null;
    }


    if (
        is_string($input)
        &&
        trim($input) !== ''
    ) {

        $row =
            db_fetch_one(
                " SELECT id FROM specializations WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND is_active = 1 LIMIT 1 ",
                [trim($input)]
            );

        return $row ? (int) $row['id'] : null;
    }


    return null;
}


/*
|--------------------------------------------------------------------------
| Resolve Specialization IDs
|--------------------------------------------------------------------------
|
| Resolves a list of specialization IDs and/or names against the
| specializations lookup table. Returns null when any input does not match
| an active specialization. Duplicates are removed. specializations is the
| single source of truth for the company industry
| (company_specializations).
|
*/

function company_repository_resolve_specialization_ids(
    array $inputs
): ?array {

    $resolved = [];

    foreach ($inputs as $input) {
        $specialization_id =
            company_repository_resolve_specialization_id(
                $input
            );

        if ($specialization_id === null) {
            return null;
        }

        $resolved[$specialization_id] = $specialization_id;
    }

    return array_values($resolved);
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
