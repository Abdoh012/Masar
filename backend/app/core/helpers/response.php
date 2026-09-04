<?php

/**
 * MASAR Response Helper
 *
 * Provides helper functions for returning
 * consistent JSON API responses.
 */


/*
|--------------------------------------------------------------------------
| Send JSON Response
|--------------------------------------------------------------------------
*/

function response_json(
    array $body,
    int $status_code = 200
): never {

    /*
    |--------------------------------------------------------------------------
    | HTTP Status
    |--------------------------------------------------------------------------
    */

    http_response_code(
        $status_code
    );


    /*
    |--------------------------------------------------------------------------
    | Content Type
    |--------------------------------------------------------------------------
    */

    header(
        'Content-Type: application/json; charset=UTF-8'
    );


    /*
    |--------------------------------------------------------------------------
    | JSON Response
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        $body,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

function response_success(
    mixed $data = null,
    string $message = 'Success.',
    int $status_code = 200
): never {

    response_json(
        [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ],
        $status_code
    );
}


/*
|--------------------------------------------------------------------------
| Created Response
|--------------------------------------------------------------------------
*/

function response_created(
    mixed $data = null,
    string $message = 'Created successfully.'
): never {

    response_success(
        $data,
        $message,
        201
    );
}


/*
|--------------------------------------------------------------------------
| No Content Response
|--------------------------------------------------------------------------
*/

function response_no_content(): never
{
    http_response_code(204);

    exit;
}


/*
|--------------------------------------------------------------------------
| Error Response
|--------------------------------------------------------------------------
*/

function response_error(
    string $message = 'An error occurred.',
    int $status_code = 500,
    mixed $errors = null,
    mixed $data = null
): never {

    response_json(
        [
            'success' => false,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ],
        $status_code
    );
}


/*
|--------------------------------------------------------------------------
| Validation Error
|--------------------------------------------------------------------------
*/

function response_validation_error(
    array $errors,
    string $message = 'Validation failed.'
): never {

    response_error(
        $message,
        422,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Bad Request
|--------------------------------------------------------------------------
*/

function response_bad_request(
    string $message = 'Bad request.',
    mixed $errors = null
): never {

    response_error(
        $message,
        400,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Unauthorized
|--------------------------------------------------------------------------
*/

function response_unauthorized(
    string $message = 'Authentication required.'
): never {

    response_error(
        $message,
        401
    );
}


/*
|--------------------------------------------------------------------------
| Forbidden
|--------------------------------------------------------------------------
*/

function response_forbidden(
    string $message = 'You do not have permission to perform this action.'
): never {

    response_error(
        $message,
        403
    );
}


/*
|--------------------------------------------------------------------------
| Not Found
|--------------------------------------------------------------------------
*/

function response_not_found(
    string $message = 'Resource not found.'
): never {

    response_error(
        $message,
        404
    );
}


/*
|--------------------------------------------------------------------------
| Method Not Allowed
|--------------------------------------------------------------------------
*/

function response_method_not_allowed(
    string $message = 'Method not allowed.'
): never {

    response_error(
        $message,
        405
    );
}


/*
|--------------------------------------------------------------------------
| Conflict
|--------------------------------------------------------------------------
*/

function response_conflict(
    string $message = 'Resource conflict.',
    mixed $errors = null
): never {

    response_error(
        $message,
        409,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Unprocessable Entity
|--------------------------------------------------------------------------
*/

function response_unprocessable(
    string $message = 'Unable to process the request.',
    mixed $errors = null
): never {

    response_error(
        $message,
        422,
        $errors
    );
}


/*
|--------------------------------------------------------------------------
| Too Many Requests
|--------------------------------------------------------------------------
*/

function response_too_many_requests(
    string $message = 'Too many requests.'
): never {

    response_error(
        $message,
        429
    );
}


/*
|--------------------------------------------------------------------------
| Server Error
|--------------------------------------------------------------------------
*/

function response_server_error(
    string $message = 'An internal server error occurred.'
): never {

    response_error(
        $message,
        500
    );
}
