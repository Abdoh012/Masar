<?php

/**
 * MASAR - Spec 199 Test Data Seeder (Company Lifecycle + Payment States)
 *
 * Maintains the Backend Development (specialization_id = 199) test dataset so
 * the backend student (student_id = 1498, mammuslim2003@gmail.com) can see the
 * test company TestHire Solutions (company_id = 100161) trainings and the four
 * paid Accepted-card payment-state demos (plus the Free Accepted scenario)
 * are always reproducible. It NEVER creates
 * new training_listings rows beyond the five permanent fixtures (the
 * normalization regression asserts the dataset stays at exactly 72 rows) and it
 * NEVER deletes historical applications/payments: existing rows are converged
 * to their canonical values, missing rows are inserted.
 *
 * 1. Fixture trainings (TestHire, spec 199) - UPSERT by (title, company_id):
 *    - Backend PHP Micro-Internship        (100303) free short, visible
 *    - Backend API & Database Bootcamp     (100304) paid bootcamp, visible,
 *      receives a "paid" payment-state demo for the student
 *    - Backend Monolith Refactoring Sprint (100305) expired-but-published
 *      'extend training' target (ends_at in the past -> hidden from public
 *      discovery until a company extends end_date via the update endpoint)
 *    - Backend GraphQL Gateway Draft       (100308) draft - company DELETE demo
 *    - Backend Senior Mentorship Circle    (100307) free long, visible
 *    Each fixture keeps application_deadline === starts_at EXACTLY (the
 *    normalization rule) and NOW-relative dates so re-running refreshes the
 *    demo window.
 *
 * 2. Always-visible spec-199 anchors (100246, 100252) are converged to
 *    NOT change company data - only their timeline is refreshed to future
 *    dates so keyword/filter/pagination demos stay stable for several weeks.
 *
 * 3. Accepted-card payment-state scenarios for the student (one accepted
 *    application per training, payment rows converged, never deleted):
 *    - pending verdict : 100262 (Machine Learning API Deployment) - existing
 *      accepted application with a pending(payment) row
 *    - paid            : 100304 (Backend API & Database Bootcamp) - paid row
 *    - failed          : 100258 (MongoDB Data Modeling) - failed row
 *    - no payment      : 100246 (Laravel REST API Mastery) - NO payment row
 *    - free accepted   : 100303 (Backend PHP Micro-Internship) - free
 *      training, NO payment row, so the card renders is_paid=false with
 *      payment_status 'not_required' and bank_account null (the Free Accepted
 *      scenario the Accepted endpoint regression verifies)
 *    (seeder-owned payment rows use the 'TRANSFER-SEEDED-' reference prefix;
 *    the no-payment / free scenarios remove only seeder-owned rows)
 *
 * 4. The student saves 100304 so the search card is_saved demo is visible.
 *
 * 5. Bank-account destinations - converges the three demo companies that
 *    currently ship no transfer destination (NileTech 100144, FutureWorks
 *    100152, TestHire 100161) so the four accepted payment states behave as
 *    the business rule requires: pending / failed / not-submitted expose a
 *    configured bank_account, paid stays null. COALESCE is used so any
 *    pre-existing configured bank details are never clobbered. (The fourth
 *    scenario owner, Alexandria Digital Labs 100145, already carries one.)
 *
 * Run from the backend root:
 *     php database/seeders/backend_spec199_test_data_seeder.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

const TDS_SPECIALIZATION_ID = 199;
const TDS_COMPANY_ID = 100161;
const TDS_STUDENT_ID = 1498;

function tds_datetime(int $dayOffset, string $time = '10:00:00'): string
{
    return date('Y-m-d H:i:s', strtotime(($dayOffset > 0 ? '+' : ($dayOffset < 0 ? '-' : '')) . abs($dayOffset) . ' day ' . $time));
}

/**
 * The five permanent TestHire fixtures. The leading integer is the FIXED id
 * (kept stable so the company DELETE demo can be repeated: deleting the
 * draft #100308 and re-running the seeder restores it under the SAME id, and
 * the dataset always comes back to exactly the five fixtures / 72 rows).
 * [id, title, description, training_type, mode, is_paid, compensation_amount,
 *  trial_period_days, capacity, status, starts_offset, ends_offset,
 *  published_offset, may_lead_to_employment, skills[]]
 */
