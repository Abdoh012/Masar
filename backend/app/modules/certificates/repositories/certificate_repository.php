<?php

/**
 * MASAR - Certificate Repository
 *
 * Responsible for certificate data access only.
 *
 * Service
 *    ↓
 * Certificate Repository
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

function certificate_repository_db()
{
    /*
     * Support the project's existing database helper.
     */

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
| Find Certificate
|--------------------------------------------------------------------------
*/

function certificate_repository_find(
    int $certificate_id
): ?array {

    if ($certificate_id <= 0) {
        return null;
    }

    $db =
        certificate_repository_db();

    $sql = "
        SELECT
            c.*
        FROM certificates c
        WHERE c.id = :certificate_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    $stmt->execute([
        ':certificate_id' =>
            $certificate_id
    ]);

    $certificate =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );

    return $certificate ?: null;
}


/*
|--------------------------------------------------------------------------
| List Certificates
|--------------------------------------------------------------------------
*/

function certificate_repository_list(
    array $filters = []
): array {

    $db =
        certificate_repository_db();

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
            'c.user_id = :user_id';

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
            'c.student_id = :student_id';

        $params[':student_id'] =
            (int) $filters['student_id'];
    }


    /*
     * Training
     */

    if (
        isset($filters['training_id'])
        &&
        $filters['training_id'] !== ''
    ) {

        $where[] =
            'c.training_id = :training_id';

        $params[':training_id'] =
            (int) $filters['training_id'];
    }


    /*
     * Company
     */

    if (
        isset($filters['company_id'])
        &&
        $filters['company_id'] !== ''
    ) {

        $where[] =
            'c.company_id = :company_id';

        $params[':company_id'] =
            (int) $filters['company_id'];
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
            'c.status = :status';

        $params[':status'] =
            (string) $filters['status'];
    }


    /*
     * Certificate Number
     */

    if (
        isset($filters['certificate_number'])
        &&
        $filters['certificate_number'] !== ''
    ) {

        $where[] =
            'c.certificate_code = :certificate_number';

        $params[':certificate_number'] =
            (string) $filters['certificate_number'];
    }


    /*
     * Date range
     */

    if (
        !empty($filters['issued_from'])
    ) {

        $where[] =
            'c.approved_at >= :issued_from';

        $params[':issued_from'] =
            (string) $filters['issued_from'];
    }


    if (
        !empty($filters['issued_to'])
    ) {

        $where[] =
            'c.approved_at <= :issued_to';

        $params[':issued_to'] =
            (string) $filters['issued_to'];
    }


    /*
     * Search keyword
     */

    if (
        !empty($filters['keyword'])
    ) {

        $where[] = "
            (
                c.certificate_code LIKE :keyword
                OR c.title LIKE :keyword
            )
        ";

        $params[':keyword'] =
            '%' .
            (string) $filters['keyword'] .
            '%';
    }


    /*
     * Build query
     */

    $sql = "
        SELECT
            c.*
        FROM certificates c
    ";

    if (!empty($where)) {

        $sql .=
            ' WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }


    /*
     * Ordering
     */

    $sql .= "
        ORDER BY
            c.approved_at DESC,
            c.id DESC
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

    $sql .=
        " LIMIT {$limit} OFFSET {$offset}";


    $stmt =
        $db->prepare($sql);

    $stmt->execute(
        $params
    );

    return
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}


/*
 |--------------------------------------------------------------------------
 | Create Certificate
 |--------------------------------------------------------------------------
 */

