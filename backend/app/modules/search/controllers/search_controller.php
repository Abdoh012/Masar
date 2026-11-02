<?php

require_once __DIR__ . '/../services/search_service.php';

function search_controller_success(mixed $data = null, string $message = 'Success.'): array {
    return ['success' => true, 'message' => $message, 'data' => $data];
}

function search_controller_error(string $message, int $status = 400): array {
    return ['success' => false, 'message' => $message, 'data' => null, 'status' => $status];
}

function search_controller_user_id(): int {
    $user = function_exists('auth_user') ? auth_user() : [];
    return max(0, (int) ($user['id'] ?? 0));
}

function search_controller_search(array $request = [], int $user_id = 0): array {
    $query = trim((string) ($request['q'] ?? $request['query'] ?? $request['search'] ?? ''));
    if ($query === '') return search_controller_error('Search query is required.');
    $request['role'] = (auth_user()['role'] ?? null);
    try { return search_controller_success(search_service_search($query, array_merge($request, ['user_id' => $user_id])), 'Search completed successfully.'); }
    catch (Throwable $exception) { return search_controller_error('Unable to complete search.'); }
}

function search_controller_users(array $request = [], int $user_id = 0): array { return search_controller_type('users', $request, $user_id); }
function search_controller_companies(array $request = [], int $user_id = 0): array { return search_controller_type('companies', $request, $user_id); }
function search_controller_trainings(array $request = [], int $user_id = 0): array {
    /*
     * Unified Training Search + Filters API backed by the single
     * GET /api/v1/search/trainings endpoint.
     *
     * Accepts an optional keyword (q / query / search) together with any
     * combination of the training filter parameters:
     *   training_type (shadowing | hands_on | project_based)
     *   mode         (onsite | remote | hybrid)
     *   paid         (0 = free, 1 = paid)
     *   sort         (newest | oldest | price_asc | price_desc | duration_asc | duration_desc)
     *   page         (positive integer, default 1)
     *   limit        (integer 1-100, default 20)
     *
     * When a keyword is present the search is performed with the filters
     * applied; when no keyword is present the request behaves exactly like
     * the previous filter-only API. Student specialization scope and the
     * training card response shape are preserved in both cases.
     */
    $allowed_types = ['shadowing', 'hands_on', 'project_based'];
    $allowed_modes = ['onsite', 'remote', 'hybrid'];
    $allowed_sorts = ['newest', 'oldest', 'price_asc', 'price_desc', 'duration_asc', 'duration_desc', 'relevance', 'date', 'created_at', 'updated_at', 'name', 'title'];

    $training_type = strtolower(trim((string) ($request['training_type'] ?? '')));
    if ($training_type !== '' && !in_array($training_type, $allowed_types, true)) {
        return search_controller_error('Invalid training_type value. Allowed: ' . implode(', ', $allowed_types) . '.', 422);
    }

    $mode = strtolower(trim((string) ($request['mode'] ?? '')));
    if ($mode !== '' && !in_array($mode, $allowed_modes, true)) {
        return search_controller_error('Invalid mode value. Allowed: ' . implode(', ', $allowed_modes) . '.', 422);
    }

    $paid_raw = trim((string) ($request['paid'] ?? ''));
    if ($paid_raw !== '' && !in_array($paid_raw, ['0', '1'], true)) {
        return search_controller_error('Invalid paid value. Allowed: 0 (free), 1 (paid).', 422);
    }

    $sort = strtolower(trim((string) ($request['sort'] ?? '')));
    if ($sort !== '' && !in_array($sort, $allowed_sorts, true)) {
        return search_controller_error('Invalid sort value. Allowed: ' . implode(', ', $allowed_sorts) . '.', 422);
    }

    $page_raw = $request['page'] ?? '';
    if ($page_raw !== '' && (filter_var($page_raw, FILTER_VALIDATE_INT) === false || (int) $page_raw < 1)) {
        return search_controller_error('Invalid page value. Page must be a positive integer.', 422);
    }

    $limit_raw = $request['limit'] ?? '';
    if ($limit_raw !== '' && (filter_var($limit_raw, FILTER_VALIDATE_INT) === false || (int) $limit_raw < 1 || (int) $limit_raw > 100)) {
        return search_controller_error('Invalid limit value. Limit must be an integer between 1 and 100.', 422);
    }

    try {
        $result = search_service_trainings([
            'query' => trim((string) ($request['q'] ?? $request['query'] ?? $request['search'] ?? '')),
            'training_type' => $training_type,
            'mode' => $mode,
            'paid' => $paid_raw === '' ? null : $paid_raw,
            'sort' => $sort,
            'page' => $page_raw === '' ? 1 : (int) $page_raw,
            'limit' => $limit_raw === '' ? 20 : (int) $limit_raw,
            'user_id' => $user_id,
            'role' => (auth_user()['role'] ?? null),
        ]);
        return search_controller_success($result, 'Training search and filters applied successfully.');
    } catch (Throwable $exception) {
        return search_controller_error('Unable to process training search and filters.');
    }
}
function search_controller_students(array $request = [], int $user_id = 0): array {
    /*
     * Student candidate search: keyword plus an optional skill filter.
     *
     * `skill_id` mirrors the existing training filter (single integer id) and
     * additionally accepts the repeated / comma-separated forms
     * `skill_id[]=5&skill_id[]=9` and `skill_id=5,9`, which mean ANY of the
     * listed skills. The semantics are documented in docs/api/search.md and
     * implemented once in search_repository_students().
     *
     * Values are validated here so a malformed id is a 422 rather than a
     * silently ignored filter; the repository re-normalizes them and binds
     * every id as a parameter.
     */
    $skill_ids = search_controller_skill_ids($request['skill_id'] ?? null);

    if ($skill_ids === null) {
        return search_controller_error('Invalid skill_id value. skill_id must be one or more positive integers.', 422);
    }

    if (empty($skill_ids)) {
        return search_controller_type('students', $request, $user_id);
    }

    return search_controller_type('students', array_merge($request, ['skill_id' => $skill_ids]), $user_id);
}

