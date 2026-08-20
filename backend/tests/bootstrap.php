<?php

/**
 * MASAR Backend - PHPUnit bootstrap.
 *
 * Loads the shared config and the procedural modules under test. The tests
 * run against the local development MySQL database (see app/config/database.php).
 */

require_once __DIR__ . '/../app/config/constants.php';

$database_config = require __DIR__ . '/../app/config/database.php';
$GLOBALS['database_config'] = $database_config;

require_once __DIR__ . '/../app/core/database/connection.php';
require_once __DIR__ . '/../app/core/database/query.php';
require_once __DIR__ . '/../app/core/database/transaction.php';

require_once __DIR__ . '/../app/modules/students/repositories/student_repository.php';
require_once __DIR__ . '/../app/modules/students/repositories/student_profile_repository.php';
require_once __DIR__ . '/../app/modules/students/services/student_profile_service.php';

require_once __DIR__ . '/../app/modules/files/repositories/file_repository.php';
require_once __DIR__ . '/../app/modules/files/services/file_upload_service.php';