<?php

/**
 * MASAR - Training Application University Regression Test
 *
 * Regression test for converting training_applications from `university_id`
 * (ID reference) to `university` (free text).
 *
 * It drives the REAL service + repository against the live database and
 * asserts that the submitted `university` text value actually persists to the
 * `training_applications.university` column and comes back in the response.
 * It also mirrors the multipart/form-data route handler build in
 * routes/applications.php so a form-data request with `university` works.
 *
 * Run from the backend root:
 *     php tests/applications_university_regression.php
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
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/modules/training/services/application_service.php';
require_once BASE . 'app/modules/training/validators/application_validator.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

// ---- Fixture: an existing student with a CV file on file ----
$student = db_fetch_one(
    "
    SELECT s.id AS sid, s.user_id AS uid, s.full_name
    FROM students s
    JOIN users u ON u.id = s.user_id
    ORDER BY s.id DESC
    LIMIT 1
    "
);
if (!$student) {
    echo "SKIP - no student fixture available\n";
    exit(0);
}

$user_id = (int) $student['uid'];
$sid     = (int) $student['sid'];

$cv = db_fetch_one(
    "SELECT id FROM files WHERE user_id = ? AND type = 'cv' ORDER BY id DESC LIMIT 1",
    [$user_id]
);
$cv_file_id = $cv ? (int) $cv['id'] : 0;
if ($cv_file_id === 0) {
    echo "SKIP - fixture student has no CV file; cannot exercise full create pipeline\n";
    exit(0);
}

/**
 * Pick a training the fixture student has NOT applied to yet, so every create
 * below is an independent, non-duplicate insertion.
 */
function pick_fresh_training(int $sid): int
{
    $t = db_fetch_one(
        "
        SELECT t.id
        FROM training_listings t
        LEFT JOIN training_applications a
               ON a.training_id = t.id AND a.student_id = ?
        WHERE a.id IS NULL
          AND t.status IN ('published', 'open', 'active')
          AND (t.application_deadline IS NULL OR t.application_deadline > NOW())
        ORDER BY t.id DESC
        LIMIT 1
        ",
        [$sid]
    );
    return $t ? (int) $t['id'] : 0;
}

/**
 * Mirror routes/applications.php multipart branch: read `university` from the
 * form data and forward it into the application payload.
 */
function route_build_application_data(int $sid, int $training_id, int $cv_file_id, array $overrides = []): array
{
    $full_name      = request_input('full_name') ?? '';
    $university     = request_input('university') ?? null;
    $applicant_type = request_input('applicant_type') ?? 'student';
    $academic_year  = request_input('academic_year') ?? '';
    $why_interested = request_input('why_interested') ?? '';
    $what_to_learn  = request_input('what_to_learn') ?? '';

    return array_merge([
        'training_id'    => $training_id,
        'full_name'      => $full_name,
        'university'     => $university,
        'academic_year'  => $academic_year,
        'applicant_type' => $applicant_type,
        'why_interested' => $why_interested,
        'what_to_learn'  => $what_to_learn,
        'cv_file_id'     => $cv_file_id,
    ], $overrides);
}

/** Create an application via the real service for a fresh training. */
function create_app(int $user_id, int $sid, int $cv_file_id, string $university): array
{
    $training_id = pick_fresh_training($sid);
    if ($training_id === 0) {
        echo "SKIP - no fresh training available for fixture student\n";
        exit(0);
    }

    // Set up the multipart/form-data surface the route reads from.
    $_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=----regression';
    $_POST['training_id']    = (string) $training_id;
    $_POST['university']     = $university;
    $_POST['academic_year']  = '3rd';
    $_POST['applicant_type'] = 'student';
    $_POST['why_interested'] = 'Interested in gaining practical experience.';
    $_POST['what_to_learn']  = 'Real production skills.';

    $data   = route_build_application_data($sid, $training_id, $cv_file_id);
    $result = application_service_create($user_id, $training_id, $data);

    return [
        'result'      => $result,
        'training_id' => $training_id,
    ];
}
$created_ids = [];

echo "== TEST 01: university text persists to DB and response ==\n";
$r = create_app($user_id, $sid, $cv_file_id, 'Cairo University');
check('create returns success (would be HTTP 201)', ($r['result']['success'] ?? null) === true);
check('response data.university === "Cairo University"', ($r['result']['data']['university'] ?? null) === 'Cairo University');
if (!empty($r['result']['data']['id'])) {
    $id = (int) $r['result']['data']['id'];
    $created_ids[] = $id;
    $row = db_fetch_one('SELECT university FROM training_applications WHERE id = ?', [$id]);
    check('DB training_applications.university === "Cairo University"', ($row['university'] ?? null) === 'Cairo University');
}

echo "\n== TEST 02: a different university text value ==\n";
$r = create_app($user_id, $sid, $cv_file_id, 'Alexandria University');
if (!empty($r['result']['data']['id'])) {
    $id = (int) $r['result']['data']['id'];
    $created_ids[] = $id;
    $row = db_fetch_one('SELECT university FROM training_applications WHERE id = ?', [$id]);
    check('DB university === "Alexandria University"', ($row['university'] ?? null) === 'Alexandria University');
} else {
    check('create for second value succeeds', false);
}

echo "\n== TEST 03: missing/empty university stays optional (stored NULL) ==\n";
$r = create_app($user_id, $sid, $cv_file_id, '');
check('empty university still succeeds (optional)', ($r['result']['success'] ?? null) === true);
if (!empty($r['result']['data']['id'])) {
    $id = (int) $r['result']['data']['id'];
    $created_ids[] = $id;
}
check('omitted/empty university yields an empty (non-error) response', in_array($r['result']['data']['university'] ?? null, ['', null], true));

$r = create_app($user_id, $sid, $cv_file_id, '   ');
check('whitespace-only university treated as optional (success)', ($r['result']['success'] ?? null) === true);
if (!empty($r['result']['data']['id'])) {
    $created_ids[] = (int) $r['result']['data']['id'];
}

echo "\n== TEST 04: validator rejects invalid university values under `university` key ==\n";
$fresh = pick_fresh_training($sid);
$base  = ['training_id' => $fresh, 'why_interested' => 'x', 'what_to_learn' => 'y', 'academic_year' => '3rd', 'applicant_type' => 'student'];

$v = application_validator_create($base + ['university' => str_repeat('A', 256)]);
check('over-length university rejected via errors.university', isset($v['errors']['university']));

$v = application_validator_create($base + ['university' => 123]);
check('non-string university rejected via errors.university', isset($v['errors']['university']));

$v = application_validator_create($base + ['university_id' => '52']);
check('legacy university_id is no longer validated (no university_id error)', !isset($v['errors']['university_id']));

echo "\n== TEST 05: GET/list paths expose university text from column ==\n";
if (!empty($created_ids)) {
    $id = $created_ids[0];
    $app = application_repository_find_by_id($id);
    check('find_by_id exposes university text', ($app['university'] ?? null) === 'Cairo University');
    check('find_by_id has no university_id field', !array_key_exists('university_id', $app));
}

echo "\n=== " . ($failures === 0 ? 'ALL PASS' : ($failures . " FAILURE(S)")) . " ===\n";

// ---- Cleanup created rows ----
if (!empty($created_ids)) {
    $sql   = 'DELETE FROM training_applications WHERE id IN (' . implode(',', array_fill(0, count($created_ids), '?')) . ')';
    $del   = db_execute($sql, $created_ids);
    echo "cleanup: deleted " . $del->rowCount() . " test application(s)\n";
}

exit($failures === 0 ? 0 : 1);
