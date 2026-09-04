<?php

/**
 * MASAR - Training Search & Filter Specialization Scope + is_saved Regression
 *
 * Regression test for two Search/Filter bug fixes:
 *
 *   BUG #1 - Search/Filter must be restricted to the CURRENT student's own
 *            specialization. A logged-in student with Specialization A must
 *            never see a published training that belongs to Specialization B,
 *            even if the search text matches that training's fields. Matching
 *            is an exact equality on the training's single specialization
 *            (training_listings.specialization_id).
 *
 *   BUG #2 - The `is_saved` flag returned for each training card must be
 *            computed for the CURRENT authenticated student (per saved_trainings
 *            row), never globally.
 *
 * The test drives the REAL search_service + search_repository against the live
 * database with isolated fixtures (a company, two specializations, two
 * published trainings, two students) and asserts:
 *   1. Search returns only the training in the student's specialization.
 *   2. Search for text matching only the out-of-scope training returns nothing.
 *   3. Multi-specialization scope (array of ids) matches by exact
 *      specialization_id equality (IN).
 *   4. is_saved = 1 for a training the current student saved.
 *   5. is_saved = 0 for a training the current student did not save.
 *   6. A training saved by another student is still is_saved = 0 for the
 *      current student.
 *   7. total (count) only counts in-scope trainings.
 *   8. The same specialization restriction works for BOTH Search and Filters.
 *
 * Run from the backend root:
 *     php tests/search_training_scope_regression.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/config/constants.php';
require_once BASE . 'app/core/database/connection.php';
require_once BASE . 'app/modules/search/services/search_service.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

/** Find the highest existing id in a table so fixtures never collide. */
function next_free_id(PDO $pdo, string $table, string $col): int
{
    $max = (int) $pdo->query("SELECT COALESCE(MAX(`{$col}`), 0) FROM `{$table}`")->fetchColumn();
    return $max + 1;
}

$pdo = get_database_connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$createdUsers = [];
$createdStudents = [];
$createdCompanies = [];
$createdTrainings = [];
$createdSpecializations = [];
$createdSaved = [];

