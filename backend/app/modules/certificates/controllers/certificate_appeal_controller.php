<?php

/**
 * MASAR - Certificate Appeal Controller
 *
 * Handles HTTP requests related to certificate appeals.
 *
 * Request
 *    ↓
 * Controller
 *    ↓
 * Service
 *    ↓
 * Repository
 *    ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../services/certificate_appeal_service.php';

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';

require_once __DIR__ . '/../../../core/validation/validator.php';


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_current_user(): ?array
{
    if (function_exists('auth_user')) {
        return auth_user();
    }

    if (function_exists('auth_current_user')) {
        return auth_current_user();
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Request Input
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_input(): array
{
    if (function_exists('request_all')) {

        $data = request_all();

        return is_array($data)
            ? $data
            : [];
    }

    if (function_exists('request_json')) {

        $data = request_json();

        return is_array($data)
            ? $data
            : [];
    }

    return $_POST ?? [];
}


/*
|--------------------------------------------------------------------------
| Route Parameter
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_parameter(
    string $name,
    mixed $default = null
): mixed {

    if (function_exists('request_route')) {

        $value =
            request_route($name);

        return $value ?? $default;
    }

    if (
        isset($_GET[$name])
        &&
        $_GET[$name] !== ''
    ) {

        return $_GET[$name];
    }

    return $default;
}


/*
|--------------------------------------------------------------------------
| Appeal ID
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_appeal_id(): ?int
{
    $id =
        certificate_appeal_controller_parameter(
            'appeal_id'
        );

    if ($id === null) {

        $id =
            certificate_appeal_controller_parameter(
                'id'
            );
    }

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


/*
|--------------------------------------------------------------------------
| Certificate ID
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_certificate_id(): ?int
{
    $id =
        certificate_appeal_controller_parameter(
            'certificate_id'
        );

    if ($id === null) {

        $id =
            certificate_appeal_controller_parameter(
                'certificate'
            );
    }

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


/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_success(
    mixed $data = null,
    string $message = 'Success',
    int $status = 200
): mixed {

    if (function_exists('response_json')) {

        return response_json(
            [
                'success' => true,
                'message' => $message,
                'data'    => $data
            ],
            $status
        );
    }

    http_response_code($status);

    return [
        'success' => true,
        'message' => $message,
        'data'    => $data
    ];
}


/*
|--------------------------------------------------------------------------
| Error Response
|--------------------------------------------------------------------------
*/

function certificate_appeal_controller_error(
    string $message,
    array $errors = [],
    int $status = 400
): mixed {

    if (function_exists('response_json')) {

        return response_json(
            [
                'success' => false,
                'message' => $message,
                'errors'  => $errors
            ],
            $status
        );
    }

    http_response_code($status);

    return [
        'success' => false,
        'message' => $message,
        'errors'  => $errors
    ];
}


/*
|--------------------------------------------------------------------------
| List Appeals
|--------------------------------------------------------------------------
|
| GET /certificate-appeals
|
*/

function certificate_appeal_controller_index(): mixed
{
    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $filters = [

        'certificate_id' =>
            certificate_appeal_controller_certificate_id(),

        'student_id' =>
            certificate_appeal_controller_parameter(
                'student_id'
            ),

        'status' =>
            certificate_appeal_controller_parameter(
                'status'
            ),

        'limit' =>
            certificate_appeal_controller_parameter(
                'limit',
                20
            ),

        'offset' =>
            certificate_appeal_controller_parameter(
                'offset',
                0
            )

    ];

    $result =
        certificate_appeal_service_list(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to retrieve certificate appeals.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        'Certificate appeals retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Show Appeal
|--------------------------------------------------------------------------
|
| GET /certificate-appeals/{id}
|
*/

function certificate_appeal_controller_show(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $result =
        certificate_appeal_service_get(
            $user,
            $appeal_id
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Certificate appeal not found.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 404
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        'Certificate appeal retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Create Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals
|
*/

function certificate_appeal_controller_create(): mixed
{
    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $data =
        certificate_appeal_controller_input();

    $certificate_id =
        certificate_appeal_controller_certificate_id();

    /*
     * If certificate_id exists in route,
     * make sure it is available to the service.
     */

    if (
        $certificate_id !== null
        &&
        !isset($data['certificate_id'])
    ) {

        $data['certificate_id'] =
            $certificate_id;
    }

    $result =
        certificate_appeal_service_create(
            $user,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to create certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal created successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Update Appeal
|--------------------------------------------------------------------------
|
| PUT/PATCH /certificate-appeals/{id}
|
*/

function certificate_appeal_controller_update(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_update(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to update certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Withdraw Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals/{id}/withdraw
|
*/

function certificate_appeal_controller_withdraw(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_withdraw(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to withdraw certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal withdrawn successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Cancel Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals/{id}/cancel
|
*/

function certificate_appeal_controller_cancel(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_cancel(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to cancel certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal cancelled successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Review Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals/{id}/review
|
*/

function certificate_appeal_controller_review(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_review(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to review certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal reviewed successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Approve Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals/{id}/approve
|
*/

function certificate_appeal_controller_approve(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_approve(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to approve certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal approved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Reject Appeal
|--------------------------------------------------------------------------
|
| POST /certificate-appeals/{id}/reject
|
*/

function certificate_appeal_controller_reject(
    ?int $appeal_id = null
): mixed {

    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $appeal_id ??=
        certificate_appeal_controller_appeal_id();

    if (!$appeal_id) {

        return certificate_appeal_controller_error(
            'Valid appeal ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_appeal_controller_input();

    $result =
        certificate_appeal_service_reject(
            $user,
            $appeal_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to reject certificate appeal.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate appeal rejected successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Appeal Statistics
|--------------------------------------------------------------------------
|
| GET /certificate-appeals/statistics
|
*/

function certificate_appeal_controller_statistics(): mixed
{
    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $filters = [

        'certificate_id' =>
            certificate_appeal_controller_certificate_id(),

        'student_id' =>
            certificate_appeal_controller_parameter(
                'student_id'
            ),

        'status' =>
            certificate_appeal_controller_parameter(
                'status'
            )

    ];

    $result =
        certificate_appeal_service_statistics(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to retrieve appeal statistics.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        'Certificate appeal statistics retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search Appeals
|--------------------------------------------------------------------------
|
| GET /certificate-appeals/search
|
*/

function certificate_appeal_controller_search(): mixed
{
    $user =
        certificate_appeal_controller_current_user();

    if (!$user) {

        return certificate_appeal_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $filters = [

        'keyword' =>
            certificate_appeal_controller_parameter(
                'q',
                ''
            ),

        'status' =>
            certificate_appeal_controller_parameter(
                'status'
            ),

        'certificate_id' =>
            certificate_appeal_controller_certificate_id(),

        'student_id' =>
            certificate_appeal_controller_parameter(
                'student_id'
            ),

        'limit' =>
            certificate_appeal_controller_parameter(
                'limit',
                20
            ),

        'offset' =>
            certificate_appeal_controller_parameter(
                'offset',
                0
            )

    ];

    $result =
        certificate_appeal_service_search(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_appeal_controller_error(
            $result['message']
                ?? 'Unable to search certificate appeals.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_appeal_controller_success(
        $result['data']
            ?? $result,
        'Certificate appeal search completed successfully.'
    );
}
