<?php

/**
 * MASAR - Certificates Test Fixtures (shared)
 *
 * Self-provisioning helpers built on the REAL database dataset. This file NO
 * LONGER creates any fake training / company / student / application: the
 * dedicated "Backend Certificate Lifecycle Bootcamp" fixtures were removed.
 *
 * The certificate lifecycle is driven against real seed data:
 *
 *   - company@test.local  = TestHire Solutions (companies.id 100161)
 *   - admin@masar.eg      = a real admin account
 *   - mammuslim2003@gmail.com = real student 1498 (lifecycle driver A) who
 *     holds REAL accepted applications on REAL TestHire trainings:
 *        100278 DevOps Fundamentals (free)              -> app 1878
 *        100304 Backend API & Database Bootcamp (paid)  -> app 2009
 *   - sara.fahim@gmail.com = real student 1464 (isolation driver B)
 *
 * Those real applications have NO training session rows today, so the ONLY
 * missing lifecycle ingredient is the completed session that defines
 * eligibility. cf_prepare_lifecycle_dataset() provisions those completed
 * sessions if (and only if) they do not already exist, records every row it
 * creates, and cf_cleanup_lifecycle_transients() removes only those rows.
 * Pre-existing sessions are never mutated.
 *
 * This file only defines functions; it never runs anything on include.
 */

declare(strict_types=1);

if (!function_exists('cf_pdo')) {
    function cf_pdo(): PDO
    {
        return get_database_connection();
    }
}

function cf_resolve_user_id(string $email): int
{
    $row = db_fetch_one(
        "SELECT id FROM users WHERE email = ? LIMIT 1",
        [$email]
    );
    return $row ? (int) $row['id'] : 0;
}

function cf_resolve_user_id_first(array $emails): int
{
    foreach ($emails as $email) {
        $id = cf_resolve_user_id((string) $email);
        if ($id > 0) {
            return $id;
        }
    }
    return 0;
}

function cf_resolve_student(
    string $email
): array {
    $row = db_fetch_one(
        "SELECT u.id AS user_id, s.id AS student_id, s.full_name
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.email = ? LIMIT 1",
        [$email]
    );
    return $row
        ? [
            'user_id'    => (int) $row['user_id'],
            'student_id' => (int) $row['student_id'],
            'full_name'  => (string) ($row['full_name'] ?? ''),
        ]
        : ['user_id' => 0, 'student_id' => 0, 'full_name' => ''];
}

function cf_resolve_student_first(array $emails): array
{
    foreach ($emails as $email) {
        $student = cf_resolve_student((string) $email);
        if ($student['user_id'] > 0) {
            $student['email'] = (string) $email;
            return $student;
        }
    }
    return ['user_id' => 0, 'student_id' => 0, 'full_name' => '', 'email' => ''];
}

function cf_resolve_company(string $email): array
{
    $row = db_fetch_one(
        "SELECT c.id AS company_id, c.legal_name, u.id AS user_id
         FROM companies c
         JOIN users u ON u.id = c.user_id
         WHERE u.email = ? LIMIT 1",
        [$email]
    );
    return $row
        ? [
            'company_id'  => (int) $row['company_id'],
            'company_name' => (string) ($row['legal_name'] ?? ''),
            'user_id'     => (int) $row['user_id'],
        ]
        : ['company_id' => 0, 'company_name' => '', 'user_id' => 0];
}

/**
 * An active company user whose company is NOT the certificate driver
 * company (used for the non-owning company 403 cases).
 */
function cf_other_company_user(int $exclude_company_id): array
{
    $row = db_fetch_one(
        "SELECT u.id AS user_id, c.id AS company_id
         FROM users u
         JOIN companies c ON c.user_id = u.id
         WHERE u.role = 'company' AND u.status = 'active'
           AND c.id <> ?
         ORDER BY c.id LIMIT 1",
        [$exclude_company_id]
    );
    return $row
        ? ['user_id' => (int) $row['user_id'], 'company_id' => (int) $row['company_id']]
        : ['user_id' => 0, 'company_id' => 0];
}

