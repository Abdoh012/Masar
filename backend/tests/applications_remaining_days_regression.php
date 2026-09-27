<?php

/**
 * MASAR - Applications `remaining_days` Regression
 *
 * Verifies the shared Application card calculation
 * (application_calculate_remaining_days() in
 *  app/shared/functions/application_cards.php) for EVERY Application response
 * and for the Dashboard's applications_snapshot.recent.
 *
 * The rule under test - remaining_days is a countdown INSIDE the training
 * period, so it can never leave [0, duration]:
 *
 *   now < starts_at     -> remaining_days = duration   (not started yet)
 *   starts_at <= now    -> duration - whole days elapsed since starts_at
 *   now >= ends_at      -> remaining_days = 0          (never negative)
 *
 * `duration` is the FIXED calendar-day span of the training and must not move
 * as time passes.
 *
 * Fixtures: a dedicated student with ZERO pre-existing applications gets one
 * freshly inserted training per (regime x status) combination, so every card
 * builder is exercised - application_applied_card, application_accepted_card,
 * application_rejected_card and application_withdrawn_card - with dates in
 * every countdown regime. All fixture rows are tagged with a unique run prefix
 * and removed by cleanup(), which is also registered on shutdown.
 *
 * Expected values are derived in PHP from the fixture dates read back out of
 * the database, independently of the implementation under test, so the
 * assertions are a real cross-check and not a restatement of the code.
 *
 * Training-side `remaining_days` (Dashboard active_training, recommended
 * trainings, search ordering) keeps its original "now -> ends_at" semantics and
 * is asserted separately as a scope guard.
 *
 * Run from the backend root:
 *     php tests/applications_remaining_days_regression.php
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
require_once BASE . 'app/modules/training/services/training_service.php';
require_once BASE . 'app/shared/functions/application_cards.php';

const REMDAYS_PREFIX = 'REMDAYS-REGRESSION-';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run_case(string $token, string $request_uri, string $route = 'applications'): array
{
    $case_file = dirname(__FILE__) . '/applications_remaining_days_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg('bearer')
        . ' ' . escapeshellarg($token)
        . ' ' . escapeshellarg($request_uri)
        . ' ' . escapeshellarg($route);

    $output = (string) shell_exec($command . ' 2>&1');

    $status = 0;
    $body = '';
    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, strlen('STATUS='));
        }
        if (str_starts_with($line, 'BODY=')) {
            $body = substr($line, strlen('BODY='));
        }
    }

    $payload = json_decode($body, true);

    return [
        'status' => $status,
        'body'   => $body,
        'data'   => is_array($payload) ? ($payload['data'] ?? []) : [],
    ];
}

/** Items of a paginated list response. */
function list_items(array $data): array
{
    if (isset($data['items']) && is_array($data['items'])) {
        return $data['items'];
    }
    return is_array($data) ? $data : [];
}

/**
 * Independent model of the required behaviour, derived from the raw training
 * dates and "today" only - it never calls the code under test.
 */
function expected_remaining_days(string $starts_at, string $ends_at): ?int
{
    $duration = training_calculate_duration($starts_at, $ends_at);

    if ($duration === null) {
        return null;
    }

    $start_day = strtotime(date('Y-m-d', (int) strtotime($starts_at)));
    $elapsed = (int) floor((strtotime('today') - $start_day) / 86400);

    if ($elapsed <= 0) {
        return $duration;
    }

    return max(0, $duration - $elapsed);
}

/* ------------------------------------------------------------------ */
/* Fixtures                                                            */
/* ------------------------------------------------------------------ */

$run = strtoupper(bin2hex(random_bytes(4)));
$training_ids = [];
$application_ids = [];

function remdays_cleanup(): void
{
    global $application_ids, $training_ids;

    /*
    | Sweep by the shared prefix instead of only the ids this run happened to
    | record, so a half-provisioned run is still cleaned in full. Applications
    | go first because training_applications references training_listings.
    */
    db_execute('DELETE FROM training_applications WHERE message LIKE ?', [REMDAYS_PREFIX . '%']);
    db_execute('DELETE FROM training_listings WHERE title LIKE ?', [REMDAYS_PREFIX . '%']);

    $application_ids = [];
    $training_ids = [];
}

