<?php

/**
 * MASAR - Cleanup Audit Logs
 *
 * Deletes ALL records from the audit_logs table.
 * This is a full daily purge — no retention period.
 * New audit logs continue being created normally during the day
 * and are cleaned up on the next scheduled run.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/database/connection.php';

function cleanup_audit_logs(): int
{
    $pdo = get_database_connection();

    $statement = $pdo->prepare('DELETE FROM audit_logs');
    $statement->execute();

    return $statement->rowCount();
}

/*
|--------------------------------------------------------------------------
| Cron Entry Point
|--------------------------------------------------------------------------
*/

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $deletedCount = cleanup_audit_logs();

        echo sprintf(
            "[%s] Deleted %d audit log record(s).%s",
            date('Y-m-d H:i:s'),
            $deletedCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to cleanup audit logs: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
