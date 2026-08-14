<?php

/**
 * MASAR - Training Session Statuses
 *
 * Centralized training session status definitions.
 */

if (!defined('TRAINING_SESSION_STATUS_SCHEDULED')) {
    define(
        'TRAINING_SESSION_STATUS_SCHEDULED',
        'scheduled'
    );
}

if (!defined('TRAINING_SESSION_STATUS_CONFIRMED')) {
    define(
        'TRAINING_SESSION_STATUS_CONFIRMED',
        'confirmed'
    );
}

if (!defined('TRAINING_SESSION_STATUS_IN_PROGRESS')) {
    define(
        'TRAINING_SESSION_STATUS_IN_PROGRESS',
        'in_progress'
    );
}

if (!defined('TRAINING_SESSION_STATUS_COMPLETED')) {
    define(
        'TRAINING_SESSION_STATUS_COMPLETED',
        'completed'
    );
}

if (!defined('TRAINING_SESSION_STATUS_CANCELLED')) {
    define(
        'TRAINING_SESSION_STATUS_CANCELLED',
        'cancelled'
    );
}

if (!defined('TRAINING_SESSION_STATUS_POSTPONED')) {
    define(
        'TRAINING_SESSION_STATUS_POSTPONED',
        'postponed'
    );
}

if (!defined('TRAINING_SESSION_STATUS_NO_SHOW')) {
    define(
        'TRAINING_SESSION_STATUS_NO_SHOW',
        'no_show'
    );
}

if (!defined('TRAINING_SESSION_STATUS_CLOSED')) {
    define(
        'TRAINING_SESSION_STATUS_CLOSED',
        'closed'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function training_session_statuses(): array
{
    return [
        TRAINING_SESSION_STATUS_SCHEDULED,
        TRAINING_SESSION_STATUS_CONFIRMED,
        TRAINING_SESSION_STATUS_IN_PROGRESS,
        TRAINING_SESSION_STATUS_COMPLETED,
        TRAINING_SESSION_STATUS_CANCELLED,
        TRAINING_SESSION_STATUS_POSTPONED,
        TRAINING_SESSION_STATUS_NO_SHOW,
        TRAINING_SESSION_STATUS_CLOSED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function training_session_status_labels(): array
{
    return [
        TRAINING_SESSION_STATUS_SCHEDULED =>
            'Scheduled',

        TRAINING_SESSION_STATUS_CONFIRMED =>
            'Confirmed',

        TRAINING_SESSION_STATUS_IN_PROGRESS =>
            'In Progress',

        TRAINING_SESSION_STATUS_COMPLETED =>
            'Completed',

        TRAINING_SESSION_STATUS_CANCELLED =>
            'Cancelled',

        TRAINING_SESSION_STATUS_POSTPONED =>
            'Postponed',

        TRAINING_SESSION_STATUS_NO_SHOW =>
            'No Show',

        TRAINING_SESSION_STATUS_CLOSED =>
            'Closed'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_training_session_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        training_session_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_training_session_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_training_session_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function training_session_status_label(
    mixed $status
): string {
    $status =
        normalize_training_session_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        training_session_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_scheduled_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_SCHEDULED;
}

function is_confirmed_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_CONFIRMED;
}

function is_in_progress_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_IN_PROGRESS;
}

function is_completed_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_COMPLETED;
}

function is_cancelled_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_CANCELLED;
}

function is_postponed_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_POSTPONED;
}

function is_no_show_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_NO_SHOW;
}

function is_closed_training_session_status(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_CLOSED;
}

/*
|--------------------------------------------------------------------------
| Workflow Helpers
|--------------------------------------------------------------------------
*/

function training_session_status_is_active(
    mixed $status
): bool {
    return in_array(
        normalize_training_session_status($status),
        [
            TRAINING_SESSION_STATUS_SCHEDULED,
            TRAINING_SESSION_STATUS_CONFIRMED,
            TRAINING_SESSION_STATUS_IN_PROGRESS
        ],
        true
    );
}

function training_session_status_is_finished(
    mixed $status
): bool {
    return in_array(
        normalize_training_session_status($status),
        [
            TRAINING_SESSION_STATUS_COMPLETED,
            TRAINING_SESSION_STATUS_CANCELLED,
            TRAINING_SESSION_STATUS_NO_SHOW,
            TRAINING_SESSION_STATUS_CLOSED
        ],
        true
    );
}

function training_session_status_is_terminal(
    mixed $status
): bool {
    return in_array(
        normalize_training_session_status($status),
        [
            TRAINING_SESSION_STATUS_COMPLETED,
            TRAINING_SESSION_STATUS_CANCELLED,
            TRAINING_SESSION_STATUS_NO_SHOW,
            TRAINING_SESSION_STATUS_CLOSED
        ],
        true
    );
}

function training_session_status_can_start(
    mixed $status
): bool {
    return in_array(
        normalize_training_session_status($status),
        [
            TRAINING_SESSION_STATUS_SCHEDULED,
            TRAINING_SESSION_STATUS_CONFIRMED
        ],
        true
    );
}

function training_session_status_can_complete(
    mixed $status
): bool {
    return
        normalize_training_session_status($status)
        === TRAINING_SESSION_STATUS_IN_PROGRESS;
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function training_session_scheduled_statuses(): array
{
    return [
        TRAINING_SESSION_STATUS_SCHEDULED,
        TRAINING_SESSION_STATUS_CONFIRMED
    ];
}

function training_session_active_statuses(): array
{
    return [
        TRAINING_SESSION_STATUS_SCHEDULED,
        TRAINING_SESSION_STATUS_CONFIRMED,
        TRAINING_SESSION_STATUS_IN_PROGRESS
    ];
}

function training_session_finished_statuses(): array
{
    return [
        TRAINING_SESSION_STATUS_COMPLETED,
        TRAINING_SESSION_STATUS_CANCELLED,
        TRAINING_SESSION_STATUS_NO_SHOW,
        TRAINING_SESSION_STATUS_CLOSED
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_training_session_statuses(): array
{
    return training_session_statuses();
}

function get_training_session_status_label(
    mixed $status
): string {
    return training_session_status_label($status);
}
