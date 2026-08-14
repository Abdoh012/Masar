<?php

/**
 * MASAR Upload Helper
 *
 * Handles validation and storage of uploaded files.
 *
 * Important:
 * - This helper does NOT handle database records.
 * - It only validates and moves uploaded files.
 * - Database/file references are handled by the feature layer.
 */


/*
|--------------------------------------------------------------------------
| Load Upload Configuration
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../config/upload.php';


/*
|--------------------------------------------------------------------------
| Get Upload Configuration
|--------------------------------------------------------------------------
*/

function upload_config(): array
{
    global $upload_config;

    return $upload_config ?? [];
}


/*
|--------------------------------------------------------------------------
| Get Upload Directory
|--------------------------------------------------------------------------
*/

function upload_directory(
    string $type
): string {

    $config = upload_config();

    $directories =
        $config['directories'] ?? [];

    if (!isset($directories[$type])) {
        throw new InvalidArgumentException(
            'Invalid upload directory type.'
        );
    }

    return rtrim(
        $directories[$type],
        DIRECTORY_SEPARATOR
    );
}


/*
|--------------------------------------------------------------------------
| Get Allowed Extensions
|--------------------------------------------------------------------------
*/

function upload_allowed_extensions(
    string $type
): array {

    $config = upload_config();

    return $config['allowed_extensions'][$type]
        ?? [];
}


/*
|--------------------------------------------------------------------------
| Get Allowed MIME Types
|--------------------------------------------------------------------------
*/

function upload_allowed_mimes(
    string $type
): array {

    $config = upload_config();

    return $config['allowed_mimes'][$type]
        ?? [];
}


/*
|--------------------------------------------------------------------------
| Get Maximum File Size
|--------------------------------------------------------------------------
*/

function upload_max_size(
    string $type
): int {

    $config = upload_config();

    return (int) (
        $config['max_size'][$type]
        ?? 0
    );
}


/*
|--------------------------------------------------------------------------
| Check Upload Error
|--------------------------------------------------------------------------
*/

function upload_has_error(
    array $file
): bool {

    return !isset($file['error'])
        || $file['error'] !== UPLOAD_ERR_OK;
}


/*
|--------------------------------------------------------------------------
| Get Upload Error Message
|--------------------------------------------------------------------------
*/

function upload_error_message(
    int $error
): string {

    return match ($error) {

        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE =>
            'Uploaded file is too large.',

        UPLOAD_ERR_PARTIAL =>
            'File upload was incomplete.',

        UPLOAD_ERR_NO_FILE =>
            'No file was uploaded.',

        UPLOAD_ERR_NO_TMP_DIR =>
            'Temporary upload directory is missing.',

        UPLOAD_ERR_CANT_WRITE =>
            'Failed to write uploaded file.',

        UPLOAD_ERR_EXTENSION =>
            'File upload was blocked by a PHP extension.',

        default =>
            'Unknown file upload error.',
    };
}


/*
|--------------------------------------------------------------------------
| Validate Uploaded File Structure
|--------------------------------------------------------------------------
*/

