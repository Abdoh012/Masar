<?php

/**
 * MASAR - Company Approval Service
 *
 * Responsible for the business logic related to
 * company approval and rejection.
 *
 * IMPORTANT:
 * - Native PHP only.
 * - No OOP.
 * - No direct SQL.
 * - No HTTP handling.
 * - Database operations are handled by repositories.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/company_repository.php';
require_once __DIR__ . '/../repositories/company_approval_repository.php';
require_once __DIR__ . '/../validators/company_validator.php';
require_once __DIR__ . '/../../../modules/auth/repositories/auth_repository.php';
require_once __DIR__ . '/../../../shared/functions/email.php';
require_once __DIR__ . '/../../../config/mail.php';
require_once __DIR__ . '/../../../config/constants.php';


/*
|--------------------------------------------------------------------------
| Approve Company
|--------------------------------------------------------------------------
|
| Approves a pending company.
|
*/

function company_approval_service_approve(
    int $company_id,
    int $admin_user_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Company ID
    |--------------------------------------------------------------------------
    */

    if ($company_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid company ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Admin ID
    |--------------------------------------------------------------------------
    */

    if ($admin_user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid admin user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            $company_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Current Status
    |--------------------------------------------------------------------------
    */

    $current_status =
        $company['approval_status']
            ?? null;


    if (
        $current_status === 'approved'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Company is already approved.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Only Pending Companies
    |--------------------------------------------------------------------------
    */

    if (
        $current_status !== 'pending'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Only pending companies can be approved.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Company Status
    |--------------------------------------------------------------------------
    */

    $updated =
        company_repository_update_approval_status(
            $company_id,
            'approved'
        );


    if (!$updated) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to approve company.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create Approval Record
    |--------------------------------------------------------------------------
    */

    $approval_record =
        company_approval_repository_create(
            [

                'company_id' =>
                    $company_id,

                'admin_user_id' =>
                    $admin_user_id,

                'action' =>
                    'approved',

                'reason' =>
                    null,

            ]
        );


    /*
    |--------------------------------------------------------------------------
    | If Approval Log Failed
    |--------------------------------------------------------------------------
    |
    | The company status has already changed.
    | The failure is returned so the caller can log/handle it.
    |
    */

    if (
        $approval_record === false
    ) {

        return [

            'success' => true,

            'status' => 200,

            'message' =>
                'Company approved, but approval history could not be recorded.',

            'data' => [

                'company_id' =>
                    $company_id,

                'approval_status' =>
                    'approved',

                'history_recorded' =>
                    false,

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Company
    |--------------------------------------------------------------------------
    */

    $updated_company =
        company_repository_find_by_id(
            $company_id
        );


    return [

        'success' => true,

        'status' => 200,

        'message' =>
            'Company approved successfully.',

        'data' => [

            'company' =>
                $updated_company,

            'approval_history_id' =>
                $approval_record,

        ],

    ];
}

function company_approval_service_send_company_approved_email(string $email, string $company_name): bool {
    $company_name = trim($company_name) !== '' ? trim($company_name) : 'Your company';
    $subject = 'MASAR Company Approved';
    $body = '<p>Hello,</p>' .
        '<p>Great news! Your company registration for <strong>' . htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . '</strong> has been approved.</p>' .
        '<p>Your account is now active and you can sign in to MASAR to manage your company dashboard.</p>' .
        '<p>Welcome aboard.</p>' .
        '<p>Best regards,<br>MASAR Team</p>';

    return send_email($email, $subject, $body, ['html' => true]);
}


/*
|--------------------------------------------------------------------------
| Reject Company
|--------------------------------------------------------------------------
|
| Rejects a pending company.
|
| A rejection reason is mandatory.
|
*/

function company_approval_service_reject(
    int $company_id,
    int $admin_user_id,
    string $reason
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Company ID
    |--------------------------------------------------------------------------
    */

    if ($company_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid company ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Admin ID
    |--------------------------------------------------------------------------
    */

    if ($admin_user_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid admin user ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Reason
    |--------------------------------------------------------------------------
    */

    $reason =
        trim($reason);


    if ($reason === '') {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'Rejection reason is required.',

            'errors' => [

                'reason' =>
                    'Rejection reason is required.',

            ],

        ];
    }


    if (
        strlen($reason) > 1000
    ) {

        return [

            'success' => false,

            'status' => 422,

            'message' =>
                'Rejection reason must not exceed 1000 characters.',

            'errors' => [

                'reason' =>
                    'Rejection reason must not exceed 1000 characters.',

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            $company_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Current Status
    |--------------------------------------------------------------------------
    */

    $current_status =
        $company['approval_status']
            ?? null;


    if (
        $current_status === 'rejected'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Company is already rejected.',

        ];
    }


    if (
        $current_status === 'approved'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'An approved company cannot be rejected through the pending approval flow.',

        ];
    }


    if (
        $current_status !== 'pending'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Only pending companies can be rejected.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Company Status
    |--------------------------------------------------------------------------
    */

    $updated =
        company_repository_update_approval_status(
            $company_id,
            'rejected'
        );


    if (!$updated) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to reject company.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create Rejection Record
    |--------------------------------------------------------------------------
    */

    $approval_record =
        company_approval_repository_create(
            [

                'company_id' =>
                    $company_id,

                'admin_user_id' =>
                    $admin_user_id,

                'action' =>
                    'rejected',

                'reason' =>
                    $reason,

            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Handle History Failure
    |--------------------------------------------------------------------------
    */

    if (
        $approval_record === false
    ) {

        return [

            'success' => true,

            'status' => 200,

            'message' =>
                'Company rejected, but rejection history could not be recorded.',

            'data' => [

                'company_id' =>
                    $company_id,

                'approval_status' =>
                    'rejected',

                'history_recorded' =>
                    false,

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Updated Company
    |--------------------------------------------------------------------------
    */

    $updated_company =
        company_repository_find_by_id(
            $company_id
        );

    if ($updated_company !== null) {
        $company_user = auth_repository_find_user_by_id(
            (int) $updated_company['user_id']
        );

        if ($company_user !== null && isset($company_user['email'])) {
            company_approval_service_send_company_rejected_email(
                $company_user['email'],
                $updated_company['company_name'] ?? '',
                $reason
            );
        }
    }

    return [

        'success' => true,

        'status' => 200,

        'message' =>
            'Company rejected successfully.',

        'data' => [

            'company' =>
                $updated_company,

            'approval_history_id' =>
                $approval_record,

            'rejection_reason' =>
                $reason,

        ],

    ];
}

function company_approval_service_send_company_rejected_email(
    string $email,
    string $company_name,
    string $reason
): bool {
    $company_name = trim($company_name) !== '' ? trim($company_name) : 'Your company';
    $subject = 'MASAR Company Registration Update';
    $body = '<p>Hello,</p>' .
        '<p>We are updating the status of your company registration for <strong>' . htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . '</strong>.</p>' .
        '<p>Unfortunately, your company profile was not approved at this time.</p>' .
        '<p><strong>Reason:</strong> ' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p>You may contact the MASAR team to request a review or resubmit your details.</p>' .
        '<p>Best regards,<br>MASAR Team</p>';

    return send_email($email, $subject, $body, ['html' => true]);
}


/*
|--------------------------------------------------------------------------
| Get Company Approval History
|--------------------------------------------------------------------------
|
| Returns all approval/rejection actions performed
| by admins on a company.
|
*/

function company_approval_service_get_history(
    int $company_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Company ID
    |--------------------------------------------------------------------------
    */

    if ($company_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid company ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            $company_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get History
    |--------------------------------------------------------------------------
    */

    $history =
        company_approval_repository_get_by_company_id(
            $company_id
        );


    return [

        'success' => true,

        'status' => 200,

        'data' => [

            'company_id' =>
                $company_id,

            'approval_status' =>
                $company['approval_status']
                    ?? null,

            'history' =>
                $history,

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Get Pending Companies
|--------------------------------------------------------------------------
|
| Used by Admin dashboard.
|
*/

function company_approval_service_get_pending_companies(
    int $page = 1,
    int $limit = 20
): array {

    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

    if ($page < 1) {
        $page = 1;
    }


    if ($limit < 1) {
        $limit = 20;
    }


    if ($limit > 100) {
        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Get Pending Companies
    |--------------------------------------------------------------------------
    */

    $companies =
        company_repository_get_pending(
            $limit,
            $offset
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        company_repository_count_by_status(
            'pending'
        );


    /*
    |--------------------------------------------------------------------------
    | Total Pages
    |--------------------------------------------------------------------------
    */

    $total_pages =
        (int) ceil(
            $total / $limit
        );


    return [

        'success' => true,

        'status' => 200,

        'data' => [

            'companies' =>
                $companies,

            'pagination' => [

                'page' =>
                    $page,

                'limit' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

            ],

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Get Rejected Companies
|--------------------------------------------------------------------------
*/

function company_approval_service_get_rejected_companies(
    int $page = 1,
    int $limit = 20
): array {

    /*
    |--------------------------------------------------------------------------
    | Normalize Pagination
    |--------------------------------------------------------------------------
    */

    if ($page < 1) {
        $page = 1;
    }


    if ($limit < 1) {
        $limit = 20;
    }


    if ($limit > 100) {
        $limit = 100;
    }


    $offset =
        ($page - 1) * $limit;


    /*
    |--------------------------------------------------------------------------
    | Get Rejected Companies
    |--------------------------------------------------------------------------
    */

    $companies =
        company_repository_get_rejected(
            $limit,
            $offset
        );


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    $total =
        company_repository_count_by_status(
            'rejected'
        );


    /*
    |--------------------------------------------------------------------------
    | Total Pages
    |--------------------------------------------------------------------------
    */

    $total_pages =
        (int) ceil(
            $total / $limit
        );


    return [

        'success' => true,

        'status' => 200,

        'data' => [

            'companies' =>
                $companies,

            'pagination' => [

                'page' =>
                    $page,

                'limit' =>
                    $limit,

                'total' =>
                    $total,

                'total_pages' =>
                    $total_pages,

            ],

        ],

    ];
}


/*
|--------------------------------------------------------------------------
| Re-submit Rejected Company
|--------------------------------------------------------------------------
|
| Allows a rejected company to return to pending status
| if the business flow allows resubmission.
|
*/

function company_approval_service_resubmit(
    int $company_id
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Company ID
    |--------------------------------------------------------------------------
    */

    if ($company_id <= 0) {

        return [

            'success' => false,

            'status' => 400,

            'message' =>
                'Invalid company ID.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company =
        company_repository_find_by_id(
            $company_id
        );


    if ($company === null) {

        return [

            'success' => false,

            'status' => 404,

            'message' =>
                'Company not found.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Status
    |--------------------------------------------------------------------------
    */

    if (
        ($company['approval_status'] ?? null)
        !==
        'rejected'
    ) {

        return [

            'success' => false,

            'status' => 409,

            'message' =>
                'Only rejected companies can be resubmitted.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Change Back To Pending
    |--------------------------------------------------------------------------
    */

    $updated =
        company_repository_update_approval_status(
            $company_id,
            'pending'
        );


    if (!$updated) {

        return [

            'success' => false,

            'status' => 500,

            'message' =>
                'Unable to resubmit company for approval.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    $updated_company =
        company_repository_find_by_id(
            $company_id
        );


    return [

        'success' => true,

        'status' => 200,

        'message' =>
            'Company resubmitted successfully and is now pending approval.',

        'data' =>
            $updated_company,

    ];
}
