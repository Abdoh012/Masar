<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/modules/training/repositories/training_repository.php';
require_once BASE . 'app/modules/training/services/training_service.php';
require_once BASE . 'app/modules/training/repositories/application_repository.php';
require_once BASE . 'app/modules/training/services/application_service.php';

$q = "SELECT a.id AS app_id, a.status AS app_status, a.training_id,
             t.ends_at, t.application_deadline, t.status AS t_status
      FROM training_applications a
      JOIN training_listings t ON t.id = a.training_id
      WHERE a.id IN (1988, 1900, 1901, 1902) ORDER BY a.id";
$rows = db_fetch_all($q);
foreach ($rows as $r) {
    echo "app_id={$r['app_id']} app_status={$r['app_status']} training={$r['training_id']} "
         . "ends_at={$r['ends_at']} deadline={$r['application_deadline']} t_status={$r['t_status']}\n";
}

echo "--- NOW ---\n";
echo db_fetch_value("SELECT NOW()") . "\n";

echo "--- ends_at-based public hiding (new rule A) ---\n";
$now = db_fetch_value("SELECT NOW()");
echo "is end-visible for each row:\n";
foreach ($rows as $r) {
    $ve = ($r['ends_at'] === null || $r['ends_at'] >= $now) ? 'VISIBLE' : 'HIDDEN(x-ends)';
    $vd = ($r['application_deadline'] === null || $r['application_deadline'] >= $now) ? 'VISIBLE' : 'HIDDEN(x-deadline)';
    echo "  app#{$r['app_id']} -> ends_at:{$ve} | deadline:{$vd}\n";
}
