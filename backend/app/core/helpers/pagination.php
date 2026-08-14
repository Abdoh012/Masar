<?php

/**
 * MASAR Pagination Helper
 *
 * Provides pagination calculations and
 * standardized pagination metadata.
 */


/*
|--------------------------------------------------------------------------
| Default Pagination Values
|--------------------------------------------------------------------------
*/

function pagination_default_page(): int
{
    return 1;
}


function pagination_default_per_page(): int
{
    return 20;
}


/*
|--------------------------------------------------------------------------
| Maximum Per Page
|--------------------------------------------------------------------------
|
| Prevents users from requesting extremely
| large datasets in a single request.
|
*/

function pagination_max_per_page(): int
{
    return 100;
}


/*
|--------------------------------------------------------------------------
| Normalize Page
|--------------------------------------------------------------------------
*/

function pagination_page(
    mixed $page
): int {

    $page = filter_var(
        $page,
        FILTER_VALIDATE_INT
    );

    if (
        $page === false
        || $page < 1
    ) {

        return pagination_default_page();
    }

    return $page;
}


/*
|--------------------------------------------------------------------------
| Normalize Per Page
|--------------------------------------------------------------------------
*/

function pagination_per_page(
    mixed $per_page
): int {

    $per_page = filter_var(
        $per_page,
        FILTER_VALIDATE_INT
    );

    if (
        $per_page === false
        || $per_page < 1
    ) {

        return pagination_default_per_page();
    }

    return min(
        $per_page,
        pagination_max_per_page()
    );
}


/*
|--------------------------------------------------------------------------
| Calculate Offset
|--------------------------------------------------------------------------
*/

function pagination_offset(
    int $page,
    int $per_page
): int {

    $page =
        pagination_page($page);

    $per_page =
        pagination_per_page($per_page);

    return (
        $page - 1
    ) * $per_page;
}


/*
|--------------------------------------------------------------------------
| Calculate Total Pages
|--------------------------------------------------------------------------
*/

function pagination_total_pages(
    int $total,
    int $per_page
): int {

    if ($total <= 0) {
        return 0;
    }

    $per_page =
        pagination_per_page($per_page);

    return (int) ceil(
        $total / $per_page
    );
}


/*
|--------------------------------------------------------------------------
| Has Previous Page
|--------------------------------------------------------------------------
*/

function pagination_has_previous(
    int $page
): bool {

    return $page > 1;
}


/*
|--------------------------------------------------------------------------
| Has Next Page
|--------------------------------------------------------------------------
*/

function pagination_has_next(
    int $page,
    int $total_pages
): bool {

    return $page < $total_pages;
}


/*
|--------------------------------------------------------------------------
| Build Pagination Metadata
|--------------------------------------------------------------------------
*/

function pagination_meta(
    int $page,
    int $per_page,
    int $total
): array {

    $page =
        pagination_page($page);

    $per_page =
        pagination_per_page($per_page);

    $total_pages =
        pagination_total_pages(
            $total,
            $per_page
        );

    /*
    |--------------------------------------------------------------------------
    | Correct Page If Beyond Last Page
    |--------------------------------------------------------------------------
    */

    if (
        $total_pages > 0
        &&
        $page > $total_pages
    ) {

        $page =
            $total_pages;
    }

    return [

        'current_page' =>
            $page,

        'per_page' =>
            $per_page,

        'total' =>
            $total,

        'total_pages' =>
            $total_pages,

        'has_previous' =>
            pagination_has_previous(
                $page
            ),

        'has_next' =>
            pagination_has_next(
                $page,
                $total_pages
            ),

        'from' =>
            $total > 0
                ? (($page - 1) * $per_page) + 1
                : null,

        'to' =>
            $total > 0
                ? min(
                    $page * $per_page,
                    $total
                )
                : null,

    ];
}


/*
|--------------------------------------------------------------------------
| Build Pagination Result
|--------------------------------------------------------------------------
|
| Standard structure for API responses.
|
*/

function pagination_result(
    array $items,
    int $page,
    int $per_page,
    int $total
): array {

    return [

        'data' =>
            array_values($items),

        'pagination' =>
            pagination_meta(
                $page,
                $per_page,
                $total
            ),

    ];
}


/*
|--------------------------------------------------------------------------
| Get Pagination From Request
|--------------------------------------------------------------------------
|
| This function assumes request_input()
| is available from the HTTP layer.
|
*/

function pagination_from_request(
    array $input
): array {

    $page =
        pagination_page(
            $input['page'] ?? 1
        );

    $per_page =
        pagination_per_page(
            $input['per_page'] ?? 20
        );

    return [

        'page' =>
            $page,

        'per_page' =>
            $per_page,

        'offset' =>
            pagination_offset(
                $page,
                $per_page
            ),

    ];
}


/*
|--------------------------------------------------------------------------
| Build SQL LIMIT/OFFSET
|--------------------------------------------------------------------------
|
| Returns integer values only.
| They should be inserted into SQL only
| after passing through this helper.
|
*/

function pagination_sql(
    int $page,
    int $per_page
): array {

    $page =
        pagination_page($page);

    $per_page =
        pagination_per_page($per_page);

    return [

        'limit' =>
            $per_page,

        'offset' =>
            pagination_offset(
                $page,
                $per_page
            ),

    ];
}


/*
|--------------------------------------------------------------------------
| Pagination Query Parameters
|--------------------------------------------------------------------------
*/

function pagination_query_params(
    int $page,
    int $per_page
): array {

    return [

        'page' =>
            pagination_page($page),

        'per_page' =>
            pagination_per_page($per_page),

    ];
}


/*
|--------------------------------------------------------------------------
| Generate Pagination Links Data
|--------------------------------------------------------------------------
|
| The backend does not generate frontend HTML.
| It only returns the information needed by
| the frontend to build pagination controls.
|
*/

function pagination_links(
    int $page,
    int $total_pages
): array {

    $links = [];

    if ($page > 1) {

        $links['previous'] = [
            'page' => $page - 1,
        ];
    }

    if ($page < $total_pages) {

        $links['next'] = [
            'page' => $page + 1,
        ];
    }

    return $links;
}


/*
|--------------------------------------------------------------------------
| Validate Pagination Parameters
|--------------------------------------------------------------------------
|
| Unlike pagination_page() and pagination_per_page(),
| this function does not silently correct invalid input.
|
*/

function pagination_validate(
    mixed $page,
    mixed $per_page
): array {

    $errors = [];

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    if (
        filter_var(
            $page,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $page < 1
    ) {

        $errors['page'][] =
            'Page must be a positive integer.';
    }


    /*
    |--------------------------------------------------------------------------
    | Per Page
    |--------------------------------------------------------------------------
    */

    if (
        filter_var(
            $per_page,
            FILTER_VALIDATE_INT
        ) === false
        ||
        (int) $per_page < 1
    ) {

        $errors['per_page'][] =
            'Per page must be a positive integer.';
    }
    elseif (
        (int) $per_page
        > pagination_max_per_page()
    ) {

        $errors['per_page'][] =
            'Per page exceeds the maximum allowed value.';
    }


    return $errors;
}


/*
|--------------------------------------------------------------------------
| Is First Page
|--------------------------------------------------------------------------
*/

function pagination_is_first(
    int $page
): bool {

    return pagination_page($page) === 1;
}


/*
|--------------------------------------------------------------------------
| Is Last Page
|--------------------------------------------------------------------------
*/

function pagination_is_last(
    int $page,
    int $total_pages
): bool {

    if ($total_pages <= 0) {
        return true;
    }

    return pagination_page($page)
        >= $total_pages;
}
