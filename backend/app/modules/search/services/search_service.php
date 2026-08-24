<?php

require_once __DIR__ . '/../repositories/search_repository.php';

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
    $result = search_repository_search($query, $filters);
    $items = is_array($result['items'] ?? null) ? array_values($result['items']) : [];
    return ['items' => $items, 'total' => (int) ($result['total'] ?? count($items)), 'page' => $filters['page'], 'limit' => $filters['limit'], 'query' => $query];
}

function search_service_trainings_filters(array $filters = []): array {
    /*
     * Training Filters API: type/mode/price filtering with whitelisted
     * sorting and standard pagination. The repository re-validates the
     * sort whitelist; page and limit are clamped defensively here.
     */
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = search_service_limit($filters['limit'] ?? 20);
    $result = search_repository_trainings_filters([
        'training_type' => $filters['training_type'] ?? '',
        'mode' => $filters['mode'] ?? '',
        'paid' => $filters['paid'] ?? null,
        'sort' => $filters['sort'] ?? 'newest',
        'page' => $page,
        'limit' => $limit,
    ]);
    $items = is_array($result['items'] ?? null) ? array_values($result['items']) : [];
    return ['items' => $items, 'total' => (int) ($result['total'] ?? count($items)), 'page' => $page, 'limit' => $limit];
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
