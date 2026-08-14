<?php

/**
 * MASAR - File Upload Service
 *
 * Handles file validation, storage and persistence.
 *
 * Controller
 *     ↓
 * FileUploadService
 *     ↓
 * FileRepository
 */

$repository_file =
    __DIR__ .
    '/../repositories/file_repository.php';

if (file_exists($repository_file)) {
    require_once $repository_file;
}


class FileUploadService
{
    protected array $config;

    public function __construct(
        array $config = []
    ) {
        $this->config = array_merge(
            [
                'storage_path' =>
                    dirname(
                        __DIR__,
                        3
                    ) . '/storage/uploads',

                'max_size' =>
                    10 * 1024 * 1024,

                'allowed_extensions' => [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'webp',
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'txt',
                    'zip'
                ],

                'allowed_mimes' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                    'application/zip'
                ]
            ],
            $config
        );
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
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        array $file,
        array $options = []
    ): array|false {

        $validation =
            $this->validateFile(
                $file
            );

        if (
            $validation !== true
        ) {
            return false;
        }


        $user_id =
            (int) (
                $options['user_id']
                ?? 0
            );

        if ($user_id <= 0) {
            return false;
        }


        $directory =
            $this->resolveDirectory(
                $options
            );


        if (
            !$this->ensureDirectory(
                $directory
            )
        ) {
            return false;
        }


        $extension =
            strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );


        $stored_name =
            $this->generateStoredName(
                $extension
            );


        $destination =
            rtrim(
                $directory,
                DIRECTORY_SEPARATOR
            ) .
            DIRECTORY_SEPARATOR .
            $stored_name;


        if (
            !$this->moveUploadedFile(
                $file['tmp_name'],
                $destination
            )
        ) {
            return false;
        }


        $relative_path =
            $this->relativePath(
                $destination
            );


        $record = [
            'user_id' =>
                $user_id,

            'original_name' =>
                basename(
                    $file['name']
                ),

            'filename' =>
                $stored_name,

            'path' =>
                $destination,

            'storage_path' =>
                $relative_path,

            'mime_type' =>
                $this->detectMimeType(
                    $destination,
                    $file['type']
                    ?? null
                ),

            'extension' =>
                $extension,

            'size' =>
                (int) (
                    $file['size']
                    ?? filesize(
                        $destination
                    )
                ),

            'category' =>
                $options['category']
                ?? null,

            'type' =>
                $options['type']
                ?? null,

            'visibility' =>
                $options['visibility']
                ?? 'private'
        ];


        $repository =
            $this->repository();


        if (
            $repository !== null
        ) {

            try {

                if (
                    method_exists(
                        $repository,
                        'create'
                    )
                ) {

                    $saved =
                        $repository->create(
                            $record
                        );


                    if (
                        $saved === false
                    ) {

                        @unlink(
                            $destination
                        );

                        return false;
                    }


                    return is_array($saved)
                        ? $saved
                        : array_merge(
                            $record,
                            [
                                'id' =>
                                    $saved
                            ]
                        );
                }

            } catch (Throwable $e) {

                @unlink(
                    $destination
                );

                return false;
            }
        }


        /*
         * Return the stored record even when
         * persistence is handled elsewhere.
         */

        return $record;
    }


    /*
    |--------------------------------------------------------------------------
    | Store Alias
    |--------------------------------------------------------------------------
    */

    public function store(
        array $file,
        array $options = []
    ): array|false {

        return $this->upload(
            $file,
            $options
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate File
    |--------------------------------------------------------------------------
    */

    protected function hasExecutableExtension(string $filename): bool
    {
        $dangerous = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'scr', 'bat', 'js', 'vbs', 'jar'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $dangerous, true);
    }

    protected function validateMagicBytes(string $path, string $mime): bool
    {
        $file = @file_get_contents($path, false, null, 0, 512);
        if ($file === false || $file === '') {
            return true;
        }

        if (str_starts_with($mime, 'image/')) {
            $valid = preg_match('/\A\x89PNG|\A\xFF\xD8\xFF|\A\x47\x49\x46\x38|\ARIFF/', $file) === 1;
            return $valid;
        }

        if ($mime === 'application/pdf') {
            return str_starts_with($file, "%PDF-") || str_starts_with($file, "\x25PDF-");
        }

        return true;
    }

    public function validateFile(
        array $file
    ): bool|string {

        if (
            !isset(
                $file['error']
            )
        ) {
            return 'Invalid upload.';
        }


        if (
            (int) $file['error'] !==
            UPLOAD_ERR_OK
        ) {
            return $this->uploadErrorMessage(
                (int) $file['error']
            );
        }


        $tmp_name =
            $file['tmp_name']
            ?? '';


        if (
            $tmp_name === '' ||
            !is_file(
                $tmp_name
            )
        ) {
            return 'Uploaded file is missing.';
        }


        $size =
            (int) (
                $file['size']
                ?? filesize(
                    $tmp_name
                )
            );


        if (
            $size <= 0
        ) {
            return 'Uploaded file is empty.';
        }


        if (
            $size >
            (int) $this->config['max_size']
        ) {
            return 'File exceeds the maximum allowed size.';
        }


        $original_name = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (
            $extension === ''
        ) {
            return 'File extension is missing.';
        }

        if (str_contains($original_name, '..') || str_contains($original_name, '/')) {
            return 'Invalid file name.';
        }

        if (preg_match('/\.(php|phtml|phar|cgi|exe|scr|bat|js|vbs|jar)(\.|$)/i', $original_name) === 1) {
            return 'Suspicious file extension detected.';
        }

        if ($this->hasExecutableExtension($original_name)) {
            return 'Executable file types are not allowed.';
        }

        if (
            !in_array(
                $extension,
                $this->config[
                    'allowed_extensions'
                ],
                true
            )
        ) {
            return 'File type is not allowed.';
        }


        $mime =
            $this->detectMimeType(
                $tmp_name,
                $file['type']
                ?? null
            );


        if (
            !empty(
                $this->config[
                    'allowed_mimes'
                ]
            ) &&
            !in_array(
                $mime,
                $this->config[
                    'allowed_mimes'
                ],
                true
            )
        ) {
            return 'File MIME type is not allowed.';
        }

        if (!$this->validateMagicBytes($tmp_name, $mime)) {
            return 'File content does not match its declared type.';
        }

        return true;
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


        $repository =
            $this->repository();


        if (
            $repository === null
        ) {
            return false;
        }


        try {

            $file = null;


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
                    $user_id > 0 &&
                    (int) (
                        $file['user_id']
                        ?? 0
                    ) !== $user_id
                ) {

                    return false;
                }
            }


            if (!$file) {
                return false;
            }


            $path =
                $file['path']
                ?? $file['storage_path']
                ?? null;


            if (
                $path &&
                is_file(
                    $path
                )
            ) {
                @unlink(
                    $path
                );
            }


            if (
                method_exists(
                    $repository,
                    'delete'
                )
            ) {

                return (bool)
                    $repository->delete(
                        $file_id,
                        $user_id
                    );
            }


            return true;

        } catch (Throwable $e) {

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Directory
    |--------------------------------------------------------------------------
    */

    protected function resolveDirectory(
        array $options
    ): string {

        $base =
            rtrim(
                $this->config[
                    'storage_path'
                ],
                DIRECTORY_SEPARATOR
            );


        $folder =
            $options['folder']
            ?? $options['directory']
            ?? 'general';


        $folder =
            $this->sanitizePathSegment(
                (string) $folder
            );


        if (
            $folder === ''
        ) {
            $folder = 'general';
        }


        return
            $base .
            DIRECTORY_SEPARATOR .
            $folder;
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Directory
    |--------------------------------------------------------------------------
    */

    protected function ensureDirectory(
        string $directory
    ): bool {

        if (
            is_dir(
                $directory
            )
        ) {
            return true;
        }


        return @mkdir(
            $directory,
            0755,
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Stored Name
    |--------------------------------------------------------------------------
    */

    protected function generateStoredName(
        string $extension
    ): string {

        $prefix =
            date(
                'Ymd'
            );


        $random =
            bin2hex(
                random_bytes(
                    16
                )
            );


        return
            $prefix .
            '_' .
            $random .
            '.' .
            $extension;
    }


    /*
    |--------------------------------------------------------------------------
    | Move Uploaded File
    |--------------------------------------------------------------------------
    */

    protected function moveUploadedFile(
        string $source,
        string $destination
    ): bool {

        /*
         * Normal HTTP upload.
         */

        if (
            function_exists(
                'is_uploaded_file'
            ) &&
            is_uploaded_file(
                $source
            )
        ) {

            return move_uploaded_file(
                $source,
                $destination
            );
        }


        /*
         * Useful for CLI/tests where the file
         * is already present on disk.
         */

        return @rename(
            $source,
            $destination
        ) || @copy(
            $source,
            $destination
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detect MIME Type
    |--------------------------------------------------------------------------
    */

    protected function detectMimeType(
        string $path,
        ?string $fallback = null
    ): string {

        if (
            function_exists(
                'finfo_open'
            )
        ) {

            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );


            if ($finfo) {

                $mime =
                    finfo_file(
                        $finfo,
                        $path
                    );


                finfo_close(
                    $finfo
                );


                if (
                    is_string($mime) &&
                    $mime !== ''
                ) {

                    return $mime;
                }
            }
        }


        if (
            $fallback
        ) {
            return $fallback;
        }


        return
            'application/octet-stream';
    }


    /*
    |--------------------------------------------------------------------------
    | Relative Path
    |--------------------------------------------------------------------------
    */

    protected function relativePath(
        string $path
    ): string {

        $base =
            rtrim(
                $this->config[
                    'storage_path'
                ],
                DIRECTORY_SEPARATOR
            );


        if (
            str_starts_with(
                $path,
                $base
            )
        ) {

            return ltrim(
                substr(
                    $path,
                    strlen($base)
                ),
                DIRECTORY_SEPARATOR
            );
        }


        return $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Sanitize Path Segment
    |--------------------------------------------------------------------------
    */

    protected function sanitizePathSegment(
        string $value
    ): string {

        $value =
            trim(
                $value
            );


        $value =
            preg_replace(
                '/[^a-zA-Z0-9_\-]/',
                '_',
                $value
            );


        return trim(
            $value,
            '._-'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Error Message
    |--------------------------------------------------------------------------
    */

    protected function uploadErrorMessage(
        int $error
    ): string {

        return match ($error) {

            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'File exceeds the allowed upload size.',

            UPLOAD_ERR_PARTIAL =>
                'File upload was incomplete.',

            UPLOAD_ERR_NO_FILE =>
                'No file was uploaded.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'Temporary upload directory is missing.',

            UPLOAD_ERR_CANT_WRITE =>
                'Unable to write uploaded file.',

            UPLOAD_ERR_EXTENSION =>
                'File upload was blocked by a server extension.',

            default =>
                'Unknown file upload error.'
        };
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

function file_upload(
    array $file,
    array $options = []
): array|false {

    return
        (new FileUploadService())
            ->upload(
                $file,
                $options
            );
}


function file_store(
    array $file,
    array $options = []
): array|false {

    return
        (new FileUploadService())
            ->store(
                $file,
                $options
            );
}


function file_delete(
    int $file_id,
    int $user_id = 0
): bool {

    return
        (new FileUploadService())
            ->delete(
                $file_id,
                $user_id
            );
}
