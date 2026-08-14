<?php

/**
 * MASAR Error Handler
 *
 * Converts PHP errors into exceptions and provides
 * a consistent way to handle application errors.
 */


/*
|--------------------------------------------------------------------------
| Convert PHP Errors To Exceptions
|--------------------------------------------------------------------------
*/

function masar_error_handler(
    int $severity,
    string $message,
    string $file,
    int $line
): bool {

    /*
    |--------------------------------------------------------------------------
    | Respect Error Reporting
    |--------------------------------------------------------------------------
    */

    if (!(error_reporting() & $severity)) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Convert Error To Exception
    |--------------------------------------------------------------------------
    */

    throw new ErrorException(
        $message,
        0,
        $severity,
        $file,
        $line
    );
}


/*
|--------------------------------------------------------------------------
| Register Error Handler
|--------------------------------------------------------------------------
*/

function register_error_handler(): void
{
    set_error_handler(
        'masar_error_handler'
    );
}


/*
|--------------------------------------------------------------------------
| Restore Previous Error Handler
|--------------------------------------------------------------------------
*/

function restore_error_handler_safe(): void
{
    restore_error_handler();
}


/*
|--------------------------------------------------------------------------
| Check Production Environment
|--------------------------------------------------------------------------
*/

function error_is_production(): bool
{
    global $app_config;

    $environment = strtolower(trim((string) ($app_config['environment'] ?? 'production')));
    $debug = filter_var($app_config['debug'] ?? false, FILTER_VALIDATE_BOOLEAN);

    return $environment === 'production' || !$debug;
}


/*
|--------------------------------------------------------------------------
| Get Safe Error Message
|--------------------------------------------------------------------------
*/

function error_safe_message(
    Throwable $exception
): string {

    /*
    |--------------------------------------------------------------------------
    | Production
    |--------------------------------------------------------------------------
    */

    if (error_is_production()) {
        return 'An internal server error occurred.';
    }

    $message = trim($exception->getMessage());

    return $message !== ''
        ? $message
        : 'An unexpected error occurred.';
}


/*
|--------------------------------------------------------------------------
| Get Error Details
|--------------------------------------------------------------------------
|
| Used internally for logging.
|
*/

function error_details(
    Throwable $exception
): array {

    return [

        'type' =>
            get_class($exception),

        'message' =>
            $exception->getMessage(),

        'file' =>
            $exception->getFile(),

        'line' =>
            $exception->getLine(),

        'trace' =>
            $exception->getTraceAsString(),

    ];
}
