<?php

/**
 * MASAR - Close Expired Trainings
 *
 * Closes published training listings whose training end date
 * (ends_at) has passed.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/database/connection.php';

function close_expired_trainings(PDO $pdo): int
{
    $sql = "
        UPDATE training_listings
        SET
            status = 'closed',
            closed_at = COALESCE(closed_at, CURRENT_TIMESTAMP),
            updated_at = CURRENT_TIMESTAMP
        WHERE
            status = 'published'
            AND ends_at IS NOT NULL
            AND ends_at < CURRENT_TIMESTAMP
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

        $closedCount = close_expired_trainings($pdo);

        $pdo->commit();

        echo sprintf(
            "[%s] Closed %d expired training listing(s).%s",
            date('Y-m-d H:i:s'),
            $closedCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to close expired trainings: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
