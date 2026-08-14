<?php

/**
 * MASAR - Search Service
 *
 * Business logic for application-wide search.
 *
 * Controller
 *     ↓
 * SearchService
 *     ↓
 * SearchRepository
 */

$repository_file =
    __DIR__ .
    '/../repositories/search_repository.php';

if (file_exists($repository_file)) {
    require_once $repository_file;
}


class SearchService
{
    protected mixed $repository = null;

    protected array $config;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        array $config = []
    ) {

        $this->config =
            array_merge(
                [
                    'default_limit' =>
                        20,

                    'max_limit' =>
                        100,

                    'min_query_length' =>
                        2,

                    'max_query_length' =>
                        255
                ],
                $config
            );


        $this->repository =
            $this->resolveRepository();
    }


    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    */

    protected function resolveRepository(): mixed
    {
        if (
            class_exists(
                'SearchRepository'
            )
        ) {

            return new SearchRepository();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function search(
        string $query,
        array $filters = []
    ): array {

        $query =
            $this->normalizeQuery(
                $query
            );


        if (
            !$this->isValidQuery(
                $query
            )
        ) {

            return [
                'items' =>
                    [],

                'total' =>
                    0,

                'page' =>
                    1,

                'limit' =>
                    $this->config[
                        'default_limit'
                    ],

                'query' =>
                    $query
            ];
        }


        $filters =
            $this->normalizeFilters(
                $filters
            );


        $filters['query'] =
            $query;


        $result =
            $this->executeSearch(
                $query,
                $filters
            );


        return $this->normalizeResult(
            $result,
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Execute Search
    |--------------------------------------------------------------------------
    */

    protected function executeSearch(
        string $query,
        array $filters
    ): mixed {

        if (
            $this->repository === null
        ) {

            return [];
        }


        try {

            if (
                method_exists(
                    $this->repository,
                    'search'
                )
            ) {

                return
                    $this->repository->search(
                        $query,
                        $filters
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'globalSearch'
                )
            ) {

                return
                    $this->repository->globalSearch(
                        $query,
                        $filters
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'find'
                )
            ) {

                return
                    $this->repository->find(
                        $query,
                        $filters
                    );
            }

        } catch (Throwable $e) {

            return [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Search By Type
    |--------------------------------------------------------------------------
    */

    public function searchByType(
        string $type,
        string $query,
        array $filters = []
    ): array {

        $filters['type'] =
            $type;


        return $this->search(
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function users(
        string $query,
        array $filters = []
    ): array {

        return $this->searchByType(
            'users',
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    public function companies(
        string $query,
        array $filters = []
    ): array {

        return $this->searchByType(
            'companies',
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */

    public function students(
        string $query,
        array $filters = []
    ): array {

        return $this->searchByType(
            'students',
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Trainings
    |--------------------------------------------------------------------------
    */

    public function trainings(
        string $query,
        array $filters = []
    ): array {

        return $this->searchByType(
            'trainings',
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Certificates
    |--------------------------------------------------------------------------
    */

    public function certificates(
        string $query,
        array $filters = []
    ): array {

        return $this->searchByType(
            'certificates',
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Suggestions
    |--------------------------------------------------------------------------
    */

    public function suggestions(
        string $query,
        array $options = []
    ): array {

        $query =
            $this->normalizeQuery(
                $query
            );


        if (
            !$this->isValidQuery(
                $query
            )
        ) {

            return [];
        }


        if (
            $this->repository === null
        ) {

            return [];
        }


        $limit =
            $this->normalizeLimit(
                $options['limit']
                ?? 10
            );


        try {

            if (
                method_exists(
                    $this->repository,
                    'suggestions'
                )
            ) {

                $result =
                    $this->repository->suggestions(
                        $query,
                        [
                            'limit' =>
                                $limit,

                            'user_id' =>
                                (int) (
                                    $options['user_id']
                                    ?? 0
                                )
                        ]
                    );

                return
                    $this->normalizeSuggestions(
                        $result
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'suggest'
                )
            ) {

                $result =
                    $this->repository->suggest(
                        $query,
                        $limit
                    );

                return
                    $this->normalizeSuggestions(
                        $result
                    );
            }

        } catch (Throwable $e) {

            return [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Suggest Alias
    |--------------------------------------------------------------------------
    */

    public function suggest(
        string $query,
        array $options = []
    ): array {

        return $this->suggestions(
            $query,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Recent Searches
    |--------------------------------------------------------------------------
    */

    public function recent(
        int $user_id,
        array $options = []
    ): array {

        if (
            $user_id <= 0
        ) {

            return [];
        }


        if (
            $this->repository === null
        ) {

            return [];
        }


        $limit =
            $this->normalizeLimit(
                $options['limit']
                ?? 10
            );


        try {

            if (
                method_exists(
                    $this->repository,
                    'recent'
                )
            ) {

                return
                    $this->normalizeList(
                        $this->repository->recent(
                            $user_id,
                            [
                                'limit' =>
                                    $limit
                            ]
                        )
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'getRecent'
                )
            ) {

                return
                    $this->normalizeList(
                        $this->repository->getRecent(
                            $user_id,
                            $limit
                        )
                    );
            }

        } catch (Throwable $e) {

            return [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Recent Alias
    |--------------------------------------------------------------------------
    */

    public function getRecent(
        int $user_id,
        int $limit = 10
    ): array {

        return $this->recent(
            $user_id,
            [
                'limit' =>
                    $limit
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save Search
    |--------------------------------------------------------------------------
    */

    public function saveSearch(
        int $user_id,
        string $query,
        array $metadata = []
    ): bool {

        if (
            $user_id <= 0
        ) {

            return false;
        }


        $query =
            $this->normalizeQuery(
                $query
            );


        if (
            !$this->isValidQuery(
                $query
            )
        ) {

            return false;
        }


        if (
            $this->repository === null
        ) {

            return false;
        }


        try {

            if (
                method_exists(
                    $this->repository,
                    'saveSearch'
                )
            ) {

                return (bool)
                    $this->repository->saveSearch(
                        $user_id,
                        $query,
                        $metadata
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'save'
                )
            ) {

                return (bool)
                    $this->repository->save(
                        [
                            'user_id' =>
                                $user_id,

                            'query' =>
                                $query,

                            'metadata' =>
                                $metadata
                        ]
                    );
            }

        } catch (Throwable $e) {

            return false;
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Recent
    |--------------------------------------------------------------------------
    */

    public function clearRecent(
        int $user_id
    ): bool {

        if (
            $user_id <= 0
        ) {

            return false;
        }


        if (
            $this->repository === null
        ) {

            return false;
        }


        try {

            if (
                method_exists(
                    $this->repository,
                    'clearRecent'
                )
            ) {

                return (bool)
                    $this->repository->clearRecent(
                        $user_id
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'clearHistory'
                )
            ) {

                return (bool)
                    $this->repository->clearHistory(
                        $user_id
                    );
            }


            if (
                method_exists(
                    $this->repository,
                    'deleteRecent'
                )
            ) {

                return (bool)
                    $this->repository->deleteRecent(
                        $user_id
                    );
            }

        } catch (Throwable $e) {

            return false;
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Clear History Alias
    |--------------------------------------------------------------------------
    */

    public function clearHistory(
        int $user_id
    ): bool {

        return $this->clearRecent(
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Query
    |--------------------------------------------------------------------------
    */

    protected function normalizeQuery(
        string $query
    ): string {

        $query =
            trim(
                $query
            );


        /*
         * Normalize multiple whitespace
         * characters into a single space.
         */

        $query =
            preg_replace(
                '/\s+/u',
                ' ',
                $query
            );


        return trim(
            $query
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Query
    |--------------------------------------------------------------------------
    */

    protected function isValidQuery(
        string $query
    ): bool {

        if (
            $query === ''
        ) {

            return false;
        }


        $length =
            mb_strlen(
                $query
            );


        if (
            $length <
            $this->config[
                'min_query_length'
            ]
        ) {

            return false;
        }


        if (
            $length >
            $this->config[
                'max_query_length'
            ]
        ) {

            return false;
        }


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Filters
    |--------------------------------------------------------------------------
    */

    protected function normalizeFilters(
        array $filters
    ): array {

        $page =
            max(
                1,
                (int) (
                    $filters['page']
                    ?? 1
                )
            );


        $limit =
            $this->normalizeLimit(
                $filters['limit']
                ?? $this->config[
                    'default_limit'
                ]
            );


        $sort =
            strtolower(
                trim(
                    (string) (
                        $filters['sort']
                        ?? 'relevance'
                    )
                )
            );


        $allowedSorts = [
            'relevance',
            'date',
            'created_at',
            'updated_at',
            'name',
            'title'
        ];


        if (
            !in_array(
                $sort,
                $allowedSorts,
                true
            )
        ) {

            $sort =
                'relevance';
        }


        $order =
            strtoupper(
                trim(
                    (string) (
                        $filters['order']
                        ?? 'DESC'
                    )
                )
            );


        if (
            !in_array(
                $order,
                [
                    'ASC',
                    'DESC'
                ],
                true
            )
        ) {

            $order =
                'DESC';
        }


        return array_merge(
            $filters,
            [
                'page' =>
                    $page,

                'limit' =>
                    $limit,

                'sort' =>
                    $sort,

                'order' =>
                    $order,

                'user_id' =>
                    (int) (
                        $filters['user_id']
                        ?? 0
                    )
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Limit
    |--------------------------------------------------------------------------
    */

    protected function normalizeLimit(
        mixed $limit
    ): int {

        $limit =
            (int) $limit;


        if (
            $limit <= 0
        ) {

            $limit =
                $this->config[
                    'default_limit'
                ];
        }


        return min(
            $limit,
            $this->config[
                'max_limit'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Result
    |--------------------------------------------------------------------------
    */

    protected function normalizeResult(
        mixed $result,
        string $query,
        array $filters
    ): array {

        /*
         * Repository may return a complete
         * pagination object.
         */

        if (
            is_array($result) &&
            (
                array_key_exists(
                    'items',
                    $result
                ) ||
                array_key_exists(
                    'results',
                    $result
                )
            )
        ) {

            $items =
                $result['items']
                ?? $result['results']
                ?? [];


            $total =
                (int) (
                    $result['total']
                    ?? count(
                        $items
                    )
                );


            return [
                'items' =>
                    $this->normalizeList(
                        $items
                    ),

                'total' =>
                    $total,

                'page' =>
                    $filters['page'],

                'limit' =>
                    $filters['limit'],

                'query' =>
                    $query
            ];
        }


        $items =
            $this->normalizeList(
                $result
            );


        return [
            'items' =>
                $items,

            'total' =>
                count(
                    $items
                ),

            'page' =>
                $filters['page'],

            'limit' =>
                $filters['limit'],

            'query' =>
                $query
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize List
    |--------------------------------------------------------------------------
    */

    protected function normalizeList(
        mixed $items
    ): array {

        if (
            $items === null
        ) {

            return [];
        }


        if (
            is_array($items)
        ) {

            return array_values(
                $items
            );
        }


        if (
            $items instanceof Traversable
        ) {

            return array_values(
                iterator_to_array(
                    $items
                )
            );
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Suggestions
    |--------------------------------------------------------------------------
    */

    protected function normalizeSuggestions(
        mixed $items
    ): array {

        $items =
            $this->normalizeList(
                $items
            );


        $result = [];


        foreach (
            $items
            as $item
        ) {

            if (
                is_string($item)
            ) {

                $result[] = [
                    'label' =>
                        $item,

                    'value' =>
                        $item
                ];

                continue;
            }


            if (
                is_array($item)
            ) {

                $result[] = [
                    'label' =>
                        $item['label']
                        ?? $item['name']
                        ?? $item['title']
                        ?? $item['value']
                        ?? '',

                    'value' =>
                        $item['value']
                        ?? $item['id']
                        ?? $item['name']
                        ?? $item['title']
                        ?? ''
                ];

                continue;
            }


            if (
                is_object($item)
            ) {

                $data =
                    get_object_vars(
                        $item
                    );


                $result[] = [
                    'label' =>
                        $data['label']
                        ?? $data['name']
                        ?? $data['title']
                        ?? $data['value']
                        ?? '',

                    'value' =>
                        $data['value']
                        ?? $data['id']
                        ?? $data['name']
                        ?? $data['title']
                        ?? ''
                ];
            }
        }


        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Repository Access
    |--------------------------------------------------------------------------
    */

    public function getRepository(): mixed
    {
        return $this->repository;
    }


    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    public function getConfig(): array
    {
        return $this->config;
    }


    public function setConfig(
        array $config
    ): self {

        $this->config =
            array_merge(
                $this->config,
                $config
            );

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function search_service_search(
    string $query,
    array $filters = []
): array {

    return
        (new SearchService())
            ->search(
                $query,
                $filters
            );
}


function search_service_suggestions(
    string $query,
    array $options = []
): array {

    return
        (new SearchService())
            ->suggestions(
                $query,
                $options
            );
}


function search_service_recent(
    int $user_id,
    array $options = []
): array {

    return
        (new SearchService())
            ->recent(
                $user_id,
                $options
            );
}


function search_service_clear_recent(
    int $user_id
): bool {

    return
        (new SearchService())
            ->clearRecent(
                $user_id
            );
}
