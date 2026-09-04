<?php

/**
 * MASAR - Company Logo Seeder (DEVELOPMENT / TEST DATA ONLY)
 *
 * Seeds companies used to verify that every training automatically
 * returns its company's CURRENT logo through:
 *
 *     training_listings.company_id -> companies.id -> companies.company_logo
 *
 * - Idempotent: safe to run multiple times (existing records are
 *   reused, never duplicated).
 * - All rows are clearly identifiable:
 *     users     -> e-mail "logotest.*@masar.test"
 *     companies -> legal_name "[CompanyLogoTest] ..."
 * - Logo values follow the project's relative storage-path convention.
 *   Small placeholder PNG files are physically created under the
 *   upload storage directory so the paths point to real files.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

const LOGOTEST_PASSWORD = 'MatchTest@123456';
const LOGOTEST_COMPANY_PREFIX = '[CompanyLogoTest]';

/*
|--------------------------------------------------------------------------
| Company Dataset
|--------------------------------------------------------------------------
*/

function logotest_company_map(): array
{
    return [

        [
            'key' => 'engineering',
            'name' => 'Engineering Corp',
            'email' => 'logotest.engineering@masar.test',
            'city' => 'Cairo',
            'logo' => 'company-logo-test/engineering.png',
            'specializations' => ['Mechanical Engineering'],
            'trainings' => [
                ['title' => '[CompanyLogoTest] Mechanical Design Training', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1],
                ['title' => '[CompanyLogoTest] CAD Engineering Training', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0],
            ],
        ],

        [
            'key' => 'software',
            'name' => 'Software Corp',
            'email' => 'logotest.software@masar.test',
            'city' => 'Cairo',
            'logo' => 'company-logo-test/software.png',
            'specializations' => ['Software Engineering'],
            'trainings' => [
                ['title' => '[CompanyLogoTest] Backend Internship', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1],
                ['title' => '[CompanyLogoTest] Frontend Internship', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0],
            ],
        ],

        [
            'key' => 'data',
            'name' => 'Data Corp',
            'email' => 'logotest.data@masar.test',
            'city' => 'Giza',
            'logo' => 'company-logo-test/data.png',
            'specializations' => ['Data Science'],
            'trainings' => [
                ['title' => '[CompanyLogoTest] Data Analyst Training', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1],
            ],
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Placeholder PNG
|--------------------------------------------------------------------------
|
| Minimal valid 1x1 transparent PNG so the seeded logo path points to
| a real image file. Created only when missing (idempotent).
|
*/

function logotest_ensure_placeholder_png(string $absolute_path): void
{

    if (is_file($absolute_path)) {
        return;
    }

    $directory = dirname($absolute_path);

    if (!is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }

    $png_base64 =
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    file_put_contents(
        $absolute_path,
        (string) base64_decode($png_base64, true)
    );
}

function logotest_storage_dir(): string
{
    // Mirrors file_upload_service_default_config(): backend/app/storage/uploads
    return dirname(__DIR__, 2) . '/app/storage/uploads';
}

/*
|--------------------------------------------------------------------------
| Helpers (find-or-create, idempotent)
|--------------------------------------------------------------------------
*/

function logotest_find_or_create_lookup(
    PDO $pdo,
    string $name
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM specializations WHERE name = :name LIMIT 1'
    );
    $statement->execute([':name' => $name]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "INSERT INTO specializations (name, is_active) VALUES (:name, 1)"
    );
    $insert->execute([':name' => $name]);

    return (int) $pdo->lastInsertId();
}

function logotest_find_or_create_user(
    PDO $pdo,
    string $email
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute([':email' => $email]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "
        INSERT INTO users (
            role,
            email,
            password_hash,
            status,
            email_verified_at
        )
        VALUES (
            'company',
            :email,
            :password_hash,
            'active',
            NOW()
        )
        "
    );
    $insert->execute([
        ':email' => $email,
        ':password_hash' => password_hash(
            LOGOTEST_PASSWORD,
            PASSWORD_DEFAULT
        ),
    ]);

    return (int) $pdo->lastInsertId();
}

function logotest_find_or_create_company(
    PDO $pdo,
    array $company,
    int $user_id
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM companies WHERE user_id = :user_id LIMIT 1'
    );
    $statement->execute([':user_id' => $user_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "
        INSERT INTO companies (
            user_id,
            legal_name,
            description,
            city,
            company_logo,
            approval_status,
            approved_at
        )
        VALUES (
            :user_id,
            :legal_name,
            :description,
            :city,
            :company_logo,
            'approved',
            NOW()
        )
        "
    );
    $insert->execute([
        ':user_id' => $user_id,
        ':legal_name' => LOGOTEST_COMPANY_PREFIX . ' ' . $company['name'],
        ':description' => 'Development test company seeded by company_logo_seeder.',
        ':city' => $company['city'],
        ':company_logo' => $company['logo'],
    ]);

    return (int) $pdo->lastInsertId();
}

function logotest_ensure_company_specialization(
    PDO $pdo,
    int $company_id,
    int $specialization_id
): void {

    $check = $pdo->prepare(
        '
        SELECT 1
        FROM company_specializations
        WHERE company_id = :company_id
          AND specialization_id = :specialization_id
        LIMIT 1
        '
    );
    $check->execute([
        ':company_id' => $company_id,
        ':specialization_id' => $specialization_id,
    ]);

    if ($check->fetchColumn() !== false) {
        return;
    }

    $insert = $pdo->prepare(
        '
        INSERT IGNORE INTO company_specializations (
            company_id,
            specialization_id
        )
        VALUES (
            :company_id,
            :specialization_id
        )
        '
    );
    $insert->execute([
        ':company_id' => $company_id,
        ':specialization_id' => $specialization_id,
    ]);
}

function logotest_find_or_create_training(
    PDO $pdo,
    int $company_id,
    array $training,
    string $city,
    int $specialization_id
): int {

    $statement = $pdo->prepare(
        '
        SELECT id
        FROM training_listings
        WHERE company_id = :company_id
          AND title = :title
        LIMIT 1
        '
    );
    $statement->execute([
        ':company_id' => $company_id,
        ':title' => $training['title'],
    ]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        return (int) $row['id'];
    }

    $is_paid = (int) $training['paid'] === 1;

    $insert = $pdo->prepare(
        "
        INSERT INTO training_listings (
            company_id,
            specialization_id,
            title,
            description,
            training_type,
            mode,
            may_lead_to_employment,
            is_paid,
            compensation_amount,
            compensation_currency,
            trial_period_days,
            capacity,
            status,
            published_at,
            starts_at,
            ends_at,
            application_deadline,
            location,
            created_at,
            updated_at
        )
        VALUES (
            :company_id,
            :specialization_id,
            :title,
            :description,
            :training_type,
            :mode,
            0,
            :is_paid,
            :compensation_amount,
            'EGP',
            :trial_period_days,
            5,
            'published',
            NOW(),
            :starts_at,
            :ends_at,
            :application_deadline,
            :location,
            NOW(),
            NOW()
        )
        "
    );
    $insert->execute([
        ':company_id' => $company_id,
        ':specialization_id' => $specialization_id,
        ':title' => $training['title'],
        ':description' => 'Realistic test opportunity: ' . $training['title'] . '. Part of the MASAR company-logo dataset.',
        ':training_type' => $training['type'],
        ':mode' => $training['mode'],
        ':is_paid' => $is_paid ? 1 : 0,
        ':compensation_amount' => $is_paid ? '2500.00' : null,
        ':trial_period_days' => $is_paid ? 7 : null,
        ':starts_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ':ends_at' => date('Y-m-d H:i:s', strtotime('+90 days')),
        ':application_deadline' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ':location' => $city . ', Egypt',
    ]);

    return (int) $pdo->lastInsertId();
}

/*
|--------------------------------------------------------------------------
| Inheritance Mirror (same as production creation flow)
|--------------------------------------------------------------------------
*/

function logotest_inherit_company_specializations(
    PDO $pdo,
    int $company_id,
    int $training_id
): void {

    $statement = $pdo->prepare(
        '
        SELECT specialization_id
        FROM company_specializations
        WHERE company_id = :company_id
        '
    );
    $statement->execute([':company_id' => $company_id]);

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $check = $pdo->prepare(
            '
            SELECT 1
            FROM training_specializations
            WHERE training_id = :training_id
              AND specialization_id = :specialization_id
            LIMIT 1
            '
        );
        $check->execute([
            ':training_id' => $training_id,
            ':specialization_id' => (int) $row['specialization_id'],
        ]);

        if ($check->fetchColumn() !== false) {
            continue;
        }

        $insert = $pdo->prepare(
            '
            INSERT IGNORE INTO training_specializations (
                training_id,
                specialization_id
            )
            VALUES (
                :training_id,
                :specialization_id
            )
            '
        );
        $insert->execute([
            ':training_id' => $training_id,
            ':specialization_id' => (int) $row['specialization_id'],
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Main Seeder
|--------------------------------------------------------------------------
*/

function seed_company_logo_data(PDO $pdo): void
{
    $stats = [
        'companies' => 0,
        'logos' => 0,
        'trainings' => 0,
    ];

    // Ensure placeholder PNG files exist for every seeded logo path.
    foreach (logotest_company_map() as $company) {
        $before = is_file(
            logotest_storage_dir() . '/' . $company['logo']
        );

        logotest_ensure_placeholder_png(
            logotest_storage_dir() . '/' . $company['logo']
        );

        if (!$before) {
            $stats['logos']++;
        }
    }

    foreach (logotest_company_map() as $company) {

        $check_company = $pdo->prepare(
            'SELECT id FROM companies WHERE user_id = (SELECT id FROM users WHERE email = :email LIMIT 1) LIMIT 1'
        );
        $check_company->execute([':email' => $company['email']]);
        $company_before = $check_company->fetch(PDO::FETCH_ASSOC);

        $user_id = logotest_find_or_create_user($pdo, $company['email']);
        $company_id = logotest_find_or_create_company($pdo, $company, $user_id);

        if ($company_before === false) {
            $stats['companies']++;
        }

        foreach ($company['specializations'] as $spec_name) {
            $spec_id = logotest_find_or_create_lookup($pdo, $spec_name);
            logotest_ensure_company_specialization($pdo, $company_id, $spec_id);
        }

        foreach ($company['trainings'] as $training) {

            $check_training = $pdo->prepare(
                '
                SELECT id
                FROM training_listings
                WHERE company_id = :company_id
                  AND title = :title
                LIMIT 1
                '
            );
            $check_training->execute([
                ':company_id' => $company_id,
                ':title' => $training['title'],
            ]);

            $existed = $check_training->fetch(PDO::FETCH_ASSOC) !== false;

            $primary_specialization_id =
                logotest_find_or_create_lookup(
                    $pdo,
                    $company['specializations'][0]
                );

            $training_id = logotest_find_or_create_training(
                $pdo,
                $company_id,
                $training,
                $company['city'],
                $primary_specialization_id
            );

            if (!$existed) {
                $stats['trainings']++;
            }

            logotest_inherit_company_specializations(
                $pdo,
                $company_id,
                $training_id
            );
        }
    }

    echo 'Company logo seed data ready.' . PHP_EOL;
    echo '  companies created:               ' . $stats['companies'] . PHP_EOL;
    echo '  placeholder pngs created:        ' . $stats['logos'] . PHP_EOL;
    echo '  trainings created:               ' . $stats['trainings'] . PHP_EOL;
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

        seed_company_logo_data($pdo);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Company logo seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