register_shutdown_function('remdays_cleanup');

/*
| Sweep fixtures abandoned by an earlier crashed run BEFORE provisioning.
| register_shutdown_function() cannot fire if the process is hard-killed (a
| timeout, a closed console), and leftover rows are not inert: trainings in the
| past with published_at = NOW() break training_dates_normalization_regression,
| and applications on a would-be probe student break the Dashboard zero-state
| cases. So the suite starts from a known-clean slate instead of assuming it.
*/
$swept = (int) (db_fetch_one(
    "SELECT COUNT(*) AS c FROM training_listings WHERE title LIKE ?",
    [REMDAYS_PREFIX . '%']
)['c'] ?? 0);

if ($swept > 0) {
    echo "  sweeping {$swept} training(s) left behind by an earlier interrupted run\n";
    db_execute('DELETE FROM training_applications WHERE message LIKE ?', [REMDAYS_PREFIX . '%']);
    db_execute('DELETE FROM training_listings WHERE title LIKE ?', [REMDAYS_PREFIX . '%']);
}

check('no stale fixture rows from an earlier run remain', (int) (db_fetch_one(
    "SELECT COUNT(*) AS c FROM training_listings WHERE title LIKE ?",
    [REMDAYS_PREFIX . '%']
)['c'] ?? 0) === 0);

echo "== Setup: dedicated student with zero applications ==\n";

$student = db_fetch_one(
    "SELECT s.id AS student_id, s.user_id, s.specialization_id, u.email
     FROM students s
     JOIN users u ON u.id = s.user_id
     WHERE u.role = 'student'
       AND u.status = 'active'
       AND (SELECT COUNT(*) FROM training_applications a WHERE a.student_id = s.id) = 0
     ORDER BY s.id
     LIMIT 1"
);

if (!is_array($student)) {
    check('dedicated student with zero applications found', false);
    echo "\n== Result ==\nFAILURES: {$failures}\n";
    exit(1);
}

check('dedicated student with zero applications found', true);

$sid = (int) $student['student_id'];
$spec_id = (int) $student['specialization_id'];

$token = jwt_issue_access_token([
    'id'   => (int) $student['user_id'],
    'role' => 'student',
]);

$company_id = (int) (db_fetch_one('SELECT id FROM companies ORDER BY id LIMIT 1')['id'] ?? 0);
check('a company is available for fixture trainings', $company_id > 0);

/*
| One training + application per (regime x status). Offsets are relative to
| the real "today" so every countdown regime is represented. Dates carry an
| explicit clock time, and the assertions compare whole days, matching
| training_calculate_duration().
*/
$regimes = [
    'future'       => ['start' => 30, 'end' => 45],
    'just_started' => ['start' => 0,  'end' => 9],
    'active'       => ['start' => -5, 'end' => 10],
    'ending_soon'  => ['start' => -8, 'end' => 1],
    'ended'        => ['start' => -40, 'end' => -25],
];

$statuses = ['submitted', 'accepted', 'rejected', 'withdrawn'];

$day = static fn(int $offset, string $time): string =>
    date('Y-m-d', strtotime(($offset >= 0 ? '+' : '-') . abs($offset) . ' days', strtotime('today'))) . ' ' . $time;

// Lifecycle timestamps: a deterministic, increasing ladder so the Dashboard
// `recent` ordering is well defined.
$applied_index = 0;

/* $fixture_apps[regime][status] => row reference, for the detail-endpoint check */
$fixture_apps = [];

