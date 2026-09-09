<?php

/**
 * MASAR - Applications Test Data Seeder
 *
 * Creates a realistic application dataset for the Postman "My Applications"
 * tabs (All / Applied / Accepted / Rejected / Withdrawn) and the
 * Accept / Reject / Withdraw workflows.
 *
 * It uses the REAL application services (application_service_create,
 * application_service_accept, application_service_reject,
 * application_service_withdraw) so every row is produced through the same
 * business rules the API enforces.
 *
 * Run from the backend root:
 *     php tests/applications_test_data_seeder.php
 *
 * Idempotent: existing rows for the same (student_id, training_id) are reused.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/modules/training/services/application_service.php';

$failures = 0;

function seed_check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

$STUDENT_EMAIL     = 'mammuslim2003@gmail.com';
$TEST_COMPANY_EMAIL    = 'company@test.local';
$TEST_COMPANY_PASSWORD = 'TestCompany@123';
$TEST_COMPANY_NAME     = 'TestHire Solutions';

/*
|--------------------------------------------------------------------------
| Student
|--------------------------------------------------------------------------
*/

$student = db_fetch_one(
    "SELECT u.id AS user_id, u.email, s.id AS student_id, s.full_name
     FROM users u JOIN students s ON s.user_id = u.id
     WHERE u.email = ?",
    [$STUDENT_EMAIL]
);
seed_check("student {$STUDENT_EMAIL} exists", is_array($student));
if (!$student) {
    exit(1);
}
$student_user_id = (int) $student['user_id'];
$student_id      = (int) $student['student_id'];

echo "\nstudent user_id={$student_user_id} student_id={$student_id} name={$student['full_name']}\n";

/*
|--------------------------------------------------------------------------
| Test Company (for Accept / Reject / Withdraw Postman flows)
|--------------------------------------------------------------------------
*/

function get_company_id_for_user(int $user_id): int
{
    $row = db_fetch_one("SELECT id FROM companies WHERE user_id = ?", [$user_id]);
    return $row ? (int) $row['id'] : 0;
}

function find_user_by_email(string $email): ?array
{
    return db_fetch_one("SELECT id FROM users WHERE email = ?", [$email]);
}

$company_user = find_user_by_email($TEST_COMPANY_EMAIL);
if ($company_user) {
    $company_user_id = (int) $company_user['id'];
    $company_id      = get_company_id_for_user($company_user_id);
    echo "\ntest company user exists: user_id={$company_user_id} company_id={$company_id}\n";
} else {
    $now = date('Y-m-d H:i:s');
    db_execute(
        "INSERT INTO users (role, email, password_hash, status, email_verified_at, created_at, updated_at)
         VALUES ('company', ?, ?, 'active', ?, ?, ?)",
        [$TEST_COMPANY_EMAIL, password_hash($TEST_COMPANY_PASSWORD, PASSWORD_DEFAULT), $now, $now, $now]
    );
    $company_user_id = (int) db_last_insert_id();
    db_execute(
        "INSERT INTO companies (user_id, legal_name, description, phone, city, approval_status, approved_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 'approved', ?, ?, ?)",
        [
            $company_user_id,
            $TEST_COMPANY_NAME,
            'Test company account used to exercise the Accept, Reject and Withdraw application workflows through the API.',
            '01000000000',
            'Alexandria',
            $now,
            $now,
            $now,
        ]
    );
    $company_id = (int) db_last_insert_id();
    echo "\ncreated test company user_id={$company_user_id} company_id={$company_id}\n";
}
seed_check('test company resolved', $company_user_id > 0 && $company_id > 0);

/*
|--------------------------------------------------------------------------
| Test Company Trainings
|--------------------------------------------------------------------------
*/

function find_training_by_title(string $title): ?array
{
    return db_fetch_one(
        "SELECT id, company_id FROM training_listings WHERE title = ? LIMIT 1",
        [$title]
    );
}

