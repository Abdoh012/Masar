<?php

/**
 * MASAR - Student Profile Views Test Seeder (DEVELOPMENT / TEST DATA ONLY)
 *
 * Populates deterministic third-party profile-view events for the existing
 * backend test student so:
 *
 *     GET /api/v1/students/me/profile-views
 *
 * returns a non-zero total_views and a populated recent_viewers list.
 *
 * Target student:
 *     student_id 1498 / mammuslim2003@gmail.com / Mohamed Ahmed
 *
 * Viewer identities are existing demo students and companies. This seeder
 * creates no users, students, companies or trainings. It only writes rows to
 * student_profile_views.
 *
 * The API contract exposes viewer_id, viewer_role, display_name and viewed_at.
 * It does not expose a job title or a separate company field, so the display
 * name is resolved exactly as the endpoint already does: students.full_name
 * for student viewers and companies.legal_name for company viewers.
 *
 * Idempotent:
 * - student_profile_views has UNIQUE(student_id, viewer_id).
 * - Each run upserts the same 13 events and converges their timestamps, so a
 *   second run cannot create duplicates.
 *
 * Run from the backend root:
 *     php database/seeders/student_profile_views_seeder.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/constants.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

const PVIEW_TARGET_EMAIL = 'mammuslim2003@gmail.com';

/*
|--------------------------------------------------------------------------
| Deterministic Viewer Dataset
|--------------------------------------------------------------------------
|
| The timestamps are fixed dates so the newest-first ordering and the
| recent_limit cap can be asserted without depending on the run time.
| The target student is deliberately absent: self-views are never seeded.
|
*/

function pview_viewers(): array
{
    return [
        [
            'email' => 'careers@niletech.eg',
            'role' => ROLE_COMPANY,
            'name' => 'NileTech Solutions',
            'viewed_at' => '2026-10-26 09:15:00',
        ],
        [
            'email' => 'talent@futureworks.io',
            'role' => ROLE_COMPANY,
            'name' => 'FutureWorks Software',
            'viewed_at' => '2026-10-25 14:30:00',
        ],
        [
            'email' => 'hr@cairomed.eg',
            'role' => ROLE_COMPANY,
            'name' => 'Cairo Medical Center',
            'viewed_at' => '2026-10-24 11:05:00',
        ],
        [
            'email' => 'omar.shazly@gmail.com',
            'role' => ROLE_STUDENT,
            'name' => 'Omar Khaled El-Shazly',
            'viewed_at' => '2026-10-23 16:45:00',
        ],
        [
            'email' => 'careers@alexdilabs.com',
            'role' => ROLE_COMPANY,
            'name' => 'Alexandria Digital Labs',
            'viewed_at' => '2026-10-22 10:20:00',
        ],
        [
            'email' => 'mariam.hassan@gmail.com',
            'role' => ROLE_STUDENT,
            'name' => 'Mariam Ahmed Hassan',
            'viewed_at' => '2026-10-21 13:10:00',
        ],
        [
            'email' => 'info@atlaseng.eg',
            'role' => ROLE_COMPANY,
            'name' => 'Atlas Engineering',
            'viewed_at' => '2026-10-20 09:00:00',
        ],
        [
            'email' => 'careers@brightreach.eg',
            'role' => ROLE_COMPANY,
            'name' => 'BrightReach Marketing',
            'viewed_at' => '2026-10-19 15:35:00',
        ],
        [
            'email' => 'youssef.farouk@outlook.com',
            'role' => ROLE_STUDENT,
            'name' => 'Youssef Mohamed Farouk',
            'viewed_at' => '2026-10-18 12:25:00',
        ],
        [
            'email' => 'hr@luxorpharma.com',
            'role' => ROLE_COMPANY,
            'name' => 'Luxor Pharma',
            'viewed_at' => '2026-10-17 08:50:00',
        ],
        [
            'email' => 'mail@themis-law.com',
            'role' => ROLE_COMPANY,
            'name' => 'Themis Law Partners',
            'viewed_at' => '2026-10-16 17:05:00',
        ],
        [
            'email' => 'hr@ledgerpro.eg',
            'role' => ROLE_COMPANY,
            'name' => 'LedgerPro Accounting',
            'viewed_at' => '2026-10-15 10:40:00',
        ],
        [
            'email' => 'farida.lotfy@gmail.com',
            'role' => ROLE_STUDENT,
            'name' => 'Farida Tarek Lotfy',
            'viewed_at' => '2026-10-14 11:30:00',
        ],
    ];
}

