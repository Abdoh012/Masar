<?php

/**
 * MASAR Database Transaction Helper
 *
 * Provides reusable functions for handling database transactions.
 */

require_once __DIR__ . '/connection.php';


/*
|--------------------------------------------------------------------------
| Begin Transaction
|--------------------------------------------------------------------------
*/

function db_begin_transaction(): void
{
    $db = get_database_connection();

    if (!$db->inTransaction()) {
        $db->beginTransaction();
    }
}


/*
|--------------------------------------------------------------------------
| Commit Transaction
|--------------------------------------------------------------------------
*/

function db_commit(): void
{
    $db = get_database_connection();

    if ($db->inTransaction()) {
        $db->commit();
    }
}


/*
|--------------------------------------------------------------------------
| Rollback Transaction
|--------------------------------------------------------------------------
*/

function db_rollback(): void
{
    $db = get_database_connection();

    if ($db->inTransaction()) {
        $db->rollBack();
    }
}


/*
|--------------------------------------------------------------------------
| Transaction Status
|--------------------------------------------------------------------------
*/

function db_in_transaction(): bool
{
    return get_database_connection()->inTransaction();
}