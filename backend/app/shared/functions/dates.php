<?php

/**
 * MASAR - Date Functions
 *
 * Shared helpers for date/time validation, normalization,
 * formatting, comparison, and date ranges.
 */

/*
|--------------------------------------------------------------------------
| Default Formats
|--------------------------------------------------------------------------
*/

if (!defined('DATE_FORMAT_DEFAULT')) {
    define(
        'DATE_FORMAT_DEFAULT',
        'Y-m-d'
    );
}

if (!defined('DATETIME_FORMAT_DEFAULT')) {
    define(
        'DATETIME_FORMAT_DEFAULT',
        'Y-m-d H:i:s'
    );
}

if (!defined('TIME_FORMAT_DEFAULT')) {
    define(
        'TIME_FORMAT_DEFAULT',
        'H:i:s'
    );
}

/*
|--------------------------------------------------------------------------
| Current Date / Time
|--------------------------------------------------------------------------
*/

function current_date(): string
{
    return date(
        DATE_FORMAT_DEFAULT
    );
}

function current_datetime(): string
{
    return date(
        DATETIME_FORMAT_DEFAULT
    );
}

function current_timestamp(): int
{
    return time();
}

/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

function is_valid_date(
    mixed $date,
    string $format = DATE_FORMAT_DEFAULT
): bool {
    if (!is_string($date)) {
        return false;
    }

    $date = trim($date);

    if ($date === '') {
        return false;
    }

    $object = DateTime::createFromFormat(
        $format,
        $date
    );

    if ($object === false) {
        return false;
    }

    $errors = DateTime::getLastErrors();

    if (
        $errors !== false &&
        (
            $errors['warning_count'] > 0 ||
            $errors['error_count'] > 0
        )
    ) {
        return false;
    }

    return $object->format($format) === $date;
}

/*
|--------------------------------------------------------------------------
| DateTime Validation
|--------------------------------------------------------------------------
*/

function is_valid_datetime(
    mixed $datetime,
    string $format = DATETIME_FORMAT_DEFAULT
): bool {
    return is_valid_date(
        $datetime,
        $format
    );
}

/*
|--------------------------------------------------------------------------
| Date Normalization
|--------------------------------------------------------------------------
*/

function normalize_date(
    mixed $date,
    string $format = DATE_FORMAT_DEFAULT
): ?string {
    if (!is_string($date)) {
        return null;
    }

    $date = trim($date);

    if ($date === '') {
        return null;
    }

    $object = DateTime::createFromFormat(
        $format,
        $date
    );

    if ($object === false) {
        return null;
    }

    $errors = DateTime::getLastErrors();

    if (
        $errors !== false &&
        (
            $errors['warning_count'] > 0 ||
            $errors['error_count'] > 0
        )
    ) {
        return null;
    }

    return $object->format($format);
}

/*
|--------------------------------------------------------------------------
| DateTime Normalization
|--------------------------------------------------------------------------
*/

function normalize_datetime(
    mixed $datetime,
    string $format = DATETIME_FORMAT_DEFAULT
): ?string {
    return normalize_date(
        $datetime,
        $format
    );
}

/*
|--------------------------------------------------------------------------
| Date Parsing
|--------------------------------------------------------------------------
*/

function parse_date(
    mixed $date,
    ?DateTimeZone $timezone = null
): ?DateTime {
    if ($date instanceof DateTime) {
        return clone $date;
    }

    if ($date instanceof DateTimeImmutable) {
        return new DateTime(
            $date->format(DATETIME_FORMAT_DEFAULT),
            $timezone ?? $date->getTimezone()
        );
    }

    if (is_int($date)) {
        return (new DateTime(
            'now',
            $timezone
        ))->setTimestamp($date);
    }

    if (!is_string($date)) {
        return null;
    }

    $date = trim($date);

    if ($date === '') {
        return null;
    }

    try {
        return new DateTime(
            $date,
            $timezone
        );
    } catch (Exception) {
        return null;
    }
}

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

function format_date(
    mixed $date,
    string $format = DATE_FORMAT_DEFAULT
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    return $object->format($format);
}

function format_datetime(
    mixed $datetime,
    string $format = DATETIME_FORMAT_DEFAULT
): ?string {
    return format_date(
        $datetime,
        $format
    );
}

function format_time(
    mixed $datetime,
    string $format = TIME_FORMAT_DEFAULT
): ?string {
    return format_date(
        $datetime,
        $format
    );
}

/*
|--------------------------------------------------------------------------
| Date Comparison
|--------------------------------------------------------------------------
*/

function date_is_before(
    mixed $first,
    mixed $second
): bool {
    $first = parse_date($first);
    $second = parse_date($second);

    if ($first === null || $second === null) {
        return false;
    }

    return $first < $second;
}

