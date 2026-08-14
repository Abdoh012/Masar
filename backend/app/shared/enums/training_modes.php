<?php

/**
 * MASAR - Training Modes
 *
 * Centralized training delivery mode definitions.
 */

if (!defined('TRAINING_MODE_IN_PERSON')) {
    define(
        'TRAINING_MODE_IN_PERSON',
        'in_person'
    );
}

if (!defined('TRAINING_MODE_ONLINE')) {
    define(
        'TRAINING_MODE_ONLINE',
        'online'
    );
}

if (!defined('TRAINING_MODE_HYBRID')) {
    define(
        'TRAINING_MODE_HYBRID',
        'hybrid'
    );
}

/*
|--------------------------------------------------------------------------
| Mode Collection
|--------------------------------------------------------------------------
*/

function training_modes(): array
{
    return [
        TRAINING_MODE_IN_PERSON,
        TRAINING_MODE_ONLINE,
        TRAINING_MODE_HYBRID
    ];
}

/*
|--------------------------------------------------------------------------
| Mode Labels
|--------------------------------------------------------------------------
*/

function training_mode_labels(): array
{
    return [
        TRAINING_MODE_IN_PERSON =>
            'In Person',

        TRAINING_MODE_ONLINE =>
            'Online',

        TRAINING_MODE_HYBRID =>
            'Hybrid'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_training_mode(
    mixed $mode
): bool {
    if (!is_string($mode)) {
        return false;
    }

    return in_array(
        strtolower(trim($mode)),
        training_modes(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_training_mode(
    mixed $mode
): ?string {
    if (!is_string($mode)) {
        return null;
    }

    $mode =
        strtolower(
            trim($mode)
        );

    return is_valid_training_mode($mode)
        ? $mode
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function training_mode_label(
    mixed $mode
): string {
    $mode =
        normalize_training_mode($mode);

    if ($mode === null) {
        return 'Unknown';
    }

    return
        training_mode_labels()[$mode]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Mode Helpers
|--------------------------------------------------------------------------
*/

function is_in_person_training_mode(
    mixed $mode
): bool {
    return
        normalize_training_mode($mode)
        === TRAINING_MODE_IN_PERSON;
}

function is_online_training_mode(
    mixed $mode
): bool {
    return
        normalize_training_mode($mode)
        === TRAINING_MODE_ONLINE;
}

function is_hybrid_training_mode(
    mixed $mode
): bool {
    return
        normalize_training_mode($mode)
        === TRAINING_MODE_HYBRID;
}

/*
|--------------------------------------------------------------------------
| Location Helpers
|--------------------------------------------------------------------------
*/

function training_mode_requires_location(
    mixed $mode
): bool {
    return in_array(
        normalize_training_mode($mode),
        [
            TRAINING_MODE_IN_PERSON,
            TRAINING_MODE_HYBRID
        ],
        true
    );
}

function training_mode_is_remote(
    mixed $mode
): bool {
    return
        normalize_training_mode($mode)
        === TRAINING_MODE_ONLINE;
}

function training_mode_supports_online(
    mixed $mode
): bool {
    return in_array(
        normalize_training_mode($mode),
        [
            TRAINING_MODE_ONLINE,
            TRAINING_MODE_HYBRID
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Modes
|--------------------------------------------------------------------------
*/

function physical_training_modes(): array
{
    return [
        TRAINING_MODE_IN_PERSON,
        TRAINING_MODE_HYBRID
    ];
}

function remote_training_modes(): array
{
    return [
        TRAINING_MODE_ONLINE
    ];
}

function flexible_training_modes(): array
{
    return [
        TRAINING_MODE_HYBRID
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function training_mode_is_valid(
    mixed $mode
): bool {
    return is_valid_training_mode($mode);
}

function get_training_modes(): array
{
    return training_modes();
}

function get_training_mode_label(
    mixed $mode
): string {
    return training_mode_label($mode);
}
