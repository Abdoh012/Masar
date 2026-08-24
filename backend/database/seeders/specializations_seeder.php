<?php

/**
 * MASAR - Specializations Seeder
 *
 * Seeds the specializations lookup table with the FIELD -> SPECIALIZATION
 * relationship (specializations.field_id -> study_fields.id).
 *
 * The same specializations list is used by:
 * - Student registration (User Field -> Specialization).
 * - Company registration (Industry = Specialization, stored in
 *   company_specializations).
 *
 * Idempotent: existing rows are reused via the unique name key and never
 * duplicated.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

/*
|--------------------------------------------------------------------------
| Field -> Specializations Dataset
|--------------------------------------------------------------------------
*/

function seed_specializations_map(): array
{
    return [
        'Engineering' => [
            'Mechanical Engineering',
            'Civil Engineering',
            'Electrical Engineering',
            'Architecture',
        ],
        'Medicine' => [
            'General Medicine',
            'Surgery',
            'Pediatrics',
            'Cardiology',
        ],
        'Pharmacy' => [
            'Clinical Pharmacy',
            'Pharmaceutical Industry',
            'Pharmacology',
        ],
        'Computer Science' => [
            'Software Engineering',
            'Artificial Intelligence',
            'Data Science',
            'Cyber Security',
            'Web Development',
        ],
        'Business' => [
            'Marketing',
            'Human Resources',
            'Business Administration',
            'Sales',
        ],
        'Law' => [
            'Corporate Law',
            'Criminal Law',
            'Commercial Law',
        ],
        'Media' => [
            'Journalism',
            'Digital Media',
            'Broadcasting',
            'Digital Marketing',
        ],
        'Design' => [
            'UI/UX Design',
            'Product Design',
            'Graphic Design',
        ],
        'Accounting' => [
            'Financial Accounting',
            'Management Accounting',
            'Auditing',
        ],
    ];
}

function seed_specializations(PDO $pdo): void
{
    // Make sure every referenced study field exists (reuses rows).
    $field_sql = "
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

    $field_statement = $pdo->prepare($field_sql);

    $find_field_sql = "
        SELECT id
        FROM study_fields
        WHERE name = :name
        LIMIT 1
    ";

    $find_field_statement = $pdo->prepare($find_field_sql);

    $specialization_sql = "
        INSERT INTO specializations (
            name,
            field_id,
            is_active
        )
        VALUES (
            :name,
            :field_id,
            1
        )
        ON DUPLICATE KEY UPDATE
            is_active = 1,
            field_id = VALUES(field_id)
    ";

    $specialization_statement = $pdo->prepare($specialization_sql);

    foreach (seed_specializations_map() as $field_name => $specializations) {
        $field_statement->execute([':name' => $field_name]);

        $find_field_statement->execute([':name' => $field_name]);
        $field_id = $find_field_statement->fetchColumn();

        if ($field_id === false) {
            throw new RuntimeException(
                "Failed to resolve study field '{$field_name}'."
            );
        }

        foreach ($specializations as $specialization_name) {
            $specialization_statement->execute([
                ':name' => $specialization_name,
                ':field_id' => (int) $field_id,
            ]);
        }
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

        seed_specializations($pdo);

        $pdo->commit();

        echo "Specializations seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Specializations seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
