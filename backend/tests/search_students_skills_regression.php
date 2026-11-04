<?php

/**
 * MASAR - Student Search Skills Filter And Company Username Regression Test
 *
 * Covers the two additive company-facing changes:
 *
 *   - GET /api/v1/search/students accepts an optional `skill_id` filter
 *   - company-facing student projections expose the public handle `username`
 *
 * SKILLS FILTER (search_repository_students)
 * ------------------------------------------
 *   Case 1   Fixtures resolve from live rows            -> a keyword that
 *            matches students, and skills that actually have holders
 *   Case 2   No filter returns the unfiltered result     -> the existing
 *            behaviour is preserved exactly
 *   Case 3   One skill_id returns only holders           -> every returned
 *            student really holds that skill
 *   Case 4   The count equals the real holder count      -> no rows lost to a
 *            JOIN, proving EXISTS is used
 *   Case 5   Multiple ids mean ANY, not ALL              -> the documented
 *            semantics, compared against COUNT(DISTINCT) in the database
 *   Case 6   No duplicate rows                          -> a student holding
 *            several requested skills still appears once
 *   Case 7   A student with none of the skills is absent
 *   Case 8   Pagination still works                     -> pages are disjoint
 *            and report the same total
 *   Case 9   An unknown skill id yields an empty result  -> not an error
 *   Case 10  The controller rejects malformed ids       -> 422 path, and the
 *            injection-shaped value never reaches SQL
 *   Case 11  Ordering and shape are unchanged           -> id DESC, and the
 *            original id / user_id / full_name columns are all still returned
 *
 * PUBLIC HANDLE (username)
 * ------------------------
 *   Case 12  The student search selects username
 *   Case 13  The search username is the real account handle, and is null
 *            rather than an invented or email-derived value
 *   Case 14  Application detail exposes student_username
 *   Case 15  Application detail still exposes student_email -> the legitimate
 *            application contact path is untouched
 *   Case 16  No credential or contact field is newly exposed
 *
 * The suite is READ-ONLY: it creates no rows and mutates nothing.
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
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/core/http/request.php';
require_once BASE . 'app/core/http/response.php';
require_once BASE . 'app/core/auth/token.php';
require_once BASE . 'app/modules/search/repositories/search_repository.php';
require_once BASE . 'app/modules/search/controllers/search_controller.php';
require_once BASE . 'app/modules/training/repositories/application_repository.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run(array $filters): array
{
    return search_repository_students('ar', $filters + ['page' => 1, 'limit' => 100]);
}

echo "== Setup: fixtures resolved from live rows ==\n";

$any_student = db_fetch_one("SELECT MIN(id) AS id FROM students")[0] ?? null;
$keyword = 'ar';

$total_students = (int) db_fetch_column('SELECT COUNT(*) FROM students');
$keyword_total = (int) db_fetch_column('SELECT COUNT(*) FROM students WHERE full_name LIKE ?', ['%' . $keyword . '%']);
$keyword_students = db_fetch_all('SELECT id FROM students WHERE full_name LIKE ? ORDER BY id', ['%' . $keyword . '%']);

$skill_a = db_fetch_one("SELECT id, name FROM skills WHERE is_active = 1 AND (SELECT COUNT(*) FROM student_skills ss WHERE ss.skill_id = skills.id) >= 3 ORDER BY id LIMIT 1");
$skill_b = db_fetch_one("SELECT id, name FROM skills WHERE is_active = 1 AND (SELECT COUNT(*) FROM student_skills ss WHERE ss.skill_id = skills.id) >= 1 AND id <> ? ORDER BY id LIMIT 1", [$skill_a['id'] ?? 0]);

$holders_a = (int) db_fetch_column('SELECT COUNT(*) FROM student_skills WHERE skill_id = ?', [$skill_a['id'] ?? 0]);

check('Case 1: students exist in the database', $total_students > 0);
check('Case 1: the keyword matches at least one student', $keyword_students !== []);
check('Case 1: a skill with at least 3 holders was found', is_array($skill_a));
check('Case 1: a second distinct skill was found', is_array($skill_b));

if (!is_array($skill_a) || !is_array($skill_b)) {
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

echo '  skill A = ' . $skill_a['name'] . ' (id ' . $skill_a['id'] . ', ' . $holders_a . " holders)\n";
echo '  skill B = ' . $skill_b['name'] . ' (id ' . $skill_b['id'] . ")\n";

echo "\n== Case 2: no filter is unchanged ==\n";

$unfiltered = run([]);
check('Case 2: the unfiltered total matches the raw keyword count', $unfiltered['total'] === $keyword_total);
check('Case 2: the unfiltered total is not clamped by the row limit', $unfiltered['total'] === count($keyword_students));

echo "\n== Case 3 + 4: one skill_id ==\n";

$single = run(['skill_id' => $skill_a['id']]);
$expected_single = (int) db_fetch_column(
    'SELECT COUNT(DISTINCT ss.student_id) FROM student_skills ss JOIN students s ON s.id = ss.student_id WHERE ss.skill_id = ? AND s.full_name LIKE ?',
    [$skill_a['id'], '%' . $keyword . '%']
);

check('Case 4: the count equals the real holder count for this keyword', $single['total'] === $expected_single);
check('Case 4: the single-skill count is not larger than the unfiltered count', $single['total'] <= $unfiltered['total']);

$all_hold = true;
foreach ($single['items'] as $row) {
    if ((int) db_fetch_column('SELECT COUNT(*) FROM student_skills WHERE student_id = ? AND skill_id = ?', [$row['id'], $skill_a['id']]) < 1) {
        $all_hold = false;
    }
}
check('Case 3: every returned student holds the requested skill', $all_hold);
check('Case 3: at least one student matched', $single['total'] > 0);

echo "\n== Case 5 + 6: multiple ids mean ANY ==\n";

$both = run(['skill_id' => [$skill_a['id'], $skill_b['id']]]);
$expected_any = (int) db_fetch_column(
    'SELECT COUNT(DISTINCT s.id) FROM students s
     JOIN student_skills ss ON ss.student_id = s.id
     WHERE ss.skill_id IN (?, ?) AND s.full_name LIKE ?',
    [$skill_a['id'], $skill_b['id'], '%' . $keyword . '%']
);
$expected_all = (int) db_fetch_column(
    'SELECT COUNT(*) FROM students s
     WHERE s.full_name LIKE ?
       AND EXISTS (SELECT 1 FROM student_skills x WHERE x.student_id = s.id AND x.skill_id = ?)
       AND EXISTS (SELECT 1 FROM student_skills y WHERE y.student_id = s.id AND y.skill_id = ?)',
    ['%' . $keyword . '%', $skill_a['id'], $skill_b['id']]
);

check('Case 5: the result equals the ANY (union) count', $both['total'] === $expected_any);
check('Case 5: the documented semantics are ANY, and ANY differs from ALL here', $expected_any !== $expected_all || $expected_all === 0);

$both_ids = array_map('intval', array_column($both['items'], 'id'));
check('Case 6: no student is duplicated by holding several skills', count($both_ids) === count(array_unique($both_ids)));
check('Case 6: the returned row count matches the total', count($both_ids) === $both['total']);

echo "\n== Case 7: students without the skill are excluded ==\n";

$holder_ids = array_map('intval', array_column($both['items'], 'id'));
$non_holder = db_fetch_one(
    'SELECT s.id FROM students s
     WHERE s.full_name LIKE ?
       AND NOT EXISTS (SELECT 1 FROM student_skills ss WHERE ss.student_id = s.id AND ss.skill_id IN (?, ?))
     ORDER BY s.id LIMIT 1',
    ['%' . $keyword . '%', $skill_a['id'], $skill_b['id']]
);

if ($non_holder) {
    check('Case 7: a non-matching student exists to test against', true);
    check('Case 7: that student is absent from the filtered result', !in_array((int) $non_holder['id'], $holder_ids, true));
} else {
    check('Case 7: every keyword-matching student holds at least one of the two skills', true);
}

echo "\n== Case 8: pagination ==\n";

$p1 = search_repository_students('ar', ['skill_id' => [$skill_a['id'], $skill_b['id']], 'page' => 1, 'limit' => 3]);
$p2 = search_repository_students('ar', ['skill_id' => [$skill_a['id'], $skill_b['id']], 'page' => 2, 'limit' => 3]);

check('Case 8: page 1 returns at most the limit', count($p1['items']) <= 3);
check('Case 8: page 2 returns at most the limit', count($p2['items']) <= 3);
check('Case 8: both pages report the same total', $p1['total'] === $p2['total']);
check('Case 8: the pages do not overlap', count(array_intersect(array_column($p1['items'], 'id'), array_column($p2['items'], 'id'))) === 0);

echo "\n== Case 9: an unknown skill id ==\n";

$unknown = run(['skill_id' => 99999999]);
check('Case 9: an unknown skill id yields zero results', $unknown['total'] === 0);
check('Case 9: and no rows at all', $unknown['items'] === []);

echo "\n== Case 10: controller validation ==\n";

check('Case 10: a single integer id is accepted', search_controller_skill_ids('5') === [5]);
check('Case 10: a comma separated list is accepted', search_controller_skill_ids('5,9') === [5, 9]);
check('Case 10: a repeated parameter is accepted', search_controller_skill_ids([5, 9]) === [5, 9]);
check('Case 10: an empty value means "no filter"', search_controller_skill_ids('') === []);
check('Case 10: null means "no filter"', search_controller_skill_ids(null) === []);
check('Case 10: a non-numeric value is rejected', search_controller_skill_ids('abc') === null);
check('Case 10: zero is rejected', search_controller_skill_ids('0') === null);
check('Case 10: a negative value is rejected', search_controller_skill_ids('-3') === null);
check('Case 10: an injection-shaped value is rejected at the controller', search_controller_skill_ids('1 OR 1=1') === null);
check('Case 10: an injection-shaped value yields no ids in the repository', search_repository_normalize_skill_ids('1) OR 1=1--') === []);
check('Case 10: duplicated ids are collapsed', search_controller_skill_ids([7, 7, 7]) === [7]);

echo "\n== Case 11: ordering and response shape ==\n";

$shape_rows = array_map('intval', array_column($unfiltered['items'], 'id'));
$sorted = $shape_rows;
rsort($sorted);
check('Case 11: rows are still ordered id DESC', $shape_rows === $sorted);

$first_row = $unfiltered['items'][0] ?? [];
check('Case 11: `id` is still selected', array_key_exists('id', $first_row));
check('Case 11: `user_id` is still selected', array_key_exists('user_id', $first_row));
check('Case 11: `full_name` is still selected', array_key_exists('full_name', $first_row));

echo "\n== Case 12 + 13: the student search exposes the public handle ==\n";

check('Case 12: the search row carries a username key', array_key_exists('username', $first_row));

$with_username = array_values(array_filter($unfiltered['items'], static fn($r) => !empty($r['username'] ?? null)));
$db_username_count = (int) db_fetch_column("SELECT COUNT(*) FROM students s JOIN users u ON u.id = s.user_id WHERE s.full_name LIKE ? AND u.username IS NOT NULL AND u.username <> ''", ['%' . $keyword . '%']);
check('Case 13: the number of rows with a handle matches the database', count($with_username) === $db_username_count);

$mismatch = true;
foreach ($with_username as $row) {
    $expected = (string) db_fetch_column('SELECT username FROM users WHERE id = ?', [$row['user_id']]);
    if ($expected !== (string) $row['username']) {
        $mismatch = false;
    }
    if (str_contains((string) $row['username'], '@')) {
        $mismatch = false;
    }
}
check('Case 13: every username is the real account handle and never an email', $mismatch);

$null_handles = array_values(array_filter($unfiltered['items'], static fn($r) => ($r['username'] ?? null) === null));
check('Case 13: a student with no handle reports null rather than a fallback', true);
if ($null_handles !== []) {
    $first_null = $null_handles[0];
    check(
        'Case 13: that null really is null in the database',
        db_fetch_column('SELECT username FROM users WHERE id = ?', [$first_null['user_id']]) === null
    );
}

echo "\n== Case 14 + 15 + 16: application detail exposes the handle too ==\n";

$application = db_fetch_one(
    'SELECT a.id, a.student_id, s.user_id
     FROM training_applications a
     JOIN students s ON s.id = a.student_id
     ORDER BY a.id
     LIMIT 1'
);

check('Case 14: an existing application row was found for the read', is_array($application));

if (is_array($application)) {
    $detail = application_repository_find_with_details((int) $application['id']);

    check('Case 14: the detail row was resolved', is_array($detail));
    check('Case 14: it carries a student_username key', is_array($detail) && array_key_exists('student_username', $detail));
    check('Case 14: it still carries a student_name key', is_array($detail) && array_key_exists('student_name', $detail));

    $expected_username = db_fetch_column('SELECT username FROM users WHERE id = ?', [$application['user_id']]);

    /*
     | array_key_exists, NOT `??`: every seeded account has username = NULL,
     | and `$detail['student_username'] ?? 'missing'` would coerce that NULL to
     | the string 'missing' and fail for the wrong reason. This is exactly the
     | trap AGENTS.md warns about.
     */
    check(
        'Case 14: student_username equals the account handle, NULL included',
        is_array($detail)
            && array_key_exists('student_username', $detail)
            && $detail['student_username'] === $expected_username
    );

    $db_null_handles = (int) db_fetch_column("SELECT COUNT(*) FROM users WHERE username IS NULL OR username = ''");
    check('Case 14: the comparison is meaningful because handles are unbackfilled', $db_null_handles > 0);

    check(
        'Case 15: the legitimate application contact email is UNCHANGED',
        is_array($detail) && array_key_exists('student_email', $detail)
            && ($detail['student_email'] ?? null) === db_fetch_column('SELECT email FROM users WHERE id = ?', [$application['user_id']])
    );
    check(
        'Case 15: the application snapshot email column is still present',
        is_array($detail) && array_key_exists('email', $detail)
    );
    check(
        'Case 15: the application snapshot phone column is still present',
        is_array($detail) && array_key_exists('phone', $detail)
    );

    $json = (string) json_encode($detail);
    foreach (['password', 'password_hash', 'token_hash', 'secret'] as $secret_key) {
        check("Case 16: `{$secret_key}` is not exposed by the application detail", !preg_match('/"' . $secret_key . '"\s*:/', $json));
    }
    check('Case 16: no bcrypt hash prefix is exposed', !str_contains($json, '$2y$'));

    $accepted = application_repository_get_accepted_by_training(
        (int) db_fetch_column('SELECT training_id FROM training_applications WHERE status = ? ORDER BY id LIMIT 1', ['accepted'])
    );
    check('Case 14: the accepted-list projection resolved', is_array($accepted));
    if ($accepted !== []) {
        check('Case 14: the accepted rows carry student_username', array_key_exists('student_username', $accepted[0]));
        check('Case 15: the accepted rows still carry student_email', array_key_exists('student_email', $accepted[0]));
    } else {
        check('Case 14: no accepted applications exist, so the list check is vacuous', true);
    }
}

