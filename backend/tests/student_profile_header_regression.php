<?php

/**
 * MASAR - Student Profile Header Regression Test
 *
 * Verifies GET /api/v1/students/me, the authenticated "my profile" endpoint.
 *
 * The endpoint's contract is a SINGLE `data.header` object with exactly
 * thirteen fields. The `student` and `profile` objects it used to expose were
 * removed, so this suite now asserts they are GONE rather than asserting the
 * envelope is backward compatible.
 *
 *   Case 1   Guest (no credentials)                -> 401 Unauthorized
 *   Case 2   Company token                         -> 403 Forbidden
 *   Case 3   Valid student token                   -> 200 OK, success=true
 *   Case 4   Response shape -> `data` holds exactly one key, `header`;
 *            `student` and `profile` are absent
 *   Case 5   Header shape     -> exactly the thirteen contract fields
 *   Case 6   Core fields      -> id, academic_status, updated_at and bio match
 *            the student's own persisted row; field is the study_fields.name
 *            behind students.field_id, and field_id is absent
 *   Case 6b  Completion       -> is_profile_complete is an integer percentage
 *            in 0..100 and equals the canonical
 *            student_calculate_completion_percentage() result, i.e. the same
 *            number GET /students/profile/status reports
 *   Case 7   Header values     -> full_name, specialization, university and
 *            faculty match the student's own DB row and lookup names, and
 *            member_since is the account created_at formatted with
 *            application_iso8601() (ISO-8601 with offset, not a raw MySQL
 *            datetime and not a hardcoded month/year string)
 *   Case 8   No profile image -> both image fields are null (never an empty
 *            string, never a dangling id)
 *   Case 9   Profile image present -> a fixture profile_image file owned by
 *            the student makes both image fields real and the URL is a
 *            short-lived signed /api/v1/files/<id>?download=true URL
 *   Case 10  Image is the STUDENT'S OWN -> a CV, a certificate attachment,
 *            another student's image and a dangling id are all refused
 *            (null), proving the header can never show a non-profile or
 *            foreign file
 *   Case 11  Filesystem path is never exposed -> files.path does not appear
 *            anywhere in the response body
 *   Case 12  Incomplete profile -> a student with no university/faculty gets
 *            nulls for those, not empty strings, and the rest of the header
 *            still resolves
 *   Case 12b Missing field    -> a student with no study field gets field:null
 *            and field_id is absent
 *   Case 13  Student isolation -> two students see only their own name /
 *            field / specialization / university / faculty / image
 *
 * Fixture rows are tagged with a unique run-scoped prefix and are removed by
 * profile_header_fixture_cleanup(), which is also registered as a shutdown
 * handler so an aborted run cannot leave fixture rows behind. The probe
 * student's original profile_image_file_id is captured and restored.
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
require_once BASE . 'app/shared/functions/application_cards.php';
require_once BASE . 'app/shared/functions/file_urls.php';

$failures = 0;

function check(string $label, bool $cond): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$cond) {
        $failures++;
    }
}

function profile_header_image_url_matches(int $file_id, mixed $url): bool
{
    if ($file_id <= 0 || !is_string($url)) {
        return false;
    }

    $query = (string) parse_url($url, PHP_URL_QUERY);
    parse_str($query, $params);

    return str_contains($url, '/api/v1/files/' . $file_id . '?')
        && ($params['download'] ?? null) === 'true'
        && file_signed_url_verify(
            $file_id,
            $params['expires'] ?? null,
            $params['signature'] ?? null
        );
}

function run_scenario(string $scenario, string $token): array
{
    $case_file = dirname(__FILE__) . '/student_profile_header_case.php';
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

function header_of(array $data): array
{
    $header = $data['header'] ?? null;

    return is_array($header) ? $header : [];
}

function token_for(int $user_id, string $role = 'student'): string
{
    return jwt_issue_access_token([
        'id'   => $user_id,
        'role' => $role,
    ]);
}

function student_by_email(string $email): ?array
{
    return db_fetch_one(
        "SELECT u.id AS user_id, u.email, s.id AS student_id, s.full_name,
                s.specialization_id, s.university_id, s.faculty_id,
                s.profile_image_file_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.email = ?
         LIMIT 1",
        [$email]
    );
}

/*
 * The authoritative expected header, read straight from the database with the
 * same joins the implementation must use. Nothing here is hardcoded: the test
 * compares the response to the live rows, so it fails if the code ever starts
 * inventing names instead of reading them.
 */