/**
 * Find a REAL training owned by $company_id on which $student_id holds an
 * ACCEPTED application AND a COMPLETED session, whose training has actually
 * ended (training_listings.ends_at <= current server time), and for which no
 * certificate row exists yet. That is exactly the certificate-eligible state.
 *
 * It intentionally prefers the stable lowest training id on each re-run.
 * Existing real data only - this helper never creates rows.
 */
function cf_find_real_driver_training(
    int $company_id,
    int $student_id,
    bool $is_paid
): array {
    $row = db_fetch_one(
        "SELECT t.id, t.title, t.is_paid, a.id AS application_id, ts.id AS session_id
         FROM training_applications a
         JOIN training_listings t ON t.id = a.training_id
         JOIN training_sessions ts
              ON ts.training_id = a.training_id
             AND ts.student_id = a.student_id
             AND ts.status = 'completed'
         WHERE a.student_id = ?
           AND a.status = 'accepted'
           AND t.company_id = ?
           AND t.is_paid = ?
           AND t.ends_at <= NOW()
           AND NOT EXISTS (
               SELECT 1 FROM certificates c
               WHERE c.student_id = a.student_id
                 AND c.training_id = a.training_id
           )
         ORDER BY t.id LIMIT 1",
        [$student_id, $company_id, $is_paid ? 1 : 0]
    );
    if (!$row) {
        return [];
    }
    return [
        'training_id'    => (int) $row['id'],
        'title'          => (string) $row['title'],
        'is_paid'        => (bool) ((int) $row['is_paid']),
        'application_id' => (int) $row['application_id'],
        'session_id'     => (int) $row['session_id'],
    ];
}

/**
 * Provision (or reuse) the COMPLETED session that makes the student eligible
 * for the real accepted application. The session is created ONLY when it does
 * not exist; a pre-existing session is returned untouched (never mutated).
 * Rows NO session should exist for the real driver applications.
 *
 * Returns ['session_id' => int, 'created' => bool, 'blocked' => bool]:
 *   - created=true  : this helper inserted the row (owned by the test).
 *   - blocked=true  : a PRE-EXISTING non-completed session was found; the
 *                     caller must abort the run instead of touching real data.
 */
function cf_provision_completed_session(
    int $training_id,
    array $student,
    int $application_id,
    int $company_id
): array {
    $existing = db_fetch_one(
        "SELECT id, status FROM training_sessions
         WHERE application_id = ? LIMIT 1",
        [$application_id]
    );
    if ($existing) {
        if (strtolower((string) $existing['status']) !== 'completed') {
            return [
                'session_id' => (int) $existing['id'],
                'created'    => false,
                'blocked'    => true,
            ];
        }
        return [
            'session_id' => (int) $existing['id'],
            'created'    => false,
            'blocked'    => false,
        ];
    }

    $now     = time();
    $started = date('Y-m-d H:i:s', $now - 85 * 86400);
    $ended   = date('Y-m-d H:i:s', $now - 46 * 86400);

    db_execute(
        "INSERT INTO training_sessions
            (application_id, training_id, student_id, company_id, status, started_at, trial_started_at,
             trial_ends_at, student_continuation_confirmed_at, actual_ended_at, employment_opportunity, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'completed', ?, NULL, NULL, NULL, ?, 0, NOW(), NOW())",
        [$application_id, $training_id, $student['student_id'], $company_id, $started, $ended]
    );

    return [
        'session_id' => (int) db_last_insert_id(),
        'created'    => true,
        'blocked'    => false,
    ];
}

/**
 * Resolve the real certificate dataset and pick the driver's FINISHED eligible
 * trainings (free + paid). Returns the resolved identities + driver trainings
 * + the dataset session ids that proof the completed-session state.
 *
 * - KEY: this function creates NO training, NO company, NO student, NO
 *   application and NO session. Since the eligibility rule now requires the
 *   training itself to have ended (training_listings.ends_at <= NOW()), the
 *   drivers are resolved from the finished-eligible dataset trainings
 *   (cf_find_real_driver_training) that come with their own completed
 *   sessions. Nothing here mutates real data.
 */
