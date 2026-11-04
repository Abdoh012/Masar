<?php

/**
 * MASAR - Lookup Controller
 *
 * HTTP handling for lookup endpoints.
 */

require_once __DIR__ . '/../services/lookup_service.php';

function lookup_controller_respond(array $result, int $success_status = 200): void
{
    if (!empty($result['success'])) {
        response_success(
            $result['data'] ?? null,
            $result['message'] ?? 'Success.',
            $success_status
        );
    }

    response_error(
        $result['message'] ?? 'Unable to process lookup request.',
        (int) ($result['status'] ?? 400)
    );
}

function lookup_controller_study_fields(): void
{
    lookup_controller_respond(lookup_service_study_fields());
}

function lookup_controller_specializations_by_field(int $field_id): void
{
    lookup_controller_respond(lookup_service_specializations_by_field($field_id));
}

function lookup_controller_specializations(): void
{
    lookup_controller_respond(lookup_service_specializations());
}

function lookup_controller_skills(): void
{
    lookup_controller_respond(lookup_service_skills());
}

function lookup_controller_education_degrees(): void
{
    lookup_controller_respond(lookup_service_education_degrees());
}
