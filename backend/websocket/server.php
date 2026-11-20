<?php

/**
 * MASAR - WebSocket server entry point
 *
 * Usage (from backend/):
 *   php websocket/server.php
 *
 * Configuration is read from the environment (see .env.example):
 *   WS_HOST, WS_PORT, WS_MAX_CONNECTIONS, WS_PING_INTERVAL,
 *   WS_OUTBOX_INTERVAL_MS, WS_OUTBOX_BATCH
 *
 * Run under a process supervisor in production. It is CLI-only and cannot be
 * started through Apache.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';

if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

$app_config = require BASE . 'app/config/app.php';

require_once BASE . 'app/config/constants.php';
require_once BASE . 'app/core/database/query.php';
require_once BASE . 'app/core/auth/auth.php';
require_once BASE . 'app/services/jwt_service.php';
require_once BASE . 'app/modules/messaging/services/conversation_service.php';
require_once BASE . 'app/modules/messaging/websocket/websocket_server.php';

ws_server_run(
    ws_server_config()
);
