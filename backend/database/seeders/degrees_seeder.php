<?php

/**
 * MASAR - Degrees Seeder
 *
 * Seeds the degrees lookup table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_degrees(PDO $pdo): void
{
    $degrees = [
        [
            'name' => 'Bachelor of Science',
            'name_ar' => 'بكالوريوس العلوم',
            'code' => 'BSC',
        ],
        [
            'name' => 'Bachelor of Engineering',
            'name_ar' => 'بكالوريوس الهندسة',
            'code' => 'BENG',
        ],
        [
            'name' => 'Bachelor of Arts',
            'name_ar' => 'بكالوريوس الآداب',
            'code' => 'BA',
        ],
        [
            'name' => 'Bachelor of Commerce',
            'name_ar' => 'بكالوريوس التجارة',
            'code' => 'BCOM',
        ],
        [
            'name' => 'Bachelor of Business Administration',
            'name_ar' => 'بكالوريوس إدارة الأعمال',
            'code' => 'BBA',
        ],
        [
            'name' => 'Bachelor of Computer Science',
            'name_ar' => 'بكالوريوس علوم الحاسب',
            'code' => 'BCS',
        ],
        [
            'name' => 'Bachelor of Information Technology',
            'name_ar' => 'بكالوريوس تكنولوجيا المعلومات',
            'code' => 'BIT',
        ],
        [
            'name' => 'Bachelor of Computer and Information Sciences',
            'name_ar' => 'بكالوريوس الحاسبات والمعلومات',
            'code' => 'BCIS',
        ],
        [
            'name' => 'Bachelor of Medicine and Surgery',
            'name_ar' => 'بكالوريوس الطب والجراحة',
            'code' => 'MBBS',
        ],
        [
            'name' => 'Bachelor of Pharmacy',
            'name_ar' => 'بكالوريوس الصيدلة',
            'code' => 'BPHARM',
        ],
        [
            'name' => 'Bachelor of Science in Nursing',
            'name_ar' => 'بكالوريوس التمريض',
            'code' => 'BSN',
        ],
        [
            'name' => 'Bachelor of Laws',
            'name_ar' => 'ليسانس الحقوق',
            'code' => 'LLB',
        ],
        [
            'name' => 'Bachelor of Fine Arts',
            'name_ar' => 'بكالوريوس الفنون الجميلة',
            'code' => 'BFA',
        ],
        [
            'name' => 'Bachelor of Architecture',
            'name_ar' => 'بكالوريوس العمارة',
            'code' => 'BARCH',
        ],
        [
            'name' => 'Bachelor of Education',
            'name_ar' => 'بكالوريوس التربية',
            'code' => 'BED',
        ],
        [
            'name' => 'Master of Science',
            'name_ar' => 'ماجستير العلوم',
            'code' => 'MSC',
        ],
        [
            'name' => 'Master of Engineering',
            'name_ar' => 'ماجستير الهندسة',
            'code' => 'MENG',
        ],
        [
            'name' => 'Master of Business Administration',
            'name_ar' => 'ماجستير إدارة الأعمال',
            'code' => 'MBA',
        ],
        [
            'name' => 'Master of Computer Science',
            'name_ar' => 'ماجستير علوم الحاسب',
            'code' => 'MCS',
        ],
        [
            'name' => 'Doctor of Philosophy',
            'name_ar' => 'دكتوراه الفلسفة',
            'code' => 'PHD',
        ],
    ];

    $sql = "
        INSERT INTO degrees (
            name
        )
        VALUES (
            :name
        )
        ON DUPLICATE KEY UPDATE
            is_active = 1
    ";

    $statement = $pdo->prepare($sql);

    foreach ($degrees as $degree) {
        $statement->execute([
            ':name' => $degree['name'],
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

        seed_degrees($pdo);

        $pdo->commit();

        echo "Degrees seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Degrees seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