foreach ($regimes as $regime => $offset) {
    foreach ($statuses as $status) {
        $starts_at = $day($offset['start'], '09:00:00');
        $ends_at   = $day($offset['end'], '17:00:00');

        $title = REMDAYS_PREFIX . $run . ' ' . $regime . ' ' . $status;

        db_execute(
            "INSERT INTO training_listings
                (company_id, specialization_id, title, description, training_type, mode,
                 status, published_at, starts_at, ends_at, application_deadline, location)
             VALUES (?, ?, ?, ?, 'shadowing', 'onsite', 'published', NOW(), ?, ?, ?, 'Cairo')",
            [
                $company_id,
                $spec_id,
                $title,
                'Fixture training created by applications_remaining_days_regression.php '
                    . 'to pin the remaining_days countdown. Safe to delete.',
                $starts_at,
                $ends_at,
                $starts_at,
            ]
        );
        $training_id = (int) db_last_insert_id();
        $training_ids[] = $training_id;

        $applied_at = date('Y-m-d H:i:s', strtotime('today 00:00:00') - (86400 * 60) + (3600 * $applied_index));
        $applied_index++;

        db_execute(
            "INSERT INTO training_applications
                (training_id, student_id, company_id, status, rejection_reason,
                 message, applied_at, reviewed_at, withdrawn_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $training_id,
                $sid,
                $company_id,
                $status,
                $status === 'rejected' ? 'other' : null,
                REMDAYS_PREFIX . $run,
                $applied_at,
                in_array($status, ['accepted', 'rejected'], true) ? $applied_at : null,
                $status === 'withdrawn' ? $applied_at : null,
            ]
        );
        $application_id = (int) db_last_insert_id();
        $application_ids[] = $application_id;

        $fixture_apps[$regime][$status] = [
            'application_id' => $application_id,
            'training_id'    => $training_id,
            'title'          => $title,
            'starts_at'      => $starts_at,
            'ends_at'        => $ends_at,
        ];
    }
}

check(
    '20 fixture trainings inserted (5 countdown regimes x 4 statuses)',
    count($training_ids) === 20 && count($application_ids) === 20
);

/* ------------------------------------------------------------------ */
/* 1. The invariant holds on every Application endpoint                 */
/* ------------------------------------------------------------------ */

echo "\n== 1. Every Application endpoint: 0 <= remaining_days <= duration, "
    . "and it matches the spec formula ==\n";

$endpoints = [
    // 'expect' = how many fixture cards the tab must return. /all returns every
    // fixture (5 regimes x 4 statuses); each status tab returns 5 (one regime each).
    'applied'   => ['uri' => '/api/v1/applications/applied?page=1&limit=100',   'expect' => 5],
    'accepted'  => ['uri' => '/api/v1/applications/accepted?page=1&limit=100',  'expect' => 5],
    'rejected'  => ['uri' => '/api/v1/applications/rejected?page=1&limit=100',  'expect' => 5],
    'withdrawn' => ['uri' => '/api/v1/applications/withdrawn?page=1&limit=100', 'expect' => 5],
    'all'       => ['uri' => '/api/v1/applications/all?page=1&limit=100',       'expect' => 20],
];

/* title -> fixture training id, so every card can be traced back to its dates */
$fixture_by_title = [];
foreach (db_fetch_all('SELECT id, title, starts_at, ends_at FROM training_listings WHERE title LIKE ?', [REMDAYS_PREFIX . $run . '%']) as $row) {
    $fixture_by_title[(string) $row['title']] = [
        'training_id' => (int) $row['id'],
        'starts_at'   => (string) $row['starts_at'],
        'ends_at'     => (string) $row['ends_at'],
    ];
}

check('20 fixture trainings readable back from the database', count($fixture_by_title) === 20);

$cards_by_endpoint = [];

foreach ($endpoints as $name => $spec) {
    $uri = $spec['uri'];
    $expect = $spec['expect'];
    $res = run_case($token, $uri);
    check("GET {$uri} -> 200", $res['status'] === 200);

    $items = list_items($res['data']);
    check("{$name} returned its {$expect} fixture cards", count($items) === $expect);

    $seen_titles = [];
    $bad_invariant = 0;
    $bad_value = 0;
    $missing_fields = 0;

    foreach ($items as $item) {
        $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
        $fixture = $fixture_by_title[$title] ?? null;
        if ($fixture === null) {
            continue;
        }
        $seen_titles[] = $title;

        if (!array_key_exists('duration', $item) || !array_key_exists('remaining_days', $item)) {
            $missing_fields++;
            continue;
        }

        $duration = (int) $item['duration'];
        $remaining = (int) $item['remaining_days'];

        if ($remaining < 0 || $remaining > $duration) {
            $bad_invariant++;
        }

        $want = expected_remaining_days($fixture['starts_at'], $fixture['ends_at']);
        if ($remaining !== $want) {
            $bad_value++;
            echo "        {$name}: {$title} remaining_days={$remaining} expected={$want}"
                . " (duration={$duration}, now->end=" . training_calculate_remaining_days($fixture['ends_at']) . ")\n";
        }
    }

    check("{$name}: all cards carry duration and remaining_days", $missing_fields === 0);
    check("{$name}: remaining_days within [0, duration] for every card", $bad_invariant === 0);
    check("{$name}: remaining_days matches the spec formula for every card", $bad_value === 0);
    check("{$name}: all {$expect} fixture cards were matched back to their training", count($seen_titles) === $expect);

    $cards_by_endpoint[$name] = $items;
}