function create_training(string $title, string $description, int $company_id, int $specialization_id, string $training_type, string $mode): int
{
    $existing = find_training_by_title($title);
    if ($existing) {
        // Self-heal the dataset invariant used by the date-normalization tests:
        // application_deadline must equal starts_at for every training.
        db_execute(
            "UPDATE training_listings
             SET application_deadline = starts_at
             WHERE id = ? AND application_deadline <> starts_at",
            [(int) $existing['id']]
        );
        return (int) $existing['id'];
    }

    $now = date('Y-m-d H:i:s');
    $starts = date('Y-m-d H:i:s', strtotime('+10 days'));
    $ends   = date('Y-m-d H:i:s', strtotime('+100 days'));
    $deadline = $starts;

    db_execute(
        "INSERT INTO training_listings
            (company_id, specialization_id, title, description, training_type, mode,
             may_lead_to_employment, is_paid, capacity, status, published_at,
             starts_at, ends_at, application_deadline, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, 0, 0, 10, 'published', ?, ?, ?, ?, ?, ?)",
        [
            $company_id,
            $specialization_id,
            $title,
            $description,
            $training_type,
            $mode,
            $now,
            $starts,
            $ends,
            $deadline,
            $now,
            $now,
        ]
    );
    return (int) db_last_insert_id();
}

// Real specialization rows currently used by the dataset (probed from the live DB).
$test_trainings = [
    ['title' => 'Backend Laravel Internship',      'type' => 'hands_on',     'mode' => 'remote',  'spec' => 199],
    ['title' => 'Frontend React Internship',       'type' => 'hands_on',     'mode' => 'hybrid',  'spec' => 103],
    ['title' => 'Data Analytics Internship',       'type' => 'project_based','mode' => 'hybrid',  'spec' => 92],
    ['title' => 'DevOps Fundamentals',             'type' => 'hands_on',     'mode' => 'remote',  'spec' => 110],
    ['title' => 'Mobile Flutter Internship',       'type' => 'project_based','mode' => 'remote',  'spec' => 122],
    ['title' => 'Cybersecurity Essentials',        'type' => 'hands_on',     'mode' => 'onsite',  'spec' => 94],
];

$test_training_ids = [];
foreach ($test_trainings as $i => $tt) {
    $tid = create_training(
        $tt['title'],
        'Hands-on ' . strtolower($tt['title']) . ' program offered to students.',
        $company_id,
        (int) $tt['spec'],
        $tt['type'],
        $tt['mode']
    );
    $test_training_ids[$i] = $tid;
    echo "  test training #{$tid} = {$tt['title']}\n";
}

/*
|--------------------------------------------------------------------------
| Application Creation Helper
|--------------------------------------------------------------------------
*/

function student_answers_for(int $training_id): array
{
    $questions = db_fetch_all(
        "SELECT id, question_name FROM (
            SELECT id, question AS question_name, question_type, options, sort_order
            FROM training_questions WHERE training_id = ?
        ) q",
        [$training_id]
    );
    $answers = [];
    foreach ($questions as $q) {
        $answers[] = ['question_id' => (int) $q['id'], 'answer' => 'I am excited about this training.'];

    }
    return $answers;
}

function create_or_reuse_application(int $student_user_id, int $student_id, int $training_id): array
{
    $existing = db_fetch_one(
        "SELECT id, status FROM training_applications WHERE training_id = ? AND student_id = ? LIMIT 1",
        [$training_id, $student_id]
    );
    if ($existing) {
        return ['id' => (int) $existing['id'], 'status' => $existing['status'], 'created' => false];
    }

    $cv = db_fetch_one(
        "SELECT id FROM files WHERE user_id = ? AND type = 'cv' ORDER BY id DESC LIMIT 1",
        [$student_user_id]
    );

    $data = [
        'training_id'    => $training_id,
        'full_name'      => 'Mohamed Ahmed',
        'email'          => 'mammuslim2003@gmail.com',
        'phone'          => '01000000000',
        'city'           => 'Alexandria',
        'address'        => 'Alexandria, Egypt',
        'why_interested' => 'I want practical experience building real products in a professional team.',
        'what_to_learn'  => 'Hands-on tools, workflows and best practices used in production.',
        'skills'         => ['PHP', 'SQL', 'Teamwork'],
        'cv_file_id'     => $cv ? (int) $cv['id'] : 0,
        'university'     => 'Alexandria University',
        'faculty_id'     => null,
        'applicant_type' => 'student',
        'academic_year'  => '3rd year',
        'graduation_year'=> null,
        'motivation'     => 'Looking forward to joining this training.',
        'cover_letter'   => 'I am a motivated student eager to learn from the team.',
        'answers'        => [],
    ];

    $result = application_service_create($student_user_id, $training_id, $data);

    if (empty($result['success']) || empty($result['data']['id'])) {
        return [
            'id' => 0,
            'status' => null,
            'created' => false,
            'error' => $result['message'] ?? 'create failed',
            'result' => $result,
        ];
    }

    return ['id' => (int) $result['data']['id'], 'status' => 'submitted', 'created' => true];
}

