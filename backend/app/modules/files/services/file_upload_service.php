<?php

/**
 * MASAR - File Upload Service
 *
 * Handles file validation, storage and persistence.
 *
 * Controller
 *     ↓
 * file_upload_service_*()
 *     ↓
 * file_repository_*()
 */

require_once __DIR__ . '/../repositories/file_repository.php';


/*
|--------------------------------------------------------------------------
| Default Configuration
|--------------------------------------------------------------------------
*/

function file_upload_service_default_config(): array
{
    return [
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
    ];
}


/*
|--------------------------------------------------------------------------
| Configuration Store
|--------------------------------------------------------------------------
*/

function file_upload_service_config(): array
{
    if (
        !array_key_exists(
            'file_upload_service_config',
            $GLOBALS
        )
    ) {
        $GLOBALS['file_upload_service_config'] =
            file_upload_service_default_config();
    }

    return $GLOBALS['file_upload_service_config'];
}


/*
|--------------------------------------------------------------------------
| Get Configuration
|--------------------------------------------------------------------------
*/

function file_upload_service_get_config(): array
{
    return file_upload_service_config();
}


/*
|--------------------------------------------------------------------------
| Set Configuration
|--------------------------------------------------------------------------
*/

function file_upload_service_set_config(
    array $config
): array {

    $GLOBALS['file_upload_service_config'] =
        array_merge(
            file_upload_service_config(),
            $config
        );

    return file_upload_service_config();
}


/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

function file_upload_service_upload(
    array $file,
    array $options = []
): array|false {

    $validation =
        file_upload_service_validate(
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
        file_upload_service_resolve_directory(
            $options
        );


    if (
        !file_upload_service_ensure_directory(
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
        file_upload_service_generate_stored_name(
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
        !file_upload_service_move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {
        return false;
    }


    $relative_path =
        file_upload_service_relative_path(
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
            file_upload_service_detect_mime_type(
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


    try {

        $saved =
            file_repository_create(
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

    } catch (Throwable $e) {

        @unlink(
            $destination
        );

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Store Alias
|--------------------------------------------------------------------------
*/

function file_upload_service_store(
    array $file,
    array $options = []
): array|false {

    return file_upload_service_upload(
        $file,
        $options
    );
}


/*
|--------------------------------------------------------------------------
| Validate File
|--------------------------------------------------------------------------
*/

function file_upload_service_has_executable_extension(string $filename): bool
{
    $dangerous = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'scr', 'bat', 'js', 'vbs', 'jar'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return in_array($extension, $dangerous, true);
}

function file_upload_service_validate_magic_bytes(string $path, string $mime): bool
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

function file_upload_service_validate(
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
        return file_upload_service_upload_error_message(
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
        (int) file_upload_service_config()['max_size']
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

    if (file_upload_service_has_executable_extension($original_name)) {
        return 'Executable file types are not allowed.';
    }

    if (
        !in_array(
            $extension,
            file_upload_service_config()['allowed_extensions'],
            true
        )
    ) {
        return 'File type is not allowed.';
    }


    $mime =
        file_upload_service_detect_mime_type(
            $tmp_name,
            $file['type']
            ?? null
        );


    if (
        !empty(
            file_upload_service_config()['allowed_mimes']
        ) &&
        !in_array(
            $mime,
            file_upload_service_config()['allowed_mimes'],
            true
        )
    ) {
        return 'File MIME type is not allowed.';
    }

    if (!file_upload_service_validate_magic_bytes($tmp_name, $mime)) {
        return 'File content does not match its declared type.';
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

function file_upload_service_delete(
    int $file_id,
    int $user_id = 0
): bool {

    if (
        $file_id <= 0
    ) {
        return false;
    }


    try {

        $file = null;

        if (
            $user_id > 0
        ) {

            $file =
                file_repository_find_for_user(
                    $file_id,
                    $user_id
                );

        } else {

            $file =
                file_repository_find(
                    $file_id
                );
        }


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


        if (!$file) {
            return false;
        }


        $path =
            $file['path']
            ?? null;


        if (
            $path &&
            file_upload_service_is_safe_storage_path(
                (string) $path
            ) &&
            is_file(
                $path
            )
        ) {
            @unlink(
                $path
            );
        }


        return (bool)
            file_repository_delete(
                $file_id,
                $user_id
            );

    } catch (Throwable $e) {

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Storage Base Path
|--------------------------------------------------------------------------
*/

function file_upload_service_storage_base(): string
{
    $config =
        file_upload_service_config();

    return rtrim(
        (string) (
            $config['storage_path']
            ?? file_upload_service_default_config()['storage_path']
        ),
        DIRECTORY_SEPARATOR
    );
}


/*
|--------------------------------------------------------------------------
| Validate Stored Storage Path
|--------------------------------------------------------------------------
|
| Returns true only when the given path is an absolute path that lives
| inside the application's allowed upload/storage directory. This guards
| every physical deletion against stale, relative or traversal paths that
| could point outside the storage tree.
|
*/

function file_upload_service_is_safe_storage_path(
    string $path
): bool {

    if (
        trim($path) === ''
    ) {
        return false;
    }

    $normalized_path =
        str_replace(
            '\\',
            '/',
            $path
        );

    $normalized_base =
        str_replace(
            '\\',
            '/',
            file_upload_service_storage_base()
        );

    /*
     * Reject path traversal segments (../).
     */

    if (
        preg_match(
            '#(^|/)\.\.(/|$)#',
            $normalized_path
        ) === 1
    ) {
        return false;
    }

    /*
     * The path must be absolute and inside the storage base directory
     * (case-insensitive to be safe on Windows).
     */

    $prefix =
        rtrim(
            $normalized_base,
            '/'
        ) . '/';

    return strncasecmp(
        $normalized_path,
        $prefix,
        strlen($prefix)
    ) === 0;
}


/*
|--------------------------------------------------------------------------
| Resolve Directory
|--------------------------------------------------------------------------
*/

function file_upload_service_resolve_directory(
    array $options
): string {

    $base =
        rtrim(
            file_upload_service_config()['storage_path'],
            DIRECTORY_SEPARATOR
        );


    $folder =
        $options['folder']
        ?? $options['directory']
        ?? 'general';


    $folder =
        file_upload_service_sanitize_path_segment(
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

function file_upload_service_ensure_directory(
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

function file_upload_service_generate_stored_name(
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

function file_upload_service_move_uploaded_file(
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

function file_upload_service_detect_mime_type(
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

function file_upload_service_relative_path(
    string $path
): string {

    $base =
        rtrim(
            file_upload_service_config()['storage_path'],
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

function file_upload_service_sanitize_path_segment(
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

function file_upload_service_upload_error_message(
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
| Backwards-Compatible Function Aliases
|--------------------------------------------------------------------------
*/

function file_upload(
    array $file,
    array $options = []
): array|false {

    return file_upload_service_upload(
        $file,
        $options
    );
}


function file_store(
    array $file,
    array $options = []
): array|false {

    return file_upload_service_store(
        $file,
        $options
    );
}


function file_delete(
    int $file_id,
    int $user_id = 0
): bool {

    return file_upload_service_delete(
        $file_id,
        $user_id
    );
}
