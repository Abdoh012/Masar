<?php

/**
 * MASAR - Backend Development Seed Data Verification
 *
 * Verifies the seeded Backend Development (specialization_id = 199) training
 * listings against the live unified GET /api/v1/search/trainings endpoint
 * backed by search_controller_trainings -> search_service_trainings:
 *
 *   1. No filters                         -> scoped to student specialization
 *   2. Keyword search (Laravel/PHP/Redis/Docker/API/MySQL) -> results present
 *   3. Filter only (training_type, mode, paid)
 *   4. Combined search + filters
 *   5. Sorts (newest, oldest, price_asc, price_desc)
 *   6. Pagination (page=1&limit=5 vs page=2&limit=5)
 *   7. Card structure (is_saved, skills, specialization, duration)
 *
 * It authenticates as the seeded backend student (user 100588 /
 * mammuslim2003@gmail.com, students.id = 1498, specialization_id = 199) so the
 * results are the student-scoped, is_saved-aware responses the app returns.
 *
 * Run from the backend root:
 *     php tests/search_trainings_backend_data_verify.php
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

$pdo = get_database_connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// The seeded backend student used by MASAR.json (mammuslim2003@gmail.com).
$USER_ID = 100588;
$ROLE = 'student';

function backend_search(array $filters): array
{
    global $USER_ID, $ROLE;
    $filters['user_id'] = $USER_ID;
    $filters['role'] = $ROLE;
    return search_service_trainings($filters);
}

try {
    // Baseline: the seeder must have added >= 20 rows for specialization 199.
    $count = (int) $pdo->query('SELECT COUNT(*) FROM training_listings WHERE specialization_id = 199 AND id >= 100246')->fetchColumn();
    check('seeded >= 20 new specialization 199 listings', $count >= 20);

    $newIds = array_column($pdo->query('SELECT id FROM training_listings WHERE specialization_id = 199 AND id >= 100246')->fetchAll(), 'id');
    check('seeded rows resolve to Backend Development',
        (int) $pdo->query('SELECT COUNT(*) FROM training_listings t JOIN specializations s ON s.id = t.specialization_id WHERE t.id >= 100246 AND s.id = 199 AND s.name = \'Backend Development\'')->fetchColumn() === count($newIds));

    echo "\n--- 1. No filters (scoped listing) ---\n";
    $result = backend_search([]);
    $items = $result['items'];
    check('no-filter on page 1 returns items', count($items) > 0);
    $page1Ids = array_column($items, 'id');
    check('no-filter contains seeded Backend Development listings', count(array_intersect($newIds, $page1Ids)) > 0);
    check('no-filter total >= 20', $result['total'] >= 20);
    check('no-filter items flagged is_saved field', isset($items[0]['is_saved']));

    // Every returned card must be specialization 199 "Backend Development".
    $allSpecOk = true;
    foreach ($items as $it) {
        if (($it['specialization']['id'] ?? null) !== 199 || ($it['specialization']['name'] ?? '') !== 'Backend Development') {
            $allSpecOk = false;
        }
    }
    check('every no-filter card belongs to specialization 199 Backend Development', $allSpecOk);

    echo "\n--- 2. Keyword search ---\n";
    foreach (['Laravel', 'PHP', 'Redis', 'Docker', 'API', 'MySQL'] as $q) {
        $r = backend_search(['query' => $q]);
        $ids = array_column($r['items'], 'id');
        $hasNew = (bool) array_intersect($newIds, $ids);
        check("q={$q} returns seeded data (found " . count($ids) . ', ' . count(array_intersect($newIds, $ids)) . ' new)', $r['total'] > 0 && $hasNew);
    }

    echo "\n--- 3. Filter only ---\n";
    $r = backend_search(['training_type' => 'hands_on']);
    check('training_type=hands_on returns items', $r['total'] > 0);
    check('training_type=hands_on filtered correctly', count(array_filter($r['items'], fn ($it) => $it['training_type'] !== 'hands_on')) === 0);

    $r = backend_search(['mode' => 'remote']);
    check('mode=remote returns items', $r['total'] > 0);
    check('mode=remote filtered correctly', count(array_filter($r['items'], fn ($it) => $it['mode'] !== 'remote')) === 0);

    $r = backend_search(['paid' => '1']);
    check('paid=1 returns items', $r['total'] > 0);
    check('paid=1 filtered correctly', count(array_filter($r['items'], fn ($it) => $it['is_paid'] != 1)) === 0);

    $r = backend_search(['paid' => '0']);
    check('paid=0 returns items', $r['total'] > 0);
    check('paid=0 filtered correctly', count(array_filter($r['items'], fn ($it) => $it['is_paid'] != 0)) === 0);

    echo "\n--- 4. Combined filters ---\n";
    $r = backend_search(['mode' => 'remote', 'paid' => '1', 'sort' => 'newest']);
    check('mode=remote&paid=1&sort=newest returns items', $r['total'] > 0);
    check('combined filters correct', count(array_filter($r['items'], fn ($it) => $it['mode'] !== 'remote' || $it['is_paid'] != 1)) === 0);

    $r = backend_search(['training_type' => 'project_based', 'mode' => 'onsite']);
    check('training_type=project_based&mode=onsite returns items', $r['total'] > 0);
    check('combined type+mode correct', count(array_filter($r['items'], fn ($it) => $it['training_type'] !== 'project_based' || $it['mode'] !== 'onsite')) === 0);

    $r = backend_search(['query' => 'Laravel', 'mode' => 'remote']);
    check('q=Laravel&mode=remote returns items', $r['total'] > 0);
    check('search+filter correct', count(array_filter($r['items'], fn ($it) => $it['mode'] !== 'remote')) === 0);

    echo "\n--- 5. Sorts ---\n";
    $newest = backend_search(['sort' => 'newest']);
    $idsNewest = array_slice(array_column($newest['items'], 'id'), 0, 5);
    $oldest = backend_search(['sort' => 'oldest']);
    $idsOldest = array_slice(array_column($oldest['items'], 'id'), 0, 5);
    check('sort=newest vs sort=oldest produce different orders', $idsNewest !== $idsOldest);

    $asc = backend_search(['sort' => 'price_asc', 'limit' => 20]);
    $desc = backend_search(['sort' => 'price_desc', 'limit' => 20]);
    $pricesAsc = array_map(fn ($it) => (float) ($it['compensation_amount'] ?? 0), $asc['items']);
    $pricesDesc = array_map(fn ($it) => (float) ($it['compensation_amount'] ?? 0), $desc['items']);
    $priceAscSorted = true;
    for ($i = 1; $i < count($pricesAsc); $i++) {
        if ($pricesAsc[$i] < $pricesAsc[$i - 1]) $priceAscSorted = false;
    }
    $priceDescSorted = true;
    for ($i = 1; $i < count($pricesDesc); $i++) {
        if ($pricesDesc[$i] > $pricesDesc[$i - 1]) $priceDescSorted = false;
    }
    check('price_asc sorted correctly', $priceAscSorted);
    check('price_desc sorted correctly', $priceDescSorted);

    echo "\n--- 6. Pagination ---\n";
    $r1 = backend_search(['page' => '1', 'limit' => '5']);
    $r2 = backend_search(['page' => '2', 'limit' => '5']);
    $ids1 = array_column($r1['items'], 'id');
    $ids2 = array_column($r2['items'], 'id');
    check('page=1&limit=5 returns 5 items', count($ids1) === 5);
    check('page=1 and page=2 have no overlap', count(array_intersect($ids1, $ids2)) === 0);
    check('page=2 returns items when total > 5', count($ids2) > 0);
    check('total consistent across pages', $r1['total'] === $r2['total']);

    echo "\n--- 7. Card structure & duration ---\n";
    $r = backend_search(['limit' => '5']);
    foreach ($r['items'] as $it) {
        check("card {$it['id']} has required shape",
            isset($it['id'], $it['title'], $it['description'], $it['training_type'], $it['mode'], $it['is_paid'], $it['capacity'], $it['is_saved'], $it['skills'], $it['specialization'], $it['duration'], $it['company_name']));
        check("card {$it['id']} skills is array", is_array($it['skills']));
        if ($it['specialization']['id'] === 199) {
            check("card {$it['id']} duration present and non-negative", is_int($it['duration']) && $it['duration'] >= 0);
        }
    }

    echo "\n--- 8. is_saved per student ---\n";
    $r = backend_search(['query' => 'Laravel']);
    $savedFlags = array_column($r['items'], 'is_saved');
    check('is_saved values are 0 or 1', count(array_filter($savedFlags, fn ($v) => !in_array($v, [0, 1], true))) === 0);

    // The chosen backend student has saved listing 100204 (Backend Dev, published).
    $r = backend_search(['query' => 'Scaling']);
    $targetFound = false;
    foreach ($r['items'] as $it) {
        if ((int) $it['id'] === 100204) {
            $targetFound = true;
            check('saved listing 100204 returns is_saved=1 for the student', (int) $it['is_saved'] === 1);
            break;
        }
    }
    if (!$targetFound) {
        check('saved listing 100204 returned by q=Scaling', false);
    }

    echo "\n" . ($failures === 0 ? 'ALL CHECKS PASSED' : "{$failures} CHECK(S) FAILED") . "\n";
    exit($failures === 0 ? 0 : 1);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Verification failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}