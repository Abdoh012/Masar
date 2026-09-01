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
            'certificates' => ['certificates', ['id', 'student_id', 'certificate_code', 'title'], ['certificate_code', 'title']],
        ];
        if ($type === 'trainings') return search_repository_trainings($query, $filters);
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

function search_repository_trainings_card_query(int $student_id = 0): string {
    /*
     * Shared SELECT for training search/filter results.
     *
     * Returns the same card structure as the training-listing
     * repository: all training_listings columns, company fields,
     * and is_saved resolved per authenticated student through the
     * saved_trainings relationship (LEFT JOIN + CASE WHEN), the same
     * pattern used by training_repository_get_public_list(). Guests
     * and users without a student profile keep is_saved = 0.
     * starts_at and ends_at are kept so the service layer can derive
     * duration and the frontend can display date information.
     */
    $student_id = max(0, (int) $student_id);

    $is_saved = $student_id > 0
        ? 'CASE WHEN st.id IS NULL THEN 0 ELSE 1 END'
        : '0';

    $saved_join = $student_id > 0
        ? "LEFT JOIN saved_trainings st
            ON st.training_id = t.id
            AND st.student_id = {$student_id}"
        : '';

    return "SELECT t.*,
            c.legal_name AS company_name,
            c.city AS company_city,
            c.company_logo AS company_logo,
            {$is_saved} AS is_saved
        FROM training_listings t
        LEFT JOIN companies c ON c.id = t.company_id
        {$saved_join}";
}

function search_repository_attach_training_relations(array $items): array {
    /* Attach skills, specializations and duration for the current page only. */
    $training_ids = array_map(static fn ($row) => (int) $row['id'], $items);
    if ($training_ids === []) return $items;
    $ids = implode(',', $training_ids);
    $skills = [];
    foreach (
        search_repository_fetch_all(
            "SELECT ts.training_id, s.id, s.name
            FROM training_skills ts
            JOIN skills s ON s.id = ts.skill_id
            WHERE ts.training_id IN ({$ids})
            ORDER BY s.name ASC",
            []
        ) as $row
    ) {
        $skills[$row['training_id']][] = ['id' => (int) $row['id'], 'name' => $row['name']];
    }
    $specializations = [];
    foreach (
        search_repository_fetch_all(
            "SELECT tsp.training_id, sp.id, sp.name
            FROM training_specializations tsp
            JOIN specializations sp ON sp.id = tsp.specialization_id
            WHERE tsp.training_id IN ({$ids})
            ORDER BY sp.name ASC",
            []
        ) as $row
    ) {
        $specializations[$row['training_id']][] = ['id' => (int) $row['id'], 'name' => $row['name']];
    }

    $today_start = @strtotime('today');

    foreach ($items as $index => $item) {
        $id = (int) $item['id'];
        $items[$index]['skills'] = array_values($skills[$id] ?? []);
        $items[$index]['specializations'] = array_values($specializations[$id] ?? []);

        $ends_at = $item['ends_at'] ?? null;

        if (
            !empty($ends_at)
            && $today_start !== false
            && @strtotime($ends_at) !== false
        ) {
            $remaining = @strtotime($ends_at) - $today_start;
            $items[$index]['duration'] = $remaining > 0
                ? (int) floor($remaining / 86400)
                : 0;
        } else {
            $items[$index]['duration'] = null;
        }
    }
    return $items;
}

function search_repository_student_specialization_ids(int $student_id): array {
    /*
     * Returns the specialization id(s) assigned to a student. In the
     * current schema a student holds a single specialization_id column
     * (there is no student_specializations pivot), so this returns at
     * most one id. The array shape keeps the OR-matching scope reusable
     * if a many-to-many relationship is introduced later.
     */
    if ($student_id <= 0) {
        return [];
    }

    $rows = search_repository_fetch_all(
        "SELECT s.specialization_id
        FROM students s
        WHERE s.id = :student_id
            AND s.specialization_id IS NOT NULL
        LIMIT 1",
        [':student_id' => $student_id]
    );

    $ids = [];
    foreach ($rows as $row) {
        $spec_id = (int) ($row['specialization_id'] ?? 0);
        if ($spec_id > 0) {
            $ids[$spec_id] = $spec_id;
        }
    }
    return array_values($ids);
}

function search_repository_specialization_scope(array $specialization_ids): string {
    /*
     * Builds an EXISTS fragment that restricts the current training row
     * (aliased t) to those whose training_specializations include at
     * least one of the given specialization ids (OR rule). EXISTS is used
     * instead of a JOIN so each training stays unique and counts/lists
     * remain correct. Every id is cast to int before being inlined, so no
     * user input reaches the SQL string.
     */
    $ids = [];
    foreach ($specialization_ids as $spec_id) {
        $spec_id = (int) $spec_id;
        if ($spec_id > 0) {
            $ids[$spec_id] = $spec_id;
        }
    }

    if (empty($ids)) {
        return '';
    }

    return " AND EXISTS (
        SELECT 1
        FROM training_specializations tsp
        WHERE tsp.training_id = t.id
            AND tsp.specialization_id IN ("
            . implode(', ', array_keys($ids))
            . ")
    )";
}

