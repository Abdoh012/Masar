<?php

/**
 * MASAR - User Roles
 *
 * Centralized user role definitions.
 */

if (!defined('USER_ROLE_ADMIN')) {
    define(
        'USER_ROLE_ADMIN',
        'admin'
    );
}

if (!defined('USER_ROLE_STUDENT')) {
    define(
        'USER_ROLE_STUDENT',
        'student'
    );
}

if (!defined('USER_ROLE_COMPANY')) {
    define(
        'USER_ROLE_COMPANY',
        'company'
    );
}

if (!defined('USER_ROLE_TRAINER')) {
    define(
        'USER_ROLE_TRAINER',
        'trainer'
    );
}

if (!defined('USER_ROLE_SUPER_ADMIN')) {
    define(
        'USER_ROLE_SUPER_ADMIN',
        'super_admin'
    );
}

if (!defined('USER_ROLE_MODERATOR')) {
    define(
        'USER_ROLE_MODERATOR',
        'moderator'
    );
}

if (!defined('USER_ROLE_EMPLOYEE')) {
    define(
        'USER_ROLE_EMPLOYEE',
        'employee'
    );
}

if (!defined('USER_ROLE_GUEST')) {
    define(
        'USER_ROLE_GUEST',
        'guest'
    );
}

/*
|--------------------------------------------------------------------------
| Role Collection
|--------------------------------------------------------------------------
*/

function user_roles(): array
{
    return [
        USER_ROLE_ADMIN,
        USER_ROLE_STUDENT,
        USER_ROLE_COMPANY,
        USER_ROLE_TRAINER,
        USER_ROLE_SUPER_ADMIN,
        USER_ROLE_MODERATOR,
        USER_ROLE_EMPLOYEE,
        USER_ROLE_GUEST
    ];
}

/*
|--------------------------------------------------------------------------
| Role Labels
|--------------------------------------------------------------------------
*/

function user_role_labels(): array
{
    return [
        USER_ROLE_ADMIN =>
            'Administrator',

        USER_ROLE_STUDENT =>
            'Student',

        USER_ROLE_COMPANY =>
            'Company',

        USER_ROLE_TRAINER =>
            'Trainer',

        USER_ROLE_SUPER_ADMIN =>
            'Super Administrator',

        USER_ROLE_MODERATOR =>
            'Moderator',

        USER_ROLE_EMPLOYEE =>
            'Employee',

        USER_ROLE_GUEST =>
            'Guest'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_user_role(
    mixed $role
): bool {
    if (!is_string($role)) {
        return false;
    }

    return in_array(
        strtolower(trim($role)),
        user_roles(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_user_role(
    mixed $role
): ?string {
    if (!is_string($role)) {
        return null;
    }

    $role =
        strtolower(
            trim($role)
        );

    return is_valid_user_role($role)
        ? $role
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function user_role_label(
    mixed $role
): string {
    $role =
        normalize_user_role($role);

    if ($role === null) {
        return 'Unknown';
    }

    return
        user_role_labels()[$role]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Privilege Helpers
|--------------------------------------------------------------------------
*/

function is_admin_role(
    mixed $role
): bool {
    $role =
        normalize_user_role($role);

    return in_array(
        $role,
        [
            USER_ROLE_ADMIN,
            USER_ROLE_SUPER_ADMIN
        ],
        true
    );
}

function is_super_admin_role(
    mixed $role
): bool {
    return
        normalize_user_role($role)
        === USER_ROLE_SUPER_ADMIN;
}

function is_student_role(
    mixed $role
): bool {
    return
        normalize_user_role($role)
        === USER_ROLE_STUDENT;
}

function is_company_role(
    mixed $role
): bool {
    return
        normalize_user_role($role)
        === USER_ROLE_COMPANY;
}

function is_trainer_role(
    mixed $role
): bool {
    return
        normalize_user_role($role)
        === USER_ROLE_TRAINER;
}

/*
|--------------------------------------------------------------------------
| Role Groups
|--------------------------------------------------------------------------
*/

function privileged_user_roles(): array
{
    return [
        USER_ROLE_ADMIN,
        USER_ROLE_SUPER_ADMIN,
        USER_ROLE_MODERATOR
    ];
}

function business_user_roles(): array
{
    return [
        USER_ROLE_COMPANY,
        USER_ROLE_EMPLOYEE
    ];
}

function platform_user_roles(): array
{
    return [
        USER_ROLE_STUDENT,
        USER_ROLE_COMPANY,
        USER_ROLE_TRAINER,
        USER_ROLE_EMPLOYEE
    ];
}
