<?php

require_once __DIR__ . '/../../core/database/query.php';

function audit_log_event(
    string $action,
    string $entity_type,
    mixed $entity_id = null,
    ?array $old_values = null,
    ?array $new_values = null,
    ?array $user = null
): void {
    try {
        $user_id = null;
        if (is_array($user) && !empty($user['id'])) {
            $user_id = (int) $user['id'];
        } elseif (function_exists('auth_user') && is_array(auth_user()) && !empty(auth_user()['id'])) {
            $user_id = (int) auth_user()['id'];
        }

        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        db_execute(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)',
            [
                'user_id' => $user_id,
                'action' => $action,
                'entity_type' => $entity_type,
                'entity_id' => $entity_id !== null ? (int) $entity_id : null,
                'old_values' => $old_values !== null ? json_encode($old_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'new_values' => $new_values !== null ? json_encode($new_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
            ]
        );
    } catch (Throwable $exception) {
        if (function_exists('logger_security')) {
            logger_security('audit_log_failed', [
                'action' => $action,
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}

function audit_log_user_action(
    string $action,
    string $entity_type,
    mixed $entity_id = null,
    ?array $old_values = null,
    ?array $new_values = null,
    ?array $user = null
): void {
    audit_log_event($action, $entity_type, $entity_id, $old_values, $new_values, $user ?? (function_exists('auth_user') ? auth_user() : null));
}
