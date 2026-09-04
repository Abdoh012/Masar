<?php

/**
 * MASAR - File Repository
 *
 * Responsible for database operations related to files.
 *
 * file_upload_service_*()
 *        ↓
 * file_repository_*()
 *        ↓
 * Database
 */

require_once __DIR__ . '/../../../core/database/query.php';

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

function file_repository_db(): PDO
{
    return get_database_connection();
}


/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

function file_repository_create( array $data ): array|int|false {

    $db = file_repository_db();

    $data =
        file_repository_normalize_data(
            $data
        );


    if (
        empty($data)
    ) {

        return false;
    }


    try {

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
                file_repository_encode_value(
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

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Find
|--------------------------------------------------------------------------
*/

function file_repository_find(
    int $file_id
): array|null {

    if (
        $file_id <= 0
    ) {
        return null;
    }


    try {

        $statement =
            file_repository_db()
                ->prepare(
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

    } catch (Throwable $e) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| Find For User
|--------------------------------------------------------------------------
*/

function file_repository_find_for_user(
    int $file_id,
    int $user_id
): array|null {

    if (
        $file_id <= 0 ||
        $user_id <= 0
    ) {
        return null;
    }


    try {

        $statement =
            file_repository_db()
                ->prepare(
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

    } catch (Throwable $e) {

        return null;
    }
}


/*
|--------------------------------------------------------------------------
| List For User
|--------------------------------------------------------------------------
*/

function file_repository_list_for_user(
    int $user_id,
    array $filters = []
): array {

    if (
        $user_id <= 0
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
            file_repository_db()
                ->prepare(
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

    } catch (Throwable $e) {

        return [];
    }
}


/*
|--------------------------------------------------------------------------
| List Alias
|--------------------------------------------------------------------------
*/

function file_repository_list(
    int $user_id,
    array $filters = []
): array {

    return file_repository_list_for_user(
        $user_id,
        $filters
    );
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

function file_repository_delete(
    int $file_id,
    int $user_id = 0
): bool {

    if (
        $file_id <= 0
    ) {
        return false;
    }


    try {

        if ($user_id > 0) {

            $statement =
                file_repository_db()
                    ->prepare(
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
                file_repository_db()
                    ->prepare(
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

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

function file_repository_update(
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


    $data =
        file_repository_normalize_data(
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


        $db =
            file_repository_db();


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
                file_repository_encode_value(
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


        return file_repository_find(
            $file_id
        );

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Count
|--------------------------------------------------------------------------
*/

function file_repository_count_for_user(
    int $user_id,
    array $filters = []
): int {

    if (
        $user_id <= 0
    ) {
        return 0;
    }


    try {

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
            file_repository_db()
                ->prepare(
                    $sql
                );


        $statement->execute(
            $parameters
        );


        return (int)
            $statement->fetchColumn();

    } catch (Throwable $e) {

        return 0;
    }
}


/*
|--------------------------------------------------------------------------
| Normalize Data
|--------------------------------------------------------------------------
|
| Maps service-level fields to the actual files table columns.
|
*/

function file_repository_normalize_data(
    array $data
): array {

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

function file_repository_encode_value(
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
