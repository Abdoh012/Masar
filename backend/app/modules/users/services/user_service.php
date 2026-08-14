<?php

    /**
 * MASAR - User Service
 *
 * Contains business logic related to the
 * general user account.
 *
 * Responsibilities:
 * - Retrieve users.
 * - Update general account information.
 * - Delete/deactivate accounts.
 * - Control what user data can be exposed.
 *
 * Student-specific data belongs to students module.
 * Company-specific data belongs to companies module.
 */

require_once __DIR__ . '/../repositories/user_repository.php';
require_once __DIR__ . '/../../companies/repositories/company_repository.php';
require_once __DIR__ . '/../../../shared/functions/email.php';
require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';

/**
 * Create backup of user data before modification/deletion
 * Used for audit trail and recovery purposes
 */
function user_backup_for_audit(array $user): array {
    return [
        'id' => $user['id'] ?? null,
        'email' => $user['email'] ?? null,
        'full_name' => $user['full_name'] ?? null,
        'role' => $user['role'] ?? null,
        'status' => $user['status'] ?? null,
        'created_at' => $user['created_at'] ?? null,
        'updated_at' => $user['updated_at'] ?? null,
    ];
}

function user_get_by_id(int $user_id): array
{

    if ($user_id <= 0) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_invalid_id', [
                'attempted_id' => $user_id,
            ]);
        }
        return ['error' => true, 'status' => 422, 'message' => 'Invalid user ID.'];
    }

    $user = user_repository_find_by_id($user_id);

    if (! $user) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_not_found', [
                'attempted_id' => $user_id,
            ]);
        }
        return ['error' => true, 'status' => 404, 'message' => 'User not found.'];
    }

    // Log successful access
    if (function_exists('security_log_event')) {
        security_log_event('user_get_success', [
            'user_id' => $user_id,
            'user_role' => $user['role'] ?? 'unknown',
            'user_status' => $user['status'] ?? 'unknown',
        ]);
    }

    return ['data' => user_sanitize($user)];
}

function user_get_by_id_for_view(int $user_id, int $current_user_id): array
{

    if ($user_id <= 0) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_view_invalid_id', [
                'attempted_id' => $user_id,
                'requester_id' => $current_user_id,
            ]);
        }
        return ['error' => true, 'status' => 422, 'message' => 'Invalid user ID.'];
    }

    if ($current_user_id <= 0) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_view_unauthenticated', [
                'requested_user_id' => $user_id,
                'requester_id' => $current_user_id,
            ]);
        }
        return ['error' => true, 'status' => 401, 'message' => 'Authentication required.'];
    }

    // Validate current user exists
    $current_user = user_repository_find_by_id($current_user_id);
    if (!$current_user) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_view_requester_not_found', [
                'requested_user_id' => $user_id,
                'requester_id' => $current_user_id,
            ]);
        }
        return ['error' => true, 'status' => 401, 'message' => 'Authentication required.'];
    }

    // Prevent accessing deleted user accounts
    if (isset($current_user['status']) && $current_user['status'] === 'deleted') {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_view_deleted_requester', [
                'requested_user_id' => $user_id,
                'requester_id' => $current_user_id,
            ]);
        }
        return ['error' => true, 'status' => 401, 'message' => 'Authentication required.'];
    }

    $user = user_repository_find_by_id($user_id);

    if (! $user) {
        if (function_exists('security_log_event')) {
            security_log_event('user_get_view_target_not_found', [
                'requested_user_id' => $user_id,
                'requester_id' => $current_user_id,
            ]);
        }
        return ['error' => true, 'status' => 404, 'message' => 'User not found.'];
    }

    // Log successful public view access
    if (function_exists('security_log_event')) {
        security_log_event('user_get_view_success', [
            'requested_user_id' => $user_id,
            'requester_id' => $current_user_id,
            'requester_role' => $current_user['role'] ?? 'unknown',
        ]);
    }

    return ['data' => user_sanitize_public($user)];
}


