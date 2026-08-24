<?php

/**
 * MASAR - Training Matching Seeder (DEVELOPMENT / TEST DATA ONLY)
 *
 * Seeds a broad, realistic dataset used to verify the
 * specialization-based training matching feature:
 *
 * - Study fields + specializations (Field -> Specialization concept).
 * - Companies with one or multiple industries
 *   (company_work_fields + company_specializations).
 * - Published / closed / draft training listings.
 * - Students with different fields/specializations,
 *   including a student without any specialization.
 *
 * IMPORTANT:
 * - Intended for development/testing environments only.
 * - Idempotent: safe to run multiple times (lookups + unique keys,
 *   existing records are reused, never duplicated or modified).
 * - All rows are clearly identifiable:
 *     users    -> e-mail "matchtest.*@masar.test"
 *     companies-> legal_name "[MatchTest] ..."
 *     students -> full_name "[MatchTest] ..."
 * - No real personal information is used.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

/*
|--------------------------------------------------------------------------
| Test Account Password
|--------------------------------------------------------------------------
|
| Shared deterministic password for every seeded account.
| Development/testing only - never use in production.
|
*/

const MATCHTEST_PASSWORD = 'MatchTest@123456';

const MATCHTEST_COMPANY_PREFIX = '[TrainingMatchTest]';
const MATCHTEST_STUDENT_PREFIX = '[TrainingMatchTest]';

/*
|--------------------------------------------------------------------------
| Field -> Specialization Dataset
|--------------------------------------------------------------------------
*/

