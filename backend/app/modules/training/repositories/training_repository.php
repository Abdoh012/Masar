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
| Get Student Specialization ID
|--------------------------------------------------------------------------
|
| Returns the specialization_id of a student, or null when the student
| does not exist or has no specialization set.
|
*/

function training_repository_get_student_specialization_id(
    int $student_id
): ?int {

    if ($student_id <= 0) {
        return null;
    }

    $row = db_fetch_one(
        "
            SELECT
                s.specialization_id
            FROM students s
            WHERE s.id = ?
            LIMIT 1
        ",
        [$student_id]
    );

    if (
        !is_array($row)
        ||
        empty($row['specialization_id'])
    ) {
        return null;
    }

    return (int) $row['specialization_id'];
}


/*
|--------------------------------------------------------------------------
| Get Company Specialization IDs
|--------------------------------------------------------------------------
|
| Returns the specialization ids registered for a company through the
| company_specializations pivot table. Used to automatically inherit
| the company's specializations when it creates a training.
|
*/

function training_repository_get_company_specialization_ids(
    int $company_id
): array {

    if ($company_id <= 0) {
        return [];
    }

    $rows = db_fetch_all(
        "
            SELECT
                cs.specialization_id
            FROM company_specializations cs
            WHERE cs.company_id = ?
            ORDER BY cs.specialization_id ASC
        ",
        [$company_id]
    );

    if (!is_array($rows)) {
        return [];
    }

    $specialization_ids = [];

    foreach ($rows as $row) {

        $specialization_id =
            (int) $row['specialization_id'];

        if ($specialization_id > 0) {
            $specialization_ids[] =
                $specialization_id;
        }
    }

    return array_values(
        array_unique(
            $specialization_ids
        )
    );
}


/*
|--------------------------------------------------------------------------
| Replace Training Specializations
|--------------------------------------------------------------------------
|
| Synchronizes the training_specializations pivot rows for a training:
| existing rows are removed and the given specialization ids are
| reinserted. INSERT IGNORE + the composite primary key
| (training_id, specialization_id) make the operation idempotent and
| duplicate-safe. An empty id list leaves the training with zero
| specialization rows.
|
*/

