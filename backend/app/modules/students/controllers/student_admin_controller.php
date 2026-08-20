<?php

/**
 * MASAR - Student Admin Controller
 *
 * Handles HTTP requests for admin-driven student deletion.
 *
 * Controller
 *     ↓
 * student_admin_service_*()
 *     ↓
 * student_admin_repository_*()
 */

require_once __DIR__ . '/../services/student_admin_service.php';

function student_admin_controller_delete( int $student_id ): void {
    if ( $student_id <= 0 ) {
        response_not_found( 'Student not found.' );
        return;
    }

    $admin_user = is_array( auth_user() ) ? auth_user() : [];

    $result = student_admin_service_delete( $student_id, $admin_user );

    if ( empty( $result['success'] ) ) {
        response_error( $result['message'] ?? 'Unable to delete the student.', (int) ( $result['status'] ?? 400 ) );
        return;
    }

    response_success( $result['data'] ?? null, $result['message'] ?? 'Student deleted successfully.' );
}
