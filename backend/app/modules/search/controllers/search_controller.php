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