function upload_validate_structure(
    array $file
): void {

    $required_keys = [
        'name',
        'type',
        'tmp_name',
        'error',
        'size',
    ];

    foreach ($required_keys as $key) {

        if (!array_key_exists($key, $file)) {

            throw new InvalidArgumentException(
                'Invalid uploaded file.'
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| Validate File
|--------------------------------------------------------------------------
*/

function upload_validate(
    array $file,
    string $type
): array {

    upload_validate_structure(
        $file
    );


    /*
    |--------------------------------------------------------------------------
    | Upload Error
    |--------------------------------------------------------------------------
    */

    if (
        $file['error'] !== UPLOAD_ERR_OK
    ) {

        return [
            'file' => [
                upload_error_message(
                    (int) $file['error']
                )
            ]
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | File Size
    |--------------------------------------------------------------------------
    */

    $max_size =
        upload_max_size($type);

    if (
        $max_size > 0 &&
        (int) $file['size'] > $max_size
    ) {

        return [
            'file' => [
                'File size exceeds the allowed limit.'
            ]
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Extension
    |--------------------------------------------------------------------------
    */

    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );


    $allowed_extensions =
        upload_allowed_extensions($type);


    if (
        !empty($allowed_extensions)
        &&
        !in_array(
            $extension,
            $allowed_extensions,
            true
        )
    ) {

        return [
            'file' => [
                'File extension is not allowed.'
            ]
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MIME Type
    |--------------------------------------------------------------------------
    |
    | Never trust $_FILES['type'].
    | Use finfo to detect the real MIME type.
    |
    */

    $finfo = new finfo(
        FILEINFO_MIME_TYPE
    );

    $mime_type = $finfo->file(
        $file['tmp_name']
    );


    $allowed_mimes =
        upload_allowed_mimes($type);


    if (
        !empty($allowed_mimes)
        &&
        !in_array(
            $mime_type,
            $allowed_mimes,
            true
        )
    ) {

        return [
            'file' => [
                'File MIME type is not allowed.'
            ]
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Return File Information
    |--------------------------------------------------------------------------
    */

    return [

        'valid' => true,

        'original_name' =>
            $file['name'],

        'extension' =>
            $extension,

        'mime_type' =>
            $mime_type,

        'size' =>
            (int) $file['size'],

        'tmp_name' =>
            $file['tmp_name'],

    ];
}


/*
|--------------------------------------------------------------------------
| Generate Safe File Name
|--------------------------------------------------------------------------
*/

function upload_generate_filename(
    string $extension
): string {

    return bin2hex(
        random_bytes(16)
    )
    . '.'
    . strtolower(
        $extension
    );
}


/*
|--------------------------------------------------------------------------
| Ensure Upload Directory
|--------------------------------------------------------------------------
*/

function upload_ensure_directory(
    string $directory
): void {

    if (is_dir($directory)) {
        return;
    }


    if (
        !mkdir(
            $directory,
            0755,
            true
        )
    ) {

        throw new RuntimeException(
            'Unable to create upload directory.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Store Uploaded File
|--------------------------------------------------------------------------
*/

function upload_store(
    array $file,
    string $type
): array {

    $validation =
        upload_validate(
            $file,
            $type
        );


    /*
    |--------------------------------------------------------------------------
    | Validation Failed
    |--------------------------------------------------------------------------
    */

    if (
        !isset($validation['valid'])
        ||
        $validation['valid'] !== true
    ) {

        return $validation;
    }


    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    */

    $directory =
        upload_directory($type);


    upload_ensure_directory(
        $directory
    );


    /*
    |--------------------------------------------------------------------------
    | Generate Filename
    |--------------------------------------------------------------------------
    */

    $filename =
        upload_generate_filename(
            $validation['extension']
        );


    $destination =
        $directory
        . DIRECTORY_SEPARATOR
        . $filename;


    /*
    |--------------------------------------------------------------------------
    | Move File
    |--------------------------------------------------------------------------
    */

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        throw new RuntimeException(
            'Failed to store uploaded file.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Return Stored File
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'filename' =>
            $filename,

        'original_name' =>
            $validation['original_name'],

        'extension' =>
            $validation['extension'],

        'mime_type' =>
            $validation['mime_type'],

        'size' =>
            $validation['size'],

        'path' =>
            $destination,

    ];
}


/*
|--------------------------------------------------------------------------
| Delete Uploaded File
|--------------------------------------------------------------------------
*/

function upload_delete(
    string $path
): bool {

    if (
        !is_file($path)
    ) {

        return false;
    }


    return unlink(
        $path
    );
}


/*
|--------------------------------------------------------------------------
| Get Uploaded File
|--------------------------------------------------------------------------
*/

function upload_get(
    string $field
): ?array {

    if (
        !isset($_FILES[$field])
    ) {

        return null;
    }

    return $_FILES[$field];
}


/*
|--------------------------------------------------------------------------
| Check File Exists In Request
|--------------------------------------------------------------------------
*/

function upload_exists(
    string $field
): bool {

    return isset(
        $_FILES[$field]
    )
    &&
    isset(
        $_FILES[$field]['error']
    )
    &&
    $_FILES[$field]['error']
        !== UPLOAD_ERR_NO_FILE;
}
