<?php

/**
 * MASAR - User Controller
 *
 * Handles authenticated user requests.
 *
 * Responsibilities:
 * - Get current user profile.
 * - Update basic account information.
 * - Change account status where allowed.
 *
 * Business logic belongs to user_service.php.
 * Database operations belong to user_repository.php.
 */

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';
require_once __DIR__ . '/../../../shared/functions/authorization.php';
require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';

require_once __DIR__ . '/../services/user_service.php';
require_once __DIR__ . '/../validators/user_validator.php';

/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
|
| GET /api/users/me
|
*/

function user_me(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    // Rate limiting check
    $identifier = request_ip() ?? 'unknown';
    $rate_limit = security_check_rate_limit_tier('global', 'user_me', $identifier, 120, 60);
    if (!$rate_limit['allowed']) {
        response_too_many_requests($rate_limit['message']);
        return;
    }

    // Security logging
    if (function_exists('security_log_event')) {
        security_log_event('user_me_accessed', [
            'user_id' => $user['id'],
            'ip' => $identifier,
        ]);
    }

    $result = user_get_by_id( (int) $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to retrieve user.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Update Current User
|--------------------------------------------------------------------------
|
| PUT /api/users/me
|
*/

function user_update_me(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    // CSRF protection (if enabled)
    if ((bool) filter_var(getenv('CSRF_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
        $csrf_header = request_header(csrf_header_name());
        $csrf_cookie = request_cookie(csrf_cookie_name());
        
        if (!is_string($csrf_header) || !is_string($csrf_cookie) || !hash_equals($csrf_cookie, $csrf_header)) {
            if (function_exists('security_log_event')) {
                security_log_event('user_update_csrf_failed', [
                    'user_id' => $user['id'],
                    'ip' => request_ip(),
                ]);
            }
            response_error('CSRF token validation failed.', 403, [
                'csrf_token' => ['Invalid or missing CSRF token.'],
            ]);
            return;
        }
    }

    $identifier = request_ip() ?? 'unknown';
    
    // Strict rate limiting for updates (sensitive operation)
    $rate_limits = [
        security_check_rate_limit_tier('global', 'user_update', $identifier, 60, 60),
        security_check_rate_limit_tier('ip', 'user_update', $identifier, 20, 900),
        security_check_rate_limit_tier('user', 'user_update', (string) $user['id'], 15, 900),
        security_check_rate_limit_tier('sensitive', 'user_update', $identifier, 10, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            if (function_exists('security_log_event')) {
                security_log_event('user_update_rate_limit_exceeded', [
                    'user_id' => $user['id'],
                    'ip' => $identifier,
                    'tier' => $rate_limit['tier'] ?? 'unknown',
                ]);
            }
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $data = request_input();
    
    // Validate input before processing
    $errors = user_validate_update( $data );

    if (!empty($errors)) {
        if (function_exists('security_log_event')) {
            security_log_event('user_update_validation_failed', [
                'user_id' => $user['id'],
                'ip' => $identifier,
                'error_count' => count($errors),
            ]);
        }
        response_validation_error( $errors );
        return;
    }

    // Execute update
    $result = user_update( (int) $user['id'], $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        if (function_exists('security_log_event')) {
            security_log_event('user_update_failed', [
                'user_id' => $user['id'],
                'ip' => $identifier,
                'message' => $result['message'] ?? 'Update failed',
            ]);
        }
        response_error( $result['message'] ?? 'Unable to update user.', $result['status'] ?? 400 );
        return;
    }

    // Audit log successful update
    if (function_exists('audit_log_user_action')) {
        audit_log_user_action('user_update_success', 'user', $user['id'], [], [
            'ip' => $identifier,
            'fields_updated' => array_keys($data),
        ], $user);
    }

    if (function_exists('security_log_event')) {
        security_log_event('user_update_success', [
            'user_id' => $user['id'],
            'ip' => $identifier,
        ]);
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Get User By ID
|--------------------------------------------------------------------------
|
| GET /api/users/{id}
|
*/

function user_show( int $user_id ): void {

    $current_user = auth_user();

    if (!$current_user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    if ($user_id <= 0) {
        if (function_exists('security_log_event')) {
            security_log_event('user_show_invalid_id', [
                'requester_id' => $current_user['id'],
                'ip' => request_ip(),
                'attempted_id' => $user_id,
            ]);
        }
        response_error( 'Invalid user ID.', 422 );
        return;
    }

    // Resolve the target resource before authorization so a missing resource is
    // reported as 404 (never 500, and never leaks authorization results first).
    $target_user = user_repository_find_by_id( $user_id );

    if ($target_user === null) {
        if (function_exists('security_log_event')) {
            security_log_event('user_show_not_found', [
                'requester_id' => $current_user['id'],
                'requested_user_id' => $user_id,
                'ip' => request_ip(),
            ]);
        }
        response_error( 'User not found.', 404 );
        return;
    }

    // Permission check - strict enforcement. The authenticated user is the only
    // source of truth; the URL {id} only identifies the target resource.
    $is_admin = auth_user_has_role($current_user, ROLE_ADMIN);
    $is_requesting_own = (int) $current_user['id'] === $user_id;

    if (!$is_admin && !$is_requesting_own) {
        if (function_exists('security_log_event')) {
            security_log_event('user_show_unauthorized_access', [
                'requester_id' => $current_user['id'],
                'requester_role' => $current_user['role'] ?? 'unknown',
                'requested_user_id' => $user_id,
                'ip' => request_ip(),
            ]);
        }
        if (function_exists('audit_log_user_action')) {
            audit_log_user_action('unauthorized_user_view', 'user', $current_user['id'], [], [
                'attempted_user_id' => $user_id,
                'ip' => request_ip(),
            ], $current_user);
        }
        response_forbidden('You do not have permission to view this user profile.');
        return;
    }

    $identifier = request_ip() ?? 'unknown';
    
    // Rate limiting
    $rate_limit = security_check_rate_limit_tier('global', 'user_show', $identifier, 150, 60);
    if (!$rate_limit['allowed']) {
        response_too_many_requests($rate_limit['message']);
        return;
    }

    // Security logging
    if (function_exists('security_log_event')) {
        security_log_event('user_show_accessed', [
            'requester_id' => $current_user['id'],
            'requested_user_id' => $user_id,
            'ip' => $identifier,
            'is_admin_access' => $is_admin,
        ]);
    }

    $result = user_get_by_id_for_view( $user_id, (int) $current_user['id'] );
    if ( isset($result['error']) && $result['error'] === true ) {
        if (function_exists('security_log_event')) {
            security_log_event('user_show_not_found', [
                'requester_id' => $current_user['id'],
                'requested_user_id' => $user_id,
                'ip' => $identifier,
            ]);
        }
        response_error( $result['message'] ?? 'Unable to retrieve user.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Delete Current Account
|--------------------------------------------------------------------------
|
| DELETE /api/users/me
|
*/

function user_delete_me(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    // CSRF protection (if enabled)
    if ((bool) filter_var(getenv('CSRF_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
        $csrf_header = request_header(csrf_header_name());
        $csrf_cookie = request_cookie(csrf_cookie_name());
        
        if (!is_string($csrf_header) || !is_string($csrf_cookie) || !hash_equals($csrf_cookie, $csrf_header)) {
            if (function_exists('security_log_event')) {
                security_log_event('user_delete_csrf_failed', [
                    'user_id' => $user['id'],
                    'ip' => request_ip(),
                ]);
            }
            response_error('CSRF token validation failed.', 403, [
                'csrf_token' => ['Invalid or missing CSRF token.'],
            ]);
            return;
        }
    }

    $identifier = request_ip() ?? 'unknown';

    // Extremely strict rate limiting for deletions
    $rate_limits = [
        security_check_rate_limit_tier('global', 'user_delete', $identifier, 30, 60),
        security_check_rate_limit_tier('ip', 'user_delete', $identifier, 5, 900),
        security_check_rate_limit_tier('user', 'user_delete', (string) $user['id'], 3, 900),
        security_check_rate_limit_tier('sensitive', 'user_delete', $identifier, 3, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            if (function_exists('security_log_event')) {
                security_log_event('user_delete_rate_limit_exceeded', [
                    'user_id' => $user['id'],
                    'ip' => $identifier,
                    'tier' => $rate_limit['tier'] ?? 'unknown',
                ]);
            }
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    // Require confirmation token for deletion
    $data = request_input();
    $confirmation_token = $data['confirm_deletion'] ?? $data['confirmation_token'] ?? null;
    
    if (!is_string($confirmation_token) || trim($confirmation_token) === '') {
        if (function_exists('security_log_event')) {
            security_log_event('user_delete_missing_confirmation', [
                'user_id' => $user['id'],
                'ip' => $identifier,
            ]);
        }
        response_error('Account deletion requires explicit confirmation token.', 400, [
            'confirmation_token' => ['Deletion confirmation is required.'],
        ]);
        return;
    }

    // Log deletion attempt
    if (function_exists('security_log_event')) {
        security_log_event('user_delete_initiated', [
            'user_id' => $user['id'],
            'ip' => $identifier,
        ]);
    }

    // Execute deletion
    $result = user_delete_account( (int) $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        if (function_exists('security_log_event')) {
            security_log_event('user_delete_failed', [
                'user_id' => $user['id'],
                'ip' => $identifier,
                'message' => $result['message'] ?? 'Deletion failed',
            ]);
        }
        response_error( $result['message'] ?? 'Unable to delete account.', $result['status'] ?? 400 );
        return;
    }

    // Audit log successful deletion
    if (function_exists('audit_log_user_action')) {
        audit_log_user_action('account_deleted', 'user', $user['id'], [], [
            'ip' => $identifier,
            'self_deletion' => true,
        ], $user);
    }

    if (function_exists('security_log_event')) {
        security_log_event('user_delete_success', [
            'deleted_user_id' => $user['id'],
            'ip' => $identifier,
        ]);
    }

    // Revoke all server-side tokens for the deleted account, mirroring the logout flow.
    // The account status is set to 'deleted' by user_repository_deactivate(), so any
    // reused access token would be rejected by middleware_auth()'s active check anyway;
    // revoking at the DB layer prevents the refresh flow (auth_handle_refresh) from
    // indefinitely rotating a live refresh token for this now-deleted account.
    jwt_revoke_all_refresh_tokens_for_user( (int) $user['id'] );

    $access_token = jwt_current_bearer_token();
    if (is_string($access_token) && trim($access_token) !== '') {
        jwt_revoke_access_token($access_token);
    }

    // Clear all session/token data
    response_clear_remember_cookie();
    jwt_clear_refresh_cookie();

    response_success( [ 'message' => 'Account deleted successfully.' ] );
}
