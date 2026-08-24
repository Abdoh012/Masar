<?php

/**
 * MASAR - Study Fields Seeder
 *
 * Seeds the study_fields lookup table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_study_fields(PDO $pdo): void
{
    $study_fields = [
        [
            'name' => 'Engineering',
        ],
        [
            'name' => 'Medicine',
        ],
        [
            'name' => 'Pharmacy',
        ],
        [
            'name' => 'Computer Science',
        ],
        [
            'name' => 'Business',
        ],
        [
            'name' => 'Law',
        ],
        [
            'name' => 'Media',
        ],
        [
            'name' => 'Design',
        ],
        [
            'name' => 'Accounting',
        ],
    ];

    $sql = "
        INSERT INTO study_fields (
            name,
            is_active
        )
        VALUES (
            :name,
            1
        )
        ON DUPLICATE KEY UPDATE
            is_active = 1
    ";

    $statement = $pdo->prepare($sql);

    foreach ($study_fields as $study_field) {
        $statement->execute([
            ':name' => $study_field['name'],
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| CLI Entry Point
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->beginTransaction();

        seed_study_fields($pdo);

        $pdo->commit();

        echo "Study fields seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Study fields seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}