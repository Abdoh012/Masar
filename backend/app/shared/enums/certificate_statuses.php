<?php

/**
 * MASAR - Certificate Statuses
 *
 * Centralized certificate status definitions.
 */

if (!defined('CERTIFICATE_STATUS_PENDING')) {
    define(
        'CERTIFICATE_STATUS_PENDING',
        'pending'
    );
}

if (!defined('CERTIFICATE_STATUS_ISSUED')) {
    define(
        'CERTIFICATE_STATUS_ISSUED',
        'issued'
    );
}

if (!defined('CERTIFICATE_STATUS_ACTIVE')) {
    define(
        'CERTIFICATE_STATUS_ACTIVE',
        'active'
    );
}

if (!defined('CERTIFICATE_STATUS_REVOKED')) {
    define(
        'CERTIFICATE_STATUS_REVOKED',
        'revoked'
    );
}

if (!defined('CERTIFICATE_STATUS_EXPIRED')) {
    define(
        'CERTIFICATE_STATUS_EXPIRED',
        'expired'
    );
}

if (!defined('CERTIFICATE_STATUS_CANCELLED')) {
    define(
        'CERTIFICATE_STATUS_CANCELLED',
        'cancelled'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function certificate_statuses(): array
{
    return [
        CERTIFICATE_STATUS_PENDING,
        CERTIFICATE_STATUS_ISSUED,
        CERTIFICATE_STATUS_ACTIVE,
        CERTIFICATE_STATUS_REVOKED,
        CERTIFICATE_STATUS_EXPIRED,
        CERTIFICATE_STATUS_CANCELLED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function certificate_status_labels(): array
{
    return [
        CERTIFICATE_STATUS_PENDING =>
            'Pending',

        CERTIFICATE_STATUS_ISSUED =>
            'Issued',

        CERTIFICATE_STATUS_ACTIVE =>
            'Active',

        CERTIFICATE_STATUS_REVOKED =>
            'Revoked',

        CERTIFICATE_STATUS_EXPIRED =>
            'Expired',

        CERTIFICATE_STATUS_CANCELLED =>
            'Cancelled'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_certificate_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        certificate_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_certificate_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_certificate_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function certificate_status_label(
    mixed $status
): string {
    $status =
        normalize_certificate_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        certificate_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_pending_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_PENDING;
}

function is_issued_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_ISSUED;
}

function is_active_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_ACTIVE;
}

function is_revoked_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_REVOKED;
}

function is_expired_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_EXPIRED;
}

function is_cancelled_certificate_status(
    mixed $status
): bool {
    return
        normalize_certificate_status($status)
        === CERTIFICATE_STATUS_CANCELLED;
}

/*
|--------------------------------------------------------------------------
| Workflow Helpers
|--------------------------------------------------------------------------
*/

function certificate_status_is_valid(
    mixed $status
): bool {
    return in_array(
        normalize_certificate_status($status),
        [
            CERTIFICATE_STATUS_ISSUED,
            CERTIFICATE_STATUS_ACTIVE
        ],
        true
    );
}

function certificate_status_is_terminal(
    mixed $status
): bool {
    return in_array(
        normalize_certificate_status($status),
        [
            CERTIFICATE_STATUS_REVOKED,
            CERTIFICATE_STATUS_EXPIRED,
            CERTIFICATE_STATUS_CANCELLED
        ],
        true
    );
}

function certificate_status_allows_verification(
    mixed $status
): bool {
    return in_array(
        normalize_certificate_status($status),
        [
            CERTIFICATE_STATUS_ISSUED,
            CERTIFICATE_STATUS_ACTIVE
        ],
        true
    );
}

function certificate_status_allows_appeal(
    mixed $status
): bool {
    return in_array(
        normalize_certificate_status($status),
        [
            CERTIFICATE_STATUS_ISSUED,
            CERTIFICATE_STATUS_ACTIVE,
            CERTIFICATE_STATUS_REVOKED,
            CERTIFICATE_STATUS_EXPIRED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function certificate_active_statuses(): array
{
    return [
        CERTIFICATE_STATUS_ISSUED,
        CERTIFICATE_STATUS_ACTIVE
    ];
}

function certificate_terminal_statuses(): array
{
    return [
        CERTIFICATE_STATUS_REVOKED,
        CERTIFICATE_STATUS_EXPIRED,
        CERTIFICATE_STATUS_CANCELLED
    ];
}

function certificate_verifiable_statuses(): array
{
    return [
        CERTIFICATE_STATUS_ISSUED,
        CERTIFICATE_STATUS_ACTIVE
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_certificate_statuses(): array
{
    return certificate_statuses();
}

function get_certificate_status_label(
    mixed $status
): string {
    return certificate_status_label($status);
}
