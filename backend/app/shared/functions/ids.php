<?php

/**
 * MASAR - ID Functions
 *
 * Shared helpers for generating, validating, normalizing,
 * and comparing application identifiers.
 */

/*
|--------------------------------------------------------------------------
| ID Prefixes
|--------------------------------------------------------------------------
*/

if (!defined('ID_PREFIX_USER')) {
    define(
        'ID_PREFIX_USER',
        'USR'
    );
}

if (!defined('ID_PREFIX_COMPANY')) {
    define(
        'ID_PREFIX_COMPANY',
        'COM'
    );
}

if (!defined('ID_PREFIX_TRAINING')) {
    define(
        'ID_PREFIX_TRAINING',
        'TRN'
    );
}

if (!defined('ID_PREFIX_APPLICATION')) {
    define(
        'ID_PREFIX_APPLICATION',
        'APP'
    );
}

if (!defined('ID_PREFIX_SESSION')) {
    define(
        'ID_PREFIX_SESSION',
        'SES'
    );
}

if (!defined('ID_PREFIX_CERTIFICATE')) {
    define(
        'ID_PREFIX_CERTIFICATE',
        'CER'
    );
}

if (!defined('ID_PREFIX_APPEAL')) {
    define(
        'ID_PREFIX_APPEAL',
        'APL'
    );
}

if (!defined('ID_PREFIX_PAYMENT')) {
    define(
        'ID_PREFIX_PAYMENT',
        'PAY'
    );
}

if (!defined('ID_PREFIX_NOTIFICATION')) {
    define(
        'ID_PREFIX_NOTIFICATION',
        'NTF'
    );
}

if (!defined('ID_PREFIX_FILE')) {
    define(
        'ID_PREFIX_FILE',
        'FIL'
    );
}

/*
|--------------------------------------------------------------------------
| Random ID Generation
|--------------------------------------------------------------------------
*/

function generate_uuid(): string
{
    $data = random_bytes(16);

    /*
     * RFC 4122 version 4 UUID.
     */
    $data[6] = chr(
        (ord($data[6]) & 0x0f) | 0x40
    );

    $data[8] = chr(
        (ord($data[8]) & 0x3f) | 0x80
    );

    return sprintf(
        '%s-%s-%s-%s-%s',
        bin2hex(substr($data, 0, 4)),
        bin2hex(substr($data, 4, 2)),
        bin2hex(substr($data, 6, 2)),
        bin2hex(substr($data, 8, 2)),
        bin2hex(substr($data, 10, 6))
    );
}

/*
|--------------------------------------------------------------------------
| Short ID
|--------------------------------------------------------------------------
*/

function generate_short_id(
    int $length = 12
): string {
    if ($length < 1) {
        $length = 12;
    }

    $characters =
        'ABCDEFGHJKLMNPQRSTUVWXYZ' .
        'abcdefghijkmnopqrstuvwxyz' .
        '23456789';

    $max =
        strlen($characters) - 1;

    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .=
            $characters[random_int(0, $max)];
    }

    return $result;
}

/*
|--------------------------------------------------------------------------
| Prefixed ID
|--------------------------------------------------------------------------
*/

function generate_prefixed_id(
    string $prefix,
    int $length = 12
): string {
    $prefix = strtoupper(
        trim($prefix)
    );

    $prefix = preg_replace(
        '/[^A-Z0-9]/',
        '',
        $prefix
    );

    if ($prefix === '') {
        return generate_short_id($length);
    }

    return
        $prefix .
        '_' .
        generate_short_id($length);
}

/*
|--------------------------------------------------------------------------
| Entity IDs
|--------------------------------------------------------------------------
*/

function generate_user_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_USER
    );
}

function generate_company_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_COMPANY
    );
}

function generate_training_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_TRAINING
    );
}

function generate_application_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_APPLICATION
    );
}

function generate_training_session_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_SESSION
    );
}

function generate_certificate_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_CERTIFICATE
    );
}

function generate_appeal_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_APPEAL
    );
}

function generate_payment_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_PAYMENT
    );
}

function generate_notification_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_NOTIFICATION
    );
}

function generate_file_id(): string
{
    return generate_prefixed_id(
        ID_PREFIX_FILE
    );
}

/*
|--------------------------------------------------------------------------
| Numeric ID Validation
|--------------------------------------------------------------------------
*/

function is_valid_numeric_id(
    mixed $id
): bool {
    if (is_int($id)) {
        return $id > 0;
    }

    if (!is_string($id)) {
        return false;
    }

    $id = trim($id);

    return
        $id !== '' &&
        ctype_digit($id) &&
        (int) $id > 0;
}

/*
|--------------------------------------------------------------------------
| Numeric ID Normalization
|--------------------------------------------------------------------------
*/

function normalize_numeric_id(
    mixed $id
): ?int {
    if (!is_valid_numeric_id($id)) {
        return null;
    }

    return (int) $id;
}