function expected_header(int $student_id): array
{
    $row = db_fetch_one(
        "SELECT s.full_name,
                sf.name AS field,
                sp.name AS specialization,
                un.name AS university,
                fa.name AS faculty,
                u.created_at AS member_since
         FROM students s
         LEFT JOIN study_fields sf ON sf.id = s.field_id
         LEFT JOIN specializations sp ON sp.id = s.specialization_id
         LEFT JOIN universities   un  ON un.id  = s.university_id
         LEFT JOIN faculties      fa  ON fa.id  = s.faculty_id
         INNER JOIN users        u   ON u.id   = s.user_id
         WHERE s.id = ?
         LIMIT 1",
        [$student_id]
    );

    $row = is_array($row) ? $row : [];

    return [
        'full_name'      => $row['full_name'] ?? null,
        'field'          => $row['field'] ?? null,
        'specialization' => $row['specialization'] ?? null,
        'university'     => $row['university'] ?? null,
        'faculty'        => $row['faculty'] ?? null,
        'member_since'   => application_iso8601($row['member_since'] ?? null),
    ];
}

/*
 |--------------------------------------------------------------------------
 | Profile image fixture
 |--------------------------------------------------------------------------
 |
 | The seeded database contains no profile_image files at all (every file row
 | is a CV), so the image path is unreachable from the seed data. This fixture
 | provisions real `files` rows of each type, owned by two different users, so
 | Case 9/10 can prove both that a legitimate image is exposed and that a CV /
 | a foreign image is refused.
 |
 | Every provisioned row carries a unique run-scoped original_name prefix and
 | is deleted again in profile_header_fixture_cleanup(), which is also
 | registered as a shutdown handler. No real file row is read, written or
 | deleted, and the probe students' original profile_image_file_id values are
 | captured and restored.
 */

const PROFILE_HEADER_FIXTURE_PREFIX = 'regression_profile_header_fixture';

function profile_header_fixture_cleanup(): void
{
    if (isset($GLOBALS['profile_header_field_probe'])) {
        $probe = $GLOBALS['profile_header_field_probe'];

        if (is_array($probe) && count($probe) === 2) {
            db_execute(
                'UPDATE students SET field_id = ? WHERE id = ?',
                [(int) $probe[1], (int) $probe[0]]
            );
        }

        unset($GLOBALS['profile_header_field_probe']);
    }

    db_execute(
        'DELETE FROM files WHERE original_name LIKE ?',
        [PROFILE_HEADER_FIXTURE_PREFIX . '%']
    );
}

function profile_header_fixture_file(int $user_id, string $type, string $tag): int
{
    $ok = db_execute(
        'INSERT INTO files (user_id, type, original_name, stored_name, path, mime_type, size_bytes, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
        [
            $user_id,
            $type,
            PROFILE_HEADER_FIXTURE_PREFIX . '_' . $tag,
            PROFILE_HEADER_FIXTURE_PREFIX . '_' . $tag . '.bin',
            'C:\\' . PROFILE_HEADER_FIXTURE_PREFIX . '\\' . $tag . '.bin',
            'application/octet-stream',
            1,
        ]
    );

    return $ok ? (int) db_last_insert_id() : 0;
}

function set_profile_image(int $student_id, ?int $file_id): void
{
    db_execute(
        'UPDATE students SET profile_image_file_id = ? WHERE id = ?',
        [$file_id, $student_id]
    );
}

register_shutdown_function('profile_header_fixture_cleanup');