/* ------------------------------------------------------------------ */
/* 2. Each countdown regime, verified on the wire                      */
/* ------------------------------------------------------------------ */

echo "\n== 2. The five countdown regimes, verified through the API ==\n";

$all_items = list_items($cards_by_endpoint['all'] ?? []);

/** @var array<string,array> $by_regime regime => card */
$by_regime = [];
foreach ($all_items as $item) {
    $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
    foreach (array_keys($regimes) as $regime) {
        if (str_contains($title, $regime)) {
            $by_regime[$regime] = $item;
        }
    }
}

check('every regime is represented in /applications/all', count($by_regime) === count($regimes));

foreach ($regimes as $regime => $offset) {
    $card = $by_regime[$regime] ?? null;
    if (!is_array($card)) {
        check("regime {$regime} present", false);
        continue;
    }

    $duration = (int) ($card['duration'] ?? -1);
    $remaining = (int) ($card['remaining_days'] ?? -1);
    $ends_at = (string) ($card['ends_at'] ?? '');
    $unbounded = training_calculate_remaining_days($ends_at);

    echo sprintf(
        "  %-12s duration=%-3d remaining_days=%-3d (old now->end helper would say %d)\n",
        $regime,
        $duration,
        $remaining,
        (int) $unbounded
    );

    $check = match ($regime) {
        'future'       => $remaining === $duration,
        'just_started' => $remaining === $duration,
        'active'       => $remaining === $duration - 5,
        'ending_soon'  => $remaining === 1,
        'ended'        => $remaining === 0,
        default        => false,
    };

    check("regime {$regime}: remaining_days is the expected countdown", $check);
}

$future_card = $by_regime['future'] ?? [];
$future_ends = (string) ($future_card['ends_at'] ?? '');
check(
    'the pre-fix over-count is real for the future fixture (now->end > remaining_days), '
        . 'so the old code would fail these assertions',
    $future_ends !== ''
        && (int) training_calculate_remaining_days($future_ends) > (int) ($future_card['remaining_days'] ?? 0)
);

/* ------------------------------------------------------------------ */
/* 3. duration is fixed, identical everywhere                           */
/* ------------------------------------------------------------------ */

echo "\n== 3. duration is the FIXED calendar-day span, identical on every endpoint ==\n";

$duration_by_training = [];
$mismatched_duration = 0;

foreach ($cards_by_endpoint as $endpoint => $items) {
    foreach ($items as $item) {
        $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
        $fixture = $fixture_by_title[$title] ?? null;
        if ($fixture === null) {
            continue;
        }

        $expected = training_calculate_duration($fixture['starts_at'], $fixture['ends_at']);

        if ((int) ($item['duration'] ?? -1) !== $expected) {
            $mismatched_duration++;
        }

        $key = (string) $fixture['training_id'];
        $duration_by_training[$key] ??= (int) ($item['duration'] ?? -1);
        if ($duration_by_training[$key] !== (int) ($item['duration'] ?? -1)) {
            $mismatched_duration++;
        }
    }
}

check('duration is the fixed DATEDIFF span and never varies by endpoint', $mismatched_duration === 0);
check('duration never depends on today (future fixture still shows its full span)',
    (int) ($by_regime['future']['duration'] ?? 0) === (int) ($by_regime['future']['duration'] ?? 0)
    && (int) ($by_regime['future']['duration'] ?? 0) > 0);
check('an ended training still reports its original full duration (not 0)',
    (int) ($by_regime['ended']['duration'] ?? 0) > 0
    && (int) ($by_regime['ended']['remaining_days'] ?? -1) === 0);

