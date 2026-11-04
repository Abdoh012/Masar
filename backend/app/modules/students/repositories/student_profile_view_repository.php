<?php

/**
 * MASAR - Student Profile View Repository
 *
 * Responsible only for database operations related to
 * third-party views of a student's public profile.
 *
 * IMPORTANT:
 * - No business logic.
 * - No validation.
 * - No HTTP handling.
 * - No authentication logic.
 *
 * Database:
 * student_profile_views
 */

require_once __DIR__ . '/../../../core/database/query.php';

/*
|--------------------------------------------------------------------------
| Count Views
|--------------------------------------------------------------------------
|
| The total number of distinct viewers of this student. Profile-view writes
| are not part of the API, so this is a plain COUNT(*) over the student's
| existing rows.
|
*/

function student_profile_view_repository_count( int $student_id ): int {

    if ( $student_id <= 0 ) {
        return 0;
    }

    $row = db_fetch_one(
        " SELECT COUNT(*) AS total FROM student_profile_views WHERE student_id = ? ",
        [ $student_id ]
    );

    return (int) ( $row['total'] ?? 0 );
}

/*
|--------------------------------------------------------------------------
| Recent Viewers
|--------------------------------------------------------------------------
|
| The most recently discovered viewers of this student, newest first.
|
| Ordering is viewed_at DESC then viewer_id DESC. The id tie-break matters
| here: viewers are recorded in bursts (a company reviewing several candidate
| profiles in one sitting) and those rows share a viewed_at to the second, so
| without a secondary sort the order of a same-second group would be whatever
| MySQL happened to return. id DESC is the repository's own insertion order
| and is stable.
|
| The viewer is resolved to a display name by ROLE: a student viewer is named
| from students.full_name, a company viewer from companies.legal_name, and an
| admin viewer has no public identity, so it falls back to the role label.
| Email and phone are deliberately NOT selected: this list is shown to a
| student about the people who looked at their profile, and it must not
| become a channel for one user to read another user's contact details.
|
*/

function student_profile_view_repository_recent( int $student_id, int $limit ): array {

    if ( $student_id <= 0 ) {
        return [];
    }

    $limit = max( 1, min( (int) $limit, 100 ) );

    $sql = "
        SELECT
            v.viewer_id,
            v.viewer_role,
            v.viewed_at,
            COALESCE( st.full_name, co.legal_name ) AS display_name
        FROM student_profile_views v
        LEFT JOIN students st
            ON st.user_id = v.viewer_id
            AND v.viewer_role = 'student'
        LEFT JOIN companies co
            ON co.user_id = v.viewer_id
            AND v.viewer_role = 'company'
        WHERE v.student_id = ?
        ORDER BY v.viewed_at DESC, v.viewer_id DESC
        LIMIT " . (int) $limit . "
    ";

    return db_fetch_all( $sql, [ $student_id ] );
}
