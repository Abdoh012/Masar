<?php

require_once __DIR__ . '/../repositories/search_repository.php';
require_once __DIR__ . '/../../training/repositories/application_repository.php';
require_once __DIR__ . '/../../../shared/enums/user_roles.php';

function search_service_student_id(int $user_id): int {
    /*
     * Resolve the authenticated user to a student profile id using the
     * canonical student lookup shared with the training module. Saved
     * state belongs to the student; guests and users without a student
     * profile resolve to 0, matching the training-listing endpoint's
     * optional student context.
     */
    if ($user_id <= 0) {
        return 0;
    }

    $student = application_repository_find_student_by_user_id($user_id);

    return $student
        ? (int) ($student['student_id'] ?? 0)
        : 0;
}

function search_service_apply_training_scope(array $filters): array {
    /*
     * Restrict training search/filter results to the authenticated
     * student's own specialization at the database query level.
     *
     * Only authenticated students are scoped. Guests and non-student
     * authenticated users (company/admin/trainer) keep the existing
     * behaviour of seeing all published trainings.
     *
     * A student is resolved through students.specialization_id (the
     * single-column specialization on the student record; there is no
     * student_specializations pivot in this schema) and matched against
     * training_listings.specialization_id, the one primary specialization
     * of each training, via an exact equality.
     *
     * When no specialization can be resolved the caller must NOT fall
     * back to "show everything"; the student's scope is empty, which the
     * repository/service translates to an empty result set.
     */
    $user_id = (int) ($filters['user_id'] ?? 0);
    $filters['student_id'] = $user_id > 0 ? search_service_student_id($user_id) : 0;

    if (!is_student_role($filters['role'] ?? null)) {
        $filters['training_scope_blocked'] = false;
        return $filters;
    }

    $student_id = (int) $filters['student_id'];

    if ($student_id <= 0) {
        $filters['training_scope_blocked'] = true;
        return $filters;
    }

    $specialization_ids = search_repository_student_specialization_ids($student_id);

    if (empty($specialization_ids)) {
        $filters['training_scope_blocked'] = true;
        return $filters;
    }

    $filters['training_scope_blocked'] = false;
    $filters['match_specialization_ids'] = $specialization_ids;
    return $filters;
}

function search_service_normalize_query(string $query): string {
    return trim((string) preg_replace('/\s+/u', ' ', trim($query)));
}

function search_service_valid_query(string $query): bool {
    $length = mb_strlen($query);
    return $query !== '' && $length >= 2 && $length <= 255;
}

function search_service_limit(mixed $limit, int $default = 20, int $maximum = 100): int {
    $limit = (int) $limit;
    return min($limit > 0 ? $limit : $default, $maximum);
}

function search_service_search(string $query, array $filters = []): array {
    $query = search_service_normalize_query($query);
    $filters['page'] = max(1, (int) ($filters['page'] ?? 1));
    $filters['limit'] = search_service_limit($filters['limit'] ?? 20);
    $filters['sort'] = in_array(($filters['sort'] ?? 'relevance'), ['relevance', 'date', 'created_at', 'updated_at', 'name', 'title'], true) ? ($filters['sort'] ?? 'relevance') : 'relevance';
    $filters['order'] = in_array(strtoupper((string) ($filters['order'] ?? 'DESC')), ['ASC', 'DESC'], true) ? strtoupper((string) ($filters['order'] ?? 'DESC')) : 'DESC';
    if (!search_service_valid_query($query)) return ['items' => [], 'total' => 0, 'page' => 1, 'limit' => 20, 'query' => $query];
    if (($filters['type'] ?? '') === 'trainings') {
        $filters = search_service_apply_training_scope($filters);
    }
    if (!empty($filters['training_scope_blocked'])) {
        return ['items' => [], 'total' => 0, 'page' => $filters['page'], 'limit' => $filters['limit'], 'query' => $query];
    }
    $result = search_repository_search($query, $filters);
    $items = is_array($result['items'] ?? null) ? array_values($result['items']) : [];
    return ['items' => $items, 'total' => (int) ($result['total'] ?? count($items)), 'page' => $filters['page'], 'limit' => $filters['limit'], 'query' => $query, 'save_state_context' => ((int) ($filters['student_id'] ?? 0)) > 0 ? 'student' : 'guest'];
}

function search_service_trainings_filters(array $filters = []): array {
    /*
     * Training Filters API: type/mode/price filtering with whitelisted
     * sorting and standard pagination. The repository re-validates the
     * sort whitelist; page and limit are clamped defensively here.
     */
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = search_service_limit($filters['limit'] ?? 20);
    $filters = search_service_apply_training_scope(array_merge($filters, ['type' => 'trainings']));
    if (!empty($filters['training_scope_blocked'])) {
        return ['items' => [], 'total' => 0, 'page' => $page, 'limit' => $limit];
    }
    $result = search_repository_trainings_filters([
        'training_type' => $filters['training_type'] ?? '',
        'mode' => $filters['mode'] ?? '',
        'paid' => $filters['paid'] ?? null,
        'sort' => $filters['sort'] ?? 'newest',
        'page' => $page,
        'limit' => $limit,
        'student_id' => $filters['student_id'] ?? 0,
        'match_specialization_ids' => $filters['match_specialization_ids'] ?? [],
    ]);
    $items = is_array($result['items'] ?? null) ? array_values($result['items']) : [];
    return ['items' => $items, 'total' => (int) ($result['total'] ?? count($items)), 'page' => $page, 'limit' => $limit, 'save_state_context' => ((int) ($filters['student_id'] ?? 0)) > 0 ? 'student' : 'guest'];
}

function search_service_suggestions(string $query, array $options = []): array {
    $query = search_service_normalize_query($query);
    if (!search_service_valid_query($query)) return [];
    return search_repository_suggestions($query, ['limit' => search_service_limit($options['limit'] ?? 10, 10, 20)]);
}

function search_service_recent(int $user_id, array $options = []): array {
    return $user_id > 0 ? search_repository_recent($user_id, ['limit' => search_service_limit($options['limit'] ?? 10, 10, 50)]) : [];
}

function search_service_save_search(int $user_id, string $query, array $metadata = []): bool {
    $query = search_service_normalize_query($query);
    return search_service_valid_query($query) && search_repository_save_search($user_id, $query, $metadata);
}

function search_service_clear_recent(int $user_id): bool {
    return search_repository_clear_recent($user_id);
}
