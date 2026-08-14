<?php

/**
 * MASAR - Payment Types
 *
 * Centralized payment type definitions.
 */

if (!defined('PAYMENT_TYPE_TRAINING')) {
    define(
        'PAYMENT_TYPE_TRAINING',
        'training'
    );
}

if (!defined('PAYMENT_TYPE_APPLICATION')) {
    define(
        'PAYMENT_TYPE_APPLICATION',
        'application'
    );
}

if (!defined('PAYMENT_TYPE_CERTIFICATE')) {
    define(
        'PAYMENT_TYPE_CERTIFICATE',
        'certificate'
    );
}

if (!defined('PAYMENT_TYPE_SUBSCRIPTION')) {
    define(
        'PAYMENT_TYPE_SUBSCRIPTION',
        'subscription'
    );
}

if (!defined('PAYMENT_TYPE_SERVICE')) {
    define(
        'PAYMENT_TYPE_SERVICE',
        'service'
    );
}

if (!defined('PAYMENT_TYPE_OTHER')) {
    define(
        'PAYMENT_TYPE_OTHER',
        'other'
    );
}

/*
|--------------------------------------------------------------------------
| Type Collection
|--------------------------------------------------------------------------
*/

function payment_types(): array
{
    return [
        PAYMENT_TYPE_TRAINING,
        PAYMENT_TYPE_APPLICATION,
        PAYMENT_TYPE_CERTIFICATE,
        PAYMENT_TYPE_SUBSCRIPTION,
        PAYMENT_TYPE_SERVICE,
        PAYMENT_TYPE_OTHER
    ];
}

/*
|--------------------------------------------------------------------------
| Type Labels
|--------------------------------------------------------------------------
*/

function payment_type_labels(): array
{
    return [
        PAYMENT_TYPE_TRAINING =>
            'Training',

        PAYMENT_TYPE_APPLICATION =>
            'Application',

        PAYMENT_TYPE_CERTIFICATE =>
            'Certificate',

        PAYMENT_TYPE_SUBSCRIPTION =>
            'Subscription',

        PAYMENT_TYPE_SERVICE =>
            'Service',

        PAYMENT_TYPE_OTHER =>
            'Other'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_payment_type(
    mixed $type
): bool {
    if (!is_string($type)) {
        return false;
    }

    return in_array(
        strtolower(trim($type)),
        payment_types(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_payment_type(
    mixed $type
): ?string {
    if (!is_string($type)) {
        return null;
    }

    $type =
        strtolower(
            trim($type)
        );

    return is_valid_payment_type($type)
        ? $type
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function payment_type_label(
    mixed $type
): string {
    $type =
        normalize_payment_type($type);

    if ($type === null) {
        return 'Unknown';
    }

    return
        payment_type_labels()[$type]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Type Helpers
|--------------------------------------------------------------------------
*/

function is_training_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_TRAINING;
}

function is_application_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_APPLICATION;
}

function is_certificate_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_CERTIFICATE;
}

function is_subscription_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_SUBSCRIPTION;
}

function is_service_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_SERVICE;
}

function is_other_payment_type(
    mixed $type
): bool {
    return
        normalize_payment_type($type)
        === PAYMENT_TYPE_OTHER;
}

/*
|--------------------------------------------------------------------------
| Grouped Types
|--------------------------------------------------------------------------
*/

function core_payment_types(): array
{
    return [
        PAYMENT_TYPE_TRAINING,
        PAYMENT_TYPE_APPLICATION,
        PAYMENT_TYPE_CERTIFICATE
    ];
}

function recurring_payment_types(): array
{
    return [
        PAYMENT_TYPE_SUBSCRIPTION
    ];
}

function service_payment_types(): array
{
    return [
        PAYMENT_TYPE_SERVICE,
        PAYMENT_TYPE_OTHER
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_payment_types(): array
{
    return payment_types();
}

function get_payment_type_label(
    mixed $type
): string {
    return payment_type_label($type);
}
