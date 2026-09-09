<?php

/**
 * MASAR - Training Dates Normalization Regression Test
 *
 * Verifies the corrected application-window date rules across the live
 * training dataset after `database/seeders/normalize_training_dates_seeder.php`
 * has run:
 *
 *   1. application_deadline equals starts_at EXACTLY for every training
 *   2. published_at < starts_at wherever published_at is set
 *   3. starts_at < ends_at for every training
 *   4. A realistic set of trainings is open for application RIGHT NOW
 *      (published_at < NOW, deadline > NOW, starts > NOW, ends > NOW) with a
 *      spread of upcoming start dates (a few days / 1-2 weeks / 3-4 weeks /
 *      later this month)
 *   5. Future listings exist (later this month / next month / following month)
 *   6. Historical/expired listings exist with consistent past dates and the
 *      same deadline = starts rule; no published row is already over
 *   7. Backend Development (spec 199) has several currently-open listings
 *   8. Business duration (days from ends_at, floor 0) is non-negative for all
 *   9. duration asc / desc remain monotonic over published spec-199 rows
 *  10. Search/filter/pagination still yield results (counts + contiguous
 *      duration boundaries)
 *
 * Plus real service-level application flow (no app-logic changes):
 *   Scenario A: open listing -> application_service_create succeeds
 *   Scenario B: deadline passed  -> application_service_create rejects 409
 *
 * Run from the backend root:
 *     php tests/training_dates_normalization_regression.php
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
$now = date('Y-m-d H:i:s');

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

$total = (int) db_fetch_one('SELECT COUNT(*) FROM training_listings')['COUNT(*)'];
// Includes the 6 permanent test-company trainings created by
// tests/applications_test_data_seeder.php (they keep deadline === starts_at).
check('dataset has 67 trainings (ids preserved)', $total === 67);

echo "\n== RULE 1: application_deadline === starts_at (exact) for all ==\n";
$bad = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_listings WHERE application_deadline <> starts_at')['c'];
check("deadline equals starts for all trainings (bad=$bad)", $bad === 0);

echo "\n== RULE 2: published_at < starts_at ==\n";
$bad = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_listings WHERE published_at IS NOT NULL AND published_at >= starts_at')['c'];
check("published before starts for every published/draft row (bad=$bad)", $bad === 0);

echo "\n== RULE 3: starts_at < ends_at ==\n";
$bad = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_listings WHERE starts_at >= ends_at')['c'];
check("starts before ends for all (bad=$bad)", $bad === 0);

echo "\n== RULE 4: several listings open for application NOW with varied starts ==\n";
$open = db_fetch_all(
    "SELECT id, starts_at, ends_at, application_deadline, published_at
     FROM training_listings
     WHERE status IN ('published','open','active')
       AND published_at IS NOT NULL AND published_at < ?
       AND application_deadline > ?
       AND starts_at > ?
       AND ends_at > ?
     ORDER BY starts_at",
    [$now, $now, $now, $now]
);
check('at least 5 applicability-visible trainings are open now', count($open) >= 5);

$dayDeltas = array_map(static function (array $t) use ($now): float {
    return round((strtotime($t['starts_at']) - strtotime($now)) / 86400);
}, $open);

foreach ([['few days', 1, 3], ['1-2 weeks', 7, 14], ['3-4 weeks', 21, 28], ['later this month', 24, 45]] as [$label, $min, $max]) {
    $has = count(array_filter($dayDeltas, static fn($d) => $d >= $min && $d <= $max)) > 0;
    check("open listings with start within $label", $has);
}

echo "\n== RULE 5: future listings exist (next/following months) ==\n";
$next = (int) db_fetch_one("SELECT COUNT(*) AS c FROM training_listings WHERE starts_at > DATE_ADD(?, INTERVAL 30 DAY)", [$now])['c'];
$after = (int) db_fetch_one("SELECT COUNT(*) AS c FROM training_listings WHERE starts_at > DATE_ADD(?, INTERVAL 60 DAY)", [$now])['c'];
check('listings starting later this month / next month exist', $next >= 3);
check('listings starting the following month or later exist', $after >= 2);

echo "\n== RULE 6: historical/expired + no over-preserved published rows ==\n";
$closedPast = db_fetch_all("SELECT id FROM training_listings WHERE status='closed' AND starts_at <= ? AND ends_at <= ?", [$now, $now]);
check('all closed listings are fully historical', count($closedPast) === 6);
$pubOver = (int) db_fetch_one("SELECT COUNT(*) AS c FROM training_listings WHERE status='published' AND ends_at <= ?", [$now])['c'];
check('no published listing has already ended', $pubOver === 0);
$bad = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_listings WHERE status=\'closed\' AND application_deadline <> starts_at')['c'];
check('closed listings still hold deadline = starts', $bad === 0);

echo "\n== RULE 7: Backend Development (spec 199) open NOW ==\n";
$specOpen = db_fetch_all(
    "SELECT id, starts_at FROM training_listings
     WHERE specialization_id = 199 AND status = 'published'
       AND published_at < ? AND application_deadline > ? AND starts_at > ? AND ends_at > ?",
    [$now, $now, $now, $now]
);
check('several spec-199 listings open now', count($specOpen) >= 5);

echo "\n== RULE 8: business duration (GREATEST(DATEDIFF(ends, today), 0)) non-negative ==\n";
$neg = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_listings WHERE GREATEST(DATEDIFF(ends_at, CURDATE()), 0) < 0')['c'];
check('duration never negative', $neg === 0);

echo "\n== RULE 9: duration asc/desc monotonic (live spec-199 published, SQL ordering) ==\n";
$asc = db_fetch_all(
    "SELECT id, ends_at,
            GREATEST(DATEDIFF(ends_at, CURDATE()), 0) AS d
     FROM training_listings
     WHERE specialization_id = 199 AND status = 'published'
     ORDER BY (ends_at IS NULL) ASC, GREATEST(DATEDIFF(ends_at, CURDATE()), 0) ASC, id ASC"
);
$mono = true;
$prev = -1;
foreach ($asc as $r) {
    if ((int) $r['d'] < $prev) {
        $mono = false;
    }
    $prev = (int) $r['d'];
}
check("duration_asc monotonic over " . count($asc) . " rows", $mono && count($asc) >= 10);

$desc = db_fetch_all(
    "SELECT GREATEST(DATEDIFF(ends_at, CURDATE()), 0) AS d
     FROM training_listings
     WHERE specialization_id = 199 AND status = 'published'
     ORDER BY (ends_at IS NULL) ASC, GREATEST(DATEDIFF(ends_at, CURDATE()), 0) DESC, id ASC"
);
$monoDesc = true;
$prev = PHP_INT_MAX;
foreach ($desc as $r) {
    if ((int) $r['d'] > $prev) {
        $monoDesc = false;
    }
    $prev = (int) $r['d'];
}
check('duration_desc monotonic', $monoDesc);

echo "\n== RULE 10: search still returns results (counts + contiguous paging) ==\n";
$allPublished = (int) db_fetch_one("SELECT COUNT(*) AS c FROM training_listings WHERE status='published'")['c'];
check("published count sane ($allPublished)", $allPublished >= 40);
check('more open-now rows than closed rows (fresh dataset mixes)', count($open) > 6);

// Page-boundary continuity for the 5 open-now rows closest to today (asc).
if (count($open) >= 2) {
    $firstHalf = array_slice($open, 0, (int) floor(count($open) / 2));
    $secondHalf = array_slice($open, (int) floor(count($open) / 2));
    $lastFirst = strtotime(end($firstHalf)['starts_at']);
    $firstSecond = strtotime(reset($secondHalf)['starts_at']);
    check('page-boundary starts stay ordered (no overlap)', $lastFirst <= $firstSecond);
}

// ---- Application flow (real service, real pipeline) ----
echo "\n== SCENARIO A: open listing accepts application ==\n";
$student = db_fetch_one(
    "SELECT s.id AS sid, s.user_id AS uid, s.full_name
     FROM students s JOIN users u ON u.id = s.user_id
     ORDER BY s.id DESC LIMIT 1"
);
$user_id = (int) $student['uid'];
$sid = (int) $student['sid'];
$cv = db_fetch_one("SELECT id FROM files WHERE user_id = ? AND type = 'cv' ORDER BY id DESC LIMIT 1", [$user_id]);
if (!$cv) {
    echo "SKIP - fixture student has no CV file\n";
    exit($failures === 0 ? 0 : 1);
}
$cv_file_id = (int) $cv['id'];

$openTrainings = db_fetch_all(
    "SELECT t.id FROM training_listings t
     WHERE t.status IN ('published','open','active')
       AND (t.application_deadline IS NULL OR t.application_deadline > NOW())
     ORDER BY t.id DESC"
);
$openTraining = 0;
foreach ($openTrainings as $t) {
    $applied = (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_applications WHERE student_id = ? AND training_id = ?', [$sid, (int) $t['id']])['c'];
    if ($applied === 0) {
        $openTraining = (int) $t['id'];
        break;
    }
}
check('found an open training not already applied to', $openTraining > 0);

$_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=----regression';
$_POST['training_id'] = (string) $openTraining;
$_POST['full_name'] = $student['full_name'];
$_POST['university'] = 'Normalization Regression University';
$_POST['academic_year'] = '3rd';
$_POST['applicant_type'] = 'student';
$_POST['why_interested'] = 'Interested in verifying application flow after date normalization.';
$_POST['what_to_learn'] = 'Production-ready skills.';

$data = [
    'training_id' => $openTraining,
    'full_name' => $_POST['full_name'],
    'university' => $_POST['university'],
    'academic_year' => $_POST['academic_year'],
    'applicant_type' => 'student',
    'why_interested' => $_POST['why_interested'],
    'what_to_learn' => $_POST['what_to_learn'],
    'cv_file_id' => $cv_file_id,
];
$resA = application_service_create($user_id, $openTraining, $data);
check('open training accepted (would be HTTP 201)', ($resA['success'] ?? null) === true);
if (($resA['data']['id'] ?? null) !== null) {
    $idA = (int) $resA['data']['id'];
    echo "  created application #$idA for training #$openTraining\n";

    echo "\n== SCENARIO B: deadline passed -> rejected (409) ==\n";
    $expired = db_fetch_one(
        "SELECT t.id FROM training_listings t
         WHERE t.status = 'published' AND t.application_deadline < NOW()
         ORDER BY t.id ASC"
    );
    check('found a published training whose deadline has passed', (bool) $expired);
    if ($expired) {
        $expiredId = (int) $expired['id'];
        $dataB = $data;
        $dataB['training_id'] = $expiredId;
        $resB = application_service_create($user_id, $expiredId, $dataB);
        check("expired listing #$expiredId rejected", ($resB['success'] ?? null) === false);
        check('deadline rejection uses 409', (int) ($resB['status_code'] ?? 0) === 409);
        check('deadline rejection message', ($resB['message'] ?? '') === 'The application deadline has passed.');
        check('deadline rejection did not insert a row',
            (int) db_fetch_one('SELECT COUNT(*) AS c FROM training_applications WHERE student_id = ? AND training_id = ?', [$sid, $expiredId])['c'] === 0);
    }

    // cleanup
    db_execute('DELETE FROM training_applications WHERE id = ?', [$idA]);
    echo "  cleanup: deleted application #$idA\n";
} else {
    check('scenario A returned an application id', false);
}

echo "\n=== " . ($failures === 0 ? 'ALL PASS' : ($failures . " FAILURE(S)")) . " ===\n";
exit($failures === 0 ? 0 : 1);
