<?php

/**
 * MASAR - API Route Catalog
 *
 * Read-only discovery of the HTTP routes declared in `routes/*.php` and the
 * system routes declared directly in `public/index.php`.
 *
 * The backend routing architecture is a set of prefix-dispatched files that
 * declare their own routes with `if ($path === '...' && $method === '...')`
 * and `preg_match('#^...#', $path)` guards. Because there is no central route
 * registry, this helper inspects those declarations statically (as text) and
 * never includes, requires or executes a route file. The route files therefore
 * remain the single source of truth.
 */

if (!function_exists('route_catalog_discover')) {

    /**
     * Discover every route declared in the backend.
     *
     * @return array{
     *     routes: array<int, array<string, mixed>>,
     *     groups: array<int, array<string, mixed>>,
     *     stats: array<string, int>,
     *     generated_at: string
     * }
     */
    function route_catalog_discover(): array
    {
        $backend_root = dirname(__DIR__, 3);
        $routes = [];

        $route_files = glob($backend_root . '/routes/*.php') ?: [];
        sort($route_files);

        foreach ($route_files as $route_file) {
            $contents = @file_get_contents($route_file);

            if (!is_string($contents)) {
                continue;
            }

            $routes = array_merge(
                $routes,
                route_catalog_parse_routes($contents, 'routes/' . basename($route_file), false)
            );
        }

        $entry_file = $backend_root . '/public/index.php';
        $entry_contents = @file_get_contents($entry_file);

        if (is_string($entry_contents)) {
            $routes = array_merge(
                $routes,
                route_catalog_parse_routes($entry_contents, 'public/index.php', true)
            );
        }

        $routes = route_catalog_deduplicate($routes);
        $routes = route_catalog_annotate($routes);
        usort($routes, 'route_catalog_compare');

        return route_catalog_build_catalog($routes);
    }

    /*
    |--------------------------------------------------------------------------
    | Parsing
    |--------------------------------------------------------------------------
    */

    function route_catalog_parse_routes(string $contents, string $source, bool $is_entry): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $contents) ?: [];
        $found = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if (!str_starts_with($trimmed, 'if')) {
                continue;
            }

            if (!preg_match('/^if\s*\((.*)\)\s*\{\s*$/', $trimmed, $match)) {
                continue;
            }

            $condition = $match[1];

            if (!str_contains($condition, '$path')) {
                continue;
            }

            $is_regex = str_contains($condition, 'preg_match');

            if (!$is_regex && !str_contains($condition, '$path ===')) {
                continue;
            }

            $methods = route_catalog_methods_from_condition($condition);

            if ($methods === []) {
                if ($is_entry) {
                    $next = route_catalog_next_statement($lines, $index);

                    // Skip prefix-dispatch blocks that only load a route file.
                    if ($next === null || preg_match('/^(require|require_once|include)/', $next)) {
                        continue;
                    }
                }

                $methods = ['GET'];
            }

            $access = route_catalog_access_from_body($lines, $index, $source);

            if ($is_regex) {
                $path = route_catalog_path_from_regex($condition);

                if ($path === null) {
                    continue;
                }

                foreach ($methods as $method) {
                    $found[] = [
                        'method' => $method,
                        'path' => $path,
                        'access' => $access,
                        'source' => $source,
                    ];
                }

                continue;
            }

            foreach (route_catalog_literal_paths($condition) as $path) {
                foreach ($methods as $method) {
                    $found[] = [
                        'method' => $method,
                        'path' => $path,
                        'access' => $access,
                        'source' => $source,
                    ];
                }
            }
        }

        return $found;
    }

    function route_catalog_methods_from_condition(string $condition): array
    {
        $allowed = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        preg_match_all("/'([A-Z]+)'/", $condition, $matches);

        $methods = [];

        foreach ($matches[1] ?? [] as $candidate) {
            if (in_array($candidate, $allowed, true)) {
                $methods[$candidate] = true;
            }
        }

        return array_keys($methods);
    }

    function route_catalog_literal_paths(string $condition): array
    {
        preg_match_all('/\$path\s*===\s*\'([^\']*)\'/', $condition, $matches);

        $paths = [];

        foreach ($matches[1] ?? [] as $path) {
            if ($path !== '') {
                $paths[$path] = true;
            }
        }

        return array_keys($paths);
    }

    function route_catalog_path_from_regex(string $condition): ?string
    {
        if (!preg_match("/preg_match\(\s*'#([^#]+)#'/", $condition, $match)) {
            return null;
        }

        $pattern = ltrim(rtrim($match[1], '$'), '^');

        // Numeric / identifier capture groups become a readable {id} token.
        $pattern = preg_replace('/\([^()]*(?:\+|\{)[^()]*\)/', '{id}', $pattern) ?? $pattern;

        if (!str_starts_with($pattern, '/')) {
            $pattern = '/' . $pattern;
        }

        return $pattern;
    }

    function route_catalog_next_statement(array $lines, int $index): ?string
    {
        $total = count($lines);

        for ($i = $index + 1; $i < $total; $i++) {
            $candidate = trim($lines[$i]);

            if ($candidate === '') {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    function route_catalog_access_from_body(array $lines, int $index, string $source = ''): ?string
    {
        $body_lines = [];
        $total = count($lines);

        for ($i = $index + 1; $i < $total; $i++) {
            $trimmed = trim($lines[$i]);

            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, 'response_not_found(')) {
                break;
            }

            if (preg_match('/^if\s*\(.*\$path.*\)/', $trimmed)) {
                break;
            }

            $body_lines[] = $lines[$i];
        }

        $body = implode("\n", $body_lines);

        $access = route_catalog_priority_from_signals(route_catalog_signals_from_text($body));

        if ($access !== null) {
            return $access;
        }

        // The whole admin route file is gated by middleware_admin() in the entry point.
        if ($source === 'routes/admin.php') {
            return 'Admin';
        }

        return null;
    }

    /**
     * Security signals that are safe to infer from a route declaration.
     *
     * `auth_user()` is intentionally excluded: it is used both to enforce access
     * and to optionally personalise otherwise-public responses, so it is not a
     * reliable signal on its own. Guards that only exist inside a controller or
     * service are not inferred either; those routes return a null access value
     * so the page can avoid asserting something it cannot verify.
     */
    function route_catalog_signals_from_text(string $text): array
    {
        $signals = [];

        if (str_contains($text, 'middleware_admin(') || str_contains($text, 'is_admin_role(')) {
            $signals['admin'] = true;
        }
        if (str_contains($text, 'middleware_company(') || str_contains($text, 'is_company_role(')) {
            $signals['company'] = true;
        }
        if (str_contains($text, 'middleware_student(') || str_contains($text, 'is_student_role(')) {
            $signals['student'] = true;
        }
        if (str_contains($text, 'middleware_jwt_auth(')) {
            $signals['jwt'] = true;
        }
        if (
            str_contains($text, 'middleware_auth(')
            || str_contains($text, 'auth_id(')
            || str_contains($text, 'current_user_id(')
        ) {
            $signals['auth'] = true;
        }

        return $signals;
    }

    function route_catalog_priority_from_signals(array $signals): ?string
    {
        $order = [
            'admin' => 'Admin',
            'company' => 'Company',
            'student' => 'Student',
            'jwt' => 'JWT',
            'auth' => 'Authenticated',
        ];

        foreach ($order as $key => $label) {
            if (!empty($signals[$key])) {
                return $label;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalization
    |--------------------------------------------------------------------------
    */

    function route_catalog_deduplicate(array $routes): array
    {
        $by_key = [];

        foreach ($routes as $route) {
            $key = $route['method'] . ' ' . $route['path'];

            if (!isset($by_key[$key])) {
                $by_key[$key] = $route;
                continue;
            }

            if (
                ($by_key[$key]['source'] ?? '') === 'public/index.php'
                && ($route['source'] ?? '') !== 'public/index.php'
            ) {
                $by_key[$key] = $route;
            }
        }

        return array_values($by_key);
    }

    function route_catalog_annotate(array $routes): array
    {
        foreach ($routes as $index => $route) {
            $group = route_catalog_group_for_path($route['path']);
            $routes[$index]['group'] = $group;
            $routes[$index]['group_label'] = route_catalog_group_label($group);
        }

        return $routes;
    }

    function route_catalog_group_for_path(string $path): string
    {
        if (str_starts_with($path, '/cron')) {
            return 'cron';
        }

        if (in_array($path, ['/health', '/api/v1', '/api/v1/health'], true)) {
            return 'system';
        }

        if (str_starts_with($path, '/api/v1/')) {
            $segments = explode('/', trim($path, '/'));
            $segment = $segments[2] ?? '';

            if ($segment === 'conversations' || $segment === 'messages') {
                return 'messaging';
            }

            if ($segment !== '') {
                return $segment;
            }
        }

        return 'system';
    }

    function route_catalog_group_labels(): array
    {
        return [
            'system' => 'System / Health',
            'auth' => 'Authentication',
            'users' => 'Users',
            'students' => 'Students',
            'companies' => 'Companies',
            'trainings' => 'Trainings',
            'applications' => 'Applications',
            'certificates' => 'Certificates',
            'messaging' => 'Messaging',
            'notifications' => 'Notifications',
            'files' => 'Files',
            'search' => 'Search',
            'lookups' => 'Lookups',
            'admin' => 'Admin',
            'cron' => 'Cron',
        ];
    }

    function route_catalog_group_label(string $group): string
    {
        $labels = route_catalog_group_labels();

        if (isset($labels[$group])) {
            return $labels[$group];
        }

        return ucwords(str_replace(['-', '_'], ' ', $group));
    }

    function route_catalog_group_order(): array
    {
        return [
            'system',
            'auth',
            'users',
            'students',
            'companies',
            'trainings',
            'applications',
            'certificates',
            'messaging',
            'notifications',
            'files',
            'search',
            'lookups',
            'admin',
            'cron',
        ];
    }

    function route_catalog_compare(array $a, array $b): int
    {
        $order = route_catalog_group_order();
        $position_a = array_search($a['group'], $order, true);
        $position_b = array_search($b['group'], $order, true);
        $position_a = $position_a === false ? 1000 : $position_a;
        $position_b = $position_b === false ? 1000 : $position_b;

        if ($position_a !== $position_b) {
            return $position_a <=> $position_b;
        }

        if ($a['group'] !== $b['group']) {
            return strcmp($a['group'], $b['group']);
        }

        if ($a['path'] !== $b['path']) {
            return strcmp($a['path'], $b['path']);
        }

        return route_catalog_method_weight($a['method']) <=> route_catalog_method_weight($b['method']);
    }

    function route_catalog_method_weight(string $method): int
    {
        $weights = ['GET' => 1, 'POST' => 2, 'PUT' => 3, 'PATCH' => 4, 'DELETE' => 5];

        return $weights[$method] ?? 9;
    }

    function route_catalog_build_catalog(array $routes): array
    {
        $groups = [];
        $stats = [
            'total' => 0,
            'get' => 0,
            'post' => 0,
            'write' => 0,
            'delete' => 0,
            'groups' => 0,
        ];

        foreach ($routes as $route) {
            $group = $route['group'];

            if (!isset($groups[$group])) {
                $groups[$group] = [
                    'key' => $group,
                    'label' => $route['group_label'],
                    'count' => 0,
                    'routes' => [],
                ];
            }

            $groups[$group]['routes'][] = $route;
            $groups[$group]['count']++;
            $stats['total']++;

            if ($route['method'] === 'GET') {
                $stats['get']++;
            } elseif ($route['method'] === 'POST') {
                $stats['post']++;
            } elseif ($route['method'] === 'PUT' || $route['method'] === 'PATCH') {
                $stats['write']++;
            } elseif ($route['method'] === 'DELETE') {
                $stats['delete']++;
            }
        }

        $stats['groups'] = count($groups);

        return [
            'routes' => $routes,
            'groups' => array_values($groups),
            'stats' => $stats,
            'generated_at' => date('c'),
        ];
    }
}
