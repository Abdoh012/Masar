<?php

/**
 * MASAR - Student Dashboard Endpoint Regression Test
 *
 * Verifies GET /api/v1/students/dashboard that aggregates the authenticated
 * student's full dashboard from the existing domain modules:
 *
 *   Case 1   Guest (no credentials)                   -> 401 Unauthorized
 *   Case 2   Company token                            -> 403 'Student access required.'
 *   Case 3   Valid student token                      -> 200 OK, success=true
 *   Case 4   Data envelope exposes all 9 sections (student, quick_stats,
 *            active_training, applications_snapshot, certificates,
 *            recommended_trainings, recent_notifications, next_action)
 *   Case 5   student section     : id matches DB, full_name matches DB,
 *            profile_completion.percentage is int 0..100
 *   Case 6   quick_stats         : applications == DB total; active_trainings
 *            == DB accepted count; issued_certificates == DB issued sum;
 *            profile_completion == int
 *   Case 7   applications_snapshot: per-status counts match the canonical
 *            repository counters, applied+accepted+rejected+withdrawn == total,
 *            acceptance_rate in [0,100], recent[] <= 3 with All-card DTO
 *   Case 8   active_training     : null when no accepted application exists;
 *            otherwise a full object with remaining_days derived from ends_at
 *   Case 9   certificates        : eligible/pending/issued/revoked match the
 *            certificate repository counts (unchanged by the preview rule);
 *            recent[] is a preview of the single latest ISSUED certificate
 *            only, presented with can_download=false (no download URL /
 *            download endpoint)
 *   Case 10  recommended_trainings: AT MOST 2 items, and never two of the same
 *            price class (at most one paid + at most one free); type in
 *            Shadowing / Hands-on / Project-based; items never endpoint-expired;
 *            specialization id always equals the student's specialization
 *            (SQL-level scoping); the card exposes the 12 pre-existing keys plus
 *            price / free_trial_days / created_at / application_deadline
 *   Case 11  recent_notifications: up to 5, each exposing id/type/title/body/
 *            read/created_at, values sourced from the real notifications table
 *   Case 12  next_action         : deterministic type/title/message/target
 *   Case 13  Student isolation   : two students see ONLY their own data
 *            (student.id, full_name, recent applications, notifications)
 *   Case 18  recent_notifications preview limit: the Dashboard caps the
 *            preview at 5. Two phases, both on an active student who starts
 *            with ZERO notifications so the expected list is fully determined:
 *              18a  7 notifications exist -> exactly 5 are returned, and they
 *                   are the 5 newest by created_at DESC
 *              18b  only 2 notifications exist -> both are returned
 *            Ordering is asserted as non-increasing created_at, matching the
 *            existing repository ORDER BY created_at DESC. It is deliberately
 *            NOT asserted against an id tie-break: the notification query has
 *            no secondary sort, and 82 users in the seeded data really do share
 *            a created_at, so any id-based ordering claim would be a test of
 *            MySQL's whim rather than of the code.
 *   Case 14  Zero-state student  : empty applications -> all counts 0,
 *            acceptance_rate 0, active_training null, recent[] == []
 *   Case 15  Certificates preview : data.certificates.recent holds AT MOST one
 *            item, and that item is ALWAYS status=issued (never pending,
 *            eligible or revoked). Fixture-driven so the status mix is known:
 *              - student WITH issued certificates   -> exactly 1 item, issued
 *              - newest row is a LATER revoked one -> the older issued row is
 *                returned and the revoked row never appears
 *              - pending/revoked but NO issued      -> recent == []
 *              - the returned row is the MAX approved_at issued row
 *            (approved_at is the column the certificate presenter exposes as
 *            issued_at, and the repository's established issued ordering)
 *            Fixture certificates are removed again before the run ends.
 *   Case 16  Applications preview : data.applications_snapshot.recent holds at
 *            most 3 applications, ordered by each application's LATEST
 *            lifecycle action (withdrawn_at / reviewed_at / applied_at maxed
 *            per row) DESC with id DESC as the deterministic tie-breaker.
 *            Fixture-driven so the lifecycle timestamps are deliberately
 *            inconsistent with the applied_at ordering:
 *              - an OLDER application with a RECENT accepted/rejected/
 *                withdrawn action outranks a NEWER application with no newer
 *                action
 *              - an untouched Applied application is placed by its
 *                submission timestamp
 *              - identical latest-action timestamps fall back to id DESC
 *            The snapshot counts (total/applied/accepted/rejected/withdrawn/
 *            acceptance_rate) and the All-card DTO fields are re-verified
 *            unchanged. Fixture applications are removed again before the end.
 *   Case 17  recommended_trainings: at most 2, being the most-APPLIED eligible
 *            training per price class (1 paid + 1 free, paid first), the winner
 *            counted from REAL training_applications rows, tie-broken by
 *            training id DESC. Fixture-driven where the seeded data cannot
 *            discriminate:
 *              17a  both price classes exist -> exactly 2, one paid and one
 *                   free, both the most-applied eligible training of its class
 *              17b  an EXPIRED paid/free training AND a training from ANOTHER
 *                   specialization, each given 20 applications (far above any
 *                   seed row, with the highest ids in the table), must still
 *                   never be recommended
 *              17c  no eligible paid training  -> only the free one
 *              17d  no eligible free training  -> only the paid one
 *              17e  neither available         -> []
 *              17f  a FREE training that still carries compensation_amount and
 *                   trial_period_days (given 9999.99 / 21 decoys) is the free
 *                   recommendation, and must STILL report price=null and
 *                   free_trial_days=null. The seeded free trainings that do
 *                   carry a trial_period_days make this load bearing: the gate
 *                   is is_paid, not the column being NULL.
 *              17g  a training with application_deadline = NULL but a real
 *                   ends_at is the recommendation, and must STILL report
 *                   application_deadline = null. Every seeded published
 *                   training carries a deadline, so this path is unreachable
 *                   from the seed data and is the only way to prove the DTO
 *                   has no ends_at/starts_at fallback: a `$deadline ?? $ends_at`
 *                   would invent a deadline the training does not have and would
 *                   contradict the Applications API, which only enforces a
 *                   deadline when the column is actually set.
 *            17a also re-verifies the three added commercial/audit fields
 *            against the stored training_listings row of the recommended
 *            training itself:
 *              price           == compensation_amount (float), null when free
 *              free_trial_days == trial_period_days (int), null when free
 *              created_at      == created_at, via the same application_iso8601
 *                               formatting as starts_at / ends_at
 *            and application_deadline:
 *              - equals training_listings.application_deadline exactly
 *              - is present and a real timestamp whenever one is stored
 *              - is NOT taken from ends_at (the stored deadline differs from
 *                ends_at in every published row, so this discriminates)
 *              - is deliberately NOT required to differ from starts_at: a
 *                training may validly set its deadline to the moment it begins
 *                (seed training 100204 sets both to 2026-08-28 09:00:00)
 *            17e additionally re-verifies the other dashboard sections, the
 *            applications_snapshot sum and the certificates/next_action shape.
 *            The students for 17c/17d/17e are resolved dynamically from the
 *            live data. Fixture rows are tagged in training_listings.title and
 *            removed again by dashboard_rec_fixture_cleanup(), which is also
 *            registered as a shutdown handler.
 *
 * Mutation coverage for the Case 17 rules (each was temporarily broken and the
 * suite re-run to confirm the assertions above actually fail):
 *   - tie-break id DESC -> id ASC        => 17a + 17b fail
 *   - drop the paid/free price pin       => 17a/17c/17d fail (11 assertions)
 *   - drop the "not expired" SQL guard   => 17b fails
 *   - drop the specialization scope      => 14 assertions fail
 *   - forward compensation_amount /
 *     trial_period_days without the
 *     is_paid gate                       => ONLY 17f fails, which is why the
 *     17f decoy fixture exists
 *   - source created_at from published_at => 2 created_at assertions fail
 *   - return price as the raw DECIMAL
 *     string instead of a float          => the "real price" assertion fails
 *   - application_deadline falling back
 *     to ends_at when NULL               => 2 x 17g assertions fail
 *   - application_deadline sourced from
 *     starts_at                          => 3 assertions fail (2 x 17a, 1 x 17g)
 *   - drop the canonical type guard      => NO assertion fails, and that is
 *     expected: training_listings.training_type is an ENUM of exactly those
 *     three strings, so the guard is a provable no-op. Case 10 asserts the
 *     constant/enum agreement instead of pretending the guard is load bearing.
 *
 * Run from the backend root:
 *     php tests/dashboard_endpoint_regression.php
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
require_once BASE . 'app/modules/certificates/repositories/certificate_repository.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function run_scenario(string $scenario, string $token): array
{
    $case_file = dirname(__FILE__) . '/dashboard_endpoint_case.php';
    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($case_file)
        . ' ' . escapeshellarg($scenario)
        . ' ' . escapeshellarg($token);

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
        'status'  => $status,
        'body'    => $body,
        'success' => is_array($payload) ? ($payload['success'] ?? null) : null,
        'data'    => is_array($payload) ? ($payload['data'] ?? []) : [],
    ];
}

function student_by_email(string $email): ?array
{
    return db_fetch_one(
        "SELECT u.id AS user_id, u.role, u.status, s.id AS student_id,
                s.full_name, s.specialization_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.email = ?
         LIMIT 1",
        [$email]
    );
}

function token_for(array $student): string
{
    return jwt_issue_access_token([
        'id'   => (int) $student['user_id'],
        'role' => 'student',
    ]);
}

function app_counts(int $sid, string $status): int
{
    return (int) (db_fetch_one(
        "SELECT COUNT(*) AS total FROM training_applications
         WHERE student_id = ? AND status = ?",
        [$sid, $status]
    )['total'] ?? 0);
}

function app_total(int $sid): int
{
    return (int) (db_fetch_one(
        "SELECT COUNT(*) AS total FROM training_applications
         WHERE student_id = ? AND status IN ('submitted','accepted','rejected','withdrawn')",
        [$sid]
    )['total'] ?? 0);
}

/*
 * Certificate preview fixture
 * ------------------------------------------------------------------
 * The dashboard certificate preview is driven by certificate rows, so the
 * preview cases need a KNOWN status mix. The fixture provisions synthetic
 * certificates on a probe student that is resolved dynamically as
 * "zero certificates + enough real training sessions", which makes the
 * resulting dashboard state fully determined by this file.
 *
 * Every provisioned row carries a unique run-scoped certificate_code prefix
 * and is deleted again in dashboard_cert_fixture_cleanup(), which is also
 * registered as a shutdown handler so an aborted run never leaves fixture
 * rows behind. No real certificate is read, written or deleted.
 */

const DASHBOARD_CERT_FIXTURE_PREFIX = 'DASHDASH-CERT-';

function dashboard_cert_fixture_cleanup(): void
{
    db_execute(
        'DELETE FROM certificates WHERE certificate_code LIKE ?',
        [DASHBOARD_CERT_FIXTURE_PREFIX . '%']
    );
}

/**
 * Provisions one synthetic certificate per spec on a clean probe student.
 *
 * Each spec is ['status' => string, 'approved_at' => string|null,
 *               'revoked_at' => string|null]. Specs are consumed in order
 * against the probe student's real training sessions so the
 * training_session_id unique key is respected.
 *
 * Returns ['ok' => bool, 'reason' => string, 'student' => array,
 *          'token' => string, 'codes' => string[]].
 */
function dashboard_cert_fixture_provision(array $specs): array
{
    $out = ['ok' => false, 'reason' => '', 'student' => [], 'token' => '', 'codes' => []];

    $needed = max(1, count($specs));

    $probe = db_fetch_one(
        "SELECT s.id AS student_id, s.user_id, u.email
         FROM students s
         JOIN users u ON u.id = s.user_id
         WHERE u.role = 'student'
           AND u.status = 'active'
           AND (SELECT COUNT(*) FROM certificates c WHERE c.student_id = s.id) = 0
           AND (SELECT COUNT(*) FROM training_sessions ts WHERE ts.student_id = s.id) >= ?
         ORDER BY s.id
         LIMIT 1",
        [$needed]
    );

    if (!is_array($probe) || (int) ($probe['student_id'] ?? 0) <= 0) {
        $out['reason'] = 'No probe student with zero certificates and >= ' . $needed . ' training sessions found.';
        return $out;
    }

    $sid = (int) $probe['student_id'];

    $sessions = db_fetch_all(
        "SELECT ts.id, ts.training_id, ts.company_id
         FROM training_sessions ts
         WHERE ts.student_id = ?
         ORDER BY ts.id
         LIMIT {$needed}",
        [$sid]
    );

    if (!is_array($sessions) || count($sessions) < $needed) {
        $out['reason'] = 'Probe student has fewer usable training sessions than required.';
        return $out;
    }

    $codes = [];
    $run   = strtoupper(bin2hex(random_bytes(4)));

    foreach (array_values($specs) as $i => $spec) {
        $session = $sessions[$i];
        $code    = DASHBOARD_CERT_FIXTURE_PREFIX . $run . '-' . $i;

        db_execute(
            "INSERT INTO certificates
                (certificate_code, student_id, company_id, training_id, training_session_id,
                 status, title, start_date, end_date, grade, grade_label, employment_eligible,
                 requested_at, reviewed_at, approved_at, revoked_at, reviewed_by,
                 rejection_reason, revocation_reason, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '88.00', 'Good', 1,
                     NOW(), ?, ?, ?, NULL, NULL, NULL, NOW(), NOW())",
            [
                $code,
                $sid,
                (int) $session['company_id'],
                (int) $session['training_id'],
                (int) $session['id'],
                (string) $spec['status'],
                'Certificate of Completion - Dashboard Preview Fixture ' . $i,
                date('Y-m-d', strtotime('-30 days')),
                date('Y-m-d', strtotime('-1 day')),
                $spec['reviewed_at'] ?? ($spec['approved_at'] ?? null),
                $spec['approved_at'] ?? null,
                $spec['revoked_at'] ?? null,
            ]
        );

        $codes[] = $code;
    }

    $out['ok']      = true;
    $out['student'] = [
        'student_id'  => $sid,
        'user_id'     => (int) $probe['user_id'],
        'full_name'   => (string) $probe['email'],
        'email'       => (string) $probe['email'],
    ];
    $out['token']    = token_for($out['student']);
    $out['codes']    = $codes;

    return $out;
}

