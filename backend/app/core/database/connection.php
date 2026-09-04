<?php

/**
 * MASAR Database Connection
 *
 * Responsible for creating and returning the PDO connection.
 */


/*
|--------------------------------------------------------------------------
| Load Database Configuration
|--------------------------------------------------------------------------
*/

$database_config = require __DIR__ . '/../../config/database.php';


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

function get_database_connection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    global $database_config;

    $host = $database_config['host'];
    $port = $database_config['port'];
    $database = $database_config['database'];
    $username = $database_config['username'];
    $password = $database_config['password'];
    $charset = $database_config['charset'];

    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s;charset=%s',
        $database_config['driver'],
        $host,
        $port,
        $database,
        $charset
    );

    try {

        $connection = new PDO(
            $dsn,
            $username,
            $password,
            $database_config['options']
        );

        return $connection;

    } catch (PDOException $exception) {

        /*
        |--------------------------------------------------------------------------
        | Database Connection Error
        |--------------------------------------------------------------------------
        |
        | Do not expose database credentials or internal errors
        | to the frontend.
        |
        */

        error_log(
            'MASAR Database Connection Error: ' .
            $exception->getMessage()
        );

        throw new RuntimeException(
            'Database connection failed.'
        );
    }
}