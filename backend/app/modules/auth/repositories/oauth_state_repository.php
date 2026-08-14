<?php

/**
 * MASAR - OAuth State Repository
 *
 * Server-side, single-use, expiring storage for OAuth state values.
 *
 * Only the SHA-256 hash (nonce) of the randomly generated state is stored;
 * the raw state is never persisted. Callback validation consumes the state
 * atomically so a state value can be used at most once.
 */

require_once __DIR__ . '/../../../core/database/query.php';

function oauth_state_repository_nonce(string $state): string
{
    return hash('sha256', $state);
}

function oauth_state_repository_purge_expired(): void
{
    db_execute('DELETE FROM oauth_states WHERE expires_at < NOW()');
}

function oauth_state_repository_create(string $state, int $ttl_seconds = 600): bool
{
    oauth_state_repository_purge_expired();

    $statement = db_execute(
        "INSERT INTO oauth_states (nonce, expires_at)
         VALUES (:nonce, DATE_ADD(NOW(), INTERVAL :ttl_seconds SECOND))",
        [
            'nonce' => oauth_state_repository_nonce($state),
            'ttl_seconds' => max(60, $ttl_seconds),
        ]
    );

    return $statement->rowCount() === 1;
}

function oauth_state_repository_validate_and_consume(string $state): bool
{
    $nonce = oauth_state_repository_nonce($state);

    $row = db_fetch_one(
        "SELECT id, nonce, used_at, expires_at
         FROM oauth_states
         WHERE nonce = :nonce
         LIMIT 1",
        ['nonce' => $nonce]
    );

    if (!is_array($row) || empty($row['id'])) {
        return false;
    }

    if (!hash_equals($row['nonce'], $nonce)) {
        return false;
    }

    if (!empty($row['used_at'])) {
        return false;
    }

    // Expiry and the one-time consumption are enforced atomically in SQL using
    // the database clock, so the comparison is immune to PHP/MySQL timezone skew.
    $statement = db_execute(
        "UPDATE oauth_states
         SET used_at = NOW()
         WHERE id = :id
           AND used_at IS NULL
           AND expires_at > NOW()
         LIMIT 1",
        ['id' => (int) $row['id']]
    );

    return $statement->rowCount() === 1;
}