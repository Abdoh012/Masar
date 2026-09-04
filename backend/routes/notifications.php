<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/core/middleware/auth.php';
require_once __DIR__ . '/../app/modules/notifications/controllers/notification_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/notifications' && $method === 'GET') {
    middleware_auth();
    notification_controller_send( notification_controller_index() );
    return;
}

if ($path === '/api/v1/notifications/unread-count' && $method === 'GET') {
    middleware_auth();
    notification_controller_send( notification_controller_unread_count() );
    return;
}

if ($path === '/api/v1/notifications/read-all' && $method === 'POST') {
    middleware_auth();
    notification_controller_send( notification_controller_mark_all_read() );
    return;
}

if (preg_match('#^/api/v1/notifications/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    middleware_auth();
    notification_controller_send( notification_controller_show((int) $matches[1]) );
    return;
}

if (preg_match('#^/api/v1/notifications/([0-9]+)/read$#', $path, $matches) && $method === 'POST') {
    middleware_auth();
    notification_controller_send( notification_controller_mark_read((int) $matches[1]) );
    return;
}

if (preg_match('#^/api/v1/notifications/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    middleware_auth();
    notification_controller_send( notification_controller_delete((int) $matches[1]) );
    return;
}

response_not_found('Notification endpoint not found.');
