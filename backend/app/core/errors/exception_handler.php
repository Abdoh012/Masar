<?php

/**
 * MASAR Exception Handler
 *
 * Handles uncaught exceptions and converts them
 * into a consistent JSON API response.
 */


/*
|--------------------------------------------------------------------------
| Load Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../http/response.php';
require_once __DIR__ . '/../logging/logger.php';
require_once __DIR__ . '/error_handler.php';


/*
|--------------------------------------------------------------------------
| Handle Uncaught Exception
|--------------------------------------------------------------------------
*/

function masar_exception_handler(
    Throwable $exception
): void {

    /*
    |--------------------------------------------------------------------------
    | Log Exception
    |--------------------------------------------------------------------------
    */

    logger_exception(
        $exception
    );


    /*
    |--------------------------------------------------------------------------
    | Determine HTTP Status
    |--------------------------------------------------------------------------
    */

    $status_code = exception_status_code($exception);
    if ($status_code < 400 || $status_code > 599) {
        $status_code = 500;
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Error Message
    |--------------------------------------------------------------------------
    */

    $message = error_safe_message(
        $exception
    );


    /*
    |--------------------------------------------------------------------------
    | JSON Response
    |--------------------------------------------------------------------------
    */

    response_error(
        $message,
        $status_code
    );
}


/*
|--------------------------------------------------------------------------
| Register Exception Handler
|--------------------------------------------------------------------------
*/

function register_exception_handler(): void
{
    set_exception_handler(
        'masar_exception_handler'
    );

    register_shutdown_function(
        'masar_shutdown_handler'
    );
}

function masar_shutdown_handler(): void
{
    $error = error_get_last();
    if (!is_array($error) || !in_array($error['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    $exception = new ErrorException(
        (string) ($error['message'] ?? 'Fatal application error.'),
        0,
        (int) ($error['type'] ?? E_ERROR),
        (string) ($error['file'] ?? ''),
        (int) ($error['line'] ?? 0)
    );

    logger_exception($exception);

    if (!headers_sent()) {
        response_error(error_safe_message($exception), 500);
    }
}


/*
|--------------------------------------------------------------------------
| Exception HTTP Status
|--------------------------------------------------------------------------
|
| Determines the appropriate HTTP status code
| based on the exception type.
|
*/

function exception_status_code(
    Throwable $exception
): int {

    /*
    |--------------------------------------------------------------------------
    | HTTP Exception
    |--------------------------------------------------------------------------
    */

    if (
        $exception instanceof HttpException
    ) {

        return $exception->getStatusCode();
    }


    /*
    |--------------------------------------------------------------------------
    | Invalid Argument
    |--------------------------------------------------------------------------
    */

    if (
        $exception instanceof InvalidArgumentException
    ) {

        return 422;
    }


    /*
    |--------------------------------------------------------------------------
    | Unauthorized
    |--------------------------------------------------------------------------
    */

    if (
        $exception instanceof UnauthorizedException
    ) {

        return 401;
    }


    /*
    |--------------------------------------------------------------------------
    | Forbidden
    |--------------------------------------------------------------------------
    */

    if (
        $exception instanceof ForbiddenException
    ) {

        return 403;
    }


    /*
    |--------------------------------------------------------------------------
    | Not Found
    |--------------------------------------------------------------------------
    */

    if (
        $exception instanceof NotFoundException
    ) {

        return 404;
    }


    /*
    |--------------------------------------------------------------------------
    | Default Server Error
    |--------------------------------------------------------------------------
    */

    return 500;
}


/*
|--------------------------------------------------------------------------
| HTTP Exception
|--------------------------------------------------------------------------
*/

class HttpException extends Exception
{
    private int $status_code;


    public function __construct(
        string $message,
        int $status_code = 500
    ) {

        parent::__construct(
            $message
        );

        $this->status_code =
            $status_code;
    }


    public function getStatusCode(): int
    {
        return $this->status_code;
    }
}


/*
|--------------------------------------------------------------------------
| Unauthorized Exception
|--------------------------------------------------------------------------
*/

class UnauthorizedException
    extends HttpException
{
    public function __construct(
        string $message = 'Unauthorized.'
    ) {

        parent::__construct(
            $message,
            401
        );
    }
}


/*
|--------------------------------------------------------------------------
| Forbidden Exception
|--------------------------------------------------------------------------
*/

class ForbiddenException
    extends HttpException
{
    public function __construct(
        string $message = 'Forbidden.'
    ) {

        parent::__construct(
            $message,
            403
        );
    }
}


/*
|--------------------------------------------------------------------------
| Not Found Exception
|--------------------------------------------------------------------------
*/

class NotFoundException
    extends HttpException
{
    public function __construct(
        string $message = 'Resource not found.'
    ) {

        parent::__construct(
            $message,
            404
        );
    }
}
