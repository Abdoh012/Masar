<?php

/**
 * MASAR - Search Controller
 *
 * Handles search-related requests and delegates
 * search operations to SearchService.
 *
 * Controller
 *     ↓
 * SearchService
 *     ↓
 * SearchRepository
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

$service_file =
    __DIR__ .
    '/../services/search_service.php';

if (file_exists($service_file)) {
    require_once $service_file;
}


$repository_file =
    __DIR__ .
    '/../repositories/search_repository.php';

if (file_exists($repository_file)) {
    require_once $repository_file;
}


/*
|--------------------------------------------------------------------------
| Search Controller
|--------------------------------------------------------------------------
*/

class SearchController
{
    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    protected function service(): mixed
    {
        if (
            class_exists(
                'SearchService'
            )
        ) {
            return new SearchService();
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function search(
        array $request = [],
        int $user_id = 0
    ): array {

        $query =
            trim(
                (string) (
                    $request['q']
                    ?? $request['query']
                    ?? $request['search']
                    ?? ''
                )
            );


        if ($query === '') {
            return $this->error(
                'Search query is required.'
            );
        }


        $page =
            max(
                1,
                (int) (
                    $request['page']
                    ?? 1
                )
            );


        $limit =
            max(
                1,
                min(
                    100,
                    (int) (
                        $request['limit']
                        ?? 20
                    )
                )
            );


        $filters = [
            'query' =>
                $query,

            'q' =>
                $query,

            'page' =>
                $page,

            'limit' =>
                $limit,

            'user_id' =>
                $user_id,

            'type' =>
                $request['type']
                ?? null,

            'category' =>
                $request['category']
                ?? null,

            'status' =>
                $request['status']
                ?? null,

            'sort' =>
                $request['sort']
                ?? 'relevance',

            'order' =>
                $request['order']
                ?? 'DESC'
        ];


        $service =
            $this->service();


        if (
            $service === null
        ) {
            return $this->error(
                'Search service is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $service,
                    'search'
                )
            ) {

                $result =
                    $service->search(
                        $query,
                        $filters
                    );

            } elseif (
                method_exists(
                    $service,
                    'execute'
                )
            ) {

                $result =
                    $service->execute(
                        $filters
                    );

            } else {

                return $this->error(
                    'Search method is unavailable.'
                );
            }


            return $this->success(
                $result,
                'Search completed successfully.'
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to complete search.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Global Search
    |--------------------------------------------------------------------------
    */

    public function global(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->search(
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Users
    |--------------------------------------------------------------------------
    */

    public function users(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->searchByType(
            'users',
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Companies
    |--------------------------------------------------------------------------
    */

    public function companies(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->searchByType(
            'companies',
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Trainings
    |--------------------------------------------------------------------------
    */

    public function trainings(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->searchByType(
            'trainings',
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Students
    |--------------------------------------------------------------------------
    */

    public function students(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->searchByType(
            'students',
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Certificates
    |--------------------------------------------------------------------------
    */

    public function certificates(
        array $request = [],
        int $user_id = 0
    ): array {

        return $this->searchByType(
            'certificates',
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search By Type
    |--------------------------------------------------------------------------
    */

    protected function searchByType(
        string $type,
        array $request = [],
        int $user_id = 0
    ): array {

        $request['type'] =
            $type;


        return $this->search(
            $request,
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Suggestions
    |--------------------------------------------------------------------------
    */

    public function suggestions(
        array $request = [],
        int $user_id = 0
    ): array {

        $query =
            trim(
                (string) (
                    $request['q']
                    ?? $request['query']
                    ?? ''
                )
            );


        if ($query === '') {
            return $this->error(
                'Search query is required.'
            );
        }


        $service =
            $this->service();


        if (
            $service === null
        ) {
            return $this->error(
                'Search service is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $service,
                    'suggestions'
                )
            ) {

                $result =
                    $service->suggestions(
                        $query,
                        [
                            'user_id' =>
                                $user_id,

                            'limit' =>
                                min(
                                    20,
                                    max(
                                        1,
                                        (int) (
                                            $request['limit']
                                            ?? 10
                                        )
                                    )
                                )
                        ]
                    );

            } elseif (
                method_exists(
                    $service,
                    'suggest'
                )
            ) {

                $result =
                    $service->suggest(
                        $query,
                        $user_id
                    );

            } else {

                return $this->error(
                    'Suggestion method is unavailable.'
                );
            }


            return $this->success(
                $result
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to retrieve search suggestions.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Recent Searches
    |--------------------------------------------------------------------------
    */

    public function recent(
        int $user_id = 0,
        array $request = []
    ): array {

        if ($user_id <= 0) {
            return $this->error(
                'Unauthorized.'
            );
        }


        $service =
            $this->service();


        if (
            $service === null
        ) {
            return $this->error(
                'Search service is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $service,
                    'recent'
                )
            ) {

                $result =
                    $service->recent(
                        $user_id,
                        [
                            'limit' =>
                                min(
                                    50,
                                    max(
                                        1,
                                        (int) (
                                            $request['limit']
                                            ?? 10
                                        )
                                    )
                                )
                        ]
                    );

            } elseif (
                method_exists(
                    $service,
                    'getRecent'
                )
            ) {

                $result =
                    $service->getRecent(
                        $user_id,
                        (int) (
                            $request['limit']
                            ?? 10
                        )
                    );

            } else {

                return $this->error(
                    'Recent search method is unavailable.'
                );
            }


            return $this->success(
                $result
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to retrieve recent searches.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Recent Searches
    |--------------------------------------------------------------------------
    */

    public function clearRecent(
        int $user_id = 0
    ): array {

        if ($user_id <= 0) {
            return $this->error(
                'Unauthorized.'
            );
        }


        $service =
            $this->service();


        if (
            $service === null
        ) {
            return $this->error(
                'Search service is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $service,
                    'clearRecent'
                )
            ) {

                $result =
                    $service->clearRecent(
                        $user_id
                    );

            } elseif (
                method_exists(
                    $service,
                    'clearHistory'
                )
            ) {

                $result =
                    $service->clearHistory(
                        $user_id
                    );

            } else {

                return $this->error(
                    'Clear recent search method is unavailable.'
                );
            }


            return $this->success(
                $result,
                'Recent searches cleared successfully.'
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to clear recent searches.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Search Request
    |--------------------------------------------------------------------------
    */

    protected function validateRequest(
        array $request
    ): bool {

        $query =
            trim(
                (string) (
                    $request['q']
                    ?? $request['query']
                    ?? ''
                )
            );


        return
            $query !== '' &&
            mb_strlen(
                $query
            ) <= 255;
    }


    /*
    |--------------------------------------------------------------------------
    | Success Response
    |--------------------------------------------------------------------------
    */

    protected function success(
        mixed $data = null,
        string $message = 'Success.'
    ): array {

        return [
            'success' =>
                true,

            'message' =>
                $message,

            'data' =>
                $data
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Error Response
    |--------------------------------------------------------------------------
    */

    protected function error(
        string $message
    ): array {

        return [
            'success' =>
                false,

            'message' =>
                $message,

            'data' =>
                null
        ];
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function search_controller_search(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->search(
                $request,
                $user_id
            );
}


function search_controller_users(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->users(
                $request,
                $user_id
            );
}


function search_controller_companies(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->companies(
                $request,
                $user_id
            );
}


function search_controller_trainings(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->trainings(
                $request,
                $user_id
            );
}


function search_controller_students(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->students(
                $request,
                $user_id
            );
}


function search_controller_certificates(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->certificates(
                $request,
                $user_id
            );
}


function search_controller_suggestions(
    array $request = [],
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->suggestions(
                $request,
                $user_id
            );
}


function search_controller_recent(
    int $user_id = 0,
    array $request = []
): array {

    return
        (new SearchController())
            ->recent(
                $user_id,
                $request
            );
}


function search_controller_clear_recent(
    int $user_id = 0
): array {

    return
        (new SearchController())
            ->clearRecent(
                $user_id
            );
}
