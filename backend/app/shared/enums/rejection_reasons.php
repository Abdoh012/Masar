<?php

/**
 * MASAR - Rejection Reasons
 *
 * Centralized rejection reason definitions.
 */

if (!defined('REJECTION_REASON_INCOMPLETE_DATA')) {
    define(
        'REJECTION_REASON_INCOMPLETE_DATA',
        'incomplete_data'
    );
}

if (!defined('REJECTION_REASON_INVALID_DATA')) {
    define(
        'REJECTION_REASON_INVALID_DATA',
        'invalid_data'
    );
}

if (!defined('REJECTION_REASON_NOT_ELIGIBLE')) {
    define(
        'REJECTION_REASON_NOT_ELIGIBLE',
        'not_eligible'
    );
}

if (!defined('REJECTION_REASON_CAPACITY_FULL')) {
    define(
        'REJECTION_REASON_CAPACITY_FULL',
        'capacity_full'
    );
}

if (!defined('REJECTION_REASON_REQUIREMENTS_NOT_MET')) {
    define(
        'REJECTION_REASON_REQUIREMENTS_NOT_MET',
        'requirements_not_met'
    );
}

if (!defined('REJECTION_REASON_DUPLICATE_APPLICATION')) {
    define(
        'REJECTION_REASON_DUPLICATE_APPLICATION',
        'duplicate_application'
    );
}

if (!defined('REJECTION_REASON_DOCUMENTS_MISSING')) {
    define(
        'REJECTION_REASON_DOCUMENTS_MISSING',
        'documents_missing'
    );
}

if (!defined('REJECTION_REASON_DOCUMENTS_INVALID')) {
    define(
        'REJECTION_REASON_DOCUMENTS_INVALID',
        'documents_invalid'
    );
}

if (!defined('REJECTION_REASON_COMPANY_NOT_APPROVED')) {
    define(
        'REJECTION_REASON_COMPANY_NOT_APPROVED',
        'company_not_approved'
    );
}

if (!defined('REJECTION_REASON_TRAINING_NOT_AVAILABLE')) {
    define(
        'REJECTION_REASON_TRAINING_NOT_AVAILABLE',
        'training_not_available'
    );
}

if (!defined('REJECTION_REASON_PAYMENT_FAILED')) {
    define(
        'REJECTION_REASON_PAYMENT_FAILED',
        'payment_failed'
    );
}

if (!defined('REJECTION_REASON_POLICY_VIOLATION')) {
    define(
        'REJECTION_REASON_POLICY_VIOLATION',
        'policy_violation'
    );
}

if (!defined('REJECTION_REASON_OTHER')) {
    define(
        'REJECTION_REASON_OTHER',
        'other'
    );
}

/*
|--------------------------------------------------------------------------
| Reason Collection
|--------------------------------------------------------------------------
*/

function rejection_reasons(): array
{
    return [
        REJECTION_REASON_INCOMPLETE_DATA,
        REJECTION_REASON_INVALID_DATA,
        REJECTION_REASON_NOT_ELIGIBLE,
        REJECTION_REASON_CAPACITY_FULL,
        REJECTION_REASON_REQUIREMENTS_NOT_MET,
        REJECTION_REASON_DUPLICATE_APPLICATION,
        REJECTION_REASON_DOCUMENTS_MISSING,
        REJECTION_REASON_DOCUMENTS_INVALID,
        REJECTION_REASON_COMPANY_NOT_APPROVED,
        REJECTION_REASON_TRAINING_NOT_AVAILABLE,
        REJECTION_REASON_PAYMENT_FAILED,
        REJECTION_REASON_POLICY_VIOLATION,
        REJECTION_REASON_OTHER
    ];
}

/*
|--------------------------------------------------------------------------
| Reason Labels
|--------------------------------------------------------------------------
*/

