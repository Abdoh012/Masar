<?php

/**
 * MASAR - File Controller
 *
 * Handles HTTP requests related to file uploads
 * and file management.
 *
 * Controller
 *     ↓
 * FileUploadService
 *     ↓
 * FileRepository
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

$service_file =
    __DIR__ .
    '/../services/file_upload_service.php';


if (file_exists($service_file)) {
    require_once $service_file;
}


$repository_file =
    __DIR__ .
    '/../repositories/file_repository.php';


if (file_exists($repository_file)) {
    require_once $repository_file;
}


/*
|--------------------------------------------------------------------------
| File Controller
|--------------------------------------------------------------------------
*/

class FileController
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
                'FileUploadService'
            )
        ) {

            return new FileUploadService();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    */

    protected function repository(): mixed
    {
        if (
            class_exists(
                'FileRepository'
            )
        ) {

            return new FileRepository();
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Authenticated User ID
    |--------------------------------------------------------------------------
    */

    protected function getAuthenticatedUserId(): int
    {

        $user = auth_user();

        return max(
            0,
            (int) (
                $user['id'] ?? 0
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        array $request = [],
        array $files = []
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return [
                'success' =>
                    false,

                'message' =>
                    'Unauthorized.'
            ];
        }


        /*
         * Resolve uploaded file.
         */

        $file =
            $this->resolveUploadedFile(
                $files,
                $request
            );


        if (
            $file === null
        ) {

            return [
                'success' =>
                    false,

                'message' =>
                    'No file was provided.'
            ];
        }


        $service =
            $this->service();


        if (
            $service === null
        ) {

            return [
                'success' =>
                    false,

                'message' =>
                    'File upload service is unavailable.'
            ];
        }


        /*
         * Build upload options.
         */

        $options = [

            'user_id' =>
                $user_id,

            'directory' =>
                $request['directory']
                ?? null,

            'folder' =>
                $request['folder']
                ?? null,

            'type' =>
                $request['type']
                ?? null,

            'category' =>
                $request['category']
                ?? null,

            'visibility' =>
                $request['visibility']
                ?? 'private'
        ];


        try {

            if (
                method_exists(
                    $service,
                    'upload'
                )
            ) {

                $result =
                    $service->upload(
                        $file,
                        $options
                    );

            } elseif (
                method_exists(
                    $service,
                    'store'
                )
            ) {

                $result =
                    $service->store(
                        $file,
                        $options
                    );

            } else {

                return [
                    'success' =>
                        false,

                    'message' =>
                        'Upload method is unavailable.'
                ];
            }


            return $this->success(
                $result,
                'File uploaded successfully.'
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to upload file.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Multiple Files
    |--------------------------------------------------------------------------
    */

    public function uploadMultiple(
        array $request = [],
        array $files = []
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return [
                'success' =>
                    false,

                'message' =>
                    'Unauthorized.'
            ];
        }


        $service =
            $this->service();


        if (
            $service === null
        ) {

            return [
                'success' =>
                    false,

                'message' =>
                    'File upload service is unavailable.'
            ];
        }


        /*
         * Collect files.
         */

        $uploaded_files =
            $this->normalizeMultipleFiles(
                $files
            );


        if (
            empty(
                $uploaded_files
            )
        ) {

            return [
                'success' =>
                    false,

                'message' =>
                    'No files were provided.'
            ];
        }


        $results = [];
        $success = true;


        foreach (
            $uploaded_files
            as $file
        ) {

            $options = [

                'user_id' =>
                    $user_id,

                'directory' =>
                    $request['directory']
                    ?? null,

                'folder' =>
                    $request['folder']
                    ?? null,

                'type' =>
                    $request['type']
                    ?? null,

                'category' =>
                    $request['category']
                    ?? null,

                'visibility' =>
                    $request['visibility']
                    ?? 'private'
            ];


            try {

                if (
                    method_exists(
                        $service,
                        'upload'
                    )
                ) {

                    $result =
                        $service->upload(
                            $file,
                            $options
                        );

                } elseif (
                    method_exists(
                        $service,
                        'store'
                    )
                ) {

                    $result =
                        $service->store(
                            $file,
                            $options
                        );

                } else {

                    $result = false;
                }


                $results[] =
                    $result;


                if (
                    $result === false
                ) {

                    $success = false;
                }

            } catch (Throwable $e) {

                $results[] =
                    false;

                $success = false;
            }
        }


        return [
            'success' =>
                $success,

            'message' =>
                $success
                    ? 'Files uploaded successfully.'
                    : 'Some files could not be uploaded.',

            'data' =>
                $results
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get File
    |--------------------------------------------------------------------------
    */

    public function show(
        int $file_id
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return $this->error(
                'Unauthorized.'
            );
        }


        $repository =
            $this->repository();


        if (
            $repository === null
        ) {

            return $this->error(
                'File repository is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $repository,
                    'findForUser'
                )
            ) {

                $file =
                    $repository->findForUser(
                        $file_id,
                        $user_id
                    );

            } elseif (
                method_exists(
                    $repository,
                    'find'
                )
            ) {

                $file =
                    $repository->find(
                        $file_id
                    );


                if (
                    $file &&
                    (int) (
                        $file['user_id']
                        ?? 0
                    ) !== $user_id
                ) {

                    $file = null;
                }

            } else {

                $file = null;
            }


            if (!$file) {

                return $this->error(
                    'File not found.'
                );
            }


            return $this->success(
                $file
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to retrieve file.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | List User Files
    |--------------------------------------------------------------------------
    */

    public function index(
        array $request = []
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return $this->error(
                'Unauthorized.'
            );
        }


        $repository =
            $this->repository();


        if (
            $repository === null
        ) {

            return $this->error(
                'File repository is unavailable.'
            );
        }


        $filters = [

            'page' =>
                max(
                    1,
                    (int) (
                        $request['page']
                        ?? 1
                    )
                ),

            'limit' =>
                max(
                    1,
                    min(
                        100,
                        (int) (
                            $request['limit']
                            ?? 20
                        )
                    )
                ),

            'category' =>
                $request['category']
                ?? null,

            'type' =>
                $request['type']
                ?? null,

            'search' =>
                $request['search']
                ?? null
        ];


        try {

            if (
                method_exists(
                    $repository,
                    'listForUser'
                )
            ) {

                $files =
                    $repository->listForUser(
                        $user_id,
                        $filters
                    );

            } elseif (
                method_exists(
                    $repository,
                    'list'
                )
            ) {

                $files =
                    $repository->list(
                        $user_id,
                        $filters
                    );

            } else {

                $files = [];
            }


            return $this->success(
                $files
            );

        } catch (Throwable $e) {

            return $this->error(
                'Unable to retrieve files.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $file_id
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return $this->error(
                'Unauthorized.'
            );
        }


        $service =
            $this->service();


        $repository =
            $this->repository();


        /*
         * Prefer service-level deletion.
         */

        if (
            $service !== null &&
            method_exists(
                $service,
                'delete'
            )
        ) {

            try {

                $result =
                    $service->delete(
                        $file_id,
                        $user_id
                    );


                return $this->success(
                    $result,
                    'File deleted successfully.'
                );

            } catch (Throwable $e) {

                return $this->error(
                    'Unable to delete file.'
                );
            }
        }


        /*
         * Repository fallback.
         */

        if (
            $repository !== null &&
            method_exists(
                $repository,
                'delete'
            )
        ) {

            try {

                $result =
                    $repository->delete(
                        $file_id,
                        $user_id
                    );


                if (!$result) {

                    return $this->error(
                        'File could not be deleted.'
                    );
                }


                return $this->success(
                    true,
                    'File deleted successfully.'
                );

            } catch (Throwable $e) {

                return $this->error(
                    'Unable to delete file.'
                );
            }
        }

        return $this->error(
            'File deletion is unavailable.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    public function download(
        int $file_id
    ): array {

        /*
         * Get authenticated user ID from authentication context.
         * Never trust client-supplied user_id.
         */
        $user_id = $this->getAuthenticatedUserId();

        if ($user_id <= 0) {

            return $this->error(
                'Unauthorized.'
            );
        }


        $repository =
            $this->repository();


        if (
            $repository === null
        ) {

            return $this->error(
                'File repository is unavailable.'
            );
        }


        try {

            if (
                method_exists(
                    $repository,
                    'findForUser'
                )
            ) {

                $file =
                    $repository->findForUser(
                        $file_id,
                        $user_id
                    );

            } elseif (
                method_exists(
                    $repository,
                    'find'
                )
            ) {

                $file =
                    $repository->find(
                        $file_id
                    );


                if (
                    $file &&
                    (int) (
                        $file['user_id']
                        ?? 0
                    ) !== $user_id
                ) {

                    $file = null;
                }

            } else {

                $file = null;
            }


            if (!$file) {

                return $this->error(
                    'File not found.'
                );
            }


            $path =
                $file['path']
                ?? $file['storage_path']
                ?? $file['file_path']
                ?? null;


            if (
                !$path ||
                !is_file(
                    $path
                )
            ) {

                return $this->error(
                    'Physical file not found.'
                );
            }


            return [
                'success' =>
                    true,

                'download' =>
                    true,

                'path' =>
                    $path,

                'filename' =>
                    $file['original_name']
                    ?? $file['filename']
                    ?? basename($path),

                'mime_type' =>
                    $file['mime_type']
                    ?? 'application/octet-stream',

                'size' =>
                    (int) (
                        $file['size']
                        ?? filesize($path)
                    )
            ];

        } catch (Throwable $e) {

            return $this->error(
                'Unable to download file.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Uploaded File
    |--------------------------------------------------------------------------
    */

    protected function resolveUploadedFile(
        array $files,
        array $request = []
    ): ?array {

        /*
         * Explicit file key.
         */

        if (
            isset(
                $files['file']
            )
        ) {

            $file =
                $files['file'];


            if (
                is_array($file) &&
                isset(
                    $file['tmp_name']
                )
            ) {

                return $file;
            }
        }


        /*
         * Request may already contain a
         * normalized file object/array.
         */

        if (
            isset(
                $request['file']
            ) &&
            is_array(
                $request['file']
            )
        ) {

            if (
                isset(
                    $request['file']['tmp_name']
                )
            ) {

                return
                    $request['file'];
            }
        }


        /*
         * First uploaded file fallback.
         */

        foreach (
            $files
            as $file
        ) {

            if (
                is_array($file) &&
                isset(
                    $file['tmp_name']
                )
            ) {

                return $file;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Multiple Files
    |--------------------------------------------------------------------------
    */

    protected function normalizeMultipleFiles(
        array $files
    ): array {

        $result = [];

        foreach (
            $files
            as $key => $value
        ) {

            /*
             * Standard single upload.
             */

            if (
                is_array($value) &&
                isset(
                    $value['tmp_name']
                )
            ) {

                /*
                 * Multiple upload format.
                 */

                if (
                    is_array(
                        $value['tmp_name']
                    )
                ) {

                    $count =
                        count(
                            $value['tmp_name']
                        );


                    for (
                        $i = 0;
                        $i < $count;
                        $i++
                    ) {

                        $result[] = [

                            'name' =>
                                $value['name'][$i]
                                ?? '',

                            'type' =>
                                $value['type'][$i]
                                ?? '',

                            'tmp_name' =>
                                $value['tmp_name'][$i]
                                ?? '',

                            'error' =>
                                $value['error'][$i]
                                ?? UPLOAD_ERR_NO_FILE,

                            'size' =>
                                $value['size'][$i]
                                ?? 0
                        ];
                    }
                } else {

                    $result[] =
                        $value;
                }
            }
        }

        return $result;
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

function file_controller_upload(
    array $request = [],
    array $files = []
): array {

    return
        (new FileController())
            ->upload(
                $request,
                $files
            );
}


function file_controller_upload_multiple(
    array $request = [],
    array $files = []
): array {

    return
        (new FileController())
            ->uploadMultiple(
                $request,
                $files
            );
}


function file_controller_index(
    array $request = []
): array {

    return
        (new FileController())
            ->index(
                $request
            );
}


function file_controller_show(
    int $file_id
): array {

    return
        (new FileController())
            ->show(
                $file_id
            );
}


function file_controller_delete(
    int $file_id
): array {

    return
        (new FileController())
            ->delete(
                $file_id
            );
}


function file_controller_download(
    int $file_id
): array {

    return
        (new FileController())
            ->download(
                $file_id
            );
}