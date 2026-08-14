<?php

/**
 * MASAR - Application Statuses
 *
 * Centralized training application status definitions.
 */

if (!defined('APPLICATION_STATUS_DRAFT')) {
    define(
        'APPLICATION_STATUS_DRAFT',
        'draft'
    );
}

if (!defined('APPLICATION_STATUS_PENDING')) {
    define(
        'APPLICATION_STATUS_PENDING',
        'pending'
    );
}

if (!defined('APPLICATION_STATUS_UNDER_REVIEW')) {
    define(
        'APPLICATION_STATUS_UNDER_REVIEW',
        'under_review'
    );
}

if (!defined('APPLICATION_STATUS_APPROVED')) {
    define(
        'APPLICATION_STATUS_APPROVED',
        'approved'
    );
}

if (!defined('APPLICATION_STATUS_REJECTED')) {
    define(
        'APPLICATION_STATUS_REJECTED',
        'rejected'
    );
}

if (!defined('APPLICATION_STATUS_WAITLISTED')) {
    define(
        'APPLICATION_STATUS_WAITLISTED',
        'waitlisted'
    );
}

if (!defined('APPLICATION_STATUS_WITHDRAWN')) {
    define(
        'APPLICATION_STATUS_WITHDRAWN',
        'withdrawn'
    );
}

if (!defined('APPLICATION_STATUS_CANCELLED')) {
    define(
        'APPLICATION_STATUS_CANCELLED',
        'cancelled'
    );
}

if (!defined('APPLICATION_STATUS_COMPLETED')) {
    define(
        'APPLICATION_STATUS_COMPLETED',
        'completed'
    );
}

/*
|--------------------------------------------------------------------------
| Status Collection
|--------------------------------------------------------------------------
*/

function application_statuses(): array
{
    return [
        APPLICATION_STATUS_DRAFT,
        APPLICATION_STATUS_PENDING,
        APPLICATION_STATUS_UNDER_REVIEW,
        APPLICATION_STATUS_APPROVED,
        APPLICATION_STATUS_REJECTED,
        APPLICATION_STATUS_WAITLISTED,
        APPLICATION_STATUS_WITHDRAWN,
        APPLICATION_STATUS_CANCELLED,
        APPLICATION_STATUS_COMPLETED
    ];
}

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

function application_status_labels(): array
{
    return [
        APPLICATION_STATUS_DRAFT =>
            'Draft',

        APPLICATION_STATUS_PENDING =>
            'Pending',

        APPLICATION_STATUS_UNDER_REVIEW =>
            'Under Review',

        APPLICATION_STATUS_APPROVED =>
            'Approved',

        APPLICATION_STATUS_REJECTED =>
            'Rejected',

        APPLICATION_STATUS_WAITLISTED =>
            'Waitlisted',

        APPLICATION_STATUS_WITHDRAWN =>
            'Withdrawn',

        APPLICATION_STATUS_CANCELLED =>
            'Cancelled',

        APPLICATION_STATUS_COMPLETED =>
            'Completed'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_application_status(
    mixed $status
): bool {
    if (!is_string($status)) {
        return false;
    }

    return in_array(
        strtolower(trim($status)),
        application_statuses(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_application_status(
    mixed $status
): ?string {
    if (!is_string($status)) {
        return null;
    }

    $status =
        strtolower(
            trim($status)
        );

    return is_valid_application_status($status)
        ? $status
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function application_status_label(
    mixed $status
): string {
    $status =
        normalize_application_status($status);

    if ($status === null) {
        return 'Unknown';
    }

    return
        application_status_labels()[$status]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| State Helpers
|--------------------------------------------------------------------------
*/

function is_draft_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_DRAFT;
}

function is_pending_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_PENDING;
}

function is_under_review_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_UNDER_REVIEW;
}

function is_approved_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_APPROVED;
}

function is_rejected_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_REJECTED;
}

function is_waitlisted_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_WAITLISTED;
}

function is_withdrawn_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_WITHDRAWN;
}

function is_cancelled_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_CANCELLED;
}

function is_completed_application_status(
    mixed $status
): bool {
    return
        normalize_application_status($status)
        === APPLICATION_STATUS_COMPLETED;
}

/*
|--------------------------------------------------------------------------
| Workflow Helpers
|--------------------------------------------------------------------------
*/

function application_status_allows_review(
    mixed $status
): bool {
    return in_array(
        normalize_application_status($status),
        [
            APPLICATION_STATUS_PENDING,
            APPLICATION_STATUS_UNDER_REVIEW
        ],
        true
    );
}

function application_status_allows_approval(
    mixed $status
): bool {
    return in_array(
        normalize_application_status($status),
        [
            APPLICATION_STATUS_PENDING,
            APPLICATION_STATUS_UNDER_REVIEW,
            APPLICATION_STATUS_WAITLISTED
        ],
        true
    );
}

function application_status_allows_withdrawal(
    mixed $status
): bool {
    return in_array(
        normalize_application_status($status),
        [
            APPLICATION_STATUS_PENDING,
            APPLICATION_STATUS_UNDER_REVIEW,
            APPLICATION_STATUS_WAITLISTED,
            APPLICATION_STATUS_APPROVED
        ],
        true
    );
}

function application_status_is_active(
    mixed $status
): bool {
    return in_array(
        normalize_application_status($status),
        [
            APPLICATION_STATUS_PENDING,
            APPLICATION_STATUS_UNDER_REVIEW,
            APPLICATION_STATUS_WAITLISTED,
            APPLICATION_STATUS_APPROVED
        ],
        true
    );
}

function application_status_is_final(
    mixed $status
): bool {
    return in_array(
        normalize_application_status($status),
        [
            APPLICATION_STATUS_REJECTED,
            APPLICATION_STATUS_WITHDRAWN,
            APPLICATION_STATUS_CANCELLED,
            APPLICATION_STATUS_COMPLETED
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Statuses
|--------------------------------------------------------------------------
*/

function application_pending_statuses(): array
{
    return [
        APPLICATION_STATUS_PENDING,
        APPLICATION_STATUS_UNDER_REVIEW
    ];
}

function application_accepted_statuses(): array
{
    return [
        APPLICATION_STATUS_APPROVED,
        APPLICATION_STATUS_WAITLISTED
    ];
}

function application_rejected_statuses(): array
{
    return [
        APPLICATION_STATUS_REJECTED
    ];
}

function application_closed_statuses(): array
{
    return [
        APPLICATION_STATUS_REJECTED,
        APPLICATION_STATUS_WITHDRAWN,
        APPLICATION_STATUS_CANCELLED,
        APPLICATION_STATUS_COMPLETED
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_application_statuses(): array
{
    return application_statuses();
}

function get_application_status_label(
    mixed $status
): string {
    return application_status_label($status);
}