function date_is_after(
    mixed $first,
    mixed $second
): bool {
    $first = parse_date($first);
    $second = parse_date($second);

    if ($first === null || $second === null) {
        return false;
    }

    return $first > $second;
}

function date_is_equal(
    mixed $first,
    mixed $second
): bool {
    $first = parse_date($first);
    $second = parse_date($second);

    if ($first === null || $second === null) {
        return false;
    }

    return $first == $second;
}

/*
|--------------------------------------------------------------------------
| Date Difference
|--------------------------------------------------------------------------
*/

function date_difference_days(
    mixed $start,
    mixed $end
): ?int {
    $start = parse_date($start);
    $end = parse_date($end);

    if ($start === null || $end === null) {
        return null;
    }

    return (int) $start
        ->diff($end)
        ->format('%r%a');
}

function date_difference_seconds(
    mixed $start,
    mixed $end
): ?int {
    $start = parse_date($start);
    $end = parse_date($end);

    if ($start === null || $end === null) {
        return null;
    }

    return $end->getTimestamp()
        - $start->getTimestamp();
}

/*
|--------------------------------------------------------------------------
| Date Arithmetic
|--------------------------------------------------------------------------
*/

function add_days(
    mixed $date,
    int $days
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->modify(
        ($days >= 0 ? '+' : '') .
        $days .
        ' days'
    );

    return $object->format(
        DATE_FORMAT_DEFAULT
    );
}

function subtract_days(
    mixed $date,
    int $days
): ?string {
    return add_days(
        $date,
        -$days
    );
}

function add_months(
    mixed $date,
    int $months
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->modify(
        ($months >= 0 ? '+' : '') .
        $months .
        ' months'
    );

    return $object->format(
        DATE_FORMAT_DEFAULT
    );
}

/*
|--------------------------------------------------------------------------
| Start / End Of Period
|--------------------------------------------------------------------------
*/

function start_of_day(
    mixed $date
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->setTime(
        0,
        0,
        0
    );

    return $object->format(
        DATETIME_FORMAT_DEFAULT
    );
}

function end_of_day(
    mixed $date
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->setTime(
        23,
        59,
        59
    );

    return $object->format(
        DATETIME_FORMAT_DEFAULT
    );
}

function start_of_month(
    mixed $date
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->modify('first day of this month');
    $object->setTime(0, 0, 0);

    return $object->format(
        DATETIME_FORMAT_DEFAULT
    );
}

function end_of_month(
    mixed $date
): ?string {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    $object->modify('last day of this month');
    $object->setTime(23, 59, 59);

    return $object->format(
        DATETIME_FORMAT_DEFAULT
    );
}

/*
|--------------------------------------------------------------------------
| Today / Past / Future
|--------------------------------------------------------------------------
*/

function is_today(
    mixed $date
): bool {
    $formatted = format_date($date);

    return $formatted !== null
        && $formatted === current_date();
}

function is_past_date(
    mixed $date
): bool {
    $object = parse_date($date);

    if ($object === null) {
        return false;
    }

    $today = new DateTime(
        'today',
        $object->getTimezone()
    );

    return $object < $today;
}

function is_future_date(
    mixed $date
): bool {
    $object = parse_date($date);

    if ($object === null) {
        return false;
    }

    $tomorrow = new DateTime(
        'tomorrow',
        $object->getTimezone()
    );

    return $object >= $tomorrow;
}

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

function date_range(
    mixed $start,
    mixed $end,
    int $step = 1
): array {
    $start = parse_date($start);
    $end = parse_date($end);

    if (
        $start === null ||
        $end === null ||
        $step <= 0 ||
        $start > $end
    ) {
        return [];
    }

    $dates = [];

    $current = clone $start;

    while ($current <= $end) {
        $dates[] =
            $current->format(
                DATE_FORMAT_DEFAULT
            );

        $current->modify(
            '+' . $step . ' days'
        );
    }

    return $dates;
}

/*
|--------------------------------------------------------------------------
| Weekday Helpers
|--------------------------------------------------------------------------
*/

function is_weekend(
    mixed $date
): bool {
    $object = parse_date($date);

    if ($object === null) {
        return false;
    }

    return (int) $object->format('N') >= 6;
}

function day_of_week(
    mixed $date
): ?int {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    return (int) $object->format('N');
}

/*
|--------------------------------------------------------------------------
| Unix Timestamp
|--------------------------------------------------------------------------
*/

function date_to_timestamp(
    mixed $date
): ?int {
    $object = parse_date($date);

    if ($object === null) {
        return null;
    }

    return $object->getTimestamp();
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_current_date(): string
{
    return current_date();
}

function get_current_datetime(): string
{
    return current_datetime();
}

function get_date_difference_days(
    mixed $start,
    mixed $end
): ?int {
    return date_difference_days(
        $start,
        $end
    );
}