function company_owner_user_for_training(int $training_id): int
{
    $row = db_fetch_one(
        "SELECT c.user_id FROM training_listings t JOIN companies c ON c.id = t.company_id WHERE t.id = ?",
        [$training_id]
    );
    return $row ? (int) $row['user_id'] : 0;
}

/*
|--------------------------------------------------------------------------
| Seed Plan
|--------------------------------------------------------------------------
|
|  t-trainings (owned by test company) :
|    [0] Backend Laravel Internship  -> Accept workflow target    (submitted)
|    [1] Frontend React Internship   -> Reject workflow target    (submitted)
|    [2] Data Analytics Internship   -> Withdraw workflow target  (submitted)
|    [3] DevOps Fundamentals         -> accepted before the flow  (accepted, for 409 tests)
|    [4] Mobile Flutter Internship   -> rejected before the flow  (rejected, for 409 tests)
|    [5] Cybersecurity Essentials    -> stays pending             (for invalid-reason 422 test)
|
|  permanent (existing companies) :
|    100247 PHP Performance & Caching Essentials -> submitted (Applied tab)
|    100261 existing application #1864           -> submitted (Applied tab, untouched)
|    100262 Machine Learning API Deployment      -> accepted
|    100264 Database Design for Analytics        -> rejected
|    100256 PostgreSQL Power User Track          -> withdrawn
|    100255 GraphQL API Design                   -> withdrawn
|--------------------------------------------------------------------------
*/

echo "\n== Seeding applications (student {$student_id}) ==\n";

$apps = [];

// Accept/Reject/Withdraw workflow targets (test company trainings)
$apps['workflow_accept']   = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[0]);
$apps['workflow_reject']   = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[1]);
$apps['workflow_withdraw'] = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[2]);
// Invalid-state targets
$apps['invalid_accepted']  = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[3]);
$apps['invalid_rejected']  = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[4]);
$apps['invalid_reason']    = create_or_reuse_application($student_user_id, $student_id, $test_training_ids[5]);
// Permanent dataset
$apps['perm_applied']      = create_or_reuse_application($student_user_id, $student_id, 100247);
$apps['perm_accepted_a']   = create_or_reuse_application($student_user_id, $student_id, 100262);
$apps['perm_rejected_a']   = create_or_reuse_application($student_user_id, $student_id, 100264);
$apps['perm_withdrawn_a']  = create_or_reuse_application($student_user_id, $student_id, 100256);
$apps['perm_withdrawn_b']  = create_or_reuse_application($student_user_id, $student_id, 100255);

foreach ($apps as $key => $app) {
    $ok = ($app['id'] ?? 0) > 0;
    seed_check("create {$key} application" . ($ok ? " (#{$app['id']})" : ''), $ok);
    if (!$ok) {
        echo "      error: " . ($app['error'] ?? 'unknown') . "\n";
        if (!empty($app['result'])) {
            echo "      result: " . json_encode($app['result']) . "\n";
        }
    }
}

/*
|--------------------------------------------------------------------------
| State Transitions
|--------------------------------------------------------------------------
*/

function to_pending_or_submitted(?string $status): bool
{
    return in_array(strtolower((string) $status), ['submitted', 'pending'], true);
}

echo "\n== Transitions ==\n";

// Accept workflow stays submitted until the Postman run.
seed_check('workflow_accept is submitted (ready for Accept flow)',
    to_pending_or_submitted($apps['workflow_accept']['status'] ?? null));
seed_check('workflow_reject is submitted (ready for Reject flow)',
    to_pending_or_submitted($apps['workflow_reject']['status'] ?? null));
seed_check('workflow_withdraw is submitted (ready for Withdraw flow)',
    to_pending_or_submitted($apps['workflow_withdraw']['status'] ?? null));

// invalid_accepted -> accepted (for 409 already-accepted tests)
if (to_pending_or_submitted($apps['invalid_accepted']['status'] ?? null)) {
    $r = application_service_accept($company_user_id, (int) $apps['invalid_accepted']['id']);
    seed_check('invalid_accepted transitioned to accepted', ($r['success'] ?? false) === true);
    $apps['invalid_accepted']['status'] = 'accepted';
} else {
    seed_check('invalid_accepted already accepted', ($apps['invalid_accepted']['status'] ?? null) === 'accepted');
}

