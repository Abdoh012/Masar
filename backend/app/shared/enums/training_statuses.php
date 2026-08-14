<?php

/**
 * MASAR - Training Statuses
 *
 * Centralized training status definitions.
 */

if (!defined('TRAINING_STATUS_DRAFT')) {
    define(
        'TRAINING_STATUS_DRAFT',
        'draft'
    );
}

if (!defined('TRAINING_STATUS_PENDING')) {
    define(
        'TRAINING_STATUS_PENDING',
        'pending'
    );
}

if (!defined('TRAINING_STATUS_APPROVED')) {
    define(
        'TRAINING_STATUS_APPROVED',
        'approved'
    );
}

if (!defined('TRAINING_STATUS_REJECTED')) {
    define(
        'TRAINING_STATUS_REJECTED',
        'rejected'
    );
}

if (!defined('TRAINING_STATUS_OPEN')) {
    define(
        'TRAINING_STATUS_OPEN',
        'open'
    );
}

if (!defined('TRAINING_STATUS_FULL')) {
    define(
        'TRAINING_STATUS_FULL',
        'full'
    );
}

if (!defined('TRAINING_STATUS_IN_PROGRESS')) {
    define(
        'TRAINING_STATUS_IN_PROGRESS',
        'in_progress'
    );
}

if (!defined('TRAINING_STATUS_COMPLETED')) {
    define(
        'TRAINING_STATUS_COMPLETED',
        'completed'
    );
}

if (!defined('TRAINING_STATUS_CANCELLED')) {
    define(
        'TRAINING_STATUS_CANCELLED',
        'cancelled'
    );
}

if (!defined('TRAINING_STATUS_CLOSED')) {
    define(
        'TRAINING_STATUS_CLOSED',
        'closed'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function training_statuses(): array
{
    return [
        TRAINING_STATUS_DRAFT,
        TRAINING_STATUS_PENDING,
        TRAINING_STATUS_APPROVED,
        TRAINING_STATUS_REJECTED,
        TRAINING_STATUS_OPEN,
        TRAINING_STATUS_FULL,
        TRAINING_STATUS_IN_PROGRESS,
        TRAINING_STATUS_COMPLETED,
        TRAINING_STATUS_CANCELLED,
        TRAINING_STATUS_CLOSED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function training_status_labels(): array
{
    return [
        TRAINING_STATUS_DRAFT =>
            'Draft',

        TRAINING_STATUS_PENDING =>
            'Pending',

        TRAINING_STATUS_APPROVED =>
            'Approved',

        TRAINING_STATUS_REJECTED =>
            'Rejected',

        TRAINING_STATUS_OPEN =>
            'Open',

        TRAINING_STATUS_FULL =>
            'Full',

        TRAINING_STATUS_IN_PROGRESS =>
            'In Progress',

        TRAINING_STATUS_COMPLETED =>
            'Completed',

        TRAINING_STATUS_CANCELLED =>
            'Cancelled',

        TRAINING_STATUS_CLOSED =>
            'Closed'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_training_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        training_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_training_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_training_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function training_status_label(
    mixed $status
): string {
    $status =
        normalize_training_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        training_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_draft_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_DRAFT;
}

function is_pending_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_PENDING;
}

function is_approved_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_APPROVED;
}

function is_open_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_OPEN;
}

function is_full_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_FULL;
}

function is_in_progress_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_IN_PROGRESS;
}

function is_completed_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_COMPLETED;
}

function is_cancelled_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_CANCELLED;
}

function is_closed_training_status(
    mixed $status
): bool {
    return
        normalize_training_status($status)
        === TRAINING_STATUS_CLOSED;
}

/*
|--------------------------------------------------------------------------
| Availability Helpers
|--------------------------------------------------------------------------
*/

function training_status_allows_application(
    mixed $status
): bool {
    return in_array(
        normalize_training_status($status),
        [
            TRAINING_STATUS_OPEN
        ],
        true
    );
}

function training_status_is_active(
    mixed $status
): bool {
    return in_array(
        normalize_training_status($status),
        [
            TRAINING_STATUS_OPEN,
            TRAINING_STATUS_FULL,
            TRAINING_STATUS_IN_PROGRESS
        ],
        true
    );
}

function training_status_is_finished(
    mixed $status
): bool {
    return in_array(
        normalize_training_status($status),
        [
            TRAINING_STATUS_COMPLETED,
            TRAINING_STATUS_CANCELLED,
            TRAINING_STATUS_CLOSED
        ],
        true
    );
}

function training_status_is_terminal(
    mixed $status
): bool {
    return in_array(
        normalize_training_status($status),
        [
            TRAINING_STATUS_REJECTED,
            TRAINING_STATUS_COMPLETED,
            TRAINING_STATUS_CANCELLED,
            TRAINING_STATUS_CLOSED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function training_draft_statuses(): array
{
    return [
        TRAINING_STATUS_DRAFT
    ];
}

function training_review_statuses(): array
{
    return [
        TRAINING_STATUS_PENDING
    ];
}

function training_available_statuses(): array
{
    return [
        TRAINING_STATUS_OPEN
    ];
}

function training_active_statuses(): array
{
    return [
        TRAINING_STATUS_OPEN,
        TRAINING_STATUS_FULL,
        TRAINING_STATUS_IN_PROGRESS
    ];
}

function training_finished_statuses(): array
{
    return [
        TRAINING_STATUS_COMPLETED,
        TRAINING_STATUS_CANCELLED,
        TRAINING_STATUS_CLOSED
    ];
}