function pview_resolve_target(PDO $pdo): array
{
    $statement = $pdo->prepare(
        'SELECT s.id AS student_id, s.user_id, u.email, s.full_name
         FROM students s
         INNER JOIN users u ON u.id = s.user_id
         WHERE u.email = ?
         LIMIT 1'
    );
    $statement->execute([PVIEW_TARGET_EMAIL]);

    $target = $statement->fetch();

    if (!is_array($target)) {
        throw new RuntimeException(
            'Target student ' . PVIEW_TARGET_EMAIL . ' was not found. Aborting.'
        );
    }

    return $target;
}

function pview_resolve_viewers(PDO $pdo, int $target_user_id): array
{
    $statement = $pdo->prepare(
        "SELECT
            u.id AS viewer_id,
            u.role,
            u.status,
            COALESCE(st.full_name, co.legal_name) AS display_name
         FROM users u
         LEFT JOIN students st ON st.user_id = u.id
         LEFT JOIN companies co ON co.user_id = u.id
         WHERE u.email = ?
         LIMIT 1"
    );

    $viewers = [];

    foreach (pview_viewers() as $fixture) {
        $statement->execute([$fixture['email']]);
        $viewer = $statement->fetch();

        if (!is_array($viewer)) {
            throw new RuntimeException(
                'Viewer ' . $fixture['email'] . ' was not found. Aborting.'
            );
        }

        if ((string) ($viewer['role'] ?? '') !== $fixture['role']) {
            throw new RuntimeException(
                'Viewer ' . $fixture['email'] . ' has role '
                . var_export($viewer['role'] ?? null, true)
                . ', expected ' . $fixture['role'] . '. Aborting.'
            );
        }

        if ((string) ($viewer['status'] ?? '') !== 'active') {
            throw new RuntimeException(
                'Viewer ' . $fixture['email'] . ' is not active. Aborting.'
            );
        }

        if ((string) ($viewer['display_name'] ?? '') !== $fixture['name']) {
            throw new RuntimeException(
                'Viewer ' . $fixture['email'] . ' resolved to '
                . var_export($viewer['display_name'] ?? null, true)
                . ', expected ' . var_export($fixture['name'], true) . '. Aborting.'
            );
        }

        $viewer_id = (int) $viewer['viewer_id'];

        if ($viewer_id === $target_user_id) {
            throw new RuntimeException(
                'Viewer ' . $fixture['email'] . ' is the target student; self-views are not seeded. Aborting.'
            );
        }

        $viewers[] = [
            'viewer_id' => $viewer_id,
            'viewer_role' => $fixture['role'],
            'display_name' => $fixture['name'],
            'viewed_at' => $fixture['viewed_at'],
        ];
    }

    return $viewers;
}

function pview_seed(PDO $pdo): array
{
    $target = pview_resolve_target($pdo);
    $target_id = (int) $target['student_id'];
    $target_user_id = (int) $target['user_id'];
    $viewers = pview_resolve_viewers($pdo, $target_user_id);

    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO student_profile_views
                (student_id, viewer_id, viewer_role, viewed_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                viewer_role = VALUES(viewer_role),
                viewed_at = VALUES(viewed_at)'
        );

        foreach ($viewers as $viewer) {
            $statement->execute([
                $target_id,
                $viewer['viewer_id'],
                $viewer['viewer_role'],
                $viewer['viewed_at'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    $count_statement = $pdo->prepare(
        'SELECT COUNT(*) FROM student_profile_views WHERE student_id = ?'
    );
    $count_statement->execute([$target_id]);
    $total = (int) $count_statement->fetchColumn();

    return [
        'target' => $target,
        'viewers' => $viewers,
        'total' => $total,
    ];
}

if (
    PHP_SAPI === 'cli'
    && isset($_SERVER['SCRIPT_FILENAME'])
    && realpath((string) $_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)
) {
    try {
        $pdo = get_database_connection();
        $report = pview_seed($pdo);

        echo 'Student profile views seeded successfully.' . PHP_EOL;
        echo 'Target: student_id=' . (int) $report['target']['student_id']
            . ' user_id=' . (int) $report['target']['user_id']
            . ' email=' . $report['target']['email']
            . ' name=' . $report['target']['full_name'] . PHP_EOL;
        echo 'Viewers seeded: ' . count($report['viewers']) . PHP_EOL;
        echo 'Rows for target: ' . (int) $report['total'] . PHP_EOL;

        foreach ($report['viewers'] as $viewer) {
            echo '  - ' . $viewer['viewed_at']
                . ' | ' . $viewer['viewer_role']
                . ' | ' . $viewer['display_name']
                . ' | viewer_id=' . $viewer['viewer_id'] . PHP_EOL;
        }
    } catch (Throwable $exception) {
        fwrite(
            STDERR,
            'Student profile views seeder failed: ' . $exception->getMessage() . PHP_EOL
        );

        exit(1);
    }
}