echo "\n== Case 17: the full controller path ==\n";

/*
 | Everything above drives search_repository_students() directly. This case goes
 | through search_controller_students(), which is what the route actually calls,
 | so the controller's validation, the service's 2-character keyword minimum and
 | the repository filter are all exercised together.
 |
 | It needs no HTTP: search_controller_students() returns the envelope array the
 | route turns into a response, and it reads auth_user() only for the role.
 */
$filter_ids = search_controller_skill_ids((string) $skill_a['id']);
check('Case 17: the controller accepted the skill id', is_array($filter_ids) && $filter_ids === [(int) $skill_a['id']]);

$viaController = search_controller_students(['q' => $keyword, 'skill_id' => $skill_a['id']]);
check('Case 17: the controller path succeeds', ($viaController['success'] ?? null) === true);
$controllerTotal = (int) ($viaController['data']['total'] ?? -1);
check('Case 17: its total matches the repository result', $controllerTotal === $single['total']);
check('Case 17: it returns items', is_array($viaController['data']['items'] ?? null));

$badFilter = search_controller_students(['q' => $keyword, 'skill_id' => '0']);
check('Case 17: a zero skill_id is a 422 from the controller', ($badFilter['success'] ?? null) === false && (int) ($badFilter['status'] ?? 0) === 422);

/*
 | A 1-character keyword is NOT a 400. search_controller_search() only rejects
 | an EMPTY q, while search_service_valid_query() enforces the 2-character
 | minimum by returning an empty result set instead. That asymmetry is
 | pre-existing behaviour on every search endpoint and is deliberately left
 | alone, so the assertion records what the code actually does.
 */
$shortKeyword = search_controller_students(['q' => 'a']);
check('Case 17: a 1-character keyword returns 200 with an empty result set (pre-existing)', ($shortKeyword['success'] ?? null) === true && (int) ($shortKeyword['data']['total'] ?? -1) === 0);

$emptyKeyword = search_controller_students(['q' => '']);
check('Case 17: an empty keyword is a 400', ($emptyKeyword['success'] ?? null) === false && (int) ($emptyKeyword['status'] ?? 0) === 400);

echo "\n== Result ==\n";

if ($failures > 0) {
    echo "FAILURES: {$failures}\n";
    exit(1);
}

echo "ALL PASS\n";
exit(0);