<?php

/**
 * MASAR - Auth Registration Test Seeder (DEVELOPMENT / TEST DATA ONLY)
 *
 * Seeds a realistic dataset used to verify the redesigned registration
 * data flow and the student <-> company specialization matching:
 *
 * - Study fields + specializations (Field -> Specialization concept,
 *   specializations.field_id).
 * - Companies linked to one or multiple industries
 *   (company_specializations.specialization_id).
 * - Students linked to a field + specialization
 *   (students.field_id / students.specialization_id).
 * - One published training listing per company so the specialization
 *   based training matching can be verified end-to-end.
 *
 * IMPORTANT:
 * - Intended for development/testing environments only.
 * - Idempotent: safe to run multiple times (existing records are reused,
 *   never duplicated or modified).
 * - All rows are clearly identifiable:
 *     users     -> e-mail "authtest.*@masar.test"
 *     companies -> legal_name "[AuthTest] ..."
 *     students  -> full_name "[AuthTest] ..."
 * - No real personal information is used.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

/*
|--------------------------------------------------------------------------
| Test Account Password
|--------------------------------------------------------------------------
*/

const AUTHTEST_PASSWORD = 'AuthTest@123456';

const AUTHTEST_COMPANY_PREFIX = '[AuthTest]';
const AUTHTEST_STUDENT_PREFIX = '[AuthTest]';

/*
|--------------------------------------------------------------------------
| Field -> Specialization Dataset (must match specializations_seeder)
|--------------------------------------------------------------------------
*/

function authtest_field_specialization_map(): array
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

/*
|--------------------------------------------------------------------------
| Company Dataset
|--------------------------------------------------------------------------
|
| Each company: one or multiple industries (specializations) and its
| single published training listing.
|
*/

