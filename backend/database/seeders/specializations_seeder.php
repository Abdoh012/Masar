<?php

/**
 * MASAR - Specializations Seeder
 *
 * Seeds the specializations lookup table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_specializations(PDO $pdo): void
{
    $specializations = [
        [
            'name' => 'Computer Science',
            'name_ar' => 'علوم الحاسب',
            'code' => 'CS',
        ],
        [
            'name' => 'Information Technology',
            'name_ar' => 'تكنولوجيا المعلومات',
            'code' => 'IT',
        ],
        [
            'name' => 'Information Systems',
            'name_ar' => 'نظم المعلومات',
            'code' => 'IS',
        ],
        [
            'name' => 'Artificial Intelligence',
            'name_ar' => 'الذكاء الاصطناعي',
            'code' => 'AI',
        ],
        [
            'name' => 'Data Science',
            'name_ar' => 'علوم البيانات',
            'code' => 'DS',
        ],
        [
            'name' => 'Cybersecurity',
            'name_ar' => 'الأمن السيبراني',
            'code' => 'CYBER',
        ],
        [
            'name' => 'Software Engineering',
            'name_ar' => 'هندسة البرمجيات',
            'code' => 'SE',
        ],
        [
            'name' => 'Computer Engineering',
            'name_ar' => 'هندسة الحاسبات',
            'code' => 'CE',
        ],
        [
            'name' => 'Electronics and Communications Engineering',
            'name_ar' => 'هندسة الإلكترونيات والاتصالات',
            'code' => 'ECE',
        ],
        [
            'name' => 'Electrical Engineering',
            'name_ar' => 'الهندسة الكهربائية',
            'code' => 'EE',
        ],
        [
            'name' => 'Mechanical Engineering',
            'name_ar' => 'الهندسة الميكانيكية',
            'code' => 'ME',
        ],
        [
            'name' => 'Civil Engineering',
            'name_ar' => 'الهندسة المدنية',
            'code' => 'CEV',
        ],
        [
            'name' => 'Architecture',
            'name_ar' => 'العمارة',
            'code' => 'ARCH',
        ],
        [
            'name' => 'Business Administration',
            'name_ar' => 'إدارة الأعمال',
            'code' => 'BA',
        ],
        [
            'name' => 'Accounting',
            'name_ar' => 'المحاسبة',
            'code' => 'ACC',
        ],
        [
            'name' => 'Finance',
            'name_ar' => 'التمويل',
            'code' => 'FIN',
        ],
        [
            'name' => 'Marketing',
            'name_ar' => 'التسويق',
            'code' => 'MKT',
        ],
        [
            'name' => 'Human Resources',
            'name_ar' => 'الموارد البشرية',
            'code' => 'HR',
        ],
        [
            'name' => 'Management Information Systems',
            'name_ar' => 'نظم معلومات إدارية',
            'code' => 'MIS',
        ],
        [
            'name' => 'Economics',
            'name_ar' => 'الاقتصاد',
            'code' => 'ECO',
        ],
        [
            'name' => 'Medicine',
            'name_ar' => 'الطب',
            'code' => 'MED',
        ],
        [
            'name' => 'Pharmacy',
            'name_ar' => 'الصيدلة',
            'code' => 'PHARM',
        ],
        [
            'name' => 'Nursing',
            'name_ar' => 'التمريض',
            'code' => 'NURS',
        ],
        [
            'name' => 'Law',
            'name_ar' => 'القانون',
            'code' => 'LAW',
        ],
        [
            'name' => 'Mass Communication',
            'name_ar' => 'الإعلام',
            'code' => 'MEDIA',
        ],
        [
            'name' => 'Graphic Design',
            'name_ar' => 'التصميم الجرافيكي',
            'code' => 'GD',
        ],
        [
            'name' => 'Digital Marketing',
            'name_ar' => 'التسويق الرقمي',
            'code' => 'DM',
        ],
        [
            'name' => 'Content Creation',
            'name_ar' => 'صناعة المحتوى',
            'code' => 'CONTENT',
        ],
        [
            'name' => 'Languages and Translation',
            'name_ar' => 'اللغات والترجمة',
            'code' => 'LANG',
        ],
        [
            'name' => 'English Language',
            'name_ar' => 'اللغة الإنجليزية',
            'code' => 'ENG',
        ],
        [
            'name' => 'Mathematics',
            'name_ar' => 'الرياضيات',
            'code' => 'MATH',
        ],
        [
            'name' => 'Physics',
            'name_ar' => 'الفيزياء',
            'code' => 'PHY',
        ],
        [
            'name' => 'Chemistry',
            'name_ar' => 'الكيمياء',
            'code' => 'CHEM',
        ],
        [
            'name' => 'Biology',
            'name_ar' => 'الأحياء',
            'code' => 'BIO',
        ],
    ];

    $sql = "
        INSERT INTO specializations (
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

    foreach ($specializations as $specialization) {
        $statement->execute([
            ':name' => $specialization['name'],
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
