<?php

/**
 * MASAR - Skills Seeder
 *
 * Seeds the skills lookup table.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_skills(PDO $pdo): void
{
    $skills = [
        [
            'name' => 'PHP',
            'name_ar' => 'PHP',
            'category' => 'programming',
        ],
        [
            'name' => 'JavaScript',
            'name_ar' => 'جافاسكريبت',
            'category' => 'programming',
        ],
        [
            'name' => 'TypeScript',
            'name_ar' => 'تايب سكريبت',
            'category' => 'programming',
        ],
        [
            'name' => 'Python',
            'name_ar' => 'بايثون',
            'category' => 'programming',
        ],
        [
            'name' => 'Java',
            'name_ar' => 'جافا',
            'category' => 'programming',
        ],
        [
            'name' => 'C++',
            'name_ar' => 'سي بلس بلس',
            'category' => 'programming',
        ],
        [
            'name' => 'C#',
            'name_ar' => 'سي شارب',
            'category' => 'programming',
        ],
        [
            'name' => 'Go',
            'name_ar' => 'جولانج',
            'category' => 'programming',
        ],
        [
            'name' => 'Laravel',
            'name_ar' => 'لارافيل',
            'category' => 'framework',
        ],
        [
            'name' => 'Symfony',
            'name_ar' => 'سيمفوني',
            'category' => 'framework',
        ],
        [
            'name' => 'React',
            'name_ar' => 'رياكت',
            'category' => 'frontend',
        ],
        [
            'name' => 'Vue.js',
            'name_ar' => 'فيو',
            'category' => 'frontend',
        ],
        [
            'name' => 'Angular',
            'name_ar' => 'أنجولار',
            'category' => 'frontend',
        ],
        [
            'name' => 'HTML',
            'name_ar' => 'HTML',
            'category' => 'frontend',
        ],
        [
            'name' => 'CSS',
            'name_ar' => 'CSS',
            'category' => 'frontend',
        ],
        [
            'name' => 'Tailwind CSS',
            'name_ar' => 'تايلويند CSS',
            'category' => 'frontend',
        ],
        [
            'name' => 'Bootstrap',
            'name_ar' => 'بوتستراب',
            'category' => 'frontend',
        ],
        [
            'name' => 'Node.js',
            'name_ar' => 'نود جي إس',
            'category' => 'backend',
        ],
        [
            'name' => 'Express.js',
            'name_ar' => 'إكسبريس',
            'category' => 'backend',
        ],
        [
            'name' => 'REST API',
            'name_ar' => 'واجهات REST',
            'category' => 'backend',
        ],
        [
            'name' => 'GraphQL',
            'name_ar' => 'جراف كيو إل',
            'category' => 'backend',
        ],
        [
            'name' => 'MySQL',
            'name_ar' => 'ماي إس كيو إل',
            'category' => 'database',
        ],
        [
            'name' => 'PostgreSQL',
            'name_ar' => 'بوستجري إس كيو إل',
            'category' => 'database',
        ],
        [
            'name' => 'MongoDB',
            'name_ar' => 'مونجو دي بي',
            'category' => 'database',
        ],
        [
            'name' => 'Redis',
            'name_ar' => 'ريديس',
            'category' => 'database',
        ],
        [
            'name' => 'Git',
            'name_ar' => 'جيت',
            'category' => 'tools',
        ],
        [
            'name' => 'GitHub',
            'name_ar' => 'جيت هب',
            'category' => 'tools',
        ],
        [
            'name' => 'Docker',
            'name_ar' => 'دوكر',
            'category' => 'devops',
        ],
        [
            'name' => 'Linux',
            'name_ar' => 'لينكس',
            'category' => 'devops',
        ],
        [
            'name' => 'CI/CD',
            'name_ar' => 'التكامل والنشر المستمر',
            'category' => 'devops',
        ],
        [
            'name' => 'AWS',
            'name_ar' => 'أمازون ويب سيرفيسز',
            'category' => 'cloud',
        ],
        [
            'name' => 'Microsoft Azure',
            'name_ar' => 'مايكروسوفت أزور',
            'category' => 'cloud',
        ],
        [
            'name' => 'Google Cloud',
            'name_ar' => 'جوجل كلاود',
            'category' => 'cloud',
        ],
        [
            'name' => 'Data Analysis',
            'name_ar' => 'تحليل البيانات',
            'category' => 'data',
        ],
        [
            'name' => 'Data Visualization',
            'name_ar' => 'تصور البيانات',
            'category' => 'data',
        ],
        [
            'name' => 'Machine Learning',
            'name_ar' => 'تعلم الآلة',
            'category' => 'ai',
        ],
        [
            'name' => 'Deep Learning',
            'name_ar' => 'التعلم العميق',
            'category' => 'ai',
        ],
        [
            'name' => 'Natural Language Processing',
            'name_ar' => 'معالجة اللغة الطبيعية',
            'category' => 'ai',
        ],
        [
            'name' => 'Cybersecurity',
            'name_ar' => 'الأمن السيبراني',
            'category' => 'security',
        ],
        [
            'name' => 'Penetration Testing',
            'name_ar' => 'اختبار الاختراق',
            'category' => 'security',
        ],
        [
            'name' => 'UI Design',
            'name_ar' => 'تصميم واجهات المستخدم',
            'category' => 'design',
        ],
        [
            'name' => 'UX Design',
            'name_ar' => 'تجربة المستخدم',
            'category' => 'design',
        ],
        [
            'name' => 'Figma',
            'name_ar' => 'فيجما',
            'category' => 'design',
        ],
        [
            'name' => 'Adobe Photoshop',
            'name_ar' => 'أدوبي فوتوشوب',
            'category' => 'design',
        ],
        [
            'name' => 'Adobe Illustrator',
            'name_ar' => 'أدوبي إليستريتور',
            'category' => 'design',
        ],
        [
            'name' => 'Project Management',
            'name_ar' => 'إدارة المشاريع',
            'category' => 'business',
        ],
        [
            'name' => 'Business Analysis',
            'name_ar' => 'تحليل الأعمال',
            'category' => 'business',
        ],
        [
            'name' => 'Digital Marketing',
            'name_ar' => 'التسويق الرقمي',
            'category' => 'marketing',
        ],
        [
            'name' => 'SEO',
            'name_ar' => 'تحسين محركات البحث',
            'category' => 'marketing',
        ],
        [
            'name' => 'Content Writing',
            'name_ar' => 'كتابة المحتوى',
            'category' => 'communication',
        ],
        [
            'name' => 'Communication',
            'name_ar' => 'مهارات التواصل',
            'category' => 'soft_skills',
        ],
        [
            'name' => 'Teamwork',
            'name_ar' => 'العمل الجماعي',
            'category' => 'soft_skills',
        ],
        [
            'name' => 'Problem Solving',
            'name_ar' => 'حل المشكلات',
            'category' => 'soft_skills',
        ],
        [
            'name' => 'Time Management',
            'name_ar' => 'إدارة الوقت',
            'category' => 'soft_skills',
        ],
        [
            'name' => 'Leadership',
            'name_ar' => 'القيادة',
            'category' => 'soft_skills',
        ],
        [
            'name' => 'English',
            'name_ar' => 'اللغة الإنجليزية',
            'category' => 'languages',
        ],
        [
            'name' => 'French',
            'name_ar' => 'اللغة الفرنسية',
            'category' => 'languages',
        ],
        [
            'name' => 'German',
            'name_ar' => 'اللغة الألمانية',
            'category' => 'languages',
        ],
    ];

    $sql = "
        INSERT INTO skills (
            name
        )
        VALUES (
            :name
        )
        ON DUPLICATE KEY UPDATE
            is_active = 1
    ";

    $statement = $pdo->prepare($sql);

    foreach ($skills as $skill) {
        $statement->execute([
            ':name' => $skill['name'],
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

        seed_skills($pdo);

        $pdo->commit();

        echo "Skills seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Skills seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
