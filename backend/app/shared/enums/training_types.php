<?php

/**
 * MASAR - Training Types
 *
 * Centralized training type definitions.
 */

if (!defined('TRAINING_TYPE_IN_PERSON')) {
    define(
        'TRAINING_TYPE_IN_PERSON',
        'in_person'
    );
}

if (!defined('TRAINING_TYPE_ONLINE')) {
    define(
        'TRAINING_TYPE_ONLINE',
        'online'
    );
}

if (!defined('TRAINING_TYPE_HYBRID')) {
    define(
        'TRAINING_TYPE_HYBRID',
        'hybrid'
    );
}

if (!defined('TRAINING_TYPE_WORKSHOP')) {
    define(
        'TRAINING_TYPE_WORKSHOP',
        'workshop'
    );
}

if (!defined('TRAINING_TYPE_INTERNSHIP')) {
    define(
        'TRAINING_TYPE_INTERNSHIP',
        'internship'
    );
}

if (!defined('TRAINING_TYPE_COURSE')) {
    define(
        'TRAINING_TYPE_COURSE',
        'course'
    );
}

/*
|--------------------------------------------------------------------------
| Type Collection
|--------------------------------------------------------------------------
*/

function training_types(): array
{
    return [
        TRAINING_TYPE_IN_PERSON,
        TRAINING_TYPE_ONLINE,
        TRAINING_TYPE_HYBRID,
        TRAINING_TYPE_WORKSHOP,
        TRAINING_TYPE_INTERNSHIP,
        TRAINING_TYPE_COURSE
    ];
}

/*
|--------------------------------------------------------------------------
| Type Labels
|--------------------------------------------------------------------------
*/

function training_type_labels(): array
{
    return [
        TRAINING_TYPE_IN_PERSON =>
            'In Person',

        TRAINING_TYPE_ONLINE =>
            'Online',

        TRAINING_TYPE_HYBRID =>
            'Hybrid',

        TRAINING_TYPE_WORKSHOP =>
            'Workshop',

        TRAINING_TYPE_INTERNSHIP =>
            'Internship',

        TRAINING_TYPE_COURSE =>
            'Course'
    ];
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

function is_valid_training_type(
    mixed $type
): bool {
    if (!is_string($type)) {
        return false;
    }

    return in_array(
        strtolower(trim($type)),
        training_types(),
        true
    );
}

/*
|--------------------------------------------------------------------------
| Normalization
|--------------------------------------------------------------------------
*/

function normalize_training_type(
    mixed $type
): ?string {
    if (!is_string($type)) {
        return null;
    }

    $type =
        strtolower(
            trim($type)
        );

    return is_valid_training_type($type)
        ? $type
        : null;
}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function training_type_label(
    mixed $type
): string {
    $type =
        normalize_training_type($type);

    if ($type === null) {
        return 'Unknown';
    }

    return
        training_type_labels()[$type]
        ?? 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Type Helpers
|--------------------------------------------------------------------------
*/

function is_in_person_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_IN_PERSON;
}

function is_online_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_ONLINE;
}

function is_hybrid_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_HYBRID;
}

function is_workshop_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_WORKSHOP;
}

function is_internship_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_INTERNSHIP;
}

function is_course_training_type(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_COURSE;
}

/*
|--------------------------------------------------------------------------
| Location Helpers
|--------------------------------------------------------------------------
*/

function training_type_requires_location(
    mixed $type
): bool {
    return in_array(
        normalize_training_type($type),
        [
            TRAINING_TYPE_IN_PERSON,
            TRAINING_TYPE_HYBRID,
            TRAINING_TYPE_WORKSHOP,
            TRAINING_TYPE_INTERNSHIP
        ],
        true
    );
}

function training_type_is_remote(
    mixed $type
): bool {
    return
        normalize_training_type($type)
        === TRAINING_TYPE_ONLINE;
}

function training_type_supports_online(
    mixed $type
): bool {
    return in_array(
        normalize_training_type($type),
        [
            TRAINING_TYPE_ONLINE,
            TRAINING_TYPE_HYBRID
        ],
        true
    );
}

/*
|--------------------------------------------------------------------------
| Grouped Types
|--------------------------------------------------------------------------
*/

function physical_training_types(): array
{
    return [
        TRAINING_TYPE_IN_PERSON,
        TRAINING_TYPE_WORKSHOP,
        TRAINING_TYPE_INTERNSHIP
    ];
}

function remote_training_types(): array
{
    return [
        TRAINING_TYPE_ONLINE
    ];
}

function mixed_training_types(): array
{
    return [
        TRAINING_TYPE_HYBRID
    ];
}

function educational_training_types(): array
{
    return [
        TRAINING_TYPE_COURSE,
        TRAINING_TYPE_WORKSHOP
    ];
}

function practical_training_types(): array
{
    return [
        TRAINING_TYPE_INTERNSHIP,
        TRAINING_TYPE_WORKSHOP
    ];
}
