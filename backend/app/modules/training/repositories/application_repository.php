<?php

/**
 * MASAR - Application Repository
 *
 * Database operations for training applications.
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
| Find Application By ID
|--------------------------------------------------------------------------
*/

function application_repository_find_by_id(
    int $application_id
): ?array {

    if ($application_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            a.*
        FROM training_applications a
        WHERE a.id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$application_id]
    );
}


/*
|--------------------------------------------------------------------------
| Find Student By User ID
|--------------------------------------------------------------------------
*/

function application_repository_find_student_by_user_id(
    int $user_id
): ?array {

    if ($user_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            s.id AS student_id,
            s.*,
            u.email AS user_email
        FROM students s
        LEFT JOIN users u
            ON u.id = s.user_id
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
| Find Student By ID
|--------------------------------------------------------------------------
*/

function application_repository_find_student_by_id(
    int $student_id
): ?array {

    if ($student_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            s.id AS student_id,
            s.*,
            u.email AS user_email
        FROM students s
        LEFT JOIN users u
            ON u.id = s.user_id
        WHERE s.id = ?
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$student_id]
    );
}


/*
|--------------------------------------------------------------------------
| Find Company By User ID
|--------------------------------------------------------------------------
*/

function application_repository_find_company_by_user_id(
    int $user_id
): ?array {

    if ($user_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            c.id AS company_id,
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
| Find Application With Details
|--------------------------------------------------------------------------
*/

function application_repository_find_with_details(
    int $application_id
): ?array {

    if ($application_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            a.*,

            t.title AS training_title,
            t.company_id AS training_company_id,
            t.is_paid AS training_is_paid,
            t.compensation_amount AS training_compensation_amount,
            t.compensation_currency AS training_compensation_currency,

            s.id AS student_id,
            s.user_id AS student_user_id,

            s.full_name AS student_name,
            u.email AS student_email

        FROM training_applications a

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        LEFT JOIN students s
            ON s.id = a.student_id

        LEFT JOIN users u
            ON u.id = s.user_id

        WHERE a.id = ?

        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [$application_id]
    );
}


/*
|--------------------------------------------------------------------------
| Get Applications By Training
|--------------------------------------------------------------------------
*/

function application_repository_get_by_training(
    int $training_id,
    ?string $status = null
): array {

    if ($training_id <= 0) {
        return [];
    }

    if ($status === 'pending') {
        $status = 'submitted';
    }

    if ($status !== null && trim($status) !== '') {

        $sql = "
            SELECT
                a.*
            FROM training_applications a
            WHERE
                a.training_id = ?
                AND a.status = ?
            ORDER BY a.applied_at DESC
        ";

        $result = db_fetch_all(
            $sql,
            [
                $training_id,
                $status
            ]
        );

    } else {

        $sql = "
            SELECT
                a.*
            FROM training_applications a
            WHERE a.training_id = ?
            ORDER BY a.applied_at DESC
        ";

        $result = db_fetch_all(
            $sql,
            [$training_id]
        );
    }

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Get Applications By Training Paginated
|--------------------------------------------------------------------------
*/

function application_repository_get_by_training_paginated(
    int $training_id,
    int $limit = 20,
    int $offset = 0,
    ?string $status = null
): array {

    if ($training_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    if ($status === 'pending') {
        $status = 'submitted';
    }

    if ($status !== null && trim($status) !== '') {

        $sql = "
            SELECT
                a.*
            FROM training_applications a
            WHERE
                a.training_id = ?
                AND a.status = ?
            ORDER BY a.applied_at DESC
            LIMIT {$limit}
            OFFSET {$offset}
        ";

        $result = db_fetch_all(
            $sql,
            [
                $training_id,
                $status
            ]
        );

    } else {

        $sql = "
            SELECT
                a.*
            FROM training_applications a
            WHERE a.training_id = ?
            ORDER BY a.applied_at DESC
            LIMIT {$limit}
            OFFSET {$offset}
        ";

        $result = db_fetch_all(
            $sql,
            [$training_id]
        );
    }

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Applications By Training
|--------------------------------------------------------------------------
*/

function application_repository_count_by_training(
    int $training_id,
    ?string $status = null
): int {

    if ($training_id <= 0) {
        return 0;
    }

    if ($status === 'pending') {
        $status = 'submitted';
    }

    if ($status !== null && trim($status) !== '') {

        $sql = "
            SELECT
                COUNT(*) AS total
            FROM training_applications
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

    } else {

        $sql = "
            SELECT
                COUNT(*) AS total
            FROM training_applications
            WHERE training_id = ?
        ";

        $row = db_fetch_one(
            $sql,
            [$training_id]
        );
    }

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Find Student Application
|--------------------------------------------------------------------------
*/

function application_repository_find_student_application(
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
        ORDER BY a.applied_at DESC
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
| Check Existing Application
|--------------------------------------------------------------------------
*/

function application_repository_exists(
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
        FROM training_applications
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
| Create Application
|--------------------------------------------------------------------------
*/

function application_repository_create(
    array $data
): ?int {

    if (empty($data)) {
        return null;
    }

    $status =
        $data['status']
        ?? 'submitted';

    if ($status === 'pending') {
        $status = 'submitted';
    }

    if (!in_array($status, ['submitted', 'accepted', 'rejected', 'withdrawn'], true)) {
        $status = 'submitted';
    }

    $message =
        $data['message']
        ?? $data['cover_letter']
        ?? $data['notes']
        ?? null;

    $applicant_type =
        $data['applicant_type']
        ?? 'student';

    if (
        !in_array(
            $applicant_type,
            ['student', 'graduated'],
            true
        )
    ) {
        $applicant_type = 'student';
    }

    $sql = "
        INSERT INTO training_applications (
            training_id,
            student_id,
            company_id,
            message,
            full_name,
            email,
            phone,
            city,
            address,
            why_interested,
            what_to_learn,
            skills,
            cv_file_id,
            university,
            faculty_id,
            applicant_type,
            academic_year,
            graduation_year,
            motivation,
            status,
            applied_at
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
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW()
        )
    ";

    $params = [

        $data['training_id']
            ?? null,

        $data['student_id']
            ?? null,

        $data['company_id']
            ?? null,

        $message,

        $data['full_name']
            ?? null,

        $data['email']
            ?? null,

        $data['phone']
            ?? null,

        $data['city']
            ?? null,

        $data['address']
            ?? null,

        $data['why_interested']
            ?? null,

        $data['what_to_learn']
            ?? null,

        is_array($data['skills'] ?? null)
            ? json_encode(
                array_values($data['skills']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
            : ($data['skills'] ?? null),

        $data['cv_file_id']
            ?? null,

        $data['university']
            ?? null,

        $data['faculty_id']
            ?? null,

        $applicant_type,

        $data['academic_year']
            ?? null,

        $data['graduation_year']
            ?? null,

        $data['motivation']
            ?? null,

        $status

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
| Reapply For Training
|--------------------------------------------------------------------------
|
| Resets a previously rejected application back to a fresh submitted state
| with the new snapshot data. The training_applications table enforces a
| unique (training_id, student_id) index, so a re-application after a
| rejection reuses the existing row instead of attempting a second INSERT
| (which would violate the unique index and fail).
|
*/

function application_repository_reapply(
    int $application_id,
    array $data
): bool {

    if (
        $application_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $message =
        $data['message']
        ?? $data['cover_letter']
        ?? $data['notes']
        ?? null;

    $applicant_type =
        $data['applicant_type']
        ?? 'student';

    if (
        !in_array(
            $applicant_type,
            ['student', 'graduated'],
            true
        )
    ) {
        $applicant_type = 'student';
    }

    $skills =
        is_array($data['skills'] ?? null)
            ? json_encode(
                array_values($data['skills']),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
            : ($data['skills'] ?? null);

    $sql = "
        UPDATE training_applications
        SET
            company_id = ?,
            message = ?,
            full_name = ?,
            email = ?,
            phone = ?,
            city = ?,
            address = ?,
            why_interested = ?,
            what_to_learn = ?,
            skills = ?,
            cv_file_id = ?,
            university = ?,
            faculty_id = ?,
            applicant_type = ?,
            academic_year = ?,
            graduation_year = ?,
            motivation = ?,
            status = 'submitted',
            rejection_reason = NULL,
            rejection_note = NULL,
            reviewed_by = NULL,
            reviewed_at = NULL,
            applied_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $data['company_id']
                ?? null,

            $message,

            $data['full_name']
                ?? null,

            $data['email']
                ?? null,

            $data['phone']
                ?? null,

            $data['city']
                ?? null,

            $data['address']
                ?? null,

            $data['why_interested']
                ?? null,

            $data['what_to_learn']
                ?? null,

            $skills,

            $data['cv_file_id']
                ?? null,

            $data['university']
                ?? null,

            $data['faculty_id']
                ?? null,

            $applicant_type,

            $data['academic_year']
                ?? null,

            $data['graduation_year']
                ?? null,

            $data['motivation']
                ?? null,

            $application_id
        ]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Save Application Answers
|--------------------------------------------------------------------------
*/

function application_repository_save_answers(
    int $application_id,
    array $answers
): bool {

    if (
        $application_id <= 0
        ||
        empty($answers)
    ) {
        return false;
    }

    $inserted = 0;

    foreach ($answers as $answer) {

        $question_id =
            isset($answer['question_id'])
                ? (int) $answer['question_id']
                : 0;

        $value =
            $answer['answer']
            ?? $answer['value']
            ?? '';

        if (
            $question_id <= 0
            ||
            !is_string($value)
        ) {
            continue;
        }

        $statement = db_execute(
            "
                INSERT INTO application_answers (
                    application_id,
                    question_id,
                    answer
                )
                VALUES (?, ?, ?)
            ",
            [
                $application_id,
                $question_id,
                trim($value)
            ]
        );

        $inserted +=
            $statement->rowCount();
    }

    return $inserted > 0;
}


/*
|--------------------------------------------------------------------------
| Delete Application Answers
|--------------------------------------------------------------------------
|
| Removes all answers belonging to an application. Used when a rejected
| student re-applies and replaces their previous answers.
|
*/

function application_repository_delete_answers(
    int $application_id
): bool {

    if ($application_id <= 0) {
        return false;
    }

    $statement = db_execute(
        "
            DELETE FROM application_answers
            WHERE application_id = ?
        ",
        [$application_id]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Get Application Answers
|--------------------------------------------------------------------------
*/

function application_repository_get_answers(
    int $application_id
): array {

    if ($application_id <= 0) {
        return [];
    }

    $rows = db_fetch_all(
        "
            SELECT
                aa.question_id,
                aa.answer,
                tq.question,
                tq.question_type,
                tq.options
            FROM application_answers aa
            INNER JOIN training_questions tq
                ON tq.id = aa.question_id
            WHERE aa.application_id = ?
            ORDER BY tq.sort_order ASC, tq.id ASC
        ",
        [$application_id]
    );

    if (!is_array($rows)) {
        return [];
    }

    return array_map(
        function ($row) {

            $row['question_id'] =
                (int) $row['question_id'];

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


/*
|--------------------------------------------------------------------------
| Find Faculty By ID
|--------------------------------------------------------------------------
*/

function application_repository_find_faculty_by_id(
    int $faculty_id
): ?array {

    if ($faculty_id <= 0) {
        return null;
    }

    return db_fetch_one(
        "
            SELECT
                id,
                name
            FROM faculties
            WHERE id = ?
            LIMIT 1
        ",
        [$faculty_id]
    );
}


/*
|--------------------------------------------------------------------------
| Update Application
|--------------------------------------------------------------------------
*/

function application_repository_update(
    int $application_id,
    array $data
): bool {

    if (
        $application_id <= 0
        ||
        empty($data)
    ) {
        return false;
    }

    $column_map = [

        'status' => 'status',

        'message' => 'message',

        'cover_letter' => 'message',

        'notes' => 'message',

        'review_note' => 'rejection_note',

        'rejection_note' => 'rejection_note',

        'rejection_reason' => 'rejection_reason',

        'reviewed_by' => 'reviewed_by',

        'reviewed_at' => 'reviewed_at',

        'withdrawn_at' => 'withdrawn_at'

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

        if (
            $column === 'status'
            &&
            !in_array($value, ['submitted', 'accepted', 'rejected', 'withdrawn'], true)
        ) {
            continue;
        }

        if (
            $column === 'rejection_reason'
            &&
            $value !== null
            &&
            !in_array(
                $value,
                [
                    'position_filled',
                    'candidate_not_suitable',
                    'requirements_not_met',
                    'training_closed',
                    'other'
                ],
                true
            )
        ) {
            $value = 'other';
        }

        $sets[] = "{$column} = ?";
        $params[] = $value;
    }

    if (empty($sets)) {
        return false;
    }

    $params[] =
        $application_id;

    $sql = "
        UPDATE training_applications
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
| Update Application Status
|--------------------------------------------------------------------------
*/

function application_repository_update_status(
    int $application_id,
    string $status,
    ?int $reviewed_by = null,
    ?string $review_note = null
): bool {

    if (
        $application_id <= 0
        ||
        trim($status) === ''
    ) {
        return false;
    }

    if ($status === 'pending') {
        $status = 'submitted';
    }

    if (!in_array($status, ['submitted', 'accepted', 'rejected', 'withdrawn'], true)) {
        return false;
    }

    $sql = "
        UPDATE training_applications
        SET
            status = ?,
            reviewed_by = ?,
            rejection_note = ?,
            reviewed_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $status,
            $reviewed_by,
            $review_note,
            $application_id
        ]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Reject Pending Applications By Training
|--------------------------------------------------------------------------
*/

function application_repository_reject_pending_by_training(
    int $training_id
): int {

    if ($training_id <= 0) {
        return 0;
    }

    $sql = "
        UPDATE training_applications
        SET
            status = 'rejected',
            rejection_reason = 'training_closed',
            rejection_note = 'Training was closed before this application was reviewed.',
            reviewed_at = NOW()
        WHERE
            training_id = ?
            AND status = 'submitted'
    ";

    return db_execute(
        $sql,
        [$training_id]
    )->rowCount();
}


/*
|--------------------------------------------------------------------------
| Accept Application
|--------------------------------------------------------------------------
*/

function application_repository_accept(
    int $application_id,
    ?int $reviewed_by = null,
    ?string $review_note = null
): bool {

    return application_repository_update_status(
        $application_id,
        'accepted',
        $reviewed_by,
        $review_note
    );
}


/*
|--------------------------------------------------------------------------
| Reject Application
|--------------------------------------------------------------------------
*/

function application_repository_reject(
    int $application_id,
    ?int $reviewed_by = null,
    ?string $reason = null,
    ?string $note = null
): bool {

    if ($application_id <= 0) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Rejection Reason Mapping
    |--------------------------------------------------------------------------
    |
    | The database rejection_reason column is an enum of short codes. The
    | API accepts the preset human-readable reasons; each is mapped to its
    | code and the human-readable text is preserved in rejection_note.
    |
    */

    $reason_map = [
        'Candidate did not meet minimum requirements' => 'requirements_not_met',
        'Position already filled' => 'position_filled',
        'Insufficient capacity in training' => 'other',
        'Application incomplete' => 'other',
        'Candidate withdrew consideration' => 'candidate_not_suitable',
        'Training program discontinued' => 'training_closed',
    ];

    $allowed_codes = [
        'position_filled',
        'candidate_not_suitable',
        'requirements_not_met',
        'training_closed',
        'other'
    ];

    $rejection_reason =
        isset($reason_map[$reason])
            ? $reason_map[$reason]
            : (
                in_array($reason, $allowed_codes, true)
                    ? $reason
                    : 'other'
            );

    $sql = "
        UPDATE training_applications
        SET
            status = 'rejected',
            reviewed_by = ?,
            rejection_reason = ?,
            rejection_note = ?,
            reviewed_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [
            $reviewed_by,
            $rejection_reason,
            $note ?: $reason,
            $application_id
        ]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Withdraw Application
|--------------------------------------------------------------------------
*/

function application_repository_withdraw(
    int $application_id,
    ?string $reason = null
): bool {

    if ($application_id <= 0) {
        return false;
    }

    $sql = "
        UPDATE training_applications
        SET
            status = 'withdrawn',
            withdrawn_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$application_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Get Student Applications
|--------------------------------------------------------------------------
*/

function application_repository_get_by_student(
    int $student_id,
    int $limit = 20,
    int $offset = 0,
    ?string $status = null,
    ?array $match_specialization_ids = null
): array {

    if ($student_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    if ($status === 'pending') {
        $status = 'submitted';
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Optional restriction to trainings whose single primary specialization
    | (training_listings.specialization_id) is one of the given ids. Used by
    | the Applied endpoint so a student only sees pending applications for
    | trainings that match their own specialization. All ids are cast to int,
    | so they are safe to inline in SQL.
    |
    */

    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    if ($status !== null && trim($status) !== '') {

        $sql = "
            SELECT
                a.*,

                t.title AS training_title,
                t.company_id,
                t.starts_at,
                t.ends_at,
                t.location,
                c.legal_name AS company_name

            FROM training_applications a

            LEFT JOIN training_listings t
                ON t.id = a.training_id

            LEFT JOIN companies c
                ON c.id = t.company_id

            WHERE
                a.student_id = ?
                AND a.status = ?
                {$specialization_condition}

            ORDER BY a.applied_at DESC

            LIMIT {$limit}
            OFFSET {$offset}
        ";

        $result = db_fetch_all(
            $sql,
            [
                $student_id,
                $status
            ]
        );

    } else {

        $sql = "
            SELECT
                a.*,

                t.title AS training_title,
                t.company_id,
                t.starts_at,
                t.ends_at,
                t.location,
                c.legal_name AS company_name

            FROM training_applications a

            LEFT JOIN training_listings t
                ON t.id = a.training_id

            LEFT JOIN companies c
                ON c.id = t.company_id

            WHERE a.student_id = ?{$specialization_condition}

            ORDER BY a.applied_at DESC

            LIMIT {$limit}
            OFFSET {$offset}
        ";

        $result = db_fetch_all(
            $sql,
            [$student_id]
        );
    }

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Get Training Card Fields By IDs
|--------------------------------------------------------------------------
|
| Returns a map of training_id => card fields (title, type, mode, paid,
| dates, specialization name, company name and logo) used by the Applied
| card DTO. Mirrors the joins used by the training card list so the card
| values stay consistent with the training-card APIs.
|
*/

function application_repository_get_training_card_fields_by_ids(
    array $training_ids
): array {

    $training_ids = array_values(
        array_filter(
            array_map(
                'intval',
                $training_ids
            ),
            static function ($id): bool {
                return $id > 0;
            }
        )
    );

    if (empty($training_ids)) {
        return [];
    }

    $marks =
        implode(
            ', ',
            array_fill(
                0,
                count($training_ids),
                '?'
            )
        );

    $sql = "
        SELECT
            t.id AS training_id,
            t.title AS training_title,
            t.training_type,
            t.mode,
            t.is_paid,
            t.trial_period_days,
            t.starts_at,
            t.ends_at,
            s.name AS specialization_name,
            c.legal_name AS company_name,
            c.company_logo AS company_logo,
            c.bank_name,
            c.bank_account_name,
            c.bank_account_number,
            c.bank_transfer_instructions

        FROM training_listings t

        LEFT JOIN specializations s
            ON s.id = t.specialization_id

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE t.id IN ({$marks})
    ";

    $rows =
        db_fetch_all(
            $sql,
            $training_ids
        );

    $map = [];

    foreach (
        is_array($rows)
            ? $rows
            : []
        as $row
    ) {
        $map[(int) ($row['training_id'] ?? 0)] =
            $row;
    }

    return $map;
}


/*
|--------------------------------------------------------------------------
| Count Student Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_by_student(
    int $student_id,
    ?string $status = null,
    ?array $match_specialization_ids = null
): int {

    if ($student_id <= 0) {
        return 0;
    }

    if ($status === 'pending') {
        $status = 'submitted';
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Mirrors application_repository_get_by_student() so the pagination total
    | respects the same optional specialization restriction.
    |
    */

    $specialization_join = '';
    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_join =
                " INNER JOIN training_listings t
                    ON t.id = a.training_id";

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    if ($status !== null && trim($status) !== '') {

        $sql = "
            SELECT
                COUNT(*) AS total
            FROM training_applications a
            {$specialization_join}

            WHERE
                a.student_id = ?
                AND a.status = ?{$specialization_condition}
        ";

        $row = db_fetch_one(
            $sql,
            [
                $student_id,
                $status
            ]
        );

    } else {

        $sql = "
            SELECT
                COUNT(*) AS total
            FROM training_applications a
            {$specialization_join}

            WHERE a.student_id = ?{$specialization_condition}
        ";

        $row = db_fetch_one(
            $sql,
            [$student_id]
        );
    }

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Accepted Applications By Training
|--------------------------------------------------------------------------
*/

function application_repository_get_accepted_by_training(
    int $training_id
): array {

    if ($training_id <= 0) {
        return [];
    }

    $sql = "
        SELECT
            a.*,

            s.id AS student_id,
            s.user_id AS student_user_id,

            s.full_name AS student_name,
            u.email AS student_email

        FROM training_applications a

        LEFT JOIN students s
            ON s.id = a.student_id

        LEFT JOIN users u
            ON u.id = s.user_id

        WHERE
            a.training_id = ?
            AND a.status = 'accepted'

        ORDER BY a.reviewed_at DESC
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
| Count Accepted Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_accepted(
    int $training_id
): int {

    if ($training_id <= 0) {
        return 0;
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_applications
        WHERE
            training_id = ?
            AND status = 'accepted'
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
| Get Student Accepted Applications
|--------------------------------------------------------------------------
|
| Returns the accepted applications of one student, ordered by the
| acceptance timestamp (reviewed_at) so the Accepted tab shows the most
| recently accepted training first. Accepts the same optional specialization
| matching used by the Applied endpoint so a student only sees accepted
| trainings that match their own specialization.
|
*/

function application_repository_get_accepted_by_student(
    int $student_id,
    int $limit = 20,
    int $offset = 0,
    ?array $match_specialization_ids = null
): array {

    if ($student_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Same optional restriction as application_repository_get_by_student():
    | trainings whose single primary specialization
    | (training_listings.specialization_id) is one of the given ids. All ids
    | are cast to int, so they are safe to inline in SQL.
    |
    */

    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            a.*,

            t.title AS training_title,
            t.company_id,
            t.starts_at,
            t.ends_at,
            t.location,
            c.legal_name AS company_name

        FROM training_applications a

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE
            a.student_id = ?
            AND a.status = 'accepted'
            {$specialization_condition}

        ORDER BY a.reviewed_at DESC, a.applied_at DESC

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$student_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Student Accepted Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_accepted_by_student(
    int $student_id,
    ?array $match_specialization_ids = null
): int {

    if ($student_id <= 0) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Mirrors application_repository_get_accepted_by_student() so the
    | pagination total respects the same optional specialization restriction.
    |
    */

    $specialization_join = '';
    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_join =
                " INNER JOIN training_listings t
                    ON t.id = a.training_id";

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_applications a
        {$specialization_join}

        WHERE
            a.student_id = ?
            AND a.status = 'accepted'{$specialization_condition}
    ";

    $row = db_fetch_one(
        $sql,
        [$student_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Student Rejected Applications
|--------------------------------------------------------------------------
|
| Returns the rejected applications of one student, ordered by the rejection
| timestamp (reviewed_at) so the Rejected tab shows the most recently
| rejected training first. Accepts the same optional specialization matching
| used by the Applied/Accepted endpoints so a student only sees rejected
| trainings that match their own specialization. Only the columns needed to
| build the Rejected Card DTO are selected — the raw application row is never
| fetched by this endpoint.
|
*/

function application_repository_get_rejected_by_student(
    int $student_id,
    int $limit = 20,
    int $offset = 0,
    ?array $match_specialization_ids = null
): array {

    if ($student_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Same optional restriction as application_repository_get_accepted_by_student():
    | trainings whose single primary specialization
    | (training_listings.specialization_id) is one of the given ids. All ids
    | are cast to int, so they are safe to inline in SQL.
    |
    */

    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            a.id,
            a.training_id,
            a.reviewed_at,
            a.rejection_reason,
            a.rejection_note,

            t.title AS training_title,
            c.legal_name AS company_name

        FROM training_applications a

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE
            a.student_id = ?
            AND a.status = 'rejected'
            {$specialization_condition}

        ORDER BY a.reviewed_at DESC, a.applied_at DESC

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$student_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Student Rejected Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_rejected_by_student(
    int $student_id,
    ?array $match_specialization_ids = null
): int {

    if ($student_id <= 0) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Mirrors application_repository_get_rejected_by_student() so the
    | pagination total respects the same optional specialization restriction.
    |
    */

    $specialization_join = '';
    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_join =
                " INNER JOIN training_listings t
                    ON t.id = a.training_id";

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_applications a
        {$specialization_join}

        WHERE
            a.student_id = ?
            AND a.status = 'rejected'{$specialization_condition}
    ";

    $row = db_fetch_one(
        $sql,
        [$student_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Student Withdrawn Applications
|--------------------------------------------------------------------------
*/

function application_repository_get_withdrawn_by_student(
    int $student_id,
    int $limit = 20,
    int $offset = 0,
    ?array $match_specialization_ids = null
): array {

    if ($student_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Same optional restriction as application_repository_get_rejected_by_student():
    | only trainings whose single primary specialization
    | (training_listings.specialization_id) is one of the given ids. All ids
    | are cast to int, so they are safe to inline in SQL.
    |
    */

    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            a.id,
            a.training_id,
            a.withdrawn_at,

            t.title AS training_title,
            c.legal_name AS company_name

        FROM training_applications a

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE
            a.student_id = ?
            AND a.status = 'withdrawn'
            {$specialization_condition}

        ORDER BY a.withdrawn_at DESC, a.applied_at DESC

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$student_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Student Withdrawn Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_withdrawn_by_student(
    int $student_id,
    ?array $match_specialization_ids = null
): int {

    if ($student_id <= 0) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Mirrors application_repository_get_withdrawn_by_student() so the
    | pagination total respects the same optional specialization restriction.
    |
    */

    $specialization_join = '';
    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_join =
                " INNER JOIN training_listings t
                    ON t.id = a.training_id";

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_applications a
        {$specialization_join}

        WHERE
            a.student_id = ?
            AND a.status = 'withdrawn'{$specialization_condition}
    ";

    $row = db_fetch_one(
        $sql,
        [$student_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Get Student All Applications
|--------------------------------------------------------------------------
|
| Returns EVERY application of one student across the four tab states
| (submitted/pending -> Applied, accepted -> Accepted, rejected -> Rejected,
| withdrawn -> Withdrawn) in one unified, paginated list. Accepts the same
| optional specialization matching used by the Applied/Accepted/Rejected/
| Withdrawn endpoints: only trainings whose single primary specialization
| (training_listings.specialization_id) is one of the given ids qualify.
| Only the columns needed to build the four existing card DTOs are selected —
| the raw application row (with its PII) is never fetched.
|
| Ordering is deterministic newest-first by the most meaningful activity
| timestamp for each status: withdrawn_at for withdrawn applications,
| reviewed_at for accepted/rejected ones (when the company decided), and
| applied_at for still-pending ones. Ties fall back to the application id so
| pagination is stable.
|
*/

function application_repository_get_all_by_student(
    int $student_id,
    int $limit = 20,
    int $offset = 0,
    ?array $match_specialization_ids = null
): array {

    if ($student_id <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 100));
    $offset = max(0, $offset);

    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            a.id,
            a.training_id,
            a.student_id,
            a.status,
            a.applied_at,
            a.reviewed_at,
            a.withdrawn_at,
            a.rejection_reason,
            a.rejection_note,

            t.title AS training_title,
            c.legal_name AS company_name

        FROM training_applications a

        LEFT JOIN training_listings t
            ON t.id = a.training_id

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE
            a.student_id = ?
            AND a.status IN (
                'submitted',
                'accepted',
                'rejected',
                'withdrawn'
            )
            {$specialization_condition}

        ORDER BY
            GREATEST(
                COALESCE(a.withdrawn_at, a.applied_at),
                COALESCE(a.reviewed_at, a.applied_at),
                a.applied_at
            ) DESC,
            a.id DESC

        LIMIT {$limit}
        OFFSET {$offset}
    ";

    $result = db_fetch_all(
        $sql,
        [$student_id]
    );

    return is_array($result)
        ? $result
        : [];
}


/*
|--------------------------------------------------------------------------
| Count Student All Applications
|--------------------------------------------------------------------------
*/

function application_repository_count_all_by_student(
    int $student_id,
    ?array $match_specialization_ids = null
): int {

    if ($student_id <= 0) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Specialization Matching
    |--------------------------------------------------------------------------
    |
    | Mirrors application_repository_get_all_by_student() so the pagination
    | total respects the same optional specialization restriction.
    |
    */

    $specialization_join = '';
    $specialization_condition = '';

    if (
        is_array($match_specialization_ids)
        &&
        !empty($match_specialization_ids)
    ) {

        $clean_ids = [];

        foreach (
            $match_specialization_ids
            as $match_specialization_id
        ) {

            $match_specialization_id =
                (int) $match_specialization_id;

            if ($match_specialization_id > 0) {

                $clean_ids[$match_specialization_id] =
                    true;
            }
        }

        if (!empty($clean_ids)) {

            $specialization_join =
                " INNER JOIN training_listings t
                    ON t.id = a.training_id";

            $specialization_condition =
                ' AND t.specialization_id IN ('
                . implode(
                    ', ',
                    array_keys($clean_ids)
                )
                . ')';
        }
    }

    $sql = "
        SELECT
            COUNT(*) AS total
        FROM training_applications a
        {$specialization_join}

        WHERE
            a.student_id = ?
            AND a.status IN (
                'submitted',
                'accepted',
                'rejected',
                'withdrawn'
            ){$specialization_condition}
    ";

    $row = db_fetch_one(
        $sql,
        [$student_id]
    );

    return (int) (
        $row['total']
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Find Payment For Application
|--------------------------------------------------------------------------
|
| Returns the most recent manual-payment row for an accepted application
| (identified by its training + student). Used by the payment-confirmation
| flow to keep the lifecycle idempotent: confirming twice never creates a
| second payment row.
|
*/

function application_repository_find_payment_for_application(
    int $training_id,
    int $student_id
): ?array {

    if ($training_id <= 0 || $student_id <= 0) {
        return null;
    }

    $sql = "
        SELECT
            id,
            training_id,
            training_session_id,
            student_id,
            company_id,
            amount,
            currency,
            platform_commission_rate,
            platform_commission_amount,
            company_amount,
            payment_method,
            status,
            external_reference,
            paid_at,
            created_at,
            updated_at
        FROM payments
        WHERE
            training_id = ?
            AND student_id = ?
        ORDER BY id DESC
        LIMIT 1
    ";

    return db_fetch_one(
        $sql,
        [
            $training_id,
            $student_id
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Create Payment
|--------------------------------------------------------------------------
*/

function application_repository_create_payment(
    array $data
): int|false {

    if (empty($data)) {
        return false;
    }

    $columns = [];
    $placeholders = [];
    $params = [];

    foreach ($data as $column => $value) {
        $columns[] = $column;
        $placeholders[] = '?';
        $params[] = $value;
    }

    $sql = "
        INSERT INTO payments
        (" . implode(', ', $columns) . ")
        VALUES
        (" . implode(', ', $placeholders) . ")
    ";

    db_execute(
        $sql,
        $params
    );

    return (int) db_last_insert_id();
}


/*
|--------------------------------------------------------------------------
| Confirm Payment
|--------------------------------------------------------------------------
|
| Marks an existing payment row as paid with the current timestamp. Only
| the payment-confirmation service calls this; statuses are validated
| there before the row is touched.
|
*/

function application_repository_confirm_payment(
    int $payment_id
): bool {

    if ($payment_id <= 0) {
        return false;
    }

    $statement = db_execute(
        "
            UPDATE payments
            SET
                status = 'paid',
                paid_at = COALESCE(paid_at, NOW()),
                updated_at = NOW()
            WHERE id = ?
            LIMIT 1
        ",
        [$payment_id]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Update Payment Reference
|--------------------------------------------------------------------------
|
| Stores the student-submitted bank transfer reference on an existing
| payment row. Guarded to pending rows: a paid (or otherwise terminal)
| row is never mutated by the student's submit-reference flow, and the
| service performs all state checks before this is called.
|
*/

function application_repository_update_payment_reference(
    int $payment_id,
    string $reference
): bool {

    if ($payment_id <= 0) {
        return false;
    }

    $reference = trim($reference);

    if ($reference === '') {
        return false;
    }

    $statement = db_execute(
        "
            UPDATE payments
            SET
                external_reference = ?,
                updated_at = NOW()
            WHERE id = ?
            AND status = 'pending'
            LIMIT 1
        ",
        [
            $reference,
            $payment_id
        ]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Payment Map For Student
|--------------------------------------------------------------------------
|
| Returns the latest payment row per training for one student, keyed by
| training_id. Used by application_accepted_cards() so the Accepted ledger
| can expose the manual-payment state (pending vs paid) without an N+1
| lookup. Only rows for the given trainings are fetched.
|
*/

function application_repository_get_payment_map_for_student(
    int $student_id,
    array $training_ids
): array {

    if ($student_id <= 0) {
        return [];
    }

    $training_ids = array_values(
        array_filter(
            array_map(
                'intval',
                $training_ids
            ),
            static function ($id): bool {
                return $id > 0;
            }
        )
    );

    if (empty($training_ids)) {
        return [];
    }

    $marks =
        implode(
            ', ',
            array_fill(
                0,
                count($training_ids),
                '?'
            )
        );

    $params =
        array_merge(
            [$student_id],
            $training_ids
        );

    $sql = "
        SELECT
            training_id,
            student_id,
            status,
            paid_at,
            id AS payment_id
        FROM payments
        WHERE
            student_id = ?
            AND training_id IN ({$marks})
        ORDER BY id DESC
    ";

    $rows =
        db_fetch_all(
            $sql,
            $params
        );

    $map = [];

    foreach (
        is_array($rows)
            ? $rows
            : []
        as $row
    ) {

        $training_id =
            (int) ($row['training_id'] ?? 0);

        if (
            $training_id <= 0
            ||
            isset($map[$training_id])
        ) {
            continue;
        }

        $map[$training_id] = [
            'status' =>
                (string) ($row['status'] ?? 'pending'),
            'paid_at' =>
                ($row['paid_at'] ?? null)
                ? (string) $row['paid_at']
                : null,
            'payment_id' =>
                (int) ($row['payment_id'] ?? 0),
        ];
    }

    return $map;
}


/*
|--------------------------------------------------------------------------
| Get Pending Applications
|--------------------------------------------------------------------------
*/

function application_repository_get_pending_by_training(
    int $training_id
): array {

    return application_repository_get_by_training(
        $training_id,
        'submitted'
    );
}


/*
|--------------------------------------------------------------------------
| Check Student Enrollment
|--------------------------------------------------------------------------
*/

function application_repository_is_accepted(
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
        FROM training_applications
        WHERE
            student_id = ?
            AND training_id = ?
            AND status = 'accepted'
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
| Find Accepted Application
|--------------------------------------------------------------------------
*/

function application_repository_find_accepted(
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
| Get Application Statistics
|--------------------------------------------------------------------------
*/

function application_repository_get_training_statistics(
    int $training_id
): array {

    if ($training_id <= 0) {
        return [

            'total' => 0,

            'pending' => 0,

            'accepted' => 0,

            'rejected' => 0,

            'withdrawn' => 0

        ];
    }

    $sql = "
        SELECT
            COUNT(*) AS total,

            SUM(
                CASE
                    WHEN status = 'submitted'
                    THEN 1
                    ELSE 0
                END
            ) AS pending,

            SUM(
                CASE
                    WHEN status = 'accepted'
                    THEN 1
                    ELSE 0
                END
            ) AS accepted,

            SUM(
                CASE
                    WHEN status = 'rejected'
                    THEN 1
                    ELSE 0
                END
            ) AS rejected,

            SUM(
                CASE
                    WHEN status = 'withdrawn'
                    THEN 1
                    ELSE 0
                END
            ) AS withdrawn

        FROM training_applications

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

        'pending' =>
            (int) (
                $row['pending']
                ?? 0
            ),

        'accepted' =>
            (int) (
                $row['accepted']
                ?? 0
            ),

        'rejected' =>
            (int) (
                $row['rejected']
                ?? 0
            ),

        'withdrawn' =>
            (int) (
                $row['withdrawn']
                ?? 0
            )

    ];
}


/*
|--------------------------------------------------------------------------
| Delete Application
|--------------------------------------------------------------------------
*/

function application_repository_delete(
    int $application_id
): bool {

    if ($application_id <= 0) {
        return false;
    }

    $sql = "
        DELETE FROM training_applications
        WHERE id = ?
        LIMIT 1
    ";

    $statement = db_execute(
        $sql,
        [$application_id]
    );
    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Check Training Capacity
|--------------------------------------------------------------------------
|
| Returns true when the training still has available seats.
|
*/

function application_repository_training_has_capacity(
    int $training_id
): bool {

    if ($training_id <= 0) {
        return false;
    }

    $sql = "
        SELECT
            t.capacity,

            (
                SELECT COUNT(*)
                FROM training_applications a
                WHERE
                    a.training_id = t.id
                    AND a.status = 'accepted'
            ) AS accepted_count

        FROM training_listings t

        WHERE t.id = ?

        LIMIT 1
    ";

    $row = db_fetch_one(
        $sql,
        [$training_id]
    );

    if (!$row) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | NULL Capacity = Unlimited
    |--------------------------------------------------------------------------
    */

    if (
        $row['capacity'] === null
    ) {
        return true;
    }

    return
        (int) $row['accepted_count']
        <
        (int) $row['capacity'];
}
