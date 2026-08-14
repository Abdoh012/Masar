<?php

/**
 * MASAR - Password Reset Repository
 *
 * Responsible for password reset token persistence.
 */

require_once __DIR__ . '/../../../core/database/query.php';

function password_reset_repository_revoke_previous(int $user_id): bool {
    $sql = "UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL";
    $statement = db_execute($sql, ['user_id' => $user_id]);
    return $statement->rowCount() > 0;
}

function password_reset_repository_create(int $user_id, string $token, int $expires_in_minutes): bool {
    password_reset_repository_revoke_previous($user_id);

    $token_hash = hash('sha256', $token);

    $sql = "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL :expires_in_minutes MINUTE), NOW())";
    $statement = db_execute($sql, [
        'user_id' => $user_id,
        'token_hash' => $token_hash,
        'expires_in_minutes' => $expires_in_minutes,
    ]);

    return $statement->rowCount() > 0;
}

function password_reset_repository_find_valid(int $user_id, string $token): array|false {
    $token_hash = hash('sha256', $token);

    $sql = "SELECT id, user_id, token_hash, expires_at, used_at, created_at FROM password_resets
            WHERE user_id = :user_id
              AND token_hash = :token_hash
              AND used_at IS NULL
              AND expires_at >= NOW()
            LIMIT 1";

    return db_fetch_one($sql, [
        'user_id' => $user_id,
        'token_hash' => $token_hash,
    ]);
}

function password_reset_repository_mark_used(int $reset_id): bool {
    $sql = "UPDATE password_resets SET used_at = NOW() WHERE id = :id AND used_at IS NULL";
    $statement = db_execute($sql, ['id' => $reset_id]);
    return $statement->rowCount() > 0;
}