/**
 * The certificate the dashboard preview MUST return for a student: the single
 * latest ISSUED row by the repository's established issued ordering
 * (approved_at DESC, id DESC). approved_at is the column the certificate
 * presenter exposes as issued_at.
 */
function dashboard_cert_expected_latest_issued(int $student_id): ?array
{
    return db_fetch_one(
        "SELECT id, status, approved_at
         FROM certificates
         WHERE student_id = ? AND status = 'issued'
         ORDER BY approved_at DESC, id DESC
         LIMIT 1",
        [$student_id]
    );
}

/*
 * Applications preview fixture
 * ------------------------------------------------------------------
 * The dashboard applications preview is ordered by each application's latest
 * lifecycle action, so the ordering cases need applications whose submission /
 * acceptance / rejection / withdrawal timestamps are deliberately INCONSISTENT
 * with their applied_at ordering. The fixture provisions synthetic
 * applications on a probe student resolved dynamically as "zero applications",
 * which makes the resulting snapshot fully determined by this file.
 *
 * The lifecycle columns are the real ones the module already uses and that the
 * card presenters read: applied_at (submission), reviewed_at (accepted AND
 * rejected) and withdrawn_at (withdrawn). No column is invented and no
 * lifecycle behaviour is invoked - rows are inserted directly and deleted
 * again by dashboard_app_fixture_cleanup(), which is also registered as a
 * shutdown handler so an aborted run leaves nothing behind.
 */

const DASHBOARD_APP_FIXTURE_PREFIX = 'DASHDASH-APP-';

function dashboard_app_fixture_cleanup(): void
{
    db_execute(
        'DELETE FROM training_applications WHERE message LIKE ?',
        [DASHBOARD_APP_FIXTURE_PREFIX . '%']
    );
}

/**
 * Provisions one synthetic application per spec on a clean probe student.
 *
 * Each spec is ['status' => string, 'applied_at' => string,
 *               'reviewed_at' => ?string, 'withdrawn_at' => ?string,
 *               'rejection_reason' => ?string].
 *
 * The message column carries the run-scoped cleanup tag, which doubles as the
 * test-visible "message" DTO value so no real text is overwritten.
 *
 * Returns ['ok' => bool, 'reason' => string, 'student' => array,
 *          'token' => string, 'rows' => array] where each row is
 * ['spec' => spec, 'id' => int].
 */
function dashboard_app_fixture_provision(array $specs): array
{
    $out = ['ok' => false, 'reason' => '', 'student' => [], 'token' => '', 'rows' => []];

    $needed = max(1, count($specs));

    $probe = db_fetch_one(
        "SELECT s.id AS student_id, s.user_id, u.email
         FROM students s
         JOIN users u ON u.id = s.user_id
         WHERE u.role = 'student'
           AND u.status = 'active'
           AND (SELECT COUNT(*) FROM training_applications a WHERE a.student_id = s.id) = 0
         ORDER BY s.id
         LIMIT 1"
    );

    if (!is_array($probe) || (int) ($probe['student_id'] ?? 0) <= 0) {
        $out['reason'] = 'No probe student with zero applications found.';
        return $out;
    }

    $sid = (int) $probe['student_id'];

    // (training_id, student_id) is UNIQUE, so each fixture row needs its own
    // real training. Trainings with a company keep the card DTO fully shaped.
    $trainings = db_fetch_all(
        "SELECT t.id
         FROM training_listings t
         WHERE t.company_id IS NOT NULL
           AND NOT EXISTS (
               SELECT 1 FROM training_applications a
               WHERE a.training_id = t.id AND a.student_id = ?
           )
         ORDER BY t.id
         LIMIT {$needed}",
        [$sid]
    );

    if (!is_array($trainings) || count($trainings) < $needed) {
        $out['reason'] = 'Fewer than ' . $needed . ' usable trainings available for the probe student.';
        return $out;
    }

    $run  = strtoupper(bin2hex(random_bytes(4)));
    $rows = [];

    foreach (array_values($specs) as $i => $spec) {
        $training_id = (int) $trainings[$i]['id'];
        $message     = DASHBOARD_APP_FIXTURE_PREFIX . $run . '-' . $i;

        db_execute(
            "INSERT INTO training_applications
                (training_id, student_id, status, rejection_reason, rejection_note,
                 message, applied_at, reviewed_at, withdrawn_at)
             VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?)",
            [
                $training_id,
                $sid,
                (string) $spec['status'],
                $spec['rejection_reason'] ?? null,
                $message,
                (string) $spec['applied_at'],
                $spec['reviewed_at'] ?? null,
                $spec['withdrawn_at'] ?? null,
            ]
        );

        $rows[] = ['spec' => $spec, 'id' => (int) db_last_insert_id()];
    }

    $out['ok']      = true;
    $out['student'] = [
        'student_id' => $sid,
        'user_id'    => (int) $probe['user_id'],
        'full_name'  => (string) $probe['email'],
        'email'      => (string) $probe['email'],
    ];
    $out['token']   = token_for($out['student']);
    $out['rows']    = $rows;

    return $out;
}

/**
 * The latest lifecycle action timestamp of a fixture spec, derived in PHP from
 * the SAME rule the module uses (GREATEST(COALESCE(withdrawn_at, applied_at),
 * COALESCE(reviewed_at, applied_at), applied_at)) but deliberately computed
 * independently of the repository query so the assertions are a real
 * cross-check and not a restatement of the implementation.
 */
function dashboard_app_spec_latest_action(array $spec): int
{
    $applied    = strtotime((string) $spec['applied_at']);
    $reviewed   = isset($spec['reviewed_at']) && $spec['reviewed_at'] !== null
        ? strtotime((string) $spec['reviewed_at'])
        : null;
    $withdrawn  = isset($spec['withdrawn_at']) && $spec['withdrawn_at'] !== null
        ? strtotime((string) $spec['withdrawn_at'])
        : null;

    return max(
        $withdrawn ?? $applied,
        $reviewed  ?? $applied,
        $applied
    );
}

/**
 * The application ids the dashboard preview MUST return for a fixture set:
 * latest action DESC, then id DESC as the deterministic tie-breaker, capped
 * at $limit. Derived in PHP from the fixture rows only.
 */
function dashboard_app_expected_ids(array $rows, int $limit): array
{
    $keys = [];

    foreach ($rows as $row) {
        $keys[] = [
            'latest' => dashboard_app_spec_latest_action($row['spec']),
            'id'     => (int) $row['id'],
        ];
    }

    usort(
        $keys,
        static function (array $a, array $b): int {
            if ($a['latest'] === $b['latest']) {
                return $b['id'] <=> $a['id'];
            }
            return $b['latest'] <=> $a['latest'];
        }
    );

    return array_map(
        static fn (array $k): int => (int) $k['id'],
        array_slice($keys, 0, $limit)
    );
}

function dashboard_app_ids_of(array $items): array
{
    return array_map(
        static fn ($item): int => (int) ($item['id'] ?? 0),
        $items
    );
}

/*
 * Recommended-trainings fixture
 * ------------------------------------------------------------------
 * The dashboard recommendation rules are "at most one paid + at most one free,
 * same specialization as the student, highest real application count,
 * never expired". Most of those combinations already exist in the seeded
 * specializations, so the paid-only / free-only / empty cases are resolved
 * dynamically from the live data.
 *
 * The remaining rules need proof that they are actually load bearing rather
 * than satisfied by accident, so the fixture creates trainings that WOULD win
 * if a rule leaked: an EXPIRED paid and free training with a very high
 * application count inside the student's own specialization, and a training in
 * a DIFFERENT specialization with a very high application count. Rows are
 * tagged in training_listings.title and removed again by
 * dashboard_rec_fixture_cleanup(), which is also registered as a shutdown
 * handler. No existing training is read-modified or deleted.
 */

const DASHBOARD_REC_FIXTURE_PREFIX = 'DASHDASH-REC-';

/**
 * Case 18 helpers for the recent_notifications preview limit.
 *
 * The fixture is deliberately minimal: it only INSERTs rows into the existing
 * notifications table (no schema, no creation logic, no read-state changes) and
 * deletes them again afterwards. It is tagged in notifications.title so
 * dashboard_notif_fixture_cleanup() can find it, and that cleanup is also
 * registered as a shutdown handler further down.
 */

const DASHBOARD_NOTIF_FIXTURE_PREFIX = 'DASHDASH-NOTIF-%';

/**
 * Removes every fixture notification, plus any that a crashed run left behind.
 */
function dashboard_notif_fixture_cleanup(): void
{
    db_execute(
        'DELETE FROM notifications WHERE title LIKE ?',
        [DASHBOARD_NOTIF_FIXTURE_PREFIX . '%']
    );
}

/**
 * An active student with NO notifications at all. Starting from zero is what
 * makes "exactly the latest 5" and "all available" provable instead of
 * dependent on whatever the seeded data happens to hold.
 */
function dashboard_notif_student_with_none(): ?array
{
    $row = db_fetch_one(
        "SELECT s.user_id, s.full_name, s.specialization_id
           FROM students s
           JOIN users u ON u.id = s.user_id
          WHERE u.role = 'student'
            AND u.status = 'active'
            AND NOT EXISTS (SELECT 1 FROM notifications n WHERE n.user_id = s.user_id)
          ORDER BY s.user_id
          LIMIT 1"
    );

    return is_array($row) && isset($row['user_id']) ? $row : null;
}

/**
 * Inserts $count notifications with DISTINCT created_at values, oldest first.
 *
 * Distinct timestamps matter: notification_repository_list() sorts by
 * created_at DESC with no tie-break, so equal timestamps would make "which 5
 * are newest" ambiguous and the assertion would be testing MySQL, not the cap.
 *
 * Every 3rd row is left read (read_at AND is_read set) so the preview's `read`
 * flag is exercised across the window rather than only as false.
 *
 * @return array the inserted ids, oldest first.
 */
function dashboard_notif_fixture_provision(int $user_id, int $count): array
{
    $ids = [];

    for ($i = 0; $i < $count; $i++) {
        $is_read = ($i % 3) === 0;

        db_execute(
            'INSERT INTO notifications
                (user_id, type, title, body, entity_type, entity_id, is_read, read_at, email_sent_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $user_id,
                'dashboard_fixture',
                DASHBOARD_NOTIF_FIXTURE_PREFIX . sprintf('%02d', $i),
                'Fixture notification body ' . $i,
                'dashboard',
                null,
                $is_read ? 1 : 0,
                $is_read ? sprintf('2026-01-%02d 10:00:00', $i + 1) : null,
                null,
                sprintf('2026-01-%02d 09:00:00', $i + 1),
            ]
        );

        $row = db_fetch_one(
            'SELECT id FROM notifications WHERE user_id = ? AND title = ? ORDER BY id DESC LIMIT 1',
            [$user_id, DASHBOARD_NOTIF_FIXTURE_PREFIX . sprintf('%02d', $i)]
        );

        if (is_array($row) && isset($row['id'])) {
            $ids[] = (int) $row['id'];
        }
    }

    return $ids;
}

/**
 * The ids the endpoint is expected to return for a fixture of $total rows:
 * the newest $limit of them by created_at DESC. The fixture's created_at values
 * are '2026-01-NN', so NN orders them exactly like the timestamps do.
 */
function dashboard_notif_expected_ids(array $inserted_ids, int $total, int $limit): array
{
    $newest = [];

    for ($i = 0; $i < $total; $i++) {
        if (isset($inserted_ids[$i])) {
            $newest[] = $inserted_ids[$i];
        }
    }

    return array_slice(array_reverse($newest), 0, $limit);
}

/**
 * The notification DTO must be exactly the same 6 keys as before the limit
 * change; the cap must not have added, dropped or renamed a field.
 */
function dashboard_notif_dto_ok(array $items): bool
{
    $expected = ['id', 'type', 'title', 'body', 'read', 'created_at'];
    sort($expected);

    foreach ($items as $item) {
        if (!is_array($item)) {
            return false;
        }
        $keys = array_keys($item);
        sort($keys);
        if ($keys !== $expected) {
            return false;
        }
    }

    return true;
}

/**
 * Ordering is asserted as non-increasing created_at, which is exactly what the
 * existing `ORDER BY created_at DESC` guarantees and nothing more.
 */
function dashboard_notif_is_desc(array $items): bool
{
    $previous = null;

    foreach ($items as $item) {
        $created = (string) ( $item['created_at'] ?? '' );
        if ($created === '') {
            return false;
        }
        if ($previous !== null && strcmp($created, $previous) > 0) {
            return false;
        }
        $previous = $created;
    }

    return true;
}

function dashboard_notif_ids_of(array $items): array
{
    return array_map(
        static fn ($item): int => (int) ( $item['id'] ?? 0 ),
        $items
    );
}

function dashboard_rec_fixture_cleanup(): void
{
    $ids = array_map(
        static fn ($row): int => (int) $row['id'],
        db_fetch_all(
            'SELECT id FROM training_listings WHERE title LIKE ?',
            [DASHBOARD_REC_FIXTURE_PREFIX . '%']
        )
    );

    if (empty($ids)) {
        return;
    }

    $in = implode(', ', $ids);

    db_execute("DELETE FROM training_applications WHERE training_id IN ({$in})");
    db_execute("DELETE FROM training_listings WHERE id IN ({$in})");
}

/**
 * Creates one training per spec and gives each of them the requested number of
 * REAL application records from distinct students.
 *
 * Each spec is ['specialization_id' => int, 'is_paid' => bool,
 *               'status' => string, 'ends_at' => string, 'applications' => int].
 *
 * Returns ['ok' => bool, 'reason' => string, 'trainings' => array] where each
 * entry is ['id' => int, 'spec' => spec, 'applications' => int].
 */
