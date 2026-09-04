<?php

/**
 * MASAR Database Query Helper
 *
 * Provides reusable functions for executing database queries
 * using PDO.
 */


/*
|--------------------------------------------------------------------------
| Load Database Connection
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/connection.php';


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
|
| Used for INSERT, UPDATE, DELETE and other SQL statements.
|
*/

function db_execute(
    string $sql,
    array $params = []
): PDOStatement {

    $db = get_database_connection();

    try {

        $statement = $db->prepare($sql);

        $statement->execute($params);

        return $statement;

    } catch (PDOException $exception) {

        error_log(
            'MASAR Database Query Error: ' .
            $exception->getMessage()
        );

        throw new RuntimeException(
            'Database query failed.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Fetch One
|--------------------------------------------------------------------------
|
| Returns one database record or false if nothing was found.
|
*/

function db_fetch_one(
    string $sql,
    array $params = []
): ?array {

    $statement = db_execute(
        $sql,
        $params
    );

    $row = $statement->fetch();

    return $row === false
        ? null
        : $row;
}


/*
|--------------------------------------------------------------------------
| Fetch All
|--------------------------------------------------------------------------
|
| Returns all matching records.
|
*/

function db_fetch_all(
    string $sql,
    array $params = []
): array {

    $statement = db_execute(
        $sql,
        $params
    );

    return $statement->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Fetch Column
|--------------------------------------------------------------------------
|
| Returns a single column from the first matching record.
|
*/

function db_fetch_column(
    string $sql,
    array $params = []
): mixed {

    $statement = db_execute(
        $sql,
        $params
    );

    return $statement->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Insert ID
|--------------------------------------------------------------------------
|
| Returns the last inserted auto-increment ID.
|
*/

function db_last_insert_id(): string {

    return get_database_connection()
        ->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| Row Count
|--------------------------------------------------------------------------
|
| Returns the number of affected rows.
|
*/

function db_row_count(
    PDOStatement $statement
): int {

    return $statement->rowCount();
}

function db_transaction(callable $callback): mixed
{
    $db = get_database_connection();

    if ($db->inTransaction()) {
        return $callback($db);
    }

    $db->beginTransaction();

    try {
        $result = $callback($db);
        $db->commit();

        return $result;
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        throw $exception;
    }
}