function cf_prepare_lifecycle_dataset(): array
{
    $company_email    = 'company@test.local';
    $admin_candidates = ['admin@test.local', 'admin@masar.eg', 'compliance@masar.eg'];
    $studentA_candidates = ['student@test.local', 'mammuslim2003@gmail.com'];
    $studentB_candidates = ['sara.mostafa@test.local', 'sara.fahim@gmail.com'];

    $company  = cf_resolve_company($company_email);
    $admin_id = cf_resolve_user_id_first($admin_candidates);
    $student_a = cf_resolve_student_first($studentA_candidates);
    $student_b = cf_resolve_student_first($studentB_candidates);

    $free = $company['company_id'] > 0 && $student_a['student_id'] > 0
        ? cf_find_real_driver_training($company['company_id'], $student_a['student_id'], false)
        : [];
    $paid = $company['company_id'] > 0 && $student_a['student_id'] > 0
        ? cf_find_real_driver_training($company['company_id'], $student_a['student_id'], true)
        : [];

    $data = [
        'company_email'      => $company_email,
        'company_id'         => $company['company_id'],
        'company_name'       => $company['company_name'],
        'company_user_id'    => $company['user_id'],
        'admin_user_id'      => $admin_id,
        'student_a_email'    => $student_a['email'] ?? '',
        'student_a'          => $student_a,
        'student_b_email'    => $student_b['email'] ?? '',
        'student_b'          => $student_b,
        'training_id'        => 0,
        'training_title'     => '',
        'paid_training_id'   => 0,
        'paid_training_title'=> '',
        'application_free'   => 0,
        'application_paid'   => 0,
        'driver_session_free'=> 0,
        'driver_session_paid'=> 0,
        'sessions_to_cleanup'=> [],
        'ok'                 => false,
        'reason'             => '',
    ];

    if ($company['company_id'] <= 0 || $admin_id <= 0 || !$student_a['student_id'] || !$student_b['student_id']) {
        $data['reason'] = 'Required seed accounts were not resolved.';
        return $data;
    }
    if (empty($free) || empty($paid)) {
        $data['reason'] = 'No real FINISHED eligible training (accepted application + completed session + ends_at <= NOW() + no certificate) found for a free AND a paid TestHire training of driver student A. Missing prerequisite: run "php tests/certificates_test_data_seeder.php" first.';
        return $data;
    }

    $data['training_id']          = $free['training_id'];
    $data['training_title']       = $free['title'];
    $data['application_free']     = $free['application_id'];
    $data['driver_session_free']  = $free['session_id'];
    $data['paid_training_id']     = $paid['training_id'];
    $data['paid_training_title']  = $paid['title'];
    $data['application_paid']     = $paid['application_id'];
    $data['driver_session_paid']  = $paid['session_id'];

    // A certificate left behind by a crashed previous run would block
    // eligibility. Drivers are resolved as ELIGIBLE (no certificate row), so
    // any leftover for these pairs is test-owned from an earlier crashed run
    // and gets removed. Dataset sessions are never touched here.
    foreach (
        [
            ['training_id' => $data['training_id'], 'application_id' => $data['application_free']],
            ['training_id' => $data['paid_training_id'], 'application_id' => $data['application_paid']],
        ] as $pair
    ) {
        db_execute(
            "DELETE FROM certificate_appeals
             WHERE certificate_id IN (SELECT id FROM certificates WHERE training_id = ? AND student_id = ?)",
            [$pair['training_id'], $student_a['student_id']]
        );
        db_execute(
            "DELETE FROM certificates WHERE training_id = ? AND student_id = ?",
            [$pair['training_id'], $student_a['student_id']]
        );
    }

    $data['ok'] = true;
    return $data;
}

/**
 * No secrets to clean up: the large dataset seeder owns the finished trainings
 * and their completed sessions, so the regression never creates sessions. This
 * helper therefore only removes rows that a crashed run of the regression left
 * behind (a certificate on a now-blocked pair). It is kept for symmetry.
 */
function cf_cleanup_lifecycle_transients(array $data): void
{
    foreach (($data['sessions_to_cleanup'] ?? []) as $session_id) {
        db_execute("DELETE FROM training_sessions WHERE id = ? LIMIT 1", [(int) $session_id]);
    }
}