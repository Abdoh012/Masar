<?php

/**
 * MASAR - Cleanup Temporary Files
 *
 * Removes temporary uploaded files that have exceeded
 * the configured retention period.
 */

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/

$tempDirectory = __DIR__ . '/../storage/uploads/temp';

$retentionHours = 24;

/*
|--------------------------------------------------------------------------
| Cleanup Function
|--------------------------------------------------------------------------
*/

function cleanup_temp_files(
    string $directory,
    int $retentionHours = 24
): int {
    if (!is_dir($directory)) {
        throw new RuntimeException(
            'Temporary upload directory not found: ' . $directory
        );
    }

    $cutoffTime = time() - ($retentionHours * 60 * 60);

    $deletedCount = 0;

    $items = scandir($directory);

    if ($items === false) {
        throw new RuntimeException(
            'Unable to read temporary upload directory.'
        );
    }

    foreach ($items as $item) {
        /*
        |--------------------------------------------------------------------------
        | Ignore Special Entries
        |--------------------------------------------------------------------------
        */

        if ($item === '.' || $item === '..') {
            continue;
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $item;

        /*
        |--------------------------------------------------------------------------
        | Only Remove Files
        |--------------------------------------------------------------------------
        */

        if (!is_file($filePath)) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | File Modification Time
        |--------------------------------------------------------------------------
        */

        $modifiedAt = filemtime($filePath);

        if ($modifiedAt === false) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Expired File
        |--------------------------------------------------------------------------
        */

        if ($modifiedAt < $cutoffTime) {
            if (unlink($filePath)) {
                $deletedCount++;
            }
        }
    }

    return $deletedCount;
}

/*
|--------------------------------------------------------------------------
| Cron Entry Point
|--------------------------------------------------------------------------
*/

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $deletedCount = cleanup_temp_files(
            $tempDirectory,
            $retentionHours
        );

        echo sprintf(
            "[%s] Deleted %d temporary file(s).%s",
            date('Y-m-d H:i:s'),
            $deletedCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to cleanup temporary files: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
