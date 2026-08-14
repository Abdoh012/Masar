<?php

/**
 * MASAR - Company Statuses
 *
 * Centralized company status definitions.
 */

if (!defined('COMPANY_STATUS_PENDING')) {
    define(
        'COMPANY_STATUS_PENDING',
        'pending'
    );
}

if (!defined('COMPANY_STATUS_ACTIVE')) {
    define(
        'COMPANY_STATUS_ACTIVE',
        'active'
    );
}

if (!defined('COMPANY_STATUS_INACTIVE')) {
    define(
        'COMPANY_STATUS_INACTIVE',
        'inactive'
    );
}

if (!defined('COMPANY_STATUS_SUSPENDED')) {
    define(
        'COMPANY_STATUS_SUSPENDED',
        'suspended'
    );
}

if (!defined('COMPANY_STATUS_BLOCKED')) {
    define(
        'COMPANY_STATUS_BLOCKED',
        'blocked'
    );
}

if (!defined('COMPANY_STATUS_REJECTED')) {
    define(
        'COMPANY_STATUS_REJECTED',
        'rejected'
    );
}

if (!defined('COMPANY_STATUS_DELETED')) {
    define(
        'COMPANY_STATUS_DELETED',
        'deleted'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function company_statuses(): array
{
    return [
        COMPANY_STATUS_PENDING,
        COMPANY_STATUS_ACTIVE,
        COMPANY_STATUS_INACTIVE,
        COMPANY_STATUS_SUSPENDED,
        COMPANY_STATUS_BLOCKED,
        COMPANY_STATUS_REJECTED,
        COMPANY_STATUS_DELETED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function company_status_labels(): array
{
    return [
        COMPANY_STATUS_PENDING =>
            'Pending',

        COMPANY_STATUS_ACTIVE =>
            'Active',

        COMPANY_STATUS_INACTIVE =>
            'Inactive',

        COMPANY_STATUS_SUSPENDED =>
            'Suspended',

        COMPANY_STATUS_BLOCKED =>
            'Blocked',

        COMPANY_STATUS_REJECTED =>
            'Rejected',

        COMPANY_STATUS_DELETED =>
            'Deleted'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_company_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        company_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_company_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_company_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function company_status_label(
    mixed $status
): string {
    $status =
        normalize_company_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        company_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_active_company_status(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_ACTIVE;
}

function is_pending_company_status(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_PENDING;
}

function is_suspended_company_status(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_SUSPENDED;
}

function is_blocked_company_status(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_BLOCKED;
}

function is_deleted_company_status(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_DELETED;
}

/*
|--------------------------------------------------------------------------
| Access Helpers
|--------------------------------------------------------------------------
*/

function company_status_allows_access(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_ACTIVE;
}

function company_status_is_disabled(
    mixed $status
): bool {
    return in_array(
        normalize_company_status($status),
        [
            COMPANY_STATUS_INACTIVE,
            COMPANY_STATUS_SUSPENDED,
            COMPANY_STATUS_BLOCKED,
            COMPANY_STATUS_REJECTED,
            COMPANY_STATUS_DELETED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function active_company_statuses(): array
{
    return [
        COMPANY_STATUS_ACTIVE
    ];
}

function inactive_company_statuses(): array
{
    return [
        COMPANY_STATUS_INACTIVE,
        COMPANY_STATUS_SUSPENDED,
        COMPANY_STATUS_BLOCKED
    ];
}

function terminal_company_statuses(): array
{
    return [
        COMPANY_STATUS_REJECTED,
        COMPANY_STATUS_DELETED
    ];
}

/*
|--------------------------------------------------------------------------
| Registration / Approval Helpers
|--------------------------------------------------------------------------
*/

function company_status_is_reviewable(
    mixed $status
): bool {
    return
        normalize_company_status($status)
        === COMPANY_STATUS_PENDING;
}

function company_status_is_final(
    mixed $status
): bool {
    return in_array(
        normalize_company_status($status),
        [
            COMPANY_STATUS_ACTIVE,
            COMPANY_STATUS_REJECTED,
            COMPANY_STATUS_DELETED
        ],
        true
    );
}
