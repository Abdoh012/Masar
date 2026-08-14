<?php

/**
 * MASAR - Notification Types
 *
 * Centralized notification type definitions.
 */

if (!defined('NOTIFICATION_TYPE_SYSTEM')) {
    define(
        'NOTIFICATION_TYPE_SYSTEM',
        'system'
    );
}

if (!defined('NOTIFICATION_TYPE_ACCOUNT')) {
    define(
        'NOTIFICATION_TYPE_ACCOUNT',
        'account'
    );
}

if (!defined('NOTIFICATION_TYPE_TRAINING')) {
    define(
        'NOTIFICATION_TYPE_TRAINING',
        'training'
    );
}

if (!defined('NOTIFICATION_TYPE_APPLICATION')) {
    define(
        'NOTIFICATION_TYPE_APPLICATION',
        'application'
    );
}

if (!defined('NOTIFICATION_TYPE_CERTIFICATE')) {
    define(
        'NOTIFICATION_TYPE_CERTIFICATE',
        'certificate'
    );
}

if (!defined('NOTIFICATION_TYPE_APPEAL')) {
    define(
        'NOTIFICATION_TYPE_APPEAL',
        'appeal'
    );
}

if (!defined('NOTIFICATION_TYPE_PAYMENT')) {
    define(
        'NOTIFICATION_TYPE_PAYMENT',
        'payment'
    );
}

if (!defined('NOTIFICATION_TYPE_MESSAGE')) {
    define(
        'NOTIFICATION_TYPE_MESSAGE',
        'message'
    );
}

if (!defined('NOTIFICATION_TYPE_COMPANY')) {
    define(
        'NOTIFICATION_TYPE_COMPANY',
        'company'
    );
}

if (!defined('NOTIFICATION_TYPE_ADMIN')) {
    define(
        'NOTIFICATION_TYPE_ADMIN',
        'admin'
    );
}

if (!defined('NOTIFICATION_TYPE_REMINDER')) {
    define(
        'NOTIFICATION_TYPE_REMINDER',
        'reminder'
    );
}

if (!defined('NOTIFICATION_TYPE_SECURITY')) {
    define(
        'NOTIFICATION_TYPE_SECURITY',
        'security'
    );
}

if (!defined('NOTIFICATION_TYPE_OTHER')) {
    define(
        'NOTIFICATION_TYPE_OTHER',
        'other'
    );
}

/*
|--------------------------------------------------------------------------
| Type Collection
|--------------------------------------------------------------------------
*/

function notification_types(): array
{
    return [
        NOTIFICATION_TYPE_SYSTEM,
        NOTIFICATION_TYPE_ACCOUNT,
        NOTIFICATION_TYPE_TRAINING,
        NOTIFICATION_TYPE_APPLICATION,
        NOTIFICATION_TYPE_CERTIFICATE,
        NOTIFICATION_TYPE_APPEAL,
        NOTIFICATION_TYPE_PAYMENT,
        NOTIFICATION_TYPE_MESSAGE,
        NOTIFICATION_TYPE_COMPANY,
        NOTIFICATION_TYPE_ADMIN,
        NOTIFICATION_TYPE_REMINDER,
        NOTIFICATION_TYPE_SECURITY,
        NOTIFICATION_TYPE_OTHER
    ];
}

/*
|--------------------------------------------------------------------------
| Type Labels
|--------------------------------------------------------------------------
*/

function notification_type_labels(): array
{
    return [
        NOTIFICATION_TYPE_SYSTEM =>
            'System',

        NOTIFICATION_TYPE_ACCOUNT =>
            'Account',

        NOTIFICATION_TYPE_TRAINING =>
            'Training',

        NOTIFICATION_TYPE_APPLICATION =>
            'Application',

        NOTIFICATION_TYPE_CERTIFICATE =>
            'Certificate',

        NOTIFICATION_TYPE_APPEAL =>
            'Appeal',

        NOTIFICATION_TYPE_PAYMENT =>
            'Payment',

        NOTIFICATION_TYPE_MESSAGE =>
            'Message',

        NOTIFICATION_TYPE_COMPANY =>
            'Company',

        NOTIFICATION_TYPE_ADMIN =>
            'Administration',

        NOTIFICATION_TYPE_REMINDER =>
            'Reminder',

        NOTIFICATION_TYPE_SECURITY =>
            'Security',

        NOTIFICATION_TYPE_OTHER =>
            'Other'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_notification_type(
    mixed $type
): bool {
    if (!is_string($type)) {
        return false;
    }

    return in_array(
        strtolower(trim($type)),
        notification_types(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_notification_type(
    mixed $type
): ?string {
    if (!is_string($type)) {
        return null;
    }

    $type =
        strtolower(
            trim($type)
        );

    return is_valid_notification_type($type)
        ? $type
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function notification_type_label(
    mixed $type
): string {
    $type =
        normalize_notification_type($type);

    if ($type === null) {
        return 'Unknown';
    }

    return
        notification_type_labels()[$type]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Type Helpers
|--------------------------------------------------------------------------
*/

function is_system_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_SYSTEM;
}

function is_account_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_ACCOUNT;
}

function is_training_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_TRAINING;
}

function is_application_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_APPLICATION;
}

function is_certificate_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_CERTIFICATE;
}

function is_appeal_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_APPEAL;
}

function is_payment_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_PAYMENT;
}

function is_message_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_MESSAGE;
}

function is_company_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_COMPANY;
}

function is_admin_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_ADMIN;
}

function is_reminder_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_REMINDER;
}

function is_security_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_SECURITY;
}

function is_other_notification_type(
    mixed $type
): bool {
    return
        normalize_notification_type($type)
        === NOTIFICATION_TYPE_OTHER;
}

/*
|--------------------------------------------------------------------------
| Grouped Types
|--------------------------------------------------------------------------
*/

function business_notification_types(): array
{
    return [
        NOTIFICATION_TYPE_TRAINING,
        NOTIFICATION_TYPE_APPLICATION,
        NOTIFICATION_TYPE_CERTIFICATE,
        NOTIFICATION_TYPE_APPEAL,
        NOTIFICATION_TYPE_PAYMENT,
        NOTIFICATION_TYPE_COMPANY
    ];
}

function user_notification_types(): array
{
    return [
        NOTIFICATION_TYPE_ACCOUNT,
        NOTIFICATION_TYPE_MESSAGE,
        NOTIFICATION_TYPE_REMINDER,
        NOTIFICATION_TYPE_SECURITY
    ];
}

function administrative_notification_types(): array
{
    return [
        NOTIFICATION_TYPE_SYSTEM,
        NOTIFICATION_TYPE_ADMIN
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_notification_types(): array
{
    return notification_types();
}

function get_notification_type_label(
    mixed $type
): string {
    return notification_type_label($type);
}
