<?php

/**
 * MASAR - User Statuses
 *
 * Centralized user account status definitions.
 */

if (!defined('USER_STATUS_PENDING')) {
    define(
        'USER_STATUS_PENDING',
        'pending'
    );
}

if (!defined('USER_STATUS_ACTIVE')) {
    define(
        'USER_STATUS_ACTIVE',
        'active'
    );
}

if (!defined('USER_STATUS_INACTIVE')) {
    define(
        'USER_STATUS_INACTIVE',
        'inactive'
    );
}

if (!defined('USER_STATUS_SUSPENDED')) {
    define(
        'USER_STATUS_SUSPENDED',
        'suspended'
    );
}

if (!defined('USER_STATUS_BLOCKED')) {
    define(
        'USER_STATUS_BLOCKED',
        'blocked'
    );
}

if (!defined('USER_STATUS_REJECTED')) {
    define(
        'USER_STATUS_REJECTED',
        'rejected'
    );
}

if (!defined('USER_STATUS_DELETED')) {
    define(
        'USER_STATUS_DELETED',
        'deleted'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function user_statuses(): array
{
    return [
        USER_STATUS_PENDING,
        USER_STATUS_ACTIVE,
        USER_STATUS_INACTIVE,
        USER_STATUS_SUSPENDED,
        USER_STATUS_BLOCKED,
        USER_STATUS_REJECTED,
        USER_STATUS_DELETED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function user_status_labels(): array
{
    return [
        USER_STATUS_PENDING =>
            'Pending',

        USER_STATUS_ACTIVE =>
            'Active',

        USER_STATUS_INACTIVE =>
            'Inactive',

        USER_STATUS_SUSPENDED =>
            'Suspended',

        USER_STATUS_BLOCKED =>
            'Blocked',

        USER_STATUS_REJECTED =>
            'Rejected',

        USER_STATUS_DELETED =>
            'Deleted'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_user_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        user_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_user_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_user_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function user_status_label(
    mixed $status
): string {
    $status =
        normalize_user_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        user_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_active_user_status(
    mixed $status
): bool {
    return
        normalize_user_status($status)
        === USER_STATUS_ACTIVE;
}

function is_pending_user_status(
    mixed $status
): bool {
    return
        normalize_user_status($status)
        === USER_STATUS_PENDING;
}

function is_suspended_user_status(
    mixed $status
): bool {
    return
        normalize_user_status($status)
        === USER_STATUS_SUSPENDED;
}

function is_blocked_user_status(
    mixed $status
): bool {
    return
        normalize_user_status($status)
        === USER_STATUS_BLOCKED;
}

function is_deleted_user_status(
    mixed $status
): bool {
    return
        normalize_user_status($status)
        === USER_STATUS_DELETED;
}

/*
|--------------------------------------------------------------------------
| Access Helpers
|--------------------------------------------------------------------------
*/

function user_status_allows_login(
    mixed $status
): bool {
    return in_array(
        normalize_user_status($status),
        [
            USER_STATUS_ACTIVE
        ],
        true
    );
}

function user_status_is_disabled(
    mixed $status
): bool {
    return in_array(
        normalize_user_status($status),
        [
            USER_STATUS_INACTIVE,
            USER_STATUS_SUSPENDED,
            USER_STATUS_BLOCKED,
            USER_STATUS_REJECTED,
            USER_STATUS_DELETED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function active_user_statuses(): array
{
    return [
        USER_STATUS_ACTIVE
    ];
}

function inactive_user_statuses(): array
{
    return [
        USER_STATUS_INACTIVE,
        USER_STATUS_SUSPENDED,
        USER_STATUS_BLOCKED
    ];
}

function terminal_user_statuses(): array
{
    return [
        USER_STATUS_REJECTED,
        USER_STATUS_DELETED
    ];
}
