<?php

/**
 * MASAR - Cleanup Expired Tokens
 *
 * Removes expired refresh tokens (JWT flow) and expired/revoked
 * legacy authentication tokens.
 *
 * NOTE: Refresh tokens are only removed once they have actually
 * expired. Revoked-but-unexpired rows are kept on purpose: the
 * refresh_tokens table also stores denylist entries for revoked
 * access tokens, and those must persist until the access token's
 * expiry so a revoked access token stays rejected.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/auth/token.php';

function cleanup_expired_tokens(): int
{
    $deleted_refresh = jwt_delete_expired_tokens();
    $deleted_legacy = token_delete_expired();

    return $deleted_refresh + $deleted_legacy;
}

/*
|--------------------------------------------------------------------------
| Cron Entry Point
|--------------------------------------------------------------------------
*/

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $deletedCount = cleanup_expired_tokens();

        echo sprintf(
            "[%s] Deleted %d expired token(s).%s",
            date('Y-m-d H:i:s'),
            $deletedCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to cleanup expired tokens: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
