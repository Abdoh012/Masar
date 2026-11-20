<?php

/**
 * MASAR - Conversation Validator
 *
 * Structural validation for the Conversations List query string.
 */


if (!defined('CONVERSATION_PAGE_DEFAULT_LIMIT')) {
    define('CONVERSATION_PAGE_DEFAULT_LIMIT', 20);
}


/*
|--------------------------------------------------------------------------
| List Filters
|--------------------------------------------------------------------------
|
| limit: 1..100, default 20. offset: >= 0, default 0. application_id and
| keyword are optional filters.
*/

function conversation_validator_list_filters(
    array $query
): array {

    $errors = [];

    $filters = [
        'limit'  => CONVERSATION_PAGE_DEFAULT_LIMIT,
        'offset' => 0,
    ];

    /*
     * limit: 1..100, default 20.
     */

    if (
        array_key_exists('limit', $query)
        &&
        $query['limit'] !== ''
    ) {

        $limit =
            filter_var(
                $query['limit'],
                FILTER_VALIDATE_INT
            );


        if (
            $limit === false ||
            $limit < 1 ||
            $limit > 100
        ) {

            $errors['limit'] =
                'Limit must be an integer between 1 and 100.';

        } else {

            $filters['limit'] =
                (int) $limit;
        }
    }


    /*
     * offset: >= 0, default 0.
     */

    if (
        array_key_exists('offset', $query)
        &&
        $query['offset'] !== ''
    ) {

        $offset =
            filter_var(
                $query['offset'],
                FILTER_VALIDATE_INT
            );


        if (
            $offset === false ||
            $offset < 0
        ) {

            $errors['offset'] =
                'Offset must be an integer greater than or equal to 0.';

        } else {

            $filters['offset'] =
                (int) $offset;
        }
    }


    /*
     * application_id: positive integer when present.
     */

    if (
        isset($query['application_id'])
        &&
        $query['application_id'] !== ''
    ) {

        $application_id =
            filter_var(
                $query['application_id'],
                FILTER_VALIDATE_INT
            );


        if (
            $application_id === false ||
            (int) $application_id < 1
        ) {

            $errors['application_id'] =
                'A valid application ID is required.';

        } else {

            $filters['application_id'] =
                (int) $application_id;
        }
    }


    /*
     * keyword: non-empty text filter.
     */

    $keyword =
        trim(
            (string) (
                $query['keyword']
                ?? ''
            )
        );


    if ($keyword !== '') {

        $filters['keyword'] =
            $keyword;
    }


    return [
        'valid'  => $errors === [],
        'value'  => $filters,
        'errors' => $errors,
    ];
}