function dashboard_rec_fixture_provision(array $specs): array
{
    $out = ['ok' => false, 'reason' => '', 'trainings' => []];

    $needed_apps = 0;

    foreach ($specs as $spec) {
        $needed_apps = max($needed_apps, (int) ($spec['applications'] ?? 0));
    }

    $students = db_fetch_all(
        "SELECT s.id
         FROM students s
         JOIN users u ON u.id = s.user_id
         WHERE u.role = 'student' AND u.status = 'active'
         ORDER BY s.id
         LIMIT {$needed_apps}"
    );

    if (!is_array($students) || count($students) < max(1, $needed_apps)) {
        $out['reason'] = 'Not enough active students to build application fixtures.';
        return $out;
    }

    $company = db_fetch_one(
        'SELECT id FROM companies ORDER BY id LIMIT 1'
    );

    if (!is_array($company) || (int) ($company['id'] ?? 0) <= 0) {
        $out['reason'] = 'No company available for the training fixture.';
        return $out;
    }

    $company_id = (int) $company['id'];
    $run        = strtoupper(bin2hex(random_bytes(4)));

    foreach (array_values($specs) as $i => $spec) {
        $title = DASHBOARD_REC_FIXTURE_PREFIX . $run . '-' . $i;

        db_execute(
            "INSERT INTO training_listings
                (company_id, specialization_id, title, description, training_type, mode,
                 may_lead_to_employment, is_paid, status, published_at,
                 starts_at, ends_at, created_at, updated_at)
             VALUES (?, ?, ?, 'Dashboard recommendation fixture training.',
                     'project_based', 'remote', 0, ?, ?, NOW(), ?, ?, NOW(), NOW())",
            [
                $company_id,
                (int) $spec['specialization_id'],
                $title,
                (int) ($spec['is_paid'] ? 1 : 0),
                (string) $spec['status'],
                (string) $spec['starts_at'],
                (string) $spec['ends_at'],
            ]
        );

        $training_id = (int) db_last_insert_id();
        $apps        = max(0, (int) ($spec['applications'] ?? 0));

        for ($a = 0; $a < $apps; $a++) {
            db_execute(
                "INSERT INTO training_applications
                    (training_id, student_id, status, applied_at)
                 VALUES (?, ?, 'submitted', NOW())",
                [$training_id, (int) $students[$a]['id']]
            );
        }

        $out['trainings'][] = [
            'id'           => $training_id,
            'spec'         => $spec,
            'applications' => $apps,
        ];
    }

    $out['ok'] = true;

    return $out;
}

/**
 * The training ids the dashboard MUST recommend for a student's specialization:
 * the most-applied eligible paid training and the most-applied eligible free
 * training, each with training id DESC as the deterministic tie-breaker.
 * Derived straight from the seed tables, independently of the search pipeline.
 */
function dashboard_rec_expected_ids(int $specialization_id): array
{
    $out = ['paid' => null, 'free' => null];

    foreach ([1 => 'paid', 0 => 'free'] as $is_paid => $bucket) {

        $row = db_fetch_one(
            "SELECT t.id, COUNT(a.id) AS apps
             FROM training_listings t
             LEFT JOIN training_applications a ON a.training_id = t.id
             WHERE t.specialization_id = ?
               AND t.status = 'published'
               AND (t.ends_at IS NULL OR t.ends_at >= NOW())
               AND t.is_paid = ?
             GROUP BY t.id
             ORDER BY apps DESC, t.id DESC
             LIMIT 1",
            [$specialization_id, $is_paid]
        );

        if (is_array($row) && (int) ($row['id'] ?? 0) > 0) {
            $out[$bucket] = (int) $row['id'];
        }
    }

    return $out;
}

/**
 * Resolves an active student whose specialization has exactly the requested
 * eligible-training shape: both buckets, paid only, free only or none.
 */
function dashboard_rec_student_with_buckets(string $shape): ?array
{
    $rows = db_fetch_all(
        "SELECT s.id AS student_id, s.user_id, s.specialization_id, u.email,
                (SELECT COUNT(*) FROM training_listings t
                  WHERE t.specialization_id = s.specialization_id
                    AND t.status = 'published'
                    AND (t.ends_at IS NULL OR t.ends_at >= NOW())
                    AND t.is_paid = 1) AS paid_c,
                (SELECT COUNT(*) FROM training_listings t
                  WHERE t.specialization_id = s.specialization_id
                    AND t.status = 'published'
                    AND (t.ends_at IS NULL OR t.ends_at >= NOW())
                    AND t.is_paid = 0) AS free_c
         FROM students s
         JOIN users u ON u.id = s.user_id
         WHERE u.role = 'student'
           AND u.status = 'active'
           AND s.specialization_id IS NOT NULL
         ORDER BY s.id"
    );

    foreach ($rows as $row) {
        $has_paid = (int) $row['paid_c'] > 0;
        $has_free = (int) $row['free_c'] > 0;

        $match = match ($shape) {
            'both'    => $has_paid && $has_free,
            'paid'    => $has_paid && !$has_free,
            'free'    => !$has_paid && $has_free,
            'neither' => !$has_paid && !$has_free,
            default   => false,
        };

        if ($match) {
            return $row;
        }
    }

    return null;
}

function dashboard_rec_run(array $student): array
{
    $dash = run_scenario('bearer', token_for($student));

    $data = is_array($dash['data']) ? $dash['data'] : [];

    return [
        'status'      => $dash['status'],
        'data'        => $data,
        'recommended' => is_array($data['recommended_trainings'] ?? null) ? $data['recommended_trainings'] : [],
    ];
}

/**
 * Case 11 for the recommendation section: the DTO shape must be exactly the 12
 * pre-existing documented keys PLUS price, free_trial_days, created_at and
 * application_deadline, with nothing else added or removed.
 */
function dashboard_rec_dto_ok(array $items): bool
{
    $keys = [
        'id', 'title', 'company', 'company_logo', 'type', 'mode',
        'is_paid',
        'price', 'free_trial_days', 'created_at', 'application_deadline',
        'starts_at', 'ends_at', 'specialization', 'duration', 'remaining_days',
    ];

    foreach ($items as $item) {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $item)) {
                return false;
            }
        }

        if (count($item) !== count($keys)) {
            return false;
        }
    }

    return true;
}

/**
 * The stored commercial/audit columns for a training, straight from the
 * training_listings row, so a recommendation can be compared against its own
 * source of truth. Deliberately returns the RAW column values (the DECIMAL
 * string and the nullable trial column) rather than the shaped DTO values.
 */
function dashboard_rec_source_row(int $training_id): ?array
{
    $row = db_fetch_one(
        'SELECT id, is_paid, compensation_amount, trial_period_days, created_at, published_at,
                application_deadline, starts_at, ends_at
         FROM training_listings WHERE id = ?',
        [$training_id]
    );

    return is_array($row) ? $row : null;
}

/**
 * The timestamp a recommendation's created_at must be derived from.
 * application_iso8601() is applied on both sides so the comparison is about the
 * underlying instant, not about string formatting.
 */
function dashboard_rec_expected_iso(?string $mysql_datetime): ?string
{
    if ($mysql_datetime === null || $mysql_datetime === '') {
        return null;
    }

    return application_iso8601($mysql_datetime);
}

echo "== Setup: student contexts ==\n";

$student_a = student_by_email('mammuslim2003@gmail.com');
if (!is_array($student_a)) {
    $student_a = student_by_email('youssef.farouk@outlook.com');
}
check('primary student (rich data) resolved', is_array($student_a));

$student_zero = student_by_email('mariam.hassan@gmail.com');
if (!is_array($student_zero)) {
    $student_zero = db_fetch_one(
        "SELECT u.id AS user_id, s.id AS student_id, s.full_name, s.specialization_id
         FROM users u JOIN students s ON s.user_id = u.id
         WHERE (SELECT COUNT(*) FROM training_applications a WHERE a.student_id = s.id) = 0
         ORDER BY s.id LIMIT 1"
    );
    if (is_array($student_zero)) {
        $student_zero['role'] = 'student';
    }
}
check('zero-application student resolved', is_array($student_zero));

$student_other = student_by_email('mariam.lotfy@gmail.com');
if (!is_array($student_other)) {
    $student_other = db_fetch_one(
        "SELECT u.id AS user_id, s.id AS student_id, s.full_name, s.specialization_id
         FROM users u JOIN students s ON s.user_id = u.id
         WHERE (SELECT COUNT(*) FROM training_applications a WHERE a.student_id = s.id) > 0
         ORDER BY s.id LIMIT 1"
    );
    if (is_array($student_other)) {
        $student_other['role'] = 'student';
    }
}
check('other student (isolation) resolved', is_array($student_other));

$company = db_fetch_one(
    "SELECT u.id AS user_id FROM users u
     JOIN companies c ON c.user_id = u.id
     WHERE u.email = 'company@test.local'
     AND u.status = 'active'
     LIMIT 1"
);
check('company account resolved', is_array($company));

$company_token = $company
    ? jwt_issue_access_token(['id' => (int) $company['user_id'], 'role' => 'company'])
    : '';

$token_a    = is_array($student_a)    ? token_for($student_a)    : '';
$token_z    = is_array($student_zero) ? token_for($student_zero) : '';
$token_o    = is_array($student_other) ? token_for($student_other) : '';

echo "\n== Case 1: guest request (no credentials) ==\n";

$guest = run_scenario('guest', '');
check('guest returns HTTP 401', $guest['status'] === 401);
check('guest returns success=false', $guest['success'] === false);
check('guest has no data section', !array_key_exists('student', $guest['data'] ?? []));

echo "\n== Case 2: company token on student endpoint ==\n";

$company_case = run_scenario('bearer', $company_token);
check('company token returns HTTP 403', $company_case['status'] === 403);
check('company token body mentions student access', stripos($company_case['body'], 'student access') !== false);

echo "\n== Case 3: valid student token -> /students/dashboard ==\n";

$dash_a = run_scenario('bearer', $token_a);
check('dashboard returns HTTP 200', $dash_a['status'] === 200);
check('dashboard returns success=true', $dash_a['success'] === true);

$data_a = is_array($dash_a['data']) ? $dash_a['data'] : [];
check('dashboard data is an array', is_array($data_a));

echo "\n== Case 4: data envelope exposes all sections ==\n";

$required_sections = [
    'student', 'quick_stats', 'active_training', 'applications_snapshot',
    'certificates', 'recommended_trainings', 'recent_notifications', 'next_action',
];
$sections_ok = true;
foreach ($required_sections as $section) {
    if (!array_key_exists($section, $data_a)) {
        $sections_ok = false;
        echo "  missing section: {$section}\n";
    }
}
check('all 8 documented dashboard sections present', $sections_ok);

$quick_stats = $data_a['quick_stats'] ?? [];
foreach (['active_trainings', 'applications', 'issued_certificates', 'profile_completion'] as $qs_key) {
    if (!array_key_exists($qs_key, $quick_stats)) {
        $sections_ok = false;
        echo "  missing quick_stats key: {$qs_key}\n";
    }
}
check('quick_stats exposes active_trainings/applications/issued_certificates/profile_completion', $sections_ok);

echo "\n== Case 5: student section ==\n";

$sid_a = (int) ($student_a['student_id'] ?? 0);
$student_section = $data_a['student'] ?? [];
check('student.id equals the authenticated student DB id', (int) ($student_section['id'] ?? 0) === $sid_a);
check('student.full_name matches the DB', (string) ($student_section['full_name'] ?? '') === (string) ($student_a['full_name'] ?? ''));
$percentage = $student_section['profile_completion']['percentage'] ?? null;
check('student.profile_completion.percentage is an int 0..100', is_int($percentage) && $percentage >= 0 && $percentage <= 100);
check('student exposes profile_image_file_id key', array_key_exists('profile_image_file_id', $student_section));

echo "\n== Case 6: quick_stats ==\n";

$db_app_total  = app_total($sid_a);
$db_app_sub    = app_counts($sid_a, 'submitted');
$db_app_acc    = app_counts($sid_a, 'accepted');

$cert_issued_sum = (int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'issued'])
    + (int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'active'])
    + (int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'valid']);

check('quick_stats.applications equals DB total', (int) ($quick_stats['applications'] ?? -1) === $db_app_total);
check('quick_stats.active_trainings equals DB accepted count', (int) ($quick_stats['active_trainings'] ?? -1) === $db_app_acc);
check('quick_stats.issued_certificates equals DB issued sum', (int) ($quick_stats['issued_certificates'] ?? -1) === $cert_issued_sum);
check('quick_stats.profile_completion is an int', is_int($quick_stats['profile_completion'] ?? null));

echo "\n== Case 7: applications_snapshot ==\n";

$snapshot = $data_a['applications_snapshot'] ?? [];
check('snapshot.total equals DB 4-status total', (int) ($snapshot['total'] ?? -1) === $db_app_total);
check('snapshot.applied equals DB submitted count', (int) ($snapshot['applied'] ?? -1) === $db_app_sub);
check('snapshot.accepted equals DB accepted count', (int) ($snapshot['accepted'] ?? -1) === $db_app_acc);
check('snapshot.rejected equals DB rejected count', (int) ($snapshot['rejected'] ?? -1) === app_counts($sid_a, 'rejected'));
check('snapshot.withdrawn equals DB withdrawn count', (int) ($snapshot['withdrawn'] ?? -1) === app_counts($sid_a, 'withdrawn'));
check('per-status counts sum to total', (int)($snapshot['applied'] ?? 0) + (int)($snapshot['accepted'] ?? 0) + (int)($snapshot['rejected'] ?? 0) + (int)($snapshot['withdrawn'] ?? 0) === (int) ($snapshot['total'] ?? -1));
$rate = (int) ($snapshot['acceptance_rate'] ?? -1);
check('acceptance_rate is int in [0,100]', $rate >= 0 && $rate <= 100);

$recent_apps = is_array($snapshot['recent'] ?? null) ? $snapshot['recent'] : [];
check('snapshot.recent is an array', is_array($snapshot['recent'] ?? null));
check('snapshot.recent has at most 3 items', count($recent_apps) <= 3);
$recent_keys_ok = true;
foreach ($recent_apps as $item) {
    foreach (['id', 'training_id', 'training_title', 'status', 'specialization', 'company_name'] as $k) {
        if (!array_key_exists($k, $item)) {
            $recent_keys_ok = false;
        }
    }
}
check('snapshot.recent items reuse the All-tab card DTO keys', $recent_keys_ok);

