<?php

/**
 * MASAR - Payment Statuses
 *
 * Centralized payment status definitions.
 */

if (!defined('PAYMENT_STATUS_PENDING')) {
    define(
        'PAYMENT_STATUS_PENDING',
        'pending'
    );
}

if (!defined('PAYMENT_STATUS_PROCESSING')) {
    define(
        'PAYMENT_STATUS_PROCESSING',
        'processing'
    );
}

if (!defined('PAYMENT_STATUS_PAID')) {
    define(
        'PAYMENT_STATUS_PAID',
        'paid'
    );
}

if (!defined('PAYMENT_STATUS_FAILED')) {
    define(
        'PAYMENT_STATUS_FAILED',
        'failed'
    );
}

if (!defined('PAYMENT_STATUS_CANCELLED')) {
    define(
        'PAYMENT_STATUS_CANCELLED',
        'cancelled'
    );
}

if (!defined('PAYMENT_STATUS_REFUNDED')) {
    define(
        'PAYMENT_STATUS_REFUNDED',
        'refunded'
    );
}

if (!defined('PAYMENT_STATUS_PARTIALLY_REFUNDED')) {
    define(
        'PAYMENT_STATUS_PARTIALLY_REFUNDED',
        'partially_refunded'
    );
}

if (!defined('PAYMENT_STATUS_EXPIRED')) {
    define(
        'PAYMENT_STATUS_EXPIRED',
        'expired'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function payment_statuses(): array
{
    return [
        PAYMENT_STATUS_PENDING,
        PAYMENT_STATUS_PROCESSING,
        PAYMENT_STATUS_PAID,
        PAYMENT_STATUS_FAILED,
        PAYMENT_STATUS_CANCELLED,
        PAYMENT_STATUS_REFUNDED,
        PAYMENT_STATUS_PARTIALLY_REFUNDED,
        PAYMENT_STATUS_EXPIRED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function payment_status_labels(): array
{
    return [
        PAYMENT_STATUS_PENDING =>
            'Pending',

        PAYMENT_STATUS_PROCESSING =>
            'Processing',

        PAYMENT_STATUS_PAID =>
            'Paid',

        PAYMENT_STATUS_FAILED =>
            'Failed',

        PAYMENT_STATUS_CANCELLED =>
            'Cancelled',

        PAYMENT_STATUS_REFUNDED =>
            'Refunded',

        PAYMENT_STATUS_PARTIALLY_REFUNDED =>
            'Partially Refunded',

        PAYMENT_STATUS_EXPIRED =>
            'Expired'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_payment_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        payment_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_payment_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_payment_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function payment_status_label(
    mixed $status
): string {
    $status =
        normalize_payment_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        payment_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_pending_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_PENDING;
}

function is_processing_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_PROCESSING;
}

function is_paid_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_PAID;
}

function is_failed_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_FAILED;
}

function is_cancelled_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_CANCELLED;
}

function is_refunded_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_REFUNDED;
}

function is_partially_refunded_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_PARTIALLY_REFUNDED;
}

function is_expired_payment_status(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_EXPIRED;
}

/*
|--------------------------------------------------------------------------
| Workflow Helpers
|--------------------------------------------------------------------------
*/

function payment_status_is_pending(
    mixed $status
): bool {
    return in_array(
        normalize_payment_status($status),
        [
            PAYMENT_STATUS_PENDING,
            PAYMENT_STATUS_PROCESSING
        ],
        true
    );
}

function payment_status_is_successful(
    mixed $status
): bool {
    return
        normalize_payment_status($status)
        === PAYMENT_STATUS_PAID;
}

function payment_status_is_failed(
    mixed $status
): bool {
    return in_array(
        normalize_payment_status($status),
        [
            PAYMENT_STATUS_FAILED,
            PAYMENT_STATUS_EXPIRED
        ],
        true
    );
}

function payment_status_is_refunded(
    mixed $status
): bool {
    return in_array(
        normalize_payment_status($status),
        [
            PAYMENT_STATUS_REFUNDED,
            PAYMENT_STATUS_PARTIALLY_REFUNDED
        ],
        true
    );
}

function payment_status_is_terminal(
    mixed $status
): bool {
    return in_array(
        normalize_payment_status($status),
        [
            PAYMENT_STATUS_PAID,
            PAYMENT_STATUS_FAILED,
            PAYMENT_STATUS_CANCELLED,
            PAYMENT_STATUS_REFUNDED,
            PAYMENT_STATUS_PARTIALLY_REFUNDED,
            PAYMENT_STATUS_EXPIRED
        ],
        true
    );
}

function payment_status_allows_retry(
    mixed $status
): bool {
    return in_array(
        normalize_payment_status($status),
        [
            PAYMENT_STATUS_FAILED,
            PAYMENT_STATUS_EXPIRED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function payment_pending_statuses(): array
{
    return [
        PAYMENT_STATUS_PENDING,
        PAYMENT_STATUS_PROCESSING
    ];
}

function payment_success_statuses(): array
{
    return [
        PAYMENT_STATUS_PAID
    ];
}

function payment_failure_statuses(): array
{
    return [
        PAYMENT_STATUS_FAILED,
        PAYMENT_STATUS_EXPIRED
    ];
}

function payment_refund_statuses(): array
{
    return [
        PAYMENT_STATUS_REFUNDED,
        PAYMENT_STATUS_PARTIALLY_REFUNDED
    ];
}

function payment_terminal_statuses(): array
{
    return [
        PAYMENT_STATUS_PAID,
        PAYMENT_STATUS_FAILED,
        PAYMENT_STATUS_CANCELLED,
        PAYMENT_STATUS_REFUNDED,
        PAYMENT_STATUS_PARTIALLY_REFUNDED,
        PAYMENT_STATUS_EXPIRED
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_payment_statuses(): array
{
    return payment_statuses();
}

function get_payment_status_label(
    mixed $status
): string {
    return payment_status_label($status);
}
