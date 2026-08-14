<?php

/**
 * MASAR Authorization Helpers
 *
 * Centralized RBAC and ownership checks.
 */

function auth_role_permissions(): array
{
    return [
        USER_ROLE_STUDENT => [
            'student:read',
            'student:update',
            'training:apply',
            'application:read',
            'application:create',
            'message:read',
            'message:create',
            'file:upload',
            'file:download:own',
        ],
        USER_ROLE_COMPANY => [
            'company:read',
            'company:update',
            'training:create',
            'training:update:own',
            'training:publish',
            'application:review:own',
            'message:read',
            'message:create',
            'file:upload',
            'file:download:own',
        ],
        USER_ROLE_ADMIN => [
            'admin:read',
            'admin:update',
            'admin:approve',
            'admin:reject',
            'training:approve',
            'application:review',
            'company:approve',
            'certificate:issue',
            'user:manage',
            'message:read',
            'message:create',
            'file:upload',
            'file:download:any',
        ],
    ];
}

function auth_user_has_permission(?array $user, string $permission): bool
{
    if (!is_array($user)) {
        return false;
    }

    $role = strtolower((string) ($user['role'] ?? ''));
    $allowed = auth_role_permissions()[$role] ?? [];

    return in_array($permission, $allowed, true);
}

function auth_user_has_role(?array $user, string $role): bool
{
    if (!is_array($user)) {
        return false;
    }

    return strtolower((string) ($user['role'] ?? '')) === strtolower($role);
}

function auth_user_can_access_resource(?array $user, int $owner_user_id, string $resource_name = 'resource'): bool
{
    if (!is_array($user)) {
        return false;
    }

    if (is_admin_role($user['role'] ?? null)) {
        return true;
    }

    $current_user_id = (int) ($user['id'] ?? 0);
    if ($current_user_id <= 0) {
        return false;
    }

    if ($current_user_id === (int) $owner_user_id) {
        return true;
    }

    return false;
}

function auth_require_ownership(?array $user, int $owner_user_id, string $resource_name = 'resource'): bool
{
    if (!auth_user_can_access_resource($user, $owner_user_id, $resource_name)) {
        response_forbidden('You do not have permission to access this ' . trim($resource_name) . '.');
    }

    return true;
}