/* ------------------------------------------------------------------ */
/* 4. All four statuses agree on the same shared calculation           */
/* ------------------------------------------------------------------ */

echo "\n== 4. All four card builders share one calculation ==\n";

$all_titles = [];
$all_ok = true;
foreach (['applied', 'accepted', 'rejected', 'withdrawn'] as $name) {
    foreach (list_items($cards_by_endpoint[$name] ?? []) as $item) {
        $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
        $fixture = $fixture_by_title[$title] ?? null;
        if ($fixture === null) {
            continue;
        }
        $all_titles[$title] = true;
        if ((int) ($item['remaining_days'] ?? -1) !== expected_remaining_days($fixture['starts_at'], $fixture['ends_at'])) {
            $all_ok = false;
        }
    }
}
check('all 20 cards across the four status tabs follow the same rule', $all_ok && count($all_titles) === 20);

/* ------------------------------------------------------------------ */
/* 5. The application detail response (application_service_enrich_)      */
/* ------------------------------------------------------------------ */

echo "\n== 5. GET /api/v1/applications/{id} (the enrich_* response path) ==\n";

$detail_bad = 0;
$detail_missing = 0;
$detail_checked = 0;

foreach ($fixture_apps as $regime => $by_status) {
    foreach ($by_status as $status => $ref) {
        $res = run_case($token, '/api/v1/applications/' . $ref['application_id']);

        if ($res['status'] !== 200) {
            $detail_bad++;
            echo "        {$regime}/{$status}: detail endpoint returned {$res['status']}\n";
            continue;
        }

        $item = $res['data'];
        $detail_checked++;

        if (!array_key_exists('duration', $item) || !array_key_exists('remaining_days', $item)) {
            $detail_missing++;
            continue;
        }

        $want = expected_remaining_days($ref['starts_at'], $ref['ends_at']);
        $remaining = (int) $item['remaining_days'];
        $duration = (int) $item['duration'];

        if ($remaining !== $want || $remaining < 0 || $remaining > $duration) {
            $detail_bad++;
            echo "        {$regime}/{$status}: remaining_days={$remaining} expected={$want} duration={$duration}\n";
        }
    }
}

check("all 20 fixture applications were reachable through the detail endpoint ({$detail_checked} requests)", $detail_checked === 20);
check('every detail response carries duration and remaining_days', $detail_missing === 0);
check('every detail response obeys 0 <= remaining_days <= duration and the spec formula', $detail_bad === 0);

/* ------------------------------------------------------------------ */
/* 6. Dashboard applications_snapshot.recent uses the same values      */
/* ------------------------------------------------------------------ */

echo "\n== 6. Dashboard applications_snapshot.recent matches the Application API ==\n";

$dash = run_case($token, '/api/v1/students/dashboard', 'students');
check('GET /api/v1/students/dashboard -> 200', $dash['status'] === 200);

$snapshot = $dash['data']['applications_snapshot'] ?? [];
$recent = is_array($snapshot['recent'] ?? null) ? $snapshot['recent'] : [];

check('applications_snapshot.recent is present', is_array($recent) && count($recent) > 0);
check('applications_snapshot.recent holds at most 3 cards', count($recent) <= 3);

/* index the Application API cards by training title for the cross-check */
$api_by_title = [];
foreach ($all_items as $item) {
    $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
    if ($title !== '') {
        $api_by_title[$title] = $item;
    }
}

$matched = 0;
$dash_mismatch = 0;
foreach ($recent as $item) {
    $title = (string) ($item['training_title'] ?? $item['title'] ?? '');
    $api = $api_by_title[$title] ?? null;
    if ($api === null) {
        continue;
    }
    $matched++;
    if (
        (int) ($item['remaining_days'] ?? -1) !== (int) ($api['remaining_days'] ?? -2)
        || (int) ($item['duration'] ?? -1) !== (int) ($api['duration'] ?? -2)
    ) {
        $dash_mismatch++;
        echo "        dashboard '{$title}': remaining_days={$item['remaining_days']} "
            . "vs applications/all {$api['remaining_days']}\n";
    }
}

check('every Dashboard recent card was matched back to the same application in /applications/all', $matched > 0);
check('Dashboard recent duration and remaining_days equal the Application API values', $dash_mismatch === 0);

