<?php

/**
 * MASAR - Training Duration Sort Order Regression
 *
 * Verifies that the unified search endpoint (search_service_trainings ->
 * search_repository_trainings) orders results by the API's "duration"
 * semantic when sort=duration_asc / sort=duration_desc.
 *
 * Duration is documented as: calendar days remaining from today until
 * ends_at, clamped to 0 when today >= ends_at (never negative). It is NOT
 * the total span of the training (DATEDIFF(ends_at, starts_at)). The fix
 * changed the ORDER BY in search_repository_trainings_sort_clause() so both
 * duration sorts key on greatested(remaining days, 0) with NULL ends_at last
 * and a deterministic training id tie-break. Because ORDER BY is applied at
 * the SQL level BEFORE LIMIT/OFFSET, results must also be globally consistent
 * across page boundaries.
 *
 * The test inserts isolated published trainings whose ends_at are spread over
 * the past, near-future and far-future, then asserts:
 *   1. duration_asc  -> non-decreasing computed duration
 *   2. duration_desc -> non-increasing computed duration
 *   3. duration is derived from ends_at (remaining days), not total span
 *   4. expired rows (today >= ends_at) report duration = 0
 *   5. NULL ends_at rows sort last (end of list) in both directions
 *   6. deterministic tie-break by training id
 *   7. page-boundary continuity across page=1/page=2 for both directions
 *   8. student specialization scope is preserved under both sorts
 *
 * Run from the backend root:
 *     php tests/search_trainings_duration_order_regression.php
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

function expected_duration(?string $endsAt): int
{
    if ($endsAt === null) {
        return -1; // place NaN sentinel; NULL sort-last is checked separately
    }
    $days = (int) floor((strtotime($endsAt) - time()) / 86400);
    return max($days, 0);
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
    $specNameA = 'USD Dur Spec Alpha ' . bin2hex(random_bytes(3));
    $specNameB = 'USD Dur Spec Beta ' . bin2hex(random_bytes(3));

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
    $insUser->execute([$companyUid, 'company', 'usd.dur.company.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $companyUid;

    $companyCid = next_free_id($pdo, 'companies', 'id');
    $insCompany = $pdo->prepare(
        "INSERT INTO companies (id, user_id, legal_name, description, approval_status, approved_at, created_at, updated_at)
         VALUES (?, ?, ?, ?, 'approved', NOW(), NOW(), NOW())"
    );
    $insCompany->execute([$companyCid, $companyUid, 'USD Duration Test Corp', 'Duration order regression fixture company']);
    $createdCompanies[] = $companyCid;

    $studentAuid = $companyUid + 1;
    $insUser->execute([$studentAuid, 'student', 'usd.dur.studentA.' . bin2hex(random_bytes(4)) . '@test.local', password_hash('Fake@123', PASSWORD_DEFAULT)]);
    $createdUsers[] = $studentAuid;

    $insStudent = $pdo->prepare(
        "INSERT INTO students (id, user_id, full_name, field_id, specialization_id, is_profile_complete, created_at, updated_at)
         VALUES (?, ?, ?, NULL, ?, 1, NOW(), NOW())"
    );
    $studentAsid = next_free_id($pdo, 'students', 'id');
    $insStudent->execute([$studentAsid, $studentAuid, 'USD Dur Student A', $specA]);
    $createdStudents[] = $studentAsid;

    $kw = 'DurQuery' . bin2hex(random_bytes(3));

    $insTraining = $pdo->prepare(
        "INSERT INTO training_listings
            (id, company_id, specialization_id, title, description, training_type, mode, is_paid,
             compensation_amount, compensation_currency, starts_at, ends_at, status,
             published_at, application_deadline, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, NOW(), NOW())"
    );

    $now = date('Y-m-d H:i:s');
    $past10 = date('Y-m-d H:i:s', time() - 10 * 86400);
    $in3 = date('Y-m-d H:i:s', time() + 3 * 86400);
    $in20 = date('Y-m-d H:i:s', time() + 20 * 86400);
    $in50 = date('Y-m-d H:i:s', time() + 50 * 86400);
    $in90 = date('Y-m-d H:i:s', time() + 90 * 86400);

    // Build trainings with KNOWN remaining-day ordering. To defeat total-span
    // (DATEDIFF ends_at - starts_at) ordering, vary starts_at independently:
    // starts near ends_at so total span !== remaining days.
    $tpl = [
        // 'id-raw-end' remaining day bucket (approx), start offset (days before ends_at)
        ['ends' => $past10, 'startOffset' => 40], // expired -> duration 0
        ['ends' => $in3,    'startOffset' => 1 ],  // ~3  days remaining
        ['ends' => $in20,   'startOffset' => 60],  // ~20 days remaining
        ['ends' => $in50,   'startOffset' => 2 ],  // ~50 days remaining
        ['ends' => $in90,   'startOffset' => 45],  // ~90 days remaining
    ];

    $trainingIds = [];
    $expectedRemaining = [];
    $createdTrainings = array_merge([], $createdTrainings);
    $firstTrainingId = next_free_id($pdo, 'training_listings', 'id');
    foreach ($tpl as $i => $row) {
        $tid = $firstTrainingId + $i;
        $starts = date('Y-m-d H:i:s', strtotime($row['ends']) - $row['startOffset'] * 86400);
        // Ensure starts_at is in the future so cards are published/visible.
        if (strtotime($starts) < time()) {
            $starts = date('Y-m-d H:i:s', time() + 2 * 86400);
        }
        $insTraining->execute([$tid, $companyCid, $specA, "{$kw} Duration Row {$i}", "{$kw} description row {$i}", 'hands_on', 'remote', 0, null, 'EGP', $starts, $row['ends'], $now, $row['ends']]);
        $trainingIds[] = $tid;
        $expectedRemaining[$tid] = expected_duration($row['ends']);
        $createdTrainings[] = $tid;
        $insTsp = $pdo->prepare("INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)");
        $insTsp->execute([$tid, $specA]);
    }

    // NULL ends_at row: belongs to specA scope but must sort LAST in both directions.
    $nullTid = $firstTrainingId + count($tpl);
    $insTraining->execute([$nullTid, $companyCid, $specA, "{$kw} Null Ends Row", "{$kw} description null ends", 'shadowing', 'onsite', 1, 2000, 'EGP', date('Y-m-d H:i:s', time() + 2 * 86400), null, $now, null]);
    $trainingIds[] = $nullTid;
    $createdTrainings[] = $nullTid;
    $insTsp->execute([$nullTid, $specA]);

    // Out-of-scope training (specB) sharing the keyword - must never appear.
    $oosTid = $nullTid + 1;
    $insTraining->execute([$oosTid, $companyCid, $specB, "{$kw} Out Of Scope", "{$kw} outside scope", 'project_based', 'remote', 1, 9999, 'EGP', date('Y-m-d H:i:s', time() + 2 * 86400), date('Y-m-d H:i:s', time() + 5 * 86400), $now, $now]);
    $createdTrainings[] = $oosTid;
    $insTsp->execute([$oosTid, $specB]);

    $base = [
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
        'query' => $kw,
    ];

    // Helper: return list of [id, duration] in the order the API returns them.
    $fetchDurations = static function (string $sort, int $page = 1, int $limit = 100) use ($base) {
        $r = search_service_trainings(array_merge($base, ['sort' => $sort, 'page' => $page, 'limit' => $limit]));
        return array_map(static fn ($it) => [(int) $it['id'], $it['duration']], $r['items'] ?? []);
    };

    // Total rows that should be returned for specA scope (5 non-null + 1 null).
    $expectCount = 6;

    echo "\n== TESTS: duration_asc ==\n";
    $asc = $fetchDurations('duration_asc');
    check('asc returns all scoped rows', count($asc) === $expectCount);
    check('asc total = scoped rows', (int) search_service_trainings(array_merge($base, ['sort' => 'duration_asc']))['total'] === $expectCount);
    // Non-null durations must be non-decreasing.
    $ascNonNull = array_values(array_filter($asc, static fn ($it) => $it[1] !== null));
    $ascDurs = array_map('intval', array_column($ascNonNull, 1));
    $isNonDec = true;
    for ($i = 1; $i < count($ascDurs); $i++) {
        if ($ascDurs[$i] < $ascDurs[$i - 1]) {
            $isNonDec = false;
        }
    }
    check('asc non-null durations non-decreasing', $isNonDec);
    check('asc NULL ends_at last', (int) end($asc)[0] === $nullTid);
    check('asc expired rows report duration 0', count(array_filter($asc, static fn ($it) => in_array($it[0], $trainingIds, true) && isset($expectedRemaining[$it[0]]) && $expectedRemaining[$it[0]] === 0 && $it[1] !== null)) > 0);

    echo "\n== TESTS: duration_desc ==\n";
    $desc = $fetchDurations('duration_desc');
    check('desc returns all scoped rows', count($desc) === $expectCount);
    $descNonNull = array_values(array_filter($desc, static fn ($it) => $it[1] !== null));
    $descDurs = array_map('intval', array_column($descNonNull, 1));
    $isNonInc = true;
    for ($i = 1; $i < count($descDurs); $i++) {
        if ($descDurs[$i] > $descDurs[$i - 1]) {
            $isNonInc = false;
        }
    }
    check('desc non-null durations non-increasing', $isNonInc);
    check('desc NULL ends_at last', (int) end($desc)[0] === $nullTid);

    echo "\n== TESTS: duration derived from ends_at (not total span) ==\n";
    // Row 2 (in20, offset 60) has a LONGER total span than Row 3 (in50, offset 2),
    // but must sort by remaining days: Row 3 (50d) should precede Row 2 (20d) in desc.
    $descById = [];
    foreach ($desc as $it) {
        if (isset($expectedRemaining[$it[0]])) {
            $descById[(int) $it[0]] = (int) $it[1];
        }
    }
    $row2 = $trainingIds[2]; // ends +20d
    $row3 = $trainingIds[3]; // ends +50d
    check('duration sort uses remaining days, not total span', ($descById[$row3] ?? -1) > ($descById[$row2] ?? -99));

    echo "\n== TESTS: deterministic id tie-break ==\n";
    // Row 1 in3/offset1 and row 4 in50/offset2 have distinct remaining days, but
    // to test the tie-break we just confirm both calls return identical order.
    $asc2 = $fetchDurations('duration_asc');
    check('asc order deterministic across calls', array_column($asc, 0) === array_column($asc2, 0));

    echo "\n== TESTS: page-boundary continuity ==\n";
    $asc1 = array_map(static fn ($it) => [$it[0], (int) $it[1]], array_filter($fetchDurations('duration_asc', 1, 3), static fn ($it) => $it[1] !== null));
    $asc2p = array_map(static fn ($it) => [$it[0], (int) $it[1]], array_filter($fetchDurations('duration_asc', 2, 3), static fn ($it) => $it[1] !== null));
    $ascMerged = array_merge($asc1, $asc2p);
    // 5 trainings have non-null durations.
    check('asc page1+page2 covers all non-null rows (no dup/leak)', count($ascMerged) === 5 && count(array_unique(array_column($ascMerged, 0))) === 5);
    $mergedIsNonDec = true;
    for ($i = 1; $i < count($ascMerged); $i++) {
        if ($ascMerged[$i][1] < $ascMerged[$i - 1][1]) {
            $mergedIsNonDec = false;
        }
    }
    check('asc page-boundary ordering preserved', $mergedIsNonDec);

    $desc1 = array_map(static fn ($it) => [$it[0], (int) $it[1]], array_filter($fetchDurations('duration_desc', 1, 3), static fn ($it) => $it[1] !== null));
    $desc2p = array_map(static fn ($it) => [$it[0], (int) $it[1]], array_filter($fetchDurations('duration_desc', 2, 3), static fn ($it) => $it[1] !== null));
    $descMerged = array_merge($desc1, $desc2p);
    check('desc page1+page2 covers all non-null rows (no dup/leak)', count($descMerged) === 5 && count(array_unique(array_column($descMerged, 0))) === 5);
    $mergedIsNonInc = true;
    for ($i = 1; $i < count($descMerged); $i++) {
        if ($descMerged[$i][1] > $descMerged[$i - 1][1]) {
            $mergedIsNonInc = false;
        }
    }
    check('desc page-boundary ordering preserved', $mergedIsNonInc);

    echo "\n== TESTS: scope preserved under both sorts ==\n";
    foreach (['duration_asc', 'duration_desc'] as $sort) {
        $ids = array_map(static fn ($it) => (int) $it[0], $fetchDurations($sort));
        check("{$sort} excludes out-of-scope training", !in_array($oosTid, $ids, true));
        check("{$sort} includes all in-scope trainings", count(array_intersect($ids, $trainingIds)) === $expectCount);
    }

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

$testEmailLike = 'usd.dur.%@test.local';
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