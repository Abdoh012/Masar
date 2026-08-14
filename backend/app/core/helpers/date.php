<?php

/**
 * MASAR Date Helper
 *
 * Provides common date/time utilities used across
 * the MASAR backend.
 */


/*
|--------------------------------------------------------------------------
| Default Date Format
|--------------------------------------------------------------------------
*/

function date_default_format(): string
{
    return 'Y-m-d H:i:s';
}


/*
|--------------------------------------------------------------------------
| Current DateTime
|--------------------------------------------------------------------------
*/

function date_now(
    string $format = 'Y-m-d H:i:s'
): string {

    return date($format);
}


/*
|--------------------------------------------------------------------------
| Current Date
|--------------------------------------------------------------------------
*/

function date_today(): string
{
    return date('Y-m-d');
}


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

function date_format_value(
    string $date,
    string $format = 'Y-m-d H:i:s'
): ?string {

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return null;
    }

    return date(
        $format,
        $timestamp
    );
}


/*
|--------------------------------------------------------------------------
| Parse Date
|--------------------------------------------------------------------------
*/

function date_parse_value(
    string $date
): ?DateTime {

    try {

        return new DateTime($date);

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Validate Date
|--------------------------------------------------------------------------
*/

function date_is_valid(
    string $date,
    string $format = 'Y-m-d'
): bool {

    $parsed =
        DateTime::createFromFormat(
            $format,
            $date
        );

    if ($parsed === false) {
        return false;
    }

    return $parsed->format($format)
        === $date;
}


/*
|--------------------------------------------------------------------------
| Validate DateTime
|--------------------------------------------------------------------------
*/

function datetime_is_valid(
    string $datetime
): bool {

    try {

        new DateTime($datetime);

        return true;

    } catch (Exception $exception) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Date Before
|--------------------------------------------------------------------------
*/

function date_is_before(
    string $date,
    string $compare_date
): bool {

    $first =
        strtotime($date);

    $second =
        strtotime($compare_date);

    if (
        $first === false ||
        $second === false
    ) {

        return false;
    }

    return $first < $second;
}


/*
|--------------------------------------------------------------------------
| Date After
|--------------------------------------------------------------------------
*/

function date_is_after(
    string $date,
    string $compare_date
): bool {

    $first =
        strtotime($date);

    $second =
        strtotime($compare_date);

    if (
        $first === false ||
        $second === false
    ) {

        return false;
    }

    return $first > $second;
}


/*
|--------------------------------------------------------------------------
| Date Equal
|--------------------------------------------------------------------------
*/

function date_is_equal(
    string $date,
    string $compare_date
): bool {

    $first =
        strtotime($date);

    $second =
        strtotime($compare_date);

    if (
        $first === false ||
        $second === false
    ) {

        return false;
    }

    return $first === $second;
}


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

function date_is_in_range(
    string $date,
    string $start_date,
    string $end_date
): bool {

    $timestamp =
        strtotime($date);

    $start =
        strtotime($start_date);

    $end =
        strtotime($end_date);

    if (
        $timestamp === false ||
        $start === false ||
        $end === false
    ) {

        return false;
    }

    return $timestamp >= $start
        && $timestamp <= $end;
}


/*
|--------------------------------------------------------------------------
| Add Days
|--------------------------------------------------------------------------
*/

function date_add_days(
    string $date,
    int $days
): ?string {

    try {

        $datetime =
            new DateTime($date);

        $datetime->modify(
            ($days >= 0 ? '+' : '')
            . $days
            . ' days'
        );

        return $datetime->format(
            'Y-m-d H:i:s'
        );

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Subtract Days
|--------------------------------------------------------------------------
*/

function date_subtract_days(
    string $date,
    int $days
): ?string {

    return date_add_days(
        $date,
        -abs($days)
    );
}


/*
|--------------------------------------------------------------------------
| Add Hours
|--------------------------------------------------------------------------
*/

function date_add_hours(
    string $date,
    int $hours
): ?string {

    try {

        $datetime =
            new DateTime($date);

        $datetime->modify(
            ($hours >= 0 ? '+' : '')
            . $hours
            . ' hours'
        );

        return $datetime->format(
            'Y-m-d H:i:s'
        );

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Difference In Days
|--------------------------------------------------------------------------
*/

function date_difference_days(
    string $start_date,
    string $end_date
): ?int {

    try {

        $start =
            new DateTime($start_date);

        $end =
            new DateTime($end_date);

        $difference =
            $start->diff($end);

        return $difference->days;

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Difference In Hours
|--------------------------------------------------------------------------
*/

function date_difference_hours(
    string $start_date,
    string $end_date
): ?int {

    $start =
        strtotime($start_date);

    $end =
        strtotime($end_date);

    if (
        $start === false ||
        $end === false
    ) {

        return null;
    }

    return (int) floor(
        abs($end - $start) / 3600
    );
}


/*
|--------------------------------------------------------------------------
| Start Of Day
|--------------------------------------------------------------------------
*/

function date_start_of_day(
    string $date
): ?string {

    try {

        $datetime =
            new DateTime($date);

        $datetime->setTime(
            0,
            0,
            0
        );

        return $datetime->format(
            'Y-m-d H:i:s'
        );

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| End Of Day
|--------------------------------------------------------------------------
*/

function date_end_of_day(
    string $date
): ?string {

    try {

        $datetime =
            new DateTime($date);

        $datetime->setTime(
            23,
            59,
            59
        );

        return $datetime->format(
            'Y-m-d H:i:s'
        );

    } catch (Exception $exception) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Training End Date
|--------------------------------------------------------------------------
|
| Used for calculating training periods.
|
*/

function date_training_end(
    string $start_date,
    int $duration_days
): ?string {

    if ($duration_days < 1) {
        return null;
    }

    return date_add_days(
        $start_date,
        $duration_days
    );
}


/*
|--------------------------------------------------------------------------
| Trial End Date
|--------------------------------------------------------------------------
|
| Paid training must respect the platform's
| minimum free trial period.
|
*/

function date_trial_end(
    string $start_date,
    int $trial_days,
    int $minimum_days = 7
): ?string {

    if ($trial_days < $minimum_days) {
        return null;
    }

    return date_add_days(
        $start_date,
        $trial_days
    );
}


/*
|--------------------------------------------------------------------------
| Is Expired
|--------------------------------------------------------------------------
*/

function date_is_expired(
    string $date
): bool {

    $timestamp =
        strtotime($date);

    if ($timestamp === false) {
        return false;
    }

    return $timestamp < time();
}


/*
|--------------------------------------------------------------------------
| Is Future
|--------------------------------------------------------------------------
*/

function date_is_future(
    string $date
): bool {

    $timestamp =
        strtotime($date);

    if ($timestamp === false) {
        return false;
    }

    return $timestamp > time();
}


/*
|--------------------------------------------------------------------------
| Is Today
|--------------------------------------------------------------------------
*/

function date_is_today(
    string $date
): bool {

    $timestamp =
        strtotime($date);

    if ($timestamp === false) {
        return false;
    }

    return date(
        'Y-m-d',
        $timestamp
    ) === date_today();
}
