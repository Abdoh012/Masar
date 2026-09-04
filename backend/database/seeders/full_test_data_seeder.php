<?php

/**
 * MASAR - Full Test Data Seeder
 *
 * Seeds a realistic, internally consistent development/test dataset with
 * synthetic Egyptian identities, realistic companies, trainings, skills,
 * applications, sessions, certificates, notifications and messaging.
 *
 * It reuses the existing lookup seeders (skills, universities, degrees,
 * faculties) so lookups always match what the application expects, then
 * inserts business data. It also removes the orphaned leftover test rows
 * from a previous test run.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function masar_seed_lookups(PDO $pdo): array
{
    require_once __DIR__ . '/skills_seeder.php';
    require_once __DIR__ . '/universities_seeder.php';
    require_once __DIR__ . '/degrees_seeder.php';
    require_once __DIR__ . '/faculties_seeder.php';

    seed_skills($pdo);
    seed_universities($pdo);
    seed_degrees($pdo);
    seed_faculties($pdo);

    $byName = function (string $table, string $col) use ($pdo): array {
        $map = [];
        $stmt = $pdo->query("SELECT id, {$col} AS name FROM {$table} WHERE is_active = 1");
        foreach ($stmt as $row) {
            $map[$row['name']] = (int) $row['id'];
        }
        return $map;
    };

    return [
        'study_fields' => $byName('study_fields', 'name'),
        'specializations' => $byName('specializations', 'name'),
        'skills' => $byName('skills', 'name'),
        'universities' => $byName('universities', 'name'),
        'degrees' => $byName('degrees', 'name'),
    ];
}

function masar_delete_orphans(PDO $pdo): void
{
    $pdo->exec("DELETE FROM students WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM training_specializations WHERE training_id NOT IN (SELECT id FROM training_listings)");
    $pdo->exec("DELETE FROM refresh_tokens WHERE user_id NOT IN (SELECT id FROM users)");
}

function masar_clean_business_data(PDO $pdo): void
{
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ([
        'application_answers', 'training_applications', 'training_questions', 'training_sessions',
        'certificates', 'certificate_appeals', 'conversations', 'messages', 'saved_trainings',
        'student_skills', 'training_skills', 'training_specializations', 'company_specializations',
        'company_work_fields', 'students', 'companies', 'training_listings', 'notifications',
        'refresh_tokens', 'auth_tokens', 'verification_tokens', 'password_resets', 'files',
        'payments', 'audit_logs', 'users',
    ] as $table) {
        $pdo->exec("DELETE FROM `{$table}`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}

function masar_insert_user(PDO $pdo, string $role, string $email, string $password, string $status = 'active'): int
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO users (role, email, password_hash, status, email_verified_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())"
    );
    $stmt->execute([$role, $email, $hash, $status]);
    $id = (int) $pdo->lastInsertId();
    if ($status === 'active') {
        $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ?")->execute([$id]);
    }
    return $id;
}

function masar_insert_student(PDO $pdo, int $user_id, array $data): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO students (user_id, full_name, phone, bio, university_id, faculty_id, field_id, degree_id, specialization_id, graduation_year, city, is_profile_complete, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())"
    );
    $stmt->execute([
        $user_id,
        $data['full_name'],
        $data['phone'] ?? null,
        $data['bio'] ?? null,
        $data['university_id'] ?? null,
        $data['faculty_id'] ?? null,
        $data['field_id'],
        $data['degree_id'] ?? null,
        $data['specialization_id'],
        $data['graduation_year'] ?? null,
        $data['city'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_add_student_skills(PDO $pdo, int $student_id, array $skills, array $skill_ids): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO student_skills (student_id, skill_id, proficiency, created_at) VALUES (?, ?, ?, NOW())"
    );
    $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
    $i = 0;
    foreach ($skills as $name) {
        $id = $skill_ids[$name] ?? null;
        if ($id === null) {
            continue;
        }
        $stmt->execute([$student_id, $id, $levels[$i % count($levels)]]);
        $i++;
    }
}

function masar_insert_company(PDO $pdo, int $user_id, array $data): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO companies (user_id, legal_name, description, website, phone, city, address, approval_status, approved_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', NOW(), NOW(), NOW())"
    );
    $stmt->execute([
        $user_id,
        $data['legal_name'],
        $data['description'] ?? null,
        $data['website'] ?? null,
        $data['phone'] ?? null,
        $data['city'] ?? null,
        $data['address'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_add_company_links(PDO $pdo, int $company_id, array $field_names, array $field_ids, array $spec_names, array $spec_ids): void
{
    $wf = $pdo->prepare("INSERT IGNORE INTO company_work_fields (company_id, field_id, created_at) VALUES (?, ?, NOW())");
    foreach ($field_names as $name) {
        if (isset($field_ids[$name])) {
            $wf->execute([$company_id, $field_ids[$name]]);
        }
    }
    $cs = $pdo->prepare("INSERT IGNORE INTO company_specializations (company_id, specialization_id, created_at) VALUES (?, ?, NOW())");
    foreach ($spec_names as $name) {
        if (isset($spec_ids[$name])) {
            $cs->execute([$company_id, $spec_ids[$name]]);
        }
    }
}

function masar_insert_training(PDO $pdo, array $d): int
{
    $status = $d['status'] ?? 'published';
    $stmt = $pdo->prepare(
        "INSERT INTO training_listings
            (company_id, specialization_id, title, description, training_type, mode, may_lead_to_employment, is_paid,
             compensation_amount, compensation_currency, trial_period_days, capacity, status,
             published_at, starts_at, ends_at, application_deadline, location, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'EGP', ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
    );
    $is_paid = !empty($d['is_paid']) ? 1 : 0;
    $stmt->execute([
        $d['company_id'],
        $d['specialization_id'],
        $d['title'],
        $d['description'],
        $d['training_type'],
        $d['mode'],
        !empty($d['may_lead_to_employment']) ? 1 : 0,
        $is_paid,
        $is_paid ? ($d['compensation_amount'] ?? null) : null,
        $is_paid ? ($d['trial_period_days'] ?? 7) : null,
        $d['capacity'] ?? null,
        $status,
        $status === 'published' ? ($d['published_at'] ?? date('Y-m-d H:i:s')) : null,
        $d['starts_at'] ?? null,
        $d['ends_at'] ?? null,
        $d['application_deadline'] ?? null,
        $d['location'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_add_training_links(PDO $pdo, int $training_id, array $skills, array $skill_ids, array $specs, array $spec_ids): void
{
    $tsk = $pdo->prepare("INSERT INTO training_skills (training_id, skill_id) VALUES (?, ?)");
    foreach ($skills as $name) {
        if (isset($skill_ids[$name])) {
            $tsk->execute([$training_id, $skill_ids[$name]]);
        }
    }
    $tsp = $pdo->prepare("INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)");
    foreach ($specs as $name) {
        if (isset($spec_ids[$name])) {
            $tsp->execute([$training_id, $spec_ids[$name]]);
        }
    }
}

function masar_insert_question(PDO $pdo, int $training_id, string $question, string $type, int $required, ?string $options, int $sort): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO training_questions (training_id, question, question_type, required, options, sort_order, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute([$training_id, $question, $type, $required, $options, $sort]);
    return (int) $pdo->lastInsertId();
}

function masar_insert_application(PDO $pdo, int $training_id, int $company_id, array $s, string $status, array $extra = []): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO training_applications
            (training_id, student_id, company_id, message, full_name, email, phone, city, address,
             why_interested, what_to_learn, skills, status, rejection_reason, rejection_note,
             applied_at, reviewed_at, withdrawn_at, reviewed_by, cv_file_id, university, faculty_id,
             applicant_type, academic_year, graduation_year, motivation)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $reviewed_at = in_array($status, ['accepted', 'rejected'], true) ? ($extra['reviewed_at'] ?? date('Y-m-d H:i:s')) : null;
    $stmt->execute([
        $training_id,
        $s['sid'],
        $company_id,
        $extra['message'] ?? null,
        $s['full_name'] ?? null,
        $s['email'] ?? null,
        $s['phone'] ?? null,
        $s['city'] ?? null,
        $extra['address'] ?? null,
        $extra['why_interested'] ?? null,
        $extra['what_to_learn'] ?? null,
        $extra['skills'] ?? null,
        $status,
        $status === 'rejected' ? ($extra['rejection_reason'] ?? 'candidate_not_suitable') : null,
        $status === 'rejected' ? ($extra['rejection_note'] ?? null) : null,
        $extra['applied_at'] ?? date('Y-m-d H:i:s'),
        $reviewed_at,
        $status === 'withdrawn' ? ($extra['withdrawn_at'] ?? date('Y-m-d H:i:s')) : null,
        $extra['reviewed_by'] ?? null,
        $extra['cv_file_id'] ?? null,
        $s['university'] ?? null,
        $s['faculty_id'] ?? null,
        $extra['applicant_type'] ?? 'student',
        $extra['academic_year'] ?? null,
        $s['graduation_year'] ?? null,
        $extra['motivation'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_insert_session(PDO $pdo, int $application_id, int $training_id, int $student_id, int $company_id, string $status, array $dates): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO training_sessions
            (application_id, training_id, student_id, company_id, status, started_at, trial_started_at,
             trial_ends_at, student_continuation_confirmed_at, actual_ended_at, employment_opportunity, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
    );
    $stmt->execute([
        $application_id,
        $training_id,
        $student_id,
        $company_id,
        $status,
        $dates['started_at'],
        $dates['trial_started_at'] ?? null,
        $dates['trial_ends_at'] ?? null,
        $dates['confirmed_at'] ?? null,
        $dates['ended_at'] ?? null,
        !empty($dates['employment_opportunity']) ? 1 : 0,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_insert_certificate(PDO $pdo, array $c): int
{
    $code = $c['code'] ?? ('MASAR-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4))));
    $stmt = $pdo->prepare(
        "INSERT INTO certificates
            (certificate_code, student_id, company_id, training_id, training_session_id, status, title,
             start_date, end_date, grade, grade_label, employment_eligible, requested_at, reviewed_at,
             approved_at, reviewed_by, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())"
    );
    $stmt->execute([
        $code,
        $c['student_id'],
        $c['company_id'],
        $c['training_id'],
        $c['training_session_id'],
        $c['status'],
        $c['title'],
        $c['start_date'],
        $c['end_date'],
        $c['grade'] ?? null,
        $c['grade_label'] ?? null,
        !empty($c['employment_eligible']) ? 1 : 0,
        $c['reviewed_at'] ?? null,
        $c['approved_at'] ?? null,
        $c['reviewed_by'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function masar_insert_notification(PDO $pdo, int $user_id, string $type, string $title, string $body, ?string $entity_type, ?int $entity_id, int $is_read = 0, string $created_at = 'NOW()'): int
{
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, type, title, body, entity_type, entity_id, is_read, read_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NULL, " . $created_at . ")"
    );
    $stmt->execute([$user_id, $type, $title, $body, $entity_type, $entity_id, $is_read]);
    return (int) $pdo->lastInsertId();
}

function masar_seed(PDO $pdo): void
{
    echo "Deleting orphaned leftover test rows...\n";
    masar_delete_orphans($pdo);

    echo "Clearing previous business data...\n";
    masar_clean_business_data($pdo);

    echo "Seeding lookup tables (skills, universities, degrees, faculties)...\n";
    $lookup = masar_seed_lookups($pdo);
    $sf = $lookup['study_fields'];
    $specs = $lookup['specializations'];
    $skills = $lookup['skills'];
    $unis = $lookup['universities'];
    $degs = $lookup['degrees'];

    $pdo->beginTransaction();

    // Resolve a few faculty ids by (university, faculty name) so student rows can reference them.
    $faculty = [];
    $fstmt = $pdo->query("SELECT f.id, f.name, u.name univ FROM faculties f JOIN universities u ON u.id = f.university_id");
    foreach ($fstmt as $row) {
        $faculty[$row['univ'] . '|' . $row['name']] = (int) $row['id'];
    }

    echo "Seeding users & students...\n";

    // ---- Admin ----
    $admin_user = masar_insert_user($pdo, 'admin', 'admin@test.local', 'TestAdmin@123', 'active');

    // ---- Students ----
    $students = []; // key => [user_id, student_id, ...]

    $defs = [
        'student@test.local' => [
            'full_name' => 'Omar Khaled Abdelrahman', 'phone' => '01012345678',
            'university' => 'Cairo University', 'faculty' => 'Faculty of Computers and Artificial Intelligence',
            'field' => 'Computer Science', 'specialization' => 'Software Engineering',
            'degree' => 'Bachelor of Computer Science', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'Final-year software engineering student with a passion for backend development and scalable systems.',
            'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL', 'Git', 'REST API', 'Problem Solving', 'English'],
            'email' => 'student@test.local', 'password' => 'TestStudent@123',
        ],
        'sara.mostafa@test.local' => [
            'full_name' => 'Sara Mostafa Elgendy', 'phone' => '01098765432',
            'university' => 'Ain Shams University', 'faculty' => 'Faculty of Computer and Information Sciences',
            'field' => 'Computer Science', 'specialization' => 'Data Science',
            'degree' => 'Bachelor of Computer and Information Sciences', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'Aspiring data scientist focused on machine learning and data visualization.',
            'skills' => ['Python', 'Machine Learning', 'Data Analysis', 'Data Visualization', 'MySQL', 'Communication'],
            'email' => 'sara.mostafa@test.local', 'password' => 'SaraData@2026!',
        ],
        'ahmed.hassan@test.local' => [
            'full_name' => 'Ahmed Hassan Mahmoud', 'phone' => '01110001111',
            'university' => 'Alexandria University', 'faculty' => 'Faculty of Engineering',
            'field' => 'Engineering', 'specialization' => 'Mechanical Engineering',
            'degree' => 'Bachelor of Engineering', 'graduation_year' => 2026, 'city' => 'Alexandria',
            'bio' => 'Mechanical engineering graduate interested in manufacturing and industrial training.',
            'skills' => ['Project Management', 'Teamwork', 'Communication', 'Problem Solving', 'English'],
            'email' => 'ahmed.hassan@test.local', 'password' => 'AhmedMech@2026!',
        ],
        'laila.nabil@test.local' => [
            'full_name' => 'Laila Nabil Fathy', 'phone' => '01055554444',
            'university' => 'Cairo University', 'faculty' => 'Faculty of Medicine',
            'field' => 'Medicine', 'specialization' => 'General Medicine',
            'degree' => 'Bachelor of Medicine and Surgery', 'graduation_year' => 2028, 'city' => 'Cairo',
            'bio' => 'Medical student seeking clinical training and hands-on hospital experience.',
            'skills' => ['Communication', 'Problem Solving', 'Time Management', 'English'],
            'email' => 'laila.nabil@test.local', 'password' => 'LailaMed@2026!',
        ],
        'youssef.tarek@test.local' => [
            'full_name' => 'Youssef Tarek Zaki', 'phone' => '01222223333',
            'university' => 'German University in Cairo', 'faculty' => 'Faculty of Management Technology',
            'field' => 'Business', 'specialization' => 'Marketing',
            'degree' => 'Bachelor of Business Administration', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'Marketing student passionate about digital campaigns and brand storytelling.',
            'skills' => ['Digital Marketing', 'SEO', 'Communication', 'Content Writing', 'Leadership'],
            'email' => 'youssef.tarek@test.local', 'password' => 'YoussefBiz@2026!',
        ],
        'nour.adel@test.local' => [
            'full_name' => 'Nour Adel Sherif', 'phone' => '01033334444',
            'university' => 'The American University in Cairo', 'faculty' => 'Faculty of Fine Arts',
            'field' => 'Design', 'specialization' => 'UI/UX Design',
            'degree' => 'Bachelor of Fine Arts', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'UI/UX designer who loves crafting accessible, user-centered product experiences.',
            'skills' => ['Figma', 'UI Design', 'UX Design', 'Adobe Photoshop', 'Content Writing'],
            'email' => 'nour.adel@test.local', 'password' => 'NourDesign@2026!',
        ],
        'karim.adel@test.local' => [
            'full_name' => 'Karim Adel Mansour', 'phone' => '01288889999',
            'university' => 'Cairo University', 'faculty' => 'Faculty of Law',
            'field' => 'Law', 'specialization' => 'Corporate Law',
            'degree' => 'Bachelor of Laws', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'Law student focused on corporate and commercial law, seeking legal internship.',
            'skills' => ['Communication', 'Problem Solving', 'English', 'Teamwork'],
            'email' => 'karim.adel@test.local', 'password' => 'KarimLaw@2026!',
        ],
        'hana.sameh@test.local' => [
            'full_name' => 'Hana Sameh Kamal', 'phone' => '01177776666',
            'university' => 'Alexandria University', 'faculty' => 'Faculty of Pharmacy',
            'field' => 'Pharmacy', 'specialization' => 'Clinical Pharmacy',
            'degree' => 'Bachelor of Pharmacy', 'graduation_year' => 2028, 'city' => 'Alexandria',
            'bio' => 'Pharmacy student with an interest in clinical pharmacy and pharmaceutical care.',
            'skills' => ['Communication', 'Time Management', 'English', 'Problem Solving'],
            'email' => 'hana.sameh@test.local', 'password' => 'HanaPharm@2026!',
        ],
        'mariam.samir@test.local' => [
            'full_name' => 'Mariam Samir Awad', 'phone' => '01066665555',
            'university' => 'Cairo University', 'faculty' => 'Faculty of Commerce',
            'field' => 'Accounting', 'specialization' => 'Financial Accounting',
            'degree' => 'Bachelor of Commerce', 'graduation_year' => 2026, 'city' => 'Giza',
            'bio' => 'Accounting graduate interested in auditing and financial reporting.',
            'skills' => ['Data Analysis', 'Problem Solving', 'Communication', 'Time Management'],
            'email' => 'mariam.samir@test.local', 'password' => 'MariamAcc@2026!',
        ],
        'ibrahim.hossam@test.local' => [
            'full_name' => 'Ibrahim Hossam Eldin', 'phone' => '01044445555',
            'university' => 'Cairo University', 'faculty' => 'Faculty of Computers and Artificial Intelligence',
            'field' => 'Computer Science', 'specialization' => 'Artificial Intelligence',
            'degree' => 'Bachelor of Computer Science', 'graduation_year' => 2027, 'city' => 'Cairo',
            'bio' => 'AI enthusiast building machine learning models for real-world problems.',
            'skills' => ['Python', 'Machine Learning', 'Deep Learning', 'Data Science', 'Git'],
            'email' => 'ibrahim.hossam@test.local', 'password' => 'IbrahimAI@2026!',
        ],
    ];

    foreach ($defs as $email => $d) {
        $uid = masar_insert_user($pdo, 'student', $email, $d['password'], 'active');
        $sid = masar_insert_student($pdo, $uid, [
            'full_name' => $d['full_name'],
            'phone' => $d['phone'],
            'bio' => $d['bio'],
            'university_id' => $unis[$d['university']] ?? null,
            'faculty_id' => $faculty[$d['university'] . '|' . $d['faculty']] ?? null,
            'field_id' => $sf[$d['field']],
            'degree_id' => $degs[$d['degree']] ?? null,
            'specialization_id' => $specs[$d['specialization']],
            'graduation_year' => $d['graduation_year'],
            'city' => $d['city'],
        ]);
        masar_add_student_skills($pdo, $sid, $d['skills'], $skills);
        $students[$email] = [
            'uid' => $uid,
            'sid' => $sid,
            'full_name' => $d['full_name'],
            'phone' => $d['phone'],
            'city' => $d['city'],
            'university_id' => $unis[$d['university']] ?? null,
            'university' => $d['university'],
            'field_id' => $sf[$d['field']],
            'specialization_id' => $specs[$d['specialization']],
            'graduation_year' => $d['graduation_year'],
            'email' => $email,
            'password' => $d['password'],
        ];
    }

    echo "Seeding companies...\n";

    // ---- Companies ----
    $companies = [];
    $cdefs = [
        'company@test.local' => [
            'legal_name' => 'Nile Software Solutions',
            'description' => 'Cairo-based software house delivering web and mobile solutions for enterprises across the region.',
            'website' => 'https://nilesoft.example.com', 'phone' => '+201000000001', 'city' => 'Cairo',
            'address' => 'Maadi, Cairo',
            'fields' => ['Computer Science'], 'specs' => ['Software Engineering', 'Artificial Intelligence', 'Web Development'],
            'email' => 'company@test.local', 'password' => 'TestCompany@123',
        ],
        'delta.engineering@test.local' => [
            'legal_name' => 'Delta Engineering Group',
            'description' => 'Alexandria-based engineering firm specializing in mechanical and electrical systems.',
            'website' => 'https://deltaeng.example.com', 'phone' => '+201000000002', 'city' => 'Alexandria',
            'address' => 'Smouha, Alexandria',
            'fields' => ['Engineering'], 'specs' => ['Mechanical Engineering', 'Electrical Engineering'],
            'email' => 'delta.engineering@test.local', 'password' => 'DeltaEng@2026!',
        ],
        'cairo.medical@test.local' => [
            'legal_name' => 'Cairo Medical Center',
            'description' => 'Multidisciplinary private medical center in downtown Cairo offering clinical internships.',
            'website' => 'https://cairomedical.example.com', 'phone' => '+201000000003', 'city' => 'Cairo',
            'address' => 'Downtown, Cairo',
            'fields' => ['Medicine'], 'specs' => ['General Medicine'],
            'email' => 'cairo.medical@test.local', 'password' => 'CairoMed@2026!',
        ],
        'brightreach.marketing@test.local' => [
            'legal_name' => 'BrightReach Marketing',
            'description' => 'Full-service digital marketing agency operating across Egypt and the Gulf.',
            'website' => 'https://brightreach.example.com', 'phone' => '+201000000004', 'city' => 'Giza',
            'address' => 'Dokki, Giza',
            'fields' => ['Business', 'Media'], 'specs' => ['Marketing', 'Digital Marketing'],
            'email' => 'brightreach.marketing@test.local', 'password' => 'BrightMkt@2026!',
        ],
        'pharmalife.egypt@test.local' => [
            'legal_name' => 'PharmaLife Egypt',
            'description' => 'Pharmaceutical company manufacturing and distributing healthcare products nationwide.',
            'website' => 'https://pharmalife.example.com', 'phone' => '+201000000005', 'city' => 'Cairo',
            'address' => 'Nasr City, Cairo',
            'fields' => ['Pharmacy'], 'specs' => ['Clinical Pharmacy'],
            'email' => 'pharmalife.egypt@test.local', 'password' => 'PharmaLife@2026!',
        ],
        'themis.law@test.local' => [
            'legal_name' => 'Themis Law Partners',
            'description' => 'Corporate law firm advising startups and established businesses in Cairo.',
            'website' => 'https://themis.example.com', 'phone' => '+201000000006', 'city' => 'Cairo',
            'address' => 'Zamalek, Cairo',
            'fields' => ['Law'], 'specs' => ['Corporate Law'],
            'email' => 'themis.law@test.local', 'password' => 'ThemisLaw@2026!',
        ],
        'pixelcraft.studio@test.local' => [
            'legal_name' => 'PixelCraft Studio',
            'description' => 'Digital product design studio crafting user interfaces for mobile and web.',
            'website' => 'https://pixelcraft.example.com', 'phone' => '+201000000007', 'city' => 'Cairo',
            'address' => 'New Cairo, Cairo',
            'fields' => ['Design'], 'specs' => ['UI/UX Design'],
            'email' => 'pixelcraft.studio@test.local', 'password' => 'PixelCraft@2026!',
        ],
        'ledgerpro.accounting@test.local' => [
            'legal_name' => 'LedgerPro Accounting',
            'description' => 'Accounting and audit firm providing financial reporting services for SMEs.',
            'website' => 'https://ledgerpro.example.com', 'phone' => '+201000000008', 'city' => 'Giza',
            'address' => 'Mohandessin, Giza',
            'fields' => ['Accounting'], 'specs' => ['Financial Accounting'],
            'email' => 'ledgerpro.accounting@test.local', 'password' => 'LedgerPro@2026!',
        ],
    ];

    foreach ($cdefs as $email => $d) {
        $uid = masar_insert_user($pdo, 'company', $email, $d['password'], 'active');
        $cid = masar_insert_company($pdo, $uid, [
            'legal_name' => $d['legal_name'],
            'description' => $d['description'],
            'website' => $d['website'],
            'phone' => $d['phone'],
            'city' => $d['city'],
            'address' => $d['address'],
        ]);
        masar_add_company_links($pdo, $cid, $d['fields'], $sf, $d['specs'], $specs);
        $companies[$email] = [
            'uid' => $uid,
            'cid' => $cid,
            'legal_name' => $d['legal_name'],
            'city' => $d['city'],
            'email' => $email,
            'password' => $d['password'],
        ];
    }

    echo "Seeding trainings...\n";

    // ---- Trainings ----
    $now = time();
    $day = 86400;
    $trainings = []; // key => id

    // Build helper to get a future/past datetime relative to today
    $dt = function (int $offsetDays, int $hour = 9) use ($now, $day) {
        return date('Y-m-d H:i:s', $now + $offsetDays * $day + ($hour - date('G', $now)) * 3600);
    };

    $tdefs = [
        'backend-intern' => [
            'company' => 'company@test.local',
            'title' => 'Backend Engineering Internship',
            'description' => 'Hands-on internship building RESTful APIs and internal tools with PHP and Laravel.',
            'training_type' => 'hands_on', 'mode' => 'remote', 'is_paid' => 1, 'compensation_amount' => 3000,
            'may_lead_to_employment' => 1, 'capacity' => 5,
            'starts_at' => $dt(15), 'ends_at' => $dt(105), 'application_deadline' => $dt(10),
            'location' => 'Cairo (Remote)',
            'skills' => ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git'],
            'specs' => ['Software Engineering', 'Web Development'],
        ],
        'ai-intern' => [
            'company' => 'company@test.local',
            'title' => 'AI & Machine Learning Internship',
            'description' => 'Work on data pipelines and machine learning prototypes with the AI team.',
            'training_type' => 'project_based', 'mode' => 'hybrid', 'is_paid' => 1, 'compensation_amount' => 4500,
            'may_lead_to_employment' => 1, 'capacity' => 3,
            'starts_at' => $dt(20), 'ends_at' => $dt(120), 'application_deadline' => $dt(14),
            'location' => 'Maadi, Cairo',
            'skills' => ['Python', 'Machine Learning', 'Deep Learning', 'Data Analysis', 'Git'],
            'specs' => ['Artificial Intelligence', 'Software Engineering'],
        ],
        'frontend-shadow' => [
            'company' => 'company@test.local',
            'title' => 'Frontend Developer Shadowing Program',
            'description' => 'Shadow senior frontend engineers and learn modern JavaScript workflow.',
            'training_type' => 'shadowing', 'mode' => 'remote', 'is_paid' => 0,
            'may_lead_to_employment' => 0, 'capacity' => 5,
            'starts_at' => $dt(5), 'ends_at' => $dt(55), 'application_deadline' => $dt(3),
            'location' => 'Remote',
            'skills' => ['JavaScript', 'React', 'HTML', 'CSS', 'Tailwind CSS'],
            'specs' => ['Web Development'],
        ],
        'mech-drafting' => [
            'company' => 'delta.engineering@test.local',
            'title' => 'Mechanical CAD Drafting Internship',
            'description' => 'Learn mechanical drafting and 3D modeling in an engineering consultancy.',
            'training_type' => 'hands_on', 'mode' => 'onsite', 'is_paid' => 1, 'compensation_amount' => 2000,
            'may_lead_to_employment' => 1, 'capacity' => 4,
            'starts_at' => $dt(10), 'ends_at' => $dt(80), 'application_deadline' => $dt(6),
            'location' => 'Smouha, Alexandria',
            'skills' => ['Teamwork', 'Project Management', 'Communication'],
            'specs' => ['Mechanical Engineering'],
        ],
        'clinical-pharm' => [
            'company' => 'pharmalife.egypt@test.local',
            'title' => 'Clinical Pharmacy Training',
            'description' => 'Hands-on training in pharmaceutical care and clinical skills.',
            'training_type' => 'hands_on', 'mode' => 'onsite', 'is_paid' => 0,
            'may_lead_to_employment' => 1, 'capacity' => 6,
            'starts_at' => $dt(7), 'ends_at' => $dt(70), 'application_deadline' => $dt(4),
            'location' => 'Nasr City, Cairo',
            'skills' => ['Communication', 'Time Management'],
            'specs' => ['Clinical Pharmacy'],
        ],
        'marketing-campaign' => [
            'company' => 'brightreach.marketing@test.local',
            'title' => 'Digital Marketing Campaign Intern',
            'description' => 'Join our growth team to plan and run digital marketing campaigns.',
            'training_type' => 'project_based', 'mode' => 'hybrid', 'is_paid' => 1, 'compensation_amount' => 2500,
            'may_lead_to_employment' => 0, 'capacity' => 4,
            'starts_at' => $dt(12), 'ends_at' => $dt(75), 'application_deadline' => $dt(8),
            'location' => 'Dokki, Giza',
            'skills' => ['Digital Marketing', 'SEO', 'Content Writing', 'Communication'],
            'specs' => ['Marketing', 'Digital Marketing'],
        ],
        'ui-ux-intern' => [
            'company' => 'pixelcraft.studio@test.local',
            'title' => 'UI/UX Design Internship',
            'description' => 'Practice end-to-end product design in a fast-paced studio.',
            'training_type' => 'hands_on', 'mode' => 'onsite', 'is_paid' => 1, 'compensation_amount' => 2200,
            'may_lead_to_employment' => 1, 'capacity' => 3,
            'starts_at' => $dt(18), 'ends_at' => $dt(95), 'application_deadline' => $dt(12),
            'location' => 'New Cairo, Cairo',
            'skills' => ['Figma', 'UI Design', 'UX Design', 'Adobe Photoshop'],
            'specs' => ['UI/UX Design'],
        ],
        'corporate-law-shadow' => [
            'company' => 'themis.law@test.local',
            'title' => 'Corporate Law Shadowing',
            'description' => 'Shadow corporate lawyers on contracts and regulatory matters.',
            'training_type' => 'shadowing', 'mode' => 'onsite', 'is_paid' => 0,
            'may_lead_to_employment' => 0, 'capacity' => 2,
            'starts_at' => $dt(9), 'ends_at' => $dt(60), 'application_deadline' => $dt(5),
            'location' => 'Zamalek, Cairo',
            'skills' => ['Communication', 'Problem Solving'],
            'specs' => ['Corporate Law'],
        ],
        'audit-intern' => [
            'company' => 'ledgerpro.accounting@test.local',
            'title' => 'Audit & Financial Reporting Intern',
            'description' => 'Assist auditors with financial reporting and analysis.',
            'training_type' => 'hands_on', 'mode' => 'onsite', 'is_paid' => 1, 'compensation_amount' => 1800,
            'may_lead_to_employment' => 1, 'capacity' => 3,
            'starts_at' => $dt(14), 'ends_at' => $dt(85), 'application_deadline' => $dt(9),
            'location' => 'Mohandessin, Giza',
            'skills' => ['Data Analysis', 'Problem Solving', 'Communication'],
            'specs' => ['Financial Accounting'],
        ],
        'medical-intern' => [
            'company' => 'cairo.medical@test.local',
            'title' => 'Clinical Medicine Internship',
            'description' => 'Clinical rotation internship across internal medicine departments.',
            'training_type' => 'hands_on', 'mode' => 'onsite', 'is_paid' => 0,
            'may_lead_to_employment' => 0, 'capacity' => 5,
            'starts_at' => $dt(25), 'ends_at' => $dt(110), 'application_deadline' => $dt(20),
            'location' => 'Downtown, Cairo',
            'skills' => ['Communication', 'Problem Solving', 'Time Management'],
            'specs' => ['General Medicine'],
        ],
        // A past / completed training used for certificates
        'frontend-bootcamp' => [
            'company' => 'company@test.local',
            'title' => 'Frontend Web Development Bootcamp',
            'description' => 'Completed six-week bootcamp that already ran. Used to test certificates.',
            'training_type' => 'hands_on', 'mode' => 'remote', 'is_paid' => 0,
            'may_lead_to_employment' => 0, 'capacity' => 8,
            'starts_at' => $dt(-90), 'ends_at' => $dt(-45), 'application_deadline' => $dt(-95),
            'location' => 'Remote',
            'skills' => ['JavaScript', 'React', 'HTML', 'CSS'],
            'specs' => ['Web Development'],
        ],
        'data-analytics' => [
            'company' => 'company@test.local',
            'title' => 'Data Analytics Internship',
            'description' => 'Analyze business data and build dashboards for product decisions.',
            'training_type' => 'hands_on', 'mode' => 'hybrid', 'is_paid' => 1, 'compensation_amount' => 2800,
            'may_lead_to_employment' => 1, 'capacity' => 4,
            'starts_at' => $dt(16), 'ends_at' => $dt(100), 'application_deadline' => $dt(11),
            'location' => 'Cairo (Hybrid)',
            'skills' => ['Data Analysis', 'Python', 'Data Visualization', 'MySQL'],
            'specs' => ['Data Science'],
        ],
    ];

    foreach ($tdefs as $key => $d) {
        $company = $companies[$d['company']];
        $primary_spec_id = isset($d['specs'][0], $specs[$d['specs'][0]])
            ? $specs[$d['specs'][0]]
            : 0;
        $tid = masar_insert_training($pdo, [
            'company_id' => $company['cid'],
            'specialization_id' => $primary_spec_id,
            'title' => $d['title'],
            'description' => $d['description'],
            'training_type' => $d['training_type'],
            'mode' => $d['mode'],
            'is_paid' => $d['is_paid'],
            'compensation_amount' => $d['compensation_amount'] ?? null,
            'may_lead_to_employment' => $d['may_lead_to_employment'],
            'capacity' => $d['capacity'],
            'status' => 'published',
            'starts_at' => $d['starts_at'],
            'ends_at' => $d['ends_at'],
            'application_deadline' => $d['application_deadline'],
            'location' => $d['location'],
        ]);
        masar_add_training_links($pdo, $tid, $d['skills'], $skills, $d['specs'], $specs);
        $trainings[$key] = $tid;
    }

    echo "Seeding training questions...\n";
    // Questions for the backend-intern training
    $q1 = masar_insert_question($pdo, $trainings['backend-intern'], 'Why do you want to join this internship?', 'textarea', 1, null, 1);
    $q2 = masar_insert_question($pdo, $trainings['backend-intern'], 'Which programming languages are you comfortable with?', 'select', 0, 'PHP,JavaScript,Python,Java', 2);
    $q3 = masar_insert_question($pdo, $trainings['backend-intern'], 'Are you available to commit 20 hours per week?', 'radio', 1, 'Yes,No', 3);
    $qs = [
        'backend-intern' => ['q1' => $q1, 'q2' => $q2, 'q3' => $q3],
    ];
    // Questions for ui-ux-intern
    $q4 = masar_insert_question($pdo, $trainings['ui-ux-intern'], 'Describe a product you recently designed.', 'textarea', 1, null, 1);
    $qs['ui-ux-intern'] = ['q4' => $q4];

    echo "Seeding saved trainings...\n";
    $save = $pdo->prepare("INSERT IGNORE INTO saved_trainings (student_id, training_id, created_at) VALUES (?, ?, NOW())");
    $save->execute([$students['student@test.local']['sid'], $trainings['ai-intern']]);
    $save->execute([$students['student@test.local']['sid'], $trainings['marketing-campaign']]);
    $save->execute([$students['sara.mostafa@test.local']['sid'], $trainings['data-analytics']]);
    $save->execute([$students['youssef.tarek@test.local']['sid'], $trainings['ui-ux-intern']]);

    echo "Seeding applications...\n";
    $apps = [];

    $stu = $students['student@test.local'];
    $apps['backend-intern|student'] = masar_insert_application($pdo, $trainings['backend-intern'], $companies['company@test.local']['cid'], $stu, 'submitted', [
        'message' => 'I would love to grow my backend skills with your team.',
        'why_interested' => 'I enjoy building scalable RESTful APIs and want real production experience.',
        'what_to_learn' => 'Clean architecture, testing, and deployment.',
        'skills' => 'PHP, Laravel, MySQL, REST API, Git',
        'applicant_type' => 'student', 'academic_year' => '4th year',
    ]);
    $apps['frontend-shadow|student'] = masar_insert_application($pdo, $trainings['frontend-shadow'], $companies['company@test.local']['cid'], $stu, 'submitted');

    $sara = $students['sara.mostafa@test.local'];
    $apps['ai-intern|sara'] = masar_insert_application($pdo, $trainings['ai-intern'], $companies['company@test.local']['cid'], $sara, 'submitted', [
        'message' => 'I am applying to contribute to your ML work.',
        'why_interested' => 'I am passionate about applied machine learning.',
        'what_to_learn' => 'End-to-end ML model deployment.',
        'skills' => 'Python, Machine Learning, Deep Learning',
    ]);

    $ahmed = $students['ahmed.hassan@test.local'];
    $apps['mech-drafting|ahmed'] = masar_insert_application($pdo, $trainings['mech-drafting'], $companies['delta.engineering@test.local']['cid'], $ahmed, 'accepted', [
        'message' => 'Excited to join your drafting team.',
        'why_interested' => 'I want practical CAD experience.',
        'what_to_learn' => 'Advanced 3D modeling.',
        'skills' => 'Teamwork, Project Management, Communication',
        'reviewed_at' => date('Y-m-d H:i:s', $now - 3 * $day),
        'reviewed_by' => $companies['delta.engineering@test.local']['uid'],
    ]);

    $hana = $students['hana.sameh@test.local'];
    $apps['clinical-pharm|hana'] = masar_insert_application($pdo, $trainings['clinical-pharm'], $companies['pharmalife.egypt@test.local']['cid'], $hana, 'accepted', [
        'message' => 'I am keen to learn clinical pharmacy.',
        'why_interested' => 'I want hands-on pharmaceutical care experience.',
        'what_to_learn' => 'Patient counseling and drug safety.',
        'skills' => 'Communication, Time Management',
        'reviewed_at' => date('Y-m-d H:i:s', $now - 5 * $day),
        'reviewed_by' => $companies['pharmalife.egypt@test.local']['uid'],
    ]);

    $youssef = $students['youssef.tarek@test.local'];
    $apps['marketing-campaign|youssef'] = masar_insert_application($pdo, $trainings['marketing-campaign'], $companies['brightreach.marketing@test.local']['cid'], $youssef, 'rejected', [
        'message' => 'I would love to join your growth team.',
        'why_interested' => 'I enjoy digital marketing strategy.',
        'what_to_learn' => 'Running real campaigns.',
        'skills' => 'Digital Marketing, SEO',
        'rejection_reason' => 'position_filled', 'rejection_note' => 'We filled this position with an internal candidate.',
        'reviewed_at' => date('Y-m-d H:i:s', $now - 2 * $day),
        'reviewed_by' => $companies['brightreach.marketing@test.local']['uid'],
    ]);

    $nour = $students['nour.adel@test.local'];
    $apps['ui-ux-intern|nour'] = masar_insert_application($pdo, $trainings['ui-ux-intern'], $companies['pixelcraft.studio@test.local']['cid'], $nour, 'submitted', [
        'message' => 'I am passionate about product design.',
        'why_interested' => 'I love crafting usable interfaces.',
        'what_to_learn' => 'Design systems.',
        'skills' => 'Figma, UI Design, UX Design',
    ]);

    $mariam = $students['mariam.samir@test.local'];
    $apps['audit-intern|mariam'] = masar_insert_application($pdo, $trainings['audit-intern'], $companies['ledgerpro.accounting@test.local']['cid'], $mariam, 'accepted', [
        'message' => 'I am applying to the audit internship.',
        'why_interested' => 'I want audit and reporting experience.',
        'what_to_learn' => 'Financial statement analysis.',
        'skills' => 'Data Analysis, Problem Solving, Communication',
        'reviewed_at' => date('Y-m-d H:i:s', $now - 1 * $day),
        'reviewed_by' => $companies['ledgerpro.accounting@test.local']['uid'],
    ]);

    $karim = $students['karim.adel@test.local'];
    $apps['corporate-law-shadow|karim'] = masar_insert_application($pdo, $trainings['corporate-law-shadow'], $companies['themis.law@test.local']['cid'], $karim, 'withdrawn', [
        'message' => 'Applying to shadow your legal team.',
        'why_interested' => 'I am pursuing corporate law.',
        'what_to_learn' => 'Contract review.',
        'skills' => 'Communication, Problem Solving',
        'withdrawn_at' => date('Y-m-d H:i:s', $now - 1 * $day),
    ]);

    // Past bootcamp accepted application -> certificate flow
    $apps['frontend-bootcamp|student'] = masar_insert_application($pdo, $trainings['frontend-bootcamp'], $companies['company@test.local']['cid'], $stu, 'accepted', [
        'message' => 'I completed the bootcamp and would like the certificate.',
        'why_interested' => 'I completed all required coursework.',
        'what_to_learn' => 'Advanced React and state management.',
        'skills' => 'JavaScript, React',
        'reviewed_at' => date('Y-m-d H:i:s', $now - 80 * $day),
        'reviewed_by' => $companies['company@test.local']['uid'],
    ]);

    echo "Seeding application answers...\n";
    $ans = $pdo->prepare("INSERT INTO application_answers (application_id, question_id, answer, created_at) VALUES (?, ?, ?, NOW())");
    $backendApp = $apps['backend-intern|student'];
    $ans->execute([$backendApp, $qs['backend-intern']['q1'], 'I want practical production backend experience.']);
    $ans->execute([$backendApp, $qs['backend-intern']['q2'], 'PHP, JavaScript']);
    $ans->execute([$backendApp, $qs['backend-intern']['q3'], 'Yes']);

    echo "Seeding training sessions...\n";
    $sessions = [];
    $bootApp = $apps['frontend-bootcamp|student'];
    $sessions['frontend-bootcamp'] = masar_insert_session($pdo, $bootApp, $trainings['frontend-bootcamp'], $stu['sid'], $companies['company@test.local']['cid'], 'completed', [
        'started_at' => date('Y-m-d H:i:s', $now - 85 * $day),
        'trial_started_at' => date('Y-m-d H:i:s', $now - 85 * $day),
        'trial_ends_at' => date('Y-m-d H:i:s', $now - 80 * $day),
        'confirmed_at' => date('Y-m-d H:i:s', $now - 79 * $day),
        'ended_at' => date('Y-m-d H:i:s', $now - 45 * $day),
        'employment_opportunity' => 0,
    ]);
    $sessions['mech-drafting'] = masar_insert_session($pdo, $apps['mech-drafting|ahmed'], $trainings['mech-drafting'], $ahmed['sid'], $companies['delta.engineering@test.local']['cid'], 'continuing', [
        'started_at' => date('Y-m-d H:i:s', $now - 2 * $day),
        'trial_started_at' => date('Y-m-d H:i:s', $now - 2 * $day),
        'trial_ends_at' => date('Y-m-d H:i:s', $now + 5 * $day),
        'confirmed_at' => null,
        'ended_at' => null,
        'employment_opportunity' => 1,
    ]);

    echo "Seeding certificates...\n";
    $cert1 = masar_insert_certificate($pdo, [
        'student_id' => $stu['sid'],
        'company_id' => $companies['company@test.local']['cid'],
        'training_id' => $trainings['frontend-bootcamp'],
        'training_session_id' => $sessions['frontend-bootcamp'],
        'status' => 'issued',
        'title' => 'Frontend Web Development Bootcamp - Certificate of Completion',
        'start_date' => date('Y-m-d', $now - 85 * $day),
        'end_date' => date('Y-m-d', $now - 45 * $day),
        'grade' => 88.50,
        'grade_label' => 'Excellent',
        'employment_eligible' => 0,
        'reviewed_at' => date('Y-m-d H:i:s', $now - 44 * $day),
        'approved_at' => date('Y-m-d H:i:s', $now - 44 * $day),
        'reviewed_by' => $companies['company@test.local']['uid'],
    ]);

    echo "Seeding conversations & messages...\n";
    $conv = $pdo->prepare(
        "INSERT IGNORE INTO conversations (student_id, company_id, application_id, created_at, updated_at)
         VALUES (?, ?, ?, NOW(), NOW())"
    );
    $conv->execute([$ahmed['sid'], $companies['delta.engineering@test.local']['cid'], $apps['mech-drafting|ahmed']]);
    $convId = (int) $pdo->lastInsertId();

    $msg = $pdo->prepare("INSERT INTO messages (conversation_id, sender_user_id, body, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $msg->execute([$convId, $companies['delta.engineering@test.local']['uid'], 'Welcome aboard! Please join the onboarding call tomorrow at 10am.']);
    $msg->execute([$convId, $ahmed['uid'], 'Thank you! I will be there.']);

    echo "Seeding notifications...\n";
    // Student-directed notifications
    masar_insert_notification($pdo, $ahmed['uid'], 'application_accepted', 'Application Accepted', 'Your application has been accepted for the Mechanical CAD Drafting Internship.', 'application', $apps['mech-drafting|ahmed']);
    masar_insert_notification($pdo, $hana['uid'], 'application_accepted', 'Application Accepted', 'Your application has been accepted for the Clinical Pharmacy Training.', 'application', $apps['clinical-pharm|hana']);
    masar_insert_notification($pdo, $youssef['uid'], 'application_rejected', 'Application Rejected', 'We are sorry, your application was not selected for the Digital Marketing Campaign Intern.', 'application', $apps['marketing-campaign|youssef']);
    masar_insert_notification($pdo, $stu['uid'], 'new_message', 'New Message', 'Delta Engineering Group sent you a message.', 'conversation', $convId);
    masar_insert_notification($pdo, $stu['uid'], 'training_updated', 'Training Updated', 'One of your saved trainings was updated.', 'training', $trainings['ai-intern']);

    echo "Seeding audit logs...\n";
    $audit = $pdo->prepare(
        "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at)
         VALUES (?, ?, ?, ?, ?, ?, '127.0.0.1', 'MASAR Test Seeder', NOW())"
    );
    $audit->execute([$companies['company@test.local']['uid'], 'training.create', 'training', $trainings['backend-intern'], null, json_encode(['title' => 'Backend Engineering Internship'])]);
    $audit->execute([$companies['delta.engineering@test.local']['uid'], 'application.accept', 'application', $apps['mech-drafting|ahmed'], null, null]);
    $audit->execute([$admin_user, 'company.approve', 'company', $companies['cairo.medical@test.local']['cid'], null, null]);

    $pdo->commit();

    echo "Seeding completed.\n";
}

/*
|--------------------------------------------------------------------------
| CLI Entry Point
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        masar_seed($pdo);

        echo "Full test data seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(
            STDERR,
            "Seeder failed: " . $exception->getMessage() . PHP_EOL
        );
        exit(1);
    }
}
