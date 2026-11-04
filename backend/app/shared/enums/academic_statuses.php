<?php

/**
 * MASAR - Academic Statuses
 *
 * Centralized definitions for the profile-level academic status of a student
 * (the Student / Graduate state shown on the Student Profile).
 *
 * IMPORTANT: this vocabulary is NOT interchangeable with
 * training_applications.applicant_type, whose enum is
 * ('student','graduated') and which is a PER APPLICATION self-declaration made
 * when applying to a training. The two answer different questions:
 *
 *   students.academic_status   "What is this student doing right now?"
 *   ...applicant_type          "What did this applicant say about themselves in
 *                              THIS application?"
 *
 * Training eligibility never reads students.academic_status, so changing a
 * student's academic status cannot allow or disallow a training application.
 */

/*
|--------------------------------------------------------------------------
| Academic Status
|--------------------------------------------------------------------------
*/

if (!defined('ACADEMIC_STATUS_STUDENT')) {
    define(
        'ACADEMIC_STATUS_STUDENT',
        'student'
    );
}

if (!defined('ACADEMIC_STATUS_GRADUATE')) {
    define(
        'ACADEMIC_STATUS_GRADUATE',
        'graduate'
    );
}

/*
|--------------------------------------------------------------------------
| Academic Status Collection
|--------------------------------------------------------------------------
*/

if (!function_exists('academic_statuses_all')) {
    /**
     * Every academic status the profile accepts, in display order.
     */
    function academic_statuses_all(): array
    {
        return [
            ACADEMIC_STATUS_STUDENT,
            ACADEMIC_STATUS_GRADUATE,
        ];
    }
}

if (!function_exists('academic_status_is_graduate')) {
    /**
     * Whether the status means the student has already graduated.
     */
    function academic_status_is_graduate(?string $status): bool
    {
        return $status === ACADEMIC_STATUS_GRADUATE;
    }
}

/*
|--------------------------------------------------------------------------
| Education Study Modes
|--------------------------------------------------------------------------
*/

if (!defined('STUDY_MODE_ONSITE')) {
    define(
        'STUDY_MODE_ONSITE',
        'onsite'
    );
}

if (!defined('STUDY_MODE_ONLINE')) {
    define(
        'STUDY_MODE_ONLINE',
        'online'
    );
}

if (!defined('STUDY_MODE_HYBRID')) {
    define(
        'STUDY_MODE_HYBRID',
        'hybrid'
    );
}

if (!function_exists('study_modes_all')) {
    /**
     * Every accepted education study mode.
     */
    function study_modes_all(): array
    {
        return [
            STUDY_MODE_ONSITE,
            STUDY_MODE_ONLINE,
            STUDY_MODE_HYBRID,
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Graduation Months
|--------------------------------------------------------------------------
|
| The twelve month names, indexed 1-12 to match students.graduation_month.
|
| Defined here rather than in a module so the validator (which must not depend
| on a service) and the account service can share one list instead of each
| carrying their own.
|
*/

if (!defined('ACADEMIC_GRADUATION_MONTH_MIN')) {
    define('ACADEMIC_GRADUATION_MONTH_MIN', 1);
}

if (!defined('ACADEMIC_GRADUATION_MONTH_MAX')) {
    define('ACADEMIC_GRADUATION_MONTH_MAX', 12);
}

if (!function_exists('academic_month_names')) {
    /**
     * Month number (1-12) => English month name.
     */
    function academic_month_names(): array
    {
        return [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }
}

if (!function_exists('academic_month_name')) {
    /**
     * The English name of a stored month number, or null when out of range.
     */
    function academic_month_name(?int $month): ?string
    {
        if ( $month === null ) {
            return null;
        }

        $names = academic_month_names();

        return $names[ $month ] ?? null;
    }
}

if (!function_exists('academic_month_number')) {
    /**
     * Resolves a month number or an English month name to 1-12, or null.
     *
     * A full name is matched first, then a three-letter abbreviation, so both
     * "September" and "Sep" are accepted while "Sept" is not a silent miss.
     */
    function academic_month_number(mixed $value): ?int
    {
        if ( $value === null ) {
            return null;
        }

        if ( is_int( $value ) || ( is_string( $value ) && ctype_digit( trim( $value ) ) ) ) {
            $number = (int) $value;

            return ( $number >= ACADEMIC_GRADUATION_MONTH_MIN && $number <= ACADEMIC_GRADUATION_MONTH_MAX )
                ? $number
                : null;
        }

        if ( !is_string( $value ) ) {
            return null;
        }

        $name = trim( $value );

        if ( $name === '' ) {
            return null;
        }

        foreach ( academic_month_names() as $number => $label ) {
            if ( strcasecmp( $label, $name ) === 0 ) {
                return $number;
            }
        }

        if ( strlen( $name ) >= 3 ) {
            foreach ( academic_month_names() as $number => $label ) {
                if ( strcasecmp( substr( $label, 0, 3 ), substr( $name, 0, 3 ) ) === 0 ) {
                    return $number;
                }
            }
        }

        return null;
    }
}

if (!function_exists('academic_month_is_valid')) {
    /**
     * Whether the value names a real month, in either accepted form.
     */
    function academic_month_is_valid(mixed $value): bool
    {
        return academic_month_number( $value ) !== null;
    }
}