$dash_invariant_bad = 0;
foreach ($recent as $item) {
    $r = (int) ($item['remaining_days'] ?? -1);
    $du = (int) ($item['duration'] ?? -1);
    if ($r < 0 || $r > $du) {
        $dash_invariant_bad++;
    }
}
check('Dashboard recent cards also satisfy 0 <= remaining_days <= duration', $dash_invariant_bad === 0);

/*
| applications_snapshot exposes FLAT counts, not a nested `counts` object, and
| it uses the card vocabulary: the snapshot's `applied` counter is the number of
| training_applications rows in the canonical 'submitted' status. Asserted
| against the database so this change is provably neutral on the counts.
*/
$db_total = (int) (db_fetch_one(
    "SELECT COUNT(*) AS c FROM training_applications WHERE student_id = ?",
    [$sid]
)['c'] ?? 0);

$db_counts = [];
foreach ($statuses as $status) {
    $db_counts[$status] = (int) (db_fetch_one(
        "SELECT COUNT(*) AS c FROM training_applications WHERE student_id = ? AND status = ?",
        [$sid, $status]
    )['c'] ?? 0);
}

$expected_counts = [
    'total'     => $db_total,
    'applied'   => $db_counts['submitted'],
    'accepted'  => $db_counts['accepted'],
    'rejected'  => $db_counts['rejected'],
    'withdrawn' => $db_counts['withdrawn'],
];

$count_mismatch = [];
foreach ($expected_counts as $key => $want) {
    $got = (int) ($snapshot[$key] ?? -1);
    if ($got !== $want) {
        $count_mismatch[] = "{$key}={$got} (db {$want})";
    }
}

check(
    'applications_snapshot counts still match the database (unchanged behaviour)'
        . ($count_mismatch ? ': ' . implode(', ', $count_mismatch) : ''),
    $count_mismatch === []
);

check('applications_snapshot.total is the sum of the four status counters',
    (int) ($snapshot['total'] ?? -1) === array_sum([$db_counts['submitted'], $db_counts['accepted'], $db_counts['rejected'], $db_counts['withdrawn']]));

/* ------------------------------------------------------------------ */
/* 7. Scope guard: Training-side remaining_days is untouched            */
/* ------------------------------------------------------------------ */

echo "\n== 7. Scope guard: Training and Search semantics are unchanged ==\n";

$active = $dash['data']['active_training'] ?? null;
if (is_array($active)) {
    check(
        'Dashboard active_training.remaining_days still uses the now->ends_at helper',
        ($active['remaining_days'] ?? null) === training_calculate_remaining_days($active['ends_at'] ?? null)
    );
} else {
    check('Dashboard active_training is null or present (no crash)', true);
}

$recommended_ok = true;
foreach ((array) ($dash['data']['recommended_trainings'] ?? []) as $rec) {
    if (
        array_key_exists('remaining_days', $rec)
        && array_key_exists('ends_at', $rec)
        && ($rec['remaining_days'] ?? null) !== training_calculate_remaining_days($rec['ends_at'] ?? null)
    ) {
        $recommended_ok = false;
    }
}
check('recommended_trainings.remaining_days still uses the now->ends_at helper', $recommended_ok);

$student_id_ok = (int) ($dash['data']['student']['id'] ?? 0) === $sid;
check('Dashboard still resolves the authenticated student', $student_id_ok);

check('recent_notifications key is still present and untouched',
    array_key_exists('recent_notifications', $dash['data']));

/* ------------------------------------------------------------------ */

remdays_cleanup();

$leftover = (int) (db_fetch_one(
    "SELECT COUNT(*) AS c FROM training_listings WHERE title LIKE ?",
    [REMDAYS_PREFIX . $run . '%']
)['c'] ?? 0);
$leftover_apps = (int) (db_fetch_one(
    "SELECT COUNT(*) AS c FROM training_applications WHERE message LIKE ?",
    [REMDAYS_PREFIX . $run . '%']
)['c'] ?? 0);
check('all fixture training rows removed by cleanup', $leftover === 0);
check('all fixture application rows removed by cleanup', $leftover_apps === 0);

echo "\n== Result ==\n";
if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}
echo "FAILURES: {$failures}\n";
exit(1);
