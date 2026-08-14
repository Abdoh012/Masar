<?php

require_once __DIR__ . '/../core/database/query.php';

/**
 * MASAR JWT Service
 *
 * Lightweight HMAC-SHA256 JWT implementation for API authentication.
 *
 * Recommended upgrade path for production infrastructure:
 * - move to asymmetric RS256 keys
 * - rotate signing keys with key IDs
 * - separate access/refresh token issuance with strict TTLs
 */

function jwt_config(): array
{
    return [
        'secret' => getenv('JWT_SECRET') ?: 'change_this_to_a_long_random_secret',
        'algorithm' => strtoupper(trim(getenv('JWT_ALGORITHM') ?: 'HS256')),
        'access_ttl' => (int) (getenv('JWT_ACCESS_TTL') ?: 3600),
        'refresh_ttl' => (int) (getenv('JWT_REFRESH_TTL') ?: 2592000),
    ];
}

function jwt_base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwt_base64url_decode(string $data): string
{
    $padding = strlen($data) % 4;
    if ($padding > 0) {
        $data .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($data, '-_', '+/'), true) ?: '';
}

function jwt_sign(string $header_json, string $payload_json, string $secret, string $algorithm = 'HS256'): string
{
    $alg = strtoupper($algorithm);

    if (!in_array($alg, ['HS256', 'HS384', 'HS512'], true)) {
        throw new RuntimeException('Unsupported JWT signing algorithm.');
    }

    $data = jwt_base64url_encode($header_json) . '.' . jwt_base64url_encode($payload_json);
    $hash = hash_hmac(strtolower(str_replace('HS', 'sha', $alg)), $data, $secret, true);

    return $data . '.' . jwt_base64url_encode($hash);
}

function jwt_verify(string $token, string $secret, string $algorithm = 'HS256'): array|false
{
    $parts = explode('.', trim($token));

    if (count($parts) !== 3) {
        return false;
    }

    [$header_b64, $payload_b64, $signature_b64] = $parts;
    $expected_signature = jwt_base64url_encode(hash_hmac(
        strtolower(str_replace('HS', 'sha', strtoupper($algorithm))),
        $header_b64 . '.' . $payload_b64,
        $secret,
        true
    ));

    if (!hash_equals($expected_signature, $signature_b64)) {
        return false;
    }

    $header = json_decode(jwt_base64url_decode($header_b64), true);
    $payload = json_decode(jwt_base64url_decode($payload_b64), true);

    if (!is_array($header) || !is_array($payload)) {
        return false;
    }

    if (($header['alg'] ?? null) !== strtoupper($algorithm)) {
        return false;
    }

    if (($payload['exp'] ?? 0) < time()) {
        return false;
    }

    return $payload;
}

function jwt_issue_token(array $user, int $ttl, string $token_type = 'access'): string
{
    $config = jwt_config();
    $secret = $config['secret'];
    $now = time();
    $payload = [
        'sub' => (int) ($user['id'] ?? 0),
        'role' => $user['role'] ?? null,
        'type' => $token_type,
        'iat' => $now,
        'exp' => $now + max(1, $ttl),
        'jti' => bin2hex(random_bytes(16)),
    ];

    $header = [
        'alg' => $config['algorithm'],
        'typ' => 'JWT',
    ];

    return jwt_sign(
        json_encode($header, JSON_UNESCAPED_SLASHES),
        json_encode($payload, JSON_UNESCAPED_SLASHES),
        $secret,
        $config['algorithm']
    );
}

function jwt_issue_access_token(array $user): string
{
    $config = jwt_config();
    return jwt_issue_token($user, $config['access_ttl'], 'access');
}

function jwt_store_refresh_token(array $user, string $token, int $ttl_seconds): void
{
    $user_id = (int) ($user['id'] ?? 0);
    if ($user_id <= 0 || trim($token) === '') {
        return;
    }

    $hash = hash('sha256', $token);
    $expires_at = date('Y-m-d H:i:s', time() + max(1, $ttl_seconds));

    db_execute(
        'DELETE FROM refresh_tokens WHERE user_id = :user_id AND revoked_at IS NULL',
        ['user_id' => $user_id]
    );

    db_execute(
        'INSERT INTO refresh_tokens (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, :expires_at, NOW())',
        [
            'user_id' => $user_id,
            'token_hash' => $hash,
            'expires_at' => $expires_at,
        ]
    );
}

function jwt_issue_refresh_token(array $user): string
{
    $config = jwt_config();
    $token = jwt_issue_token($user, $config['refresh_ttl'], 'refresh');
    jwt_store_refresh_token($user, $token, $config['refresh_ttl']);

    return $token;
}

function jwt_revoke_all_refresh_tokens_for_user(int $user_id): int
{
    $statement = db_execute(
        'UPDATE refresh_tokens SET revoked_at = NOW() WHERE user_id = :user_id AND revoked_at IS NULL',
        ['user_id' => $user_id]
    );

    return $statement->rowCount();
}

