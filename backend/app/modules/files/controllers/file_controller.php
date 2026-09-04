<?php

/**
 * MASAR - File Controller
 *
 * Handles HTTP requests related to file uploads
 * and file management.
 *
 * Controller
 *     ↓
 * file_upload_service_*()
 *     ↓
 * file_repository_*()
 */

require_once __DIR__ . '/../services/file_upload_service.php';
require_once __DIR__ . '/../repositories/file_repository.php';


/*
|--------------------------------------------------------------------------
| Get Authenticated User ID
|--------------------------------------------------------------------------
*/

function file_controller_auth_user_id(): int
{
    $user = auth_user();
    return max( 0, (int) ( $user['id'] ?? 0 ) );
}


/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

function file_controller_upload( array $request = [], array $files = [] ): array {
    $user_id = file_controller_auth_user_id();
    if ($user_id <= 0) {
        return [ 'success' => false, 'message' => 'Unauthorized.'];
    }

    $file = file_controller_resolve_uploaded_file( $files, $request );

    if ( $file === null ) {
        return [ 'success' => false, 'message' => 'No file was provided.'];
    }

    $options = [ 'user_id' => $user_id, 'directory' => $request['directory'] ?? null, 'folder' => $request['folder'] ?? null, 'type' => $request['type'] ?? null, 'category' => $request['category'] ?? null, 'visibility' => $request['visibility'] ?? 'private'];

    try { $result = file_upload_service_upload( $file, $options );
        return file_controller_success( is_array($result) ? file_controller_sanitize_file($result) : $result, 'File uploaded successfully.');
    } catch (Throwable $e) {
        return file_controller_error( 'Unable to upload file.');
    }
}


/*
|--------------------------------------------------------------------------
| Upload Multiple Files
|--------------------------------------------------------------------------
*/

function file_controller_upload_multiple( array $request = [], array $files = [] ): array {
    $user_id = file_controller_auth_user_id();

    if ($user_id <= 0) {
        return  ['success' => false, 'message' => 'Unauthorized.' ];
    }

    $uploaded_files = file_controller_normalize_multiple_files( $files );

    if ( empty( $uploaded_files ) ) {
        return  ['success' => false, 'message' => 'No files were provided.'];
    }

    $results = [];
    $success = true;

    foreach ( $uploaded_files as $file ) {
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
            $result = file_upload_service_upload( $file, $options );
            $results[] = $result;

            if ( $result === false ) {
                $success = false;
            }

        } catch (Throwable $e) {
            $results[] = false;
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
            file_controller_sanitize_file_list(
                $results
            )
    ];
}


/*
|--------------------------------------------------------------------------
| Sanitize File List
|--------------------------------------------------------------------------
*/

function file_controller_sanitize_file_list( array $items ): array {

    $sanitized = [];

    foreach ($items as $item) {
        $sanitized[] = is_array($item) ? file_controller_sanitize_file($item) : $item;
    }

    return $sanitized;
}


/*
|--------------------------------------------------------------------------
| Get File
|--------------------------------------------------------------------------
*/

function file_controller_show( int $file_id ): array {

    $user_id = file_controller_auth_user_id();

    if ($user_id <= 0) {
        return file_controller_error( 'Unauthorized.' );
    }


    try {
        $file = file_repository_find_for_user( $file_id, $user_id );

        if (!$file) {
            return file_controller_error( 'File not found.' );
        }

        return file_controller_success( file_controller_sanitize_file($file) );

    } catch (Throwable $e) {

        return file_controller_error( 'Unable to retrieve file.' );
    }
}


/*
|--------------------------------------------------------------------------
| List User Files
|--------------------------------------------------------------------------
*/