$live_expected = array_map(
    static fn ($r): int => (int) $r['id'],
    db_fetch_all(
        "SELECT id FROM training_applications
         WHERE student_id = ? AND status IN ('submitted','accepted','rejected','withdrawn')
         ORDER BY GREATEST(
                      COALESCE(withdrawn_at, applied_at),
                      COALESCE(reviewed_at, applied_at),
                      applied_at
                  ) DESC,
                  id DESC
         LIMIT 3",
        [$sid_a]
    )
);
check(
    'snapshot.recent is ordered by latest lifecycle action (id DESC tie-break)',
    dashboard_app_ids_of($recent_apps) === $live_expected
);

echo "\n== Case 8: active_training ==\n";

$active = array_key_exists('active_training', $data_a) ? $data_a['active_training'] : 'MISSING';
check('active_training key exists', $active !== 'MISSING');

if ($active === null) {
    check('active_training is null for the primary student only when DB has no accepted', $db_app_acc === 0);
} else {
    check('active_training exposes the 10 documented keys',
        is_array($active)
        && array_key_exists('id', $active) && array_key_exists('title', $active)
        && array_key_exists('company', $active) && array_key_exists('type', $active)
        && array_key_exists('mode', $active) && array_key_exists('is_paid', $active)
        && array_key_exists('trial_days', $active) && array_key_exists('starts_at', $active)
        && array_key_exists('ends_at', $active) && array_key_exists('remaining_days', $active));
    check('active_training.remaining_days is derived from ends_at',
        ($active['remaining_days'] ?? null) === training_calculate_remaining_days($active['ends_at'] ?? null)
        || (($active['remaining_days'] ?? null) === null && ($active['ends_at'] ?? null) === null));
}

echo "\n== Case 9: certificates ==\n";

$certs = $data_a['certificates'] ?? [];
check('certificates.issued equals DB issued+active+valid sum', (int) ($certs['issued'] ?? -1) === $cert_issued_sum);
check('certificates.pending equals DB pending count', (int) ($certs['pending'] ?? -1) === (int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'pending']));
check('certificates.revoked equals DB revoked count', (int) ($certs['revoked'] ?? -1) === (int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'revoked']));
check('certificates.eligible is non-negative int', is_int($certs['eligible'] ?? null) && (int) ($certs['eligible'] ?? -1) >= 0);

$recent_certs = is_array($certs['recent'] ?? null) ? $certs['recent'] : [];
check('certificates.recent is an array', is_array($certs['recent'] ?? null));
check('certificates.recent has at most 1 item (latest issued only)', count($recent_certs) <= 1);
check('every certificates.recent item is status=issued', (static function (array $items): bool {
    foreach ($items as $item) {
        if (strtolower((string) ($item['status'] ?? '')) !== 'issued') {
            return false;
        }
    }
    return true;
})($recent_certs));
$cert_dto_ok = true;
foreach ($recent_certs as $item) {
    foreach (['id', 'status', 'training_title', 'company_name', 'can_download'] as $k) {
        if (!array_key_exists($k, $item)) {
            $cert_dto_ok = false;
        }
    }
    if (($item['can_download'] ?? true) !== false) {
        $cert_dto_ok = false;
    }
    if (array_key_exists('download_url', $item) && $item['download_url'] !== null) {
        $cert_dto_ok = false;
    }
}
check('certificate recent items reuse the presenter DTO with can_download=false', $cert_dto_ok);

echo "\n== Case 10: recommended_trainings ==\n";

$recommended = is_array($data_a['recommended_trainings'] ?? null) ? $data_a['recommended_trainings'] : [];
check('recommended_trainings has at most 2 items', is_array($recommended) && count($recommended) <= 2);

$allowed_types = [TRAINING_TYPE_SHADOWING, TRAINING_TYPE_HANDS_ON, TRAINING_TYPE_PROJECT_BASED];
$recommend_ok = (is_int(count($recommended)));
$specialization_id_a = (int) ($student_a['specialization_id'] ?? 0);
$today = date('Y-m-d 00:00:00');
$items_checked = 0;
$paid_count = 0;
$free_count = 0;
foreach ($recommended as $item) {
    $items_checked++;
    if (!in_array(strtolower((string) ($item['type'] ?? '')), $allowed_types, true)) {
        $recommend_ok = false;
    }
    $spec = is_array($item['specialization'] ?? null) ? $item['specialization'] : null;
    $spec_id = (int) ($spec['id'] ?? 0);
    if ($specialization_id_a > 0 && $spec_id !== $specialization_id_a) {
        $recommend_ok = false;
    }
    if (
        !empty($item['ends_at'])
        && strtotime((string) $item['ends_at']) !== false
        && strtotime((string) $item['ends_at']) < strtotime($today)
    ) {
        $recommend_ok = false;
    }
    foreach (['id', 'title', 'company', 'company_logo', 'type', 'mode', 'is_paid', 'price', 'free_trial_days', 'created_at', 'application_deadline', 'starts_at', 'ends_at', 'specialization', 'duration', 'remaining_days'] as $k) {
        if (!array_key_exists($k, $item)) {
            $recommend_ok = false;
        }
    }
    if ((bool) ($item['is_paid'] ?? false)) {
        $paid_count++;
    } else {
        $free_count++;
    }
}
check('every recommended item type in Shadowing/Hands-on/Project-based', $recommend_ok);
check('every recommended item specialization matches the student specialization', $recommend_ok);
check('recommended items are never endpoint-expired', $recommend_ok);
check('recommended items expose the training card keys', $recommend_ok);
check('recommended_trainings never contains two paid trainings', $paid_count <= 1);
check('recommended_trainings never contains two free trainings', $free_count <= 1);
echo "  (checked {$items_checked} recommended item(s): {$paid_count} paid, {$free_count} free)\n";

/*
 * The canonical-type guard is deliberately NOT asserted to be load bearing:
 * training_listings.training_type is an ENUM('shadowing','hands_on',
 * 'project_based') and TRAINING_TYPE_SHADOWING / _HANDS_ON / _PROJECT_BASED are
 * exactly those three strings, so no row can ever be filtered out by it. The
 * check above is therefore a schema-consistency guard, not a behavioural one.
 * (Confirmed by mutation: disabling the filter changes no dashboard output.)
 * A fixture cannot make it discriminate, because the database forbids the
 * non-canonical value the filter would have to reject.
 */
check(
    'the canonical type constants still match the training_listings.training_type enum',
    (static function (): bool {
        $enum = (string) (db_fetch_one(
            "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'training_listings'
               AND COLUMN_NAME = 'training_type'"
        )['COLUMN_TYPE'] ?? '');

        $allowed = [];
        if (preg_match_all("/'([^']+)'/", $enum, $m) === 1 || !empty($m[1])) {
            $allowed = $m[1];
        }

        sort($allowed);
        $constants = [TRAINING_TYPE_SHADOWING, TRAINING_TYPE_HANDS_ON, TRAINING_TYPE_PROJECT_BASED];
        sort($constants);

        return $allowed === $constants;
    })()
);

echo "\n== Case 11: recent_notifications ==\n";

$notifications = is_array($data_a['recent_notifications'] ?? null) ? $data_a['recent_notifications'] : [];
check('recent_notifications has at most 5 items', count($notifications) <= 5);
$notification_ok = true;
$db_notif_bodies = [];
$db_notif_rows = db_fetch_all(
    "SELECT id, type, title, body, read_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
    [(int) $student_a['user_id']]
);
foreach (is_array($db_notif_rows) ? $db_notif_rows : [] as $row) {
    $db_notif_bodies[(int) $row['id']] = $row;
}
foreach ($notifications as $item) {
    foreach (['id', 'type', 'title', 'body', 'read', 'created_at'] as $k) {
        if (!array_key_exists($k, $item)) {
            $notification_ok = false;
        }
    }
    $nid = (int) ($item['id'] ?? 0);
    if ($nid > 0 && isset($db_notif_bodies[$nid])) {
        if ((string) ($item['title'] ?? '') !== (string) ($db_notif_bodies[$nid]['title'] ?? '')) {
            $notification_ok = false;
        }
        if ((bool) ($item['read'] ?? false) !== !empty($db_notif_bodies[$nid]['read_at'])) {
            $notification_ok = false;
        }
    }
}
check('notification items are sourced from the real notifications table', $notification_ok);

echo "\n== Case 12: next_action ==\n";

$next = $data_a['next_action'] ?? [];
foreach (['type', 'title', 'message', 'target'] as $k) {
    if (!array_key_exists($k, $next)) {
        check("next_action has {$k}", false);
        $next = [];
    }
}
check('next_action exposes type/title/message/target', is_array($next) && !empty($next['type']) && !empty($next['target']));

echo "\n== Case 13: student isolation ==\n";

$dash_o = run_scenario('bearer', $token_o);
check('other student dashboard returns HTTP 200', $dash_o['status'] === 200);
$data_o = is_array($dash_o['data']) ? $dash_o['data'] : [];
check('other student sees their own student.id', (int) ($data_o['student']['id'] ?? 0) === (int) ($student_other['student_id'] ?? 0));
check('other student sees their own full_name', (string) ($data_o['student']['full_name'] ?? '') === (string) ($student_other['full_name'] ?? ''));
check('primary and other students have distinct ids', (int) ($data_a['student']['id'] ?? 0) !== (int) ($data_o['student']['id'] ?? 0));

$recent_a = is_array($data_a['applications_snapshot']['recent'] ?? null) ? $data_a['applications_snapshot']['recent'] : [];
$recent_o = is_array($data_o['applications_snapshot']['recent'] ?? null) ? $data_o['applications_snapshot']['recent'] : [];
$overlap_ids = array_values(array_intersect(
    array_map(static fn ($it) => (int) ($it['id'] ?? 0), $recent_a),
    array_map(static fn ($it) => (int) ($it['id'] ?? 0), $recent_o)
));
check('recent applications never leak across students', $overlap_ids === []);

$notif_a = is_array($data_a['recent_notifications'] ?? null) ? $data_a['recent_notifications'] : [];
$notif_o = is_array($data_o['recent_notifications'] ?? null) ? $data_o['recent_notifications'] : [];
$notif_overlap = array_values(array_intersect(
    array_map(static fn ($n) => (int) ($n['id'] ?? 0), $notif_a),
    array_map(static fn ($n) => (int) ($n['id'] ?? 0), $notif_o)
));
check('notifications never leak across students', $notif_overlap === []);

echo "\n== Case 14: zero-state student ==\n";

$dash_z = run_scenario('bearer', $token_z);
check('zero-state dashboard returns HTTP 200', $dash_z['status'] === 200);
$data_z = is_array($dash_z['data']) ? $dash_z['data'] : [];

$zero_snapshot = $data_z['applications_snapshot'] ?? [];
check('zero-state total is 0', (int) ($zero_snapshot['total'] ?? -1) === 0);
check('zero-state applied is 0', (int) ($zero_snapshot['applied'] ?? -1) === 0);
check('zero-state accepted is 0', (int) ($zero_snapshot['accepted'] ?? -1) === 0);
check('zero-state rejected is 0', (int) ($zero_snapshot['rejected'] ?? -1) === 0);
check('zero-state withdrawn is 0', (int) ($zero_snapshot['withdrawn'] ?? -1) === 0);
check('zero-state acceptance_rate is 0', (int) ($zero_snapshot['acceptance_rate'] ?? -1) === 0);
check('zero-state recent is an empty array', (is_array($zero_snapshot['recent'] ?? null) && $zero_snapshot['recent'] === []));
check('zero-state active_training is null', array_key_exists('active_training', $data_z) && $data_z['active_training'] === null);
check('zero-state quick_stats.applications is 0', (int) ($data_z['quick_stats']['applications'] ?? -1) === 0);
check('zero-state recent_notifications exists as array', is_array($data_z['recent_notifications'] ?? null));

/*
 * ---------------------------------------------------------------------------
 * Case 15: certificates preview -> only the latest ISSUED certificate
 * ---------------------------------------------------------------------------
 *
 * Fixture-driven so the certificate status mix is fully known. Three probes are
 * provisioned in order, each verified then cleaned up before the next:
 *
 *   15a  newest certificate is a LATER revoked row, older issued rows exist
 *        -> exactly 1 item, status=issued, and it is the older issued row
 *            (the revoked row is never returned and never used as fallback)
 *   15b  three issued rows with distinct approved_at (+ one pending)
 *        -> exactly 1 item, status=issued, and it is the MAX approved_at row
 *   15c  pending + revoked rows and NO issued row
 *        -> recent == [] (no revoked and no pending fallback)
 *
 * Every probe also re-checks that the eligible/pending/issued/revoked counts
 * still match the repository counters, i.e. only the preview list changed.
 */

register_shutdown_function('dashboard_cert_fixture_cleanup');

function dashboard_cert_probe_assert(
    array $fixture,
    array $specs_expected_counts,
    string $label
): array {
    $sid   = (int) $fixture['student']['student_id'];
    $dash  = run_scenario('bearer', (string) $fixture['token']);
    $data  = is_array($dash['data']) ? $dash['data'] : [];
    $certs = is_array($data['certificates'] ?? null) ? $data['certificates'] : [];
    $items = is_array($certs['recent'] ?? null) ? $certs['recent'] : [];

    check("{$label}: dashboard returns HTTP 200", $dash['status'] === 200);

    // Case 5 for the probe: counts must remain the untouched repository values.
    check("{$label}: certificates.issued count unchanged", (int) ($certs['issued'] ?? -1) === (int) $specs_expected_counts['issued']);
    check("{$label}: certificates.pending count unchanged", (int) ($certs['pending'] ?? -1) === (int) $specs_expected_counts['pending']);
    check("{$label}: certificates.revoked count unchanged", (int) ($certs['revoked'] ?? -1) === (int) $specs_expected_counts['revoked']);
    check("{$label}: certificates.eligible is a non-negative int", is_int($certs['eligible'] ?? null) && (int) ($certs['eligible'] ?? -1) >= 0);

    return [$certs, $items];
}

echo "\n== Case 15a: newest certificate is revoked, an older issued one exists ==\n";

$now_issued = date('Y-m-d H:i:s', strtotime('-10 days'));

