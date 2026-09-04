<?php

/**
 * MASAR - Pagination Functions
 *
 * Shared helpers for pagination calculations,
 * offsets, limits, metadata, and page navigation.
 */

/*
|--------------------------------------------------------------------------
| Default Pagination Values
|--------------------------------------------------------------------------
*/

if (!defined('PAGINATION_DEFAULT_PAGE')) {
    define(
        'PAGINATION_DEFAULT_PAGE',
        1
    );
}

if (!defined('PAGINATION_DEFAULT_PER_PAGE')) {
    define(
        'PAGINATION_DEFAULT_PER_PAGE',
        20
    );
}

if (!defined('PAGINATION_MAX_PER_PAGE')) {
    define(
        'PAGINATION_MAX_PER_PAGE',
        100
    );
}

/*
|--------------------------------------------------------------------------
| Page Normalization
|--------------------------------------------------------------------------
*/

function normalize_page(
    mixed $page
): int {
    if (
        is_string($page) &&
        trim($page) === ''
    ) {
        return PAGINATION_DEFAULT_PAGE;
    }

    if (!is_numeric($page)) {
        return PAGINATION_DEFAULT_PAGE;
    }

    $page = (int) $page;

    return max(
        1,
        $page
    );
}

/*
|--------------------------------------------------------------------------
| Per Page Normalization
|--------------------------------------------------------------------------
*/

function normalize_per_page(
    mixed $perPage
): int {
    if (
        $perPage === null ||
        $perPage === ''
    ) {
        return PAGINATION_DEFAULT_PER_PAGE;
    }

    if (!is_numeric($perPage)) {
        return PAGINATION_DEFAULT_PER_PAGE;
    }

    $perPage = (int) $perPage;

    if ($perPage < 1) {
        return PAGINATION_DEFAULT_PER_PAGE;
    }

    return min(
        $perPage,
        PAGINATION_MAX_PER_PAGE
    );
}

/*
|--------------------------------------------------------------------------
| Offset Calculation
|--------------------------------------------------------------------------
*/

function pagination_offset(
    mixed $page,
    mixed $perPage
): int {
    $page = normalize_page($page);
    $perPage = normalize_per_page($perPage);

    return (
        $page - 1
    ) * $perPage;
}

/*
|--------------------------------------------------------------------------
| Total Pages
|--------------------------------------------------------------------------
*/

function pagination_total_pages(
    mixed $total,
    mixed $perPage
): int {
    $total = max(
        0,
        (int) $total
    );

    $perPage =
        normalize_per_page($perPage);

    if ($total === 0) {
        return 0;
    }

    return (int) ceil(
        $total / $perPage
    );
}

/*
|--------------------------------------------------------------------------
| Page Bounds
|--------------------------------------------------------------------------
*/

function pagination_first_page(): int
{
    return PAGINATION_DEFAULT_PAGE;
}

function pagination_last_page(
    mixed $total,
    mixed $perPage
): int {
    return pagination_total_pages(
        $total,
        $perPage
    );
}

/*
|--------------------------------------------------------------------------
| Page Validation
|--------------------------------------------------------------------------
*/

function is_valid_page(
    mixed $page,
    mixed $total,
    mixed $perPage
): bool {
    $page = normalize_page($page);

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    if ($totalPages === 0) {
        return $page === 1;
    }

    return $page <= $totalPages;
}

/*
|--------------------------------------------------------------------------
| Page Clamping
|--------------------------------------------------------------------------
*/

function clamp_page(
    mixed $page,
    mixed $total,
    mixed $perPage
): int {
    $page = normalize_page($page);

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    if ($totalPages === 0) {
        return 1;
    }

    return min(
        $page,
        $totalPages
    );
}

/*
|--------------------------------------------------------------------------
| Has Previous / Next
|--------------------------------------------------------------------------
*/

function pagination_has_previous(
    mixed $page
): bool {
    return normalize_page($page) > 1;
}

function pagination_has_next(
    mixed $page,
    mixed $total,
    mixed $perPage
): bool {
    $page = normalize_page($page);

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    return $page < $totalPages;
}

/*
|--------------------------------------------------------------------------
| Previous / Next Page
|--------------------------------------------------------------------------
*/

function pagination_previous_page(
    mixed $page
): ?int {
    $page = normalize_page($page);

    if ($page <= 1) {
        return null;
    }

    return $page - 1;
}

function pagination_next_page(
    mixed $page,
    mixed $total,
    mixed $perPage
): ?int {
    $page = normalize_page($page);

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    if ($page >= $totalPages) {
        return null;
    }

    return $page + 1;
}

/*
|--------------------------------------------------------------------------
| Current Page Item Range
|--------------------------------------------------------------------------
*/

function pagination_from(
    mixed $page,
    mixed $perPage,
    mixed $total
): int {
    $total = max(
        0,
        (int) $total
    );

    if ($total === 0) {
        return 0;
    }

    $offset =
        pagination_offset(
            $page,
            $perPage
        );

    if ($offset >= $total) {
        return 0;
    }

    return $offset + 1;
}

