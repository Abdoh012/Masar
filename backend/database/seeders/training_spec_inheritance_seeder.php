<?php

/**
 * MASAR - Training Specialization Inheritance Seeder (DEVELOPMENT / TEST DATA ONLY)
 *
 * Seeds companies + trainings used to verify that a newly created
 * training automatically inherits ALL specializations of its company
 * through the company_specializations -> training_specializations
 * relationship.
 *
 * - Idempotent: safe to run multiple times (existing records are
 *   reused, never duplicated).
 * - All rows are clearly identifiable:
 *     users     -> e-mail "tspec.*@masar.test"
 *     companies -> legal_name "[TrainingSpecTest] ..."
 *     trainings -> titles defined in the dataset below
 * - The inheritance itself is mirrored here exactly like production:
 *   after inserting a listing, every specialization of the company
 *   (company_specializations) is linked to the training via
 *   training_specializations.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

const TSPEC_PASSWORD = 'MatchTest@123456';
const TSPEC_COMPANY_PREFIX = '[TrainingSpecTest]';

/*
|--------------------------------------------------------------------------
| Company Dataset
|--------------------------------------------------------------------------
|
| Each company lists its registered specializations (company_specializations)
| and its trainings. Company D intentionally has NO specializations so the
| "no specialization" behavior can be tested.
|
*/

function tspec_company_map(): array
{
    return [

        [
            'key' => 'a',
            'name' => 'Company A Mechanics',
            'email' => 'tspec.a@masar.test',
            'city' => 'Cairo',
            'specializations' => ['Mechanical Engineering'],
            'trainings' => [
                ['title' => 'Mechanical Design Training', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '3000.00'],
                ['title' => 'CAD Engineering Training', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null],
            ],
        ],

        [
            'key' => 'b',
            'name' => 'Company B Software',
            'email' => 'tspec.b@masar.test',
            'city' => 'Cairo',
            'specializations' => ['Software Engineering', 'Data Science'],
            'trainings' => [
                ['title' => 'Backend Development Internship', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1, 'amount' => '4000.00'],
                ['title' => 'Data Science Internship', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null],
                ['title' => 'AI/Software Training', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '3500.00'],
            ],
        ],

        [
            'key' => 'c',
            'name' => 'Company C Business',
            'email' => 'tspec.c@masar.test',
            'city' => 'Giza',
            'specializations' => ['Marketing', 'Finance', 'Accounting'],
            'trainings' => [
                ['title' => 'Digital Marketing Training', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 0, 'amount' => null],
                ['title' => 'Finance Internship', 'type' => 'shadowing', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '2500.00'],
            ],
        ],

        /*
         * Control company: approved but WITHOUT any registered
         * specialization. Trainings created by it must end up with
         * zero training_specializations rows.
         */
        [
            'key' => 'd',
            'name' => 'Company D NoSpecialization',
            'email' => 'tspec.d@masar.test',
            'city' => 'Cairo',
            'specializations' => [],
            'trainings' => [],
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Helpers (find-or-create, idempotent)
|--------------------------------------------------------------------------
*/

function tspec_find_or_create_lookup(
    PDO $pdo,
    string $table,
    string $name
): int {

    $statement = $pdo->prepare(
        "SELECT id FROM {$table} WHERE name = :name LIMIT 1"
    );
    $statement->execute([':name' => $name]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "INSERT INTO {$table} (name, is_active) VALUES (:name, 1)"
    );
    $insert->execute([':name' => $name]);

    return (int) $pdo->lastInsertId();
}

function tspec_find_or_create_user(
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
            TSPEC_PASSWORD,
            PASSWORD_DEFAULT
        ),
    ]);

    return (int) $pdo->lastInsertId();
}

function tspec_find_or_create_company(
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
            approval_status,
            approved_at
        )
        VALUES (
            :user_id,
            :legal_name,
            :description,
            :city,
            'approved',
            NOW()
        )
        "
    );
    $insert->execute([
        ':user_id' => $user_id,
        ':legal_name' => TSPEC_COMPANY_PREFIX . ' ' . $company['name'],
        ':description' => 'Development test company seeded by training_spec_inheritance_seeder.',
        ':city' => $company['city'],
    ]);

    return (int) $pdo->lastInsertId();
}