function rejection_reason_labels(): array
{
    return [
        REJECTION_REASON_INCOMPLETE_DATA =>
            'Incomplete Data',

        REJECTION_REASON_INVALID_DATA =>
            'Invalid Data',

        REJECTION_REASON_NOT_ELIGIBLE =>
            'Not Eligible',

        REJECTION_REASON_CAPACITY_FULL =>
            'Capacity Full',

        REJECTION_REASON_REQUIREMENTS_NOT_MET =>
            'Requirements Not Met',

        REJECTION_REASON_DUPLICATE_APPLICATION =>
            'Duplicate Application',

        REJECTION_REASON_DOCUMENTS_MISSING =>
            'Documents Missing',

        REJECTION_REASON_DOCUMENTS_INVALID =>
            'Documents Invalid',

        REJECTION_REASON_COMPANY_NOT_APPROVED =>
            'Company Not Approved',

        REJECTION_REASON_TRAINING_NOT_AVAILABLE =>
            'Training Not Available',

        REJECTION_REASON_PAYMENT_FAILED =>
            'Payment Failed',

        REJECTION_REASON_POLICY_VIOLATION =>
            'Policy Violation',

        REJECTION_REASON_OTHER =>
            'Other'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_rejection_reason(
    mixed $reason
): bool {
    if (!is_string($reason)) {
        return false;
    }

    return in_array(
        strtolower(trim($reason)),
        rejection_reasons(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_rejection_reason(
    mixed $reason
): ?string {
    if (!is_string($reason)) {
        return null;
    }

    $reason =
        strtolower(
            trim($reason)
        );

    return is_valid_rejection_reason($reason)
        ? $reason
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function rejection_reason_label(
    mixed $reason
): string {
    $reason =
        normalize_rejection_reason($reason);

    if ($reason === null) {
        return 'Unknown';
    }

    return
        rejection_reason_labels()[$reason]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Reason Helpers
|--------------------------------------------------------------------------
*/

function is_data_rejection_reason(
    mixed $reason
): bool {
    return in_array(
        normalize_rejection_reason($reason),
        [
            REJECTION_REASON_INCOMPLETE_DATA,
            REJECTION_REASON_INVALID_DATA
        ],
        true
    );
}

function is_document_rejection_reason(
    mixed $reason
): bool {
    return in_array(
        normalize_rejection_reason($reason),
        [
            REJECTION_REASON_DOCUMENTS_MISSING,
            REJECTION_REASON_DOCUMENTS_INVALID
        ],
        true
    );
}

function is_eligibility_rejection_reason(
    mixed $reason
): bool {
    return in_array(
        normalize_rejection_reason($reason),
        [
            REJECTION_REASON_NOT_ELIGIBLE,
            REJECTION_REASON_REQUIREMENTS_NOT_MET
        ],
        true
    );
}

function is_capacity_rejection_reason(
    mixed $reason
): bool {
    return
        normalize_rejection_reason($reason)
        === REJECTION_REASON_CAPACITY_FULL;
}

function is_payment_rejection_reason(
    mixed $reason
): bool {
    return
        normalize_rejection_reason($reason)
        === REJECTION_REASON_PAYMENT_FAILED;
}

function is_policy_rejection_reason(
    mixed $reason
): bool {
    return
        normalize_rejection_reason($reason)
        === REJECTION_REASON_POLICY_VIOLATION;
}

/*
|--------------------------------------------------------------------------
| Grouped Reasons
|--------------------------------------------------------------------------
*/

function application_rejection_reasons(): array
{
    return [
        REJECTION_REASON_INCOMPLETE_DATA,
        REJECTION_REASON_INVALID_DATA,
        REJECTION_REASON_NOT_ELIGIBLE,
        REJECTION_REASON_REQUIREMENTS_NOT_MET,
        REJECTION_REASON_DUPLICATE_APPLICATION,
        REJECTION_REASON_DOCUMENTS_MISSING,
        REJECTION_REASON_DOCUMENTS_INVALID
    ];
}

function training_rejection_reasons(): array
{
    return [
        REJECTION_REASON_CAPACITY_FULL,
        REJECTION_REASON_TRAINING_NOT_AVAILABLE,
        REJECTION_REASON_COMPANY_NOT_APPROVED
    ];
}

function payment_rejection_reasons(): array
{
    return [
        REJECTION_REASON_PAYMENT_FAILED
    ];
}

function administrative_rejection_reasons(): array
{
    return [
        REJECTION_REASON_POLICY_VIOLATION,
        REJECTION_REASON_OTHER
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_rejection_reasons(): array
{
    return rejection_reasons();
}

function get_rejection_reason_label(
    mixed $reason
): string {
    return rejection_reason_label($reason);
}