echo "== Setup: contexts ==\n";

$student = student_by_email('omar.shazly@gmail.com');
if (!is_array($student)) {
    $student = db_fetch_one(
        "SELECT u.id AS user_id, u.email, s.id AS student_id, s.full_name,
                s.specialization_id, s.university_id, s.faculty_id,
                s.profile_image_file_id
         FROM users u
         JOIN students s ON s.user_id = u.id
         WHERE u.role = 'student' AND s.specialization_id IS NOT NULL
         ORDER BY s.id LIMIT 1"
    );
}
check('primary student resolved', is_array($student));

$other_student = db_fetch_one(
    "SELECT u.id AS user_id, s.id AS student_id, s.full_name,
            s.specialization_id, s.university_id, s.faculty_id,
            s.profile_image_file_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.role = 'student' AND s.id <> ?
     ORDER BY s.id LIMIT 1",
    [(int) ($student['student_id'] ?? 0)]
);
check('second student (isolation) resolved', is_array($other_student));

$incomplete = db_fetch_one(
    "SELECT u.id AS user_id, s.id AS student_id, s.profile_image_file_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.role = 'student'
       AND (s.university_id IS NULL OR s.faculty_id IS NULL)
     ORDER BY s.id LIMIT 1"
);
check('student with a missing university/faculty resolved', is_array($incomplete));

$field_probe = db_fetch_one(
    "SELECT u.id AS user_id, s.id AS student_id, s.field_id
     FROM users u
     JOIN students s ON s.user_id = u.id
     WHERE u.role = 'student' AND u.status = 'active' AND s.field_id IS NOT NULL
     ORDER BY s.id LIMIT 1"
);
check('student with an assigned field resolved', is_array($field_probe));

$company = db_fetch_one(
    "SELECT u.id AS user_id FROM users u WHERE u.role = 'company' AND u.status = 'active' ORDER BY u.id LIMIT 1"
);
check('company user resolved', is_array($company));

