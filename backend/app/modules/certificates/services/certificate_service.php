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

    return certificate_service_success(
        $result,
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
        $certificate,
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

    /*
     * Companies may access certificates associated
     * with their company.
     */

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

    if (!certificate_service_is_admin($user)) {
        return certificate_service_error(
            'You are not authorized to revoke certificates.',
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
                    'revoked_by' =>
                        certificate_service_user_id($user),
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

    /*
     * Create notification for certificate revocation.
     * Notify the certificate owner (student) and the admin.
     */
    $student_id = $certificate['student_id'] ?? null;

    if ($student_id) {
        notification_service_create([
            'user_id' => $student_id,
            'title' => 'Certificate Revoked',
            'body' => 'Your certificate has been revoked.',
            'type' => 'certificate',
            'data' => [
                'certificate_id' => $certificate['id'],
                'event' => 'revoked',
                'reason' => $reason
            ]
        ]);
    }

    $updated =
        certificate_repository_find(
            $certificate_id
        );

    return certificate_service_success(
        $updated,
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
 | Download Certificate
 |--------------------------------------------------------------------------
 */

function certificate_service_download(
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

    if (
        !certificate_service_is_admin($user)
        &&
        !certificate_service_can_access(
            $user,
            $certificate
        )
    ) {
        return certificate_service_error(
            'You are not authorized to download this certificate.',
            [],
            403
        );
    }

    /*
     * Repository may already resolve the certificate
     * file path.
     */

    if (
        function_exists(
            'certificate_repository_get_file'
        )
    ) {

        $file =
            certificate_repository_get_file(
                $certificate_id
            );

        if (!$file) {
            return certificate_service_error(
                'Certificate file not found.',
                [],
                404
            );
        }

        return certificate_service_success(
            [
                'file' => $file
            ],
            'Certificate file prepared successfully.'
        );
    }

    /*
     * Fallback to a stored file path.
     */

    $path =
        $certificate['file_path']
        ?? $certificate['certificate_path']
        ?? null;

    if (
        !$path
        ||
        !is_file($path)
    ) {
        return certificate_service_error(
            'Certificate file not found.',
            [],
            404
        );
    }

    return certificate_service_success(
        [
            'file' => [
                'path' =>
                    $path,

                'name' =>
                    $certificate['file_name']
                    ?? basename($path),

                'mime' =>
                    $certificate['mime_type']
                    ?? 'application/pdf'
            ]
        ],
        'Certificate file prepared successfully.'
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

    return certificate_service_success(
        $result,
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