function matchtest_field_specialization_map(): array
{
    return [

        'Engineering' => [
            'Mechanical Engineering',
            'Civil Engineering',
            'Electrical Engineering',
            'Computer Engineering',
            'Architecture',
        ],

        'Computer Science' => [
            'Software Engineering',
            'Data Science',
            'Cyber Security',
            'Artificial Intelligence',
            'Web Development',
        ],

        'Business' => [
            'Marketing',
            'Human Resources',
            'Business Administration',
            'Accounting',
            'Finance',
            'Sales',
        ],

        'Medicine' => [
            'General Medicine',
            'Dentistry',
            'Pharmacy',
        ],

        'Accounting' => [
            'Financial Accounting',
            'Management Accounting',
            'Auditing',
        ],

        'Design' => [
            'UI/UX Design',
            'Graphic Design',
            'Product Design',
        ],

        'Media' => [
            'Digital Marketing',
            'Content Creation',
            'Journalism',
        ],

        'Law' => [
            'Corporate Law',
            'Criminal Law',
            'Commercial Law',
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Company Dataset
|--------------------------------------------------------------------------
|
| Each company: work fields (broad study fields), industries
| (specializations) and its training listings.
|
*/

function matchtest_company_map(): array
{
    return [

        /*
         * Single-industry companies.
         */

        [
            'key' => 'delta',
            'name' => 'Delta Mechanics Co.',
            'email' => 'matchtest.delta@masar.test',
            'city' => 'Cairo',
            'website' => 'https://delta-mechanics.test',
            'fields' => ['Engineering'],
            'industries' => ['Mechanical Engineering'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Mechanical Design Intern', 'specialization' => 'Mechanical Engineering', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '3000.00', 'status' => 'published'],
                ['title' => 'CAD Engineering Trainee', 'specialization' => 'Mechanical Engineering', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Manufacturing Engineering Intern', 'specialization' => 'Mechanical Engineering', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '2500.00', 'status' => 'published'],
                ['title' => 'HVAC Engineering Trainee', 'specialization' => 'Mechanical Engineering', 'type' => 'shadowing', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'codewave',
            'name' => 'CodeWave Software',
            'email' => 'matchtest.codewave@masar.test',
            'city' => 'Cairo',
            'website' => 'https://codewave.test',
            'fields' => ['Computer Science'],
            'industries' => ['Software Engineering'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Backend PHP Developer Intern', 'specialization' => 'Software Engineering', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1, 'amount' => '4000.00', 'status' => 'published'],
                ['title' => 'React Frontend Intern', 'specialization' => 'Software Engineering', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Laravel Developer Trainee', 'specialization' => 'Software Engineering', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '3500.00', 'status' => 'published'],
                ['title' => 'Full Stack Developer Intern', 'specialization' => 'Software Engineering', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'datapulse',
            'name' => 'DataPulse Analytics',
            'email' => 'matchtest.datapulse@masar.test',
            'city' => 'Giza',
            'website' => 'https://datapulse.test',
            'fields' => ['Computer Science'],
            'industries' => ['Data Science'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Data Analyst Intern', 'specialization' => 'Data Science', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '3000.00', 'status' => 'published'],
                ['title' => 'Machine Learning Trainee', 'specialization' => 'Data Science', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Python Data Science Intern', 'specialization' => 'Data Science', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1, 'amount' => '3200.00', 'status' => 'published'],
            ],
        ],

        [
            'key' => 'secureshield',
            'name' => 'SecureShield Systems',
            'email' => 'matchtest.secureshield@masar.test',
            'city' => 'Cairo',
            'website' => 'https://secureshield.test',
            'fields' => ['Computer Science'],
            'industries' => ['Cyber Security'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'SOC Analyst Intern', 'specialization' => 'Cyber Security', 'type' => 'shadowing', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Cyber Security Trainee', 'specialization' => 'Cyber Security', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '3500.00', 'status' => 'published'],
                ['title' => 'Penetration Testing Intern', 'specialization' => 'Cyber Security', 'type' => 'project_based', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'nilebuild',
            'name' => 'Nile Build Construction',
            'email' => 'matchtest.nilebuild@masar.test',
            'city' => 'Cairo',
            'website' => 'https://nilebuild.test',
            'fields' => ['Engineering'],
            'industries' => ['Civil Engineering'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Civil Site Engineer Intern', 'specialization' => 'Civil Engineering', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '2800.00', 'status' => 'published'],
                ['title' => 'Structural Engineering Trainee', 'specialization' => 'Civil Engineering', 'type' => 'project_based', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Construction Management Intern', 'specialization' => 'Civil Engineering', 'type' => 'shadowing', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'voltedge',
            'name' => 'VoltEdge Electrical',
            'email' => 'matchtest.voltedge@masar.test',
            'city' => 'Alexandria',
            'website' => 'https://voltedge.test',
            'fields' => ['Engineering'],
            'industries' => ['Electrical Engineering'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Electrical Design Intern', 'specialization' => 'Electrical Engineering', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '3000.00', 'status' => 'published'],
                ['title' => 'Power Systems Trainee', 'specialization' => 'Electrical Engineering', 'type' => 'shadowing', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Embedded Systems Intern', 'specialization' => 'Electrical Engineering', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'brightreach',
            'name' => 'BrightReach Marketing',
            'email' => 'matchtest.brightreach@masar.test',
            'city' => 'Cairo',
            'website' => 'https://brightreach.test',
            'fields' => ['Business'],
            'industries' => ['Marketing'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Digital Marketing Intern', 'specialization' => 'Marketing', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '2000.00', 'status' => 'published'],
                ['title' => 'Marketing Specialist Trainee', 'specialization' => 'Marketing', 'type' => 'project_based', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Social Media Marketing Intern', 'specialization' => 'Marketing', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'closed'],
            ],
        ],

        [
            'key' => 'talentbridge',
            'name' => 'TalentBridge HR',
            'email' => 'matchtest.talentbridge@masar.test',
            'city' => 'Cairo',
            'website' => 'https://talentbridge.test',
            'fields' => ['Business'],
            'industries' => ['Human Resources'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'HR Intern', 'specialization' => 'Human Resources', 'type' => 'shadowing', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Talent Acquisition Trainee', 'specialization' => 'Human Resources', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '2200.00', 'status' => 'published'],
            ],
        ],

        [
            'key' => 'pixelcraft',
            'name' => 'PixelCraft Studio',
            'email' => 'matchtest.pixelcraft@masar.test',
            'city' => 'Cairo',
            'website' => 'https://pixelcraft.test',
            'fields' => ['Design'],
            'industries' => ['UI/UX Design'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'UI/UX Designer Intern', 'specialization' => 'UI/UX Design', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Product Design Trainee', 'specialization' => 'UI/UX Design', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '2600.00', 'status' => 'published'],
            ],
        ],

        [
            'key' => 'ledgerpro',
            'name' => 'LedgerPro Accounting',
            'email' => 'matchtest.ledgerpro@masar.test',
            'city' => 'Cairo',
            'website' => 'https://ledgerpro.test',
            'fields' => ['Accounting'],
            'industries' => ['Financial Accounting'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Accounting Intern', 'specialization' => 'Financial Accounting', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Financial Reporting Trainee', 'specialization' => 'Financial Accounting', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '2400.00', 'status' => 'published'],
            ],
        ],

        /*
         * Multi-industry companies.
         */

        [
            'key' => 'technova',
            'name' => 'TechNova Solutions',
            'email' => 'matchtest.technova@masar.test',
            'city' => 'Cairo',
            'website' => 'https://technova.test',
            'fields' => ['Computer Science'],
            'industries' => ['Software Engineering', 'Web Development', 'Artificial Intelligence', 'Data Science'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Backend Microservices Intern', 'specialization' => 'Software Engineering', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1, 'amount' => '4500.00', 'status' => 'published'],
                ['title' => 'Web Development Intern', 'specialization' => 'Web Development', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'AI Engineer Intern', 'specialization' => 'Artificial Intelligence', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 1, 'amount' => '5000.00', 'status' => 'published'],
                ['title' => 'Machine Learning Intern', 'specialization' => 'Artificial Intelligence', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'NLP Research Trainee', 'specialization' => 'Artificial Intelligence', 'type' => 'shadowing', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Data Insights Engineer Intern', 'specialization' => 'Data Science', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 1, 'amount' => '4200.00', 'status' => 'published'],
            ],
        ],

        [
            'key' => 'unionind',
            'name' => 'Union Industries Group',
            'email' => 'matchtest.unionind@masar.test',
            'city' => 'Alexandria',
            'website' => 'https://unionind.test',
            'fields' => ['Engineering'],
            'industries' => ['Mechanical Engineering', 'Electrical Engineering'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'CNC Maintenance Intern', 'specialization' => 'Mechanical Engineering', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 1, 'amount' => '2700.00', 'status' => 'published'],
                ['title' => 'Control Systems Trainee', 'specialization' => 'Electrical Engineering', 'type' => 'shadowing', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'Robotics Engineering Intern', 'specialization' => 'Mechanical Engineering', 'type' => 'project_based', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        [
            'key' => 'mediahub',
            'name' => 'MediaHub Agency',
            'email' => 'matchtest.mediahub@masar.test',
            'city' => 'Cairo',
            'website' => 'https://mediahub.test',
            'fields' => ['Media'],
            'industries' => ['Marketing', 'Digital Marketing', 'Content Creation'],
            'approval_status' => 'approved',
            'trainings' => [
                ['title' => 'Brand Campaign Intern', 'specialization' => 'Marketing', 'type' => 'hands_on', 'mode' => 'hybrid', 'paid' => 0, 'amount' => null, 'status' => 'published'],
                ['title' => 'SEO Content Intern', 'specialization' => 'Digital Marketing', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 1, 'amount' => '1800.00', 'status' => 'published'],
                ['title' => 'Video Content Creation Intern', 'specialization' => 'Content Creation', 'type' => 'hands_on', 'mode' => 'onsite', 'paid' => 0, 'amount' => null, 'status' => 'published'],
            ],
        ],

        /*
         * Unapproved company: trainings stay in draft, so nothing leaks
         * into listings while still allowing approval-rule inspection.
         */

        [
            'key' => 'ghostlabs',
            'name' => 'Ghost Startup Labs',
            'email' => 'matchtest.ghostlabs@masar.test',
            'city' => 'Cairo',
            'website' => 'https://ghostlabs.test',
            'fields' => ['Computer Science'],
            'industries' => ['Software Engineering'],
            'approval_status' => 'pending',
            'trainings' => [
                ['title' => 'Backend PHP Developer Intern', 'specialization' => 'Software Engineering', 'type' => 'hands_on', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'draft'],
                ['title' => 'React Frontend Intern', 'specialization' => 'Software Engineering', 'type' => 'project_based', 'mode' => 'remote', 'paid' => 0, 'amount' => null, 'status' => 'draft'],
            ],
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Student Dataset
|--------------------------------------------------------------------------
|
| Specialization "null" simulates a student who never completed
| the academic part of the profile.
|
*/

function matchtest_student_map(): array
{
    return [
        ['key' => 'omar', 'name' => 'Omar Hassan', 'field' => 'Engineering', 'specialization' => 'Mechanical Engineering', 'city' => 'Cairo'],
        ['key' => 'nada', 'name' => 'Nada Sherif', 'field' => 'Engineering', 'specialization' => 'Electrical Engineering', 'city' => 'Alexandria'],
        ['key' => 'sara', 'name' => 'Sara Adel', 'field' => 'Engineering', 'specialization' => 'Civil Engineering', 'city' => 'Cairo'],
        ['key' => 'karim', 'name' => 'Karim Fouad', 'field' => 'Computer Science', 'specialization' => 'Software Engineering', 'city' => 'Giza'],
        ['key' => 'laila', 'name' => 'Laila Mostafa', 'field' => 'Computer Science', 'specialization' => 'Data Science', 'city' => 'Cairo'],
        ['key' => 'ahmedzaki', 'name' => 'Ahmed Zaki', 'field' => 'Computer Science', 'specialization' => 'Artificial Intelligence', 'city' => 'Cairo'],
        ['key' => 'mona', 'name' => 'Mona Said', 'field' => 'Computer Science', 'specialization' => 'Web Development', 'city' => 'Alexandria'],
        ['key' => 'youssef', 'name' => 'Youssef Nabil', 'field' => 'Business', 'specialization' => 'Marketing', 'city' => 'Cairo'],
        ['key' => 'hala', 'name' => 'Hala Ramzy', 'field' => 'Business', 'specialization' => 'Human Resources', 'city' => 'Cairo'],
        ['key' => 'tarek', 'name' => 'Tarek Samir', 'field' => 'Accounting', 'specialization' => 'Financial Accounting', 'city' => 'Cairo'],
        ['key' => 'nour', 'name' => 'Nour Khaled', 'field' => 'Design', 'specialization' => 'UI/UX Design', 'city' => 'Cairo'],
        ['key' => 'rami', 'name' => 'Rami Anwar', 'field' => 'Law', 'specialization' => 'Corporate Law', 'city' => 'Cairo'],
        ['key' => 'dina', 'name' => 'Dina Ehab', 'field' => 'Medicine', 'specialization' => null, 'city' => 'Cairo'],
    ];
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function matchtest_find_or_create_lookup(
    PDO $pdo,
    string $table,
    string $name,
    bool &$was_created
): int {

    $statement = $pdo->prepare(
        "SELECT id FROM {$table} WHERE name = :name LIMIT 1"
    );
    $statement->execute([':name' => $name]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        $was_created = false;
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "INSERT INTO {$table} (name, is_active) VALUES (:name, 1)"
    );
    $insert->execute([':name' => $name]);

    $was_created = true;
    return (int) $pdo->lastInsertId();
}

function matchtest_find_or_create_user(
    PDO $pdo,
    string $role,
    string $email,
    bool &$was_created
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute([':email' => $email]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        $was_created = false;
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
            :role,
            :email,
            :password_hash,
            'active',
            NOW()
        )
        "
    );
    $insert->execute([
        ':role' => $role,
        ':email' => $email,
        ':password_hash' => password_hash(
            MATCHTEST_PASSWORD,
            PASSWORD_DEFAULT
        ),
    ]);

    $was_created = true;
    return (int) $pdo->lastInsertId();
}

function matchtest_find_or_create_company(
    PDO $pdo,
    array $company,
    int $user_id,
    bool &$was_created
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM companies WHERE user_id = :user_id LIMIT 1'
    );
    $statement->execute([':user_id' => $user_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        $was_created = false;
        return (int) $row['id'];
    }

    $approved = $company['approval_status'] === 'approved';

    $insert = $pdo->prepare(
        "
        INSERT INTO companies (
            user_id,
            legal_name,
            description,
            website,
            phone,
            city,
            approval_status,
            approved_at
        )
        VALUES (
            :user_id,
            :legal_name,
            :description,
            :website,
            :phone,
            :city,
            :approval_status,
            :approved_at
        )
        "
    );
    $insert->execute([
        ':user_id' => $user_id,
        ':legal_name' => MATCHTEST_COMPANY_PREFIX . ' ' . $company['name'],
        ':description' => 'Development test company seeded by training_matching_seeder for specialization-based matching verification.',
        ':website' => $company['website'],
        ':phone' => '+2010000000' . str_pad((string) (abs(crc32($company['key'])) % 100), 2, '0', STR_PAD_LEFT),
        ':city' => $company['city'],
        ':approval_status' => $company['approval_status'],
        ':approved_at' => $approved ? date('Y-m-d H:i:s') : null,
    ]);

    $was_created = true;
    return (int) $pdo->lastInsertId();
}

function matchtest_ensure_company_pivot_row(
    PDO $pdo,
    string $table,
    int $company_id,
    string $id_column,
    int $value_id
): bool {

    $check = $pdo->prepare(
        "
        SELECT 1
        FROM {$table}
        WHERE company_id = :company_id
          AND {$id_column} = :value_id
        LIMIT 1
        "
    );
    $check->execute([
        ':company_id' => $company_id,
        ':value_id' => $value_id,
    ]);

    if ($check->fetchColumn() !== false) {
        return false;
    }

    $insert = $pdo->prepare(
        "
        INSERT IGNORE INTO {$table} (
            company_id,
            {$id_column}
        )
        VALUES (
            :company_id,
            :value_id
        )
        "
    );
    $insert->execute([
        ':company_id' => $company_id,
        ':value_id' => $value_id,
    ]);

    return true;
}

function matchtest_ensure_training(
    PDO $pdo,
    int $company_id,
    array $training,
    string $city,
    int $specialization_id,
    bool &$was_created
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
        $was_created = false;
        $training_id = (int) $row['id'];
    } else {

        $is_paid = (int) $training['paid'] === 1;
        $status = $training['status'];

        $starts_at = date('Y-m-d H:i:s', strtotime('+7 days'));
        $ends_at = date('Y-m-d H:i:s', strtotime('+90 days'));
        $application_deadline = date('Y-m-d H:i:s', strtotime('+30 days'));
        $published_at = $status === 'published'
            ? date('Y-m-d H:i:s')
            : ($status === 'closed' ? date('Y-m-d H:i:s', strtotime('-60 days')) : null);
        $closed_at = $status === 'closed'
            ? date('Y-m-d H:i:s')
            : null;

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
                closed_at,
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
                :status,
                :published_at,
                :starts_at,
                :ends_at,
                :application_deadline,
                :closed_at,
                :location,
                NOW(),
                NOW()
            )
            "
        );
        $insert->execute([
            ':company_id' => $company_id,
            ':title' => $training['title'],
            ':description' => 'Realistic test opportunity: ' . $training['title'] . '. Part of the MASAR matching-test dataset.',
            ':training_type' => $training['type'],
            ':mode' => $training['mode'],
            ':is_paid' => $is_paid ? 1 : 0,
            ':compensation_amount' => $is_paid ? $training['amount'] : null,
            ':trial_period_days' => $is_paid ? 7 : null,
            ':status' => $status,
            ':published_at' => $published_at,
            ':starts_at' => $starts_at,
            ':ends_at' => $ends_at,
            ':application_deadline' => $application_deadline,
            ':closed_at' => $closed_at,
            ':location' => $city . ', Egypt',
        ]);

        $was_created = true;
        $training_id = (int) $pdo->lastInsertId();
    }

    /*
     | Link the training to its specialization so the existing
     | enrichment (specializations array in API responses) works.
     */
    matchtest_ensure_training_specialization(
        $pdo,
        $training_id,
        $specialization_id
    );

    return $training_id;
}

function matchtest_ensure_training_specialization(
    PDO $pdo,
    int $training_id,
    int $specialization_id
): void {

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
        ':specialization_id' => $specialization_id,
    ]);

    if ($check->fetchColumn() !== false) {
        return;
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
        ':specialization_id' => $specialization_id,
    ]);
}

function matchtest_find_or_create_student(
    PDO $pdo,
    array $student,
    int $user_id,
    int $field_id,
    ?int $specialization_id,
    bool &$was_created
): int {

    $statement = $pdo->prepare(
        'SELECT id FROM students WHERE user_id = :user_id LIMIT 1'
    );
    $statement->execute([':user_id' => $user_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($row)) {
        $was_created = false;
        return (int) $row['id'];
    }

    $insert = $pdo->prepare(
        "
        INSERT INTO students (
            user_id,
            full_name,
            bio,
            university_id,
            faculty_id,
            field_id,
            degree_id,
            specialization_id,
            graduation_year,
            city,
            is_profile_complete
        )
        VALUES (
            :user_id,
            :full_name,
            :bio,
            NULL,
            NULL,
            :field_id,
            NULL,
            :specialization_id,
            '2027',
            :city,
            1
        )
        "
    );
    $insert->execute([
        ':user_id' => $user_id,
        ':full_name' => MATCHTEST_STUDENT_PREFIX . ' ' . $student['name'],
        ':bio' => 'Development test student seeded by training_matching_seeder.',
        ':field_id' => $field_id,
        ':specialization_id' => $specialization_id,
        ':city' => $student['city'],
    ]);

    $was_created = true;
    return (int) $pdo->lastInsertId();
}

/*
|--------------------------------------------------------------------------
| Main Seeder
|--------------------------------------------------------------------------
*/

function seed_training_matching_data(PDO $pdo): void
{

    $stats = [
        'fields' => 0,
        'specializations' => 0,
        'users' => 0,
        'companies' => 0,
        'work_fields' => 0,
        'company_specializations' => 0,
        'trainings' => 0,
        'students' => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | 1. Study Fields + Specializations
    |--------------------------------------------------------------------------
    |
    | Reuses existing lookup rows whenever the name already exists
    | (both tables have a UNIQUE key on name).
    |
    */

    foreach (matchtest_field_specialization_map() as $field_name => $specialization_names) {

        $created = false;
        matchtest_find_or_create_lookup($pdo, 'study_fields', $field_name, $created);
        if ($created) {
            $stats['fields']++;
        }

        foreach ($specialization_names as $specialization_name) {
            $created = false;
            matchtest_find_or_create_lookup($pdo, 'specializations', $specialization_name, $created);
            if ($created) {
                $stats['specializations']++;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Companies (+ users, work fields, industries, trainings)
    |--------------------------------------------------------------------------
    */

    foreach (matchtest_company_map() as $company) {

        $created = false;

        $company_user_id = matchtest_find_or_create_user(
            $pdo,
            'company',
            $company['email'],
            $created
        );
        if ($created) {
            $stats['users']++;
        }

        $company_id = matchtest_find_or_create_company(
            $pdo,
            $company,
            $company_user_id,
            $created
        );
        if ($created) {
            $stats['companies']++;
        }

        foreach ($company['fields'] as $field_name) {
            $created = false;
            $field_id = matchtest_find_or_create_lookup($pdo, 'study_fields', $field_name, $created);

            if (
                matchtest_ensure_company_pivot_row(
                    $pdo,
                    'company_work_fields',
                    $company_id,
                    'field_id',
                    $field_id
                )
            ) {
                $stats['work_fields']++;
            }
        }

        foreach ($company['industries'] as $industry_name) {
            $created = false;
            $industry_id = matchtest_find_or_create_lookup($pdo, 'specializations', $industry_name, $created);

            if (
                matchtest_ensure_company_pivot_row(
                    $pdo,
                    'company_specializations',
                    $company_id,
                    'specialization_id',
                    $industry_id
                )
            ) {
                $stats['company_specializations']++;
            }
        }

        foreach ($company['trainings'] as $training) {

            $created = false;
            $created_lookups = false;
            $specialization_id = matchtest_find_or_create_lookup(
                $pdo,
                'specializations',
                $training['specialization'],
                $created_lookups
            );

            matchtest_ensure_training(
                $pdo,
                $company_id,
                $training,
                $company['city'],
                $specialization_id,
                $created
            );

            if ($created) {
                $stats['trainings']++;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Students
    |--------------------------------------------------------------------------
    */

    foreach (matchtest_student_map() as $student) {

        $created = false;

        $student_user_id = matchtest_find_or_create_user(
            $pdo,
            'student',
            'matchtest.' . $student['key'] . '@masar.test',
            $created
        );
        if ($created) {
            $stats['users']++;
        }

        $created = false;
        $field_id = matchtest_find_or_create_lookup(
            $pdo,
            'study_fields',
            $student['field'],
            $created
        );

        $specialization_id = null;

        if ($student['specialization'] !== null) {
            $created = false;
            $specialization_id = matchtest_find_or_create_lookup(
                $pdo,
                'specializations',
                $student['specialization'],
                $created
            );
        }

        $created = false;
        matchtest_find_or_create_student(
            $pdo,
            $student,
            $student_user_id,
            $field_id,
            $specialization_id,
            $created
        );

        if ($created) {
            $stats['students']++;
        }
    }

    echo 'Training matching seed data ready.' . PHP_EOL;
    echo '  study_fields created:            ' . $stats['fields'] . PHP_EOL;
    echo '  specializations created:         ' . $stats['specializations'] . PHP_EOL;
    echo '  users created:                   ' . $stats['users'] . PHP_EOL;
    echo '  companies created:               ' . $stats['companies'] . PHP_EOL;
    echo '  company_work_fields added:       ' . $stats['work_fields'] . PHP_EOL;
    echo '  company_specializations added:   ' . $stats['company_specializations'] . PHP_EOL;
    echo '  trainings created:               ' . $stats['trainings'] . PHP_EOL;
    echo '  students created:                ' . $stats['students'] . PHP_EOL;
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

        seed_training_matching_data($pdo);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Training matching seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