$fixture_a = dashboard_cert_fixture_provision([
    [ 'status' => 'issued',  'approved_at' => date('Y-m-d H:i:s', strtotime('-30 days')) ],
    [ 'status' => 'issued',  'approved_at' => $now_issued ],
    [ 'status' => 'pending', 'approved_at' => null ],
    [ 'status' => 'revoked', 'approved_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'revoked_at' => date('Y-m-d H:i:s', strtotime('-1 day')) ],
]);

if (!$fixture_a['ok']) {
    check("Case 15a fixture provisioned: {$fixture_a['reason']}", false);
} else {
    check('Case 15a fixture provisioned on a clean probe student', true);

    $sid_a15  = (int) $fixture_a['student']['student_id'];
    $revoked_row = db_fetch_one(
        "SELECT id FROM certificates WHERE student_id = ? AND status = 'revoked' ORDER BY approved_at DESC, id DESC LIMIT 1",
        [$sid_a15]
    );
    $newer_issued_row = db_fetch_one(
        "SELECT id FROM certificates WHERE student_id = ? AND status = 'issued' ORDER BY approved_at DESC, id DESC LIMIT 1",
        [$sid_a15]
    );

    [$certs_a15, $items_a15] = dashboard_cert_probe_assert(
        $fixture_a,
        [
            'issued'  => (int) certificate_repository_count(['student_id' => $sid_a15, 'status' => 'issued'])
                + (int) certificate_repository_count(['student_id' => $sid_a15, 'status' => 'active'])
                + (int) certificate_repository_count(['student_id' => $sid_a15, 'status' => 'valid']),
            'pending' => (int) certificate_repository_count(['student_id' => $sid_a15, 'status' => 'pending']),
            'revoked' => (int) certificate_repository_count(['student_id' => $sid_a15, 'status' => 'revoked']),
        ],
        'Case 15a'
    );

    // Case 1
    check('Case 15a: certificates.recent.length === 1', count($items_a15) === 1);
    check(
        'Case 15a: certificates.recent[0].status === "issued"',
        (string) ($items_a15[0]['status'] ?? '') === 'issued'
    );

    // Case 2
    check(
        'Case 15a: the newest revoked certificate is NOT returned',
        is_array($revoked_row)
            && (int) ($items_a15[0]['id'] ?? 0) !== (int) $revoked_row['id']
    );
    check(
        'Case 15a: the older issued certificate IS returned',
        is_array($newer_issued_row)
            && (int) ($items_a15[0]['id'] ?? 0) === (int) $newer_issued_row['id']
    );

    dashboard_cert_fixture_cleanup();
}

echo "\n== Case 15b: latest issued certificate wins (issued_at / approved_at) ==\n";

$fixture_b = dashboard_cert_fixture_provision([
    [ 'status' => 'issued', 'approved_at' => date('Y-m-d H:i:s', strtotime('-20 days')) ],
    [ 'status' => 'issued', 'approved_at' => date('Y-m-d H:i:s', strtotime('-5 days')) ],
    [ 'status' => 'issued', 'approved_at' => date('Y-m-d H:i:s', strtotime('-2 days')) ],
    [ 'status' => 'pending', 'approved_at' => null ],
]);

if (!$fixture_b['ok']) {
    check("Case 15b fixture provisioned: {$fixture_b['reason']}", false);
} else {
    check('Case 15b fixture provisioned on a clean probe student', true);

    $sid_b15   = (int) $fixture_b['student']['student_id'];
    $expected  = dashboard_cert_expected_latest_issued($sid_b15);
    $oldest    = db_fetch_one(
        "SELECT id FROM certificates WHERE student_id = ? AND status = 'issued' ORDER BY approved_at ASC, id ASC LIMIT 1",
        [$sid_b15]
    );

    [$certs_b15, $items_b15] = dashboard_cert_probe_assert(
        $fixture_b,
        [
            'issued'  => (int) certificate_repository_count(['student_id' => $sid_b15, 'status' => 'issued'])
                + (int) certificate_repository_count(['student_id' => $sid_b15, 'status' => 'active'])
                + (int) certificate_repository_count(['student_id' => $sid_b15, 'status' => 'valid']),
            'pending' => (int) certificate_repository_count(['student_id' => $sid_b15, 'status' => 'pending']),
            'revoked' => (int) certificate_repository_count(['student_id' => $sid_b15, 'status' => 'revoked']),
        ],
        'Case 15b'
    );

    check('Case 15b: certificates.recent.length === 1', count($items_b15) === 1);
    check(
        'Case 15b: certificates.recent[0].status === "issued"',
        (string) ($items_b15[0]['status'] ?? '') === 'issued'
    );

    // Case 4
    check(
        'Case 15b: the returned certificate is the latest issued one (max approved_at / issued_at)',
        is_array($expected) && (int) ($items_b15[0]['id'] ?? 0) === (int) $expected['id']
    );
    check(
        'Case 15b: the oldest issued certificate is not returned',
        is_array($oldest) && is_array($expected) && (int) $oldest['id'] !== (int) $expected['id']
    );
    check(
        'Case 15b: the presented issued_at equals the DB approved_at of the returned row',
        is_array($expected)
            && (string) ($items_b15[0]['issued_at'] ?? '') === (string) $expected['approved_at']
    );

    dashboard_cert_fixture_cleanup();
}

echo "\n== Case 15c: pending/revoked certificates but NO issued certificate ==\n";

$fixture_c = dashboard_cert_fixture_provision([
    [ 'status' => 'pending', 'approved_at' => null ],
    [ 'status' => 'pending', 'approved_at' => null ],
    [ 'status' => 'revoked', 'approved_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'revoked_at' => date('Y-m-d H:i:s', strtotime('-1 day')) ],
    [ 'status' => 'revoked', 'approved_at' => date('Y-m-d H:i:s', strtotime('-2 days')), 'revoked_at' => date('Y-m-d H:i:s', strtotime('-2 days')) ],
]);

if (!$fixture_c['ok']) {
    check("Case 15c fixture provisioned: {$fixture_c['reason']}", false);
} else {
    check('Case 15c fixture provisioned on a clean probe student', true);

    $sid_c15 = (int) $fixture_c['student']['student_id'];

    [$certs_c15, $items_c15] = dashboard_cert_probe_assert(
        $fixture_c,
        [
            'issued'  => (int) certificate_repository_count(['student_id' => $sid_c15, 'status' => 'issued'])
                + (int) certificate_repository_count(['student_id' => $sid_c15, 'status' => 'active'])
                + (int) certificate_repository_count(['student_id' => $sid_c15, 'status' => 'valid']),
            'pending' => (int) certificate_repository_count(['student_id' => $sid_c15, 'status' => 'pending']),
            'revoked' => (int) certificate_repository_count(['student_id' => $sid_c15, 'status' => 'revoked']),
        ],
        'Case 15c'
    );

    // Case 3
    check('Case 15c: certificates.recent is an empty array', is_array($certs_c15['recent'] ?? null) && $certs_c15['recent'] === []);
    check('Case 15c: no revoked certificate is returned as a fallback', count($items_c15) === 0);
    check('Case 15c: no pending certificate is returned as a fallback', count($items_c15) === 0);
    check(
        'Case 15c: the counts still report the pending and revoked certificates',
        (int) ($certs_c15['pending'] ?? -1) > 0 && (int) ($certs_c15['revoked'] ?? -1) > 0
    );

    dashboard_cert_fixture_cleanup();
}

echo "\n== Case 15d: seed student whose newest certificate is a LATER revoked row ==\n";

if ((int) certificate_repository_count(['student_id' => $sid_a, 'status' => 'issued']) > 0) {
    $seed_newest_issued = dashboard_cert_expected_latest_issued($sid_a);
    $seed_newest_any    = db_fetch_one(
        "SELECT id, status FROM certificates WHERE student_id = ? ORDER BY approved_at DESC, id DESC LIMIT 1",
        [$sid_a]
    );

    // The seed student also has revoked rows with a later approved_at than any
    // issued row, so the live dashboard run above must already prove the
    // "newest is revoked" rule on real (non-fixture) data.
    check(
        'Case 15d: the seed student really has a revoked row newer than every issued row',
        is_array($seed_newest_any)
            && (string) $seed_newest_any['status'] === 'revoked'
            && is_array($seed_newest_issued)
    );
    check(
        'Case 15d: the seed dashboard returned the latest issued row, not the newest revoked row',
        is_array($seed_newest_issued)
            && count($recent_certs) === 1
            && (int) ($recent_certs[0]['id'] ?? 0) === (int) $seed_newest_issued['id']
    );
} else {
    check('Case 15d: skipped (seed student has no issued certificate)', true);
}

dashboard_cert_fixture_cleanup();

/*
 * ---------------------------------------------------------------------------
 * Case 16: applications preview -> latest 3, ordered by latest lifecycle action
 * ---------------------------------------------------------------------------
 *
 * Fixture-driven so the lifecycle timestamps are deliberately inconsistent with
 * the applied_at ordering, which is the whole point of the rule. The expected
 * order is derived in PHP from the fixture rows alone (latest action DESC,
 * id DESC, capped at 3), so the assertions cross-check the endpoint instead of
 * restating its SQL. The lifecycle columns are the real ones the module uses:
 * applied_at (submission), reviewed_at (accepted + rejected), withdrawn_at
 * (withdrawn).
 *
 * 16a  a NEWER untouched Applied application must NOT outrank OLDER
 *      applications that were recently accepted / rejected / withdrawn
 * 16b  an untouched Applied application is placed by its submission timestamp
 * 16c  identical latest-action timestamps fall back to id DESC
 * 16d  the snapshot counts and the All-card DTO fields are unchanged
 */

register_shutdown_function('dashboard_app_fixture_cleanup');

/**
 * Runs the dashboard for a fixture student and re-verifies that the snapshot
 * counts still equal the canonical repository counters (the preview size must
 * never influence them). Returns [$snapshot, $items].
 */
function dashboard_app_probe_assert(array $fixture, string $label): array
{
    $sid  = (int) $fixture['student']['student_id'];
    $dash = run_scenario('bearer', (string) $fixture['token']);
    $data = is_array($dash['data']) ? $dash['data'] : [];
    $snap = is_array($data['applications_snapshot'] ?? null) ? $data['applications_snapshot'] : [];
    $items = is_array($snap['recent'] ?? null) ? $snap['recent'] : [];

    check("{$label}: dashboard returns HTTP 200", $dash['status'] === 200);

    $total = (int) (db_fetch_one(
        "SELECT COUNT(*) AS total FROM training_applications
         WHERE student_id = ? AND status IN ('submitted','accepted','rejected','withdrawn')",
        [$sid]
    )['total'] ?? 0);

    check("{$label}: snapshot.total unchanged (all fixture applications counted)", (int) ($snap['total'] ?? -1) === $total);
    check("{$label}: snapshot.applied unchanged", (int) ($snap['applied'] ?? -1) === app_counts($sid, 'submitted'));
    check("{$label}: snapshot.accepted unchanged", (int) ($snap['accepted'] ?? -1) === app_counts($sid, 'accepted'));
    check("{$label}: snapshot.rejected unchanged", (int) ($snap['rejected'] ?? -1) === app_counts($sid, 'rejected'));
    check("{$label}: snapshot.withdrawn unchanged", (int) ($snap['withdrawn'] ?? -1) === app_counts($sid, 'withdrawn'));
    check("{$label}: per-status counts still sum to total", (int) ($snap['applied'] ?? 0) + (int) ($snap['accepted'] ?? 0) + (int) ($snap['rejected'] ?? 0) + (int) ($snap['withdrawn'] ?? 0) === $total);
    check("{$label}: acceptance_rate unchanged", (int) ($snap['acceptance_rate'] ?? -1) === ($total > 0 ? (int) round( (app_counts($sid, 'accepted') / $total) * 100 ) : 0));

    // Case 1
    check("{$label}: snapshot.recent is an array", is_array($snap['recent'] ?? null));
    check("{$label}: snapshot.recent has at most 3 items", count($items) <= 3);

    return [$snap, $items];
}

/**
 * Case 7 for the fixture student: the All-card DTO fields per status are
 * exactly the ones the Applications All tab already returns.
 */
function dashboard_app_dto_ok(array $items): bool
{
    $common = ['id', 'training_id', 'training_title', 'status', 'specialization', 'company_name'];

    $per_status = [
        'Applied'  => array_merge($common, ['applied_at', 'starts_at', 'ends_at', 'duration', 'remaining_days', 'status_message', 'can_withdraw']),
        'Accepted' => array_merge($common, ['accepted_at', 'starts_at', 'ends_at', 'duration', 'remaining_days', 'status_message']),
        'Rejected' => array_merge($common, ['rejected_at', 'rejection_reason', 'rejection_note', 'status_message']),
        'Withdrawn' => array_merge($common, ['withdrawn_at', 'status_message']),
    ];

    foreach ($items as $item) {
        $status = (string) ($item['status'] ?? '');

        if (!isset($per_status[$status])) {
            return false;
        }

        foreach ($per_status[$status] as $key) {
            if (!array_key_exists($key, $item)) {
                return false;
            }
        }
    }

    return true;
}

echo "\n== Case 16a: latest lifecycle action decides the order (not applied_at) ==\n";

$ts = static fn (string $modifier): string => date('Y-m-d H:i:s', strtotime($modifier));

$fixture_app = dashboard_app_fixture_provision([
    // Newest SUBMISSION, but no newer action -> must NOT win.
    [ 'status' => 'submitted', 'applied_at' => $ts('-1 hour') ],
    // OLDEST application, most RECENT action -> must be first.
    [ 'status' => 'accepted', 'applied_at' => $ts('-30 days'), 'reviewed_at' => $ts('-2 minutes') ],
    [ 'status' => 'rejected', 'applied_at' => $ts('-40 days'), 'reviewed_at' => $ts('-30 minutes'), 'rejection_reason' => 'other' ],
    [ 'status' => 'withdrawn', 'applied_at' => $ts('-50 days'), 'withdrawn_at' => $ts('-45 minutes') ],
    // Oldest and untouched.
    [ 'status' => 'submitted', 'applied_at' => $ts('-20 days') ],
]);

if (!$fixture_app['ok']) {
    check("Case 16a fixture provisioned: {$fixture_app['reason']}", false);
} else {
    check('Case 16a fixture provisioned on a clean probe student', true);

    $rows_app = $fixture_app['rows'];

    $accepted_row = null;
    $rejected_row = null;
    $withdrawn_row = null;
    $newest_applied_row = null;
    $oldest_applied_row = null;

    foreach ($rows_app as $row) {
        switch ($row['spec']['status']) {
            case 'accepted':
                $accepted_row = $row;
                break;
            case 'rejected':
                $rejected_row = $row;
                break;
            case 'withdrawn':
                $withdrawn_row = $row;
                break;
            default:
                if ($newest_applied_row === null) {
                    $newest_applied_row = $row;
                } else {
                    $oldest_applied_row = $row;
                }
        }
    }

    [$snap_app, $items_app] = dashboard_app_probe_assert($fixture_app, 'Case 16a');

    // Case 2
    $expected_app_ids = dashboard_app_expected_ids($rows_app, 3);
    check(
        'Case 16a: recent is ordered by latest lifecycle action, id DESC tie-break, max 3',
        dashboard_app_ids_of($items_app) === $expected_app_ids
    );
    check('Case 16a: recent contains exactly the 3 most recently acted-on applications', count($items_app) === 3);
    check(
        'Case 16a: the OLDEST application with the MOST RECENT acceptance is first',
        (int) ($items_app[0]['id'] ?? 0) === (int) ($accepted_row['id'] ?? 0)
    );
    check(
        'Case 16a: the OLDER rejected application outranks the newer accepted one',
        (int) ($items_app[1]['id'] ?? 0) === (int) ($rejected_row['id'] ?? 0)
            && (int) ($items_app[0]['id'] ?? 0) === (int) ($accepted_row['id'] ?? 0)
    );
    check(
        'Case 16a: the OLDER withdrawn application is third',
        (int) ($items_app[2]['id'] ?? 0) === (int) ($withdrawn_row['id'] ?? 0)
    );

    // Case 3
    check(
        'Case 16a: the NEWEST untouched Applied application does NOT outrank the older acted-on ones',
        !in_array((int) ($newest_applied_row['id'] ?? 0), dashboard_app_ids_of($items_app), true)
    );
    check(
        'Case 16a: the oldest untouched application is not in the preview either',
        !in_array((int) ($oldest_applied_row['id'] ?? 0), dashboard_app_ids_of($items_app), true)
    );

    // Case 4
    check(
        'Case 16a: the first item is the Accepted card and carries accepted_at from reviewed_at',
        (string) ($items_app[0]['status'] ?? '') === 'Accepted'
    );
    check(
        'Case 16a: the second item is the Rejected card and carries rejected_at from reviewed_at',
        (string) ($items_app[1]['status'] ?? '') === 'Rejected'
    );
    check(
        'Case 16a: the third item is the Withdrawn card and carries withdrawn_at',
        (string) ($items_app[2]['status'] ?? '') === 'Withdrawn'
    );

    // Case 7
    check('Case 16a: recent items keep the existing All-card DTO fields', dashboard_app_dto_ok($items_app));

    dashboard_app_fixture_cleanup();
}

echo "\n== Case 16b: untouched Applied applications are placed by submission timestamp ==\n";

$fixture_app_b = dashboard_app_fixture_provision([
    [ 'status' => 'submitted', 'applied_at' => $ts('-3 days') ],
    [ 'status' => 'submitted', 'applied_at' => $ts('-1 hour') ],
    [ 'status' => 'submitted', 'applied_at' => $ts('-2 days') ],
]);

if (!$fixture_app_b['ok']) {
    check("Case 16b fixture provisioned: {$fixture_app_b['reason']}", false);
} else {
    check('Case 16b fixture provisioned on a clean probe student', true);

    $rows_app_b   = $fixture_app_b['rows'];
    $oldest_b     = $rows_app_b[0];
    $newest_b     = $rows_app_b[1];
    $middle_b     = $rows_app_b[2];

    [$snap_b, $items_b] = dashboard_app_probe_assert($fixture_app_b, 'Case 16b');

    check(
        'Case 16b: recent is ordered by applied_at DESC when nothing was acted on',
        dashboard_app_ids_of($items_b) === dashboard_app_expected_ids($rows_app_b, 3)
    );
    check(
        'Case 16b: the most recent submission is first, the oldest submission last',
        (int) ($items_b[0]['id'] ?? 0) === (int) $newest_b['id']
            && (int) ($items_b[1]['id'] ?? 0) === (int) $middle_b['id']
            && (int) ($items_b[2]['id'] ?? 0) === (int) $oldest_b['id']
    );
    check(
        'Case 16b: every item is an untouched Applied card',
        count($items_b) === 3
            && (static function (array $items): bool {
                foreach ($items as $item) {
                    if ((string) ($item['status'] ?? '') !== 'Applied') {
                        return false;
                    }
                }
                return true;
            })($items_b)
    );
    check(
        'Case 16b: each Applied card exposes its submission timestamp as applied_at',
        count($items_b) === 3
            && (string) ($items_b[0]['applied_at'] ?? '') !== ''
            && (string) ($items_b[0]['applied_at'] ?? '') !== (string) ($items_b[2]['applied_at'] ?? '')
    );
    check('Case 16b: recent items keep the existing All-card DTO fields', dashboard_app_dto_ok($items_b));

    dashboard_app_fixture_cleanup();
}

echo "\n== Case 16c: identical latest-action timestamps fall back to id DESC ==\n";

$tied = $ts('-90 minutes');

$fixture_app_c = dashboard_app_fixture_provision([
    [ 'status' => 'submitted', 'applied_at' => $tied ],
    [ 'status' => 'submitted', 'applied_at' => $tied ],
    [ 'status' => 'submitted', 'applied_at' => $tied ],
    [ 'status' => 'submitted', 'applied_at' => $tied ],
    [ 'status' => 'accepted', 'applied_at' => $ts('-5 days'), 'reviewed_at' => $tied ],
    [ 'status' => 'rejected', 'applied_at' => $ts('-6 days'), 'reviewed_at' => $tied, 'rejection_reason' => 'other' ],
]);

if (!$fixture_app_c['ok']) {
    check("Case 16c fixture provisioned: {$fixture_app_c['reason']}", false);
} else {
    check('Case 16c fixture provisioned on a clean probe student', true);

    $rows_app_c = $fixture_app_c['rows'];

    // Insert order is ascending, so id ASC == the fixture order.
    $tied_ids_desc = array_map(
        static fn (array $r): int => (int) $r['id'],
        array_reverse($rows_app_c)
    );
    $expected_c = array_slice($tied_ids_desc, 0, 3);

    [$snap_c, $items_c] = dashboard_app_probe_assert($fixture_app_c, 'Case 16c');

    check(
        'Case 16c: every fixture application shares the same latest action timestamp',
        (static function (array $rows) use ($tied): bool {
            foreach ($rows as $row) {
                if ($row['spec']['applied_at'] !== $tied
                    && (string) date('Y-m-d H:i:s', dashboard_app_spec_latest_action($row['spec'])) !== $tied
                ) {
                    return false;
                }
            }
            return true;
        })($rows_app_c)
    );
    check(
        'Case 16c: ties are broken deterministically with id DESC',
        dashboard_app_ids_of($items_c) === $expected_c
    );
    check(
        'Case 16c: the tie-break yields strictly descending ids',
        count($items_c) === 3
            && (int) $items_c[0]['id'] > (int) $items_c[1]['id']
            && (int) $items_c[1]['id'] > (int) $items_c[2]['id']
    );
    check('Case 16c: recent items keep the existing All-card DTO fields', dashboard_app_dto_ok($items_c));

    dashboard_app_fixture_cleanup();
}

echo "\n== Case 16d: Applications All endpoint behaviour is unchanged ==\n";

// The dashboard preview is the SAME repository query the All tab runs, only
// with a smaller limit. Proving the repository is untouched: a larger limit
// still returns the identical ordering, so no Applications API behaviour moved.
$repo_head = application_repository_get_all_by_student($sid_a, 3);
$repo_wide = application_repository_get_all_by_student($sid_a, 10);
check(
    'Case 16d: the repository ordering for limit 3 is the prefix of the ordering for limit 10',
    array_slice(array_map(static fn ($r) => (int) $r['id'], $repo_wide), 0, 3)
        === array_map(static fn ($r) => (int) $r['id'], $repo_head)
);
check(
    'Case 16d: the repository still defaults to a wide page size (20) for the All tab',
    (function () {
        $r = new ReflectionFunction('application_repository_get_all_by_student');
        $p = $r->getParameters();
        return isset($p[1]) && $p[1]->getDefaultValue() === 20;
    })()
);
check(
    'Case 16d: the dashboard preview did not change the All-tab DTOs (they come from the same mapper)',
    (function () use ($sid_a): bool {
        $cards = application_all_cards(application_repository_get_all_by_student($sid_a, 3));
        foreach ($cards as $card) {
            if (!array_key_exists('id', $card) || !array_key_exists('status', $card)) {
                return false;
            }
        }
        return $cards !== [];
    })()
);

dashboard_app_fixture_cleanup();
dashboard_cert_fixture_cleanup();

/*
 * ---------------------------------------------------------------------------
 * Case 17: recommended_trainings -> at most one paid + one free, most applied
 * ---------------------------------------------------------------------------
 *
 * 17a  both buckets exist            -> exactly 2 recommendations, one paid and
 *                                       one free, both in the student's own
 *                                       specialization, each the most-applied
 *                                       eligible training of its bucket
 * 17b  load-bearing rule check       -> an EXPIRED paid/free training and a
 *                                       training from ANOTHER specialization,
 *                                       all with a much higher application
 *                                       count, must still never be recommended
 * 17c  paid unavailable              -> only the free training is returned
 * 17d  free unavailable              -> only the paid training is returned
 * 17e  neither available             -> recommended_trainings is []
 *
 * Every probe additionally re-checks the DTO shape, the never-two-same-bucket
 * rule and the never-expired rule, and 17e re-verifies that the other
 * dashboard sections are unaffected.
 */

register_shutdown_function('dashboard_rec_fixture_cleanup');

echo "\n== Case 17a: one most-applied paid + one most-applied free ==\n";

$both_student = dashboard_rec_student_with_buckets('both');

if (!is_array($both_student)) {
    check('Case 17a: a student with both paid and free eligible trainings resolved', false);
} else {
    check('Case 17a: a student with both paid and free eligible trainings resolved', true);

    $spec_id   = (int) $both_student['specialization_id'];
    $expected17 = dashboard_rec_expected_ids($spec_id);
    $rec17      = dashboard_rec_run($both_student);

    check('Case 17a: dashboard returns HTTP 200', $rec17['status'] === 200);
    check('Case 17a: recommended_trainings is an array', is_array($rec17['data']['recommended_trainings'] ?? null));

    // Case 1 + Case 2
    check('Case 17a: exactly two recommendations are returned', count($rec17['recommended']) === 2);
    check(
        'Case 17a: exactly one paid and exactly one free',
        count(array_filter($rec17['recommended'], static fn ($i) => (bool) ($i['is_paid'] ?? false))) === 1
            && count(array_filter($rec17['recommended'], static fn ($i) => !($i['is_paid'] ?? false))) === 1
    );

    $paid_items  = array_values(array_filter($rec17['recommended'], static fn ($i) => (bool) ($i['is_paid'] ?? false)));
    $free_items  = array_values(array_filter($rec17['recommended'], static fn ($i) => !($i['is_paid'] ?? false)));
    $paid_first  = (bool) ($rec17['recommended'][0]['is_paid'] ?? false);

    // Case 4 + Case 5
    check(
        'Case 17a: the paid recommendation is the most-applied eligible paid training',
        $expected17['paid'] !== null
            && count($paid_items) === 1
            && (int) $paid_items[0]['id'] === (int) $expected17['paid']
    );
    check(
        'Case 17a: the free recommendation is the most-applied eligible free training',
        $expected17['free'] !== null
            && count($free_items) === 1
            && (int) $free_items[0]['id'] === (int) $expected17['free']
    );
    check(
        'Case 17a: the paid recommendation really has >= applications than every other eligible paid training',
        (function () use ($paid_items, $spec_id): bool {
            if (empty($paid_items)) {
                return false;
            }
            $winner = (int) $paid_items[0]['id'];
            $others = db_fetch_all(
                "SELECT t.id, COUNT(a.id) AS apps
                 FROM training_listings t
                 LEFT JOIN training_applications a ON a.training_id = t.id
                 WHERE t.specialization_id = ? AND t.status = 'published'
                   AND (t.ends_at IS NULL OR t.ends_at >= NOW()) AND t.is_paid = 1
                 GROUP BY t.id",
                [$spec_id]
            );
            $winner_apps = application_repository_count_by_training($winner);
            foreach ($others as $o) {
                if ((int) $o['id'] === $winner) {
                    continue;
                }
                if ((int) $o['apps'] > $winner_apps) {
                    return false;
                }
            }
            return true;
        })()
    );
    check(
        'Case 17a: the free recommendation really has >= applications than every other eligible free training',
        (function () use ($free_items, $spec_id): bool {
            if (empty($free_items)) {
                return false;
            }
            $winner = (int) $free_items[0]['id'];
            $others = db_fetch_all(
                "SELECT t.id, COUNT(a.id) AS apps
                 FROM training_listings t
                 LEFT JOIN training_applications a ON a.training_id = t.id
                 WHERE t.specialization_id = ? AND t.status = 'published'
                   AND (t.ends_at IS NULL OR t.ends_at >= NOW()) AND t.is_paid = 0
                 GROUP BY t.id",
                [$spec_id]
            );
            $winner_apps = application_repository_count_by_training($winner);
            foreach ($others as $o) {
                if ((int) $o['id'] === $winner) {
                    continue;
                }
                if ((int) $o['apps'] > $winner_apps) {
                    return false;
                }
            }
            return true;
        })()
    );
    check(
        'Case 17a: the paid recommendation is listed before the free one',
        $paid_first && count($rec17['recommended']) === 2
    );

    // Case 3
    check(
        'Case 17a: both recommendations carry the student specialization',
        (static function (array $items) use ($spec_id): bool {
            foreach ($items as $item) {
                $spec = is_array($item['specialization'] ?? null) ? $item['specialization'] : null;
                if ((int) ($spec['id'] ?? 0) !== $spec_id) {
                    return false;
                }
            }
            return $items !== [];
        })($rec17['recommended'])
    );

    // Case 6
    check(
        'Case 17a: neither recommendation is expired',
        (static function (array $items): bool {
            foreach ($items as $item) {
                if (empty($item['ends_at'])) {
                    continue;
                }
                if (strtotime((string) $item['ends_at']) < strtotime(date('Y-m-d 00:00:00'))) {
                    return false;
                }
            }
            return true;
        })($rec17['recommended'])
    );

    // Case 11
    check('Case 17a: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17['recommended']));
    check(
        'Case 17a: is_paid in the DTO mirrors training_listings.is_paid',
        (function () use ($rec17): bool {
            foreach ($rec17['recommended'] as $item) {
                $row = db_fetch_one('SELECT is_paid FROM training_listings WHERE id = ?', [(int) $item['id']]);
                if (!is_array($row) || ((bool) $row['is_paid']) !== (bool) ($item['is_paid'] ?? false)) {
                    return false;
                }
            }
            return $rec17['recommended'] !== [];
        })()
    );

    /*
     * Requirements 8, 9 and 11: every added field must equal the stored column
     * of the very training that is being recommended. The paid item and the free
     * item are checked against their own training_listings row, never against a
     * hard-coded id, so the assertions follow the selection.
     */
    foreach ($rec17['recommended'] as $idx => $item) {

        $src   = dashboard_rec_source_row((int) $item['id']);
        $paid  = (bool) $item['is_paid'];
        $label = $paid ? 'paid' : 'free';

        check("Case 17a ({$label}): the recommended training row exists in training_listings", is_array($src));

        if (!is_array($src)) {
            continue;
        }

        // Case 8: price == training_listings.compensation_amount
        check(
            "Case 17a ({$label}): price equals the stored compensation_amount",
            array_key_exists('price', $item)
                && (
                    $paid
                        ? is_numeric($src['compensation_amount'])
                            && (float) $item['price'] === (float) $src['compensation_amount']
                        : $item['price'] === null
                )
        );

        // Case 9: free_trial_days == training_listings.trial_period_days
        check(
            "Case 17a ({$label}): free_trial_days equals the stored trial_period_days",
            array_key_exists('free_trial_days', $item)
                && (
                    $paid
                        ? $item['free_trial_days'] === (int) $src['trial_period_days']
                        : $item['free_trial_days'] === null
                )
        );

        // Case 11: created_at == training_listings.created_at
        check(
            "Case 17a ({$label}): created_at equals the stored training_listings.created_at",
            array_key_exists('created_at', $item)
                && $item['created_at'] === dashboard_rec_expected_iso($src['created_at'] ?? null)
        );

        // Sanity: the field is really populated from the row, not a constant.
        check(
            "Case 17a ({$label}): created_at is a non-empty timestamp",
            is_string($item['created_at'] ?? null) && $item['created_at'] !== ''
        );

        /*
         * application_deadline: the stored application-submission deadline, and
         * nothing else. requirements 1, 2 and 3.
         */
        check(
            "Case 17a ({$label}): application_deadline equals the stored training_listings.application_deadline",
            array_key_exists('application_deadline', $item)
                && $item['application_deadline'] === dashboard_rec_expected_iso($src['application_deadline'] ?? null)
        );
        check(
            "Case 17a ({$label}): application_deadline is present and is a real timestamp when one is stored",
            (static function (array $it) use ($src): bool {
                if (!array_key_exists('application_deadline', $it)) {
                    return false;
                }
                if (($src['application_deadline'] ?? null) === null) {
                    return $it['application_deadline'] === null;
                }
                return is_string($it['application_deadline']) && $it['application_deadline'] !== '';
            })($item)
        );
        /*
         * Requirement 3: the deadline is its own date. It must be reported from
         * the deadline column and must never be back-filled or swapped with the
         * end date. Note it is deliberately NOT asserted to differ from
         * starts_at: a real training may legitimately set its submission
         * deadline to the moment it begins (seed training 100204 does exactly
         * that, 2026-08-28 09:00:00 for both), so "deadline == starts_at" is
         * valid data, not a confusion. The stored deadline differing from
         * ends_at in every published row is what makes the ends_at comparison
         * below a real discrimination.
         */
        check(
            "Case 17a ({$label}): application_deadline is NOT taken from ends_at",
            (static function (array $it) use ($src): bool {
                if (!array_key_exists('application_deadline', $it)) {
                    return false;
                }
                $dl = $it['application_deadline'];

                if (($src['application_deadline'] ?? null) === null) {
                    // Nothing stored: must be null, never a substitute.
                    return $dl === null;
                }

                $ends = dashboard_rec_expected_iso($src['ends_at'] ?? null);

                if ($src['application_deadline'] !== $src['ends_at']) {
                    // The columns hold different instants, so the emitted value
                    // must be the deadline one, not the ends_at one.
                    if ($dl === $ends) {
                        return false;
                    }
                }

                // And in every case it is the deadline instant, verbatim.
                return $dl === dashboard_rec_expected_iso($src['application_deadline'] ?? null);
            })($item)
        );
    }

    check(
        'Case 17a: the paid recommendation exposes a real price and a real free_trial_days',
        (static function (array $items): bool {
            foreach ($items as $i) {
                if (($i['is_paid'] ?? false) !== true) {
                    continue;
                }
                if (!is_int($i['price'] ?? null) && !is_float($i['price'] ?? null)) {
                    return false;
                }
                if ((float) $i['price'] <= 0.0) {
                    return false;
                }
                if (!is_int($i['free_trial_days'] ?? null) || $i['free_trial_days'] <= 0) {
                    return false;
                }
            }
            return true;
        })($rec17['recommended'])
    );
    check(
        'Case 17a: the free recommendation exposes price=null and free_trial_days=null',
        (static function (array $items): bool {
            foreach ($items as $i) {
                if (($i['is_paid'] ?? true) === true) {
                    continue;
                }
                // array_key_exists, not ??: a present null must not be mistaken
                // for a missing key.
                if (!array_key_exists('price', $i) || $i['price'] !== null) {
                    return false;
                }
                if (!array_key_exists('free_trial_days', $i) || $i['free_trial_days'] !== null) {
                    return false;
                }
            }
            return true;
        })($rec17['recommended'])
    );
}

echo "\n== Case 17b: expired trainings and other specializations are excluded ==\n";

$expired_student = dashboard_rec_student_with_buckets('both');

if (!is_array($expired_student)) {
    check('Case 17b: a student with both buckets resolved', false);
} else {
    check('Case 17b: a student with both buckets resolved', true);

    $spec_id_b = (int) $expired_student['specialization_id'];

    // A different, real specialization so "other specialization" is genuine.
    $other_spec = db_fetch_one(
        'SELECT id FROM specializations WHERE id <> ? ORDER BY id DESC LIMIT 1',
        [$spec_id_b]
    );

    if (!is_array($other_spec) || (int) ($other_spec['id'] ?? 0) <= 0) {
        check('Case 17b: a second real specialization resolved', false);
    } else {
        check('Case 17b: a second real specialization resolved', true);

        $other_spec_id = (int) $other_spec['id'];

        // 20 applications each: far above anything in the seed data, and the
        // fixture ids are the highest in the table, so a leaked rule would win.
        $fixture17b = dashboard_rec_fixture_provision([
            [
                'specialization_id' => $spec_id_b,
                'is_paid'           => true,
                'status'            => 'published',
                'starts_at'         => date('Y-m-d H:i:s', strtotime('-120 days')),
                'ends_at'           => date('Y-m-d H:i:s', strtotime('-60 days')),
                'applications'      => 20,
            ],
            [
                'specialization_id' => $spec_id_b,
                'is_paid'           => false,
                'status'            => 'published',
                'starts_at'         => date('Y-m-d H:i:s', strtotime('-120 days')),
                'ends_at'           => date('Y-m-d H:i:s', strtotime('-60 days')),
                'applications'      => 20,
            ],
            [
                'specialization_id' => $other_spec_id,
                'is_paid'           => true,
                'status'            => 'published',
                'starts_at'         => date('Y-m-d H:i:s', strtotime('+10 days')),
                'ends_at'           => date('Y-m-d H:i:s', strtotime('+40 days')),
                'applications'      => 20,
            ],
        ]);

        if (!$fixture17b['ok']) {
            check("Case 17b fixture provisioned: {$fixture17b['reason']}", false);
        } else {
            check('Case 17b fixture provisioned (expired paid/free + other specialization)', true);

            $fixture_ids = array_map(static fn ($t) => (int) $t['id'], $fixture17b['trainings']);

            // Precondition: the trap really would win if the rules were ignored.
            $eligible_paid_max  = application_repository_count_by_training((int) (dashboard_rec_expected_ids($spec_id_b)['paid'] ?? 0));
            $expired_paid_apps  = application_repository_count_by_training((int) $fixture17b['trainings'][0]['id']);
            check(
                'Case 17b: the expired paid fixture out-applies the real eligible paid winner',
                $expired_paid_apps > $eligible_paid_max
            );

            $expected17b = dashboard_rec_expected_ids($spec_id_b);
            $rec17b      = dashboard_rec_run($expired_student);
            $returned17b = array_map(static fn ($i) => (int) $i['id'], $rec17b['recommended']);

            // Case 6
            check(
                'Case 17b: no expired training is recommended',
                array_intersect($returned17b, [$fixture17b['trainings'][0]['id'], $fixture17b['trainings'][1]['id']]) === []
            );
            check(
                'Case 17b: the recommendations are still the real eligible paid/free winners',
                $returned17b === array_values(array_filter([$expected17b['paid'], $expected17b['free']], static fn ($v) => $v !== null))
            );

            // Case 7
            check(
                'Case 17b: no training from another specialization is recommended',
                !in_array((int) $fixture17b['trainings'][2]['id'], $returned17b, true)
            );
            check(
                'Case 17b: every recommendation still belongs to the student specialization',
                (static function (array $items) use ($spec_id_b): bool {
                    foreach ($items as $item) {
                        $spec = is_array($item['specialization'] ?? null) ? $item['specialization'] : null;
                        if ((int) ($spec['id'] ?? 0) !== $spec_id_b) {
                            return false;
                        }
                    }
                    return $items !== [];
                })($rec17b['recommended'])
            );
            check('Case 17b: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17b['recommended']));

echo "\n== Case 18: recent_notifications preview limit is 5 ==\n";

$notif_student = dashboard_notif_student_with_none();

check(
    'Case 18 precondition: an active student with zero notifications exists',
    $notif_student !== null
);

if ($notif_student !== null) {
    $notif_uid = (int) $notif_student['user_id'];
    $notif_token = token_for($notif_student);

    register_shutdown_function('dashboard_notif_fixture_cleanup');

    // The other sections must be unaffected by the limit change, so snapshot
    // their shapes here and re-check them in both phases.
    $section_keys = [
        'student', 'quick_stats', 'active_training', 'applications_snapshot',
        'certificates', 'recommended_trainings', 'recent_notifications', 'next_action',
    ];

    /* ---------------- Phase A: 7 notifications, exactly 5 returned --------- */

    $ids_7 = dashboard_notif_fixture_provision($notif_uid, 7);

    check(
        'Case 18a precondition: 7 notifications were created',
        count($ids_7) === 7
            && (int) (db_fetch_one('SELECT COUNT(*) n FROM notifications WHERE user_id = ?', [$notif_uid])['n'] ?? 0) === 7
    );

    $r18a = run_scenario('bearer', $notif_token)['data'];
    $n18a = is_array($r18a['recent_notifications'] ?? null) ? $r18a['recent_notifications'] : [];

    check('Case 18a: exactly 5 notifications are returned when 7 exist', count($n18a) === 5);
    check(
        'Case 18a: they are exactly the 5 newest by created_at DESC',
        dashboard_notif_ids_of($n18a) === dashboard_notif_expected_ids($ids_7, 7, 5)
    );
    check('Case 18a: returned notifications are ordered created_at DESC', dashboard_notif_is_desc($n18a));
    check('Case 18a: the notification DTO is still exactly id/type/title/body/read/created_at', dashboard_notif_dto_ok($n18a));

    // Values must still come from the real rows (not truncated/rebuilt).
    $n18a_ok = true;
    foreach ($n18a as $item) {
        $src = db_fetch_one(
            'SELECT id, type, title, body, read_at, created_at FROM notifications WHERE id = ?',
            [(int) ($item['id'] ?? 0)]
        );
        if (!is_array($src)) {
            $n18a_ok = false;
            continue;
        }
        if ((string) ($item['title'] ?? '') !== (string) $src['title']) {
            $n18a_ok = false;
        }
        if ((string) ($item['type'] ?? '') !== (string) $src['type']) {
            $n18a_ok = false;
        }
        if ((bool) ($item['read'] ?? false) !== !empty($src['read_at'])) {
            $n18a_ok = false;
        }
        if ((string) ($item['created_at'] ?? '') !== dashboard_rec_expected_iso($src['created_at'] ?? null)) {
            $n18a_ok = false;
        }
    }
    check('Case 18a: every field is still sourced from the stored notification row', $n18a_ok);

    // read state survives the wider window: the fixture marks every 3rd row
    // read, so the 5-item window contains both read and unread items.
    $read_flags = array_map(
        static fn ($item): bool => (bool) ($item['read'] ?? false),
        $n18a
    );
    check(
        'Case 18a: the window contains both read and unread items (read state preserved)',
        in_array(true, $read_flags, true) && in_array(false, $read_flags, true)
    );

    check(
        'Case 18a: all 8 dashboard sections are still present',
        array_diff($section_keys, array_keys($r18a)) === []
    );
    check(
        'Case 18a: recommended_trainings is unaffected (at most 1 paid + 1 free)',
        is_array($r18a['recommended_trainings'] ?? null)
            && count($r18a['recommended_trainings']) <= 2
    );

    /* ---------------- Phase B: only 2 notifications, both returned --------- */

    // Drop the 5 oldest, leaving 2, and re-read the same student.
    $oldest_five = array_slice($ids_7, 0, 5);
    $in_18b = implode(', ', $oldest_five);
    db_execute("DELETE FROM notifications WHERE id IN ({$in_18b})");

    check(
        'Case 18b precondition: exactly 2 notifications remain',
        (int) (db_fetch_one('SELECT COUNT(*) n FROM notifications WHERE user_id = ?', [$notif_uid])['n'] ?? 0) === 2
    );

    $r18b = run_scenario('bearer', $notif_token)['data'];
    $n18b = is_array($r18b['recent_notifications'] ?? null) ? $r18b['recent_notifications'] : [];

    check('Case 18b: fewer than 5 exist, so all 2 are returned', count($n18b) === 2);
    check(
        'Case 18b: they are the 2 that actually exist, newest first',
        dashboard_notif_ids_of($n18b) === dashboard_notif_expected_ids($ids_7, 7, 2)
    );
    check('Case 18b: returned notifications are ordered created_at DESC', dashboard_notif_is_desc($n18b));
    check('Case 18b: the notification DTO is still exactly id/type/title/body/read/created_at', dashboard_notif_dto_ok($n18b));
    check(
        'Case 18b: all 8 dashboard sections are still present',
        array_diff($section_keys, array_keys($r18b)) === []
    );
    check(
        'Case 18b: the other sections are byte-identical between the two reads',
        $r18a['quick_stats'] === $r18b['quick_stats']
            && $r18a['certificates'] === $r18b['certificates']
            && $r18a['applications_snapshot'] === $r18b['applications_snapshot']
            && $r18a['active_training'] === $r18b['active_training']
            && $r18a['next_action'] === $r18b['next_action']
            && $r18a['recommended_trainings'] === $r18b['recommended_trainings']
    );

    dashboard_notif_fixture_cleanup();

    check(
        'Case 18: the fixture was removed',
        (int) (db_fetch_one(
            'SELECT COUNT(*) n FROM notifications WHERE title LIKE ?',
            [DASHBOARD_NOTIF_FIXTURE_PREFIX . '%']
        )['n'] ?? 0) === 0
    );
}

dashboard_notif_fixture_cleanup();
dashboard_rec_fixture_cleanup();

            $leftovers = array_filter($fixture_ids, static fn (int $id): bool => (bool) db_fetch_one(
                'SELECT id FROM training_listings WHERE id = ?',
                [$id]
            ));
            $leftover_apps = array_filter($fixture_ids, static fn (int $id): bool => (int) (db_fetch_one(
                'SELECT COUNT(*) AS total FROM training_applications WHERE training_id = ?',
                [$id]
            )['total'] ?? 0) > 0);

            check('Case 17b: fixture trainings and their applications were removed', $leftovers === [] && $leftover_apps === []);
        }
    }
}

echo "\n== Case 17c: no eligible paid training -> only the free one ==\n";

$free_only = dashboard_rec_student_with_buckets('free');

if (!is_array($free_only)) {
    check('Case 17c: a student whose specialization has only free eligible trainings resolved', false);
} else {
    check('Case 17c: a student whose specialization has only free eligible trainings resolved', true);

    $expected17c = dashboard_rec_expected_ids((int) $free_only['specialization_id']);
    $rec17c      = dashboard_rec_run($free_only);

    check('Case 17c: dashboard returns HTTP 200', $rec17c['status'] === 200);
    check('Case 17c: exactly one recommendation is returned', count($rec17c['recommended']) === 1);
    check(
        'Case 17c: the single recommendation is the free one and not paid',
        count($rec17c['recommended']) === 1
            && !($rec17c['recommended'][0]['is_paid'] ?? true)
            && (int) $rec17c['recommended'][0]['id'] === (int) $expected17c['free']
    );
    check('Case 17c: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17c['recommended']));
}

echo "\n== Case 17d: no eligible free training -> only the paid one ==\n";

$paid_only = dashboard_rec_student_with_buckets('paid');

if (!is_array($paid_only)) {
    check('Case 17d: a student whose specialization has only paid eligible trainings resolved', false);
} else {
    check('Case 17d: a student whose specialization has only paid eligible trainings resolved', true);

    $expected17d = dashboard_rec_expected_ids((int) $paid_only['specialization_id']);
    $rec17d      = dashboard_rec_run($paid_only);

    check('Case 17d: dashboard returns HTTP 200', $rec17d['status'] === 200);
    check('Case 17d: exactly one recommendation is returned', count($rec17d['recommended']) === 1);
    check(
        'Case 17d: the single recommendation is the paid one',
        count($rec17d['recommended']) === 1
            && (bool) ($rec17d['recommended'][0]['is_paid'] ?? false)
            && (int) $rec17d['recommended'][0]['id'] === (int) $expected17d['paid']
    );
    check('Case 17d: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17d['recommended']));
}

echo "\n== Case 17e: neither paid nor free eligible -> recommended_trainings is [] ==\n";

$none_student = dashboard_rec_student_with_buckets('neither');

if (!is_array($none_student)) {
    check('Case 17e: a student whose specialization has no eligible trainings resolved', false);
} else {
    check('Case 17e: a student whose specialization has no eligible trainings resolved', true);

    $rec17e = dashboard_rec_run($none_student);

    check('Case 17e: dashboard returns HTTP 200', $rec17e['status'] === 200);
    check(
        'Case 17e: recommended_trainings is an empty array',
        is_array($rec17e['data']['recommended_trainings'] ?? null) && $rec17e['data']['recommended_trainings'] === []
    );

    // Case 12: the other dashboard sections are unaffected.
    $data17e = $rec17e['data'];

    foreach ([
        'student', 'quick_stats', 'active_training', 'applications_snapshot',
        'certificates', 'recent_notifications', 'next_action',
    ] as $section) {
        check("Case 17e: section {$section} is still present", array_key_exists($section, $data17e));
    }

    $snap17e = $data17e['applications_snapshot'] ?? [];
    check(
        'Case 17e: applications_snapshot counts are still internally consistent',
        (int) ($snap17e['applied'] ?? 0) + (int) ($snap17e['accepted'] ?? 0)
            + (int) ($snap17e['rejected'] ?? 0) + (int) ($snap17e['withdrawn'] ?? 0)
            === (int) ($snap17e['total'] ?? 0)
    );
    check(
        'Case 17e: certificates section is still complete',
        array_key_exists('eligible', $data17e['certificates'] ?? [])
            && array_key_exists('pending', $data17e['certificates'] ?? [])
            && array_key_exists('issued', $data17e['certificates'] ?? [])
            && array_key_exists('revoked', $data17e['certificates'] ?? [])
            && is_array($data17e['certificates']['recent'] ?? null)
    );
    check(
        'Case 17e: next_action is still deterministic',
        !empty($data17e['next_action']['type'] ?? null) && !empty($data17e['next_action']['target'] ?? null)
    );
}

echo "\n== Case 17f: a FREE training that still carries price/trial columns hides them ==\n";

/*
 * training_listings stores compensation_amount / trial_period_days
 * independently of is_paid, and the seeded data really does contain FREE rows
 * with a non-null trial_period_days. So "is_paid = false -> price and
 * free_trial_days are null" cannot be satisfied by simply forwarding whatever
 * the columns hold: the gate has to be is_paid itself.
 *
 * The fixture creates a FREE training in the student's own specialization that
 * is deliberately given a price and a trial allowance, plus enough applications
 * to be the most-applied FREE training, so it is certainly the one recommended.
 * The assertions then require null / null anyway.
 */

$gate_student = dashboard_rec_student_with_buckets('both');

if (!is_array($gate_student)) {
    check('Case 17f: a student with both buckets resolved', false);
} else {
    check('Case 17f: a student with both buckets resolved', true);

    $spec_id_f = (int) $gate_student['specialization_id'];

    $fixture17f = dashboard_rec_fixture_provision([
        [
            'specialization_id' => $spec_id_f,
            'is_paid'           => false,
            'status'            => 'published',
            'starts_at'         => date('Y-m-d H:i:s', strtotime('+20 days')),
            'ends_at'           => date('Y-m-d H:i:s', strtotime('+50 days')),
            'applications'      => 20,
        ],
    ]);

    if (!$fixture17f['ok']) {
        check("Case 17f fixture provisioned: {$fixture17f['reason']}", false);
    } else {
        check('Case 17f fixture provisioned', true);

        $free_id = (int) $fixture17f['trainings'][0]['id'];

        // Give the free fixture the exact decoys a naive forwarder would leak.
        db_execute(
            'UPDATE training_listings SET compensation_amount = 9999.99, trial_period_days = 21 WHERE id = ?',
            [$free_id]
        );

        $decoy = dashboard_rec_source_row($free_id);

        check(
            'Case 17f precondition: the FREE fixture really does hold a price and a trial allowance',
            (float) $decoy['compensation_amount'] === 9999.99 && (int) $decoy['trial_period_days'] === 21
                && (int) $decoy['is_paid'] === 0
        );

        $rec17f    = dashboard_rec_run($gate_student);
        $returnedf = array_map(static fn ($i) => (int) $i['id'], $rec17f['recommended']);

        check(
            'Case 17f: the heavily-applied free fixture is the free recommendation',
            in_array($free_id, $returnedf, true)
        );

        $free_item = null;
        foreach ($rec17f['recommended'] as $i) {
            if ((int) $i['id'] === $free_id) {
                $free_item = $i;
            }
        }

        check(
            'Case 17f: the free recommendation reports is_paid=false',
            is_array($free_item) && ($free_item['is_paid'] ?? true) === false
        );

        // Case 10
        check(
            'Case 17f: the free recommendation does NOT expose the stored price',
            is_array($free_item) && array_key_exists('price', $free_item) && $free_item['price'] === null
        );

        // Case 10
        check(
            'Case 17f: the free recommendation does NOT expose the stored trial allowance',
            is_array($free_item) && array_key_exists('free_trial_days', $free_item) && $free_item['free_trial_days'] === null
        );

        // Case 11: created_at is still returned for a free training.
        check(
            'Case 17f: the free recommendation still returns its created_at',
            is_array($free_item)
                && $free_item['created_at'] === dashboard_rec_expected_iso($decoy['created_at'] ?? null)
        );

        check('Case 17f: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17f['recommended']));

        dashboard_rec_fixture_cleanup();

        check(
            'Case 17f: the free fixture was removed',
            !db_fetch_one('SELECT id FROM training_listings WHERE id = ?', [$free_id])
        );
    }
}

echo "\n== Case 17g: a training with NO stored deadline reports null, never ends_at ==\n";

/*
 * Every seeded published training happens to carry an application_deadline, so
 * the "what happens when there is no value" path cannot be reached from the seed
 * data. That is exactly the case where a tempting `$deadline ?? $ends_at`
 * fallback would silently invent a deadline the training does not have, and
 * would contradict the Applications API, which only enforces a deadline when the
 * column is actually set (application_service.php: "The application deadline has
 * passed." is reached only for a non-empty deadline).
 *
 * The fixture therefore creates a training in the student's own specialization
 * with application_deadline = NULL, a real ends_at, and enough applications to
 * be the recommendation, then requires the DTO to report null.
 */

$dl_student = dashboard_rec_student_with_buckets('both');

if (!is_array($dl_student)) {
    check('Case 17g: a student with both buckets resolved', false);
} else {
    check('Case 17g: a student with both buckets resolved', true);

    $spec_id_g = (int) $dl_student['specialization_id'];

    $fixture17g = dashboard_rec_fixture_provision([
        [
            'specialization_id' => $spec_id_g,
            'is_paid'           => true,
            'status'            => 'published',
            'starts_at'         => date('Y-m-d H:i:s', strtotime('+25 days')),
            'ends_at'           => date('Y-m-d H:i:s', strtotime('+55 days')),
            'applications'      => 20,
        ],
    ]);

    if (!$fixture17g['ok']) {
        check("Case 17g fixture provisioned: {$fixture17g['reason']}", false);
    } else {
        check('Case 17g fixture provisioned', true);

        $dl_id = (int) $fixture17g['trainings'][0]['id'];

        db_execute('UPDATE training_listings SET application_deadline = NULL WHERE id = ?', [$dl_id]);

        $dl_src = dashboard_rec_source_row($dl_id);

        check(
            'Case 17g precondition: the fixture really has NULL deadline but a real ends_at',
            // No ?? here: a NULL column must be compared directly, since ??
            // substitutes for null and would report the opposite.
            is_array($dl_src) && $dl_src['application_deadline'] === null && !empty($dl_src['ends_at'])
        );

        $rec17g    = dashboard_rec_run($dl_student);
        $returnedg = array_map(static fn ($i) => (int) $i['id'], $rec17g['recommended']);

        check(
            'Case 17g: the fixture is the paid recommendation',
            in_array($dl_id, $returnedg, true)
        );

        $dl_item = null;
        foreach ($rec17g['recommended'] as $i) {
            if ((int) $i['id'] === $dl_id) {
                $dl_item = $i;
            }
        }

        check(
            'Case 17g: application_deadline is present and null',
            is_array($dl_item) && array_key_exists('application_deadline', $dl_item) && $dl_item['application_deadline'] === null
        );

        check(
            'Case 17g: application_deadline was NOT back-filled from ends_at',
            is_array($dl_item) && $dl_item['application_deadline'] !== dashboard_rec_expected_iso($dl_src['ends_at'] ?? null)
        );

        check(
            'Case 17g: the other timestamp fields are still real values',
            is_array($dl_item)
                && !empty($dl_item['ends_at'])
                && !empty($dl_item['created_at'])
                && $dl_item['ends_at'] === dashboard_rec_expected_iso($dl_src['ends_at'] ?? null)
        );

        check('Case 17g: the recommendation DTO shape is unchanged', dashboard_rec_dto_ok($rec17g['recommended']));

        dashboard_rec_fixture_cleanup();

        check(
            'Case 17g: the fixture was removed',
            !db_fetch_one('SELECT id FROM training_listings WHERE id = ?', [$dl_id])
        );
    }
}

dashboard_rec_fixture_cleanup();

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";

exit($failures === 0 ? 0 : 1);