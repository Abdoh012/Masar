<?php

/**
 * MASAR - Expire Trial Periods
 *
 * Stops training sessions still in the trial period whose trial
 * period has expired, according to the session lifecycle.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/database/connection.php';

function expire_trial_periods(PDO $pdo): int
{
    $sql = "
        UPDATE training_sessions
        SET
            status = 'stopped',
            actual_ended_at = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP
        WHERE
            status = 'trial'
            AND trial_ends_at IS NOT NULL
            AND trial_ends_at < CURRENT_TIMESTAMP
    ";

    $statement = $pdo->prepare($sql);
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
        $pdo = get_database_connection();
        $pdo->beginTransaction();

        $expiredCount = expire_trial_periods($pdo);

        $pdo->commit();

        echo sprintf(
            "[%s] Expired %d trial period(s).%s",
            date('Y-m-d H:i:s'),
            $expiredCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to expire trial periods: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
