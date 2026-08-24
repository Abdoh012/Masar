<?php

/**
 * MASAR - Lookup Service
 *
 * Business logic for lookup data used by registration forms:
 * study fields and their specializations.
 *
 * The field -> specialization relationship is the source of truth for
 * student registration (field + specialization) and company registration
 * (industry = specialization from the same specializations table).
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No direct SQL.
 */

require_once __DIR__ . '/../repositories/lookup_repository.php';

/*
|--------------------------------------------------------------------------
| Get Study Fields
|--------------------------------------------------------------------------
*/

function lookup_service_study_fields(): array
{
    return [
        'success' => true,
        'status' => 200,
        'data' => [
            'study_fields' => lookup_repository_get_active_study_fields(),
        ],
    ];
}


/*
|--------------------------------------------------------------------------
| Get Specializations By Study Field
|--------------------------------------------------------------------------
*/

function lookup_service_specializations_by_field(int $field_id): array
{
    if ($field_id <= 0) {
        return [
            'success' => false,
            'status' => 422,
            'message' => 'Invalid study field ID.',
        ];
    }

    $field = lookup_repository_find_active_study_field($field_id);

    if ($field === null) {
        return [
            'success' => false,
            'status' => 404,
            'message' => 'Study field not found.',
        ];
    }

    return [
        'success' => true,
        'status' => 200,
        'data' => [
            'study_field' => $field,
            'specializations' => lookup_repository_get_active_specializations_by_field($field_id),
        ],
    ];
}


/*
|--------------------------------------------------------------------------
| Get All Active Specializations
|--------------------------------------------------------------------------
|
| Shared list for students (Specialization) and companies (Industry).
|
*/

function lookup_service_specializations(): array
{
    return [
        'success' => true,
        'status' => 200,
        'data' => [
            'specializations' => lookup_repository_get_active_specializations(),
        ],
    ];
}