function authtest_company_map(): array
{
    return [
        '[AuthTest] CodeWave Technologies' => [
            'email' => 'authtest.codewave@masar.test',
            'industries' => ['Software Engineering'],
            'training' => 'Full-Stack Web Development Internship',
        ],
        '[AuthTest] DataPulse AI' => [
            'email' => 'authtest.datapulse@masar.test',
            'industries' => ['Data Science'],
            'training' => 'Data Analytics Summer Training',
        ],
        '[AuthTest] TechNova Labs' => [
            'email' => 'authtest.technova@masar.test',
            'industries' => [
                'Software Engineering',
                'Artificial Intelligence',
                'Data Science',
                'Web Development',
            ],
            'training' => 'AI Research Shadowing Program',
        ],
        '[AuthTest] Delta Engineering' => [
            'email' => 'authtest.delta@masar.test',
            'industries' => ['Mechanical Engineering'],
            'training' => 'Mechanical Design Hands-On Training',
        ],
        '[AuthTest] BuildCore Engineering' => [
            'email' => 'authtest.buildcore@masar.test',
            'industries' => ['Civil Engineering'],
            'training' => 'Site Engineering Field Training',
        ],
        '[AuthTest] MediCare Center' => [
            'email' => 'authtest.medicare@masar.test',
            'industries' => ['General Medicine'],
            'training' => 'Clinical Rotation Shadowing',
        ],
        '[AuthTest] PharmaLife' => [
            'email' => 'authtest.pharmalife@masar.test',
            'industries' => ['Clinical Pharmacy'],
            'training' => 'Clinical Pharmacy Practical Training',
        ],
        '[AuthTest] MarketPro' => [
            'email' => 'authtest.marketpro@masar.test',
            'industries' => ['Digital Marketing', 'Marketing'],
            'training' => 'Digital Marketing Campaign Project',
        ],
        '[AuthTest] DesignHub' => [
            'email' => 'authtest.designhub@masar.test',
            'industries' => ['UI/UX Design'],
            'training' => 'UI/UX Design Studio Training',
        ],
        '[AuthTest] LawBridge' => [
            'email' => 'authtest.lawbridge@masar.test',
            'industries' => ['Corporate Law'],
            'training' => 'Corporate Law Office Shadowing',
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Student Dataset
|--------------------------------------------------------------------------
*/

function authtest_student_map(): array
{
    return [
        '[AuthTest] Ahmed Hassan' => [
            'email' => 'authtest.ahmed@masar.test',
            'field' => 'Engineering',
            'specialization' => 'Mechanical Engineering',
        ],
        '[AuthTest] Sara Mohamed' => [
            'email' => 'authtest.sara@masar.test',
            'field' => 'Engineering',
            'specialization' => 'Civil Engineering',
        ],
        '[AuthTest] Omar Ali' => [
            'email' => 'authtest.omar@masar.test',
            'field' => 'Computer Science',
            'specialization' => 'Software Engineering',
        ],
        '[AuthTest] Laila Mostafa' => [
            'email' => 'authtest.laila@masar.test',
            'field' => 'Computer Science',
            'specialization' => 'Data Science',
        ],
        '[AuthTest] Karim Adel' => [
            'email' => 'authtest.karim@masar.test',
            'field' => 'Computer Science',
            'specialization' => 'Artificial Intelligence',
        ],
        '[AuthTest] Mona Samir' => [
            'email' => 'authtest.mona@masar.test',
            'field' => 'Medicine',
            'specialization' => 'Cardiology',
        ],
        '[AuthTest] Youssef Tarek' => [
            'email' => 'authtest.youssef@masar.test',
            'field' => 'Business',
            'specialization' => 'Marketing',
        ],
        '[AuthTest] Dina Ahmed' => [
            'email' => 'authtest.dina@masar.test',
            'field' => 'Law',
            'specialization' => 'Corporate Law',
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function authtest_find_or_create_user(
    PDO $pdo,
    string $email,
    string $role,
    string $status = 'active'
): int {
    $find = $pdo->prepare(
        " SELECT id FROM users WHERE email = :email LIMIT 1 "
    );
    $find->execute([':email' => $email]);
    $existing = $find->fetchColumn();

    if ($existing !== false) {
        return (int) $existing;
    }

    $insert = $pdo->prepare("
        INSERT INTO users (
            role,
            email,
            password_hash,
            status,
            email_verified_at,
            created_at,
            updated_at
        )
        VALUES (
            :role,
            :email,
            :password_hash,
            :status,
            NOW(),
            NOW(),
            NOW()
        )
    ");
    $insert->execute([
        ':role' => $role,
        ':email' => $email,
        ':password_hash' => password_hash(AUTHTEST_PASSWORD, PASSWORD_DEFAULT),
        ':status' => $status,
    ]);

    return (int) $pdo->lastInsertId();
}

function authtest_find_or_create_lookup(
    PDO $pdo,
    string $table,
    string $name,
    ?int $field_id = null,
    bool &$created = false
): int {
    $find = $pdo->prepare(
        " SELECT id FROM `{$table}` WHERE name = :name LIMIT 1 "
    );
    $find->execute([':name' => $name]);
    $existing = $find->fetchColumn();

    if ($existing !== false) {
        return (int) $existing;
    }

    if ($table === 'study_fields') {
        $insert = $pdo->prepare(
            " INSERT INTO study_fields (name, is_active) VALUES (:name, 1) "
        );
        $insert->execute([':name' => $name]);
    } else {
        $insert = $pdo->prepare("
            INSERT INTO specializations (name, field_id, is_active)
            VALUES (:name, :field_id, 1)
        ");
        $insert->execute([
            ':name' => $name,
            ':field_id' => $field_id,
        ]);
    }

    $created = true;

    return (int) $pdo->lastInsertId();
}

function authtest_find_or_create_company(
    PDO $pdo,
    string $legal_name,
    int $user_id,
    array $industry_names
): int {
    $find = $pdo->prepare(
        " SELECT id FROM companies WHERE legal_name = :legal_name LIMIT 1 "
    );
    $find->execute([':legal_name' => $legal_name]);
    $existing = $find->fetchColumn();

    if ($existing !== false) {
        $company_id = (int) $existing;
    } else {
        $insert = $pdo->prepare("
            INSERT INTO companies (
                user_id,
                legal_name,
                description,
                approval_status,
                approved_at,
                created_at,
                updated_at
            )
            VALUES (
                :user_id,
                :legal_name,
                :description,
                'approved',
                NOW(),
                NOW(),
                NOW()
            )
        ");
        $insert->execute([
            ':user_id' => $user_id,
            ':legal_name' => $legal_name,
            ':description' => 'Development test company seeded by auth_registration_test_seeder.',
        ]);

        $company_id = (int) $pdo->lastInsertId();
    }

    // Attach industries to company_specializations.
    foreach ($industry_names as $industry_name) {
        $created = false;
        $specialization_id = authtest_find_or_create_specialization_by_name(
            $pdo,
            $industry_name,
            $created
        );

        $link = $pdo->prepare("
            INSERT IGNORE INTO company_specializations (
                company_id,
                specialization_id,
                created_at
            )
            VALUES (
                :company_id,
                :specialization_id,
                NOW()
            )
        ");
        $link->execute([
            ':company_id' => $company_id,
            ':specialization_id' => $specialization_id,
        ]);
    }

    return $company_id;
}

function authtest_find_or_create_specialization_by_name(
    PDO $pdo,
    string $name,
    bool &$created = false
): int {
    // Resolve through the seeded field map so field_id stays consistent.
    foreach (authtest_field_specialization_map() as $field_name => $specializations) {
        if (in_array($name, $specializations, true)) {
            $field_created = false;
            $field_id = authtest_find_or_create_lookup(
                $pdo,
                'study_fields',
                $field_name,
                null,
                $field_created
            );

            return authtest_find_or_create_lookup(
                $pdo,
                'specializations',
                $name,
                $field_id,
                $created
            );
        }
    }

    // Unknown specialization name: create without a field.
    return authtest_find_or_create_lookup($pdo, 'specializations', $name, null, $created);
}

function authtest_find_or_create_student(
    PDO $pdo,
    string $full_name,
    string $email,
    string $field_name,
    string $specialization_name
): int {
    $user_id = authtest_find_or_create_user($pdo, $email, 'student');

    $find = $pdo->prepare(
        " SELECT id FROM students WHERE user_id = :user_id LIMIT 1 "
    );
    $find->execute([':user_id' => $user_id]);
    $existing = $find->fetchColumn();

    if ($existing !== false) {
        return (int) $existing;
    }

    $field_created = false;
    $field_id = authtest_find_or_create_lookup(
        $pdo,
        'study_fields',
        $field_name,
        null,
        $field_created
    );

    $spec_created = false;
    $specialization_id = authtest_find_or_create_specialization_by_name(
        $pdo,
        $specialization_name,
        $spec_created
    );

    $insert = $pdo->prepare("
        INSERT INTO students (
            user_id,
            full_name,
            field_id,
            specialization_id,
            is_profile_complete,
            created_at,
            updated_at
        )
        VALUES (
            :user_id,
            :full_name,
            :field_id,
            :specialization_id,
            1,
            NOW(),
            NOW()
        )
    ");
    $insert->execute([
        ':user_id' => $user_id,
        ':full_name' => $full_name,
        ':field_id' => $field_id,
        ':specialization_id' => $specialization_id,
    ]);

    return (int) $pdo->lastInsertId();
}

function authtest_find_or_create_training(
    PDO $pdo,
    int $company_id,
    string $title
): int {
    $find = $pdo->prepare("
        SELECT id FROM training_listings
        WHERE company_id = :company_id AND title = :title
        LIMIT 1
    ");
    $find->execute([
        ':company_id' => $company_id,
        ':title' => $title,
    ]);
    $existing = $find->fetchColumn();

    if ($existing !== false) {
        return (int) $existing;
    }

    $insert = $pdo->prepare("
        INSERT INTO training_listings (
            company_id,
            title,
            description,
            training_type,
            mode,
            status,
            published_at,
            starts_at,
            ends_at,
            application_deadline,
            capacity,
            created_at,
            updated_at
        )
        VALUES (
            :company_id,
            :title,
            :description,
            'hands_on',
            'hybrid',
            'published',
            NOW(),
            DATE_ADD(NOW(), INTERVAL 14 DAY),
            DATE_ADD(NOW(), INTERVAL 75 DAY),
            DATE_ADD(NOW(), INTERVAL 10 DAY),
            10,
            NOW(),
            NOW()
        )
    ");
    $insert->execute([
        ':company_id' => $company_id,
        ':title' => $title,
        ':description' => 'Development test training listing seeded by auth_registration_test_seeder.',
    ]);

    $training_id = (int) $pdo->lastInsertId();

    // Link the training to every specialization of its company industry.
    $link = $pdo->prepare("
        INSERT IGNORE INTO training_specializations (
            training_id,
            specialization_id
        )
        SELECT :training_id, cs.specialization_id
        FROM company_specializations cs
        WHERE cs.company_id = :company_id
    ");
    $link->execute([
        ':training_id' => $training_id,
        ':company_id' => $company_id,
    ]);

    return $training_id;
}

/*
|--------------------------------------------------------------------------
| Seed Entry Point
|--------------------------------------------------------------------------
*/

function seed_auth_registration_test_data(PDO $pdo): void
{
    /*
     * Companies (+ industries + one published training each).
     */

    foreach (authtest_company_map() as $legal_name => $config) {
        $user_id = authtest_find_or_create_user($pdo, $config['email'], 'company');

        $company_id = authtest_find_or_create_company(
            $pdo,
            $legal_name,
            $user_id,
            $config['industries']
        );

        authtest_find_or_create_training(
            $pdo,
            $company_id,
            $config['training']
        );
    }

    /*
     * Students (+ field + specialization).
     */

    foreach (authtest_student_map() as $full_name => $config) {
        authtest_find_or_create_student(
            $pdo,
            $full_name,
            $config['email'],
            $config['field'],
            $config['specialization']
        );
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

        seed_auth_registration_test_data($pdo);

        $pdo->commit();

        echo "Auth registration test data seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Auth registration test seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
