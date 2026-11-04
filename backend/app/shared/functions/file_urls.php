<?php

/**
 * MASAR - File URL Functions
 *
 * Shared helpers for building the public, client-consumable URL of a
 * stored file. Modules that need to render a file reference (a profile
 * image, an attachment) use this so every module emits the SAME url and
 * the absolute filesystem path stored in `files.path` is never exposed.
 */

require_once __DIR__ . '/../../core/http/request.php';

/*
|--------------------------------------------------------------------------
| Public File URL
|--------------------------------------------------------------------------
|
| Builds the public API url for a file. The absolute filesystem path is
| never exposed to clients.
|
| Lives in shared/functions rather than the files controller so that any
| module may reference a stored file without depending on a controller.
| The files controller delegates here, keeping one url shape.
|
*/

function file_public_url( int $file_id ): ?string
{
    if ($file_id <= 0) {
        return null;
    }

    $base = rtrim( (string) ( getenv('APP_URL') ?: request_base_url() ), '/' );

    return $base . '/api/v1/files/' . $file_id . '?download=true';
}


/*
|--------------------------------------------------------------------------
| Signed File URL
|--------------------------------------------------------------------------
|
| A short-lived URL for a PROFILE IMAGE only. A browser <img> request cannot
| carry the SPA's Bearer token, so the image is addressed with an expiry and
| an HMAC instead of an Authorization header.
|
| The signature is domain-separated from the JWT secret: leaking a signed
| image URL cannot be replayed as an access token, and rotating JWT_SECRET
| invalidates every outstanding image URL. No permanent credential is placed
| in the URL.
|
| The files route honours a valid signature ONLY for rows typed
| `profile_image`; CVs, academic certificates and every other private file
| still require a Bearer token plus an ownership check.
|
*/

function file_signed_url_secret(): string
{
    $secret = getenv( 'JWT_SECRET' );

    if ( !is_string( $secret ) || trim( $secret ) === '' ) {
        $secret = 'change_this_to_a_long_random_secret';
    }

    return hash_hmac( 'sha256', 'masar:file-url:v1', $secret, true );
}

function file_signed_url_signature( int $file_id, int $expires_at ): string
{
    return hash_hmac(
        'sha256',
        'file|' . $file_id . '|' . $expires_at,
        file_signed_url_secret()
    );
}

function file_signed_url( int $file_id, ?int $expires_at = null ): ?string
{
    if ( $file_id <= 0 ) {
        return null;
    }

    $ttl = defined( 'FILE_SIGNED_URL_TTL' ) ? (int) FILE_SIGNED_URL_TTL : 3600;
    $expires_at = $expires_at ?? ( time() + $ttl );

    $base = rtrim( (string) ( getenv( 'APP_URL' ) ?: request_base_url() ), '/' );

    return $base
        . '/api/v1/files/' . $file_id
        . '?download=true'
        . '&expires=' . $expires_at
        . '&signature=' . file_signed_url_signature( $file_id, $expires_at );
}

function file_signed_url_verify( int $file_id, mixed $expires_at, mixed $signature ): bool
{
    if ( $file_id <= 0 || !is_string( $signature ) || $signature === '' ) {
        return false;
    }

    if ( is_int( $expires_at ) ) {
        $expires_at = (string) $expires_at;
    }

    if ( !is_string( $expires_at ) || $expires_at === '' || !ctype_digit( $expires_at ) ) {
        return false;
    }

    $expires_at = (int) $expires_at;

    if ( $expires_at < time() ) {
        return false;
    }

    $expected = file_signed_url_signature( $file_id, $expires_at );

    return hash_equals( $expected, strtolower( $signature ) );
}