function tds_trainings(): array
{
    return [
        [100303,
            'Backend PHP Micro-Internship',
            'A short, free hands-on internship at TestHire where juniors pair with the backend team on PHP endpoints: PSR-standard structure, input validation and clean database access. Trainees ship one small production-shaped feature end to end and get direct code review. Purpose: short remaining duration for duration_asc sorting and a free apply/accept target.',
            'hands_on', 'onsite', false, null, null, 10,
            'published', +8, +15, -14, 1,
            ['PHP', 'MySQL', 'Git', 'Redis'],
        ],
        [100304,
            'Backend API & Database Bootcamp',
            'A paid project-based bootcamp covering the full backend stack at TestHire: REST API design, authentication, SQL schema design and deployment. Trainees build a complete service and present it in review. Purpose: paid training for the application + manual payment (reference -> confirm) lifecycle. Fee is charged to accepted students per MASAR rules.',
            'project_based', 'remote', true, 2500.00, 14, 8,
            'published', +25, +60, -14, 1,
            ['PHP', 'Laravel', 'MySQL', 'Docker', 'Git'],
        ],
        [100305,
            'Backend Monolith Refactoring Sprint',
            'A fixture published training whose ends_at has already passed so the tester can observe an expired-but-published listing (fixed duration still computed, remaining_days = 0) and then extend it with PUT /api/v1/trainings/update?id=... { "end_date": ... }. The close_expired_trainings cron only removes it after it flips to closed; before that run the company can extend it and bring it back to a live countdown.',
            'project_based', 'hybrid', false, null, null, 6,
            'published', -40, -3, -55, 1,
            ['PHP', 'MySQL', 'Refactoring', 'Git'],
        ],
        [100308,
            'Backend GraphQL Gateway Draft',
            'A DRAFT fixture owned by TestHire. Only drafts can be deleted, so this is the target for DELETE /api/v1/trainings/delete?id=... (409 for published/closed rows, 403 for other companies). NOT published, so it never appears in student discovery.',
            'hands_on', 'remote', false, null, null, 5,
            'draft', +10, +40, null, 0,
            ['GraphQL', 'Node.js', 'PostgreSQL'],
        ],
        [100307,
            'Backend Senior Mentorship Circle',
            'A long, free remote mentorship at TestHire where a senior engineer guides a small group through architecture reviews, incident postmortems and career-grade project work over several months. Purpose: long remaining duration for duration_desc sorting and a large countdown value.',
            'shadowing', 'remote', false, null, null, 4,
            'published', +5, +95, -14, 1,
            ['PHP', 'Architecture', 'Mentoring'],
        ],
    ];
}

/**
 * Always-visible spec-199 anchors (owned by other test companies but part of
 * the synthetic id>=100246 data block). Only the timeline is refreshed; the
 * published status, ownership, paid flag, compensation and lead flag are
 * left untouched.
 */
function tds_visible_anchors(): array
{
    return [
        ['id' => 100246, 'starts_offset' => +3, 'ends_offset' => +36],
        ['id' => 100252, 'starts_offset' => +3, 'ends_offset' => +38],
    ];
}

/**
 * Accepted-card payment-state scenarios.
 * [training_id, payment_status (null = no payment row), reference]
 * 100303 is the Free Accepted scenario: a free training that carries no
 * payment row, so is_paid=false -> payment_status 'not_required' and
 * bank_account null.
 */
function tds_payment_scenarios(): array
{
    return [
        ['training_id' => 100262, 'payment_status' => 'pending', 'reference' => 'TRANSFER-SEEDED-PENDING'],
        ['training_id' => 100304, 'payment_status' => 'paid',    'reference' => 'TRANSFER-SEEDED-PAID'],
        ['training_id' => 100258, 'payment_status' => 'failed',  'reference' => 'TRANSFER-SEEDED-FAILED'],
        ['training_id' => 100246, 'payment_status' => null,      'reference' => null],
        ['training_id' => 100303, 'payment_status' => null,      'reference' => null],
    ];
}