function pagination_to(
    mixed $page,
    mixed $perPage,
    mixed $total
): int {
    $total = max(
        0,
        (int) $total
    );

    if ($total === 0) {
        return 0;
    }

    $offset =
        pagination_offset(
            $page,
            $perPage
        );

    if ($offset >= $total) {
        return 0;
    }

    return min(
        $offset + normalize_per_page($perPage),
        $total
    );
}

/*
|--------------------------------------------------------------------------
| Pagination Metadata
|--------------------------------------------------------------------------
*/

function pagination_meta(
    mixed $page,
    mixed $perPage,
    mixed $total
): array {
    $page = normalize_page($page);
    $perPage = normalize_per_page($perPage);

    $total = max(
        0,
        (int) $total
    );

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    if ($totalPages > 0) {
        $page = min(
            $page,
            $totalPages
        );
    }

    return [
        'current_page' =>
            $page,

        'per_page' =>
            $perPage,

        'total' =>
            $total,

        'total_pages' =>
            $totalPages,

        'from' =>
            pagination_from(
                $page,
                $perPage,
                $total
            ),

        'to' =>
            pagination_to(
                $page,
                $perPage,
                $total
            ),

        'has_previous' =>
            pagination_has_previous($page),

        'has_next' =>
            pagination_has_next(
                $page,
                $total,
                $perPage
            ),

        'previous_page' =>
            pagination_previous_page($page),

        'next_page' =>
            pagination_next_page(
                $page,
                $total,
                $perPage
            )
    ];
}

/*
|--------------------------------------------------------------------------
| Pagination Window
|--------------------------------------------------------------------------
*/

function pagination_pages(
    mixed $page,
    mixed $total,
    mixed $perPage,
    int $window = 2
): array {
    $page = normalize_page($page);

    $totalPages =
        pagination_total_pages(
            $total,
            $perPage
        );

    if ($totalPages <= 0) {
        return [];
    }

    $page = min(
        $page,
        $totalPages
    );

    $window = max(
        0,
        $window
    );

    $start = max(
        1,
        $page - $window
    );

    $end = min(
        $totalPages,
        $page + $window
    );

    $pages = [];

    for (
        $current = $start;
        $current <= $end;
        $current++
    ) {
        $pages[] = $current;
    }

    return $pages;
}

/*
|--------------------------------------------------------------------------
| Full Pagination Navigation
|--------------------------------------------------------------------------
*/

function pagination_navigation(
    mixed $page,
    mixed $total,
    mixed $perPage,
    int $window = 2
): array {
    $meta =
        pagination_meta(
            $page,
            $perPage,
            $total
        );

    $meta['pages'] =
        pagination_pages(
            $page,
            $total,
            $perPage,
            $window
        );

    return $meta;
}

/*
|--------------------------------------------------------------------------
| Limit / Offset Pair
|--------------------------------------------------------------------------
*/

function pagination_limit_offset(
    mixed $page,
    mixed $perPage
): array {
    $perPage =
        normalize_per_page($perPage);

    return [
        'limit' =>
            $perPage,

        'offset' =>
            pagination_offset(
                $page,
                $perPage
            )
    ];
}

/*
|--------------------------------------------------------------------------
| Pagination Query Parameters
|--------------------------------------------------------------------------
*/

function pagination_query_params(
    mixed $page,
    mixed $perPage
): array {
    return [
        'page' =>
            normalize_page($page),

        'per_page' =>
            normalize_per_page($perPage)
    ];
}

/*
|--------------------------------------------------------------------------
| Pagination Input
|--------------------------------------------------------------------------
*/

function pagination_from_input(
    mixed $input
): array {
    if (!is_array($input)) {
        $input = [];
    }

    $page =
        $input['page']
        ?? PAGINATION_DEFAULT_PAGE;

    $perPage =
        $input['per_page']
        ?? PAGINATION_DEFAULT_PER_PAGE;

    return [
        'page' =>
            normalize_page($page),

        'per_page' =>
            normalize_per_page($perPage)
    ];
}

/*
|--------------------------------------------------------------------------
| Empty Pagination
|--------------------------------------------------------------------------
*/

function empty_pagination(
    mixed $perPage = null
): array {
    $perPage =
        normalize_per_page($perPage);

    return [
        'current_page' => 1,
        'per_page' => $perPage,
        'total' => 0,
        'total_pages' => 0,
        'from' => 0,
        'to' => 0,
        'has_previous' => false,
        'has_next' => false,
        'previous_page' => null,
        'next_page' => null,
        'pages' => []
    ];
}

/*
|--------------------------------------------------------------------------
| Compatibility Helpers
|--------------------------------------------------------------------------
*/

function get_pagination_offset(
    mixed $page,
    mixed $perPage
): int {
    return pagination_offset(
        $page,
        $perPage
    );
}

function get_total_pages(
    mixed $total,
    mixed $perPage
): int {
    return pagination_total_pages(
        $total,
        $perPage
    );
}

function get_pagination_meta(
    mixed $page,
    mixed $perPage,
    mixed $total
): array {
    return pagination_meta(
        $page,
        $perPage,
        $total
    );
}
