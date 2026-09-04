<?php

/**
 * MASAR - Universities Seeder
 *
 * Seeds the universities lookup table with common Egyptian universities.
 *
 * Intended for development / initial data setup.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_universities(PDO $pdo): void
{
    $universities = [
        [
            'name' => 'Cairo University',
            'name_ar' => 'جامعة القاهرة',
            'code' => 'CU',
        ],
        [
            'name' => 'Ain Shams University',
            'name_ar' => 'جامعة عين شمس',
            'code' => 'ASU',
        ],
        [
            'name' => 'Alexandria University',
            'name_ar' => 'جامعة الإسكندرية',
            'code' => 'AU',
        ],
        [
            'name' => 'Mansoura University',
            'name_ar' => 'جامعة المنصورة',
            'code' => 'MU',
        ],
        [
            'name' => 'Assiut University',
            'name_ar' => 'جامعة أسيوط',
            'code' => 'AUN',
        ],
        [
            'name' => 'Tanta University',
            'name_ar' => 'جامعة طنطا',
            'code' => 'TU',
        ],
        [
            'name' => 'Zagazig University',
            'name_ar' => 'جامعة الزقازيق',
            'code' => 'ZUA',
        ],
        [
            'name' => 'Suez Canal University',
            'name_ar' => 'جامعة قناة السويس',
            'code' => 'SCU',
        ],
        [
            'name' => 'Helwan University',
            'name_ar' => 'جامعة حلوان',
            'code' => 'HU',
        ],
        [
            'name' => 'Fayoum University',
            'name_ar' => 'جامعة الفيوم',
            'code' => 'FYM',
        ],
        [
            'name' => 'Minia University',
            'name_ar' => 'جامعة المنيا',
            'code' => 'MINIA',
        ],
        [
            'name' => 'Sohag University',
            'name_ar' => 'جامعة سوهاج',
            'code' => 'SOHAG',
        ],
        [
            'name' => 'Benha University',
            'name_ar' => 'جامعة بنها',
            'code' => 'BU',
        ],
        [
            'name' => 'Kafr El Sheikh University',
            'name_ar' => 'جامعة كفر الشيخ',
            'code' => 'KSU',
        ],
        [
            'name' => 'Port Said University',
            'name_ar' => 'جامعة بورسعيد',
            'code' => 'PSU',
        ],
        [
            'name' => 'Damietta University',
            'name_ar' => 'جامعة دمياط',
            'code' => 'DU',
        ],
        [
            'name' => 'Damanhour University',
            'name_ar' => 'جامعة دمنهور',
            'code' => 'Damanhour',
        ],
        [
            'name' => 'Aswan University',
            'name_ar' => 'جامعة أسوان',
            'code' => 'ASWU',
        ],
        [
            'name' => 'Luxor University',
            'name_ar' => 'جامعة الأقصر',
            'code' => 'LU',
        ],
        [
            'name' => 'New Valley University',
            'name_ar' => 'جامعة الوادي الجديد',
            'code' => 'NVU',
        ],
        [
            'name' => 'Arab Academy for Science, Technology and Maritime Transport',
            'name_ar' => 'الأكاديمية العربية للعلوم والتكنولوجيا والنقل البحري',
            'code' => 'AASTMT',
        ],
        [
            'name' => 'The American University in Cairo',
            'name_ar' => 'الجامعة الأمريكية بالقاهرة',
            'code' => 'AUC',
        ],
        [
            'name' => 'German University in Cairo',
            'name_ar' => 'الجامعة الألمانية بالقاهرة',
            'code' => 'GUC',
        ],
        [
            'name' => 'British University in Egypt',
            'name_ar' => 'الجامعة البريطانية في مصر',
            'code' => 'BUE',
        ],
        [
            'name' => 'Future University in Egypt',
            'name_ar' => 'جامعة المستقبل في مصر',
            'code' => 'FUE',
        ],
    ];

    $sql = "
        INSERT INTO universities (
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

    foreach ($universities as $university) {
        $statement->execute([
            ':name' => $university['name'],
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

        seed_universities($pdo);

        $pdo->commit();

        echo "Universities seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Universities seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