function tspec_ensure_company_specialization(
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

function tspec_find_or_create_training(
    PDO $pdo,
    int $company_id,
    array $training,
    string $city
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
        ':title' => $training['title'],
        ':description' => 'Realistic test opportunity: ' . $training['title'] . '. Part of the MASAR training-specialization-inheritance dataset.',
        ':training_type' => $training['type'],
        ':mode' => $training['mode'],
        ':is_paid' => $is_paid ? 1 : 0,
        ':compensation_amount' => $is_paid ? $training['amount'] : null,
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
| Inheritance Mirror
|--------------------------------------------------------------------------
|
| Copies the company's current specializations onto the training,
| exactly like the production creation flow does. Idempotent thanks
| to the composite primary key on training_specializations.
|
*/

function tspec_inherit_company_specializations(
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

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {

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

function seed_training_spec_inheritance_data(PDO $pdo): void
{
    $stats = [
        'companies' => 0,
        'company_specializations' => 0,
        'trainings' => 0,
        'training_specializations' => 0,
    ];

    foreach (tspec_company_map() as $company) {

        $check_company = $pdo->prepare(
            'SELECT id FROM companies WHERE user_id = (SELECT id FROM users WHERE email = :email LIMIT 1) LIMIT 1'
        );
        $check_company->execute([':email' => $company['email']]);
        $company_before = $check_company->fetch(PDO::FETCH_ASSOC);

        $user_id = tspec_find_or_create_user($pdo, $company['email']);
        $company_id = tspec_find_or_create_company($pdo, $company, $user_id);

        if ($company_before === false) {
            $stats['companies']++;
        }

        foreach ($company['specializations'] as $specialization_name) {

            $specialization_id = tspec_find_or_create_lookup(
                $pdo,
                'specializations',
                $specialization_name
            );

            $pivot_before = $pdo->prepare(
                '
                SELECT 1
                FROM company_specializations
                WHERE company_id = :company_id
                  AND specialization_id = :specialization_id
                LIMIT 1
                '
            );
            $pivot_before->execute([
                ':company_id' => $company_id,
                ':specialization_id' => $specialization_id,
            ]);

            $existed = $pivot_before->fetchColumn() !== false;

            tspec_ensure_company_specialization(
                $pdo,
                $company_id,
                $specialization_id
            );

            if (!$existed) {
                $stats['company_specializations']++;
            }
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

            $training_existed =
                $check_training->fetch(PDO::FETCH_ASSOC) !== false;

            $training_id = tspec_find_or_create_training(
                $pdo,
                $company_id,
                $training,
                $company['city']
            );

            if (!$training_existed) {
                $stats['trainings']++;
            }

            $spec_check = $pdo->prepare(
                '
                SELECT COUNT(*) AS total
                FROM training_specializations
                WHERE training_id = :training_id
                '
            );
            $spec_check->execute([':training_id' => $training_id]);
            $specs_before = (int) $spec_check->fetch(PDO::FETCH_ASSOC)['total'];

            tspec_inherit_company_specializations(
                $pdo,
                $company_id,
                $training_id
            );

            $spec_after = $pdo->prepare(
                '
                SELECT COUNT(*) AS total
                FROM training_specializations
                WHERE training_id = :training_id
                '
            );
            $spec_after->execute([':training_id' => $training_id]);
            $specs_after_count = (int) $spec_after->fetch(PDO::FETCH_ASSOC)['total'];

            $stats['training_specializations'] +=
                max(0, $specs_after_count - $specs_before);
        }
    }

    echo 'Training specialization inheritance seed data ready.' . PHP_EOL;
    echo '  companies created:               ' . $stats['companies'] . PHP_EOL;
    echo '  company_specializations added:   ' . $stats['company_specializations'] . PHP_EOL;
    echo '  trainings created:               ' . $stats['trainings'] . PHP_EOL;
    echo '  training_specializations added:  ' . $stats['training_specializations'] . PHP_EOL;
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

        seed_training_spec_inheritance_data($pdo);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Training specialization inheritance seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