function tds_run(PDO $pdo): array
{
    $specName = $pdo->query('SELECT name FROM specializations WHERE id = ' . TDS_SPECIALIZATION_ID)->fetchColumn();
    if ($specName !== 'Backend Development') {
        throw new RuntimeException('Specialization ' . TDS_SPECIALIZATION_ID . ' is not "Backend Development" (' . var_export($specName, true) . '). Aborting.');
    }

    $company = $pdo->prepare('SELECT id, city FROM companies WHERE id = ? AND approval_status = \'approved\'');
    $company->execute([TDS_COMPANY_ID]);
    $companyRow = $company->fetch();
    if (!$companyRow) {
        throw new RuntimeException('Test company ' . TDS_COMPANY_ID . ' is not approved/missing. Aborting.');
    }
    $companyCity = $companyRow['city'] ?? null;

    $student = $pdo->prepare('SELECT id, specialization_id FROM students WHERE id = ?');
    $student->execute([TDS_STUDENT_ID]);
    $studentRow = $student->fetch();
    if (!$studentRow || (int) ($studentRow['specialization_id'] ?? 0) !== TDS_SPECIALIZATION_ID) {
        throw new RuntimeException('Test student ' . TDS_STUDENT_ID . ' is missing or has a non-199 specialization. Aborting.');
    }

    $report = ['fixtures' => [], 'anchors' => [], 'applications' => [], 'payments' => [], 'saved' => [], 'bank' => []];

    /*
    |--------------------------------------------------------------------------
    | 1. Fixture trainings (UPSERT, never add beyond the five)
    |--------------------------------------------------------------------------
    */
    $existingById = static function (PDO $pdo, string $title): ?int {
        $stmt = $pdo->prepare('SELECT id FROM training_listings WHERE title = ? AND company_id = ? LIMIT 1');
        $stmt->execute([$title, TDS_COMPANY_ID]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    };

    foreach (tds_trainings() as $t) {
        [$fixtureId, $title, $description, $trainingType, $mode, $isPaid, $compensation, $trial, $capacity,
        $status, $startsOffset, $endsOffset, $publishedOffset, $mayLead, $skills] = $t;

        $startsAt = tds_datetime($startsOffset, '09:00:00');
        $endsAt = tds_datetime($endsOffset, '17:00:00');
        $publishedAt = ($status === 'published' && $publishedOffset !== null) ? tds_datetime($publishedOffset, '08:00:00') : null;
        $now = date('Y-m-d H:i:s');
        $trainingId = $existingById($pdo, $title);

        if ($trainingId === null) {
            if ((int) $pdo->query("SELECT COUNT(*) FROM training_listings WHERE id = {$fixtureId}")->fetchColumn() !== 0) {
                throw new RuntimeException("Fixture id {$fixtureId} is already taken by a different training. Aborting.");
            }
            $stmt = $pdo->prepare(
                'INSERT INTO training_listings
                    (id, company_id, specialization_id, title, description, training_type, mode, may_lead_to_employment,
                     is_paid, compensation_amount, compensation_currency, trial_period_days, capacity, status,
                     published_at, starts_at, ends_at, application_deadline, location, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([
                $fixtureId,
                TDS_COMPANY_ID, TDS_SPECIALIZATION_ID, $title, $description, $trainingType, $mode, $mayLead,
                $isPaid ? 1 : 0, $isPaid ? $compensation : null, 'EGP', $isPaid ? $trial : null, $capacity,
                $status, $publishedAt, $startsAt, $endsAt, $startsAt, $companyCity,
            ]);
            $trainingId = (int) $fixtureId;
            $action = 'INSERTED';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE training_listings SET
                    description = ?, training_type = ?, mode = ?, may_lead_to_employment = ?,
                    is_paid = ?, compensation_amount = ?, compensation_currency = ?, trial_period_days = ?,
                    capacity = ?, status = ?, published_at = ?, starts_at = ?, ends_at = ?,
                    application_deadline = ?, location = ?, updated_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([
                $description, $trainingType, $mode, $mayLead,
                $isPaid ? 1 : 0, $isPaid ? $compensation : null, 'EGP', $isPaid ? $trial : null, $capacity,
                $status, $publishedAt, $startsAt, $endsAt, $startsAt, $companyCity, $trainingId,
            ]);
            $action = 'CONVERGED';
        }

        $insSkill = $pdo->prepare('INSERT IGNORE INTO training_skills (training_id, skill_id) VALUES (?, ?)');
        $resolveSkillId = static function (PDO $pdo, string $name): ?int {
            $stmt = $pdo->prepare('SELECT id FROM skills WHERE name = ?');
            $stmt->execute([$name]);
            $value = $stmt->fetchColumn();
            return $value === false ? null : (int) $value;
        };
        foreach ($skills as $skillName) {
            $skillId = $resolveSkillId($pdo, $skillName);
            if ($skillId !== null) {
                $insSkill->execute([$trainingId, $skillId]);
            }
        }

        $specCheck = $pdo->prepare('SELECT COUNT(*) FROM training_specializations WHERE training_id = ? AND specialization_id = ?');
        $specCheck->execute([$trainingId, TDS_SPECIALIZATION_ID]);
        if ((int) $specCheck->fetchColumn() === 0) {
            $pdo->prepare('INSERT IGNORE INTO training_specializations (training_id, specialization_id) VALUES (?, ?)')->execute([$trainingId, TDS_SPECIALIZATION_ID]);
        }

        $report['fixtures'][] = "$action #{$trainingId} [{$status}] {$title}";
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Always-visible spec-199 anchors (timeline only)
    |--------------------------------------------------------------------------
    */
    foreach (tds_visible_anchors() as $anchor) {
        $row = $pdo->prepare('SELECT id, status FROM training_listings WHERE id = ? LIMIT 1');
        $row->execute([$anchor['id']]);
        $training = $row->fetch();
        if (!$training || $training['status'] !== 'published') {
            $report['anchors'][] = "SKIP #{$anchor['id']} (not found / not published)";
            continue;
        }
        $startsAt = tds_datetime($anchor['starts_offset'], '09:00:00');
        $endsAt = tds_datetime($anchor['ends_offset'], '17:00:00');
        $stmt = $pdo->prepare(
            'UPDATE training_listings SET starts_at = ?, ends_at = ?, application_deadline = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$startsAt, $endsAt, $startsAt, $anchor['id']]);
        $report['anchors'][] = "CONVERGED #{$anchor['id']} (starts {$startsAt}, ends {$endsAt})";
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Accepted-card payment-state scenarios
    |--------------------------------------------------------------------------
    */
    $appNow = tds_datetime(-30, '09:00:00');
    $appReviewed = tds_datetime(-25, '11:00:00');
    $appTemplate = [
        'full_name'      => 'Mohamed Ahmed',
        'email'          => 'mammuslim2003@gmail.com',
        'phone'          => '01000000000',
        'city'           => 'Cairo',
        'address'        => 'Cairo',
        'why_interested' => 'I want to deepen my backend engineering skills through hands-on production practice.',
        'what_to_learn'  => 'Production-grade backend APIs, databases and team workflows.',
        'skills'         => 'PHP, Laravel, MySQL, Git',
        'university'     => 'Test Data University',
        'applicant_type' => 'student',
        'academic_year'  => '3rd',
    ];

    foreach (tds_payment_scenarios() as $scenario) {
        $trainingId = $scenario['training_id'];
        $training = $pdo->prepare('SELECT id, company_id, compensation_amount FROM training_listings WHERE id = ? AND specialization_id = ? LIMIT 1');
        $training->execute([$trainingId, TDS_SPECIALIZATION_ID]);
        $trainingRow = $training->fetch();
        if (!$trainingRow) {
            throw new RuntimeException("Scenario training #{$trainingId} is missing or not spec 199. Aborting.");
        }
        $companyId = (int) ($trainingRow['company_id'] ?? 0);
        $amount = number_format((float) ($trainingRow['compensation_amount'] ?? 0) ?: 3000.00, 2, '.', '');

        $existingApp = $pdo->prepare('SELECT id FROM training_applications WHERE training_id = ? AND student_id = ? LIMIT 1');
        $existingApp->execute([$trainingId, TDS_STUDENT_ID]);
        $appId = $existingApp->fetchColumn();
        $appId = $appId === false ? null : (int) $appId;

        if ($appId === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO training_applications
                    (training_id, student_id, company_id, message, full_name, email, phone, city, address,
                     why_interested, what_to_learn, skills, status, applied_at, reviewed_at,
                     university, applicant_type, academic_year)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $trainingId, TDS_STUDENT_ID, $companyId,
                'Accepted for the ' . $trainingRow['id'] . ' training (payment-state demo).',
                $appTemplate['full_name'], $appTemplate['email'], $appTemplate['phone'],
                $appTemplate['city'], $appTemplate['address'], $appTemplate['why_interested'],
                $appTemplate['what_to_learn'], $appTemplate['skills'], 'accepted', $appNow, $appReviewed,
                $appTemplate['university'], $appTemplate['applicant_type'], $appTemplate['academic_year'],
            ]);
            $appId = (int) $pdo->lastInsertId();
            $report['applications'][] = "INSERTED accepted app #{$appId} for training #{$trainingId} (student " . TDS_STUDENT_ID . ')';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE training_applications SET
                    status = COALESCE(status, \'accepted\'),
                    company_id = COALESCE(company_id, ?),
                    full_name = COALESCE(full_name, ?),
                    email = COALESCE(email, ?),
                    phone = COALESCE(phone, ?),
                    city = COALESCE(city, ?),
                    address = COALESCE(address, ?),
                    university = COALESCE(university, ?),
                    applicant_type = COALESCE(applicant_type, ?),
                    academic_year = COALESCE(academic_year, ?),
                    applied_at = COALESCE(applied_at, ?),
                    reviewed_at = COALESCE(reviewed_at, ?)
                 WHERE id = ?'
            );
            $stmt->execute([
                $companyId, $appTemplate['full_name'], $appTemplate['email'], $appTemplate['phone'],
                $appTemplate['city'], $appTemplate['address'], $appTemplate['university'],
                $appTemplate['applicant_type'], $appTemplate['academic_year'], $appNow, $appReviewed, $appId,
            ]);

            $statusRow = $pdo->prepare('SELECT status FROM training_applications WHERE id = ?');
            $statusRow->execute([$appId]);
            $currentStatus = (string) $statusRow->fetchColumn();
            if ($currentStatus !== 'accepted') {
                $pdo->prepare('UPDATE training_applications SET status = ? WHERE id = ?')->execute(['accepted', $appId]);
                $report['applications'][] = "CONVERGED app #{$appId} (training #{$trainingId}) status -> accepted";
            } else {
                $report['applications'][] = "CONVERGED app #{$appId} for training #{$trainingId} (already accepted)";
            }
        }

        /*
        | Payment row converge / insert. NULL status removes seeder-owned rows
        | (the no-payment scenario) and otherwise leaves foreign rows untouched.
        */
        $existingPay = $pdo->prepare('SELECT id FROM payments WHERE training_id = ? AND student_id = ? ORDER BY id DESC LIMIT 1');
        $existingPay->execute([$trainingId, TDS_STUDENT_ID]);
        $payId = $existingPay->fetchColumn();
        $payId = $payId === false ? null : (int) $payId;

        if ($scenario['payment_status'] === null) {
            if ($payId !== null) {
                $ref = $pdo->prepare('SELECT external_reference FROM payments WHERE id = ?');
                $ref->execute([$payId]);
                $externalRef = (string) ($ref->fetchColumn() ?: '');
                if (str_starts_with($externalRef, 'TRANSFER-SEEDED-')) {
                    $pdo->prepare('DELETE FROM payments WHERE id = ?')->execute([$payId]);
                    $report['payments'][] = "REMOVED seeder-owned payment #{$payId} (training #{$trainingId}, no-payment scenario)";
                } else {
                    $report['payments'][] = "KEPT foreign payment #{$payId} for training #{$trainingId} (not seeder-owned)";
                }
            } else {
                $report['payments'][] = "OK no payment row for training #{$trainingId} (no-payment scenario)";
            }
            continue;
        }

        $paidAt = $scenario['payment_status'] === 'paid' ? tds_datetime(-5, '14:30:00') : null;
        if ($payId === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO payments
                    (training_id, student_id, company_id, amount, currency,
                     platform_commission_rate, platform_commission_amount, company_amount,
                     payment_method, status, external_reference, paid_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 0.00, 0.00, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([
                $trainingId, TDS_STUDENT_ID, $companyId, $amount, 'EGP', $amount,
                'manual', $scenario['payment_status'], $scenario['reference'], $paidAt,
            ]);
            $report['payments'][] = "INSERTED payment row (training #{$trainingId}, status {$scenario['payment_status']}, ref {$scenario['reference']})";
        } else {
            $stmt = $pdo->prepare(
                'UPDATE payments SET
                    company_id = ?, amount = ?, currency = \'EGP\',
                    platform_commission_rate = 0.00, platform_commission_amount = 0.00, company_amount = ?,
                    payment_method = \'manual\', status = ?, external_reference = ?, paid_at = ?, updated_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([
                $companyId, $amount, $amount, $scenario['payment_status'], $scenario['reference'], $paidAt, $payId,
            ]);
            $report['payments'][] = "CONVERGED payment #{$payId} (training #{$trainingId}, status {$scenario['payment_status']}, ref {$scenario['reference']})";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Saved training (100304, visible paid fixture)
    |--------------------------------------------------------------------------
    */
    $saved = $pdo->prepare('SELECT id FROM saved_trainings WHERE student_id = ? AND training_id = ? LIMIT 1');
    $saved->execute([TDS_STUDENT_ID, 100304]);
    if ($saved->fetchColumn() === false) {
        $pdo->prepare('INSERT INTO saved_trainings (student_id, training_id, created_at) VALUES (?, ?, NOW())')->execute([TDS_STUDENT_ID, 100304]);
        $report['saved'][] = 'INSERTED saved_trainings (student ' . TDS_STUDENT_ID . ', training 100304)';
    } else {
        $report['saved'][] = 'OK saved_trainings (student ' . TDS_STUDENT_ID . ', training 100304) already present';
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Bank-account destinations (paid accepted payment states)
    |--------------------------------------------------------------------------
    |
    | The accepted-card payment states need a configured company destination
    | to be meaningful: pending / failed / not-submitted must expose the
    | bank_account the student transfers to, and "paid" must be a true test of
    | the null-from-rule (not null from missing data). Three scenario-owning
    | companies carry no bank details today, so they are converged here using
    | COALESCE - any pre-existing configured bank values are left untouched.
    |
    */
    $bankDestinations = [
        100144 => [
            'bank_name' => 'Nile Commercial Bank',
            'bank_account_name' => 'NileTech Solutions',
            'bank_account_number' => 'DEMO-001-2211-4688-7',
            'bank_transfer_instructions' => 'Place the applicant full name in the transfer reference. Confirmations are posted within one business day.',
        ],
        100152 => [
            'bank_name' => 'Future Horizon Bank',
            'bank_account_name' => 'FutureWorks Software',
            'bank_account_number' => 'DEMO-003-8855-1199-4',
            'bank_transfer_instructions' => 'Place the applicant full name in the transfer reference. Confirmations are posted within one business day.',
        ],
        100161 => [
            'bank_name' => 'Cairo Business Bank',
            'bank_account_name' => 'TestHire Solutions',
            'bank_account_number' => 'DEMO-004-9472-3300-6',
            'bank_transfer_instructions' => 'Place the applicant full name in the transfer reference. Confirmations are posted within one business day.',
        ],
    ];

    $bankStmt = $pdo->prepare(
        'UPDATE companies SET
             bank_name = COALESCE(bank_name, ?),
             bank_account_name = COALESCE(bank_account_name, ?),
             bank_account_number = COALESCE(bank_account_number, ?),
             bank_transfer_instructions = COALESCE(bank_transfer_instructions, ?),
             updated_at = NOW()
         WHERE id = ?'
    );
    foreach ($bankDestinations as $bankCompanyId => $bank) {
        $bankStmt->execute([
            $bank['bank_name'], $bank['bank_account_name'],
            $bank['bank_account_number'], $bank['bank_transfer_instructions'],
            $bankCompanyId,
        ]);
        $report['bank'][] = "CONVERGED company #{$bankCompanyId} bank destination (demo; existing values kept)";
    }

    $visibleCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE specialization_id = " . TDS_SPECIALIZATION_ID . "
           AND status = 'published'
           AND (ends_at IS NULL OR ends_at >= NOW())"
    )->fetchColumn();
    $report['visible_count'] = $visibleCount;

    return $report;
}

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();

        $report = tds_run($pdo);
        $pdo->commit();

        foreach (['fixtures', 'anchors', 'applications', 'payments', 'saved', 'bank'] as $section) {
            foreach ($report[$section] as $line) {
                echo "  {$line}\n";
            }
        }
        echo sprintf("Spec-199 test data seeder complete. Visible spec-199 published listings now: %d.\n", $report['visible_count']);
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, 'Seeder failed: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}