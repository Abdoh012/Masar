<?php

/**
 * MASAR String Helper
 *
 * Provides common string utilities used across
 * the MASAR backend.
 */


/*
|--------------------------------------------------------------------------
| Trim String
|--------------------------------------------------------------------------
*/

function string_trim(
    mixed $value
): string {

    return trim(
        (string) $value
    );
}


/*
|--------------------------------------------------------------------------
| Lowercase
|--------------------------------------------------------------------------
*/

function string_lower(
    mixed $value
): string {

    return mb_strtolower(
        trim((string) $value),
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Uppercase
|--------------------------------------------------------------------------
*/

function string_upper(
    mixed $value
): string {

    return mb_strtoupper(
        trim((string) $value),
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Capitalize First Letter
|--------------------------------------------------------------------------
*/

function string_ucfirst(
    mixed $value
): string {

    $value = trim(
        (string) $value
    );

    if ($value === '') {
        return '';
    }

    return mb_strtoupper(
        mb_substr($value, 0, 1, 'UTF-8'),
        'UTF-8'
    )
    .
    mb_substr(
        $value,
        1,
        null,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Normalize Spaces
|--------------------------------------------------------------------------
*/

function string_normalize_spaces(
    mixed $value
): string {

    $value = trim(
        (string) $value
    );

    $value = preg_replace(
        '/\s+/u',
        ' ',
        $value
    );

    return $value ?? '';
}


/*
|--------------------------------------------------------------------------
| Normalize Text
|--------------------------------------------------------------------------
|
| Trims the string and normalizes repeated spaces.
|
*/

function string_normalize(
    mixed $value
): string {

    return string_normalize_spaces(
        $value
    );
}


/*
|--------------------------------------------------------------------------
| String Length
|--------------------------------------------------------------------------
*/

function string_length(
    mixed $value
): int {

    return mb_strlen(
        (string) $value,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Limit String Length
|--------------------------------------------------------------------------
|
| Adds "..." when the string exceeds the limit.
|
*/

function string_limit(
    mixed $value,
    int $limit,
    string $suffix = '...'
): string {

    $value = trim(
        (string) $value
    );

    if ($limit < 1) {
        return '';
    }

    if (
        string_length($value)
        <= $limit
    ) {

        return $value;
    }

    $available_length =
        $limit - string_length($suffix);

    if ($available_length <= 0) {
        return mb_substr(
            $suffix,
            0,
            $limit,
            'UTF-8'
        );
    }

    return mb_substr(
        $value,
        0,
        $available_length,
        'UTF-8'
    )
    . $suffix;
}


/*
|--------------------------------------------------------------------------
| Contains
|--------------------------------------------------------------------------
*/

function string_contains(
    mixed $haystack,
    mixed $needle
): bool {

    return mb_strpos(
        (string) $haystack,
        (string) $needle,
        0,
        'UTF-8'
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| Starts With
|--------------------------------------------------------------------------
*/

function string_starts_with(
    mixed $value,
    mixed $prefix
): bool {

    $value = (string) $value;
    $prefix = (string) $prefix;

    if ($prefix === '') {
        return true;
    }

    return mb_substr(
        $value,
        0,
        string_length($prefix),
        'UTF-8'
    ) === $prefix;
}


/*
|--------------------------------------------------------------------------
| Ends With
|--------------------------------------------------------------------------
*/

function string_ends_with(
    mixed $value,
    mixed $suffix
): bool {

    $value = (string) $value;
    $suffix = (string) $suffix;

    if ($suffix === '') {
        return true;
    }

    return mb_substr(
        $value,
        -string_length($suffix),
        null,
        'UTF-8'
    ) === $suffix;
}


/*
|--------------------------------------------------------------------------
| Remove HTML
|--------------------------------------------------------------------------
|
| Used when plain text is expected.
|
*/

function string_strip_html(
    mixed $value
): string {

    return trim(
        strip_tags(
            (string) $value
        )
    );
}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
|
| Useful when text is rendered into HTML.
|
*/

function string_escape_html(
    mixed $value
): string {

    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Generate Random String
|--------------------------------------------------------------------------
*/

function string_random(
    int $length = 32
): string {

    if ($length < 1) {
        return '';
    }

    $characters =
        'abcdefghijklmnopqrstuvwxyz'
        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        . '0123456789';

    $characters_length =
        strlen($characters);

    $result = '';

    for (
        $i = 0;
        $i < $length;
        $i++
    ) {

        $result .= $characters[
            random_int(
                0,
                $characters_length - 1
            )
        ];
    }

    return $result;
}


/*
|--------------------------------------------------------------------------
| Generate Random Token
|--------------------------------------------------------------------------
|
| Cryptographically secure token.
|
*/

function string_random_token(
    int $bytes = 32
): string {

    if ($bytes < 1) {
        $bytes = 32;
    }

    return bin2hex(
        random_bytes($bytes)
    );
}


/*
|--------------------------------------------------------------------------
| Generate UUID v4
|--------------------------------------------------------------------------
*/

function string_uuid(): string
{
    $data = random_bytes(16);

    /*
    |--------------------------------------------------------------------------
    | Set UUID Version
    |--------------------------------------------------------------------------
    */

    $data[6] =
        chr(
            (ord($data[6]) & 0x0f)
            | 0x40
        );

    /*
    |--------------------------------------------------------------------------
    | Set UUID Variant
    |--------------------------------------------------------------------------
    */

    $data[8] =
        chr(
            (ord($data[8]) & 0x3f)
            | 0x80
        );

    return sprintf(
        '%s-%s-%s-%s-%s',

        bin2hex(
            substr($data, 0, 4)
        ),

        bin2hex(
            substr($data, 4, 2)
        ),

        bin2hex(
            substr($data, 6, 2)
        ),

        bin2hex(
            substr($data, 8, 2)
        ),

        bin2hex(
            substr($data, 10, 6)
        )
    );
}


/*
|--------------------------------------------------------------------------
| Slugify
|--------------------------------------------------------------------------
|
| Useful for readable URLs or identifiers.
|
*/

function string_slug(
    mixed $value
): string {

    $value = string_normalize(
        $value
    );

    if ($value === '') {
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Convert Spaces
    |--------------------------------------------------------------------------
    */

    $value = preg_replace(
        '/[\s_]+/u',
        '-',
        $value
    );

    /*
    |--------------------------------------------------------------------------
    | Remove Unsupported Characters
    |--------------------------------------------------------------------------
    |
    | Keep Unicode letters and numbers.
    |
    */

    $value = preg_replace(
        '/[^\p{L}\p{N}\-]+/u',
        '',
        $value
    );

    /*
    |--------------------------------------------------------------------------
    | Remove Duplicate Hyphens
    |--------------------------------------------------------------------------
    */

    $value = preg_replace(
        '/-+/u',
        '-',
        $value
    );

    return trim(
        $value ?? '',
        '-'
    );
}


/*
|--------------------------------------------------------------------------
| Mask Email
|--------------------------------------------------------------------------
|
| Example:
| mohamed@example.com
| becomes:
| mo*****@example.com
|
*/

function string_mask_email(
    string $email
): string {

    $email = trim(
        $email
    );

    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        return '';
    }

    [$username, $domain] =
        explode(
            '@',
            $email,
            2
        );

    $length =
        mb_strlen(
            $username,
            'UTF-8'
        );

    if ($length <= 2) {

        return
            mb_substr(
                $username,
                0,
                1,
                'UTF-8'
            )
            . '***@'
            . $domain;
    }

    return
        mb_substr(
            $username,
            0,
            2,
            'UTF-8'
        )
        . '***@'
        . $domain;
}


/*
|--------------------------------------------------------------------------
| Convert To Array
|--------------------------------------------------------------------------
*/

function string_to_array(
    mixed $value,
    string $separator = ','
): array {

    if (is_array($value)) {
        return $value;
    }

    $value = trim(
        (string) $value
    );

    if ($value === '') {
        return [];
    }

    $items =
        explode(
            $separator,
            $value
        );

    $result = [];

    foreach ($items as $item) {

        $item = trim($item);

        if ($item !== '') {
            $result[] = $item;
        }
    }

    return $result;
}


/*
|--------------------------------------------------------------------------
| Convert Array To String
|--------------------------------------------------------------------------
*/

function string_from_array(
    array $values,
    string $separator = ', '
): string {

    $result = [];

    foreach ($values as $value) {

        if (
            is_scalar($value)
            && trim((string) $value) !== ''
        ) {

            $result[] =
                trim((string) $value);
        }
    }

    return implode(
        $separator,
        $result
    );
}


/*
|--------------------------------------------------------------------------
| Check Empty String
|--------------------------------------------------------------------------
*/

function string_is_empty(
    mixed $value
): bool {

    return trim(
        (string) $value
    ) === '';
}


/*
|--------------------------------------------------------------------------
| Check Non Empty String
|--------------------------------------------------------------------------
*/

function string_is_not_empty(
    mixed $value
): bool {

    return !string_is_empty(
        $value
    );
}
