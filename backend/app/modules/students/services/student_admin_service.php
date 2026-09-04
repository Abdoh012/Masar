<?php

/**
 * MASAR - Student Admin Service
 *
 * Orchestrates the permanent deletion of a student account:
 * 1. Validates the target is a student and owns no company data.
 * 2. Collects the physical paths of the student's uploaded files.
 * 3. Deletes all student-owned rows, the student record and the
 *    underlying user record inside a single transaction.
 * 4. Removes the physical files after the commit (best effort).
 * 5. Records an audit log entry that survives the deletion.
 *
 * Service
 *     ↓
 * student_admin_repository_*()
 *     ↓
 * Database
 */

require_once __DIR__ . '/../repositories/student_admin_repository.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';

function student_admin_service_delete( int $student_id, array $admin_user ): array {
    $student = student_admin_repository_find_student( $student_id );

    if ( $student === null ) {
        return [
            'success' => false,
            'message' => 'Student not found.',
            'status' => 404,
        ];
    }

    $user_id = (int) ( $student['user_id'] ?? 0 );
    $user = $user_id > 0 ? student_admin_repository_find_user( $user_id ) : null;

    if ( $user !== null && strtolower( (string) ( $user['role'] ?? '' ) ) !== USER_ROLE_STUDENT ) {
        return [
            'success' => false,
            'message' => 'The target account is not a student and cannot be deleted.',
            'status' => 422,
        ];
    }

    if ( $user_id > 0 && student_admin_repository_user_owns_company( $user_id ) ) {
        return [
            'success' => false,
            'message' => 'The student owns a company account and cannot be deleted.',
            'status' => 422,
        ];
    }

    $file_paths = $user_id > 0 ? student_admin_repository_collect_file_paths( $user_id ) : [];

    try {
        db_transaction(
            function () use ( $student_id, $user_id, $student, $user, $admin_user ): void {
                student_admin_repository_delete_student_owned_rows( $student_id );
                student_admin_repository_delete_student_record( $student_id );

                if ( $user_id > 0 ) {
                    student_admin_repository_delete_user_tokens( $user_id );
                    student_admin_repository_delete_user_record( $user_id );
                }

                audit_log_event(
                    'admin.student.deleted',
                    'student',
                    $student_id,
                    [
                        'user_id' => $user_id,
                        'email' => $user['email'] ?? null,
                        'full_name' => $student['full_name'] ?? null,
                    ],
                    null,
                    $admin_user
                );
            }
        );
    } catch ( Throwable $exception ) {
        if ( function_exists( 'logger_security' ) ) {
            logger_security( 'student_delete_failed', [
                'student_id' => $student_id,
                'user_id' => $user_id,
                'message' => $exception->getMessage(),
            ] );
        }

        return [
            'success' => false,
            'message' => 'Unable to delete the student.',
            'status' => 500,
        ];
    }

    $removed_files = 0;

    foreach ( $file_paths as $path ) {
        if ( !is_file( $path ) ) {
            continue;
        }

        if ( @unlink( $path ) ) {
            $removed_files++;
        } else {
            error_log( 'MASAR student_delete physical file cleanup failed: ' . $path );
        }
    }

    return [
        'success' => true,
        'message' => 'Student deleted successfully.',
        'data' => [
            'student_id' => $student_id,
            'user_id' => $user_id,
            'files_removed' => $removed_files,
        ],
    ];
}
