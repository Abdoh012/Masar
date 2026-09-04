<?php

/**
 * MASAR - Certificate Appeal Service
 *
 * Business logic for certificate appeals.
 *
 * Controller
 *     ↓
 * Appeal Service
 *     ↓
 * Appeal Repository
 *     ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/certificate_appeal_repository.php';

require_once __DIR__ . '/../../../core/database/transaction.php';
require_once __DIR__ . '/../../../core/validation/validator.php';


/*
|--------------------------------------------------------------------------
| Response Helpers
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_success(
    mixed $data = null,
    string $message = 'Success'
): array {
    return [
        'success' => true,
        'message' => $message,
        'data'    => $data
    ];
}


function certificate_appeal_service_error(
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
| User Helpers
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_user_id(
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


function certificate_appeal_service_role(
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


function certificate_appeal_service_is_admin(
    array $user
): bool {

    return in_array(
        certificate_appeal_service_role($user),
        [
            'admin',
            'super_admin',
            'administrator'
        ],
        true
    );
}


/*
|--------------------------------------------------------------------------
| List Appeals
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_list(
    array $user,
    array $filters = []
): array {

    $user_id =
        certificate_appeal_service_user_id($user);

    if (!$user_id) {
        return certificate_appeal_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    /*
     * Non-admin users can only see their own appeals.
     */

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        $filters['user_id'] = $user_id;
    }

    $result =
        certificate_appeal_repository_list(
            $filters
        );

    if (!is_array($result)) {
        return certificate_appeal_service_error(
            'Unable to retrieve certificate appeals.',
            [],
            500
        );
    }

    return certificate_appeal_service_success(
        $result,
        'Certificate appeals retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_get(
    array $user,
    int $appeal_id
): array {

    if ($appeal_id <= 0) {
        return certificate_appeal_service_error(
            'Invalid appeal ID.',
            [],
            422
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    if (
        !certificate_appeal_service_is_admin($user)
        &&
        !certificate_appeal_service_can_access(
            $user,
            $appeal
        )
    ) {
        return certificate_appeal_service_error(
            'You are not authorized to access this appeal.',
            [],
            403
        );
    }

    return certificate_appeal_service_success(
        $appeal,
        'Certificate appeal retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Access Check
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_can_access(
    array $user,
    array $appeal
): bool {

    $user_id =
        certificate_appeal_service_user_id($user);

    if (!$user_id) {
        return false;
    }

    $appeal_user_id =
        $appeal['user_id']
        ?? $appeal['student_user_id']
        ?? null;

    if (
        $appeal_user_id !== null
        &&
        (int) $appeal_user_id === $user_id
    ) {
        return true;
    }

    /*
     * If repository supports a dedicated ownership check,
     * use it for student-based appeals.
     */

    $student_id =
        $appeal['student_id']
        ?? null;

    if (
        $student_id !== null
        &&
        function_exists(
            'certificate_appeal_repository_student_belongs_to_user'
        )
    ) {

        return (bool)
            certificate_appeal_repository_student_belongs_to_user(
                (int) $student_id,
                $user_id
            );
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| Create Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_create(
    array $user,
    array $data
): array {

    $user_id =
        certificate_appeal_service_user_id($user);

    if (!$user_id) {
        return certificate_appeal_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $validation =
        certificate_appeal_service_validate_create(
            $data
        );

    if ($validation !== true) {
        return certificate_appeal_service_error(
            'Certificate appeal data is invalid.',
            $validation,
            422
        );
    }

    $certificate_id =
        (int) $data['certificate_id'];

    /*
     * Make sure the certificate exists.
     */

    if (
        function_exists(
            'certificate_appeal_repository_certificate_exists'
        )
    ) {

        $exists =
            certificate_appeal_repository_certificate_exists(
                $certificate_id
            );

        if (!$exists) {
            return certificate_appeal_service_error(
                'Certificate not found.',
                [],
                404
            );
        }
    }

    /*
     * Prevent duplicate active appeals.
     */

    if (
        function_exists(
            'certificate_appeal_repository_has_active_appeal'
        )
    ) {

        $has_active =
            certificate_appeal_repository_has_active_appeal(
                $certificate_id,
                $user_id
            );

        if ($has_active) {
            return certificate_appeal_service_error(
                'You already have an active appeal for this certificate.',
                [],
                409
            );
        }
    }

    /*
     * Users should not be able to submit an appeal
     * on behalf of another user.
     */

    $data['user_id'] =
        $user_id;

    unset(
        $data['id'],
        $data['appeal_id'],
        $data['created_at'],
        $data['updated_at'],
        $data['reviewed_at'],
        $data['reviewed_by']
    );

    if (empty($data['status'])) {
        $data['status'] = 'pending';
    }

    $result =
        certificate_appeal_repository_create(
            $data
        );

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to create certificate appeal.',
            [],
            500
        );
    }

    /*
     * Create notification for new certificate appeal.
     * Notify administrators about the new appeal.
     */
    notification_service_create([
        'user_id' => 1, // TODO: Get admin user ID from context
        'title' => 'New Certificate Appeal',
        'body' => 'A new certificate appeal has been submitted.',
        'type' => 'certificate_appeal',
        'data' => [
            'appeal_id' => $result,
            'certificate_id' => $certificate_id,
            'student_id' => $user_id,
            'event' => 'submitted'
        ]
    ]);

    return certificate_appeal_service_success(
        $result,
        'Certificate appeal created successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Update Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_update(
    array $user,
    int $appeal_id,
    array $data
): array {

    if ($appeal_id <= 0) {
        return certificate_appeal_service_error(
            'Invalid appeal ID.',
            [],
            422
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    if (
        !certificate_appeal_service_is_admin($user)
        &&
        !certificate_appeal_service_can_access(
            $user,
            $appeal
        )
    ) {
        return certificate_appeal_service_error(
            'You are not authorized to update this appeal.',
            [],
            403
        );
    }

    /*
     * Only pending appeals may be edited by the owner.
     */

    $status =
        strtolower(
            (string) (
                $appeal['status']
                ?? ''
            )
        );

    if (
        !certificate_appeal_service_is_admin($user)
        &&
        $status !== 'pending'
    ) {
        return certificate_appeal_service_error(
            'Only pending appeals can be updated.',
            [],
            409
        );
    }

    $validation =
        certificate_appeal_service_validate_update(
            $data
        );

    if ($validation !== true) {
        return certificate_appeal_service_error(
            'Certificate appeal data is invalid.',
            $validation,
            422
        );
    }

    /*
     * Protected fields.
     */

    unset(
        $data['id'],
        $data['appeal_id'],
        $data['certificate_id'],
        $data['user_id'],
        $data['status'],
        $data['created_at'],
        $data['updated_at'],
        $data['reviewed_at'],
        $data['reviewed_by'],
        $data['decision']
    );

    if (empty($data)) {
        return certificate_appeal_service_error(
            'No updateable fields were provided.',
            [],
            422
        );
    }

    $result =
        certificate_appeal_repository_update(
            $appeal_id,
            $data
        );

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to update certificate appeal.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Withdraw Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_withdraw(
    array $user,
    int $appeal_id,
    array $data = []
): array {

    $user_id =
        certificate_appeal_service_user_id($user);

    if (!$user_id) {
        return certificate_appeal_service_error(
            'Invalid authenticated user.',
            [],
            401
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    if (
        !certificate_appeal_service_can_access(
            $user,
            $appeal
        )
    ) {
        return certificate_appeal_service_error(
            'You are not authorized to withdraw this appeal.',
            [],
            403
        );
    }

    $status =
        strtolower(
            (string) (
                $appeal['status']
                ?? ''
            )
        );

    if (
        !in_array(
            $status,
            [
                'pending',
                'under_review',
                'reviewing'
            ],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal cannot be withdrawn in its current status.',
            [],
            409
        );
    }

    $result = false;

    if (
        function_exists(
            'certificate_appeal_repository_withdraw'
        )
    ) {

        $result =
            certificate_appeal_repository_withdraw(
                $appeal_id,
                $user_id
            );

    } else {

        $result =
            certificate_appeal_repository_update(
                $appeal_id,
                [
                    'status' => 'withdrawn',
                    'withdrawn_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to withdraw certificate appeal.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal withdrawn successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Cancel Appeal
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_cancel(
    array $user,
    int $appeal_id,
    array $data = []
): array {

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        return certificate_appeal_service_error(
            'Only authorized administrators can cancel appeals.',
            [],
            403
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
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
        return certificate_appeal_service_error(
            'Cancellation reason is required.',
            [
                'reason' =>
                    'A cancellation reason is required.'
            ],
            422
        );
    }

    if (
        function_exists(
            'certificate_appeal_repository_cancel'
        )
    ) {

        $result =
            certificate_appeal_repository_cancel(
                $appeal_id,
                $reason,
                certificate_appeal_service_user_id($user)
            );

    } else {

        $result =
            certificate_appeal_repository_update(
                $appeal_id,
                [
                    'status' => 'cancelled',
                    'decision' => $reason,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to cancel certificate appeal.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal cancelled successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Review Appeal
 |--------------------------------------------------------------------------
 */

function certificate_appeal_service_review(
    array $user,
    int $appeal_id,
    array $data
): array {

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        return certificate_appeal_service_error(
            'Only authorized administrators can review appeals.',
            [],
            403
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    $status =
        strtolower(
            (string) (
                $appeal['status']
                ?? ''
            )
        );

    /*
     * Validate appeal state transition.
     * Only pending or under_review appeals can be moved to review.
     */
    if (
        in_array(
            $status,
            ['approved', 'rejected', 'withdrawn', 'cancelled'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal has already been finalized.',
            [],
            409
        );
    }

    if (
        !in_array(
            $status,
            ['pending', 'under_review'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal cannot be moved to review in its current status.',
            [],
            409
        );
    }

    $notes =
        trim(
            (string) (
                $data['notes']
                ?? $data['review_notes']
                ?? ''
            )
        );

    if (
        function_exists(
            'certificate_appeal_repository_review'
        )
    ) {

        $result =
            certificate_appeal_repository_review(
                $appeal_id,
                [
                    'status' => 'under_review',
                    'review_notes' => $notes,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );

    } else {

        $result =
            certificate_appeal_repository_update(
                $appeal_id,
                [
                    'status' => 'under_review',
                    'review_notes' => $notes,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to move appeal to review.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal moved to review successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Approve Appeal
 |--------------------------------------------------------------------------
 */

function certificate_appeal_service_approve(
    array $user,
    int $appeal_id,
    array $data
): array {

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        return certificate_appeal_service_error(
            'Only authorized administrators can approve appeals.',
            [],
            403
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    $status =
        strtolower(
            (string) (
                $appeal['status']
                ?? ''
            )
        );

    if (
        in_array(
            $status,
            ['approved', 'rejected', 'withdrawn', 'cancelled'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal has already been finalized.',
            [],
            409
        );
    }

    /*
     * Validate appeal state transition.
     * Only pending or under_review appeals can be approved.
     */
    if (
        !in_array(
            $status,
            ['pending', 'under_review'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal cannot be approved in its current status.',
            [],
            409
        );
    }

    $decision =
        trim(
            (string) (
                $data['decision']
                ?? $data['notes']
                ?? ''
            )
        );

    /*
     * Approval may also trigger certificate restoration
     * or correction. Keep this operation inside a
     * repository-level transaction when supported.
     */

    if (
        function_exists(
            'certificate_appeal_repository_approve'
        )
    ) {

        $result =
            certificate_appeal_repository_approve(
                $appeal_id,
                [
                    'decision' => $decision,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );

    } else {

        $result =
            certificate_appeal_repository_update(
                $appeal_id,
                [
                    'status' => 'approved',
                    'decision' => $decision,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to approve certificate appeal.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal approved successfully.'
    );
}


/*
 |--------------------------------------------------------------------------
 | Reject Appeal
 |--------------------------------------------------------------------------
 */

function certificate_appeal_service_reject(
    array $user,
    int $appeal_id,
    array $data
): array {

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        return certificate_appeal_service_error(
            'Only authorized administrators can reject appeals.',
            [],
            403
        );
    }

    $appeal =
        certificate_appeal_repository_find(
            $appeal_id
        );

    if (!$appeal) {
        return certificate_appeal_service_error(
            'Certificate appeal not found.',
            [],
            404
        );
    }

    $status =
        strtolower(
            (string) (
                $appeal['status']
                ?? ''
            )
        );

    if (
        in_array(
            $status,
            ['approved', 'rejected', 'withdrawn', 'cancelled'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal has already been finalized.',
            [],
            409
        );
    }

    /*
     * Validate appeal state transition.
     * Only pending or under_review appeals can be rejected.
     */
    if (
        !in_array(
            $status,
            ['pending', 'under_review'],
            true
        )
    ) {
        return certificate_appeal_service_error(
            'This appeal cannot be rejected in its current status.',
            [],
            409
        );
    }

    $reason =
        trim(
            (string) (
                $data['reason']
                ?? $data['decision']
                ?? ''
            )
        );

    if ($reason === '') {
        return certificate_appeal_service_error(
            'Rejection reason is required.',
            [
                'reason' =>
                    'A rejection reason is required.'
            ],
            422
        );
    }

    if (
        function_exists(
            'certificate_appeal_repository_reject'
        )
    ) {

        $result =
            certificate_appeal_repository_reject(
                $appeal_id,
                [
                    'reason' => $reason,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );

    } else {

        $result =
            certificate_appeal_repository_update(
                $appeal_id,
                [
                    'status' => 'rejected',
                    'decision' => $reason,
                    'reviewed_by' =>
                        certificate_appeal_service_user_id($user),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s')
                ]
            );
    }

    if (!$result) {
        return certificate_appeal_service_error(
            'Unable to reject certificate appeal.',
            [],
            500
        );
    }

    $updated =
        certificate_appeal_repository_find(
            $appeal_id
        );

    return certificate_appeal_service_success(
        $updated,
        'Certificate appeal rejected successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_statistics(
    array $user,
    array $filters = []
): array {

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        $filters['user_id'] =
            certificate_appeal_service_user_id($user);
    }

    if (
        function_exists(
            'certificate_appeal_repository_statistics'
        )
    ) {

        $statistics =
            certificate_appeal_repository_statistics(
                $filters
            );

        return certificate_appeal_service_success(
            $statistics,
            'Certificate appeal statistics retrieved successfully.'
        );
    }

    $statistics = [
        'total'        => 0,
        'pending'      => 0,
        'under_review' => 0,
        'approved'     => 0,
        'rejected'     => 0,
        'withdrawn'    => 0,
        'cancelled'    => 0
    ];

    if (
        function_exists(
            'certificate_appeal_repository_count'
        )
    ) {

        $statistics['total'] =
            (int) certificate_appeal_repository_count(
                $filters
            );

        foreach (
            [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'withdrawn',
                'cancelled'
            ] as $status
        ) {

            $statistics[$status] =
                (int) certificate_appeal_repository_count(
                    array_merge(
                        $filters,
                        [
                            'status' => $status
                        ]
                    )
                );
        }
    }

    return certificate_appeal_service_success(
        $statistics,
        'Certificate appeal statistics retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_search(
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
        return certificate_appeal_service_error(
            'Search keyword is required.',
            [
                'keyword' =>
                    'Please provide a search keyword.'
            ],
            422
        );
    }

    if (
        !certificate_appeal_service_is_admin($user)
    ) {
        $filters['user_id'] =
            certificate_appeal_service_user_id($user);
    }

    if (
        function_exists(
            'certificate_appeal_repository_search'
        )
    ) {

        $result =
            certificate_appeal_repository_search(
                $filters
            );

    } else {

        $result =
            certificate_appeal_repository_list(
                $filters
            );
    }

    if (!is_array($result)) {
        return certificate_appeal_service_error(
            'Unable to search certificate appeals.',
            [],
            500
        );
    }

    return certificate_appeal_service_success(
        $result,
        'Certificate appeal search completed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Validation - Create
|--------------------------------------------------------------------------
*/

function certificate_appeal_service_validate_create(
    array $data
): array|true {

    $errors = [];

    if (
        empty($data['certificate_id'])
        ||
        filter_var(
            $data['certificate_id'],
            FILTER_VALIDATE_INT
        ) === false
    ) {
        $errors['certificate_id'] =
            'Valid certificate ID is required.';
    }

    /*
     * Depending on the database design, the appeal
     * may contain a reason, description, or message.
     */

    $reason =
        trim(
            (string) (
                $data['reason']
                ?? $data['description']
                ?? $data['message']
                ?? ''
            )
        );

    if ($reason === '') {
        $errors['reason'] =
            'Appeal reason is required.';
    } elseif (mb_strlen($reason) < 10) {
        $errors['reason'] =
            'Appeal reason must contain at least 10 characters.';
    }

    if (mb_strlen($reason) > 5000) {
        $errors['reason'] =
            'Appeal reason is too long.';
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

function certificate_appeal_service_validate_update(
    array $data
): array|true {

    $errors = [];

    $has_reason =
        array_key_exists(
            'reason',
            $data
        );

    $has_description =
        array_key_exists(
            'description',
            $data
        );

    $has_message =
        array_key_exists(
            'message',
            $data
        );

    if (
        $has_reason
        ||
        $has_description
        ||
        $has_message
    ) {

        $reason =
            trim(
                (string) (
                    $data['reason']
                    ?? $data['description']
                    ?? $data['message']
                    ?? ''
                )
            );

        if ($reason === '') {
            $errors['reason'] =
                'Appeal reason cannot be empty.';
        } elseif (mb_strlen($reason) < 10) {
            $errors['reason'] =
                'Appeal reason must contain at least 10 characters.';
        } elseif (mb_strlen($reason) > 5000) {
            $errors['reason'] =
                'Appeal reason is too long.';
        }
    }

    return empty($errors)
        ? true
        : $errors;
}