/*
 * Parses the accepted `skill_id` shapes into a list of positive integers.
 * Returns null when the caller sent something that is not a positive
 * integer, which the controller turns into a 422.
 */
function search_controller_skill_ids(mixed $raw): ?array {
    if ($raw === null || $raw === '' || $raw === []) {
        return [];
    }

    $values = is_array($raw) ? $raw : explode(',', (string) $raw);
    $ids = [];

    foreach ($values as $value) {
        if (is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            continue;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            return null;
        }

        $ids[(int) $value] = (int) $value;
    }

    ksort($ids);

    return array_values($ids);
}

function search_controller_certificates(array $request = [], int $user_id = 0): array { return search_controller_type('certificates', $request, $user_id); }
function search_controller_type(string $type, array $request, int $user_id): array { $request['type'] = $type; return search_controller_search($request, $user_id); }

function search_controller_suggestions(array $request = [], int $user_id = 0): array {
    $query = trim((string) ($request['q'] ?? $request['query'] ?? ''));
    if ($query === '') return search_controller_error('Search query is required.');
    try { return search_controller_success(search_service_suggestions($query, array_merge($request, ['user_id' => $user_id]))); }
    catch (Throwable $exception) { return search_controller_error('Unable to retrieve search suggestions.'); }
}

function search_controller_recent(int $user_id = 0, array $request = []): array {
    if ($user_id <= 0) return search_controller_error('Unauthorized.', 401);
    try { return search_controller_success(search_service_recent($user_id, $request)); }
    catch (Throwable $exception) { return search_controller_error('Unable to retrieve recent searches.'); }
}

function search_controller_clear_recent(int $user_id = 0): array {
    if ($user_id <= 0) return search_controller_error('Unauthorized.', 401);
    try { return search_controller_success(search_service_clear_recent($user_id), 'Recent searches cleared successfully.'); }
    catch (Throwable $exception) { return search_controller_error('Unable to clear recent searches.'); }
}
