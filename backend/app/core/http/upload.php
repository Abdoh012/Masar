<?php

/**
 * MASAR HTTP Upload Helper
 *
 * Responsible for handling uploaded files received through
 * HTTP multipart/form-data requests.
 *
 * Validation rules are loaded from:
 * app/config/upload.php
 */


/*
|--------------------------------------------------------------------------
| Load Upload Configuration
|--------------------------------------------------------------------------
*/

$upload_config = require __DIR__ . '/../../config/upload.php';


/*
|--------------------------------------------------------------------------
| Get Upload Error Message
|--------------------------------------------------------------------------
*/

function upload_error_message(int $error_code): string
{
    return match ($error_code) {

        UPLOAD_ERR_INI_SIZE =>
            'The uploaded file exceeds the server upload limit.',

        UPLOAD_ERR_FORM_SIZE =>
            'The uploaded file exceeds the allowed form size.',

        UPLOAD_ERR_PARTIAL =>
            'The uploaded file was only partially uploaded.',

        UPLOAD_ERR_NO_FILE =>
            'No file was uploaded.',

        UPLOAD_ERR_NO_TMP_DIR =>
            'Temporary upload directory is missing.',

        UPLOAD_ERR_CANT_WRITE =>
            'Failed to write the uploaded file.',

        UPLOAD_ERR_EXTENSION =>
            'The uploaded file was blocked by a server extension.',

        default =>
            'Unknown file upload error.',
    };
}


/*
|--------------------------------------------------------------------------
| Check Upload Error
|--------------------------------------------------------------------------
*/

function upload_has_error(array $file): bool
{
    return isset($file['error'])
        && $file['error'] !== UPLOAD_ERR_OK;
}


/*
|--------------------------------------------------------------------------
| Validate Upload Structure
|--------------------------------------------------------------------------
*/

function upload_validate_structure(array $file): bool
{
    return isset(
        $file['name'],
        $file['type'],
        $file['tmp_name'],
        $file['error'],
        $file['size']
    );
}


/*
|--------------------------------------------------------------------------
| Validate Upload Error
|--------------------------------------------------------------------------
*/

function upload_validate_error(array $file): void
{
    if (!upload_validate_structure($file)) {

        throw new InvalidArgumentException(
            'Invalid uploaded file data.'
        );
    }

    if (upload_has_error($file)) {

        throw new RuntimeException(
            upload_error_message(
                (int) $file['error']
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| Validate File Size
|--------------------------------------------------------------------------
*/

function upload_validate_size(
    array $file,
    ?int $max_size = null
): void {

    global $upload_config;

    $maximum = $max_size
        ?? $upload_config['max_size'];

    if ((int) $file['size'] <= 0) {

        throw new InvalidArgumentException(
            'Uploaded file is empty.'
        );
    }

    if ((int) $file['size'] > $maximum) {

        throw new InvalidArgumentException(
            'Uploaded file exceeds the maximum allowed size.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Get File Extension
|--------------------------------------------------------------------------
*/

function upload_extension(array $file): string
{
    return strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );
}


/*
|--------------------------------------------------------------------------
| Validate File Extension
|--------------------------------------------------------------------------
*/

function upload_validate_extension(
    array $file,
    ?array $allowed_extensions = null
): void {

    global $upload_config;

    $extensions = $allowed_extensions
        ?? $upload_config['allowed_extensions'];

    $extension = upload_extension($file);

    if (
        $extension === '' ||
        !in_array(
            $extension,
            $extensions,
            true
        )
    ) {

        throw new InvalidArgumentException(
            'File extension is not allowed.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Detect MIME Type
|--------------------------------------------------------------------------
*/

function upload_detect_mime(array $file): string
{
    if (!is_uploaded_file($file['tmp_name'])) {

        throw new InvalidArgumentException(
            'Invalid uploaded file.'
        );
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file(
        $file['tmp_name']
    );

    if ($mime === false) {

        throw new RuntimeException(
            'Unable to determine file MIME type.'
        );
    }

    return $mime;
}


/*
|--------------------------------------------------------------------------
| Validate MIME Type
|--------------------------------------------------------------------------
*/

function upload_validate_mime(
    array $file,
    ?array $allowed_mimes = null
): void {

    global $upload_config;

    $mimes = $allowed_mimes
        ?? $upload_config['allowed_mimes'];

    $detected_mime = upload_detect_mime($file);

    if (
        !in_array(
            $detected_mime,
            $mimes,
            true
        )
    ) {

        throw new InvalidArgumentException(
            'File MIME type is not allowed.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Validate Blocked Extension
|--------------------------------------------------------------------------
*/

function upload_validate_security(
    array $file
): void {

    global $upload_config;

    $extension = upload_extension($file);

    $blocked_extensions =
        $upload_config['security']['blocked_extensions'];

    if (
        in_array(
            $extension,
            $blocked_extensions,
            true
        )
    ) {

        throw new InvalidArgumentException(
            'This file type is not allowed for security reasons.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Validate Uploaded File
|--------------------------------------------------------------------------
*/

function upload_validate(
    array $file,
    ?array $allowed_extensions = null,
    ?array $allowed_mimes = null,
    ?int $max_size = null
): void {

    upload_validate_error($file);

    upload_validate_size(
        $file,
        $max_size
    );

    upload_validate_security($file);

    upload_validate_extension(
        $file,
        $allowed_extensions
    );

    upload_validate_mime(
        $file,
        $allowed_mimes
    );
}


/*
|--------------------------------------------------------------------------
| Generate Random Filename
|--------------------------------------------------------------------------
*/

function upload_generate_filename(
    array $file
): string {

    $extension = upload_extension($file);

    $filename = bin2hex(
        random_bytes(32)
    );

    return $filename . '.' . $extension;
}


/*
|--------------------------------------------------------------------------
| Create Upload Directory
|--------------------------------------------------------------------------
*/

function upload_create_directory(
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
        && !is_dir($directory)
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
    string $directory,
    ?string $filename = null
): string {

    if (!is_uploaded_file($file['tmp_name'])) {

        throw new InvalidArgumentException(
            'Invalid uploaded file.'
        );
    }

    upload_create_directory(
        $directory
    );

    $stored_filename =
        $filename
        ?? upload_generate_filename($file);

    $destination = rtrim(
        $directory,
        DIRECTORY_SEPARATOR
    )
    . DIRECTORY_SEPARATOR
    . $stored_filename;

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

    return $stored_filename;
}


/*
|--------------------------------------------------------------------------
| Delete Uploaded File
|--------------------------------------------------------------------------
*/

function upload_delete(
    string $file_path
): bool {

    if (!file_exists($file_path)) {
        return false;
    }

    return unlink($file_path);
}


/*
|--------------------------------------------------------------------------
| Get CV Upload Configuration
|--------------------------------------------------------------------------
*/

function upload_cv_config(): array
{
    global $upload_config;

    return $upload_config['cv'];
}