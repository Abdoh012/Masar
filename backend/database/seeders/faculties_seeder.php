<?php

/**
 * MASAR - Faculties Seeder
 *
 * Seeds faculties associated with the universities
 * already inserted by universities_seeder.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_faculties(PDO $pdo): void
{
    $faculties = [
        [
            'university_code' => 'CU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'CU',
            'name' => 'Faculty of Computers and Artificial Intelligence',
            'name_ar' => 'كلية الحاسبات والذكاء الاصطناعي',
            'code' => 'FCAI',
        ],
        [
            'university_code' => 'CU',
            'name' => 'Faculty of Commerce',
            'name_ar' => 'كلية التجارة',
            'code' => 'COM',
        ],
        [
            'university_code' => 'CU',
            'name' => 'Faculty of Science',
            'name_ar' => 'كلية العلوم',
            'code' => 'SCI',
        ],
        [
            'university_code' => 'CU',
            'name' => 'Faculty of Medicine',
            'name_ar' => 'كلية الطب',
            'code' => 'MED',
        ],
        [
            'university_code' => 'ASU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'ASU',
            'name' => 'Faculty of Computer and Information Sciences',
            'name_ar' => 'كلية الحاسبات والمعلومات',
            'code' => 'FCIS',
        ],
        [
            'university_code' => 'ASU',
            'name' => 'Faculty of Commerce',
            'name_ar' => 'كلية التجارة',
            'code' => 'COM',
        ],
        [
            'university_code' => 'ASU',
            'name' => 'Faculty of Science',
            'name_ar' => 'كلية العلوم',
            'code' => 'SCI',
        ],
        [
            'university_code' => 'AU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'AU',
            'name' => 'Faculty of Computers and Data Science',
            'name_ar' => 'كلية الحاسبات وعلوم البيانات',
            'code' => 'FCSD',
        ],
        [
            'university_code' => 'AU',
            'name' => 'Faculty of Commerce',
            'name_ar' => 'كلية التجارة',
            'code' => 'COM',
        ],
        [
            'university_code' => 'AU',
            'name' => 'Faculty of Science',
            'name_ar' => 'كلية العلوم',
            'code' => 'SCI',
        ],
        [
            'university_code' => 'MU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'MU',
            'name' => 'Faculty of Computers and Information',
            'name_ar' => 'كلية الحاسبات والمعلومات',
            'code' => 'FCI',
        ],
        [
            'university_code' => 'MU',
            'name' => 'Faculty of Commerce',
            'name_ar' => 'كلية التجارة',
            'code' => 'COM',
        ],
        [
            'university_code' => 'TU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'TU',
            'name' => 'Faculty of Computers and Information',
            'name_ar' => 'كلية الحاسبات والمعلومات',
            'code' => 'FCI',
        ],
        [
            'university_code' => 'HU',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'HU',
            'name' => 'Faculty of Computers and Artificial Intelligence',
            'name_ar' => 'كلية الحاسبات والذكاء الاصطناعي',
            'code' => 'FCAI',
        ],
        [
            'university_code' => 'HU',
            'name' => 'Faculty of Commerce and Business Administration',
            'name_ar' => 'كلية التجارة وإدارة الأعمال',
            'code' => 'CBA',
        ],
        [
            'university_code' => 'AUC',
            'name' => 'School of Business',
            'name_ar' => 'كلية إدارة الأعمال',
            'code' => 'BUS',
        ],
        [
            'university_code' => 'AUC',
            'name' => 'School of Sciences and Engineering',
            'name_ar' => 'كلية العلوم والهندسة',
            'code' => 'SSE',
        ],
        [
            'university_code' => 'GUC',
            'name' => 'Faculty of Information Engineering and Technology',
            'name_ar' => 'كلية هندسة وتكنولوجيا المعلومات',
            'code' => 'IET',
        ],
        [
            'university_code' => 'GUC',
            'name' => 'Faculty of Management Technology',
            'name_ar' => 'كلية تكنولوجيا الإدارة',
            'code' => 'MT',
        ],
        [
            'university_code' => 'BUE',
            'name' => 'Faculty of Informatics and Computer Science',
            'name_ar' => 'كلية المعلومات وعلوم الحاسب',
            'code' => 'ICS',
        ],
        [
            'university_code' => 'BUE',
            'name' => 'Faculty of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
        ],
        [
            'university_code' => 'FUE',
            'name' => 'Faculty of Computer and Information Technology',
            'name_ar' => 'كلية الحاسبات وتكنولوجيا المعلومات',
            'code' => 'CIT',
        ],
        [
            'university_code' => 'FUE',
            'name' => 'Faculty of Commerce and Business Administration',
            'name_ar' => 'كلية التجارة وإدارة الأعمال',
            'code' => 'CBA',
        ],
    ];

    $universityNames = [
        'CU' => 'Cairo University',
        'ASU' => 'Ain Shams University',
        'AU' => 'Alexandria University',
        'MU' => 'Mansoura University',
        'TU' => 'Tanta University',
        'HU' => 'Helwan University',
        'AUC' => 'The American University in Cairo',
        'GUC' => 'German University in Cairo',
        'BUE' => 'British University in Egypt',
        'FUE' => 'Future University in Egypt',
    ];

    $universityStatement = $pdo->prepare(
        "
        SELECT id
        FROM universities
        WHERE name = :name
        LIMIT 1
        "
    );

    $insertStatement = $pdo->prepare(
        "
        INSERT INTO faculties (
            university_id,
            name,
            is_active
        )
        VALUES (
            :university_id,
            :name,
            1
        )
        ON DUPLICATE KEY UPDATE
            is_active = 1
        "
    );

    foreach ($faculties as $faculty) {
        $universityStatement->execute([
            ':name' => $universityNames[$faculty['university_code']] ?? '',
        ]);

        $universityId =
            $universityStatement->fetchColumn();

        if ($universityId === false) {
            throw new RuntimeException(
                'University not found: ' .
                $faculty['university_code']
            );
        }

        $insertStatement->execute([
            ':university_id' => (int) $universityId,
            ':name' => $faculty['name'],
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

        seed_faculties($pdo);

        $pdo->commit();

        echo "Faculties seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Faculties seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
