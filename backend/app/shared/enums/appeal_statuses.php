<?php

/**
 * MASAR - Appeal Statuses
 *
 * Centralized appeal status definitions.
 */

if (!defined('APPEAL_STATUS_PENDING')) {
    define(
        'APPEAL_STATUS_PENDING',
        'pending'
    );
}

if (!defined('APPEAL_STATUS_UNDER_REVIEW')) {
    define(
        'APPEAL_STATUS_UNDER_REVIEW',
        'under_review'
    );
}

if (!defined('APPEAL_STATUS_APPROVED')) {
    define(
        'APPEAL_STATUS_APPROVED',
        'approved'
    );
}

if (!defined('APPEAL_STATUS_REJECTED')) {
    define(
        'APPEAL_STATUS_REJECTED',
        'rejected'
    );
}

if (!defined('APPEAL_STATUS_WITHDRAWN')) {
    define(
        'APPEAL_STATUS_WITHDRAWN',
        'withdrawn'
    );
}

if (!defined('APPEAL_STATUS_CLOSED')) {
    define(
        'APPEAL_STATUS_CLOSED',
        'closed'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function appeal_statuses(): array
{
    return [
        APPEAL_STATUS_PENDING,
        APPEAL_STATUS_UNDER_REVIEW,
        APPEAL_STATUS_APPROVED,
        APPEAL_STATUS_REJECTED,
        APPEAL_STATUS_WITHDRAWN,
        APPEAL_STATUS_CLOSED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function appeal_status_labels(): array
{
    return [
        APPEAL_STATUS_PENDING =>
            'Pending',

        APPEAL_STATUS_UNDER_REVIEW =>
            'Under Review',

        APPEAL_STATUS_APPROVED =>
            'Approved',

        APPEAL_STATUS_REJECTED =>
            'Rejected',

        APPEAL_STATUS_WITHDRAWN =>
            'Withdrawn',

        APPEAL_STATUS_CLOSED =>
            'Closed'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_appeal_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        appeal_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_appeal_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_appeal_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function appeal_status_label(
    mixed $status
): string {
    $status =
        normalize_appeal_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        appeal_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_pending_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_PENDING;
}

function is_under_review_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_UNDER_REVIEW;
}

function is_approved_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_APPROVED;
}

function is_rejected_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_REJECTED;
}

function is_withdrawn_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_WITHDRAWN;
}

function is_closed_appeal_status(
    mixed $status
): bool {
    return
        normalize_appeal_status($status)
        === APPEAL_STATUS_CLOSED;
}

/*
|--------------------------------------------------------------------------
| Workflow Helpers
|--------------------------------------------------------------------------
*/

function appeal_status_is_reviewable(
    mixed $status
): bool {
    return in_array(
        normalize_appeal_status($status),
        [
            APPEAL_STATUS_PENDING,
            APPEAL_STATUS_UNDER_REVIEW
        ],
        true
    );
}

function appeal_status_is_active(
    mixed $status
): bool {
    return in_array(
        normalize_appeal_status($status),
        [
            APPEAL_STATUS_PENDING,
            APPEAL_STATUS_UNDER_REVIEW
        ],
        true
    );
}

function appeal_status_is_final(
    mixed $status
): bool {
    return in_array(
        normalize_appeal_status($status),
        [
            APPEAL_STATUS_APPROVED,
            APPEAL_STATUS_REJECTED,
            APPEAL_STATUS_WITHDRAWN,
            APPEAL_STATUS_CLOSED
        ],
        true
    );
}

function appeal_status_allows_withdrawal(
    mixed $status
): bool {
    return in_array(
        normalize_appeal_status($status),
        [
            APPEAL_STATUS_PENDING,
            APPEAL_STATUS_UNDER_REVIEW
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function appeal_pending_statuses(): array
{
    return [
        APPEAL_STATUS_PENDING,
        APPEAL_STATUS_UNDER_REVIEW
    ];
}

function appeal_resolved_statuses(): array
{
    return [
        APPEAL_STATUS_APPROVED,
        APPEAL_STATUS_REJECTED
    ];
}

function appeal_closed_statuses(): array
{
    return [
        APPEAL_STATUS_APPROVED,
        APPEAL_STATUS_REJECTED,
        APPEAL_STATUS_WITHDRAWN,
        APPEAL_STATUS_CLOSED
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_appeal_statuses(): array
{
    return appeal_statuses();
}

function get_appeal_status_label(
    mixed $status
): string {
    return appeal_status_label($status);
}
