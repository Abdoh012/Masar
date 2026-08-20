<?php

/**
 * MASAR Security Helpers
 *
 * Centralized protection for high-risk auth flows.
 */

function security_rate_limit_directory(): string
{
    $dir = __DIR__ . '/../../../storage/cache/security';

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

function security_rate_limit_file(string $action, string $identifier): string
{
    $safe_action = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower(trim($action))) ?: 'request';
    $safe_identifier = preg_replace('/[^a-z0-9._:-]+/i', '-', strtolower(trim($identifier))) ?: 'unknown';

    return security_rate_limit_directory() . DIRECTORY_SEPARATOR . $safe_action . '-' . md5($safe_identifier) . '.json';
}

function security_check_rate_limit(
    string $action,
    string $identifier,
    int $max_requests = 5,
    int $window_seconds = 900
): array {
    $environment = strtolower(trim((string) (getenv('APP_ENV') ?: 'production')));
    $rate_limit_enabled = filter_var(getenv('RATE_LIMIT_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN);

    if (!$rate_limit_enabled && $environment !== 'production') {
        return [
            'allowed' => true,
            'count' => 0,
            'disabled_for_local_testing' => true,
        ];
    }

    $now = time();
    $file = security_rate_limit_file($action, $identifier);
    $state = [
        'count' => 0,
        'window_started' => $now,
        'expires_at' => $now + $window_seconds,
    ];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $state = $decoded;
            }
        }
    }

    if (($state['expires_at'] ?? $now) <= $now) {
        $state = [
            'count' => 0,
            'window_started' => $now,
            'expires_at' => $now + $window_seconds,
        ];
    }

    $current_count = (int) ($state['count'] ?? 0);

    if ($current_count >= $max_requests) {
        $retry_after = max(1, (int) (($state['expires_at'] ?? $now) - $now));

        @file_put_contents($file, json_encode([
            'count' => $current_count,
            'window_started' => $state['window_started'] ?? $now,
            'expires_at' => $state['expires_at'] ?? ($now + $window_seconds),
            'last_attempt_at' => $now,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        return [
            'allowed' => false,
            'message' => 'Too many requests. Please try again in ' . $retry_after . ' seconds.',
            'retry_after' => $retry_after,
        ];
    }

    $state['count'] = $current_count + 1;
    $state['window_started'] = $state['window_started'] ?? $now;
    $state['expires_at'] = $now + $window_seconds;
    $state['last_attempt_at'] = $now;

    @file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

    return [
        'allowed' => true,
        'count' => $state['count'],
    ];
}

function security_rate_limit_tier_defaults(): array
{
    return [
        'global' => [
            'max_requests' => 200,
            'window_seconds' => 60,
        ],
        'ip' => [
            'max_requests' => 30,
            'window_seconds' => 60,
        ],
        'user' => [
            'max_requests' => 20,
            'window_seconds' => 300,
        ],
        'endpoint' => [
            'max_requests' => 50,
            'window_seconds' => 60,
        ],
        'sensitive' => [
            'max_requests' => 5,
            'window_seconds' => 900,
        ],
    ];
}

function security_check_rate_limit_tier(
    string $tier,
    string $action,
    string $identifier,
    int $max_requests = 5,
    int $window_seconds = 900,
    int $delay_seconds = 0
): array {
    $config = security_rate_limit_tier_defaults();
    $tier_config = $config[strtolower(trim($tier))] ?? [];

    $effective_max = $max_requests > 0 ? $max_requests : (int) ($tier_config['max_requests'] ?? 5);
    $effective_window = $window_seconds > 0 ? $window_seconds : (int) ($tier_config['window_seconds'] ?? 900);

    $bucket_name = strtolower(trim($tier)) !== '' ? strtolower(trim($tier)) : 'endpoint';
    $bucket_identifier = trim($identifier) !== '' ? trim($identifier) : 'unknown';
    $bucket_key = $bucket_name . ':' . preg_replace('/[^a-z0-9._:-]+/i', '-', strtolower($action)) . ':' . $bucket_identifier;

    $result = security_check_rate_limit($bucket_key, $bucket_identifier, $effective_max, $effective_window);

    if (!$result['allowed']) {
        if ($delay_seconds > 0) {
            $result['retry_after'] = max((int) ($result['retry_after'] ?? $delay_seconds), $delay_seconds);
        }

        return $result;
    }

    return $result;
}

function security_sensitive_admin_actions(): array
{
    return [
        'user_status_change',
        'user_delete',
        'company_approval',
        'company_status_change',
        'company_delete',
        'training_delete',
        'certificate_approval',
        'admin_sensitive_action',
    ];
}

function security_require_admin_reauth(?array $user, array $context = [], string $action = 'admin_sensitive_action'): void
{
    $verified = !empty($context['reauth_verified']) || !empty($context['re_authenticated']) || !empty($context['admin_reauth_verified']);
    if ($verified) {
        return;
    }

    $user_id = (int) ($user['id'] ?? $context['user_id'] ?? $context['admin_id'] ?? 0);
    if ($user_id <= 0) {
        if (function_exists('response_forbidden')) {
            response_forbidden('Sensitive admin action requires re-authentication.');
        }
        throw new RuntimeException('Sensitive admin action requires re-authentication.');
    }

    $password = trim((string) ($context['password'] ?? $context['current_password'] ?? $context['reauth_password'] ?? ''));
    if ($password === '') {
        if (function_exists('response_forbidden')) {
            response_forbidden('Sensitive admin action requires a password confirmation.');
        }
        throw new RuntimeException('Sensitive admin action requires a password confirmation.');
    }

    if (!function_exists('auth_find_user_by_id')) {
        return;
    }

    $account = auth_find_user_by_id($user_id);
    if (!is_array($account) || empty($account['password_hash'])) {
        if (function_exists('response_forbidden')) {
            response_forbidden('Sensitive admin action requires a valid session.');
        }
        throw new RuntimeException('Sensitive admin action requires a valid session.');
    }

    if (!function_exists('password_verify')) {
        return;
    }

    if (!password_verify($password, $account['password_hash'])) {
        if (function_exists('response_forbidden')) {
            response_forbidden('Re-authentication failed.');
        }
        throw new RuntimeException('Re-authentication failed.');
    }

    if (function_exists('audit_log_user_action')) {
        audit_log_user_action('admin_reauth_success', 'user', $user_id, [], ['action' => $action]);
    }
}

function security_password_strength_errors(string $password): array
{
    $errors = [];

    if ($password === '') {
        $errors[] = 'Password is required.';
        return $errors;
    }

    if (strlen($password) < 12) {
        $errors[] = 'Password must be at least 12 characters long.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    return $errors;
}

function security_apply_http_headers(): void
{
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (!empty($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

function security_log_event(string $event, array $context = []): void
{
    if (function_exists('logger_security')) {
        logger_security($event, $context);
    }
}
