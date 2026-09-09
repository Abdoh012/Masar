<?php

/**
 * MASAR - Application Controller
 *
 * Handles HTTP requests related to training applications.
 *
 * Native PHP - No OOP.
 *
 * Responsibilities:
 * - Receive request data.
 * - Check authentication and role.
 * - Call application service.
 * - Return API responses.
 *
 * Business logic belongs to application_service.php.
 * Database logic belongs to application_repository.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../services/application_service.php';

require_once __DIR__ . '/../../../shared/functions/application_cards.php';


/*
|--------------------------------------------------------------------------
| Create Application
|--------------------------------------------------------------------------
|
| Student applies for a training opportunity.
|
*/

function application_controller_create(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can apply for training opportunities.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    |
    | The Create Application request is raw JSON (application/json). The CV
    | is not part of this request: it is uploaded separately through the
    | existing file upload endpoint and only its id (cv_file_id) is sent.
    |
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Create Application
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_create(
            (int) $user['id'],
            (int) (
                $data['training_id']
                ?? 0
            ),
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to submit application.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_created(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Application submitted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Get Application By ID
|--------------------------------------------------------------------------
*/

function application_controller_show( int $application_id = 0 ): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    if ($application_id <= 0) {

        $application_id =
            request_get_int(
                'id'
            );
    }


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Application
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_find(
            (int) $user['id'],
            $application_id,
            strtolower((string) ($user['role'] ?? ''))
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Application not found.',

            $result['status_code']
                ??
            404,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null
    );
}


/*
|--------------------------------------------------------------------------
| Get My Applications
|--------------------------------------------------------------------------
|
| Returns applications submitted by the authenticated student.
|
*/

/*
|--------------------------------------------------------------------------
| Get Applied Applications
|--------------------------------------------------------------------------
|
| Returns only the applications submitted (pending) by the authenticated
| student. The status filter is fixed to the pending state so the Applied
| tab never depends on a client-supplied status parameter.
|
*/

function application_controller_applied_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Applied applications are the pending/submitted set. The status is fixed
    | server-side and cannot be overridden by the client. Results are also
    | scoped to the student's own specialization (the same specialization
    | matching used by the Trainings List), so the Applied tab only shows
    | pending applications for trainings that match the student.
    |
    */

    $filters = [

        'status' =>
            'pending',

        'scope_to_specialization' =>
            true,

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_student(
            (int) $user['id'],
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    |
    | Items are shaped into the clean Applied card DTO (no application
    | PII/detail fields). The items are already restricted server-side to
    | the student's pending/submitted applications.
    |
    */

    $data =
        is_array($result['data'] ?? null)
            ? $result['data']
            : [];

    if (
        isset($data['items'])
        &&
        is_array($data['items'])
    ) {
        $data['items'] =
            application_applied_cards(
                $data['items']
            );
    }

    response_success(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Get Accepted Applications
|--------------------------------------------------------------------------
|
| Returns only the applications accepted for the authenticated student,
| scoped to the student's own specialization. Mirrors the Applied tab but
| with the status fixed to the accepted state and the cards shaped as the
| Accepted Card DTO (with acceptance date, free-trial info and a daily
| motivational message).
|
*/

function application_controller_accepted_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Accepted applications are the accepted set. The status is fixed
    | server-side and cannot be overridden by the client. Results are also
    | scoped to the student's own specialization (the same specialization
    | matching used by the Trainings List and the Applied tab).
    |
    */

    $filters = [

        'scope_to_specialization' =>
            true,

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_accepted(
            (int) $user['id'],
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    |
    | Items are shaped into the clean Accepted Card DTO (no application
    | PII/detail fields). The items are already restricted server-side to
    | the student's accepted applications within their specialization.
    |
    */

    $data =
        is_array($result['data'] ?? null)
            ? $result['data']
            : [];

    if (
        isset($data['items'])
        &&
        is_array($data['items'])
    ) {
        $data['items'] =
            application_accepted_cards(
                $data['items']
            );
    }

    response_success(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Get Rejected Applications
|--------------------------------------------------------------------------
|
| Returns only the applications rejected for the authenticated student,
| scoped to the student's own specialization. Mirrors the Applied and
| Accepted tabs but with the status fixed to the rejected state and the
| cards shaped as the Rejected Card DTO (rejection timestamp, reason and
| note — no application PII or detail fields).
|
*/

function application_controller_rejected_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Rejected applications are the rejected set. The status is fixed
    | server-side and cannot be overridden by the client. Results are also
    | scoped to the student's own specialization (the same specialization
    | matching used by the Trainings List, the Applied and the Accepted tabs).
    |
    */

    $filters = [

        'scope_to_specialization' =>
            true,

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_rejected(
            (int) $user['id'],
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    |
    | Items are shaped into the clean Rejected Card DTO (no application
    | PII/detail fields). The items are already restricted server-side to
    | the student's rejected applications within their specialization.
    |
    */

    $data =
        is_array($result['data'] ?? null)
            ? $result['data']
            : [];

    if (
        isset($data['items'])
        &&
        is_array($data['items'])
    ) {
        $data['items'] =
            application_rejected_cards(
                $data['items']
            );
    }

    response_success(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Get Withdrawn Applications
|--------------------------------------------------------------------------
*/

function application_controller_withdrawn_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Withdrawn applications are the withdrawn set. The status is fixed
    | server-side and cannot be overridden by the client. Results are also
    | scoped to the student's own specialization (the same specialization
    | matching used by the Trainings List, the Applied, the Accepted and the
    | Rejected tabs).
    |
    */

    $filters = [

        'scope_to_specialization' =>
            true,

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_withdrawn(
            (int) $user['id'],
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    |
    | Items are shaped into the clean Withdrawn Card DTO (no application
    | PII/detail fields). The items are already restricted server-side to
    | the student's withdrawn applications within their specialization.
    |
    */

    $data =
        is_array($result['data'] ?? null)
            ? $result['data']
            : [];

    if (
        isset($data['items'])
        &&
        is_array($data['items'])
    ) {
        $data['items'] =
            application_withdrawn_cards(
                $data['items']
            );
    }

    response_success(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Get All Applications
|--------------------------------------------------------------------------
*/

function application_controller_all_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can access their applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | The All endpoint returns the student's complete application set across
    | the four tab states. The status set is fixed server-side and cannot be
    | overridden by the client. Results are also scoped to the student's own
    | specialization (the same specialization matching used by the Trainings
    | List and all four tab endpoints).
    |
    */

    $filters = [

        'scope_to_specialization' =>
            true,

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_all(
            (int) $user['id'],
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    |
    | Items are shaped into the unified All card DTO: every record keeps the
    | common card fields plus the status-specific fields of its own tab card
    | (Applied/Accepted/Rejected/Withdrawn). The items are already restricted
    | server-side to the student's own applications within their
    | specialization.
    |
    */

    $data =
        is_array($result['data'] ?? null)
            ? $result['data']
            : [];

    if (
        isset($data['items'])
        &&
        is_array($data['items'])
    ) {
        $data['items'] =
            application_all_cards(
                $data['items']
            );
    }

    response_success(
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Get Company Applications
|--------------------------------------------------------------------------
*/

function application_controller_company_applications(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can access received applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Training ID
    |--------------------------------------------------------------------------
    */

    $training_id =
        request_get_int(
            'training_id'
        );


    if (
        $training_id <= 0
    ) {

        response_validation_error(
            [

                'training_id' =>
                    'A valid training ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $filters = [

        'status' =>
            request_get(
                'status'
            ),

        'page' =>
            request_get_int(
                'page',
                1
            ),

        'limit' =>
            request_get_int(
                'limit',
                20
            ),

    ];


    /*
    |--------------------------------------------------------------------------
    | Get Applications
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_list_company(
            (int) $user['id'],
            $training_id,
            $filters
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve applications.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        []
    );
}


/*
|--------------------------------------------------------------------------
| Withdraw Application
|--------------------------------------------------------------------------
|
| Student can withdraw the application while it is pending.
|
*/

function application_controller_withdraw(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can withdraw applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Withdraw
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_withdraw(
            (int) $user['id'],
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to withdraw application.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Application withdrawn successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Accept Application
|--------------------------------------------------------------------------
|
| Company accepts a student application.
|
*/

function application_controller_accept(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can accept applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Accept
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_accept(
            (int) $user['id'],
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to accept application.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Application accepted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Confirm Manual Payment
|--------------------------------------------------------------------------
|
| Company confirms receipt of the manual (bank transfer) payment for an
| accepted paid training. Company-only; ownership, accepted status and the
| paid flag are all enforced inside the service.
|
*/

function application_controller_confirm_payment(
    int $application_id
): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_company_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only companies can confirm payments.'
        );

        return;
    }


    if ($application_id <= 0) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_confirm_payment(
            (int) $user['id'],
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to confirm payment.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Payment confirmed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Submit Payment Reference
|--------------------------------------------------------------------------
|
| Student submits the bank transfer reference for an accepted paid training.
| Student-only; the application must belong to the authenticated student, and
| acceptance, paid flag and fee are all enforced inside the service. The row
| is created/updated as pending — never paid.
|
*/

function application_controller_submit_payment_reference(
    int $application_id
): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can submit a payment reference.'
        );

        return;
    }


    if ($application_id <= 0) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_json();

    $reference =
        is_array($data)
            ? trim(
                (string) ($data['reference'] ?? '')
            )
            : '';


    if ($reference === '') {

        response_validation_error(
            [

                'reference' =>
                    'A payment reference is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_submit_payment_reference(
            (int) $user['id'],
            $application_id,
            $reference
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to submit payment reference.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Payment reference submitted successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Show Payment
|--------------------------------------------------------------------------
|
| Student reads the manual payment lifecycle state for one of their own
| applications. Student-only; ownership is enforced inside the service.
|
*/

function application_controller_show_payment(
    int $application_id
): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Student Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        !is_student_role($user['role'] ?? null)
    ) {

        response_forbidden(
            'Only students can view payments.'
        );

        return;
    }


    if ($application_id <= 0) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_show_payment(
            (int) $user['id'],
            $application_id
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to retrieve payment.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Payment retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Reject Application
|--------------------------------------------------------------------------
|
| Company rejects a student application.
|
| Rejection reason is required by MASAR business rules.
|
*/

function application_controller_reject(): void
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'POST'
    ) {

        response_method_not_allowed(
            'Only POST method is allowed.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Company Only
    |--------------------------------------------------------------------------
    */

    if (
        !isset($user['role'])
        ||
        $user['role'] !== 'company'
    ) {

        response_forbidden(
            'Only companies can reject applications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    $application_id =
        request_get_int(
            'id'
        );


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Request Data
    |--------------------------------------------------------------------------
    */

    $data =
        request_json();


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_reject(
            (int) $user['id'],
            $application_id,
            $data
        );


    /*
    |--------------------------------------------------------------------------
    | Handle Error
    |--------------------------------------------------------------------------
    */

    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to reject application.',

            $result['status_code']
                ??
            400,

            $result['errors']
                ??
            []
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    response_success(
        $result['data']
            ??
        null,

        $result['message']
            ??
        'Application rejected successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Download Application CV
|--------------------------------------------------------------------------
|
| Authorized by the application service through Application → Training →
| Company ownership (or the owning student / an administrator). The route
| streams the physical file returned here.
|
*/

function application_controller_cv( int $application_id = 0 ): array
{
    /*
    |--------------------------------------------------------------------------
    | Request Method
    |--------------------------------------------------------------------------
    */

    if (
        request_method() !== 'GET'
    ) {

        response_method_not_allowed(
            'Only GET method is allowed.'
        );

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    $user = auth_user();


    if (!$user) {

        response_unauthorized(
            'Authentication is required.'
        );

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    */

    if ($application_id <= 0) {

        $application_id =
            request_get_int(
                'id'
            );
    }


    if (
        $application_id <= 0
    ) {

        response_validation_error(
            [

                'id' =>
                    'A valid application ID is required.'

            ]
        );

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve CV
    |--------------------------------------------------------------------------
    */

    $result =
        application_service_cv(
            (int) $user['id'],
            $application_id,
            strtolower((string) ($user['role'] ?? ''))
        );


    if (
        !$result['success']
    ) {

        response_error(
            $result['message']
                ??
            'Unable to download CV.',

            $result['status_code']
                ??
            404,

            $result['errors']
                ??
            []
        );

        return [];
    }

    return $result;
}