function search_repository_trainings(string $query, array $filters = []): array {
    /*
     * Dedicated training search over published listings.
     *
     * Matches title/description/location directly plus company name,
     * skills and specializations through their normalized relations
     * (companies, training_skills -> skills, training_specializations
     * -> specializations). EXISTS keeps one row per training so
     * pagination and counts stay correct.
     */
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $student_id = max(0, (int) ($filters['student_id'] ?? 0));

    $where = "(t.title LIKE :kw_title
        OR t.description LIKE :kw_description
        OR t.location LIKE :kw_location
        OR EXISTS (
            SELECT 1 FROM companies ksc
            WHERE ksc.id = t.company_id
                AND ksc.legal_name LIKE :kw_company
        )
        OR EXISTS (
            SELECT 1 FROM training_skills ksts
            JOIN skills kws ON kws.id = ksts.skill_id
            WHERE ksts.training_id = t.id
                AND kws.name LIKE :kw_skill
        )
        OR EXISTS (
            SELECT 1 FROM training_specializations kstsp
            JOIN specializations kws2 ON kws2.id = kstsp.specialization_id
            WHERE kstsp.training_id = t.id
                AND kws2.name LIKE :kw_specialization
        ))";

    $params = [
        ':kw_title' => '%' . $query . '%',
        ':kw_description' => '%' . $query . '%',
        ':kw_location' => '%' . $query . '%',
        ':kw_company' => '%' . $query . '%',
        ':kw_skill' => '%' . $query . '%',
        ':kw_specialization' => '%' . $query . '%',
    ];

    $scope = search_repository_specialization_scope(
        $filters['match_specialization_ids'] ?? []
    );

    $items = search_repository_fetch_all(
        search_repository_trainings_card_query($student_id) . "
        WHERE t.status = 'published' AND {$where}{$scope}
        ORDER BY t.created_at DESC, t.id DESC
        LIMIT {$limit} OFFSET {$offset}",
        $params
    );

    $count_params = $params;
    $total = (int) search_repository_fetch_value(
        "SELECT COUNT(*)
        FROM training_listings t
        WHERE t.status = 'published' AND {$where}{$scope}",
        $count_params
    );

    return ['items' => search_repository_attach_training_relations($items), 'total' => $total];
}

function search_repository_trainings_sort_clause(string $sort): string {
    /*
     * Whitelisted sort mapping for the training filters API.
     * Never interpolates user input into ORDER BY directly.
     * Duration is derived from starts_at/ends_at; NULLs last.
     */
    switch ($sort) {
        case 'oldest':
            return 'ORDER BY t.created_at ASC, t.id ASC';
        case 'price_asc':
            return 'ORDER BY COALESCE(t.compensation_amount, 0) ASC';
        case 'price_desc':
            return 'ORDER BY COALESCE(t.compensation_amount, 0) DESC';
        case 'duration_asc':
            return 'ORDER BY (t.ends_at IS NULL OR t.starts_at IS NULL) ASC, DATEDIFF(t.ends_at, t.starts_at) ASC';
        case 'duration_desc':
            return 'ORDER BY (t.ends_at IS NULL OR t.starts_at IS NULL) ASC, DATEDIFF(t.ends_at, t.starts_at) DESC';
        case 'newest':
        default:
            return 'ORDER BY t.created_at DESC, t.id DESC';
    }
}

function search_repository_trainings_filters(array $filters = []): array {
    /*
     * Filter-only listing over published trainings for the Training
     * Filters API: training type, mode and price combined with AND,
     * whitelisted sorting and standard pagination. No keyword here:
     * keyword searching lives in search_repository_trainings().
     */
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $student_id = max(0, (int) ($filters['student_id'] ?? 0));

    $conditions = ["t.status = 'published'"];
    $params = [];

    if (!empty($filters['training_type'])) {
        $conditions[] = 't.training_type = :f_training_type';
        $params[':f_training_type'] = (string) $filters['training_type'];
    }

    if (!empty($filters['mode'])) {
        $conditions[] = 't.mode = :f_mode';
        $params[':f_mode'] = (string) $filters['mode'];
    }

    if (isset($filters['paid']) && $filters['paid'] !== null && $filters['paid'] !== '') {
        $conditions[] = 't.is_paid = :f_paid';
        $params[':f_paid'] = (int) $filters['paid'];
    }

    $where = implode(' AND ', $conditions);

    $where .= search_repository_specialization_scope(
        $filters['match_specialization_ids'] ?? []
    );

    $items = search_repository_fetch_all(
        search_repository_trainings_card_query($student_id) . "
        WHERE {$where}
        " . search_repository_trainings_sort_clause((string) ($filters['sort'] ?? 'newest')) . "
        LIMIT {$limit} OFFSET {$offset}",
        $params
    );

    $total = (int) search_repository_fetch_value(
        "SELECT COUNT(*)
        FROM training_listings t
        WHERE {$where}",
        $params
    );

    return ['items' => search_repository_attach_training_relations($items), 'total' => $total];
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