/*
|--------------------------------------------------------------------------
| String ID Validation
|--------------------------------------------------------------------------
*/

function is_valid_string_id(
    mixed $id,
    int $minLength = 1,
    int $maxLength = 100
): bool {
    if (!is_string($id)) {
        return false;
    }

    $id = trim($id);

    if ($id === '') {
        return false;
    }

    $length = strlen($id);

    return
        $length >= $minLength &&
        $length <= $maxLength &&
        preg_match(
            '/^[A-Za-z0-9_-]+$/',
            $id
        ) === 1;
}

/*
|--------------------------------------------------------------------------
| Prefixed ID Validation
|--------------------------------------------------------------------------
*/

function is_valid_prefixed_id(
    mixed $id,
    string $prefix
): bool {
    if (!is_string($id)) {
        return false;
    }

    $id = trim($id);

    $prefix = strtoupper(
        trim($prefix)
    );

    if ($id === '' || $prefix === '') {
        return false;
    }

    return preg_match(
        '/^' .
        preg_quote($prefix, '/') .
        '_[A-Za-z0-9_-]+$/',
        $id
    ) === 1;
}

/*
|--------------------------------------------------------------------------
| Prefix Extraction
|--------------------------------------------------------------------------
*/

function id_prefix(
    mixed $id
): ?string {
    if (!is_string($id)) {
        return null;
    }

    $id = trim($id);

    if ($id === '') {
        return null;
    }

    $position = strpos(
        $id,
        '_'
    );

    if ($position === false) {
        return null;
    }

    $prefix = substr(
        $id,
        0,
        $position
    );

    return $prefix !== ''
        ? strtoupper($prefix)
        : null;
}

/*
|--------------------------------------------------------------------------
| ID Value Extraction
|--------------------------------------------------------------------------
*/

function id_value(
    mixed $id
): ?string {
    if (!is_string($id)) {
        return null;
    }

    $id = trim($id);

    if ($id === '') {
        return null;
    }

    $position = strpos(
        $id,
        '_'
    );

    if ($position === false) {
        return $id;
    }

    $value = substr(
        $id,
        $position + 1
    );

    return $value !== ''
        ? $value
        : null;
}

/*
|--------------------------------------------------------------------------
| ID Comparison
|--------------------------------------------------------------------------
*/

function ids_are_equal(
    mixed $first,
    mixed $second
): bool {
    if (
        $first === null ||
        $second === null
    ) {
        return false;
    }

    return trim((string) $first)
        === trim((string) $second);
}

/*
|--------------------------------------------------------------------------
| ID List Normalization
|--------------------------------------------------------------------------
*/

function normalize_id_list(
    mixed $ids
): array {
    if (!is_array($ids)) {
        return [];
    }

    $result = [];

    foreach ($ids as $id) {
        if (
            is_string($id) ||
            is_int($id)
        ) {
            $id = trim((string) $id);

            if ($id !== '') {
                $result[] = $id;
            }
        }
    }

    return array_values(
        array_unique($result)
    );
}

/*
|--------------------------------------------------------------------------
| ID List Validation
|--------------------------------------------------------------------------
*/

function all_ids_are_valid(
    mixed $ids,
    bool $numeric = false
): bool {
    if (!is_array($ids)) {
        return false;
    }

    foreach ($ids as $id) {
        if ($numeric) {
            if (!is_valid_numeric_id($id)) {
                return false;
            }

            continue;
        }

        if (!is_valid_string_id($id)) {
            return false;
        }
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| Entity Prefix Mapping
|--------------------------------------------------------------------------
*/

function entity_id_prefixes(): array
{
    return [
        'user' =>
            ID_PREFIX_USER,

        'company' =>
            ID_PREFIX_COMPANY,

        'training' =>
            ID_PREFIX_TRAINING,

        'application' =>
            ID_PREFIX_APPLICATION,

        'training_session' =>
            ID_PREFIX_SESSION,

        'certificate' =>
            ID_PREFIX_CERTIFICATE,

        'appeal' =>
            ID_PREFIX_APPEAL,

        'payment' =>
            ID_PREFIX_PAYMENT,

        'notification' =>
            ID_PREFIX_NOTIFICATION,

        'file' =>
            ID_PREFIX_FILE
    ];
}

/*
|--------------------------------------------------------------------------
| Entity ID Generation
|--------------------------------------------------------------------------
*/

function generate_entity_id(
    string $entity
): ?string {
    $entity = strtolower(
        trim($entity)
    );

    $prefixes =
        entity_id_prefixes();

    if (!isset($prefixes[$entity])) {
        return null;
    }

    return generate_prefixed_id(
        $prefixes[$entity]
    );
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function generate_id(
    string $prefix = ''
): string {
    if ($prefix === '') {
        return generate_uuid();
    }

    return generate_prefixed_id(
        $prefix
    );
}

function is_valid_id(
    mixed $id
): bool {
    return
        is_valid_string_id($id) ||
        is_valid_numeric_id($id);
}
