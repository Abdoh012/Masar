<?php

/**
 * MASAR - Search Repository
 *
 * Responsible for database-level search operations.
 *
 * SearchService
 *      ↓
 * SearchRepository
 *      ↓
 * Database
 */


/*
|--------------------------------------------------------------------------
| Database Bootstrap
|--------------------------------------------------------------------------
|
| This repository tries to use the application's existing
| database connection. It does not create a new connection
| when one is already available.
|
*/

class SearchRepository
{
    protected mixed $db = null;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        mixed $db = null
    ) {

        $this->db =
            $db
            ?? $this->resolveDatabase();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Database
    |--------------------------------------------------------------------------
    */

    protected function resolveDatabase(): mixed
    {
        /*
         * Existing global connection.
         */

        if (
            isset($GLOBALS['db'])
        ) {

            return $GLOBALS['db'];
        }


        if (
            isset($GLOBALS['pdo'])
        ) {

            return $GLOBALS['pdo'];
        }


        /*
         * Common application helpers.
         */

        if (
            function_exists(
                'db'
            )
        ) {

            try {

                return db();

            } catch (Throwable $e) {
                // Continue.
            }
        }


        if (
            function_exists(
                'get_db'
            )
        ) {

            try {

                return get_db();

            } catch (Throwable $e) {
                // Continue.
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Main Search
    |--------------------------------------------------------------------------
    */

    public function search(
        string $query,
        array $filters = []
    ): array {

        $type =
            trim(
                (string) (
                    $filters['type']
                    ?? ''
                )
            );


        /*
         * If the application provides dedicated
         * type-specific methods, use them.
         */

        if (
            $type !== ''
        ) {

            return $this->searchByType(
                $type,
                $query,
                $filters
            );
        }


        /*
         * Generic global search.
         */

        return $this->globalSearch(
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Global Search
    |--------------------------------------------------------------------------
    */

    public function globalSearch(
        string $query,
        array $filters = []
    ): array {

        /*
         * The repository supports two database
         * styles:
         *
         * 1. PDO
         * 2. mysqli
         *
         * If no database connection exists, return
         * an empty result instead of breaking the
         * application.
         */

        if (
            $this->db === null
        ) {

            return [];
        }


        /*
         * The actual searchable entities can differ
         * according to the application's database.
         *
         * We therefore prefer an application-provided
         * unified search table/view when available.
         */

        $table =
            $filters['search_table']
            ?? 'search_index';


        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {

            $table =
                'search_index';
        }


        $page =
            max(
                1,
                (int) (
                    $filters['page']
                    ?? 1
                )
            );


        $limit =
            max(
                1,
                min(
                    100,
                    (int) (
                        $filters['limit']
                        ?? 20
                    )
                )
            );


        $offset =
            (
                $page - 1
            ) * $limit;


        $sort =
            $this->normalizeSort(
                $filters['sort']
                ?? 'relevance'
            );


        $order =
            $this->normalizeOrder(
                $filters['order']
                ?? 'DESC'
            );


        /*
         * Search-index convention:
         *
         * id
         * entity_type
         * entity_id
         * title
         * description
         * content
         *
         * The application can override the table
         * through search_table.
         */

        $sql = "
            SELECT
                id,
                entity_type,
                entity_id,
                title,
                description,
                content
            FROM {$table}
            WHERE
                (
                    title LIKE :query
                    OR description LIKE :query
                    OR content LIKE :query
                )
        ";


        $params = [
            ':query' =>
                '%' . $query . '%'
        ];


        if (
            !empty(
                $filters['status']
            )
        ) {

            $sql .= "
                AND status = :status
            ";


            $params[':status'] =
                $filters['status'];
        }


        if (
            !empty(
                $filters['category']
            )
        ) {

            $sql .= "
                AND category = :category
            ";


            $params[':category'] =
                $filters['category'];
        }


        /*
         * Relevance ordering is preferred when
         * supported by the database.
         */

        if (
            $sort === 'relevance'
        ) {

            $sql .= "
                ORDER BY
                    CASE
                        WHEN title LIKE :exact_query
                        THEN 0
                        WHEN title LIKE :start_query
                        THEN 1
                        ELSE 2
                    END,
                    id DESC
            ";


            $params[':exact_query'] =
                $query;


            $params[':start_query'] =
                $query . '%';

        } else {

            $sql .= "
                ORDER BY
                    {$sort}
                    {$order}
            ";
        }


        $sql .= "
            LIMIT :limit
            OFFSET :offset
        ";


        $params[':limit'] =
            $limit;


        $params[':offset'] =
            $offset;


        try {

            $items =
                $this->fetchAll(
                    $sql,
                    $params
                );


            $total =
                $this->countGlobal(
                    $table,
                    $query,
                    $filters
                );


            return [
                'items' =>
                    $items,

                'total' =>
                    $total
            ];

        } catch (Throwable $e) {

            return [];
        }
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

        $type =
            strtolower(
                trim(
                    $type
                )
            );


        switch ($type) {

            case 'users':
                return $this->users(
                    $query,
                    $filters
                );


            case 'students':
                return $this->students(
                    $query,
                    $filters
                );


            case 'companies':
                return $this->companies(
                    $query,
                    $filters
                );


            case 'trainings':
                return $this->trainings(
                    $query,
                    $filters
                );


            case 'certificates':
                return $this->certificates(
                    $query,
                    $filters
                );


            default:

                return [];
        }
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

        return $this->entitySearch(
            'users',
            [
                'id',
                'name',
                'email',
                'username'
            ],
            [
                'name',
                'email',
                'username'
            ],
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

        return $this->entitySearch(
            'students',
            [
                'id',
                'user_id',
                'name',
                'student_code',
                'email'
            ],
            [
                'name',
                'student_code',
                'email'
            ],
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

        return $this->entitySearch(
            'companies',
            [
                'id',
                'name',
                'email',
                'description'
            ],
            [
                'name',
                'email',
                'description'
            ],
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

        return $this->entitySearch(
            'trainings',
            [
                'id',
                'company_id',
                'title',
                'description',
                'location'
            ],
            [
                'title',
                'description',
                'location'
            ],
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

        return $this->entitySearch(
            'certificates',
            [
                'id',
                'student_id',
                'certificate_number',
                'title',
                'description'
            ],
            [
                'certificate_number',
                'title',
                'description'
            ],
            $query,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generic Entity Search
    |--------------------------------------------------------------------------
    */

    protected function entitySearch(
        string $table,
        array $selectColumns,
        array $searchColumns,
        string $query,
        array $filters = []
    ): array {

        if (
            $this->db === null
        ) {

            return [];
        }


        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {

            return [];
        }


        foreach (
            $selectColumns
            as $column
        ) {

            if (
                !$this->isSafeIdentifier(
                    $column
                )
            ) {

                return [];
            }
        }


        foreach (
            $searchColumns
            as $column
        ) {

            if (
                !$this->isSafeIdentifier(
                    $column
                )
            ) {

                return [];
            }
        }


        $page =
            max(
                1,
                (int) (
                    $filters['page']
                    ?? 1
                )
            );


        $limit =
            max(
                1,
                min(
                    100,
                    (int) (
                        $filters['limit']
                        ?? 20
                    )
                )
            );


        $offset =
            (
                $page - 1
            ) * $limit;


        $select =
            implode(
                ', ',
                $selectColumns
            );


        $conditions = [];


        $params = [];


        foreach (
            $searchColumns
            as $index => $column
        ) {

            $parameter =
                ':search_' .
                $index;


            $conditions[] =
                "{$column} LIKE {$parameter}";


            $params[$parameter] =
                '%' .
                $query .
                '%';
        }


        $where =
            implode(
                ' OR ',
                $conditions
            );


        $sql = "
            SELECT
                {$select}
            FROM {$table}
            WHERE
                ({$where})
        ";


        if (
            isset(
                $filters['status']
            ) &&
            $filters['status'] !== ''
        ) {

            $sql .= "
                AND status = :status
            ";


            $params[':status'] =
                $filters['status'];
        }


        $sql .= "
            ORDER BY id DESC
            LIMIT :limit
            OFFSET :offset
        ";


        $params[':limit'] =
            $limit;


        $params[':offset'] =
            $offset;


        try {

            $items =
                $this->fetchAll(
                    $sql,
                    $params
                );


            $countSql = "
                SELECT
                    COUNT(*)
                FROM {$table}
                WHERE
                    ({$where})
            ";


            if (
                isset(
                    $filters['status']
                ) &&
                $filters['status'] !== ''
            ) {

                $countSql .= "
                    AND status = :status
                ";
            }


            $count =
                $this->fetchValue(
                    $countSql,
                    $params
                );


            return [
                'items' =>
                    $items,

                'total' =>
                    (int) $count
            ];

        } catch (Throwable $e) {

            return [];
        }
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

        if (
            $this->db === null
        ) {

            return [];
        }


        $table =
            $options['search_table']
            ?? 'search_index';


        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {

            return [];
        }


        $limit =
            max(
                1,
                min(
                    50,
                    (int) (
                        $options['limit']
                        ?? 10
                    )
                )
            );


        $sql = "
            SELECT
                entity_type,
                entity_id,
                title
            FROM {$table}
            WHERE
                title LIKE :query
            ORDER BY
                CASE
                    WHEN title LIKE :start
                    THEN 0
                    ELSE 1
                END,
                title ASC
            LIMIT :limit
        ";


        $params = [
            ':query' =>
                '%' . $query . '%',

            ':start' =>
                $query . '%',

            ':limit' =>
                $limit
        ];


        try {

            $rows =
                $this->fetchAll(
                    $sql,
                    $params
                );


            $result = [];


            foreach (
                $rows
                as $row
            ) {

                $result[] = [
                    'label' =>
                        $row['title']
                        ?? '',

                    'value' =>
                        $row['entity_id']
                        ?? '',

                    'type' =>
                        $row['entity_type']
                        ?? null
                ];
            }


            return $result;

        } catch (Throwable $e) {

            return [];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Suggest Alias
    |--------------------------------------------------------------------------
    */

    public function suggest(
        string $query,
        int $limit = 10
    ): array {

        return $this->suggestions(
            $query,
            [
                'limit' =>
                    $limit
            ]
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
            $this->db === null ||
            $user_id <= 0
        ) {

            return [];
        }


        $table =
            $options['table']
            ?? 'search_history';


        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {

            return [];
        }


        $limit =
            max(
                1,
                min(
                    50,
                    (int) (
                        $options['limit']
                        ?? 10
                    )
                )
            );


        $sql = "
            SELECT
                id,
                query,
                created_at
            FROM {$table}
            WHERE
                user_id = :user_id
            ORDER BY
                created_at DESC,
                id DESC
            LIMIT :limit
        ";


        try {

            return $this->fetchAll(
                $sql,
                [
                    ':user_id' =>
                        $user_id,

                    ':limit' =>
                        $limit
                ]
            );

        } catch (Throwable $e) {

            return [];
        }
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
            $this->db === null ||
            $user_id <= 0 ||
            trim($query) === ''
        ) {

            return false;
        }


        $table =
            $metadata['table']
            ?? 'search_history';


        if (
            !$this->isSafeIdentifier(
                $table
            )
        ) {

            return false;
        }


        $sql = "
            INSERT INTO {$table}
            (
                user_id,
                query
            )
            VALUES
            (
                :user_id,
                :query
            )
        ";


        try {

            return $this->execute(
                $sql,
                [
                    ':user_id' =>
                        $user_id,

                    ':query' =>
                        trim($query)
                ]
            );

        } catch (Throwable $e) {

            return false;
        }
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
            $this->db === null ||
            $user_id <= 0
        ) {

            return false;
        }


        $sql = "
            DELETE FROM search_history
            WHERE user_id = :user_id
        ";


        try {

            return $this->execute(
                $sql,
                [
                    ':user_id' =>
                        $user_id
                ]
            );

        } catch (Throwable $e) {

            return false;
        }
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
    | Delete Recent Alias
    |--------------------------------------------------------------------------
    */

    public function deleteRecent(
        int $user_id
    ): bool {

        return $this->clearRecent(
            $user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Count Global
    |--------------------------------------------------------------------------
    */

    protected function countGlobal(
        string $table,
        string $query,
        array $filters
    ): int {

        if (
            $this->db === null
        ) {

            return 0;
        }


        $sql = "
            SELECT
                COUNT(*)
            FROM {$table}
            WHERE
                (
                    title LIKE :query
                    OR description LIKE :query
                    OR content LIKE :query
                )
        ";


        $params = [
            ':query' =>
                '%' . $query . '%'
        ];


        if (
            !empty(
                $filters['status']
            )
        ) {

            $sql .= "
                AND status = :status
            ";


            $params[':status'] =
                $filters['status'];
        }


        if (
            !empty(
                $filters['category']
            )
        ) {

            $sql .= "
                AND category = :category
            ";


            $params[':category'] =
                $filters['category'];
        }


        try {

            return (int)
                $this->fetchValue(
                    $sql,
                    $params
                );

        } catch (Throwable $e) {

            return 0;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch All
    |--------------------------------------------------------------------------
    */

    protected function fetchAll(
        string $sql,
        array $params = []
    ): array {

        /*
         * PDO
         */

        if (
            $this->db instanceof PDO
        ) {

            $statement =
                $this->db->prepare(
                    $sql
                );


            $statement->execute(
                $params
            );


            return
                $statement->fetchAll(
                    PDO::FETCH_ASSOC
                );
        }


        /*
         * mysqli
         */

        if (
            $this->isMysqli()
        ) {

            $statement =
                $this->prepareMysqli(
                    $sql,
                    $params
                );


            if (
                $statement === null
            ) {

                return [];
            }


            $statement->execute();


            $result =
                $statement->get_result();


            if (
                $result === false
            ) {

                return [];
            }


            return
                $result->fetch_all(
                    MYSQLI_ASSOC
                );
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Value
    |--------------------------------------------------------------------------
    */

    protected function fetchValue(
        string $sql,
        array $params = []
    ): mixed {

        /*
         * PDO
         */

        if (
            $this->db instanceof PDO
        ) {

            $statement =
                $this->db->prepare(
                    $sql
                );


            $statement->execute(
                $params
            );


            return
                $statement->fetchColumn();
        }


        /*
         * mysqli
         */

        if (
            $this->isMysqli()
        ) {

            $statement =
                $this->prepareMysqli(
                    $sql,
                    $params
                );


            if (
                $statement === null
            ) {

                return null;
            }


            $statement->execute();


            $result =
                $statement->get_result();


            if (
                $result === false
            ) {

                return null;
            }


            $row =
                $result->fetch_row();


            return
                $row[0]
                ?? null;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Execute
    |--------------------------------------------------------------------------
    */

    protected function execute(
        string $sql,
        array $params = []
    ): bool {

        /*
         * PDO
         */

        if (
            $this->db instanceof PDO
        ) {

            $statement =
                $this->db->prepare(
                    $sql
                );


            return
                $statement->execute(
                    $params
                );
        }


        /*
         * mysqli
         */

        if (
            $this->isMysqli()
        ) {

            $statement =
                $this->prepareMysqli(
                    $sql,
                    $params
                );


            if (
                $statement === null
            ) {

                return false;
            }


            return
                $statement->execute();
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare mysqli Statement
    |--------------------------------------------------------------------------
    */

    protected function prepareMysqli(
        string $sql,
        array $params = []
    ): mixed {

        if (
            !$this->isMysqli()
        ) {

            return null;
        }


        $statement =
            $this->db->prepare(
                $sql
            );


        if (
            $statement === false
        ) {

            return null;
        }


        if (
            !empty($params)
        ) {

            $types = '';


            $values = [];


            foreach (
                $params
                as $value
            ) {

                if (
                    is_int($value)
                ) {

                    $types .= 'i';

                } elseif (
                    is_float($value)
                ) {

                    $types .= 'd';

                } else {

                    $types .= 's';
                }


                $values[] =
                    $value;
            }


            $statement->bind_param(
                $types,
                ...$values
            );
        }


        return $statement;
    }


    /*
    |--------------------------------------------------------------------------
    | Is mysqli
    |--------------------------------------------------------------------------
    */

    protected function isMysqli(): bool
    {
        return
            class_exists(
                'mysqli'
            ) &&
            $this->db instanceof mysqli;
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Identifier
    |--------------------------------------------------------------------------
    */

    protected function isSafeIdentifier(
        string $identifier
    ): bool {

        return
            (bool) preg_match(
                '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                $identifier
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Sort
    |--------------------------------------------------------------------------
    */

    protected function normalizeSort(
        string $sort
    ): string {

        $sort =
            strtolower(
                trim(
                    $sort
                )
            );


        $allowed = [
            'relevance',
            'id',
            'name',
            'title',
            'created_at',
            'updated_at'
        ];


        return
            in_array(
                $sort,
                $allowed,
                true
            )
                ? $sort
                : 'relevance';
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Order
    |--------------------------------------------------------------------------
    */

    protected function normalizeOrder(
        string $order
    ): string {

        $order =
            strtoupper(
                trim(
                    $order
                )
            );


        return
            in_array(
                $order,
                [
                    'ASC',
                    'DESC'
                ],
                true
            )
                ? $order
                : 'DESC';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Database
    |--------------------------------------------------------------------------
    */

    public function getDatabase(): mixed
    {
        return $this->db;
    }


    /*
    |--------------------------------------------------------------------------
    | Set Database
    |--------------------------------------------------------------------------
    */

    public function setDatabase(
        mixed $db
    ): self {

        $this->db =
            $db;

        return $this;
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function search_repository_search(
    string $query,
    array $filters = []
): array {

    return
        (new SearchRepository())
            ->search(
                $query,
                $filters
            );
}


function search_repository_suggestions(
    string $query,
    array $options = []
): array {

    return
        (new SearchRepository())
            ->suggestions(
                $query,
                $options
            );
}


function search_repository_recent(
    int $user_id,
    array $options = []
): array {

    return
        (new SearchRepository())
            ->recent(
                $user_id,
                $options
            );
}
