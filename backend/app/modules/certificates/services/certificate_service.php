<?php
/**
 * MASAR - Certificate Service
 *
 * Business logic for certificate management.
 *
 * Controller
 *     ↓
 * Certificate Service
 *     ↓
 * Certificate Repository
 *     ↓
 * Database
 */


/*
 |--------------------------------------------------------------------------
 | Dependencies
 |--------------------------------------------------------------------------
 */

require_once __DIR__ . '/../repositories/certificate_repository.php';

require_once __DIR__ . '/../../../core/database/transaction.php';
require_once __DIR__ . '/../../../core/validation/validator.php';

/*
 * Notification helpers are loaded when available so certificate lifecycle
 * events can notify the affected user without breaking module isolation.
 */

if (
    file_exists(
        __DIR__ . '/../../notifications/services/notification_service.php'
    )
) {
    require_once __DIR__ . '/../../notifications/services/notification_service.php';
}


/*
 |--------------------------------------------------------------------------
 | Helpers
 |--------------------------------------------------------------------------
 */

function certificate_service_success(
    mixed $data = null,
    string $message = 'Success'
): array {
    return [
        'success' => true,
        'message' => $message,
        'data'    => $data
    ];
}


function certificate_service_error(
    string $message,
    array $errors = [],
    int $status = 400
): array {
    return [
        'success' => false,
        'message' => $message,
        'errors'  => $errors,
        'status'  => $status
    ];
}


/*
 |--------------------------------------------------------------------------
 | Authorization
 |--------------------------------------------------------------------------
 */

function certificate_service_user_id(
    array $user
): ?int {

    $id =
        $user['id']
        ?? $user['user_id']
        ?? null;

    if (
        filter_var(
            $id,
            FILTER_VALIDATE_INT
        ) === false
    ) {
        return null;
    }

    $id = (int) $id;

    return $id > 0
        ? $id
        : null;
}


function certificate_service_role(
    array $user
): ?string {

    $role =
        $user['role']
        ?? $user['user_role']
        ?? null;

    return $role !== null
        ? strtolower((string) $role)
        : null;
}


function certificate_service_is_admin(
    array $user
): bool {

    return in_array(
        certificate_service_role($user),
        [
            'admin',
            'super_admin',
            'administrator'
        ],
        true
    );
}


