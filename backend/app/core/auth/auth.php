<?php

/**
 * MASAR Authentication Helper
 *
 * Responsible for managing the authenticated user
 * during the current HTTP request.
 *
 * Token generation and token validation are handled
 * separately inside token.php.
 */

require_once __DIR__ . '/../database/query.php';
require_once __DIR__ . '/../http/request.php';

$GLOBALS['authenticated_user'] = null;

function auth_user(): ?array
{
    return $GLOBALS['authenticated_user'];
}

function auth_set_user(?array $user): void
{
    $GLOBALS['authenticated_user'] = $user;
}

function auth_logout(): void
{
    $GLOBALS['authenticated_user'] = null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_guest(): bool
{
    return !auth_check();
}

function auth_id(): ?int
{
    $user = auth_user();

    if ($user === null) {
        return null;
    }

    return isset($user['id']) ? (int) $user['id'] : null;
}

function auth_role(): ?string
{
    $user = auth_user();

    if ($user === null) {
        return null;
    }

    return $user['role'] ?? null;
}

function auth_has_role(string $role): bool
{
    return auth_role() === $role;
}

function auth_is_student(): bool
{
    return is_student_role(auth_role());
}

function auth_is_company(): bool
{
    return is_company_role(auth_role());
}

function auth_is_admin(): bool
{
    return is_admin_role(auth_role());
}

function auth_find_user_by_id(int $user_id): ?array
{
    return db_fetch_one('SELECT id, email, role, status, created_at, updated_at FROM users WHERE id = :id LIMIT 1 ', ['id' => $user_id]);
}

function auth_find_user_by_email(string $email): ?array
{
    return db_fetch_one(' SELECT id, email, password_hash, role, status, created_at, updated_at FROM users WHERE email = :email LIMIT 1 ', ['email' => $email]);
}

function auth_user_is_active(?array $user = null): bool
{
    $user ??= auth_user();

    if ($user === null) {
        return false;
    }

    return user_status_allows_login($user['status'] ?? null);
}

function auth_login_user(int $user_id): bool
{
    $user = auth_find_user_by_id($user_id);

    if ($user === null) {
        return false;
    }

    if (! auth_user_is_active($user)) {
        return false;
    }

    auth_set_user($user);
    return true;
}

function auth_require(): array
{
    $user = auth_user();

    if ($user === null) {
        throw new RuntimeException('Authentication required.');
    }

    return $user;
}

function auth_require_active(): array
{
    $user = auth_require();

    if (! auth_user_is_active($user)) {
        throw new RuntimeException('User account is not active.');
    }

    return $user;
}

function auth_require_role(string $role): array
{

    $user = auth_require();

    if (($user['role'] ?? null) !== $role) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_require_student(): array
{
    $user = auth_require();

    if (!is_student_role($user['role'] ?? null)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_require_company(): array
{
    $user = auth_require();

    if (!is_company_role($user['role'] ?? null)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_require_admin(): array
{
    $user = auth_require();

    if (!is_admin_role($user['role'] ?? null)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_require_super_admin(): array
{
    $user = auth_require();

    if (!is_super_admin_role($user['role'] ?? null)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_email(): ?string
{
    $user = auth_user();

    if ($user === null) {
        return null;
    }

    return $user['email'] ?? null;
}

function auth_can(string $permission): bool
{
    $user = auth_user();

    if ($user === null) {
        return false;
    }

    return auth_user_has_permission($user, $permission);
}

function auth_require_permission(string $permission): array
{
    $user = auth_require();

    if (!auth_user_has_permission($user, $permission)) {
        throw new RuntimeException('You do not have permission to perform this action.');
    }

    return $user;
}

function auth_verified(): bool
{
    $user = auth_user();

    if ($user === null) {
        return false;
    }

    return !empty($user['email_verified_at'] ?? null);
}

function auth_require_verified(): array
{
    $user = auth_require();

    if (empty($user['email_verified_at'] ?? null)) {
        throw new RuntimeException('Email verification required.');
    }

    return $user;
}