function file_controller_index( array $request = [] ): array {

    $user_id = file_controller_auth_user_id();

    if ($user_id <= 0) {
        return file_controller_error( 'Unauthorized.' );
    }

    $filters = [
        'page' =>
            max( 1, (int) ( $request['page'] ?? 1 ) ),

        'limit' =>
            max( 1, min( 100, (int) ( $request['limit'] ?? 20 ) ) ),

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
        $files =
            file_repository_list_for_user(
                $user_id,
                $filters
            );


        return file_controller_success(
            array_map(
                fn ($item) =>
                    file_controller_sanitize_file($item),
                $files
            )
        );

    } catch (Throwable $e) {

        return file_controller_error(
            'Unable to retrieve files.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

function file_controller_delete( int $file_id): array {

    $user_id = file_controller_auth_user_id();

    if ($user_id <= 0) {
        return file_controller_error( 'Unauthorized.');
    }

    try {
        $file = file_repository_find_for_user( $file_id, $user_id );

        if (!$file) {
            return file_controller_error( 'File not found.', 404 );
        }

        $result = file_upload_service_delete( $file_id, $user_id );

        return file_controller_success( $result, 'File deleted successfully.' );

    } catch (Throwable $e) {
        return file_controller_error( 'Unable to delete file.' );
    }
}


/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

function file_controller_download( int $file_id ): array {

    $user_id = file_controller_auth_user_id();

    if ($user_id <= 0) {
        return file_controller_error( 'Unauthorized.' );
    }

    try {
        $file = file_repository_find_for_user( $file_id, $user_id );

        if (!$file) {
            return file_controller_error( 'File not found.' );
        }

        $path = $file['path'] ?? $file['storage_path'] ?? $file['file_path'] ?? null;

        if ( !$path || !is_file( $path ) ) {
            return file_controller_error( 'Physical file not found.' );
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
                (int) ( $file['size'] ?? filesize($path) )
        ];

    } catch (Throwable $e) {
        return file_controller_error( 'Unable to download file.' );
    }
}


/*
|--------------------------------------------------------------------------
| Resolve Uploaded File
|--------------------------------------------------------------------------
*/

function file_controller_resolve_uploaded_file( array $files, array $request = [] ): ?array {

    if ( isset( $files['file'] ) ) {

        $file = $files['file'];

        if ( is_array($file) && isset( $file['tmp_name'] ) ) {
            return $file;
        }
    }

    if ( isset( $request['file'] ) && is_array( $request['file'] ) ) {

        if ( isset( $request['file']['tmp_name'] ) ) {
            return $request['file'];
        }
    }

    foreach ( $files as $file ) {

        if ( is_array($file) && isset( $file['tmp_name'] ) ) {
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

function file_controller_normalize_multiple_files( array $files ): array {

    $result = [];

    foreach ( $files as $key => $value ) {

        if ( is_array($value) && isset( $value['tmp_name'] ) ) {

            if ( is_array( $value['tmp_name'] ) ) {

                $count = count( $value['tmp_name'] );

                for ( $i = 0; $i < $count; $i++ ) {

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
                $result[] = $value;
            }
        }
    }

    return $result;
}


/*
|--------------------------------------------------------------------------
| Public File URL
|--------------------------------------------------------------------------
|
| Builds the public API download URL for a file. The absolute filesystem
| path is never exposed to clients.
|
*/

function file_controller_public_file_url( int $file_id ): string {

    $base = rtrim( (string) ( getenv('APP_URL') ?: request_base_url() ), '/' );

    return $base . '/api/v1/files/' . $file_id . '?download=true';
}


/*
|--------------------------------------------------------------------------
| Sanitize File
|--------------------------------------------------------------------------
|
| Removes internal filesystem fields from a file record before it is
| returned to a client and attaches a public download URL.
|
*/

function file_controller_sanitize_file( array $file ): array {

    unset( $file['path'], $file['storage_path'] );

    $file_id = (int) ( $file['id'] ?? 0 );

    if ($file_id > 0) {
        $file['url'] = file_controller_public_file_url( $file_id );
    }

    return $file;
}


/*
|--------------------------------------------------------------------------
| Success Response
|--------------------------------------------------------------------------
*/

function file_controller_success( mixed $data = null, string $message = 'Success.' ): array {

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

function file_controller_error( string $message, int $status_code = 400 ): array {

    return [

        'success' =>
            false,

        'message' =>
            $message,

        'data' =>
            null,

        'status' =>
            $status_code
    ];
}
