<?php

/**
 * MASAR - Training Session Repository
 *
 * Database operations related to training sessions.
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
| Find Session By ID
|--------------------------------------------------------------------------
*/

function training_session_repository_find_by_id(
    int $session_id
): ?array {

    if ($session_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            ts.*,

            t.title AS training_title,
            t.company_id

        FROM training_sessions ts

        LEFT JOIN training_listings t
            ON t.id = ts.training_id

        WHERE ts.id = ?

        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$session_id]
    );
}


/*
|--------------------------------------------------------------------------
| Find Company By User ID
|--------------------------------------------------------------------------
*/

function training_session_repository_find_company_by_user_id(
    int $user_id
): ?array {

    if ($user_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            c.*
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
| Find Student By User ID
|--------------------------------------------------------------------------
*/

function training_session_repository_find_student_by_user_id(
    int $user_id
): ?array {

    if ($user_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            s.*
        FROM students s
        WHERE s.user_id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$user_id]
    );
}


/*
|--------------------------------------------------------------------------
| Create Training Session
|--------------------------------------------------------------------------
*/

function training_session_repository_create(
    array $data
): ?int {

    if (empty($data)) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Real training_sessions are per-student training trials.
    | They require an accepted application, a student and a company.
    |--------------------------------------------------------------------------
    */

    if (
        empty($data['application_id'])
        &&
        isset($data['training_id'])
        &&
        (
            empty($data['student_id'])
            ||
            empty($data['company_id'])
        )
    ) {
        return null;
    }

    $status_map = [
        'scheduled' => 'trial',
        'in_progress' => 'continuing',
        'deleted' => 'cancelled',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'stopped' => 'stopped'
    ];

    $status =
        $data['status']
        ?? 'trial';

    if (isset($status_map[$status])) {
        $status = $status_map[$status];
    }

    if (!in_array($status, ['trial', 'continuing', 'completed', 'stopped', 'cancelled'], true)) {
        $status = 'trial';
    }

    $started_at =
        $data['started_at']
        ?? (
            (!empty($data['session_date']) && !empty($data['start_time']))
                ? $data['session_date'] . ' ' . $data['start_time']
                : null
        )
        ?? $data['trial_start_date']
        ?? date('Y-m-d H:i:s');

    $trial_started_at =
        $data['trial_started_at']
        ?? $data['trial_start_date']
        ?? $started_at;

    $trial_ends_at =
        $data['trial_ends_at']
        ?? $data['trial_end_date']
        ?? null;

    $sql = "
        INSERT INTO training_sessions (
            application_id,
            training_id,
            student_id,
            company_id,
            status,
            started_at,
            trial_started_at,
            trial_ends_at,
            student_continuation_confirmed_at,
            actual_ended_at,
            employment_opportunity,
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
            ?,
            ?,
            NOW(),
            NOW()
        )
    ";

    $params = [

        $data['application_id']
            ?? null,

        $data['training_id']
            ?? null,

        $data['student_id']
            ?? $data['student_user_id']
            ?? null,

        $data['company_id']
            ?? null,

        $status,

        $started_at,

        $trial_started_at,

        $trial_ends_at,

        null,

        null,

        (int) (
            $data['employment_opportunity']
            ?? 0
        ) > 0 ? 1 : 0

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
| Update Training Session
|--------------------------------------------------------------------------
*/

function training_session_repository_update(
    int $session_id,
    array $data
): bool {

    if (
        $session_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $status_map = [
        'scheduled' => 'trial',
        'in_progress' => 'continuing',
        'deleted' => 'cancelled'
    ];

    $sets = [];

    $params = [];

    foreach (
        $data as $field => $value
    ) {

        if ($field === 'status') {

            if (isset($status_map[$value])) {
                $value = $status_map[$value];
            }

            if (!in_array($value, ['trial', 'continuing', 'completed', 'stopped', 'cancelled'], true)) {
                continue;
            }

            $sets[] = "status = ?";
            $params[] = $value;
            continue;
        }

        if ($field === 'session_date' || $field === 'start_time') {

            if (
                isset($data['session_date'])
                &&
                isset($data['start_time'])
                &&
                ($field === 'start_time')
            ) {

                $sets[] = "started_at = ?";
                $params[] = $data['session_date'] . ' ' . $data['start_time'];
            }
            continue;
        }

        if (in_array($field, [
            'title',
            'description',
            'location',
            'meeting_url',
            'end_time',
            'cancellation_reason'
        ], true)) {
            continue;
        }

        if ($field === 'completed_at') {
            $sets[] = "actual_ended_at = ?";
            $params[] = $value;
            continue;
        }

        if ($field === 'trial_start_date') {
            $sets[] = "trial_started_at = ?";
            $params[] = $value;
            continue;
        }

        if ($field === 'trial_end_date') {
            $sets[] = "trial_ends_at = ?";
            $params[] = $value;
            continue;
        }

        if (in_array($field, [
            'started_at',
            'trial_started_at',
            'trial_ends_at',
            'student_continuation_confirmed_at',
            'actual_ended_at',
            'employment_opportunity'
        ], true)) {

            $sets[] = "{$field} = ?";
            $params[] = $value;
        }
    }

    if (empty($sets)) {
        return false;
    }

    $sets[] =
        "updated_at = NOW()";

    $params[] =
        $session_id;

    $sql = "
        UPDATE training_sessions
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
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Find Duplicate Session
|--------------------------------------------------------------------------
*/

function training_session_repository_find_duplicate(
    int $training_id,
    ?string $session_date,
    ?string $start_time
): ?array {

    if (
        $training_id <= 0
        ||
        empty($session_date)
        ||
        empty($start_time)
    ) {
        return null;
    }

    $started_at =
        $session_date . ' ' . $start_time;

    $sql = "
        SELECT
            *
        FROM training_sessions
        WHERE
            training_id = ?
            AND started_at = ?
            AND status NOT IN (
                'completed',
                'cancelled',
                'stopped'
            )
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [
            $training_id,
            $started_at
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Get Sessions By Training
|--------------------------------------------------------------------------
*/

function training_session_repository_get_training_sessions(
    int $training_id,
    int $limit = 20,
    int $offset = 0
): array {

    if ($training_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $sql = "
        SELECT
            ts.*
        FROM training_sessions ts
        WHERE ts.training_id = ?
        ORDER BY
            ts.started_at ASC
        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$training_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Get All Sessions By Training
|--------------------------------------------------------------------------
*/

function training_session_repository_get_all_by_training(
    int $training_id
): array {

    if ($training_id <= 0) {
        return [];
    }

    $sql = "
        SELECT
            ts.*
        FROM training_sessions ts
        WHERE ts.training_id = ?
        ORDER BY
            ts.started_at ASC
    ";

    $result = db_fetch_all(
        $sql,
        [$training_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Training Sessions
|--------------------------------------------------------------------------
*/

function training_session_repository_count_training_sessions(
    int $training_id
): int {

    if ($training_id <= 0) {
        return 0;
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_sessions
        WHERE training_id = ?
    ";

    $row = db_fetch_one(
        $sql,
        [$training_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Map Session Status To Real Enum
|--------------------------------------------------------------------------
*/

function training_session_repository_map_status(
    string $status
): string {

    $status_map = [
        'scheduled' => 'trial',
        'in_progress' => 'continuing',
        'deleted' => 'cancelled'
    ];

    $mapped =
        $status_map[$status]
        ?? $status;

    return in_array(
        $mapped,
        ['trial', 'continuing', 'completed', 'stopped', 'cancelled'],
        true
    )
        ? $mapped
        : 'trial';
}


/*
|--------------------------------------------------------------------------
| Get Sessions By Status
|--------------------------------------------------------------------------
*/

function training_session_repository_get_by_status(
    int $training_id,
    string $status
): array {

    if (
        $training_id <= 0
        ||
        trim($status) === ''
    ) {
        return [];
    }

    $status = training_session_repository_map_status($status);

    $sql = "
        SELECT
            ts.*
        FROM training_sessions ts
        WHERE
            ts.training_id = ?
            AND ts.status = ?
        ORDER BY
            ts.started_at ASC
    ";

    $result = db_fetch_all(
        $sql,
        [
            $training_id,
            $status
        ]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Sessions By Status
|--------------------------------------------------------------------------
*/

function training_session_repository_count_by_status(
    int $training_id,
    string $status
): int {

    if (
        $training_id <= 0
        ||
        trim($status) === ''
    ) {
        return 0;
    }

    $status = training_session_repository_map_status($status);

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_sessions
        WHERE
            training_id = ?
            AND status = ?
    ";

    $row = db_fetch_one(
        $sql,
        [
            $training_id,
            $status
        ]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Find Accepted Application
|--------------------------------------------------------------------------
*/

function training_session_repository_find_accepted_application(
    int $student_id,
    int $training_id
): ?array {

    if (
        $student_id <= 0
        ||
        $training_id <= 0
    ) {
        return null;
    }

    $sql = "
        SELECT
            a.*
        FROM training_applications a
        WHERE
            a.student_id = ?
            AND a.training_id = ?
            AND a.status = 'accepted'
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [
            $student_id,
            $training_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
*/

function training_session_repository_start(
    int $session_id
): bool {

    if ($session_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_sessions
        SET
            status = 'continuing',
            student_continuation_confirmed_at = NOW(),
            updated_at = NOW()
        WHERE
            id = ?
            AND status = 'trial'
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$session_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Complete Session
|--------------------------------------------------------------------------
*/

function training_session_repository_complete(
    int $session_id
): bool {

    if ($session_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_sessions
        SET
            status = 'completed',
            actual_ended_at = NOW(),
            updated_at = NOW()
        WHERE
            id = ?
            AND status = 'continuing'
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$session_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Cancel Session
|--------------------------------------------------------------------------
*/

function training_session_repository_cancel(
    int $session_id,
    ?string $reason = null
): bool {

    if ($session_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_sessions
        SET
            status = 'cancelled',
            actual_ended_at = NOW(),
            updated_at = NOW()
        WHERE
            id = ?
            AND status NOT IN (
                'completed',
                'cancelled'
            )
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$session_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Delete Session
|--------------------------------------------------------------------------
*/

function training_session_repository_delete(
    int $session_id
): bool {

    if ($session_id <= 0) {
        return false;
    }

    $sql = "
        DELETE FROM training_sessions
        WHERE
            id = ?
            AND status = 'trial'
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$session_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Soft Delete Session
|--------------------------------------------------------------------------
|
| The real training_sessions.status enum has no 'deleted' value,
| so a true soft delete is not possible at the database level.
| As a safe equivalent, the session is hard deleted when still a trial.
|
*/

function training_session_repository_soft_delete(
    int $session_id
): bool {

    if ($session_id <= 0) {
        return false;
    }

    $sql = "
        DELETE FROM training_sessions
        WHERE
            id = ?
            AND status IN (
                'trial',
                'continuing'
            )
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$session_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Check Session Ownership
|--------------------------------------------------------------------------
*/

function training_session_repository_belongs_to_company(
    int $session_id,
    int $company_id
): bool {

    if (
        $session_id <= 0
        ||
        $company_id <= 0
    ) {
        return false;
    }

    $sql = "
        SELECT
            ts.id
        FROM training_sessions ts

        INNER JOIN training_listings t
            ON t.id = ts.training_id

        WHERE
            ts.id = ?
            AND t.company_id = ?

        LIMIT 1
    ";

    $row = db_fetch_one(
        $sql,
        [
            $session_id,
            $company_id
        ]
    );

    return !empty($row);
}


/*
|--------------------------------------------------------------------------
| Get Session Statistics
|--------------------------------------------------------------------------
*/

function training_session_repository_get_statistics(
    int $training_id
): array {

    if ($training_id <= 0) {

        return [

            'total' => 0,

            'scheduled' => 0,

            'in_progress' => 0,

            'completed' => 0,

            'cancelled' => 0,

            'deleted' => 0

        ];
    }

    $sql = "
        SELECT

            COUNT(*) AS total,

            SUM(
                CASE
                    WHEN status = 'trial'
                    THEN 1
                    ELSE 0
                END
            ) AS scheduled,

            SUM(
                CASE
                    WHEN status = 'continuing'
                    THEN 1
                    ELSE 0
                END
            ) AS in_progress,

            SUM(
                CASE
                    WHEN status = 'completed'
                    THEN 1
                    ELSE 0
                END
            ) AS completed,

            SUM(
                CASE
                    WHEN status = 'cancelled'
                    THEN 1
                    ELSE 0
                END
            ) AS cancelled,

            SUM(
                CASE
                    WHEN status = 'stopped'
                    THEN 1
                    ELSE 0
                END
            ) AS deleted

        FROM training_sessions

        WHERE training_id = ?
    ";

    $row = db_fetch_one(
        $sql,
        [$training_id]
    );

    return [

        'total' =>
            (int) (
                $row['total']
                ?? 0
            ),

        'scheduled' =>
            (int) (
                $row['scheduled']
                ?? 0
            ),

        'in_progress' =>
            (int) (
                $row['in_progress']
                ?? 0
            ),

        'completed' =>
            (int) (
                $row['completed']
                ?? 0
            ),

        'cancelled' =>
            (int) (
                $row['cancelled']
                ?? 0
            ),

        'deleted' =>
            (int) (
                $row['deleted']
                ?? 0
            )

    ];
}


/*
|--------------------------------------------------------------------------
| Get Upcoming Sessions
|--------------------------------------------------------------------------
*/

function training_session_repository_get_upcoming(
    int $training_id,
    int $limit = 10
): array {

    if ($training_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));

    $sql = "
        SELECT
            ts.*
        FROM training_sessions ts
        WHERE
            ts.training_id = ?
            AND ts.status = 'trial'
            AND ts.started_at > NOW()
        ORDER BY
            ts.started_at ASC
        LIMIT {$limit}
    ";

    $result = db_fetch_all(
        $sql,
        [$training_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Get Today's Sessions
|--------------------------------------------------------------------------
*/

function training_session_repository_get_today(
    int $training_id
): array {

    if ($training_id <= 0) {
        return [];
    }

    $sql = "
        SELECT
            ts.*
        FROM training_sessions ts
        WHERE
            ts.training_id = ?
            AND DATE(ts.started_at) = CURDATE()
        ORDER BY
            ts.started_at ASC
    ";

    $result = db_fetch_all(
        $sql,
        [$training_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Update Session Status
|--------------------------------------------------------------------------
*/

function training_session_repository_update_status(
    int $session_id,
    string $status
): bool {

    if (
        $session_id <= 0
        ||
        trim($status) === ''
    ) {
        return false;
    }

    $status = training_session_repository_map_status($status);

    $sql = "
        UPDATE training_sessions
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
            $session_id
        ]
    );
    return $statement->rowCount() > 0;
}