function jwt_rotate_refresh_token(array $user, string $old_token): string
{
    $config = jwt_config();

    $old_hash = hash('sha256', $old_token);
    $existing = db_fetch_one(
        'SELECT id, user_id, revoked_at, expires_at FROM refresh_tokens WHERE token_hash = :token_hash LIMIT 1',
        ['token_hash' => $old_hash]
    );

    if (is_array($existing) && empty($existing['revoked_at'])) {
        db_execute(
            'UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = :token_hash AND revoked_at IS NULL',
            ['token_hash' => $old_hash]
        );
    }

    $new_token = jwt_issue_refresh_token($user);

    return $new_token;
}

function jwt_validate_access_token(string $token): array|false
{
    $config = jwt_config();
    $payload = jwt_verify($token, $config['secret'], $config['algorithm']);

    if ($payload === false) {
        return false;
    }

    if (($payload['type'] ?? null) !== 'access') {
        return false;
    }

    $hash = hash('sha256', $token);
    $record = db_fetch_one(
        'SELECT revoked_at FROM refresh_tokens WHERE token_hash = :token_hash LIMIT 1',
        ['token_hash' => $hash]
    );

    if (is_array($record) && !empty($record['revoked_at'])) {
        return false;
    }

    return $payload;
}

function jwt_revoke_access_token(string $access_token): void
{
    if (!is_string($access_token) || trim($access_token) === '') {
        return;
    }

    $config = jwt_config();
    $payload = jwt_verify($access_token, $config['secret'], $config['algorithm']);

    if ($payload === false || ($payload['type'] ?? null) !== 'access') {
        return;
    }

    $hash = hash('sha256', $access_token);
    $expires_at = date('Y-m-d H:i:s', (int) ($payload['exp'] ?? (time() + 3600)));

    db_execute(
        'INSERT INTO refresh_tokens (user_id, token_hash, expires_at, created_at, revoked_at)
         VALUES (:user_id, :token_hash, :expires_at, NOW(), NOW())
         ON DUPLICATE KEY UPDATE revoked_at = COALESCE(revoked_at, NOW())',
        [
            'user_id' => (int) ($payload['sub'] ?? 0),
            'token_hash' => $hash,
            'expires_at' => $expires_at,
        ]
    );
}

function jwt_validate_refresh_token(string $token): array|false
{
    $config = jwt_config();
    $payload = jwt_verify($token, $config['secret'], $config['algorithm']);

    if ($payload === false) {
        return false;
    }

    if (($payload['type'] ?? null) !== 'refresh') {
        return false;
    }

    $hash = hash('sha256', $token);
    $record = db_fetch_one(
        'SELECT id, revoked_at, expires_at FROM refresh_tokens WHERE token_hash = :token_hash LIMIT 1',
        ['token_hash' => $hash]
    );

    if (!is_array($record)) {
        return false;
    }

    if (!empty($record['revoked_at'])) {
        return false;
    }

    if (!empty($record['expires_at']) && strtotime((string) $record['expires_at']) <= time()) {
        return false;
    }

    return $payload;
}

function jwt_mark_refresh_token_reused(string $token): bool
{
    $hash = hash('sha256', $token);
    $statement = db_execute(
        'UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = :token_hash AND revoked_at IS NULL',
        ['token_hash' => $hash]
    );

    return $statement->rowCount() > 0;
}

function jwt_delete_expired_tokens(): int
{
    $statement = db_execute(
        'DELETE FROM refresh_tokens WHERE expires_at <= NOW()'
    );

    return $statement->rowCount();
}

function jwt_current_bearer_token(): ?string
{
    return request_bearer_token();
}

function jwt_require_user(): ?array
{
    $token = jwt_current_bearer_token();

    if (!is_string($token) || $token === '') {
        return null;
    }

    $payload = jwt_validate_access_token($token);

    if ($payload === false) {
        return null;
    }

    $user_id = (int) ($payload['sub'] ?? 0);
    if ($user_id <= 0) {
        return null;
    }

    $user = auth_find_user_by_id($user_id);
    if ($user === false || ($user['status'] ?? null) !== USER_STATUS_ACTIVE) {
        return null;
    }

    return $user;
}

function jwt_refresh_cookie_path(): string
{
    $base_path = rtrim((string) parse_url((string) getenv('APP_URL'), PHP_URL_PATH), '/');
    return $base_path . '/api/v1/auth';
}

function jwt_refresh_cookie_secure(): bool
{
    return filter_var(getenv('SECURE_COOKIES'), FILTER_VALIDATE_BOOLEAN);
}

function jwt_set_refresh_cookie(string $token, int $ttl_seconds = 2592000): void
{
    response_set_cookie(
        'refresh_token',
        $token,
        time() + $ttl_seconds,
        jwt_refresh_cookie_path(),
        '',
        jwt_refresh_cookie_secure(),
        true,
        'Lax'
    );
}

function jwt_clear_refresh_cookie(): void
{
    response_delete_cookie(
        'refresh_token',
        jwt_refresh_cookie_path(),
        '',
        jwt_refresh_cookie_secure(),
        true,
        'Lax'
    );
}