// invalid_rejected -> rejected (for 409 already-rejected tests)
if (to_pending_or_submitted($apps['invalid_rejected']['status'] ?? null)) {
    $r = application_service_reject($company_user_id, (int) $apps['invalid_rejected']['id'], [
        'rejection_reason' => 'Candidate did not meet minimum requirements',
        'rejection_note'   => 'Profile does not match the position requirements.',
    ]);
    seed_check('invalid_rejected transitioned to rejected', ($r['success'] ?? false) === true);
    $apps['invalid_rejected']['status'] = 'rejected';
} else {
    seed_check('invalid_rejected already rejected', ($apps['invalid_rejected']['status'] ?? null) === 'rejected');
}

// Permanent accepted
if (to_pending_or_submitted($apps['perm_accepted_a']['status'] ?? null)) {
    $r = application_service_accept(company_owner_user_for_training(100262), (int) $apps['perm_accepted_a']['id']);
    seed_check('perm_accepted (100262) accepted', ($r['success'] ?? false) === true);
    $apps['perm_accepted_a']['status'] = 'accepted';
} else {
    seed_check('perm_accepted (100262) already accepted', ($apps['perm_accepted_a']['status'] ?? null) === 'accepted');
}

// Permanent rejected
if (to_pending_or_submitted($apps['perm_rejected_a']['status'] ?? null)) {
    $r = application_service_reject(company_owner_user_for_training(100264), (int) $apps['perm_rejected_a']['id'], [
        'rejection_reason' => 'Training program discontinued',
        'rejection_note'   => 'This training has been discontinued.',
    ]);
    seed_check('perm_rejected (100264) rejected', ($r['success'] ?? false) === true);
    $apps['perm_rejected_a']['status'] = 'rejected';
} else {
    seed_check('perm_rejected (100264) already rejected', ($apps['perm_rejected_a']['status'] ?? null) === 'rejected');
}

// Permanent withdrawn
foreach (['perm_withdrawn_a' => 100256, 'perm_withdrawn_b' => 100255] as $key => $training_id) {
    if (to_pending_or_submitted($apps[$key]['status'] ?? null)) {
        $r = application_service_withdraw($student_user_id, (int) $apps[$key]['id']);
        seed_check("{$key} ({$training_id}) withdrawn", ($r['success'] ?? false) === true);
        $apps[$key]['status'] = 'withdrawn';
    } else {
        seed_check("{$key} ({$training_id}) already withdrawn", ($apps[$key]['status'] ?? null) === 'withdrawn');
    }
}

/*
|--------------------------------------------------------------------------
| Report
|--------------------------------------------------------------------------
*/

echo "\n== Final dataset for student {$student_id} (mammuslim2003@gmail.com) ==\n";

$rows = db_fetch_all(
    "SELECT id, training_id, company_id, status, rejection_reason, applied_at, reviewed_at, withdrawn_at
     FROM training_applications WHERE student_id = ? ORDER BY status, id",
    [$student_id]
);
$counts = ['submitted' => 0, 'accepted' => 0, 'rejected' => 0, 'withdrawn' => 0];
foreach ($rows as $r) {
    $s = $r['status'];
    $counts[$s] = ($counts[$s] ?? 0) + 1;
    echo "  app#{$r['id']} training={$r['training_id']} company={$r['company_id']} status={$s} reason=" . var_export($r['rejection_reason'], true) . "\n";
}
echo "  counts: " . json_encode($counts) . "\n";

echo "\n== Collection variables to set in MASAR.json ==\n";
$var_map = [
    'accept_application_id'     => $apps['workflow_accept'],
    'reject_application_id'     => $apps['workflow_reject'],
    'withdraw_application_id'   => $apps['workflow_withdraw'],
    'accepted_application_id'   => $apps['invalid_accepted'],
    'rejected_application_id'   => $apps['invalid_rejected'],
    'invalid_reason_application_id' => $apps['invalid_reason'],
    'applied_application_id'    => $apps['perm_applied'],
    'foreign_pending_application_id' => $apps['perm_applied'],
];
foreach ($var_map as $var => $app) {
    echo "  {$var} = " . ($app['id'] ?? '') . "\n";
}

echo "\n== Company account ==\n";
echo "  company_email = company@test.local\n";
echo "  company_password = TestCompany@123\n";
echo "  company_id = {$company_id}  company_user_id = {$company_user_id}\n";

echo "\n=== " . ($failures === 0 ? 'ALL PASS' : ($failures . " FAILURE(S)")) . " ===\n";
exit($failures === 0 ? 0 : 1);