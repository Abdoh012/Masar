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
    try { return search_controller_success(search_service_search($query, array_merge($request, ['user_id' => $user_id])), 'Search completed successfully.'); }
    catch (Throwable $exception) { return search_controller_error('Unable to complete search.'); }
}

function search_controller_users(array $request = [], int $user_id = 0): array { return search_controller_type('users', $request, $user_id); }
function search_controller_companies(array $request = [], int $user_id = 0): array { return search_controller_type('companies', $request, $user_id); }
function search_controller_trainings(array $request = [], int $user_id = 0): array { return search_controller_type('trainings', $request, $user_id); }
function search_controller_students(array $request = [], int $user_id = 0): array { return search_controller_type('students', $request, $user_id); }
function search_controller_certificates(array $request = [], int $user_id = 0): array { return search_controller_type('certificates', $request, $user_id); }
function search_controller_type(string $type, array $request, int $user_id): array { $request['type'] = $type; return search_controller_search($request, $user_id); }

function search_controller_trainings_filters(array $request = [], int $user_id = 0): array {
    /*
     * Training Filters API: training type + mode + price combined
     * with whitelisted sorting and pagination. No keyword parameter:
     * keyword searching belongs to /api/v1/search/trainings.
     */
    $allowed_types = ['shadowing', 'hands_on', 'project_based'];
    $allowed_modes = ['onsite', 'remote', 'hybrid'];
    $allowed_sorts = ['newest', 'oldest', 'price_asc', 'price_desc', 'duration_asc', 'duration_desc'];

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
        $result = search_service_trainings_filters([
            'training_type' => $training_type,
            'mode' => $mode,
            'paid' => $paid_raw === '' ? null : $paid_raw,
            'sort' => $sort === '' ? 'newest' : $sort,
            'page' => $page_raw === '' ? 1 : (int) $page_raw,
            'limit' => $limit_raw === '' ? 20 : (int) $limit_raw,
        ]);
        return search_controller_success($result, 'Training filters applied successfully.');
    } catch (Throwable $exception) {
        return search_controller_error('Unable to apply training filters.');
    }
}

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
