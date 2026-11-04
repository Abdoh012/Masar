<?php

/**
 * MASAR - Student Profile View Service
 *
 * Business rules for third-party views of a student's public profile.
 *
 * Responsibilities:
 * - Report the total view count and the recent viewers.
 * - Never expose viewer contact details.
 *
 * IMPORTANT:
 * - No SQL.
 * - No HTTP handling.
 * - Database access belongs to student_profile_view_repository.php.
 */

require_once __DIR__ . '/../repositories/student_profile_view_repository.php';
require_once __DIR__ . '/../repositories/student_repository.php';
require_once __DIR__ . '/../../../shared/functions/application_cards.php';


/*
|--------------------------------------------------------------------------
| Profile View Summary
|--------------------------------------------------------------------------
|
| What the profile's "N people viewed your profile" and "Show recent viewers"
| sections render: the real total, plus the most recently discovered viewers.
|
| The recent list exposes who looked (role + display name) and when. It
| deliberately does not expose the viewer's email or phone, and it does not
| expose anything about the student beyond what the student already owns.
|
*/
function student_profile_view_summary( int $student_id, ?int $recent_limit = null ): array {

    if ( $student_id <= 0 ) {
        return [ 'error' => true, 'status' => 422, 'message' => 'Invalid student ID.' ];
    }

    if ( !student_repository_exists( $student_id ) ) {
        return [ 'error' => true, 'status' => 404, 'message' => 'Student profile not found.' ];
    }

    $limit = $recent_limit === null ? PROFILE_VIEW_RECENT_LIMIT : max( 1, min( (int) $recent_limit, 100 ) );

    $total  = student_profile_view_repository_count( $student_id );
    $recent = student_profile_view_repository_recent( $student_id, $limit );

    return [
        'data' => [
            'total_views'     => $total,
            'recent_viewers'  => array_map( 'student_profile_view_present', $recent ),
            'recent_limit'    => $limit,
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Present One Viewer
|--------------------------------------------------------------------------
*/

function student_profile_view_present( array $row ): array {

    $role = (string) ( $row['viewer_role'] ?? '' );

    return [
        'viewer_id'    => (int) ( $row['viewer_id'] ?? 0 ),
        'viewer_role'  => $role,
        'display_name' => $row['display_name'] ?? null,
        'viewed_at'    => application_iso8601( $row['viewed_at'] ?? null ),
    ];
}
