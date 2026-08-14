<?php

/**
 * MASAR Authentication Token Helper
 *
 * Handles creation, validation, revocation and cleanup
 * of authentication tokens.
 *
 * Tokens are stored hashed in the database.
 */

require_once __DIR__ . '/../database/query.php';
require_once __DIR__ . '/../http/request.php';
require_once __DIR__ . '/../../services/jwt_service.php';
require_once __DIR__ . '/auth.php';

function token_expiration_seconds(): int
{
    global $app_config;
    return (int) ( $app_config['auth']['token_expiration'] ?? 60 * 60 * 24 * 7 ); // Default: 7 days
}

function token_generate(): string
{
    return bin2hex( random_bytes(32) );
}

function token_hash(string $token): string
{
    return hash( 'sha256', $token );
}

function token_create( int $user_id, ?string $ip_address = null, ?string $user_agent = null, ?int $expires_in_seconds = null ): string {

    $plain_token = token_generate();
    $token_hash = token_hash( $plain_token );
    $expires_seconds = $expires_in_seconds ?? token_expiration_seconds();
    $expires_at = date( 'Y-m-d H:i:s', time() + $expires_seconds );
    db_execute(' INSERT INTO auth_tokens ( user_id, token_hash, expires_at, ip_address, user_agent, created_at ) VALUES ( :user_id, :token_hash, :expires_at, :ip_address, :user_agent, NOW() ) ',
        [ 'user_id' => $user_id, 'token_hash' => $token_hash, 'expires_at' => $expires_at, 'ip_address' => $ip_address, 'user_agent' => $user_agent ] );
    return $plain_token;
}

function token_find( string $plain_token ): ?array {
    $token_hash = token_hash( $plain_token );
    return db_fetch_one( 'SELECT id, user_id, token_hash, expires_at, revoked_at, ip_address, user_agent, created_at FROM auth_tokens WHERE token_hash = :token_hash LIMIT 1 ', 
        [ 'token_hash' => $token_hash ] );
}

function token_is_expired( array $token ): bool {

    if (empty($token['expires_at'])) {
        return true;
    }

    return strtotime( $token['expires_at'] ) <= time();
}

function token_is_revoked( array $token ): bool {
    return !empty( $token['revoked_at'] );
}

function token_is_valid( array $token ): bool {

    if (token_is_revoked($token)) {
        return false;
    }

    if (token_is_expired($token)) {
        return false;
    }

    return true;
}

function token_authenticate( string $plain_token ): bool {

    if (trim($plain_token) === '') {
        return false;
    }

    $token = token_find(
        $plain_token
    );

    if ($token === null) {
        return token_authenticate_jwt(
            $plain_token
        );
    }

    if (!token_is_valid($token)) {
        return false;
    }

    $user = auth_find_user_by_id(
        (int) $token['user_id']
    );

    if ($user === null) {
        return false;
    }

    if (!auth_user_is_active($user)) {
        return false;
    }

    auth_set_user($user);

    return true;
}

function token_authenticate_jwt( string $plain_token ): bool
{
    $payload = jwt_validate_access_token(
        $plain_token
    );

    if ($payload === false) {
        return false;
    }

    $user = auth_find_user_by_id(
        (int) ($payload['sub'] ?? 0)
    );

    if ($user === null) {
        return false;
    }

    if (!auth_user_is_active($user)) {
        return false;
    }

    auth_set_user($user);

    return true;
}

function token_cookie_name(): string
{
    global $app_config;

    return $app_config['auth']['cookie_name'] ?? 'MASAR_REMEMBER';
}

function token_authenticate_from_cookie(): bool
{
    $cookie_name = token_cookie_name();
    $token = request_cookie($cookie_name);

    if (!is_string($token) || trim($token) === '') {
        return false;
    }

    return token_authenticate(
        $token
    );
}

function token_authenticate_request(): bool
{
    $token = request_bearer_token();

    if ($token !== null) {
        return token_authenticate(
            $token
        );
    }

    return token_authenticate_from_cookie();
}


/*
|--------------------------------------------------------------------------
| Get Current Request Token
|--------------------------------------------------------------------------
*/

function token_current(): ?string
{
    $token = request_bearer_token();

    if ($token !== null) {
        return $token;
    }

    return request_cookie(token_cookie_name());
}


/*
|--------------------------------------------------------------------------
| Revoke Token
|--------------------------------------------------------------------------
*/

function token_revoke(
    string $plain_token
): bool {

    $token_hash = token_hash(
        $plain_token
    );

    $statement = db_execute(
        '
            UPDATE auth_tokens
            SET
                revoked_at = NOW()
            WHERE token_hash = :token_hash
              AND revoked_at IS NULL
        ',
        [
            'token_hash' => $token_hash
        ]
    );

    return $statement->rowCount() > 0;
}


/*
|--------------------------------------------------------------------------
| Revoke Current Token
|--------------------------------------------------------------------------
*/

function token_revoke_current(): bool
{
    $token = token_current();

    if ($token === null) {
        return false;
    }

    $result = token_revoke(
        $token
    );

    auth_logout();

    return $result;
}


/*
|--------------------------------------------------------------------------
| Revoke All User Tokens
|--------------------------------------------------------------------------
*/

function token_revoke_all_for_user(
    int $user_id
): int {

    $statement = db_execute(
        '
            UPDATE auth_tokens
            SET
                revoked_at = NOW()
            WHERE user_id = :user_id
              AND revoked_at IS NULL
        ',
        [
            'user_id' => $user_id
        ]
    );

    return $statement->rowCount();
}


/*
|--------------------------------------------------------------------------
| Delete Expired Tokens
|--------------------------------------------------------------------------
|
| This can later be executed by a Cron Job.
|
*/

function token_delete_expired(): int
{
    $statement = db_execute(
        '
            DELETE FROM auth_tokens
            WHERE expires_at <= NOW()
               OR revoked_at IS NOT NULL
        '
    );

    return $statement->rowCount();
}