function user_update( int $user_id, array $data ): array {

    if ($user_id <= 0) {
        return [ 'error'   => true, 'status'  => 422, 'message' => 'Invalid user ID.', ];
    }

    $user = user_repository_find_by_id( $user_id );

    if (! $user) {
        return [ 'error'   => true, 'status'  => 404, 'message' => 'User not found.', ];
    }

    $update_data = [];
    $email_changed = false;
    $old_email = null;
    $new_email = null;

    if (array_key_exists('email', $data)) {
        
        // Normalize email using secure function
        $email = normalize_email( $data['email'] );
        
        if ( $email === null ) {
            if (function_exists('security_log_event')) {
                security_log_event('email_update_invalid_format', [
                    'user_id' => $user_id,
                    'attempted_email' => substr( $data['email'], 0, 50 ),
                    'reason' => 'normalize_email returned null',
                ]);
            }
            return [ 'error' => true, 'status' => 422, 'message' => 'Invalid email address provided.', ];
        }
        
        $old_email = normalize_email( $user['email'] );
        $new_email = $email;
        
        if ( $email !== $old_email ) {
            $email_changed = true;
            
            // Additional strict validation
            if ( strlen($email) < 5 || strlen($email) > 254 ) {
                return [ 'error' => true, 'status' => 422, 'message' => 'Email length is invalid.', ];
            }
            
            // Check for consecutive dots
            if ( strpos( $email, '..' ) !== false ) {
                if (function_exists('security_log_event')) {
                    security_log_event('email_update_suspicious', [
                        'user_id' => $user_id,
                        'reason' => 'consecutive dots detected',
                    ]);
                }
                return [ 'error' => true, 'status' => 422, 'message' => 'Email format is invalid.', ];
            }
            
            // Check for suspicious patterns
            if ( preg_match( '/^\\.|.*\\.@|@\\./', $email ) ) {
                if (function_exists('security_log_event')) {
                    security_log_event('email_update_suspicious', [
                        'user_id' => $user_id,
                        'reason' => 'suspicious dot placement',
                    ]);
                }
                return [ 'error' => true, 'status' => 422, 'message' => 'Email format is invalid.', ];
            }
            
            // Check if email already exists
            if ( user_repository_email_exists_for_other_user( $email, $user_id ) ) {
                if (function_exists('audit_log_user_action')) {
                    audit_log_user_action('email_update_duplicate', 'user', $user_id, [], [
                        'attempted_email' => $email,
                        'reason' => 'duplicate email',
                    ], $user);
                }
                return [ 'error' => true, 'status' => 409, 'message' => 'This email is already in use.', ];
            }
            
            $update_data['email'] = $email;
        }
    }

    if (empty($update_data)) {
        return [ 'data' => [ 'message' => 'No changes were made.', ], ];
    }

    // Log email change before executing
    if ( $email_changed && function_exists('security_log_event') ) {
        security_log_event('email_update_initiated', [
            'user_id' => $user_id,
            'old_email_domain' => email_domain($old_email),
            'new_email_domain' => email_domain($new_email),
        ]);
    }

    $updated = user_repository_update( $user_id, $update_data );

    if (! $updated) {
        if (function_exists('security_log_event')) {
            security_log_event('email_update_failed', [
                'user_id' => $user_id,
                'reason' => 'database update failed',
            ]);
        }
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to update user.', ];
    }

    $updated_user = user_repository_find_by_id( $user_id );

    if (! $updated_user) {
        return [ 'error'   => true, 'status'  => 500, 'message' => 'User was updated but could not be retrieved.', ];
    }

    return [ 'data' => [ 'user' => user_sanitize( $updated_user ), ], ];
}

function user_delete_account(int $user_id): array
{

    if ($user_id <= 0) {
        return ['error' => true, 'status' => 422, 'message' => 'Invalid user ID.'];
    }

    $user = user_repository_find_by_id($user_id);

    if (! $user) {
        if (function_exists('security_log_event')) {
            security_log_event('user_delete_not_found', [
                'attempted_user_id' => $user_id,
            ]);
        }
        return ['error' => true, 'status' => 404, 'message' => 'User not found.'];
    }

    // Backup user data before deletion
    $user_backup = user_backup_for_audit($user);

    // Log deletion attempt
    if (function_exists('security_log_event')) {
        security_log_event('user_delete_initiated', [
            'user_id' => $user_id,
            'user_email' => $user['email'] ?? null,
            'user_role' => $user['role'] ?? null,
        ]);
    }

    // Perform deactivation. For company accounts, the related companies row must
    // be removed in the SAME transaction: if the company deletion fails, the user
    // deletion must not partially succeed, and vice versa.
    try {
        $updated = db_transaction(function () use ($user_id, $user): bool {
            $role = $user['role'] ?? null;

            if (is_company_role($role)) {
                $company = company_repository_find_by_user_id($user_id);

                if ($company !== null) {
                    $company_deleted = company_repository_delete((int) $company['id']);

                    if (!$company_deleted) {
                        throw new RuntimeException('Unable to delete company profile.');
                    }
                }
            }

            $user_deleted = user_repository_deactivate($user_id);

            if (!$user_deleted) {
                throw new RuntimeException('Unable to deactivate account.');
            }

            return true;
        });
    } catch (Throwable $exception) {
        if (function_exists('security_log_event')) {
            security_log_event('user_delete_failed', [
                'user_id' => $user_id,
                'reason' => $exception instanceof RuntimeException ? $exception->getMessage() : 'database update failed',
            ]);
        }
        return ['error' => true, 'status' => 500, 'message' => 'Unable to deactivate account.'];
    }

    if (!$updated) {
        if (function_exists('security_log_event')) {
            security_log_event('user_delete_failed', [
                'user_id' => $user_id,
                'reason' => 'database update failed',
            ]);
        }
        return ['error' => true, 'status' => 500, 'message' => 'Unable to deactivate account.'];
    }

    // Audit log the deletion with backup data
    if (function_exists('audit_log_user_action')) {
        audit_log_user_action('user_deleted', 'user', $user_id, $user_backup, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'previous_status' => $user['status'] ?? null,
            'new_status' => 'deleted',
        ], $user);
    }

    if (function_exists('security_log_event')) {
        security_log_event('user_delete_success', [
            'deleted_user_id' => $user_id,
            'email_domain' => email_domain($user['email'] ?? ''),
        ]);
    }

    return [
        'data' => ['message' => 'Account deleted successfully.'],
    ];
}

function user_sanitize(array $user): array
{
    unset($user['password_hash']);
    unset($user['remember_token']);
    unset($user['verification_token']);
    return $user;
    }

function user_sanitize_public(array $user): array
{
    return [
        'id'         => $user['id'] ?? null,
        'email'      => $user['email'] ?? null,
        'full_name'  => $user['full_name'] ?? null,
        'role'       => $user['role'] ?? null,
        'created_at' => $user['created_at'] ?? null,

    ];
}
