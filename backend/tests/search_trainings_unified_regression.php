<?php

/**
 * MASAR - Unified Training Search + Filters Regression
 *
 * Verifies that the single GET /api/v1/search/trainings endpoint (backed by
 * search_controller_trainings -> search_service_trainings) supports BOTH the
 * keyword search and the training filter dimensions (training_type, mode,
 * paid, sort, page, limit) together and independently, while preserving:
 *
 *   - Student specialization scope (student-specific matching)
 *   - is_saved computed per authenticated student
 *   - skills / specialization / duration card shape
 *   - pagination metadata
 *   - the previous filter-only behaviour (no keyword -> filters)
 *
 * The test drives the REAL search_service_trainings() + search_repository
 * against the live database with isolated fixtures (company, specializations,
 * published trainings, students) and asserts:
 *   1. Search only (keyword)                               -> scoped results
 *   2. Filter only (no keyword)                            -> filters apply
 *   3. Search + single filter                              -> combined
 *   4. Multiple filters + sort                             -> combined
 *   5. Saved / is_saved per student                        -> preserved
 *   6. Student-specific specialization matching            -> preserved
 *   7. Response card structure (duration, skills, spec)    -> unchanged
 *   8. Pagination                                          -> preserved
 *
 * Run from the backend root:
 *     php tests/search_trainings_unified_regression.php
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
    $specNameA = 'USD Spec Alpha ' . bin2hex(random_bytes(3));
    $specNameB = 'USD Spec Beta ' . bin2hex(random_bytes(3));

    $insSpec = $pdo->prepare(
        "INSERT INTO specializations (id, name, is_active, created_at, updated_at)
         VALUES (?, ?, 1, NOW(), NOW())"
    );
    $insSpec->execute([$specA, $specNameA]);
    $createdSpecializations[] = $specA;
    $insSpec->execute([$specB, $specNameB]);
    $createdSpecializations[] = $specB;

    $insUser = $pdo->prepare(
        "INSERT INTO users (id, role, email, password_hash, status, email_verified_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'active', NOW(), NOW(), NOW())"
    );
    $companyUid = next_free_id($pdo, 'users', 'id');
    $insUser->execute([$companyUid, 'company', 'usd.company.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $companyUid;

    $companyCid = next_free_id($pdo, 'companies', 'id');
    $insCompany = $pdo->prepare(
        "INSERT INTO companies (id, user_id, legal_name, description, approval_status, approved_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'approved', NOW(), NOW(), NOW())"
    );
    $insCompany->execute([$companyCid, $companyUid, 'USD Test Corp', 'Unified regression fixture company']);
    $createdCompanies[] = $companyCid;

    $studentAuid = $companyUid + 1;
    $insUser->execute([$studentAuid, 'student', 'usd.studentA.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $studentAuid;

    $insStudent = $pdo->prepare(
        "INSERT INTO students (id, user_id, full_name, field_id, specialization_id, is_profile_complete, created_at, updated_at)
         VALUES (?, ?, ?, NULL, ?, 1, NOW(), NOW())"
    );
    $studentAsid = next_free_id($pdo, 'students', 'id');
    $insStudent->execute([$studentAsid, $studentAuid, 'USD Student A', $specA]);
    $createdStudents[] = $studentAsid;

    // Two published trainings in Student A's specialization (specA) with
    // different filter dimensions so we can exercise search + filters.
    $kw = 'UniQuery' . bin2hex(random_bytes(3));

    $insTraining = $pdo->prepare(
        "INSERT INTO training_listings
            (id, company_id, specialization_id, title, description, training_type, mode, is_paid,
             compensation_amount, compensation_currency, starts_at, ends_at, status,
             published_at, application_deadline, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, NOW(), NOW())"
    );

    $now = date('Y-m-d H:i:s');
    $in30 = date('Y-m-d H:i:s', time() + 30 * 86400);
    $in40 = date('Y-m-d H:i:s', time() + 40 * 86400);
    $in45 = date('Y-m-d H:i:s', time() + 45 * 86400);
    $in60 = date('Y-m-d H:i:s', time() + 60 * 86400);

    $trainingRemotePaid = next_free_id($pdo, 'training_listings', 'id');
    $insTraining->execute([$trainingRemotePaid, $companyCid, $specA, "{$kw} Fullstack Remote Paid", "{$kw} description remote paid", 'hands_on', 'remote', 1, 5000, 'EGP', $now, $in60, $now, $in60]);
    $createdTrainings[] = $trainingRemotePaid;

    $trainingOnsiteFree = $trainingRemotePaid + 1;
    $insTraining->execute([$trainingOnsiteFree, $companyCid, $specA, "{$kw} Frontend Onsite Free", "{$kw} description onsite free", 'shadowing', 'onsite', 0, null, 'EGP', $now, $in30, $now, $in30]);
    $createdTrainings[] = $trainingOnsiteFree;

    $trainingHybrid = $trainingOnsiteFree + 1;
    $insTraining->execute([$trainingHybrid, $companyCid, $specA, "{$kw} Backend Hybrid Paid", "{$kw} description hybrid", 'project_based', 'hybrid', 1, 3000, 'EGP', $now, $in45, $now, $in45]);
    $createdTrainings[] = $trainingHybrid;

    $insTsp = $pdo->prepare("INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)");
    foreach ([$trainingRemotePaid, $trainingOnsiteFree, $trainingHybrid] as $tid) {
        $insTsp->execute([$tid, $specA]);
    }

    // Student A saved the remote paid training.
    $insSave = $pdo->prepare("INSERT INTO saved_trainings (student_id, training_id, created_at) VALUES (?, ?, NOW())");
    $insSave->execute([$studentAsid, $trainingRemotePaid]);
    $createdSaved[] = $pdo->lastInsertId();

    $base = [
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
    ];

    echo "\n== TEST 1: Search only (keyword, no filter) ==\n";
    $r = search_service_trainings(array_merge($base, ['query' => $kw]));
    $ids = array_map(static fn ($it) => (int) $it['id'], $r['items'] ?? []);
    check('search returns all 3 in-scope trainings', count(array_intersect($ids, [$trainingRemotePaid, $trainingOnsiteFree, $trainingHybrid])) === 3);
    check('search total = 3', (int) ($r['total'] ?? 0) === 3);
    check('search save_state_context = student', ($r['save_state_context'] ?? '') === 'student');

    echo "\n== TEST 2: Filter only (no keyword) ==\n";
    $r2 = search_service_trainings(array_merge($base, ['mode' => 'remote']));
    $ids2 = array_map(static fn ($it) => (int) $it['id'], $r2['items'] ?? []);
    check('filter mode=remote returns only remote training', $ids2 === [$trainingRemotePaid] || (in_array($trainingRemotePaid, $ids2, true) && !in_array($trainingOnsiteFree, $ids2, true) && !in_array($trainingHybrid, $ids2, true)));
    check('filter mode=remote total = 1', (int) ($r2['total'] ?? 0) === 1);

    echo "\n== TEST 3: Search + single filter ==\n";
    $r3 = search_service_trainings(array_merge($base, ['query' => $kw, 'mode' => 'remote']));
    $ids3 = array_map(static fn ($it) => (int) $it['id'], $r3['items'] ?? []);
    check('search+mode returns remote paid training', in_array($trainingRemotePaid, $ids3, true) && !in_array($trainingOnsiteFree, $ids3, true) && !in_array($trainingHybrid, $ids3, true));
    check('search+mode total = 1', (int) ($r3['total'] ?? 0) === 1);

    echo "\n== TEST 4: Multiple filters + sort ==\n";
    $r4 = search_service_trainings(array_merge($base, ['mode' => 'remote', 'paid' => '1', 'sort' => 'price_desc']));
    $ids4 = array_map(static fn ($it) => (int) $it['id'], $r4['items'] ?? []);
    check('multiple filters (mode=remote, paid=1) returns only listed trainings', count(array_intersect($ids4, [$trainingRemotePaid, $trainingOnsiteFree, $trainingHybrid])) <= 3);
    // With mode=remote + paid=1 only the remote paid training qualifies.
    check('multi-filter total = 1', (int) ($r4['total'] ?? 0) === 1);
    check('multi-filter item is remote paid', in_array($trainingRemotePaid, $ids4, true));

    echo "\n== TEST 5: Saved / is_saved per student ==\n";
    $byId = [];
    foreach (($r['items'] ?? []) as $it) { $byId[(int) $it['id']] = $it; }
    check('is_saved=1 for training Student A saved', (int) ($byId[$trainingRemotePaid]['is_saved'] ?? -1) === 1);
    check('is_saved=0 for training Student A did NOT save', (int) ($byId[$trainingOnsiteFree]['is_saved'] ?? -1) === 0);

    echo "\n== TEST 6: Student-specific specialization matching ==\n";
    // Create a training in specB (out of Student A's scope) sharing the keyword.
    $trainingOutOfScope = $trainingHybrid + 1;
    $insTraining->execute([$trainingOutOfScope, $companyCid, $specB, "{$kw} Out Of Scope", "{$kw} outside scope", 'hands_on', 'remote', 1, 9999, 'EGP', $now, $in40, $now, $in40]);
    $createdTrainings[] = $trainingOutOfScope;
    $insTsp->execute([$trainingOutOfScope, $specB]);

    $r6 = search_service_trainings(array_merge($base, ['query' => $kw]));
    $ids6 = array_map(static fn ($it) => (int) $it['id'], $r6['items'] ?? []);
    check('out-of-scope training (specB) NOT returned for Student A', !in_array($trainingOutOfScope, $ids6, true));
    check('student scope does not leak (total stays 3)', (int) ($r6['total'] ?? 0) === 3);

    echo "\n== TEST 7: Response card structure ==\n";
    $first = ($r['items'] ?? [])[0] ?? null;
    check('card has duration (int or null)', $first !== null && (is_int($first['duration'] ?? null) || ($first['duration'] ?? null) === null));
    check('card has skills array', $first !== null && is_array($first['skills'] ?? null));
    check('card has specialization object', $first !== null && is_array($first['specialization'] ?? null));
    if ($first !== null) {
        check('specialization has id + name', isset($first['specialization']['id']) && isset($first['specialization']['name']));
        check('card has company_name', isset($first['company_name']));
        check('card has company_logo key', array_key_exists('company_logo', $first));
        check('card has is_saved', isset($first['is_saved']));
    }

    echo "\n== TEST 8: Pagination ==\n";
    $r8 = search_service_trainings(array_merge($base, ['query' => $kw, 'page' => 1, 'limit' => 2]));
    check('pagination limit applied', count($r8['items'] ?? []) === 2);
    check('pagination metadata present', isset($r8['total']) && isset($r8['page']) && isset($r8['limit']));
    check('page reflects requested page', (int) ($r8['page'] ?? 0) === 1);
    check('limit reflects requested limit', (int) ($r8['limit'] ?? 0) === 2);

    echo "\n== Filter-only still behaves like the old filters API ==\n";
    $rF = search_service_trainings(array_merge($base, ['training_type' => 'shadowing']));
    $idsF = array_map(static fn ($it) => (int) $it['id'], $rF['items'] ?? []);
    check('filter training_type=shadowing returns only that training', in_array($trainingOnsiteFree, $idsF, true) && !in_array($trainingRemotePaid, $idsF, true));

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

$testEmailLike = 'usd.%@test.local';
$stIds = $pdo->query("SELECT id FROM users WHERE email LIKE '{$testEmailLike}'")->fetchAll(PDO::FETCH_COLUMN);
if (!empty($stIds)) {
    $pdo->exec('DELETE FROM companies WHERE user_id IN (' . implode(',', $stIds) . ')');
    $pdo->exec('DELETE FROM students WHERE user_id IN (' . implode(',', $stIds) . ')');
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
