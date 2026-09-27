<?php

/**
 * MASAR PHPUnit bootstrap.
 *
 * Loads the Composer autoloader and, when present, the local .env file so
 * unit tests can rely on the same configuration sources as the application.
 * This mirrors the bootstrap shim used by the standalone endpoint runners
 * under tests/.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__))->safeLoad();
}