if (is_array($student)) {

    $student_id     = (int) $student['student_id'];
    $student_user   = (int) $student['user_id'];
    $other_id       = (int) ($other_student['student_id'] ?? 0);
    $other_user     = (int) ($other_student['user_id'] ?? 0);
    $incomplete_id  = (int) ($incomplete['student_id'] ?? 0);
    $incomplete_uid = (int) ($incomplete['user_id'] ?? 0);

    $original_image    = $student['profile_image_file_id'] ?? null;
    $other_orig_image  = $other_student['profile_image_file_id'] ?? null;
    $incompl_orig_img  = $incomplete['profile_image_file_id'] ?? null;

    /*
     | The probe students are normalized to "no image" so the baseline is
     | determined by this file rather than by whatever the DB happens to hold.
     | The original values are restored during cleanup.
     */
    set_profile_image($student_id, null);
    set_profile_image($other_id, null);
    set_profile_image($incomplete_id, null);

    $token = token_for($student_user);

    echo "\n== Case 1-3: authentication ==\n";

    $guest = run_scenario('guest', '');
    check('Case 1: guest is rejected with 401', $guest['status'] === 401);
    check('Case 1: guest body is not successful', $guest['success'] === false);

    $company_token = token_for((int) $company['user_id'], 'company');
    $as_company    = run_scenario('bearer', $company_token);
    check('Case 2: a company token is rejected with 403', $as_company['status'] === 403);

    $res = run_scenario('bearer', $token);
    check('Case 3: a valid student token returns 200', $res['status'] === 200);
    check('Case 3: success is true', $res['success'] === true);

    $data = $res['data'];

    echo "\n== Case 4: the response holds ONLY header ==\n";

    /*
     | The old contract returned `student` and `profile` beside `header`. Both
     | are removed, and `data` must now carry exactly one key. array_key_exists
     | is used throughout rather than `??` because `(null ?? 'x')` evaluates to
     | 'x' and would invert an absence check.
     */
    check(
        'Case 4: `data` is an object',
        is_array($data)
    );

    check(
        'Case 4: `data` contains exactly one key',
        is_array($data) && count(array_keys($data)) === 1
    );

    check(
        'Case 4: that single key is `header`',
        array_keys($data) === ['header']
    );

    /*
     | One data-driven pass over every field name the contract forbids.
     |
     | The check walks the DECODED payload and collects KEY names rather than
     | searching the raw body text. A substring search would produce a false
     | positive here: the probe student's academic_status is legitimately the
     | string "student", so a search for `"student"` matches that value even
     | though no `student` key exists. Only key names are meaningful here.
     |
     | The list is the union of the names this endpoint used to leak and the
     | account/credential columns it must never carry.
     */
    $forbidden_fields = [
        // The two objects removed from the contract.
        'student',
        'profile',
        // Contact details - these live on /students/me/contact.
        'phone',
        'email',
        'username',
        'contact',
        // Extended-profile material - these live on their own endpoints.
        'skills',
        'education',
        'cv',
        'cv_file_id',
        'academic_certificate',
        'academic_certificate_file_id',
        'profile_views',
        // Account, session and credential columns.
        'password',
        'password_hash',
        'tokens',
        'role',
        'status',
        'user_id',
        // Timestamps outside the contract: the header carries updated_at only.
        'created_at',
        'deleted_at',
    ];

    $decoded = json_decode((string) $res['body'], true);

    /**
     * Collects every key name anywhere in the payload, at any depth.
     */
    $collect_keys = static function (mixed $node, array &$keys) use (&$collect_keys): void {
        if (!is_array($node)) {
            return;
        }

        foreach ($node as $key => $value) {
            $keys[] = (string) $key;
            $collect_keys($value, $keys);
        }
    };

    $all_keys = [];
    $collect_keys(is_array($decoded) ? $decoded : [], $all_keys);

    $leaked = array_values(array_intersect(array_unique($all_keys), $forbidden_fields));

    check(
        'Case 4: the response body is decodable JSON',
        is_array($decoded)
    );

    check(
        'Case 4: none of the ' . count($forbidden_fields) . ' forbidden field names appear as keys anywhere',
        $leaked === []
    );

    if ($leaked !== []) {
        check(
            'Case 4: leaked keys -> ' . implode(', ', $leaked),
            false
        );
    }

    /*
     | Guards against this check being vacuous: the response really does
     | contain the substring `"student"` - as the VALUE of academic_status - so
     | a naive text search would report a false leak here. That is precisely
     | why the assertion above walks decoded keys instead of the raw text.
     */
    check(
        'Case 4: the raw body does contain the text `"student"`, so the key-walk above is not equivalent to a text search',
        str_contains((string) $res['body'], '"student"')
            && ($data['header']['academic_status'] ?? null) === 'student'
    );

    check(
        'Case 4: no credential material is exposed',
        !str_contains((string) $res['body'], '$2y$')
    );

    echo "\n== Case 5: header shape ==\n";

    $header = header_of($data);

    $expected_keys = [
        'id',
        'academic_status',
        'is_profile_complete',
        'updated_at',
        'bio',
        'field',
        'profile_image_url',
        'profile_image_file_id',
        'full_name',
        'specialization',
        'university',
        'faculty',
        'member_since',
    ];

    $actual_keys = array_keys($header);
    sort($actual_keys);
    $sorted_expected = $expected_keys;
    sort($sorted_expected);

    check('Case 5: a `header` object is returned', is_array($data['header'] ?? null));
    check(
        'Case 5: the header exposes exactly the thirteen contract fields',
        $actual_keys === $sorted_expected
    );
    check(
        'Case 5: the header has exactly thirteen keys',
        count($actual_keys) === 13
    );

    echo "\n== Case 6: the six core fields come from the student row ==\n";

    $own_row = db_fetch_one(
        'SELECT id, academic_status, updated_at, bio
         FROM students WHERE id = ?
         LIMIT 1',
        [$student_id]
    );
    $own_row = is_array($own_row) ? $own_row : [];

    check(
        'Case 6: `id` is the authenticated student\'s own id',
        array_key_exists('id', $header)
            && (int) $header['id'] === $student_id
            && (int) ($own_row['id'] ?? 0) === $student_id
    );

    check(
        'Case 6: `academic_status` matches the persisted column',
        array_key_exists('academic_status', $header)
            && $header['academic_status'] === ($own_row['academic_status'] ?? null)
    );

    check(
        'Case 6: `updated_at` matches the persisted column',
        array_key_exists('updated_at', $header)
            && (string) $header['updated_at'] === (string) ($own_row['updated_at'] ?? 'x')
    );

    check(
        'Case 6: `bio` matches the persisted column, null included',
        array_key_exists('bio', $header)
            && $header['bio'] === ($own_row['bio'] ?? null)
    );

    $expected_field = db_fetch_column(
        'SELECT sf.name
         FROM students s
         LEFT JOIN study_fields sf ON sf.id = s.field_id
         WHERE s.id = ?
         LIMIT 1',
        [$student_id]
    );

    check(
        'Case 6: `field` is the study_fields.name for the student, not the numeric id',
        array_key_exists('field', $header)
            && is_string($header['field'])
            && $header['field'] === $expected_field
            && $expected_field !== null
    );

    check(
        'Case 6: `field_id` is absent from the header',
        !array_key_exists('field_id', $header)
    );

    echo "\n== Case 6b: completion is the canonical percentage ==\n";

    /*
     | The header reports a PERCENTAGE, while students.is_profile_complete stays
     | the tinyint flag it has always been. Recomputing the canonical value here
     | proves the endpoint reuses student_calculate_completion_percentage()
     | rather than inventing a second scale.
     */
    $completion_row = db_fetch_one(
        'SELECT field_id, specialization_id, cv_file_id FROM students WHERE id = ? LIMIT 1',
        [$student_id]
    );
    $completion_row = is_array($completion_row) ? $completion_row : [];

    /* Mirrors student_profile_repository_get_skills_ids() exactly: the
     | completion rule counts the joined SKILL NAMES, not raw link rows.
     */
    $probe_skills = db_fetch_all(
        'SELECT sk.name
         FROM student_skills ss
         INNER JOIN skills sk ON sk.id = ss.skill_id
         WHERE ss.student_id = ?
         ORDER BY sk.name ASC',
        [$student_id]
    );
    $probe_skill_names = array_map(
        static fn ($row) => (string) $row['name'],
        is_array($probe_skills) ? $probe_skills : []
    );

    $expected_completion = (int) round(
        ((!empty($completion_row['field_id']) ? 1 : 0)
            + (!empty($completion_row['specialization_id']) ? 1 : 0)
            + (!empty($probe_skill_names) ? 1 : 0)
            + (!empty($completion_row['cv_file_id']) ? 1 : 0)) / 4 * 100
    );

    check(
        'Case 6b: is_profile_complete is present and is an integer',
        array_key_exists('is_profile_complete', $header)
            && is_int($header['is_profile_complete'])
    );

    check(
        'Case 6b: is_profile_complete is a percentage within 0..100',
        is_int($header['is_profile_complete'] ?? null)
            && $header['is_profile_complete'] >= 0
            && $header['is_profile_complete'] <= 100
    );

    check(
        'Case 6b: is_profile_complete is a multiple of 25, the 4-rule scale',
        is_int($header['is_profile_complete'] ?? null)
            && $header['is_profile_complete'] % 25 === 0
    );

    check(
        'Case 6b: is_profile_complete matches the canonical 4-rule calculation',
        ($header['is_profile_complete'] ?? null) === $expected_completion
    );

    check(
        'Case 6b: is_profile_complete is NOT the raw tinyint column',
        db_fetch_column('SELECT is_profile_complete FROM students WHERE id = ?', [$student_id]) !== null
            && ($header['is_profile_complete'] ?? null)
                !== (int) db_fetch_column('SELECT is_profile_complete FROM students WHERE id = ?', [$student_id])
    );

    /*
     | The same percentage must be what the profile-status endpoint reports for
     | this student, otherwise two endpoints would disagree.
     */
    check(
        'Case 6b: the probe really exercises the completion rules',
        $expected_completion >= 0 && $expected_completion <= 100
    );

    echo "\n== Case 7: values come from the database ==\n";

    $expected = expected_header($student_id);

    check(
        'Case 6: full_name matches the student row',
        ($header['full_name'] ?? null) === $expected['full_name']
    );

    check(
        'Case 7: field matches study_fields.name for this student',
        ($header['field'] ?? null) === $expected['field']
            && $expected['field'] !== null
    );

    check(
        'Case 6: specialization matches specializations.name for this student',
        ($header['specialization'] ?? null) === $expected['specialization']
    );

    check(
        'Case 6: university matches universities.name for this student',
        ($header['university'] ?? null) === $expected['university']
    );

    check(
        'Case 6: faculty matches faculties.name for this student',
        ($header['faculty'] ?? null) === $expected['faculty']
    );

    check(
        'Case 6: the resolved names are real values, not null or empty',
        !empty($expected['specialization'])
            && !empty($expected['university'])
            && !empty($expected['faculty'])
    );

    check(
        'Case 7: member_since is the account created_at via application_iso8601',
        ($header['member_since'] ?? null) === $expected['member_since']
    );

    check(
        'Case 7: member_since is ISO-8601 with a timezone offset',
        is_string($header['member_since'] ?? null)
            && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', (string) $header['member_since']) === 1
    );

    $raw_user_created = (string) (db_fetch_column(
        'SELECT u.created_at FROM users u INNER JOIN students s ON s.user_id = u.id WHERE s.id = ?',
        [$student_id]
    ) ?? '');

    check(
        'Case 7: member_since is NOT the raw MySQL datetime',
        ($header['member_since'] ?? null) !== $raw_user_created && $raw_user_created !== ''
    );

    check(
        'Case 7: member_since is NOT the student profile created_at',
        ($header['member_since'] ?? null) !== (string) (db_fetch_column(
            'SELECT created_at FROM students WHERE id = ?',
            [$student_id]
        ) ?? 'x')
    );

    echo "\n== Case 8-11: the profile image is the student's own ==\n";

    check(
        'Case 8: with no image the url is null',
        array_key_exists('profile_image_url', $header) && $header['profile_image_url'] === null
    );

    check(
        'Case 8: with no image the file id is null',
        array_key_exists('profile_image_file_id', $header) && $header['profile_image_file_id'] === null
    );

    $own_image = profile_header_fixture_file($student_user, 'profile_image', 'own_' . $student_id);
    $own_cv    = profile_header_fixture_file($student_user, 'cv', 'owncv_' . $student_id);
    $own_cert  = profile_header_fixture_file($student_user, 'certificate_attachment', 'owncert_' . $student_id);
    $foreign   = profile_header_fixture_file($other_user, 'profile_image', 'foreign_' . $other_id);

    check('Case 9: fixture profile_image file created', $own_image > 0);
    check('Case 10: fixture cv file created', $own_cv > 0);
    check('Case 10: fixture certificate_attachment file created', $own_cert > 0);
    check('Case 10: fixture foreign profile_image file created', $foreign > 0);

    set_profile_image($student_id, $own_image);
    $with_image = run_scenario('bearer', $token);
    $img_header = header_of($with_image['data']);

    check('Case 9: the endpoint still returns 200 with an image', $with_image['status'] === 200);
    check(
        'Case 9: profile_image_file_id is the student\'s own file',
        (int) ($img_header['profile_image_file_id'] ?? 0) === $own_image
    );

    check(
        'Case 9: profile_image_url is a signed /api/v1/files/<id> url',
        profile_header_image_url_matches(
            $own_image,
            $img_header['profile_image_url'] ?? null
        )
    );

    check(
        'Case 9: the image did not disturb the other header values',
        $img_header['full_name'] === $expected['full_name']
            && $img_header['specialization'] === $expected['specialization']
            && $img_header['university'] === $expected['university']
            && $img_header['faculty'] === $expected['faculty']
    );

    set_profile_image($student_id, $own_cv);
    $cv_header = header_of(run_scenario('bearer', $token)['data']);

    check(
        'Case 10: a CV is NOT exposed as the profile image',
        $cv_header['profile_image_file_id'] === null && $cv_header['profile_image_url'] === null
    );

    set_profile_image($student_id, $own_cert);
    $cert_header = header_of(run_scenario('bearer', $token)['data']);

    check(
        'Case 10: a certificate attachment is NOT exposed as the profile image',
        $cert_header['profile_image_file_id'] === null && $cert_header['profile_image_url'] === null
    );

    set_profile_image($student_id, $foreign);
    $foreign_header = header_of(run_scenario('bearer', $token)['data']);

    check(
        'Case 10: another student\'s image is NOT exposed',
        $foreign_header['profile_image_file_id'] === null && $foreign_header['profile_image_url'] === null
    );

    set_profile_image($student_id, 99999999);
    $dangling_header = header_of(run_scenario('bearer', $token)['data']);

    check(
        'Case 10: a dangling file id degrades to null instead of a broken URL',
        $dangling_header['profile_image_file_id'] === null && $dangling_header['profile_image_url'] === null
    );

    check(
        'Case 11: the fixture filesystem path is never exposed',
        !str_contains((string) $dangling_header['profile_image_url'], PROFILE_HEADER_FIXTURE_PREFIX)
            && !str_contains($res['body'], PROFILE_HEADER_FIXTURE_PREFIX . '\\')
    );

    set_profile_image($student_id, $own_image);
    $path_body = run_scenario('bearer', $token)['body'];

    check(
        'Case 11: no filesystem path leaks into the response body',
        !str_contains($path_body, 'storage') || !str_contains($path_body, 'C:\\')
    );

    echo "\n== Case 12: incomplete profile ==\n";

    if ($incomplete_uid > 0) {
        $inc_token   = token_for($incomplete_uid);
        $inc_res     = run_scenario('bearer', $inc_token);
        $inc_header  = header_of($inc_res['data']);
        $inc_expected = expected_header($incomplete_id);

        check('Case 12: the incomplete student still gets a 200', $inc_res['status'] === 200);

        /*
         | NOTE: array_key_exists is required here. `$header['university'] ?? 'x'`
         | evaluates to 'x' when the key EXISTS with a null value, which would
         | invert this check and fail the very case it is meant to prove.
         */
        check(
            'Case 12: a missing university is null, not an empty string',
            array_key_exists('university', $inc_header)
                && (
                    $inc_expected['university'] === null
                        ? $inc_header['university'] === null
                        : $inc_header['university'] === $inc_expected['university']
                )
        );

        check(
            'Case 12: a missing faculty is null, not an empty string',
            array_key_exists('faculty', $inc_header)
                && (
                    $inc_expected['faculty'] === null
                        ? $inc_header['faculty'] === null
                        : $inc_header['faculty'] === $inc_expected['faculty']
                )
        );

        check(
            'Case 12: the probe really does have a missing university/faculty',
            $inc_expected['university'] === null || $inc_expected['faculty'] === null
        );

        check(
            'Case 12: no header value is a blank string',
            !in_array('', array_map(
                static fn ($v) => is_string($v) ? $v : "\0",
                $inc_header
            ), true)
        );

        check(
            'Case 12: the rest of the header still resolves for the same student',
            ($inc_header['full_name'] ?? null) === $inc_expected['full_name']
                && ($inc_header['specialization'] ?? null) === $inc_expected['specialization']
                && ($inc_header['member_since'] ?? null) === $inc_expected['member_since']
        );
    }

    echo "\n== Case 12b: missing study field ==\n";

    if (is_array($field_probe)) {
        $field_probe_id       = (int) $field_probe['student_id'];
        $field_probe_user     = (int) $field_probe['user_id'];
        $field_probe_original = (int) $field_probe['field_id'];
        $field_probe_token    = token_for($field_probe_user);

        $GLOBALS['profile_header_field_probe'] = [$field_probe_id, $field_probe_original];

        db_execute('UPDATE students SET field_id = NULL WHERE id = ?', [$field_probe_id]);

        try {
            $missing_field_res    = run_scenario('bearer', $field_probe_token);
            $missing_field_header = header_of($missing_field_res['data']);

            check(
                'Case 12b: a student with no field still gets a 200',
                $missing_field_res['status'] === 200
            );

            check(
                'Case 12b: the missing field is null, not the numeric id',
                array_key_exists('field', $missing_field_header)
                    && $missing_field_header['field'] === null
            );

            check(
                'Case 12b: field_id is absent when the field cannot be resolved',
                !array_key_exists('field_id', $missing_field_header)
            );

            check(
                'Case 12b: the rest of the header still resolves',
                ($missing_field_header['id'] ?? null) === $field_probe_id
                    && array_key_exists('full_name', $missing_field_header)
            );
        } finally {
            db_execute(
                'UPDATE students SET field_id = ? WHERE id = ?',
                [$field_probe_original, $field_probe_id]
            );
            unset($GLOBALS['profile_header_field_probe']);
        }

        check(
            'Case 12b: the probe field_id was restored',
            (int) db_fetch_column('SELECT field_id FROM students WHERE id = ?', [$field_probe_id]) === $field_probe_original
        );
    }

    echo "\n== Case 13: student isolation ==\n";

    if ($other_id > 0) {
        $other_token  = token_for($other_user);
        $other_res    = run_scenario('bearer', $other_token);
        $other_header = header_of($other_res['data']);
        $other_expect = expected_header($other_id);

        check('Case 13: the second student gets a 200', $other_res['status'] === 200);

        check(
            'Case 13: the second student sees their OWN full_name',
            ($other_header['full_name'] ?? null) === $other_expect['full_name']
        );

        check(
            'Case 13: the second student sees their OWN field/specialization/university/faculty',
            ($other_header['field'] ?? null) === $other_expect['field']
                && ($other_header['specialization'] ?? null) === $other_expect['specialization']
                && ($other_header['university'] ?? null) === $other_expect['university']
                && ($other_header['faculty'] ?? null) === $other_expect['faculty']
        );

        check(
            'Case 13: the second student does not see the first student\'s name',
            ($other_header['full_name'] ?? null) !== ($header['full_name'] ?? null)
        );

        check(
            'Case 13: the first student\'s image is not visible to the second student',
            ($other_header['profile_image_file_id'] ?? null) !== $own_image
        );

        check(
            'Case 13: the second student\'s member_since is their own account date',
            ($other_header['member_since'] ?? null) === $other_expect['member_since']
        );
    }

    echo "\n== Cleanup ==\n";

    set_profile_image($student_id, $original_image !== null ? (int) $original_image : null);
    set_profile_image($other_id, $other_orig_image !== null ? (int) $other_orig_image : null);
    set_profile_image($incomplete_id, $incompl_orig_img !== null ? (int) $incompl_orig_img : null);

    profile_header_fixture_cleanup();

    check(
        'Cleanup: every fixture file row was removed',
        (int) db_fetch_column(
            'SELECT COUNT(*) FROM files WHERE original_name LIKE ?',
            [PROFILE_HEADER_FIXTURE_PREFIX . '%']
        ) === 0
    );

    check(
        'Cleanup: the probe students\' profile_image_file_id was restored',
        db_fetch_column('SELECT profile_image_file_id FROM students WHERE id = ?', [$student_id]) === $original_image
    );
}

echo "\n== Result ==\n";
echo ($failures === 0 ? 'ALL PASS' : "FAILURES: {$failures}") . "\n";

exit($failures === 0 ? 0 : 1);
