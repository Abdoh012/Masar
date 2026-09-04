<?php

/**
 * MASAR Logger
 *
 * Responsible for recording application errors,
 * exceptions and important system events.
 */


/*
|--------------------------------------------------------------------------
| Logger Configuration
|--------------------------------------------------------------------------
*/

function logger_config(): array
{
    global $app_config;

    return $app_config['logging']
        ?? [
            'enabled' => true,
            'path' => __DIR__ . '/../../../storage/logs',
            'file' => 'app.log',
        ];
}


/*
|--------------------------------------------------------------------------
| Ensure Log Directory
|--------------------------------------------------------------------------
*/

function logger_ensure_directory(): bool
{
    $config = logger_config();

    $path = $config['path'];

    if (is_dir($path)) {
        return true;
    }

    return mkdir(
        $path,
        0755,
        true
    );
}


/*
|--------------------------------------------------------------------------
| Get Log File
|--------------------------------------------------------------------------
*/

function logger_file(): string
{
    $config = logger_config();

    logger_ensure_directory();

    return rtrim(
        $config['path'],
        DIRECTORY_SEPARATOR
    )
    . DIRECTORY_SEPARATOR
    . ($config['file'] ?? 'app.log');
}


/*
|--------------------------------------------------------------------------
| Write Log
|--------------------------------------------------------------------------
*/

function logger_write(
    string $level,
    string $message,
    array $context = []
): void {

    $config = logger_config();

    if (
        isset($config['enabled'])
        && !$config['enabled']
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Timestamp
    |--------------------------------------------------------------------------
    */

    $timestamp = date(
        'Y-m-d H:i:s'
    );


    /*
    |--------------------------------------------------------------------------
    | Request Information
    |--------------------------------------------------------------------------
    */

    $request_method =
        $_SERVER['REQUEST_METHOD']
        ?? 'CLI';

    $request_uri =
        $_SERVER['REQUEST_URI']
        ?? '';


    $ip_address =
        $_SERVER['REMOTE_ADDR']
        ?? '';


    /*
    |--------------------------------------------------------------------------
    | Context
    |--------------------------------------------------------------------------
    */

    $log_data = [

        'timestamp' =>
            $timestamp,

        'level' =>
            strtoupper($level),

        'message' =>
            $message,

        'request' => [

            'method' =>
                $request_method,

            'uri' =>
                $request_uri,

            'ip' =>
                $ip_address,

        ],

        'context' =>
            $context,

    ];


    /*
    |--------------------------------------------------------------------------
    | Convert To JSON
    |--------------------------------------------------------------------------
    */

    $log_line = json_encode(
        $log_data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );


    /*
    |--------------------------------------------------------------------------
    | Write Log
    |--------------------------------------------------------------------------
    */

    file_put_contents(
        logger_file(),
        $log_line . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}


/*
|--------------------------------------------------------------------------
| Debug
|--------------------------------------------------------------------------
*/

function logger_debug(
    string $message,
    array $context = []
): void {

    logger_write(
        'debug',
        $message,
        $context
    );
}


/*
|--------------------------------------------------------------------------
| Info
|--------------------------------------------------------------------------
*/

function logger_info(
    string $message,
    array $context = []
): void {

    logger_write(
        'info',
        $message,
        $context
    );
}


/*
|--------------------------------------------------------------------------
| Warning
|--------------------------------------------------------------------------
*/

function logger_warning(
    string $message,
    array $context = []
): void {

    logger_write(
        'warning',
        $message,
        $context
    );
}


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

function logger_error(
    string $message,
    array $context = []
): void {

    logger_write(
        'error',
        $message,
        $context
    );
}


/*
|--------------------------------------------------------------------------
| Exception
|--------------------------------------------------------------------------
*/

function logger_exception(
    Throwable $exception,
    array $context = []
): void {

    $exception_context = [

        'exception' => [
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
        ],

        'context' =>
            $context,
    ];


    logger_write(
        'error',
        $exception->getMessage(),
        $exception_context
    );
}


/*
|--------------------------------------------------------------------------
| Security Log
|--------------------------------------------------------------------------
*/

function logger_security(
    string $message,
    array $context = []
): void {

    logger_write(
        'security',
        $message,
        $context
    );
}


/*
|--------------------------------------------------------------------------
| Database Error
|--------------------------------------------------------------------------
*/

function logger_database_error(
    string $message,
    array $context = []
): void {

    logger_write(
        'database',
        $message,
        $context
    );
}
