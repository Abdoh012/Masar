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


function certificate_controller_error(
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
| Download Certificate
|--------------------------------------------------------------------------
|
| GET /certificates/{id}/download
|
*/

function certificate_controller_download(
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
        certificate_service_download(
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
                ?? 'Unable to download certificate.',
            $result['errors']
                ?? [],
            $result['status']
                ?? 400
        );
    }

    /*
     * The service may return a prepared file response.
     */

    if (
        isset($result['file'])
        &&
        is_array($result['file'])
    ) {

        $file =
            $result['file'];

        if (
            isset($file['path'])
            &&
            is_file(
                $file['path']
            )
        ) {

            if (
                function_exists(
                    'response_file'
                )
            ) {

                return response_file(
                    $file['path'],
                    $file['name']
                        ?? basename(
                            $file['path']
                        ),
                    $file['mime']
                        ?? 'application/pdf'
                );
            }

            header(
                'Content-Type: ' .
                (
                    $file['mime']
                    ?? 'application/pdf'
                )
            );

            header(
                'Content-Disposition: attachment; filename="' .
                (
                    $file['name']
                    ?? basename(
                        $file['path']
                    )
                ) .
                '"'
            );

            header(
                'Content-Length: ' .
                filesize(
                    $file['path']
                )
            );

            readfile(
                $file['path']
            );

            exit;
        }
    }

    return certificate_controller_success(
        $result['data']
            ?? $result,
        'Certificate download prepared successfully.'
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
