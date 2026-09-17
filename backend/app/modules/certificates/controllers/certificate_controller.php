<?php

/**
 * MASAR - Certificate Controller
 *
 * Handles HTTP requests related to certificates.
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

require_once __DIR__ . '/../services/certificate_service.php';

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';

require_once __DIR__ . '/../../../core/validation/validator.php';


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

function certificate_controller_current_user(): ?array
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
| Get Request Data
|--------------------------------------------------------------------------
*/

function certificate_controller_input(): array
{
    if (function_exists('request_input')) {
        $data = request_input();

        return is_array($data)
            ? $data
            : [];
    }

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
| Get Route Parameter
|--------------------------------------------------------------------------
*/

function certificate_controller_parameter(
    string $name,
    mixed $default = null
): mixed {

    if (function_exists('request_route')) {
        $value = request_route($name);

        if ($value !== null) {
            return $value;
        }
    }

    if (function_exists('request_get')) {
        $value = request_get($name, $default);

        if ($value !== null) {
            return $value;
        }
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
| Get Certificate ID
|--------------------------------------------------------------------------
*/

function certificate_controller_certificate_id(): ?int
{
    $id = certificate_controller_parameter(
        'certificate_id'
    );

    if ($id === null) {
        $id = certificate_controller_parameter('id');
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
| Response Helpers
|--------------------------------------------------------------------------
*/

function certificate_controller_success(
    mixed $data = null,
    string $message = 'Success',
    int $status = 200
): mixed {

    if (function_exists('response_json')) {
        return response_json(
            $data,
            $status,
            $message
        );
    }

    http_response_code($status);

    return [
        'success' => true,
        'message' => $message,
        'data'    => $data
    ];
}


function certificate_controller_error(
    string $message,
    array $errors = [],
    int $status = 400
): mixed {

    if (function_exists('response_json')) {
        return response_json(
            null,
            $status,
            $message,
            $errors
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
| Index
|--------------------------------------------------------------------------
|
| GET /certificates
|
*/

function certificate_controller_index(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $limit = certificate_controller_parameter(
        'limit',
        20
    );

    $offset = certificate_controller_parameter(
        'offset',
        0
    );

    $filters = [
        'status' => certificate_controller_parameter(
            'status'
        ),

        'student_id' => certificate_controller_parameter(
            'student_id'
        ),

        'training_id' => certificate_controller_parameter(
            'training_id'
        ),

        'company_id' => certificate_controller_parameter(
            'company_id'
        ),

        'limit' => $limit,

        'offset' => $offset
    ];

    $result =
        certificate_service_list(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Certificates retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
|
| GET /certificates/{id}
|
*/

function certificate_controller_show(
    ?int $certificate_id = null
): mixed {

    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $certificate_id ??=
        certificate_controller_certificate_id();

    if (!$certificate_id) {
        return certificate_controller_error(
            'Valid certificate ID is required.',
            [],
            422
        );
    }

    $result =
        certificate_service_get(
            $user,
            $certificate_id
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Certificate not found.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 404
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Certificate retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Eligible Certificates
|--------------------------------------------------------------------------
|
| GET /certificates/eligible
|
*/

function certificate_controller_eligible(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $filters = [
        'student_id' => certificate_controller_parameter(
            'student_id'
        ),

        'training_id' => certificate_controller_parameter(
            'training_id'
        ),

        'company_id' => certificate_controller_parameter(
            'company_id'
        )
    ];

    $result =
        certificate_service_eligible(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve eligible certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Eligible certificates retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Pending Certificates
|--------------------------------------------------------------------------
|
| GET /certificates/pending
|
| Returns only the authenticated user's certificates whose status is
| `pending`. Scope is derived from the auth context only; no client-
| supplied student_id or other query parameter is trusted.
|
*/

function certificate_controller_pending(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $result =
        certificate_service_pending(
            $user
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve pending certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Pending certificates retrieved successfully.'
    );
}

/*
|--------------------------------------------------------------------------
| Issued Certificates
|--------------------------------------------------------------------------
|
| GET /certificates/issued
|
| Returns only the authenticated user's certificates whose status is
| `issued`. Scope is derived from the auth context only; no client-
| supplied student_id or other query parameter is trusted.
|
*/

function certificate_controller_issued(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $result =
        certificate_service_issued(
            $user
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve issued certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Issued certificates retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Revoked Certificates
|--------------------------------------------------------------------------
|
| GET /certificates/revoked
|
| Returns only the authenticated user's certificates whose status is
| `revoked`. Scope is derived from the auth context only; no client-
| supplied student_id or other query parameter is trusted.
|
*/

function certificate_controller_revoked(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $result =
        certificate_service_revoked(
            $user
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve revoked certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Revoked certificates retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Request Certificate
|--------------------------------------------------------------------------
|
| POST /certificates
|
| Role-aware entry point for the certificate lifecycle. Students request
| their own certificate; companies and admins request on behalf of a
| student. Always creates a PENDING certificate.
|
*/

function certificate_controller_request(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $data =
        certificate_controller_input();

    $result =
        certificate_service_request(
            $user,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to request certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate requested successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Confirm Certificate
|--------------------------------------------------------------------------
|
| POST /certificates/{id}/confirm
|
*/

function certificate_controller_confirm(
    ?int $certificate_id = null
): mixed {

    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $certificate_id ??=
        certificate_controller_certificate_id();

    if (!$certificate_id) {
        return certificate_controller_error(
            'Valid certificate ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_controller_input();

    $result =
        certificate_service_confirm(
            $user,
            $certificate_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to confirm certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate confirmed and issued successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Issue Certificate
|--------------------------------------------------------------------------
|
| POST /certificates
|
*/

function certificate_controller_create(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $data =
        certificate_controller_input();

    $result =
        certificate_service_create(
            $user,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to issue certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate issued successfully.',
        201
    );
}


/*
|--------------------------------------------------------------------------
| Update Certificate
|--------------------------------------------------------------------------
|
| PUT/PATCH /certificates/{id}
|
*/

function certificate_controller_update(
    ?int $certificate_id = null
): mixed {

    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $certificate_id ??=
        certificate_controller_certificate_id();

    if (!$certificate_id) {
        return certificate_controller_error(
            'Valid certificate ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_controller_input();

    $result =
        certificate_service_update(
            $user,
            $certificate_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to update certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate updated successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Revoke Certificate
|--------------------------------------------------------------------------
|
| POST /certificates/{id}/revoke
|
*/

function certificate_controller_revoke(
    ?int $certificate_id = null
): mixed {

    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $certificate_id ??=
        certificate_controller_certificate_id();

    if (!$certificate_id) {
        return certificate_controller_error(
            'Valid certificate ID is required.',
            [],
            422
        );
    }

    $data =
        certificate_controller_input();

    $result =
        certificate_service_revoke(
            $user,
            $certificate_id,
            $data
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to revoke certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate revoked successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Verify Certificate
|--------------------------------------------------------------------------
|
| GET /certificates/{id}/verify
|
*/

function certificate_controller_verify(
    ?int $certificate_id = null
): mixed {

    $certificate_id ??=
        certificate_controller_certificate_id();

    if (!$certificate_id) {
        return certificate_controller_error(
            'Valid certificate ID is required.',
            [],
            422
        );
    }

    $result =
        certificate_service_verify(
            $certificate_id
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to verify certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        $result['message']
            ?? 'Certificate verification completed.'
    );
}


/*
|--------------------------------------------------------------------------
| Certificate Statistics
|--------------------------------------------------------------------------
|
| GET /certificates/statistics
|
*/

function certificate_controller_statistics(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $filters = [
        'student_id' =>
            certificate_controller_parameter(
                'student_id'
            ),

        'training_id' =>
            certificate_controller_parameter(
                'training_id'
            ),

        'company_id' =>
            certificate_controller_parameter(
                'company_id'
            )
    ];

    $result =
        certificate_service_statistics(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to retrieve certificate statistics.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Certificate statistics retrieved successfully.'
    );
}


/*
|--------------------------------------------------------------------------
| Search Certificates
|--------------------------------------------------------------------------
|
| GET /certificates/search
|
*/

function certificate_controller_search(): mixed
{
    $user = certificate_controller_current_user();

    if (!$user) {
        return certificate_controller_error(
            'Authentication required.',
            [],
            401
        );
    }

    $keyword =
        certificate_controller_parameter(
            'q',
            ''
        );

    $filters = [
        'keyword' => $keyword,

        'status' =>
            certificate_controller_parameter(
                'status'
            ),

        'limit' =>
            certificate_controller_parameter(
                'limit',
                20
            ),

        'offset' =>
            certificate_controller_parameter(
                'offset',
                0
            )
    ];

    $result =
        certificate_service_search(
            $user,
            $filters
        );

    if (
        !is_array($result)
        ||
        ($result['success'] ?? false) === false
    ) {

        return certificate_controller_error(
            $result['message']
                ?? 'Unable to search certificates.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Certificate search completed successfully.'
    );
}
