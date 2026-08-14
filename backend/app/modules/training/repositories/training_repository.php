<?php

/**
 * MASAR - Training Repository
 *
 * Responsible only for database operations related to trainings.
 *
 * Controller
 *     ↓
 * Service
 *     ↓
 * Repository
 *     ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/database/connection.php';
require_once __DIR__ . '/../../../core/database/query.php';


/*
|--------------------------------------------------------------------------
| Find Training By ID
|--------------------------------------------------------------------------
*/

function training_repository_find_by_id(
    int $training_id
): ?array {

    if ($training_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        WHERE t.id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$training_id]
    );
}


/*
|--------------------------------------------------------------------------
| Find Company By User ID
|--------------------------------------------------------------------------
*/

function training_repository_find_company_by_user_id(
    int $user_id
): ?array {

    if ($user_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            c.*,
            c.id AS company_id
        FROM companies c
        WHERE c.user_id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$user_id]
    );
}


/*
|--------------------------------------------------------------------------
| Get Training List
|--------------------------------------------------------------------------
*/

function training_repository_get_all(
    int $limit = 20,
    int $offset = 0
): array {

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Trainings
|--------------------------------------------------------------------------
*/

function training_repository_count(): int {

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings
    ";

    $row = db_fetch_one(
        $sql
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Trainings By Company
|--------------------------------------------------------------------------
*/

function training_repository_get_by_company(
    int $company_id,
    int $limit = 20,
    int $offset = 0
): array {

    if ($company_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        WHERE t.company_id = ?
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$company_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Trainings By Company
|--------------------------------------------------------------------------
*/

function training_repository_count_by_company(
    int $company_id
): int {

    if ($company_id <= 0) {
        return 0;
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings
        WHERE company_id = ?
    ";

    $row = db_fetch_one(
        $sql,
        [$company_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Create Training
|--------------------------------------------------------------------------
*/

function training_repository_create(
    array $data
): ?int {

    if (empty($data)) {
        return null;
    }

    $training_type = [
        'shadowing',
        'hands_on',
        'project_based'
    ];

    $mode = [
        'onsite',
        'remote',
        'hybrid'
    ];

    $status = [
        'draft',
        'published',
        'closed'
    ];

    $type_value =
        isset($data['training_type'])
            ? $data['training_type']
            : 'hands_on';

    if (!in_array($type_value, $training_type, true)) {
        $type_value = 'hands_on';
    }

    $mode_value =
        isset($data['work_mode'])
            ? $data['work_mode']
            : 'hybrid';

    if (!in_array($mode_value, $mode, true)) {
        $mode_value = 'hybrid';
    }

    $status_value =
        $data['status'] ?? 'draft';

    if (!in_array($status_value, $status, true)) {
        $status_value = 'draft';
    }

    $field_value =
        trim(
            (string) (
                $data['field']
                ?? $data['specialization']
                ?? $data['title']
                ?? ''
            )
        );

    if ($field_value === '') {
        $field_value = 'General';
    }

    $is_paid =
        (int) (
            $data['is_paid']
            ?? $data['paid']
            ?? 0
        ) > 0 ? 1 : 0;

    $salary =
        $data['compensation_amount']
        ?? $data['salary']
        ?? null;

    $sql = "
        INSERT INTO training_listings (
            company_id,
            title,
            description,
            field,
            training_type,
            mode,
            may_lead_to_employment,
            is_paid,
            compensation_amount,
            compensation_currency,
            trial_period_days,
            capacity,
            status,
            published_at,
            starts_at,
            ends_at,
            closed_at,
            location,
            created_at,
            updated_at
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'EGP',
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW(),
            NOW()
        )
    ";

    $params = [

        $data['company_id']
            ?? null,

        $data['title']
            ?? null,

        $data['description']
            ?? null,

        $field_value,

        $type_value,

        $mode_value,

        (int) (
            $data['may_lead_to_employment']
            ?? $data['employment_possible']
            ?? 0
        ) > 0 ? 1 : 0,

        $is_paid,

        $is_paid === 1
            ? $salary
            : null,

        $is_paid === 1
            ? (
                isset($data['trial_period_days'])
                    ? max(7, (int) $data['trial_period_days'])
                    : 7
            )
            : null,

        isset($data['capacity'])
            ? (int) $data['capacity']
            : null,

        $status_value,

        $status_value === 'published'
            ? date('Y-m-d H:i:s')
            : null,

        $data['start_date']
            ?? $data['starts_at']
            ?? null,

        $data['end_date']
            ?? $data['ends_at']
            ?? null,

        $status_value === 'closed'
            ? date('Y-m-d H:i:s')
            : null,

        $data['location']
            ?? null

    ];

    $statement = db_execute(
        $sql,
        $params
    );

    if ($statement->rowCount() < 1) {
        return null;
    }

    return (int) db_last_insert_id();
}


/*
|--------------------------------------------------------------------------
| Update Training
|--------------------------------------------------------------------------
*/

function training_repository_update(
    int $training_id,
    array $data
): bool {

    if (
        $training_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $column_map = [

        'title' => 'title',

        'description' => 'description',

        'location' => 'location',

        'training_type' => 'training_type',

        'start_date' => 'starts_at',

        'end_date' => 'ends_at',

        'capacity' => 'capacity',

        'status' => 'status',

        'is_paid' => 'is_paid',

        'paid' => 'is_paid',

        'compensation_amount' => 'compensation_amount',

        'salary' => 'compensation_amount',

        'may_lead_to_employment' => 'may_lead_to_employment',

        'employment_possible' => 'may_lead_to_employment',

        'field' => 'field'

    ];

    $sets = [];
    $params = [];

    foreach (
        $column_map as $service_field => $column
    ) {

        if (
            !array_key_exists(
                $service_field,
                $data
            )
        ) {
            continue;
        }

        $value = $data[$service_field];

        if ($column === 'training_type') {

            $allowed = [
                'shadowing',
                'hands_on',
                'project_based'
            ];

            $value = strtolower(
                trim(
                    (string) $value
                )
            );

            if (!in_array($value, $allowed, true)) {
                continue;
            }
        }

        if (
            $column === 'status'
            &&
            !in_array($value, ['draft', 'published', 'closed'], true)
        ) {
            continue;
        }

        if ($column === 'starts_at') {
            $sets[] = "{$column} = ?";
            $params[] = !empty($value) ? $value : null;
            continue;
        }

        if ($column === 'ends_at') {
            $sets[] = "{$column} = ?";
            $params[] = !empty($value) ? $value : null;
            continue;
        }

        $sets[] = "{$column} = ?";
        $params[] = $value;
    }

    if (empty($sets)) {
        return false;
    }

    $sets[] =
        "updated_at = NOW()";

    $params[] =
        $training_id;

    $sql = "
        UPDATE training_listings
        SET
            " . implode(
                ", ",
                $sets
            ) . "
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        $params
    );

    if ($statement->rowCount() < 1) {
        return false;
    }

    if (array_key_exists('status', $data)) {

        $status_value =
            $data['status'];

        if ($status_value === 'published') {

            db_execute(
                "
                    UPDATE training_listings
                    SET
                        published_at = COALESCE(published_at, NOW()),
                        closed_at = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                    LIMIT 1
                ",
                [$training_id]
            );
        }

        if ($status_value === 'closed') {

            db_execute(
                "
                    UPDATE training_listings
                    SET
                        closed_at = COALESCE(closed_at, NOW()),
                        updated_at = NOW()
                    WHERE id = ?
                    LIMIT 1
                ",
                [$training_id]
            );
        }

        if ($status_value === 'draft') {

            db_execute(
                "
                    UPDATE training_listings
                    SET
                        closed_at = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                    LIMIT 1
                ",
                [$training_id]
            );
        }
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Delete Training
|--------------------------------------------------------------------------
*/

function training_repository_delete(
    int $training_id
): bool {

    if ($training_id <= 0) {
        return false;
    }

    $sql = "
        DELETE FROM training_listings
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$training_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Close Training
|--------------------------------------------------------------------------
*/

function training_repository_close(
    int $training_id,
    ?string $closing_note = null
): bool {

    if ($training_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            status = 'closed',
            closed_at = COALESCE(closed_at, NOW()),
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$training_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Reopen Training
|--------------------------------------------------------------------------
*/

function training_repository_reopen(
    int $training_id
): bool {

    if ($training_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            status = 'published',
            published_at = COALESCE(published_at, NOW()),
            closed_at = NULL,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$training_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Publish Training
|--------------------------------------------------------------------------
*/

function training_repository_publish(
    int $training_id
): bool {

    if ($training_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            status = 'published',
            published_at = COALESCE(published_at, NOW()),
            closed_at = NULL,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$training_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Find Published Trainings
|--------------------------------------------------------------------------
*/

function training_repository_get_published(
    int $limit = 20,
    int $offset = 0
): array {

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        WHERE t.status = 'published'
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Published Trainings
|--------------------------------------------------------------------------
*/

function training_repository_count_published(): int {

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings
        WHERE status = 'published'
    ";

    $row = db_fetch_one(
        $sql
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Public Training List
|--------------------------------------------------------------------------
|
| Used by the public listing endpoint to return
| only published training opportunities.
|
*/

function training_repository_get_public_list(
    array $filters = [],
    int $limit = 20,
    int $offset = 0
): array {

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $conditions = [
        "t.status = 'published'"
    ];

    $params = [];

    if (
        !empty($filters['company_id'])
    ) {

        $conditions[] =
            't.company_id = ?';

        $params[] =
            (int) $filters['company_id'];
    }

    if (
        !empty($filters['specialization'])
    ) {

        $conditions[] =
            't.field = ?';

        $params[] =
            $filters['specialization'];
    }

    if (
        !empty($filters['training_type'])
        &&
        in_array(
            $filters['training_type'],
            ['shadowing', 'hands_on', 'project_based'],
            true
        )
    ) {

        $conditions[] =
            't.training_type = ?';

        $params[] =
            $filters['training_type'];
    }

    if (
        !empty($filters['work_mode'])
        &&
        in_array(
            $filters['work_mode'],
            ['onsite', 'remote', 'hybrid'],
            true
        )
    ) {

        $conditions[] =
            't.mode = ?';

        $params[] =
            $filters['work_mode'];
    }

    if (
        isset($filters['paid'])
        &&
        $filters['paid'] !== null
    ) {

        $conditions[] =
            't.is_paid = ?';

        $params[] =
            (int) $filters['paid'] > 0 ? 1 : 0;
    }

    if (
        !empty($filters['keyword'])
    ) {

        $search =
            '%' . $filters['keyword'] . '%';

        $conditions[] =
            "(t.title LIKE ? OR t.description LIKE ? OR t.field LIKE ? OR t.location LIKE ?)";

        array_push(
            $params,
            $search,
            $search,
            $search,
            $search
        );
    }

    $where =
        implode(
            " AND ",
            $conditions
        );

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        WHERE {$where}
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        $params
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Public Trainings
|--------------------------------------------------------------------------
*/

function training_repository_count_public(
    array $filters = []
): int {

    $conditions = [
        "status = 'published'"
    ];

    $params = [];

    if (
        !empty($filters['company_id'])
    ) {

        $conditions[] =
            'company_id = ?';

        $params[] =
            (int) $filters['company_id'];
    }

    if (
        !empty($filters['specialization'])
    ) {

        $conditions[] =
            'field = ?';

        $params[] =
            $filters['specialization'];
    }

    if (
        !empty($filters['training_type'])
        &&
        in_array(
            $filters['training_type'],
            ['shadowing', 'hands_on', 'project_based'],
            true
        )
    ) {

        $conditions[] =
            'training_type = ?';

        $params[] =
            $filters['training_type'];
    }

    if (
        !empty($filters['work_mode'])
        &&
        in_array(
            $filters['work_mode'],
            ['onsite', 'remote', 'hybrid'],
            true
        )
    ) {

        $conditions[] =
            'mode = ?';

        $params[] =
            $filters['work_mode'];
    }

    if (
        isset($filters['paid'])
        &&
        $filters['paid'] !== null
    ) {

        $conditions[] =
            'is_paid = ?';

        $params[] =
            (int) $filters['paid'] > 0 ? 1 : 0;
    }

    if (
        !empty($filters['keyword'])
    ) {

        $search =
            '%' . $filters['keyword'] . '%';

        $conditions[] =
            "(title LIKE ? OR description LIKE ? OR field LIKE ? OR location LIKE ?)";

        array_push(
            $params,
            $search,
            $search,
            $search,
            $search
        );
    }

    $where =
        implode(
            " AND ",
            $conditions
        );

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings
        WHERE {$where}
    ";

    $row = db_fetch_one(
        $sql,
        $params
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Search Trainings
|--------------------------------------------------------------------------
*/

function training_repository_search(
    string $keyword,
    int $limit = 20,
    int $offset = 0
): array {

    $keyword =
        trim($keyword);

    if ($keyword === '') {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $search =
        '%' . $keyword . '%';

    $sql = "
        SELECT
            t.*
        FROM training_listings t
        WHERE
            t.title LIKE ?
            OR t.description LIKE ?
            OR t.location LIKE ?
            OR t.field LIKE ?
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [
            $search,
            $search,
            $search,
            $search
        ]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Search Results
|--------------------------------------------------------------------------
*/

function training_repository_count_search(
    string $keyword
): int {

    $keyword =
        trim($keyword);

    if ($keyword === '') {
        return 0;
    }

    $search =
        '%' . $keyword . '%';

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings
        WHERE
            title LIKE ?
            OR description LIKE ?
            OR location LIKE ?
            OR field LIKE ?
    "; 

    $row = db_fetch_one(
        $sql,
        [
            $search,
            $search,
            $search,
            $search
        ]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Training With Company
|--------------------------------------------------------------------------
*/

function training_repository_find_with_company(
    int $training_id
): ?array {

    if ($training_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            t.*,

            c.id AS company_id,
            c.legal_name AS company_name,
            c.city AS company_city,
            NULL AS company_logo

        FROM training_listings t

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE t.id = ?

        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$training_id]
    );
}


/*
|--------------------------------------------------------------------------
| Check Training Ownership
|--------------------------------------------------------------------------
*/

function training_repository_belongs_to_company(
    int $training_id,
    int $company_id
): bool {

    if (
        $training_id <= 0
        ||
        $company_id <= 0
    ) {
        return false;
    }

    $sql = "
        SELECT
            id
        FROM training_listings
        WHERE
            id = ?
            AND company_id = ?
        LIMIT 1
    ";

    $row = db_fetch_one(
        $sql,
        [
            $training_id,
            $company_id
        ]
    );

    return !empty($row);
}


/*
|--------------------------------------------------------------------------
| Update Training Status
|--------------------------------------------------------------------------
*/

function training_repository_update_status(
    int $training_id,
    string $status
): bool {

    if (
        $training_id <= 0
        ||
        trim($status) === ''
    ) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            status = ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $status,
            $training_id
        ]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Increment Training Capacity
|--------------------------------------------------------------------------
*/

function training_repository_increment_capacity(
    int $training_id,
    int $amount = 1
): bool {

    if (
        $training_id <= 0
        ||
        $amount <= 0
    ) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            capacity = COALESCE(capacity, 0) + ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $amount,
            $training_id
        ]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Decrement Training Capacity
|--------------------------------------------------------------------------
*/

function training_repository_decrement_capacity(
    int $training_id,
    int $amount = 1
): bool {

    if (
        $training_id <= 0
        ||
        $amount <= 0
    ) {
        return false;
    }

    $sql = "
        UPDATE training_listings
        SET
            capacity =
                CASE
                    WHEN COALESCE(capacity, 0) >= ?
                    THEN capacity - ?
                    ELSE 0
                END,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $amount,
            $amount,
            $training_id
        ]
    );
    return $statement->rowCount() > 0;
}
