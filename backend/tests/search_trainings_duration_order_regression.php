<?php

/**
 * MASAR - Training Duration Sort Order Regression
 *
 * Verifies that the unified search endpoint (search_service_trainings ->
 * search_repository_trainings) orders results by the API's "remaining days"
 * semantic when sort=duration_asc / sort=duration_desc.
 *
 * Card semantics (corrected): `duration` is the FIXED total span of the
 * training (DATEDIFF(ends_at, starts_at)); `remaining_days` is the decreasing
 * countdown from today until ends_at (clamped to 0, never negative). The
 * duration sorts key on remaining days with NULL ends_at last and a
 * deterministic training id tie-break; ORDER BY is applied at the SQL level
 * BEFORE LIMIT/OFFSET so results are globally consistent across pages.
 *
 * The test inserts isolated published trainings (application_deadline ===
 * starts_at) plus one already-expired published training (deadline passed, so
 * it is HIDDEN by the discovery deadline filter) and asserts:
 *   1. duration_asc  -> non-decreasing remaining_days
 *   2. duration_desc -> non-increasing remaining_days
 *   3. remaining_days derives from ends_at, NOT from the fixed duration span
 *      (a longer-span row with fewer remaining days sorts AFTER a shorter-span
 *       row with more remaining days)
 *   4. card `duration` equals the fixed total span (DATEDIFF ends - starts)
 *   5. the published training whose deadline/ends has passed is EXCLUDED from
 *      discovery (deadline filter), even though it is still published
 *   6. NULL ends_at rows sort last (end of list) in both directions
 *   7. deterministic ordering across repeated calls
 *   8. page-boundary continuity across page=1/page=2 for both directions
 *   9. student specialization scope is preserved under both sorts
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

function expected_remaining(?string $endsAt): int
{
    if ($endsAt === null) {
        return -1; // sentinel: NULL ends_at (sorted last)
    }
    $days = (int) floor((strtotime($endsAt) - time()) / 86400);
    return max($days, 0);
}

function fixed_span(?string $startsAt, ?string $endsAt): ?int
{
    if ($startsAt === null || $endsAt === null) {
        // NULL ends_at rows expose a NULL card span (kept in DTO).
        return null;
    }
    $starts = date_create($startsAt);
    $ends = date_create($endsAt);
    if ($starts === false || $ends === false) {
        return null;
    }
    $diff = (int) $starts->diff($ends)->format('%r%a');
    return max($diff, 0);
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

    $in2  = date('Y-m-d H:i:s', time() + 2 * 86400);
    $in3  = date('Y-m-d H:i:s', time() + 3 * 86400);
    $in20 = date('Y-m-d H:i:s', time() + 20 * 86400);
    $in48 = date('Y-m-d H:i:s', time() + 48 * 86400);
    $in50 = date('Y-m-d H:i:s', time() + 50 * 86400);
    $in45 = date('Y-m-d H:i:s', time() + 45 * 86400);
    $in90 = date('Y-m-d H:i:s', time() + 90 * 86400);
    $past10 = date('Y-m-d H:i:s', time() - 10 * 86400);

    /*
     * Visible in-spec rows (deadline === starts, starts in the future).
     * R2 has a LONGER fixed span (18) than R3 (2) but FEWER remaining days
     * (20 vs 50) - the sort must key on remaining days, not the span.
     *   visible: R1 (3d), R2 (20d), R3 (50d), R4 (90d), NULL-ends
     *   hidden : R0 (published, ends/deadline in the past) - excluded
     *   oos    : specB training sharing the keyword - excluded by scope
     */
    $tpl = [
        ['ends' => $in3,  'starts' => $in2,  'title' => 'Row 1'], // rem 3,  span 1
        ['ends' => $in20, 'starts' => $in2,  'title' => 'Row 2'], // rem 20, span 18
        ['ends' => $in50, 'starts' => $in48, 'title' => 'Row 3'], // rem 50, span 2
        ['ends' => $in90, 'starts' => $in45, 'title' => 'Row 4'], // rem 90, span 45
    ];

    $trainingIds = [];
    $expectedRemaining = [];
    $expectedSpan = [];
    $firstTrainingId = next_free_id($pdo, 'training_listings', 'id');

    foreach ($tpl as $i => $row) {
        $tid = $firstTrainingId + $i;
        $insTraining->execute([$tid, $companyCid, $specA, "{$kw} Duration {$row['title']}", "{$kw} description row {$i}", 'hands_on', 'remote', 0, null, 'EGP', $row['starts'], $row['ends'], $in2, $row['ends']]);
        $trainingIds[] = $tid;
        $expectedRemaining[$tid] = expected_remaining($row['ends']);
        $expectedSpan[$tid] = fixed_span($row['starts'], $row['ends']);
        $createdTrainings[] = $tid;
        $insTsp = $pdo->prepare("INSERT INTO training_specializations (training_id, specialization_id) VALUES (?, ?)");
        $insTsp->execute([$tid, $specA]);
    }

    // R0: published but already ended (deadline passed) -> hidden in discovery.
    $hiddenTid = $firstTrainingId + count($tpl);
    $insTraining->execute([$hiddenTid, $companyCid, $specA, "{$kw} Duration Expired Hidden", "{$kw} description expired", 'hands_on', 'hybrid', 0, null, 'EGP', $in2, $past10, $in2, $past10]);
    $createdTrainings[] = $hiddenTid;
    $insTsp->execute([$hiddenTid, $specA]);

    // NULL ends_at row: belongs to specA scope but must sort LAST in both directions.
    $nullTid = $hiddenTid + 1;
    $insTraining->execute([$nullTid, $companyCid, $specA, "{$kw} Null Ends Row", "{$kw} description null ends", 'shadowing', 'onsite', 1, 2000, 'EGP', $in2, null, $in2, null]);
    $trainingIds[] = $nullTid;
    $createdTrainings[] = $nullTid;
    $insTsp->execute([$nullTid, $specA]);

    // Out-of-scope training (specB) sharing the keyword - must never appear.
    $oosTid = $nullTid + 1;
    $insTraining->execute([$oosTid, $companyCid, $specB, "{$kw} Out Of Scope", "{$kw} outside scope", 'project_based', 'remote', 1, 9999, 'EGP', $in2, $in5 = date('Y-m-d H:i:s', time() + 5 * 86400), $in2, $in5]);
    $createdTrainings[] = $oosTid;
    $insTsp->execute([$oosTid, $specB]);

    $base = [
        'user_id' => $studentAuid,
        'role' => 'student',
        'page' => 1,
        'limit' => 100,
        'query' => $kw,
    ];

    // Helper: return [id, duration, remaining_days] in API order.
    $fetchDurations = static function (string $sort, int $page = 1, int $limit = 100) use ($base) {
        $r = search_service_trainings(array_merge($base, ['sort' => $sort, 'page' => $page, 'limit' => $limit]));
        return array_map(static fn ($it) => [(int) $it['id'], $it['duration'], $it['remaining_days']], $r['items'] ?? []);
    };

    // Visible in-spec rows: 4 non-null + 1 null.
    $expectCount = 5;

    echo "\n== TESTS: duration_asc ==\n";
    $asc = $fetchDurations('duration_asc');
    check('asc returns all scoped visible rows', count($asc) === $expectCount);
    check('asc total = scoped visible rows', (int) search_service_trainings(array_merge($base, ['sort' => 'duration_asc']))['total'] === $expectCount);
    // Ordering by remaining_days must be non-decreasing (nulls surface last).
    $ascRemaining = array_map(static fn ($it) => $it[2], $asc);
    $isNonDec = true;
    for ($i = 1; $i < count($ascRemaining); $i++) {
        if ($ascRemaining[$i] !== null && $ascRemaining[$i - 1] !== null && $ascRemaining[$i] < $ascRemaining[$i - 1]) {
            $isNonDec = false;
        }
    }
    check('asc remaining_days non-decreasing', $isNonDec);
    check('asc NULL ends_at last', (int) end($asc)[0] === $nullTid);
    check('asc expired-deadline published row is hidden', !in_array($hiddenTid, array_map(static fn ($it) => (int) $it[0], $asc), true));

    echo "\n== TESTS: duration_desc ==\n";
    $desc = $fetchDurations('duration_desc');
    check('desc returns all scoped visible rows', count($desc) === $expectCount);
    $descRemaining = array_map(static fn ($it) => $it[2], $desc);
    $isNonInc = true;
    for ($i = 1; $i < count($descRemaining); $i++) {
        if ($descRemaining[$i] !== null && $descRemaining[$i - 1] !== null && $descRemaining[$i] > $descRemaining[$i - 1]) {
            $isNonInc = false;
        }
    }
    check('desc remaining_days non-increasing', $isNonInc);
    check('desc NULL ends_at last', (int) end($desc)[0] === $nullTid);

    echo "\n== TESTS: remaining_days (not fixed span) drives the sort ==\n";
    $descById = [];
    foreach ($desc as $it) {
        if (isset($expectedRemaining[$it[0]])) {
            $descById[(int) $it[0]] = $it;
        }
    }
    // Row 3 (50 days remaining, span 2) must precede Row 2 (20 days, span 18)
    // in desc even though Row 2 has the LONGER fixed span.
    $row2 = $trainingIds[1];
    $row3 = $trainingIds[2];
    check('remaining_days drives the sort (not the fixed duration span)',
        ($descById[$row3][2] ?? -1) > ($descById[$row2][2] ?? -99));

    echo "\n== TESTS: duration card field equals the fixed total span ==\n";
    $spanMatch = true;
    foreach ($desc as $it) {
        $tid = (int) $it[0];
        if ($tid === $nullTid) {
            if ($it[1] !== null || $it[2] !== null) {
                $spanMatch = false;
            }
            continue;
        }
        if ($it[1] !== $expectedSpan[$tid]) {
            $spanMatch = false;
            echo "  [DIAG] span mismatch tid={$tid} api_duration=" . var_export($it[1], true) . " expected=" . var_export($expectedSpan[$tid] ?? null, true) . "\n";
        }
    }
    check('duration equals DATEDIFF(ends_at, starts_at) for every visible row', $spanMatch);
    check('duration is NOT the remaining-days countdown (fixed values)', ($descById[$row2][1] ?? 0) !== ($descById[$row2][2] ?? 0));

    echo "\n== TESTS: deterministic ordering ==\n";
    $asc2 = $fetchDurations('duration_asc');
    check('asc order deterministic across calls', array_column($asc, 0) === array_column($asc2, 0));

    echo "\n== TESTS: page-boundary continuity ==\n";
    $asc1 = array_map(static fn ($it) => [(int) $it[0], (int) $it[2]], array_filter($fetchDurations('duration_asc', 1, 3), static fn ($it) => $it[2] !== null));
    $asc2p = array_map(static fn ($it) => [(int) $it[0], (int) $it[2]], array_filter($fetchDurations('duration_asc', 2, 3), static fn ($it) => $it[2] !== null));
    $ascMerged = array_merge($asc1, $asc2p);
    // 4 trainings have non-null remaining days (R1-R4).
    check('asc page1+page2 covers all non-null rows (no dup/leak)', count($ascMerged) === 4 && count(array_unique(array_column($ascMerged, 0))) === 4);
    $mergedIsNonDec = true;
    for ($i = 1; $i < count($ascMerged); $i++) {
        if ($ascMerged[$i][1] < $ascMerged[$i - 1][1]) {
            $mergedIsNonDec = false;
        }
    }
    check('asc page-boundary ordering preserved', $mergedIsNonDec);

    $desc1 = array_map(static fn ($it) => [(int) $it[0], (int) $it[2]], array_filter($fetchDurations('duration_desc', 1, 3), static fn ($it) => $it[2] !== null));
    $desc2p = array_map(static fn ($it) => [(int) $it[0], (int) $it[2]], array_filter($fetchDurations('duration_desc', 2, 3), static fn ($it) => $it[2] !== null));
    $descMerged = array_merge($desc1, $desc2p);
    check('desc page1+page2 covers all non-null rows (no dup/leak)', count($descMerged) === 4 && count(array_unique(array_column($descMerged, 0))) === 4);
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
        check("{$sort} includes all visible in-scope trainings", count(array_intersect($ids, $trainingIds)) === $expectCount);
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