function training_repository_replace_specializations(
    int $training_id,
    array $specialization_ids
): bool {

    if ($training_id <= 0) {
        return false;
    }

    /*
     | Remove current relationships first so the training always
     | reflects exactly the provided set.
     */

    db_execute(
        "
            DELETE FROM training_specializations
            WHERE training_id = ?
        ",
        [$training_id]
    );

    /*
     | Deduplicate and sanitize ids before inserting.
     */

    $clean_ids = [];

    foreach ($specialization_ids as $specialization_id) {

        $specialization_id =
            (int) $specialization_id;

        if ($specialization_id > 0) {
            $clean_ids[$specialization_id] =
                true;
        }
    }

    if (empty($clean_ids)) {
        return true;
    }

    $values = [];
    $params = [];

    foreach (
        array_keys($clean_ids)
        as $clean_id
    ) {
        $values[] =
            "(?, ?)";
        $params[] =
            $training_id;
        $params[] =
            $clean_id;
    }

    $sql = "
        INSERT IGNORE INTO training_specializations (
            training_id,
            specialization_id
        )
        VALUES
            " . implode(
                ", ",
                $values
            ) . "
    ";

    db_execute(
        $sql,
        $params
    );

    return true;
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

        'employment_possible' => 'employment_possible'

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
    int $offset = 0,
    string $sort = 'newest'
): array {

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $parts =
        training_repository_build_public_query(
            $filters
        );

    $saved_only =
        !empty($filters['saved_only'])
        &&
        !empty($filters['student_id']);

    $student_id =
        (int) (
            $filters['student_id']
            ?? 0
        );

    $sql = "
        SELECT
            t.*,

            c.legal_name AS company_name,
            c.city AS company_city,
            c.company_logo AS company_logo,

            " . (
                $saved_only
                    ? '1'
                    : 'CASE WHEN st.id IS NULL THEN 0 ELSE 1 END'
            ) . " AS is_saved

        FROM training_listings t

        LEFT JOIN companies c
            ON c.id = t.company_id

        " . (
            $saved_only
                ? ''
                : "LEFT JOIN saved_trainings st
                    ON st.training_id = t.id
                    AND st.student_id = ?"
        ) . "

        {$parts['joins']}

        WHERE {$parts['where']}

        " . training_repository_sort_clause(
            $sort
        ) . "

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $params = $saved_only
        ? $parts['params']
        : array_merge(
            [$student_id],
            $parts['params']
        );

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

    $parts =
        training_repository_build_public_query(
            $filters
        );

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_listings t
        {$parts['joins']}
        WHERE {$parts['where']}
    ";

    $row = db_fetch_one(
        $sql,
        $parts['params']
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Build Public Training Query Parts
|--------------------------------------------------------------------------
|
| Shared WHERE / JOIN builder used by both the public list and
| its total count so filters stay consistent.
|
*/

function training_repository_build_public_query(
    array $filters = []
): array {

    $joins = '';
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
        !empty($filters['specialization_id'])
    ) {

        $joins .=
            " JOIN training_specializations tsp
                ON tsp.training_id = t.id";

        $conditions[] =
            'tsp.specialization_id = ?';

        $params[] =
            (int) $filters['specialization_id'];
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization-Based Company Matching
    |--------------------------------------------------------------------------
    |
    | Restricts results to trainings owned by companies whose registered
    | industry (company_specializations) includes one of the given
    | specialization ids. Used for authenticated students so their
    | Opportunities page only shows relevant trainings.
    |
    | All ids are cast to int, so they are safe to inline in SQL.
    |
    */

    if (
        !empty($filters['match_company_specialization_ids'])
        &&
        is_array($filters['match_company_specialization_ids'])
    ) {

        $match_specialization_ids = [];

        foreach (
            $filters['match_company_specialization_ids']
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $match_specialization_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($match_specialization_ids)) {

            $joins .=
                " JOIN company_specializations csm
                    ON csm.company_id = t.company_id
                    AND csm.specialization_id IN ("
                    . implode(
                        ', ',
                        array_keys($match_specialization_ids)
                    )
                    . ")";
        }
    }

    if (
        !empty($filters['skill_id'])
    ) {

        $joins .=
            " JOIN training_skills tsk
                ON tsk.training_id = t.id";

        $conditions[] =
            'tsk.skill_id = ?';

        $params[] =
            (int) $filters['skill_id'];
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
        isset($filters['employment_possible'])
        &&
        $filters['employment_possible'] !== null
    ) {

        $conditions[] =
            't.may_lead_to_employment = ?';

        $params[] =
            (int) $filters['employment_possible'] > 0 ? 1 : 0;
    }

    if (
        !empty($filters['city'])
    ) {

        $conditions[] =
            't.location LIKE ?';

        $params[] =
            '%' . $filters['city'] . '%';
    }

    if (
        !empty($filters['keyword'])
    ) {

        $search =
            '%' . $filters['keyword'] . '%';

        $conditions[] =
            "(t.title LIKE ? OR t.description LIKE ? OR t.location LIKE ?)";

        array_push(
            $params,
            $search,
            $search,
            $search
        );
    }

    if (
        !empty($filters['saved_only'])
        &&
        !empty($filters['student_id'])
    ) {

        $joins .=
            " JOIN saved_trainings st
                ON st.training_id = t.id";

        $conditions[] =
            'st.student_id = ?';

        $params[] =
            (int) $filters['student_id'];
    }

    return [
        'joins' => $joins,
        'where' => implode(
            " AND ",
            $conditions
        ),
        'params' => $params,
    ];
}


/*
|--------------------------------------------------------------------------
| Public Training Sort Clause
|--------------------------------------------------------------------------
|
| Maps a whitelisted sort key to an ORDER BY clause.
|
*/

function training_repository_sort_clause(
    string $sort
): string {

    switch ($sort) {

        case 'oldest':
            return "ORDER BY t.created_at ASC";

        case 'title':
        case 'name':
            return "ORDER BY t.title ASC";

        case 'deadline':
            return "ORDER BY t.application_deadline IS NULL ASC, t.application_deadline ASC";

        case 'relevance':
            return "ORDER BY t.created_at DESC";

        case 'newest':
        default:
            return "ORDER BY t.created_at DESC";
    }
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
        ORDER BY t.created_at DESC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [
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
    "; 

    $row = db_fetch_one(
        $sql,
        [
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
            c.company_logo AS company_logo

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


/*
|--------------------------------------------------------------------------
| Save Training For Student
|--------------------------------------------------------------------------
*/

function training_repository_saved_add(
    int $student_id,
    int $training_id
): bool {

    if (
        $student_id <= 0
        ||
        $training_id <= 0
    ) {
        return false;
    }

    $sql = "
        INSERT IGNORE INTO saved_trainings (
            student_id,
            training_id
        )
        VALUES (?, ?)
    ";

    $statement = db_execute(
        $sql,
        [
            $student_id,
            $training_id
        ]
    );

    return true;
}


/*
|--------------------------------------------------------------------------
| Unsave Training For Student
|--------------------------------------------------------------------------
*/

function training_repository_saved_remove(
    int $student_id,
    int $training_id
): bool {

    if (
        $student_id <= 0
        ||
        $training_id <= 0
    ) {
        return false;
    }

    $sql = "
        DELETE FROM saved_trainings
        WHERE
            student_id = ?
            AND training_id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $student_id,
            $training_id
        ]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Check Training Saved By Student
|--------------------------------------------------------------------------
*/

function training_repository_is_saved(
    int $student_id,
    int $training_id
): bool {

    if (
        $student_id <= 0
        ||
        $training_id <= 0
    ) {
        return false;
    }

    $sql = "
        SELECT
            id
        FROM saved_trainings
        WHERE
            student_id = ?
            AND training_id = ?
        LIMIT 1
    ";

    $row = db_fetch_one(
        $sql,
        [
            $student_id,
            $training_id
        ]
    );

    return !empty($row);
}


/*
|--------------------------------------------------------------------------
| Get Saved Training IDs For Student
|--------------------------------------------------------------------------
*/

function training_repository_get_saved_ids(
    int $student_id
): array {

    if ($student_id <= 0) {
        return [];
    }

    $rows = db_fetch_all(
        "
            SELECT
                training_id
            FROM saved_trainings
            WHERE student_id = ?
            ORDER BY created_at DESC
        ",
        [$student_id]
    );

    if (!is_array($rows)) {
        return [];
    }

    return array_map(
        function ($row) {
            return (int) $row['training_id'];
        },
        $rows
    );
}


/*
|--------------------------------------------------------------------------
| Count Saved Trainings For Student
|--------------------------------------------------------------------------
*/

function training_repository_count_saved(
    int $student_id
): int {

    if ($student_id <= 0) {
        return 0;
    }

    $row = db_fetch_one(
        "
            SELECT
                COUNT(*) AS total
            FROM saved_trainings
            WHERE student_id = ?
        ",
        [$student_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Skills By Training IDs
|--------------------------------------------------------------------------
*/

function training_repository_get_skills_by_training_ids(
    array $training_ids
): array {

    if (empty($training_ids)) {
        return [];
    }

    $placeholders =
        implode(
            ',',
            array_fill(0, count($training_ids), '?')
        );

    $rows = db_fetch_all(
        "
            SELECT
                ts.training_id,
                s.id AS skill_id,
                s.name AS skill_name
            FROM training_skills ts
            INNER JOIN skills s
                ON s.id = ts.skill_id
            WHERE ts.training_id IN ({$placeholders})
            ORDER BY s.name ASC
        ",
        array_values($training_ids)
    );

    $grouped = [];

    foreach ($rows as $row) {
        $training_id = (int) $row['training_id'];

        if (!isset($grouped[$training_id])) {
            $grouped[$training_id] = [];
        }

        $grouped[$training_id][] = [
            'id' => (int) $row['skill_id'],
            'name' => $row['skill_name'],
        ];
    }

    return $grouped;
}


/*
|--------------------------------------------------------------------------
| Specializations By Training IDs
|--------------------------------------------------------------------------
*/

function training_repository_get_specializations_by_training_ids(
    array $training_ids
): array {

    if (empty($training_ids)) {
        return [];
    }

    $placeholders =
        implode(
            ',',
            array_fill(0, count($training_ids), '?')
        );

    $rows = db_fetch_all(
        "
            SELECT
                tsp.training_id,
                sp.id AS specialization_id,
                sp.name AS specialization_name
            FROM training_specializations tsp
            INNER JOIN specializations sp
                ON sp.id = tsp.specialization_id
            WHERE tsp.training_id IN ({$placeholders})
            ORDER BY sp.name ASC
        ",
        array_values($training_ids)
    );

    $grouped = [];

    foreach ($rows as $row) {
        $training_id = (int) $row['training_id'];

        if (!isset($grouped[$training_id])) {
            $grouped[$training_id] = [];
        }

        $grouped[$training_id][] = [
            'id' => (int) $row['specialization_id'],
            'name' => $row['specialization_name'],
        ];
    }

    return $grouped;
}


/*
|--------------------------------------------------------------------------
| Questions By Training ID
|--------------------------------------------------------------------------
*/

function training_repository_get_questions(
    int $training_id
): array {

    if ($training_id <= 0) {
        return [];
    }

    $rows = db_fetch_all(
        "
            SELECT
                id,
                question,
                question_type,
                required,
                options,
                sort_order
            FROM training_questions
            WHERE training_id = ?
            ORDER BY sort_order ASC, id ASC
        ",
        [$training_id]
    );

    if (!is_array($rows)) {
        return [];
    }

    return array_map(
        function ($row) {
            $row['id'] = (int) $row['id'];
            $row['required'] = (int) $row['required'] > 0;
            $row['sort_order'] = (int) $row['sort_order'];

            if (
                !empty($row['options'])
                &&
                in_array(
                    $row['question_type'],
                    ['select', 'radio'],
                    true
                )
            ) {
                $decoded = json_decode($row['options'], true);

                $row['options'] = is_array($decoded)
                    ? $decoded
                    : [];
            } else {
                $row['options'] = [];
            }

            return $row;
        },
        $rows
    );
}
