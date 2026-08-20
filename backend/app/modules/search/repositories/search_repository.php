<?php

require_once __DIR__ . '/../../../core/database/connection.php';

function search_repository_db(): PDO {
    return get_database_connection();
}

function search_repository_safe_identifier(string $value): bool {
    return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value);
}

function search_repository_fetch_all(string $sql, array $params = []): array {
    $statement = search_repository_db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function search_repository_fetch_value(string $sql, array $params = []): mixed {
    $statement = search_repository_db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchColumn();
}

function search_repository_search(string $query, array $filters = []): array {
    $type = strtolower(trim((string) ($filters['type'] ?? '')));
    if ($type !== '') {
        $definitions = [
            'users' => ['users', ['id', 'email', 'role'], ['email']],
            'students' => ['students', ['id', 'user_id', 'full_name'], ['full_name']],
            'companies' => ['companies', ['id', 'user_id', 'legal_name', 'description', 'city'], ['legal_name', 'description', 'city']],
            'trainings' => ['training_listings', ['id', 'company_id', 'title', 'description', 'location', 'field'], ['title', 'description', 'location', 'field']],
            'certificates' => ['certificates', ['id', 'student_id', 'certificate_code', 'title'], ['certificate_code', 'title']],
        ];
        if (!isset($definitions[$type])) return ['items' => [], 'total' => 0];
        return search_repository_entity($definitions[$type][0], $definitions[$type][1], $definitions[$type][2], $query, $filters);
    }

    $table = $filters['search_table'] ?? 'search_index';
    if (!search_repository_safe_identifier($table)) $table = 'search_index';
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));
    $params = [':query' => '%' . $query . '%'];
    $where = '(title LIKE :query OR description LIKE :query OR content LIKE :query)';
    if (($filters['status'] ?? '') !== '') { $where .= ' AND status = :status'; $params[':status'] = $filters['status']; }
    if (($filters['category'] ?? '') !== '') { $where .= ' AND category = :category'; $params[':category'] = $filters['category']; }
    $sql = "SELECT id, entity_type, entity_id, title, description, content FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit; $params[':offset'] = ($page - 1) * $limit;
    $items = search_repository_fetch_all($sql, $params);
    $count_params = array_filter($params, fn($key) => $key !== ':limit' && $key !== ':offset', ARRAY_FILTER_USE_KEY);
    return ['items' => $items, 'total' => (int) search_repository_fetch_value("SELECT COUNT(*) FROM {$table} WHERE {$where}", $count_params)];
}

function search_repository_entity(string $table, array $select, array $columns, string $query, array $filters): array {
    if (!search_repository_safe_identifier($table)) return ['items' => [], 'total' => 0];
    foreach (array_merge($select, $columns) as $column) if (!search_repository_safe_identifier($column)) return ['items' => [], 'total' => 0];
    $conditions = []; $params = [];
    foreach ($columns as $index => $column) { $name = ':search_' . $index; $conditions[] = "{$column} LIKE {$name}"; $params[$name] = '%' . $query . '%'; }
    $where = '(' . implode(' OR ', $conditions) . ')';
    if (($filters['status'] ?? '') !== '') { $where .= ' AND status = :status'; $params[':status'] = $filters['status']; }
    $page = max(1, (int) ($filters['page'] ?? 1)); $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));
    $params[':limit'] = $limit; $params[':offset'] = ($page - 1) * $limit;
    $items = search_repository_fetch_all('SELECT ' . implode(', ', $select) . " FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset", $params);
    $count_params = array_filter($params, fn($key) => $key !== ':limit' && $key !== ':offset', ARRAY_FILTER_USE_KEY);
    return ['items' => $items, 'total' => (int) search_repository_fetch_value("SELECT COUNT(*) FROM {$table} WHERE {$where}", $count_params)];
}

function search_repository_suggestions(string $query, array $options = []): array {
    $table = $options['search_table'] ?? 'search_index';
    if (!search_repository_safe_identifier($table)) return [];
    $limit = max(1, min(50, (int) ($options['limit'] ?? 10)));
    $rows = search_repository_fetch_all("SELECT entity_type, entity_id, title FROM {$table} WHERE title LIKE :query ORDER BY CASE WHEN title LIKE :start THEN 0 ELSE 1 END, title ASC LIMIT :limit", [':query' => '%' . $query . '%', ':start' => $query . '%', ':limit' => $limit]);
    return array_map(fn($row) => ['label' => $row['title'] ?? '', 'value' => $row['entity_id'] ?? '', 'type' => $row['entity_type'] ?? null], $rows);
}

function search_repository_recent(int $user_id, array $options = []): array {
    if ($user_id <= 0) return [];
    $table = $options['table'] ?? 'search_history';
    if (!search_repository_safe_identifier($table)) return [];
    $limit = max(1, min(50, (int) ($options['limit'] ?? 10)));
    return search_repository_fetch_all("SELECT id, query, created_at FROM {$table} WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT :limit", [':user_id' => $user_id, ':limit' => $limit]);
}

function search_repository_save_search(int $user_id, string $query, array $metadata = []): bool {
    if ($user_id <= 0 || trim($query) === '') return false;
    $table = $metadata['table'] ?? 'search_history';
    if (!search_repository_safe_identifier($table)) return false;
    $statement = search_repository_db()->prepare("INSERT INTO {$table} (user_id, query) VALUES (:user_id, :query)");
    return $statement->execute([':user_id' => $user_id, ':query' => trim($query)]);
}

function search_repository_clear_recent(int $user_id): bool {
    if ($user_id <= 0) return false;
    $statement = search_repository_db()->prepare('DELETE FROM search_history WHERE user_id = :user_id');
    return $statement->execute([':user_id' => $user_id]);
}
