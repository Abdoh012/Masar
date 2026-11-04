<?php

/**
 * MASAR - Lookup Repository
 *
 * Responsible only for database operations related to
 * lookup data (study fields and specializations).
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 */

require_once __DIR__ . '/../../../core/database/query.php';

/*
|--------------------------------------------------------------------------
| Get Active Study Fields
|--------------------------------------------------------------------------
*/

function lookup_repository_get_active_study_fields(): array
{
    $sql = "
        SELECT
            id,
            name
        FROM study_fields
        WHERE is_active = 1
        ORDER BY name ASC
    ";

    return db_fetch_all($sql);
}


/*
|--------------------------------------------------------------------------
| Get Active Specializations By Field
|--------------------------------------------------------------------------
*/

function lookup_repository_get_active_specializations_by_field(int $field_id): array
{
    if ($field_id <= 0) {
        return [];
    }

    $sql = "
        SELECT
            id,
            name,
            field_id
        FROM specializations
        WHERE field_id = ?
            AND is_active = 1
        ORDER BY name ASC
    ";

    return db_fetch_all($sql, [$field_id]);
}


/*
|--------------------------------------------------------------------------
| Get Active Specializations
|--------------------------------------------------------------------------
|
| All active specializations with their study field. This is the single
| source of truth for both the student specialization list and the
| company industry list.
|
*/

function lookup_repository_get_active_specializations(): array
{
    $sql = "
        SELECT
            s.id,
            s.name,
            s.field_id,
            sf.name AS field_name
        FROM specializations s
        LEFT JOIN study_fields sf
            ON sf.id = s.field_id
        WHERE s.is_active = 1
        ORDER BY sf.name ASC, s.name ASC
    ";

    return db_fetch_all($sql);
}


/*
|--------------------------------------------------------------------------
| Find Active Study Field By ID
|--------------------------------------------------------------------------
*/

function lookup_repository_find_active_study_field(int $field_id): ?array
{
    if ($field_id <= 0) {
        return null;
    }

    $row = db_fetch_one(
        " SELECT id, name FROM study_fields WHERE id = ? AND is_active = 1 LIMIT 1 ",
        [$field_id]
    );

    return is_array($row) ? $row : null;
}


/*
|--------------------------------------------------------------------------
| Get Active Skills
|--------------------------------------------------------------------------
|
| The shared skill taxonomy: the single source of truth for the skills a
| student may attach and the skills a company may filter by. It is read from
| the `skills` table rather than being listed in code, so the set a client sees
| is always exactly the set the API will accept.
|
| Inactive rows are excluded here, which is what makes is_active meaningful:
| a retired skill disappears from the picker and can no longer be attached,
| while the existing student_skills links of anyone who already had it are
| left intact.
|
*/

function lookup_repository_get_active_skills(): array
{
    $sql = "
        SELECT
            id,
            name
        FROM skills
        WHERE is_active = 1
        ORDER BY name ASC
    ";

    return db_fetch_all($sql);
}


/*
|--------------------------------------------------------------------------
| Get Active Education Degrees
|--------------------------------------------------------------------------
|
| The education degrees a student can pick from inside the student profile.
| It reads the shared `degrees` lookup (the same table the education history
| resolves against), so the picker and the persisted value can never drift.
|
*/

function lookup_repository_get_active_education_degrees(): array
{
    $sql = "
        SELECT
            id,
            name
        FROM degrees
        WHERE is_active = 1
        ORDER BY id ASC
    ";

    return db_fetch_all($sql);
}
