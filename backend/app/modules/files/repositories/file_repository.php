<?php

/**
 * MASAR - File Repository
 *
 * Responsible for database operations related to files.
 *
 * FileUploadService
 *        ↓
 * FileRepository
 *        ↓
 * Database
 */

class FileRepository
{
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    */

    protected function db(): mixed
    {
        /*
         * Support common application database
         * connection patterns.
         */

        if (
            function_exists(
                'get_database_connection'
            )
        ) {

            return get_database_connection();
        }

        if (
            function_exists(
                'db'
            )
        ) {

            return db();
        }


        if (
            function_exists(
                'database'
            )
        ) {

            return database();
        }


        if (
            class_exists(
                'Database'
            )
        ) {

            try {
                return Database::getInstance();
            } catch (Throwable $e) {
                return null;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data
    ): array|int|false {

        $db =
            $this->db();


        if (
            $db === null
        ) {

            return false;
        }


        $data =
            $this->normalizeData(
                $data
            );


        if (
            empty($data)
        ) {

            return false;
        }


        try {

            /*
             * PDO-style connection.
             */

            if (
                $db instanceof PDO
            ) {

                $columns =
                    array_keys(
                        $data
                    );


                $placeholders =
                    array_map(
                        fn ($column) =>
                            ':' . $column,
                        $columns
                    );


                $sql =
                    'INSERT INTO files (' .
                    implode(
                        ', ',
                        $columns
                    ) .
                    ') VALUES (' .
                    implode(
                        ', ',
                        $placeholders
                    ) .
                    ')';


                $statement =
                    $db->prepare(
                        $sql
                    );


                foreach (
                    $data
                    as $column => $value
                ) {

                    $statement->bindValue(
                        ':' . $column,
                        $this->encodeValue(
                            $value
                        )
                    );
                }


                $statement->execute();


                $id =
                    (int)
                    $db->lastInsertId();


                return array_merge(
                    $data,
                    [
                        'id' =>
                            $id
                    ]
                );
            }


            /*
             * Query-builder style connection.
             */

            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $result =
                    $db
                        ->table('files')
                        ->insert(
                            $data
                        );


                if (
                    is_int($result) ||
                    is_numeric($result)
                ) {

                    return array_merge(
                        $data,
                        [
                            'id' =>
                                (int) $result
                        ]
                    );
                }


                return $result
                    ? $data
                    : false;
            }


            /*
             * Generic insert() API.
             */

            if (
                method_exists(
                    $db,
                    'insert'
                )
            ) {

                $result =
                    $db->insert(
                        'files',
                        $data
                    );


                if (
                    is_int($result) ||
                    is_numeric($result)
                ) {

                    return array_merge(
                        $data,
                        [
                            'id' =>
                                (int) $result
                        ]
                    );
                }


                return $result
                    ? $data
                    : false;
            }

        } catch (Throwable $e) {

            return false;
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        int $file_id
    ): array|null {

        if (
            $file_id <= 0
        ) {
            return null;
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return null;
        }


        try {

            if (
                $db instanceof PDO
            ) {

                $statement =
                    $db->prepare(
                        'SELECT * FROM files WHERE id = :id LIMIT 1'
                    );


                $statement->execute([
                    'id' =>
                        $file_id
                ]);


                $result =
                    $statement->fetch(
                        PDO::FETCH_ASSOC
                    );


                return
                    $result ?: null;
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $result =
                    $db
                        ->table('files')
                        ->where(
                            'id',
                            $file_id
                        )
                        ->first();


                return $this->toArray(
                    $result
                );
            }


            if (
                method_exists(
                    $db,
                    'fetchOne'
                )
            ) {

                $result =
                    $db->fetchOne(
                        'SELECT * FROM files WHERE id = ? LIMIT 1',
                        [
                            $file_id
                        ]
                    );


                return $this->toArray(
                    $result
                );
            }

        } catch (Throwable $e) {

            return null;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find For User
    |--------------------------------------------------------------------------
    */

    public function findForUser(
        int $file_id,
        int $user_id
    ): array|null {

        if (
            $file_id <= 0 ||
            $user_id <= 0
        ) {
            return null;
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return null;
        }


        try {

            if (
                $db instanceof PDO
            ) {

                $statement =
                    $db->prepare(
                        'SELECT * FROM files
                         WHERE id = :id
                         AND user_id = :user_id
                         LIMIT 1'
                    );


                $statement->execute([
                    'id' =>
                        $file_id,

                    'user_id' =>
                        $user_id
                ]);


                $result =
                    $statement->fetch(
                        PDO::FETCH_ASSOC
                    );


                return
                    $result ?: null;
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $result =
                    $db
                        ->table('files')
                        ->where(
                            'id',
                            $file_id
                        )
                        ->where(
                            'user_id',
                            $user_id
                        )
                        ->first();


                return $this->toArray(
                    $result
                );
            }


            if (
                method_exists(
                    $db,
                    'fetchOne'
                )
            ) {

                $result =
                    $db->fetchOne(
                        'SELECT * FROM files
                         WHERE id = ?
                         AND user_id = ?
                         LIMIT 1',
                        [
                            $file_id,
                            $user_id
                        ]
                    );


                return $this->toArray(
                    $result
                );
            }

        } catch (Throwable $e) {

            return null;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | List For User
    |--------------------------------------------------------------------------
    */

    public function listForUser(
        int $user_id,
        array $filters = []
    ): array {

        if (
            $user_id <= 0
        ) {
            return [];
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return [];
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


        $category =
            $filters['category']
            ?? null;


        $type =
            $filters['type']
            ?? null;


        $search =
            trim(
                (string) (
                    $filters['search']
                    ?? ''
                )
            );


        try {

            if (
                $db instanceof PDO
            ) {

                $conditions = [
                    'user_id = :user_id'
                ];


                $parameters = [
                    'user_id' =>
                        $user_id
                ];


                if (
                    $category !== null &&
                    $category !== ''
                ) {

                    $conditions[] =
                        'category = :category';

                    $parameters['category'] =
                        $category;
                }


                if (
                    $type !== null &&
                    $type !== ''
                ) {

                    $conditions[] =
                        'type = :type';

                    $parameters['type'] =
                        $type;
                }


                if (
                    $search !== ''
                ) {

                    $conditions[] =
                        'original_name LIKE :search';

                    $parameters['search'] =
                        '%' .
                        $search .
                        '%';
                }


                $sql =
                    'SELECT * FROM files
                     WHERE ' .
                    implode(
                        ' AND ',
                        $conditions
                    ) .
                    ' ORDER BY id DESC
                     LIMIT :limit
                     OFFSET :offset';


                $statement =
                    $db->prepare(
                        $sql
                    );


                foreach (
                    $parameters
                    as $key => $value
                ) {

                    $statement->bindValue(
                        ':' . $key,
                        $value
                    );
                }


                $statement->bindValue(
                    ':limit',
                    $limit,
                    PDO::PARAM_INT
                );


                $statement->bindValue(
                    ':offset',
                    $offset,
                    PDO::PARAM_INT
                );


                $statement->execute();


                return
                    $statement->fetchAll(
                        PDO::FETCH_ASSOC
                    );
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $query =
                    $db
                        ->table('files')
                        ->where(
                            'user_id',
                            $user_id
                        );


                if (
                    $category !== null &&
                    $category !== ''
                ) {

                    $query =
                        $query->where(
                            'category',
                            $category
                        );
                }


                if (
                    $type !== null &&
                    $type !== ''
                ) {

                    $query =
                        $query->where(
                            'type',
                            $type
                        );
                }


                if (
                    $search !== ''
                ) {

                    if (
                        method_exists(
                            $query,
                            'whereLike'
                        )
                    ) {

                        $query =
                            $query->whereLike(
                                'original_name',
                                '%' .
                                $search .
                                '%'
                            );
                    }
                }


                if (
                    method_exists(
                        $query,
                        'orderBy'
                    )
                ) {

                    $query =
                        $query->orderBy(
                            'id',
                            'DESC'
                        );
                }


                if (
                    method_exists(
                        $query,
                        'limit'
                    )
                ) {

                    $query =
                        $query->limit(
                            $limit
                        );
                }


                if (
                    method_exists(
                        $query,
                        'offset'
                    )
                ) {

                    $query =
                        $query->offset(
                            $offset
                        );
                }


                if (
                    method_exists(
                        $query,
                        'get'
                    )
                ) {

                    return $this->toArrayList(
                        $query->get()
                    );
                }
            }

        } catch (Throwable $e) {

            return [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | List Alias
    |--------------------------------------------------------------------------
    */

    public function list(
        int $user_id,
        array $filters = []
    ): array {

        return $this->listForUser(
            $user_id,
            $filters
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $file_id,
        int $user_id = 0
    ): bool {

        if (
            $file_id <= 0
        ) {
            return false;
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return false;
        }


        try {

            if (
                $db instanceof PDO
            ) {

                if ($user_id > 0) {

                    $statement =
                        $db->prepare(
                            'DELETE FROM files
                             WHERE id = :id
                             AND user_id = :user_id'
                        );


                    $statement->execute([
                        'id' =>
                            $file_id,

                        'user_id' =>
                            $user_id
                    ]);

                } else {

                    $statement =
                        $db->prepare(
                            'DELETE FROM files
                             WHERE id = :id'
                        );


                    $statement->execute([
                        'id' =>
                            $file_id
                    ]);
                }


                return
                    $statement->rowCount() > 0;
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $query =
                    $db
                        ->table('files')
                        ->where(
                            'id',
                            $file_id
                        );


                if (
                    $user_id > 0
                ) {

                    $query =
                        $query->where(
                            'user_id',
                            $user_id
                        );
                }


                return (bool)
                    $query->delete();
            }


            if (
                method_exists(
                    $db,
                    'delete'
                )
            ) {

                $conditions = [
                    'id' =>
                        $file_id
                ];


                if (
                    $user_id > 0
                ) {

                    $conditions['user_id'] =
                        $user_id;
                }


                return (bool)
                    $db->delete(
                        'files',
                        $conditions
                    );
            }

        } catch (Throwable $e) {

            return false;
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        int $file_id,
        array $data,
        int $user_id = 0
    ): array|false {

        if (
            $file_id <= 0 ||
            empty($data)
        ) {
            return false;
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return false;
        }


        $data =
            $this->normalizeData(
                $data
            );


        unset(
            $data['id'],
            $data['created_at']
        );


        if (
            empty($data)
        ) {
            return false;
        }


        try {

            if (
                $db instanceof PDO
            ) {

                $sets = [];


                foreach (
                    $data
                    as $column => $value
                ) {

                    $sets[] =
                        $column .
                        ' = :' .
                        $column;
                }


                $where =
                    'id = :where_id';


                if (
                    $user_id > 0
                ) {

                    $where .=
                        ' AND user_id = :where_user_id';
                }


                $sql =
                    'UPDATE files
                     SET ' .
                    implode(
                        ', ',
                        $sets
                    ) .
                    ' WHERE ' .
                    $where;


                $statement =
                    $db->prepare(
                        $sql
                    );


                foreach (
                    $data
                    as $column => $value
                ) {

                    $statement->bindValue(
                        ':' . $column,
                        $this->encodeValue(
                            $value
                        )
                    );
                }


                $statement->bindValue(
                    ':where_id',
                    $file_id,
                    PDO::PARAM_INT
                );


                if (
                    $user_id > 0
                ) {

                    $statement->bindValue(
                        ':where_user_id',
                        $user_id,
                        PDO::PARAM_INT
                    );
                }


                $statement->execute();


                if (
                    $statement->rowCount() <= 0
                ) {

                    return false;
                }


                return $this->find(
                    $file_id
                );
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $query =
                    $db
                        ->table('files')
                        ->where(
                            'id',
                            $file_id
                        );


                if (
                    $user_id > 0
                ) {

                    $query =
                        $query->where(
                            'user_id',
                            $user_id
                        );
                }


                $result =
                    $query->update(
                        $data
                    );


                if (!$result) {
                    return false;
                }


                return $this->find(
                    $file_id
                );
            }

        } catch (Throwable $e) {

            return false;
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

    public function countForUser(
        int $user_id,
        array $filters = []
    ): int {

        if (
            $user_id <= 0
        ) {
            return 0;
        }


        $db =
            $this->db();


        if (
            $db === null
        ) {
            return 0;
        }


        try {

            if (
                $db instanceof PDO
            ) {

                $conditions = [
                    'user_id = :user_id'
                ];


                $parameters = [
                    'user_id' =>
                        $user_id
                ];


                if (
                    !empty(
                        $filters['category']
                    )
                ) {

                    $conditions[] =
                        'category = :category';

                    $parameters['category'] =
                        $filters['category'];
                }


                if (
                    !empty(
                        $filters['type']
                    )
                ) {

                    $conditions[] =
                        'type = :type';

                    $parameters['type'] =
                        $filters['type'];
                }


                $sql =
                    'SELECT COUNT(*)
                     FROM files
                     WHERE ' .
                    implode(
                        ' AND ',
                        $conditions
                    );


                $statement =
                    $db->prepare(
                        $sql
                    );


                $statement->execute(
                    $parameters
                );


                return (int)
                    $statement->fetchColumn();
            }


            if (
                method_exists(
                    $db,
                    'table'
                )
            ) {

                $query =
                    $db
                        ->table('files')
                        ->where(
                            'user_id',
                            $user_id
                        );


                if (
                    !empty(
                        $filters['category']
                    )
                ) {

                    $query =
                        $query->where(
                            'category',
                            $filters['category']
                        );
                }


                if (
                    !empty(
                        $filters['type']
                    )
                ) {

                    $query =
                        $query->where(
                            'type',
                            $filters['type']
                        );
                }


                if (
                    method_exists(
                        $query,
                        'count'
                    )
                ) {

                    return (int)
                        $query->count();
                }
            }

        } catch (Throwable $e) {

            return 0;
        }


        return 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Data
    |--------------------------------------------------------------------------
    */

    protected function normalizeData(
        array $data
    ): array {

        /*
         * Map service-level fields to the actual
         * files table columns.
         */

        $column_map = [
            'user_id' => 'user_id',
            'original_name' => 'original_name',
            'filename' => 'stored_name',
            'path' => 'path',
            'mime_type' => 'mime_type',
            'size' => 'size_bytes',
            'type' => 'type'
        ];

        $result = [];


        foreach (
            $column_map
            as $source => $column
        ) {

            if (
                array_key_exists(
                    $source,
                    $data
                )
            ) {

                $result[$column] =
                    $data[$source];
            }
        }


        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Encode Value
    |--------------------------------------------------------------------------
    */

    protected function encodeValue(
        mixed $value
    ): mixed {

        if (
            is_array($value) ||
            is_object($value)
        ) {

            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );
        }


        return $value;
    }


    /*
    |--------------------------------------------------------------------------
    | To Array
    |--------------------------------------------------------------------------
    */

    protected function toArray(
        mixed $value
    ): ?array {

        if (
            $value === null
        ) {
            return null;
        }


        if (
            is_array($value)
        ) {
            return $value;
        }


        if (
            is_object($value)
        ) {

            return
                get_object_vars(
                    $value
                );
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | To Array List
    |--------------------------------------------------------------------------
    */

    protected function toArrayList(
        mixed $value
    ): array {

        if (
            $value === null
        ) {
            return [];
        }


        if (
            is_array($value)
        ) {

            return array_map(
                fn ($item) =>
                    $this->toArray($item)
                    ?? [],
                $value
            );
        }


        if (
            $value instanceof Traversable
        ) {

            $result = [];


            foreach (
                $value
                as $item
            ) {

                $result[] =
                    $this->toArray(
                        $item
                    )
                    ?? [];
            }


            return $result;
        }


        return [];
    }
}


/*
|--------------------------------------------------------------------------
| Function-Based Compatibility API
|--------------------------------------------------------------------------
*/

function file_repository_find(
    int $file_id
): ?array {

    return
        (new FileRepository())
            ->find(
                $file_id
            );
}


function file_repository_find_for_user(
    int $file_id,
    int $user_id
): ?array {

    return
        (new FileRepository())
            ->findForUser(
                $file_id,
                $user_id
            );
}


function file_repository_list(
    int $user_id,
    array $filters = []
): array {

    return
        (new FileRepository())
            ->listForUser(
                $user_id,
                $filters
            );
}


function file_repository_delete(
    int $file_id,
    int $user_id = 0
): bool {

    return
        (new FileRepository())
            ->delete(
                $file_id,
                $user_id
            );
}