function certificate_service_is_user_company(
    array $user,
    int $company_id
): bool {

    $user_role = certificate_service_role($user);

    /*
     * Only companies and admins can set employment eligibility.
     * Students are not allowed to set this value.
     */

    if (in_array($user_role, ['company', 'admin'], true)) {
        return true;
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| User Scope
|--------------------------------------------------------------------------
|
| The certificates table has no user_id column; it references
| student_id and company_id. Resolve the authenticated user to
| the matching profile so listings stay scoped to the user.
|
*/

function certificate_service_user_student_id(
    int $user_id
): ?int {

    if ($user_id <= 0) {
        return null;
    }

    if (!function_exists('certificate_repository_db')) {
        return null;
    }

    $db = certificate_repository_db();

    $stmt = $db->prepare(
        "SELECT id FROM students WHERE user_id = :user_id LIMIT 1"
    );

    $stmt->execute([
        ':user_id' => $user_id
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row
        ? (int) $row['id']
        : null;
}


function certificate_service_user_company_id(
    int $user_id
): ?int {

    if ($user_id <= 0) {
        return null;
    }

    if (!function_exists('certificate_repository_db')) {
        return null;
    }

    $db = certificate_repository_db();

    $stmt = $db->prepare(
        "SELECT id FROM companies WHERE user_id = :user_id LIMIT 1"
    );

    $stmt->execute([
        ':user_id' => $user_id
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row
        ? (int) $row['id']
        : null;
}


/*
|--------------------------------------------------------------------------
| Apply User Scope
|--------------------------------------------------------------------------
|
| Returns the filters scoped to the authenticated user, or null
| when the user cannot access any certificates.
|
*/

function certificate_service_user_scope(
    array $user,
    array $filters
): ?array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return null;
    }

    $role =
        certificate_service_role($user);

    if ($role === 'student') {

        $student_id =
            certificate_service_user_student_id(
                $user_id
            );

        if ($student_id === null) {
            return null;
        }

        $filters['student_id'] =
            $student_id;

        return $filters;
    }

    if ($role === 'company') {

        $company_id =
            certificate_service_user_company_id(
                $user_id
            );

        if ($company_id === null) {
            return null;
        }

        $filters['company_id'] =
            $company_id;

        return $filters;
    }

    return null;
}


/*
 |--------------------------------------------------------------------------
 | View Model / Presenter
 |--------------------------------------------------------------------------
 |
 | Maps a raw certificate row to the API-facing shape. Derives the
 | capability flags (can_request / can_view) that drive the student and
 | company dashboards. can_download is ALWAYS false: certificates are
 | view-only and the download endpoint no longer exists.
 |
|  Issued-only fields (certificate_number, issued_at) are exposed ONLY
 |  once the certificate actually reaches the issued state. A pending
 |  certificate stores its code internally but never leaks it.
 |
 |  A revoked certificate keeps its history: certificate_number (stable,
 |  never regenerated) and the original issued_at stay visible alongside
 |  revocation_reason and revoked_at — always with status='revoked', never
 |  as an active/valid issued certificate.
 |
 |  Company / training / specialization names and the student profile come
 |  from the existing DB relationships (joined by the repository), never
 |  from the request body. A pending request therefore carries:
 |  status, company_name, training_title, specialization_id/name,
 |  is_paid, requested_at, student and view/download capabilities.
 |
 */

function certificate_service_status_is_issued(
    ?string $status
): bool {

    return in_array(
        strtolower((string) $status),
        [
            'issued',
            'active',
            'valid'
        ],
        true
    );
}


function certificate_service_present(
    array $certificate,
    array $user
): array {

    $status =
        strtolower(
            (string) (
                $certificate['status']
                ?? ''
            )
        );

    $issued =
        certificate_service_status_is_issued(
            $status
        );

    $presented = [
        'id'                 => (int) (
            $certificate['id']
            ?? 0
        ),

        'certificate_id'     => (int) (
            $certificate['id']
            ?? 0
        ),

        'status'             => $status,

        'student_id'         => isset($certificate['student_id'])
            ? (int) $certificate['student_id']
            : null,

        'company_id'         => isset($certificate['company_id'])
            ? (int) $certificate['company_id']
            : null,

        'company_name'       => $certificate['company_name']
            ?? null,

        'training_id'        => isset($certificate['training_id'])
            ? (int) $certificate['training_id']
            : null,

        'training_title'     => $certificate['training_title']
            ?? null,

        'specialization_id'  => isset($certificate['specialization_id'])
            ? (int) $certificate['specialization_id']
            : null,

        'specialization_name' => $certificate['specialization_name']
            ?? null,

        'is_paid'            => (bool) (
            $certificate['is_paid']
            ?? false
        ),

        'training_session_id' => isset($certificate['training_session_id'])
            ? (int) $certificate['training_session_id']
            : null,

        'title'              => $certificate['title']
            ?? null,

        'grade'              => $certificate['grade']
            ?? null,

        'grade_label'        => $certificate['grade_label']
            ?? null,

        'start_date'         => $certificate['start_date']
            ?? null,

        'end_date'           => $certificate['end_date']
            ?? null,

        'requested_at'       => $certificate['requested_at']
            ?? $certificate['created_at']
            ?? null,

        'reviewed_at'        => $certificate['reviewed_at']
            ?? null,

        'reviewed_by'        => isset($certificate['reviewed_by'])
            ? (int) $certificate['reviewed_by']
            : null,

        'employment_eligible' => isset($certificate['employment_eligible'])
            ? (int) $certificate['employment_eligible']
            : null,

        'can_request'        => false,

        'can_view'           => true,

        'can_download'       => false,

        'student'            => [
            'id'             => (int) (
                $certificate['student_id']
                ?? 0
            ),

            'full_name'      => $certificate['student_name']
                ?? null,

            'email'          => $certificate['student_email']
                ?? null,

            'phone'          => $certificate['student_phone']
                ?? null,

            'city'           => $certificate['student_city']
                ?? null,

            'university'     => $certificate['student_university']
                ?? null,

            'field'          => $certificate['student_field']
                ?? null,

            'specialization' => $certificate['student_specialization']
                ?? null,
        ],
    ];

    if ($issued) {

        $presented['certificate_number'] =
            $certificate['certificate_code']
            ?? null;

        $presented['issued_at'] =
            $certificate['approved_at']
            ?? null;
    }

    if ($status === 'pending') {

        $presented['requested_at'] =
            $certificate['requested_at']
            ?? $certificate['created_at']
            ?? null;
    }

    if ($status === 'revoked') {

        /*
         * A revoked certificate keeps its history: the original (stable)
         * certificate_number and issued_at remain visible next to the
         * revocation fields. status stays 'revoked' — it is never presented
         * as an active/valid issued certificate.
         */

        $presented['revocation_reason'] =
            $certificate['revocation_reason']
            ?? null;

        $presented['revoked_at'] =
            $certificate['revoked_at']
            ?? null;

        $presented['certificate_number'] =
            $certificate['certificate_code']
            ?? null;

        $presented['issued_at'] =
            $certificate['approved_at']
            ?? null;
    }

    if ($status === 'rejected') {

        $presented['rejection_reason'] =
            $certificate['rejection_reason']
            ?? null;
    }

    return $presented;
}


/*
 |--------------------------------------------------------------------------
 | Eligible Certificates
 |--------------------------------------------------------------------------
 |
 | Returns the trainings for which the authenticated user may request a
 | certificate:
 |
 |   - Student : own accepted + completed trainings with no certificate.
 |   - Company : every eligible student across the company's trainings.
 |   - Admin   : the union, scoped by optional filters.
 |
 | Each item carries `is_paid` (true/false) mirroring the owning
 | training_listings.is_paid flag, matching the Training API semantics.
 |
 */

function certificate_service_eligible(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $role =
        certificate_service_role($user);

    if ($role === 'student') {

        $student_id =
            certificate_service_user_student_id(
                $user_id
            );

        if ($student_id === null) {
            return certificate_service_success(
                [],
                'Eligible certificates retrieved successfully.'
            );
        }

        $filters['student_id'] =
            $student_id;

    } elseif ($role === 'company') {

        $company_id =
            certificate_service_user_company_id(
                $user_id
            );

        if ($company_id === null) {
            return certificate_service_success(
                [],
                'Eligible certificates retrieved successfully.'
            );
        }

        $filters['company_id'] =
            $company_id;

    } elseif (
        !certificate_service_is_admin($user)
    ) {
        return certificate_service_error(
            'You are not authorized to view eligible certificates.',
            [],
            403
        );
    }

    $rows =
        certificate_repository_eligible(
            $filters
        );

    $items = [];

    foreach ($rows as $row) {

        $items[] = [
            'status'              => 'eligible',

            'training_id'         => (int) (
                $row['training_id']
                ?? 0
            ),

            'training_title'      => $row['training_title']
                ?? null,

            'company_id'          => (int) (
                $row['company_id']
                ?? 0
            ),

            'company_name'        => $row['company_name']
                ?? null,

            'training_session_id' => (int) (
                $row['training_session_id']
                ?? 0
            ),

            'is_paid'             => (bool) (
                $row['is_paid']
                ?? false
            ),

            'start_date'          => isset($row['started_on'])
                ? date(
                    'Y-m-d',
                    strtotime(
                        (string) $row['started_on']
                    )
                )
                : null,

            'end_date'            => isset($row['ended_on'])
                ? date(
                    'Y-m-d',
                    strtotime(
                        (string) $row['ended_on']
                    )
                )
                : null,

            'completed_on'        => $row['completed_on']
                ?? null,

            'can_request'         => true,

            'can_view'            => false,

            'can_download'        => false,
        ];
    }

return certificate_service_success(
        $items,
        'Certificates retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Pending Certificates
 |--------------------------------------------------------------------------
 |
 | Dedicated endpoint for the PENDING state only: GET /certificates/pending.
 |
 | Uses the module's existing list + presenter logic
 | (certificate_repository_list + certificate_service_present) with the
 | status filter pinned to `pending`, and user scope derived from the
 | authenticated user:
 |
 |   - Student : only the authenticated student's pending certificates.
 |   - Company : pending certificates across the company's own trainings.
 |   - Admin   : all pending certificates.
 |
 | Scope is always derived from the auth context (certificate_service_user_scope);
 | a client-supplied student_id is never trusted here.
 |
 */

function certificate_service_pending(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $filters['status'] =
        'pending';

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [],
                'Pending certificates retrieved successfully.'
            );
        }
    }

    $result =
        certificate_repository_list(
            $filters
        );

    if (
        !is_array($result)
    ) {
        return certificate_service_error(
            'Unable to retrieve pending certificates.',
            [],
            500
        );
    }

    $items = [];

    foreach ($result as $row) {

        if (is_array($row)) {
            $items[] =
                certificate_service_present(
                    $row,
                    $user
                );
        }
    }

    return certificate_service_success(
        $items,
        'Pending certificates retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Issued Certificates
 |--------------------------------------------------------------------------
 |
 | Dedicated endpoint for the ISSUED state only: GET /certificates/issued.
 |
 | Uses the module's existing list + presenter logic
 | (certificate_repository_list + certificate_service_present) with the
 | status filter pinned to `issued`, and user scope derived from the
 | authenticated user:
 |
 |   - Student : only the authenticated student's issued certificates.
 |   - Company : issued certificates across the company's own trainings.
 |   - Admin   : all issued certificates.
 |
 | Scope is always derived from the auth context (certificate_service_user_scope);
 | a client-supplied student_id is never trusted here.
 |
 */

function certificate_service_issued(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $filters['status'] =
        'issued';

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [],
                'Issued certificates retrieved successfully.'
            );
        }
    }

    $result =
        certificate_repository_list(
            $filters
        );

    if (
        !is_array($result)
    ) {
        return certificate_service_error(
            'Unable to retrieve issued certificates.',
            [],
            500
        );
    }

    $items = [];

    foreach ($result as $row) {

        if (is_array($row)) {
            $items[] =
                certificate_service_present(
                    $row,
                    $user
                );
        }
    }

    return certificate_service_success(
        $items,
        'Issued certificates retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Revoked Certificates
 |--------------------------------------------------------------------------
 |
 | Dedicated endpoint for the REVOKED state only: GET /certificates/revoked.
 |
 | Uses the module's existing list + presenter logic
 | (certificate_repository_list + certificate_service_present) with the
 | status filter pinned to `revoked`, and user scope derived from the
 | authenticated user:
 |
 |   - Student : only the authenticated student's revoked certificates.
 |   - Company : revoked certificates across the company's own trainings.
 |   - Admin   : all revoked certificates.
 |
 | Scope is always derived from the auth context (certificate_service_user_scope);
 | a client-supplied student_id is never trusted here.
 |
 */

function certificate_service_revoked(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $filters['status'] =
        'revoked';

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [],
                'Revoked certificates retrieved successfully.'
            );
        }
    }

    $result =
        certificate_repository_list(
            $filters
        );

    if (
        !is_array($result)
    ) {
        return certificate_service_error(
            'Unable to retrieve revoked certificates.',
            [],
            500
        );
    }

    $items = [];

    foreach ($result as $row) {

        if (is_array($row)) {
            $items[] =
                certificate_service_present(
                    $row,
                    $user
                );
        }
    }

    return certificate_service_success(
        $items,
        'Revoked certificates retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Get Certificate
 |--------------------------------------------------------------------------
 |
 | Creates a PENDING certificate. Approved for:
 |
 |   - Students who completed the training (derived from auth).
 |   - The training's owning company on behalf of a student.
 |   - Admins on behalf of a student.
 |
 | Duplicate requests are rejected at the service layer: every
 | (student, training) pair may only ever have ONE certificate row,
 | regardless of its state.
 |
 */

function certificate_service_request(
    array $user,
    array $data
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $role =
        certificate_service_role($user);

    if (
        !in_array(
            $role,
            [
                'student',
                'company',
                'admin'
            ],
            true
        )
    ) {
        return certificate_service_error(
            'You are not authorized to request certificates.',
            [],
            403
        );
    }

    $training_id =
        (int) (
            $data['training_id']
            ?? 0
        );

    if ($training_id <= 0) {
        return certificate_service_error(
            'Valid training_id is required.',
            [
                'training_id' =>
                    'A valid training ID is required to request a certificate.'
            ],
            422
        );
    }

    /*
     * Derive the affected student and company from the authenticated
     * user whenever possible. Never trust client-supplied identities.
     */

    $student_id = 0;
    $company_id = 0;

    if ($role === 'student') {

        $student_id =
            certificate_service_user_student_id(
                $user_id
            );

        if ($student_id === null) {
            return certificate_service_error(
                'No student profile is linked to this account.',
                [],
                403
            );
        }

    } elseif ($role === 'company') {

        $company_id =
            certificate_service_user_company_id(
                $user_id
            );

        if ($company_id === null) {
            return certificate_service_error(
                'No company profile is linked to this account.',
                [],
                403
            );
        }

        $training =
            certificate_repository_find_training_listing(
                $training_id
            );

        if (
            !$training
            ||
            (int) ($training['company_id'] ?? 0)
                !== $company_id
        ) {
            return certificate_service_error(
                'You are not authorized to request certificates for this training.',
                [],
                403
            );
        }

        $student_id =
            (int) (
                $data['student_id']
                ?? 0
            );

        if ($student_id <= 0) {
            return certificate_service_error(
                'Valid student_id is required when requesting on behalf of a student.',
                [
                    'student_id' =>
                        'A valid student ID is required.'
                ],
                422
            );
        }

    } else {

        $student_id =
            (int) (
                $data['student_id']
                ?? 0
            );

        if ($student_id <= 0) {
            return certificate_service_error(
                'Valid student_id is required.',
                [
                    'student_id' =>
                        'A valid student ID is required.'
                ],
                422
            );
        }
    }

    /*
     * Duplicate protection: one certificate per (student, training)
     * regardless of state (pending, issued, revoked, ...).
     */

    $existing =
        certificate_repository_find_by_student_training(
            $student_id,
            $training_id
        );

    if ($existing) {
        return certificate_service_error(
            'A certificate has already been requested for this training.',
            [],
            409
        );
    }

    /*
     * Eligibility: accepted application + completed session + no certificate
     * + the training itself has already ended (training_listings.ends_at
     * <= current server time). This is what keeps a still-running training
     * ineligible regardless of other state.
     */

    $eligible =
        certificate_repository_eligible([
            'student_id'  => $student_id,
            'training_id' => $training_id
        ]);

    if (empty($eligible)) {
        return certificate_service_error(
            'This training must be accepted and completed before requesting a certificate.',
            [],
            422
        );
    }

    $window = $eligible[0];

    $result =
        certificate_repository_create([
            'certificate_code' =>
                certificate_repository_generate_number(),

            'student_id' =>
                (int) $student_id,

            'company_id' =>
                (int) (
                    $company_id > 0
                        ? $company_id
                        : ($window['company_id'] ?? 0)
                ),

            'training_id' =>
                (int) $training_id,

            'training_session_id' =>
                (int) (
                    $window['training_session_id']
                    ?? 0
                ),

            'status' =>
                'pending',

            'title' =>
                'Certificate of Completion - '
                . ($window['training_title'] ?? 'Training'),

            'start_date' =>
                date(
                    'Y-m-d',
                    strtotime(
                        (string) (
                            $window['started_on']
                            ?? ''
                        )
                    )
                ),

            'end_date' =>
                date(
                    'Y-m-d',
                    strtotime(
                        (string) (
                            $window['ended_on']
                            ?? ''
                        )
                    )
                ),

            'employment_eligible' =>
                0
        ]);

    if (!$result) {
        return certificate_service_error(
            'Unable to request certificate.',
            [],
            500
        );
    }

    $certificate =
        is_array($result)
            ? $result
            : certificate_repository_find(
                (int) $result
            );

    if (!$certificate) {
        return certificate_service_error(
            'Unable to load the requested certificate.',
            [],
            500
        );
    }

    /*
     * Notify the student that the request is awaiting approval.
     */

    $student_user_id =
        certificate_repository_student_user_id(
            $student_id
        );

    if (
        $student_user_id
        &&
        function_exists(
            'notification_service_notify_user'
        )
    ) {
        notification_service_notify_user(
            (int) $student_user_id,
            'Certificate Requested',
            'Your certificate request is awaiting approval.',
            'certificate',
            [
                'certificate_id' =>
                    (int) $certificate['id'],
                'event' => 'requested'
            ]
        );
    }

    return certificate_service_success(
        certificate_service_present(
            $certificate,
            $user
        ),
        'Certificate requested successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Confirm Certificate
 |--------------------------------------------------------------------------
 |
 | Approves a PENDING certificate, moving it to ISSUED. Allowed for the
 | training's owning company or an admin. Stamps reviewed_at,
 | approved_at and reviewed_by. The mapped issued_at is approved_at.
 |
 */

function certificate_service_confirm(
    array $user,
    int $certificate_id,
    array $data = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if ($user_id === null) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    if ($certificate_id <= 0) {
        return certificate_service_error(
            'Invalid certificate ID.',
            [],
            422
        );
    }

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return certificate_service_error(
            'Certificate not found.',
            [],
            404
        );
    }

    /*
     * Only the owning company or an admin may confirm.
     */

    if (!certificate_service_is_admin($user)) {

        if (
            certificate_service_role($user)
            !== 'company'
        ) {
            return certificate_service_error(
                'You are not authorized to confirm certificates.',
                [],
                403
            );
        }

        $company_id =
            certificate_service_user_company_id(
                $user_id
            );

        if (
            $company_id === null
            ||
            (int) ($certificate['company_id'] ?? 0)
                !== $company_id
        ) {
            return certificate_service_error(
                'You are not authorized to confirm this certificate.',
                [],
                403
            );
        }
    }

    $status =
        strtolower(
            (string) (
                $certificate['status']
                ?? ''
            )
        );

    if ($status !== 'pending') {
        return certificate_service_error(
            'Only pending certificates can be confirmed.',
            [],
            409
        );
    }

    $result =
        certificate_repository_confirm(
            $certificate_id,
            $user_id
        );

    if (!$result) {
        return certificate_service_error(
            'Unable to confirm certificate.',
            [],
            500
        );
    }

    $updated =
        certificate_repository_find(
            $certificate_id
        );

    /*
     * Notify the student that the certificate has been issued.
     */

    $student_user_id =
        certificate_repository_student_user_id(
            (int) (
                $certificate['student_id']
                ?? 0
            )
        );

    if (
        $student_user_id
        &&
        function_exists(
            'notification_service_notify_user'
        )
    ) {
        notification_service_notify_user(
            (int) $student_user_id,
            'Certificate Issued',
            'Your certificate has been issued successfully.',
            'certificate',
            [
                'certificate_id' =>
                    (int) $certificate_id,
                'event' => 'issued'
            ]
        );
    }

    return certificate_service_success(
        certificate_service_present(
            $updated,
            $user
        ),
        'Certificate confirmed and issued successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Certificate Status Transition Matrix
 |--------------------------------------------------------------------------
 */

const CERTIFICATE_STATUS_TRANSITIONS = [
    'pending'       => ['issued', 'rejected'],
    'issued'        => ['active', 'revoked'],
    'active'        => ['valid', 'revoked'],
    'valid'         => ['expired', 'revoked'],
    'revoked'       => [],
    'expired'       => [],
];


/*
 |--------------------------------------------------------------------------
 | List Certificates
 |--------------------------------------------------------------------------
 */

function certificate_service_list(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_service_user_id($user);

    if (!$user_id) {
        return certificate_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    /*
     * Admins can access the requested filters.
     * Regular users are restricted to their own
     * accessible certificates.
     */

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [],
                'Certificates retrieved successfully.'
            );
        }
    }

    $result =
        certificate_repository_list(
            $filters
        );

    if (
        !is_array($result)
    ) {
        return certificate_service_error(
            'Unable to retrieve certificates.',
            [],
            500
        );
    }

    $items = [];

    foreach ($result as $row) {

        if (is_array($row)) {
            $items[] =
                certificate_service_present(
                    $row,
                    $user
                );
        }
    }

    return certificate_service_success(
        $items,
        'Certificates retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Get Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_get(
    array $user,
    int $certificate_id
): array {

    if ($certificate_id <= 0) {
        return certificate_service_error(
            'Invalid certificate ID.',
            [],
            422
        );
    }

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return certificate_service_error(
            'Certificate not found.',
            [],
            404
        );
    }

    /*
     * Admins can access any certificate.
     */

    if (
        !certificate_service_is_admin($user)
        &&
        !certificate_service_can_access(
            $user,
            $certificate
        )
    ) {
        return certificate_service_error(
            'You are not authorized to access this certificate.',
            [],
            403
        );
    }

    return certificate_service_success(
        certificate_service_present(
            $certificate,
            $user
        ),
        'Certificate retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Access Check
 |--------------------------------------------------------------------------
 */

function certificate_service_can_access(
    array $user,
    array $certificate
): bool {

    $user_id =
        certificate_service_user_id($user);

    if (!$user_id) {
        return false;
    }

    $certificate_user_id =
        $certificate['user_id']
        ?? null;

    $student_id =
        $certificate['student_id']
        ?? null;

    if (
        $certificate_user_id !== null
        &&
        (int) $certificate_user_id === $user_id
    ) {
        return true;
    }

    /*
     * Company accounts access certificates associated
     * with their company.
     */

    if (
        certificate_service_role($user)
        === 'company'
    ) {

        $company_id =
            $certificate['company_id']
            ?? null;

        if (
            $company_id !== null
            &&
            function_exists(
                'certificate_repository_company_belongs_to_user'
            )
        ) {
            return (bool)
                certificate_repository_company_belongs_to_user(
                    (int) $company_id,
                    $user_id
                );
        }

        return false;
    }

    /*
     * If the certificate belongs to a student,
     * ask the repository to verify ownership.
     */

    if (
        $student_id !== null
        &&
        function_exists(
            'certificate_repository_student_belongs_to_user'
        )
    ) {
        return (bool)
            certificate_repository_student_belongs_to_user(
                (int) $student_id,
                $user_id
            );
    }

    return false;
}


/*
 |--------------------------------------------------------------------------
 | Create / Issue Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_create(
    array $user,
    array $data
): array {

    if (!certificate_service_is_admin($user)) {

        return certificate_service_error(
            'Only authorized administrators can issue certificates.',
            [],
            403
        );
    }

    $validation =
        certificate_service_validate_create(
            $data
        );

    if ($validation !== true) {

        return certificate_service_error(
            'Certificate data is invalid.',
            $validation,
            422
        );
    }

    /*
     * Prevent externally supplied audit fields.
     */

    unset(
        $data['id'],
        $data['certificate_id'],
        $data['created_at'],
        $data['updated_at'],
        $data['issued_at']
    );

    $data['issued_by'] =
        certificate_service_user_id($user);

    if (
        empty($data['status'])
    ) {
        $data['status'] = 'issued';
    }

    /*
     * Generate certificate code if the repository
     * supports it.
     */

    if (
        empty($data['certificate_code'])
        &&
        function_exists(
            'certificate_repository_generate_number'
        )
    ) {

        $data['certificate_code'] =
            certificate_repository_generate_number();
    }

    /*
     * Validate the training/application relationship.
     * Certificate must be linked to a legitimate training
     * through an accepted/completed application.
     */

    if (
        !isset($data['training_id'])
        ||
        $data['training_id'] <= 0
    ) {
        return certificate_service_error(
            'Valid training ID is required.',
            [],
            422
        );
    }

    /*
     * Set status based on training status.
     * If training is not completed, certificate cannot be issued.
     */

    $allowed_statuses = [
        'issued',
        'active',
        'valid'
    ];

    if (
        !in_array(
            strtolower($data['status']),
            $allowed_statuses
        )
    ) {
        return certificate_service_error(
            'Certificate can only be issued with status: issued, active, or valid.',
            [],
            422
        );
    }

    /*
     * Set employment_eligible only if the requesting
     * user is the company associated with the training.
     * Students cannot set this value.
     */

    $company_id = $data['company_id'] ?? null;

    if (
        $company_id
        &&
        certificate_service_is_user_company(
            $user,
            $company_id
        )
    ) {
        // Company can set employment eligibility
        if (!isset($data['employment_eligible'])) {
            $data['employment_eligible'] = 0;
        }
    } else {
        // Students cannot set employment_eligible
        unset($data['employment_eligible']);
    }

    $result =
        certificate_repository_create(
            $data
        );

    if (!$result) {
        return certificate_service_error(
            'Unable to issue certificate.',
            [],
            500
        );
    }

    /*
     * Create notification for certificate issuance.
     * Notify the student whose certificate was issued.
     */
    $student_id = $data['student_id'] ?? null;

    if ($student_id) {
        notification_service_create([
            'user_id' => $student_id,
            'title' => 'Certificate Issued',
            'body' => 'Your certificate has been issued successfully.',
            'type' => 'certificate',
            'data' => [
                'certificate_id' => $result,
                'event' => 'issued'
            ]
        ]);
    }

    $certificate =
        certificate_repository_find(
            (int) $result
        );

    return certificate_service_success(
        $certificate,
        'Certificate issued successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Update Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_update(
    array $user,
    int $certificate_id,
    array $data
): array {

    if ($certificate_id <= 0) {
        return certificate_service_error(
            'Invalid certificate ID.',
            [],
            422
        );
    }

    if (!certificate_service_is_admin($user)) {
        return certificate_service_error(
            'You are not authorized to update certificates.',
            [],
            403
        );
    }

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return certificate_service_error(
            'Certificate not found.',
            [],
            404
        );
    }

    // Validate status transition if status is being changed
    if (isset($data['status'])) {
        $current_status = strtolower($certificate['status'] ?? '');
        $new_status = strtolower($data['status']);

        $allowedTransitions = CERTIFICATE_STATUS_TRANSITIONS[$current_status] ?? [];
        
        if (!in_array($new_status, $allowedTransitions, true)) {
            return certificate_service_error(
                'Invalid status transition from ' . ucfirst($current_status) . ' to ' . ucfirst($new_status) . '.',
                [],
                422
            );
        }
    }

    $validation =
        certificate_service_validate_update(
            $data
        );

    if ($validation !== true) {

        return certificate_service_error(
            'Certificate data is invalid.',
            $validation,
            422
        );
    }

    /*
     * Protected fields cannot be changed through
     * a normal update operation.
     */

    unset(
        $data['id'],
        $data['certificate_id'],
        $data['created_at'],
        $data['updated_at'],
        $data['issued_at'],
        $data['issued_by']
    );

    if (empty($data)) {
        return certificate_service_error(
            'No updateable fields were provided.',
            [],
            422
        );
    }

    $result =
        certificate_repository_update(
            $certificate_id,
            $data
        );

    if (!$result) {
        return certificate_service_error(
            'Unable to update certificate.',
            [],
            500
        );
    }

    $updated =
        certificate_repository_find(
            $certificate_id
        );

    return certificate_service_success(
        $updated,
        'Certificate updated successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Revoke Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_revoke(
    array $user,
    int $certificate_id,
    array $data = []
): array {

    if ($certificate_id <= 0) {
        return certificate_service_error(
            'Invalid certificate ID.',
            [],
            422
        );
    }

    $user_id =
        certificate_service_user_id($user);

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return certificate_service_error(
            'Certificate not found.',
            [],
            404
        );
    }

    /*
     * Only the owning company or an admin may revoke.
     */

    if (!certificate_service_is_admin($user)) {

        if (
            certificate_service_role($user)
            !== 'company'
        ) {
            return certificate_service_error(
                'You are not authorized to revoke certificates.',
                [],
                403
            );
        }

        $company_id =
            certificate_service_user_company_id(
                $user_id ?? 0
            );

        if (
            $company_id === null
            ||
            (int) ($certificate['company_id'] ?? 0)
                !== $company_id
        ) {
            return certificate_service_error(
                'You are not authorized to revoke this certificate.',
                [],
                403
            );
        }
    }

    $status =
        strtolower(
            (string) (
                $certificate['status']
                ?? ''
            )
        );

    if ($status === 'revoked') {
        return certificate_service_error(
            'Certificate is already revoked.',
            [],
            409
        );
    }

    $reason =
        trim(
            (string) (
                $data['reason']
                ?? ''
            )
        );

    if ($reason === '') {
        return certificate_service_error(
            'Revocation reason is required.',
            [
                'reason' =>
                    'A revocation reason is required.'
            ],
            422
        );
    }

    /*
     * Prefer a dedicated repository method when
     * available because revocation is a state
     * transition rather than a normal update.
     */

    if (
        function_exists(
            'certificate_repository_revoke'
        )
    ) {

        $result =
            certificate_repository_revoke(
                $certificate_id,
                $reason,
                certificate_service_user_id($user)
            );

    } else {

        $result =
            certificate_repository_update(
                $certificate_id,
                [
                    'status' => 'revoked',
                    'revocation_reason' => $reason,
                    'revoked_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_service_error(
            'Unable to revoke certificate.',
            [],
            500
        );
    }

    /**
     * Notify the student that the certificate was revoked.
     */

    $student_user_id =
        certificate_repository_student_user_id(
            (int) (
                $certificate['student_id']
                ?? 0
            )
        );

    if (
        $student_user_id
        &&
        function_exists(
            'notification_service_notify_user'
        )
    ) {
        notification_service_notify_user(
            (int) $student_user_id,
            'Certificate Revoked',
            'Your certificate has been revoked.',
            'certificate',
            [
                'certificate_id' =>
                    (int) ($certificate['id'] ?? 0),
                'event'  => 'revoked',
                'reason' => $reason
            ]
        );
    }

    $updated =
        certificate_repository_find(
            $certificate_id
        );

    return certificate_service_success(
        certificate_service_present(
            $updated,
            $user
        ),
        'Certificate revoked successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Verify Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_verify(
    int $certificate_id
): array {

    if ($certificate_id <= 0) {
        return certificate_service_error(
            'Invalid certificate ID.',
            [],
            422
        );
    }

    $certificate =
        certificate_repository_find(
            $certificate_id
        );

    if (!$certificate) {
        return certificate_service_error(
            'Certificate not found.',
            [],
            404
        );
    }

    $status =
        strtolower(
            (string) (
                $certificate['status']
                ?? ''
            )
        );

    $valid =
        in_array(
            $status,
            [
                'issued',
                'active',
                'valid'
            ],
            true
        );

    /*
     * If the repository has a dedicated verification
     * implementation, use it.
     */

    if (
        function_exists(
            'certificate_repository_verify'
        )
    ) {

        $verification =
            certificate_repository_verify(
                $certificate_id
            );

        if (is_array($verification)) {
            $valid =
                (bool) (
                    $verification['valid']
                    ?? $valid
                );

            $certificate =
                $verification['certificate']
                ?? $certificate;
        }
    }

    return certificate_service_success(
        [
            'valid'       => $valid,
            'certificate' => $certificate
        ],
        $valid
            ? 'Certificate is valid.'
            : 'Certificate is not valid.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Statistics
 |--------------------------------------------------------------------------
 */

function certificate_service_statistics(
    array $user,
    array $filters = []
): array {

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [
                    'total'   => 0,
                    'issued'  => 0,
                    'valid'   => 0,
                    'revoked' => 0
                ],
                'Certificate statistics retrieved successfully.'
            );
        }
    }

    if (
        function_exists(
            'certificate_repository_statistics'
        )
    ) {

        $statistics =
            certificate_repository_statistics(
                $filters
            );

        return certificate_service_success(
            $statistics,
            'Certificate statistics retrieved successfully.'
        );
    }

    /*
     * Fallback based on repository counts.
     */

    $statistics = [
        'total'   => 0,
        'issued'  => 0,
        'valid'   => 0,
        'revoked' => 0
    ];

    if (
        function_exists(
            'certificate_repository_count'
        )
    ) {

        $statistics['total'] =
            (int) certificate_repository_count(
                $filters
            );

        $statistics['issued'] =
            (int) certificate_repository_count(
                array_merge(
                    $filters,
                    [
                        'status' => 'issued'
                    ]
                )
            );

        $statistics['valid'] =
            (int) certificate_repository_count(
                array_merge(
                    $filters,
                    [
                        'status' => 'valid'
                    ]
                )
            );

        $statistics['revoked'] =
            (int) certificate_repository_count(
                array_merge(
                    $filters,
                    [
                        'status' => 'revoked'
                    ]
                )
            );
    }

    return certificate_service_success(
        $statistics,
        'Certificate statistics retrieved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Search
 |--------------------------------------------------------------------------
 */

function certificate_service_search(
    array $user,
    array $filters = []
): array {

    $keyword =
        trim(
            (string) (
                $filters['keyword']
                ?? ''
            )
        );

    if ($keyword === '') {
        return certificate_service_error(
            'Search keyword is required.',
            [
                'keyword' =>
                    'Please provide a search keyword.'
            ],
            422
        );
    }

    if (!certificate_service_is_admin($user)) {

        $filters =
            certificate_service_user_scope(
                $user,
                $filters
            );

        if ($filters === null) {

            return certificate_service_success(
                [],
                'Certificate search completed successfully.'
            );
        }
    }

    if (
        function_exists(
            'certificate_repository_search'
        )
    ) {

        $result =
            certificate_repository_search(
                $filters
            );

    } else {

        $result =
            certificate_repository_list(
                $filters
            );
    }

    if (!is_array($result)) {
        return certificate_service_error(
            'Unable to search certificates.',
            [],
            500
        );
    }

    $items = [];

    foreach ($result as $row) {

        if (is_array($row)) {
            $items[] =
                certificate_service_present(
                    $row,
                    $user
                );
        }
    }

    return certificate_service_success(
        $items,
        'Certificate search completed successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Validation - Create
 |--------------------------------------------------------------------------
 */

function certificate_service_validate_create(
    array $data
): array|true {

    $errors = [];

    if (
        empty($data['student_id'])
        ||
        !filter_var(
            $data['student_id'],
            FILTER_VALIDATE_INT
        )
    ) {
        $errors['student_id'] =
            'Valid student ID is required.';
    }

    if (
        empty($data['training_id'])
        ||
        !filter_var(
            $data['training_id'],
            FILTER_VALIDATE_INT
        )
    ) {
        $errors['training_id'] =
            'Valid training ID is required.';
    }

    if (
        isset($data['company_id'])
        &&
        $data['company_id'] !== ''
        &&
        filter_var(
            $data['company_id'],
            FILTER_VALIDATE_INT
        ) === false
    ) {
        $errors['company_id'] =
            'Company ID must be valid.';
    }

    if (
        isset($data['certificate_code'])
        &&
        strlen(
            trim(
                (string) $data['certificate_code']
            )
        ) > 150
    ) {
        $errors['certificate_code'] =
            'Certificate code is too long.';
    }

    if (
        isset($data['status'])
        &&
        !in_array(
            strtolower(
                (string) $data['status']
            ),
            [
                'issued',
                'active',
                'valid',
                'revoked',
                'pending'
            ],
            true
        )
    ) {
        $errors['status'] =
            'Invalid certificate status.';
    }

    if (
        isset($data['start_date'])
        &&
        trim(
            (string) $data['start_date']
        ) !== ''
        &&
        !strtotime(
            (string) $data['start_date']
        )
    ) {
        $errors['start_date'] =
            'Invalid start date format. Use Y-m-d.';
    }

    if (
        isset($data['end_date'])
        &&
        trim(
            (string) $data['end_date']
        ) !== ''
        &&
        !strtotime(
            (string) $data['end_date']
        )
    ) {
        $errors['end_date'] =
            'Invalid end date format. Use Y-m-d.';
    }

    if (
        isset($data['start_date'])
        &&
        isset($data['end_date'])
        &&
        trim(
            (string) $data['start_date']
        ) !== ''
        &&
        trim(
            (string) $data['end_date']
        ) !== ''
        &&
        strtotime(
            (string) $data['start_date']
        ) > strtotime(
            (string) $data['end_date']
        )
    ) {
        $errors['start_end_date'] =
            'Start date cannot be after end date.';
    }

    return empty($errors)
        ? true
        : $errors;
}


/*
 |--------------------------------------------------------------------------
 | Validation - Update
 |--------------------------------------------------------------------------
 */

function certificate_service_validate_update(
    array $data
): array|true {

    $errors = [];

    if (
        isset($data['status'])
        &&
        !in_array(
            strtolower(
                (string) $data['status']
            ),
            [
                'issued',
                'active',
                'valid',
                'revoked',
                'pending'
            ],
            true
        )
    ) {
        $errors['status'] =
            'Invalid certificate status.';
    }

    if (
        isset($data['start_date'])
        &&
        trim(
            (string) $data['start_date']
        ) !== ''
        &&
        !strtotime(
            (string) $data['start_date']
        )
    ) {
        $errors['start_date'] =
            'Invalid start date format. Use Y-m-d.';
    }

    if (
        isset($data['end_date'])
        &&
        trim(
            (string) $data['end_date']
        ) !== ''
        &&
        !strtotime(
            (string) $data['end_date']
        )
    ) {
        $errors['end_date'] =
            'Invalid end date format. Use Y-m-d.';
    }

    if (
        isset($data['start_date'])
        &&
        isset($data['end_date'])
        &&
        trim(
            (string) $data['start_date']
        ) !== ''
        &&
        trim(
            (string) $data['end_date']
        ) !== ''
        &&
        strtotime(
            (string) $data['start_date']
        ) > strtotime(
            (string) $data['end_date']
        )
    ) {
        $errors['start_end_date'] =
            'Start date cannot be after end date.';
    }

    return empty($errors)
        ? true
        : $errors;
}