function certificate_repository_create(
    array $data
): ?array {

    $db =
        certificate_repository_db();

    if (empty($data)) {
        return null;
    }

/*
 * Whitelist supported fields.
 */

$allowed = [
    'certificate_code',
    'student_id',
    'training_id',
    'company_id',
    'title',
    'status',
    'start_date',
    'end_date',
    'grade',
    'grade_label',
    'employment_eligible'
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
     * Default timestamps.
     */

    /*
     * Skip adding issued_at since it is not a column in the certificates table.
     * created_at and updated_at are handled by the database defaults.
     */


    $sql = "
        INSERT INTO certificates
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

    $stmt =
        $db->prepare($sql);

    $stmt->execute(
        $params
    );

    $id =
        (int) $db->lastInsertId();

    if ($id <= 0) {
        return null;
    }

    return
        certificate_repository_find(
            $id
        );
}


/*
|--------------------------------------------------------------------------
| Update Certificate
|--------------------------------------------------------------------------
*/

function certificate_repository_update(
    int $certificate_id,
    array $data
): bool {

    if (
        $certificate_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $db =
        certificate_repository_db();

$allowed = [
        'certificate_code',
        'student_id',
        'training_id',
        'company_id',
        'title',
        'status',
        'grade',
        'grade_label',
        'employment_eligible',
        'revocation_reason',
        'revoked_by',
        'revoked_at'
    ];

    $sets = [];

    $params = [
        ':certificate_id' =>
            $certificate_id
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
     * Add updated_at only if the table has it.
     *
     * The project schema should contain this field.
     */

    $sets[] =
        'updated_at = NOW()';


    $sql = "
        UPDATE certificates
        SET
            " .
            implode(
                ', ',
                $sets
            ) .
            "
        WHERE id = :certificate_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    return
        $stmt->execute(
            $params
        );
}


/*
|--------------------------------------------------------------------------
| Generate Certificate Number
|--------------------------------------------------------------------------
*/

function certificate_repository_generate_number(): string
{
    $db =
        certificate_repository_db();

    /*
     * Example:
     *
     * MASAR-2026-XXXXXXXX
     *
     * The random component makes collisions highly unlikely.
     */

    do {

        $number =
            'MASAR-' .
            date('Y') .
            '-' .
            strtoupper(
                bin2hex(
                    random_bytes(4)
                )
            );

        $sql = "
            SELECT id
            FROM certificates
            WHERE certificate_number = :number
            LIMIT 1
        ";

        $stmt =
            $db->prepare($sql);

        $stmt->execute([
            ':number' =>
                $number
        ]);

        $exists =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

    } while ($exists);

    return $number;
}


/*
|--------------------------------------------------------------------------
| Revoke Certificate
|--------------------------------------------------------------------------
*/

function certificate_repository_revoke(
    int $certificate_id,
    string $reason,
    ?int $revoked_by = null
): bool {

    if ($certificate_id <= 0) {
        return false;
    }

    $db =
        certificate_repository_db();

    $sql = "
        UPDATE certificates
        SET
            status = 'revoked',
            revocation_reason = :reason,
            revoked_by = :revoked_by,
            revoked_at = NOW(),
            updated_at = NOW()
        WHERE id = :certificate_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    return
        $stmt->execute([
            ':reason' =>
                $reason,

            ':revoked_by' =>
                $revoked_by,

            ':certificate_id' =>
                $certificate_id
        ]);
}


/*
|--------------------------------------------------------------------------
| Verify Certificate
|--------------------------------------------------------------------------
*/

function certificate_repository_verify(
    int $certificate_id
): ?array {

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return null;
    }

    $status =
        strtolower(
            (string) (
                $certificate['status']
                ?? ''
            )
        );

    $valid =
        in_array(
            $status,
            [
                'issued',
                'active',
                'valid'
            ],
            true
        );


    /*
     * Expiration check.
     */

    if (
        $valid
        &&
        !empty(
            $certificate['expires_at']
        )
    ) {

        $expires_at =
            strtotime(
                $certificate['expires_at']
            );

        if (
            $expires_at !== false
            &&
            $expires_at < time()
        ) {
            $valid = false;
        }
    }


    return [
        'valid' =>
            $valid,

        'certificate' =>
            $certificate
    ];
}


/*
|--------------------------------------------------------------------------
| Get Certificate File
|--------------------------------------------------------------------------
*/

function certificate_repository_get_file(
    int $certificate_id
): ?array {

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return null;
    }

    $path =
        $certificate['file_path']
        ?? $certificate['certificate_path']
        ?? null;

    if (
        !$path
        ||
        !is_file($path)
    ) {
        return null;
    }

    return [
        'path' =>
            $path,

        'name' =>
            $certificate['file_name']
            ?? basename($path),

        'mime' =>
            $certificate['mime_type']
            ?? 'application/pdf',

        'size' =>
            filesize($path)
    ];
}


/*
|--------------------------------------------------------------------------
| Ownership - Student
|--------------------------------------------------------------------------
*/

function certificate_repository_student_belongs_to_user(
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

    $db =
        certificate_repository_db();

    $sql = "
        SELECT id
        FROM students
        WHERE
            id = :student_id
            AND user_id = :user_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    $stmt->execute([
        ':student_id' =>
            $student_id,

        ':user_id' =>
            $user_id
    ]);

    return
        (bool) $stmt->fetch(
            PDO::FETCH_ASSOC
        );
}


/*
|--------------------------------------------------------------------------
| Ownership - Company
|--------------------------------------------------------------------------
*/

function certificate_repository_company_belongs_to_user(
    int $company_id,
    int $user_id
): bool {

    if (
        $company_id <= 0
        ||
        $user_id <= 0
    ) {
        return false;
    }

    $db =
        certificate_repository_db();

    $sql = "
        SELECT id
        FROM companies
        WHERE
            id = :company_id
            AND user_id = :user_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    $stmt->execute([
        ':company_id' =>
            $company_id,

        ':user_id' =>
            $user_id
    ]);

    return
        (bool) $stmt->fetch(
            PDO::FETCH_ASSOC
        );
}


/*
|--------------------------------------------------------------------------
| Count Certificates
|--------------------------------------------------------------------------
*/

function certificate_repository_count(
    array $filters = []
): int {

    $db =
        certificate_repository_db();

    $where = [];

    $params = [];


    if (
        isset($filters['user_id'])
        &&
        $filters['user_id'] !== ''
    ) {

        $where[] =
            'c.user_id = :user_id';

        $params[':user_id'] =
            (int) $filters['user_id'];
    }


    if (
        isset($filters['student_id'])
        &&
        $filters['student_id'] !== ''
    ) {

        $where[] =
            'c.student_id = :student_id';

        $params[':student_id'] =
            (int) $filters['student_id'];
    }


    if (
        isset($filters['training_id'])
        &&
        $filters['training_id'] !== ''
    ) {

        $where[] =
            'c.training_id = :training_id';

        $params[':training_id'] =
            (int) $filters['training_id'];
    }


    if (
        isset($filters['company_id'])
        &&
        $filters['company_id'] !== ''
    ) {

        $where[] =
            'c.company_id = :company_id';

        $params[':company_id'] =
            (int) $filters['company_id'];
    }


    if (
        isset($filters['status'])
        &&
        $filters['status'] !== ''
    ) {

        $where[] =
            'c.status = :status';

        $params[':status'] =
            (string) $filters['status'];
    }


    $sql = "
        SELECT COUNT(*) AS total
        FROM certificates c
    ";

    if (!empty($where)) {

        $sql .=
            ' WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }

    $stmt =
        $db->prepare($sql);

    $stmt->execute(
        $params
    );

    return
        (int) (
            $stmt->fetchColumn()
            ?: 0
        );
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

function certificate_repository_statistics(
    array $filters = []
): array {

    $total =
        certificate_repository_count(
            $filters
        );

    $issued =
        certificate_repository_count(
            array_merge(
                $filters,
                [
                    'status' => 'issued'
                ]
            )
        );

    $active =
        certificate_repository_count(
            array_merge(
                $filters,
                [
                    'status' => 'active'
                ]
            )
        );

    $valid =
        certificate_repository_count(
            array_merge(
                $filters,
                [
                    'status' => 'valid'
                ]
            )
        );

    $revoked =
        certificate_repository_count(
            array_merge(
                $filters,
                [
                    'status' => 'revoked'
                ]
            )
        );

    $pending =
        certificate_repository_count(
            array_merge(
                $filters,
                [
                    'status' => 'pending'
                ]
            )
        );

    return [
        'total' =>
            $total,

        'issued' =>
            $issued,

        'active' =>
            $active,

        'valid' =>
            $valid,

        'revoked' =>
            $revoked,

        'pending' =>
            $pending
    ];
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

function certificate_repository_search(
    array $filters = []
): array {

    if (
        empty(
            $filters['keyword']
        )
    ) {
        return [];
    }

    return
        certificate_repository_list(
            $filters
        );
}

/*
 |--------------------------------------------------------------------------
 | Duplicate Prevention
 |--------------------------------------------------------------------------
 */

function certificate_repository_duplicate_exists(
    int $student_id,
    int $training_id
): bool {

    $db =
        certificate_repository_db();

    $sql = "
        SELECT id
        FROM certificates
        WHERE
            student_id = :student_id
            AND training_id = :training_id
            AND status IN ('issued', 'active', 'valid')
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    $stmt->execute([
        ':student_id' =>
            $student_id,

        ':training_id' =>
            $training_id
    ]);

    return
        (bool) $stmt->fetch(
            PDO::FETCH_ASSOC
        );
}


/*
 |--------------------------------------------------------------------------
 | Training/Application Validation
 |--------------------------------------------------------------------------
 */

function certificate_repository_validate_training_status(
    int $training_id,
    int $student_id
): array {

    $db =
        certificate_repository_db();

    $errors = [];

    /*
     * Check that the training listing exists and is not closed.
     */
    $training = certificate_repository_find_training_listing($training_id);

    if (!$training) {
        $errors['training_id'] = 'Training listing not found.';
    } elseif (
        !empty($training['status'])
        &&
        in_array(
            strtolower($training['status']),
            ['closed'],
            true
        )
    ) {
        $errors['training_id'] = 'Cannot issue certificate for a closed training listing.';
    }

    /*
     * Check that the training session is completed.
     */
    if (
        function_exists(
            'training_session_repository_find_by_training'
        )
    ) {

        $session =
            training_session_repository_find_by_training(
                $training_id
            );

        if (!$session) {
            $errors['training_id'] = 'No training session found for this training.';
        } elseif (
            !empty($session['status'])
            &&
            strtolower($session['status']) !== 'completed'
        ) {
            $errors['training_id'] = 'Training session must be completed before issuing a certificate.';
        }

    } else {

        /*
         * Fallback: check training_sessions directly.
         */
        $session_sql = "
            SELECT id, status
            FROM training_sessions
            WHERE training_id = :training_id
            LIMIT 1
        ";

        $session_stmt =
            $db->prepare($session_sql);

        $session_stmt->execute([
            ':training_id' => $training_id
        ]);

        $session = $session_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            $errors['training_id'] = 'No training session found for this training.';
        } elseif (
            !empty($session['status'])
            &&
            strtolower($session['status']) !== 'completed'
        ) {
            $errors['training_id'] = 'Training session must be completed before issuing a certificate.';
        }
    }

    /*
     * Check the training application status.
     * Application must be accepted (not pending, rejected, or withdrawn).
     */
    if (
        function_exists(
            'training_application_repository_find_by_student_and_training'
        )
    ) {

        $application =
            training_application_repository_find_by_student_and_training(
                $student_id,
                $training_id
            );

        if (!$application) {
            $errors['training_id'] = 'No accepted training application found for this student and training.';
        } elseif (
            !empty($application['status'])
            &&
            strtolower($application['status']) !== 'accepted'
        ) {
            $errors['training_id'] = 'Training application must be accepted before issuing a certificate.';
        }

    } else {

        /*
         * Fallback: check training_applications directly.
         */
        $app_sql = "
            SELECT id, status
            FROM training_applications
            WHERE student_id = :student_id
            AND training_id = :training_id
            LIMIT 1
        ";

        $app_stmt =
            $db->prepare($app_sql);

        $app_stmt->execute([
            ':student_id' => $student_id,
            ':training_id' => $training_id
        ]);

        $application = $app_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            $errors['training_id'] = 'No accepted training application found for this student and training.';
        } elseif (
            !empty($application['status'])
            &&
            strtolower($application['status']) !== 'accepted'
        ) {
            $errors['training_id'] = 'Training application must be accepted before issuing a certificate.';
        }
    }

    return $errors;
}


function certificate_repository_find_training_listing(
    int $training_id
): ?array {

    $db =
        certificate_repository_db();

    $sql = "
        SELECT id, status, company_id, starts_at, ends_at
        FROM training_listings
        WHERE id = :training_id
        LIMIT 1
    ";

    $stmt =
        $db->prepare($sql);

    $stmt->execute([
        ':training_id' => $training_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
