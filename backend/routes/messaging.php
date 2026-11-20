<?php

$app_config = require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/messaging/controllers/conversation_controller.php';
require_once __DIR__ . '/../app/modules/messaging/controllers/message_controller.php';

$path = request_path();
$method = request_method();

if ($path === '/api/v1/conversations' && $method === 'GET') {
    conversation_controller_index();
    return;
}

if ($path === '/api/v1/conversations' && $method === 'POST') {
    conversation_controller_create();
    return;
}

if (preg_match('#^/api/v1/conversations/([0-9]+)/messages$#', $path, $matches) && $method === 'GET') {
    message_controller_index((int) $matches[1]);
    return;
}

if (preg_match('#^/api/v1/conversations/([0-9]+)/messages$#', $path, $matches) && $method === 'POST') {
    message_controller_create((int) $matches[1]);
    return;
}

if (preg_match('#^/api/v1/conversations/([0-9]+)/messages/([0-9]+)$#', $path, $matches) && $method === 'PATCH') {
    message_controller_edit((int) $matches[1], (int) $matches[2]);
    return;
}

if (preg_match('#^/api/v1/conversations/([0-9]+)/messages/([0-9]+)$#', $path, $matches) && $method === 'DELETE') {
    message_controller_delete_message((int) $matches[1], (int) $matches[2]);
    return;
}

response_not_found('Messaging endpoint not found.');