try {
    // ---------------------------------------------------------------------
    // Fixtures
    // ---------------------------------------------------------------------
    $specA = next_free_id($pdo, 'specializations', 'id');
    $specB = $specA + 1;
    $specNameA = 'RSD Spec Alpha ' . bin2hex(random_bytes(3));
    $specNameB = 'RSD Spec Beta ' . bin2hex(random_bytes(3));

    $insSpec = $pdo->prepare(
        "INSERT INTO specializations (id, name, is_active, created_at, updated_at)
         VALUES (?, ?, 1, NOW(), NOW())"
    );
    $insSpec->execute([$specA, $specNameA]);
    $createdSpecializations[] = $specA;
    $insSpec->execute([$specB, $specNameB]);
    $createdSpecializations[] = $specB;

    // Company (publisher of both trainings)
    $insUser = $pdo->prepare(
        "INSERT INTO users (id, role, email, password_hash, status, email_verified_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'active', NOW(), NOW(), NOW())"
    );
    $companyUid = next_free_id($pdo, 'users', 'id');
    $insUser->execute([$companyUid, 'company', 'rsd.company.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $companyUid;

    $companyCid = next_free_id($pdo, 'companies', 'id');
    $insCompany = $pdo->prepare(
        "INSERT INTO companies (id, user_id, legal_name, description, approval_status, approved_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'approved', NOW(), NOW(), NOW())"
    );
    $insCompany->execute([$companyCid, $companyUid, 'RSD Test Corp', 'Regression fixture company']);
    $createdCompanies[] = $companyCid;

    // Two students: A (specA) and B (specB), plus a third user for a saved-by-other check
    $studentAuid = $companyUid + 1;
    $insUser->execute([$studentAuid, 'student', 'rsd.studentA.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $studentAuid;

    $studentBuid = $studentAuid + 1;
    $insUser->execute([$studentBuid, 'student', 'rsd.studentB.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $studentBuid;

    $insStudent = $pdo->prepare(
        "INSERT INTO students (id, user_id, full_name, field_id, specialization_id, is_profile_complete, created_at, updated_at)
         VALUES (?, ?, ?, NULL, ?, 1, NOW(), NOW())"
    );
    $studentAsid = next_free_id($pdo, 'students', 'id');
    $insStudent->execute([$studentAsid, $studentAuid, 'RSD Student A', $specA]);
    $createdStudents[] = $studentAsid;

    $studentBsid = $studentAsid + 1;
    $insStudent->execute([$studentBsid, $studentBuid, 'RSD Student B', $specB]);
    $createdStudents[] = $studentBsid;

    // Student C shares Student A's specialization (specA) but saves nothing.
    $studentCuid = $studentBuid + 1;
    $insUser->execute([$studentCuid, 'student', 'rsd.studentC.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $studentCuid;
    $studentCsid = $studentBsid + 1;
    $insStudent->execute([$studentCsid, $studentCuid, 'RSD Student C', $specA]);
    $createdStudents[] = $studentCsid;

    // Two published trainings, one per specialization, sharing a keyword
    // in their title/description so the search query matches both fields.
    $kw = 'ZebraQuery' . bin2hex(random_bytes(3));
    $trainingAid = next_free_id($pdo, 'training_listings', 'id');
    $trainingBid = $trainingAid + 1;

    $insTraining = $pdo->prepare(
        "INSERT INTO training_listings
            (id, company_id, specialization_id, title, description, training_type, mode, is_paid, status,
             published_at, application_deadline, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 'hands_on', 'onsite', 0, 'published', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), NOW(), NOW())"
    );
    $insTraining->execute([$trainingAid, $companyCid, $specA, "Spec A {$kw} Engineering", "Spec A only {$kw}"]);
    $createdTrainings[] = $trainingAid;
    $insTraining->execute([$trainingBid, $companyCid, $specB, "Spec B {$kw} Marketing", "Spec B only {$kw}"]);
    $createdTrainings[] = $trainingBid;

    // Single primary specialization per training: A -> specA, B -> specB.
    // (company/admin enrichment pivot is kept for the company view.)
    $insTsp = $pdo->prepare("INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)");
    $insTsp->execute([$trainingAid, $specA]);
    $insTsp->execute([$trainingBid, $specB]);

    // Saved: Student A saved Training A; Student B saved Training B.
    // Neither saved the OTHER's training.
    $insSave = $pdo->prepare("INSERT INTO saved_trainings (student_id, training_id, created_at) VALUES (?, ?, NOW())");
    $insSave->execute([$studentAsid, $trainingAid]);
    $createdSaved[] = $pdo->lastInsertId();
    $insSave->execute([$studentBsid, $trainingBid]);
    $createdSaved[] = $pdo->lastInsertId();

    // ---------------------------------------------------------------------
    // TEST 1 & 2 & 4 & 5 & 7 : Search scoped to Student A's specialization
    // ---------------------------------------------------------------------
    echo "== SEARCH endpoint (Student A, spec A) ==\n";
    $res = search_service_search($kw, [
        'type' => 'trainings',
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
    ]);
    $ids = array_map(static fn ($it) => (int) $it['id'], $res['items'] ?? []);
    $byId = [];
    foreach (($res['items'] ?? []) as $it) {
        $byId[(int) $it['id']] = $it;
    }

    check('TEST1 search returns Student A\'s in-scope training', in_array($trainingAid, $ids, true));
    check('TEST1 search does NOT return out-of-scope training B', !in_array($trainingBid, $ids, true));
    check('TEST7 count (total) only counts in-scope trainings', (int) ($res['total'] ?? 0) === 1);
    check('TEST4 current student saved training A => is_saved=1', (int) ($byId[$trainingAid]['is_saved'] ?? 0) === 1);
    // TEST5 & TEST6 (current student did not save => is_saved=0 while another did)
    // are asserted below via Student C (shared specialization, did not save A).

    echo "\n== TEST2: keyword matching ONLY training B ==\n";
    $resB = search_service_search("Spec B only", [
        'type' => 'trainings',
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
    ]);
    check('TEST2 out-of-scope match on description is NOT returned', !in_array($trainingBid, array_map(static fn ($it) => (int) $it['id'], $resB['items'] ?? []), true));
    check('TEST2 total is 0 for out-of-scope keyword', (int) ($resB['total'] ?? 0) === 0);

    echo "\n== TEST5 & TEST6: training in scope but NOT saved by current student => is_saved=0 ==\n";
    // Student C has the SAME specialization as A, so T_A is in C's scope and C SEES it.
    // Student A saved T_A; Student C did NOT save it -> C must see is_saved=0 even though
    // Student A saved the very same training (is_saved is scoped to the current student).
    $resC = search_service_search($kw, [
        'type' => 'trainings', 'user_id' => $studentCuid, 'role' => 'student', 'page' => 1, 'limit' => 100,
    ]);
    $cBy = [];
    foreach (($resC['items'] ?? []) as $it) { $cBy[(int) $it['id']] = $it; }
    check('TEST5 Student C sees in-scope training A', isset($cBy[$trainingAid]));
    check('TEST5 current student (C) did not save A => is_saved=0', (int) ($cBy[$trainingAid]['is_saved'] ?? -1) === 0);
    check('TEST6 another student (A) saved the same training but C still is_saved=0', (int) ($cBy[$trainingAid]['is_saved'] ?? -1) === 0);

    echo "\n== TEST8a: FILTERS endpoint scoped to Student A ==\n";
    $resF = search_service_trainings_filters([
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
    ]);
    $fIds = array_map(static fn ($it) => (int) $it['id'], $resF['items'] ?? []);
    check('TEST8 filters returns only in-scope training A', in_array($trainingAid, $fIds, true) && !in_array($trainingBid, $fIds, true));
    check('TEST8 filter count only counts in-scope trainings', (int) ($resF['total'] ?? 0) === 1);

    echo "\n== TEST8b: FILTERS with type filter still scoped ==\n";
    $resF2 = search_service_trainings_filters([
        'user_id' => $studentAuid,
        'role' => 'student',
        'training_type' => 'project_based', // no training matches this; must stay scoped + yield 0
        'page' => 1,
        'limit' => 100,
    ]);
    check('TEST8 filter unaffected by scope; type filter yields empty (no leak)', (int) ($resF2['total'] ?? 0) === 0);

    echo "\n== Search/Filters for Student B: sees B with is_saved=1, not A ==\n";
    $resBsearch = search_service_search($kw, [
        'type' => 'trainings', 'user_id' => $studentBuid, 'role' => 'student', 'page' => 1, 'limit' => 100,
    ]);
    $bIds = array_map(static fn ($it) => (int) $it['id'], $resBsearch['items'] ?? []);
    $bBy = [];
    foreach (($resBsearch['items'] ?? []) as $it) { $bBy[(int) $it['id']] = $it; }
    check('Student B sees training B (in scope)', in_array($trainingBid, $bIds, true));
    check('Student B does NOT see training A (out of scope)', !in_array($trainingAid, $bIds, true));
    check('Student B is_saved=1 for B (B saved it)', (int) ($bBy[$trainingBid]['is_saved'] ?? -1) === 1);
    check('Student B total = 1 (only in-scope)', (int) ($resBsearch['total'] ?? 0) === 1);

    echo "\n== TEST3: multi-specialization scope (array of ids) matches by exact specialization_id ==\n";
    // With one-specialization-per-training, the student's scope [A,B] becomes
    // an exact `t.specialization_id IN (A,B)` match: A and B are both eligible.
    $scope = search_repository_specialization_scope([$specA, $specB]);
    $rows = search_repository_fetch_all(
        "SELECT t.id FROM training_listings t WHERE t.status='published' AND t.id IN (:a, :b) {$scope}",
        [':a' => $trainingAid, ':b' => $trainingBid]
    );
    $scopeIds = array_map(static fn ($r) => (int) $r['id'], $rows);
    check('TEST3 scope with [A,B] matches BOTH trainings', in_array($trainingAid, $scopeIds, true) && in_array($trainingBid, $scopeIds, true));

    echo "\n== Student without specializations never bypasses scope (empty result) ==\n";
    $noSpecUid = $studentCuid + 1;
    $insUser->execute([$noSpecUid, 'student', 'rsd.nospec.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $noSpecUid;
    $noSpecSid = $studentCsid + 1;
    $insStudent->execute([$noSpecSid, $noSpecUid, 'RSD No Spec', null]); // specialization_id NULL
    $createdStudents[] = $noSpecSid;
    $resNo = search_service_search($kw, [
        'type' => 'trainings', 'user_id' => $noSpecUid, 'role' => 'student', 'page' => 1, 'limit' => 100,
    ]);
    check('No-spec student gets empty (does not leak whole DB)', (int) ($resNo['total'] ?? 0) === 0 && empty($resNo['items'] ?? []));

    echo "\n" . '===' . ($failures === 0 ? ' ALL PASS' : (" {$failures} FAILURE(S)")) . " ===\n";

} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    $failures++;
}

// -------------------------------------------------------------------------
// Cleanup (reverse order; FK-safe)
// -------------------------------------------------------------------------
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

// Safety net: remove any residual rows (including from interrupted runs) by
// our unique markers, regardless of which ids were tracked this run.
$testEmailLike = 'rsd.%@test.local';
$stIds = $pdo->query("SELECT id FROM users WHERE email LIKE '{$testEmailLike}'")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($stIds)) {
    $pdo->exec('DELETE FROM companies WHERE user_id IN (' . implode(',', $stIds) . ')');
    $pdo->exec('DELETE FROM students WHERE user_id IN (' . implode(',', $stIds) . ')');
}
$kwRows = $pdo->query("SELECT id FROM training_listings WHERE title LIKE '%ZebraQuery%'")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($kwRows)) {
    $pdo->exec('DELETE FROM training_specializations WHERE training_id IN (' . implode(',', $kwRows) . ')');
    $pdo->exec('DELETE FROM training_listings WHERE id IN (' . implode(',', $kwRows) . ')');
}

if (!empty($createdSaved)) {
    $pdo->exec('DELETE FROM saved_trainings WHERE id IN (' . implode(',', $createdSaved) . ')');
}
if (!empty($createdTrainings)) {
    $pdo->exec('DELETE FROM training_specializations WHERE training_id IN (' . implode(',', $createdTrainings) . ')');
    $pdo->exec('DELETE FROM training_listings WHERE id IN (' . implode(',', $createdTrainings) . ')');
}
if (!empty($createdCompanies)) {
    $pdo->exec('DELETE FROM companies WHERE id IN (' . implode(',', $createdCompanies) . ')');
}
if (!empty($createdStudents)) {
    $pdo->exec('DELETE FROM students WHERE id IN (' . implode(',', $createdStudents) . ')');
}
if (!empty($createdUsers)) {
    $pdo->exec('DELETE FROM users WHERE id IN (' . implode(',', $createdUsers) . ')');
}
if (!empty($createdSpecializations)) {
    $pdo->exec('DELETE FROM specializations WHERE id IN (' . implode(',', $createdSpecializations) . ')');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

exit($failures === 0 ? 0 : 1);
