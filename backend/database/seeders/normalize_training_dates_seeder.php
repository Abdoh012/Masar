<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

/**
 * MASAR - Training Date Normalization Seeder
 *
 * Corrects the four date columns of EVERY existing training listing so the
 * dataset honours the application-window business rules:
 *
 *     1. published_at < starts_at
 *     2. application_deadline = starts_at           (exact stored datetime)
 *     3. starts_at < ends_at
 *
 * No schema changes are made and no rows are deleted/recreated: training ids,
 * companies, specializations, skills, applications, saved listings and
 * statuses stay untouched. Only published_at / starts_at / ends_at /
 * application_deadline are recomputed.
 *
 * Distribution (deterministic from the row id so re-runs are stable within a
 * day):
 *   - closed  -> fully historical: starts/ends in the past, deadline = starts
 *   - draft   -> published_at stays NULL, consistent future dates
 *   - published:
 *       id % 7 < 3  -> open for application NOW (starts in a few days / this
 *                      month; already listed so it is searchable)
 *       id % 7 in {3,4} -> future (starts next months; already published)
 *       id % 7 in {5,6} -> already started / in progress (ends still future)
 *
 * This guarantees several currently-applicable listings (including Backend
 * Development spec 199) while keeping a realistic mix of upcoming and active
 * ones for search/filter/sort testing.
 *
 * Run from the backend root:
 *     php database/seeders/normalize_training_dates_seeder.php
 */

const NTD_MODE = 'mod7';

function ntd_datetime(int $dayOffset, string $time): string
{
    return date('Y-m-d H:i:s', strtotime(($dayOffset > 0 ? '+' : ($dayOffset < 0 ? '-' : '')) . abs($dayOffset) . ' day ' . $time));
}

function ntd_new_dates(string $status, int $id): array
{
    if ($status === 'closed') {
        $publishedOffset = -(55 + ($id % 15));
        $startsOffset = -(25 + ($id % 25));
        $endsOffset = $startsOffset + (15 + ($id % 15));
        return [
            'published_at' => ntd_datetime($publishedOffset, '10:00:00'),
            'starts_at' => ntd_datetime($startsOffset, '09:00:00'),
            'ends_at' => ntd_datetime($endsOffset, '17:00:00'),
        ];
    }

    if ($status === 'draft') {
        $startsOffset = 40 + ($id % 30);
        $endsOffset = $startsOffset + (30 + ($id % 20));
        return [
            'published_at' => null,
            'starts_at' => ntd_datetime($startsOffset, '09:00:00'),
            'ends_at' => ntd_datetime($endsOffset, '17:00:00'),
        ];
    }

    // published
    $bucket = $id % 7;

    if ($bucket === 3 || $bucket === 4) {
        $startsOffset = 40 + ($id % 55);
        $publishedOffset = -(12 + ($id % 18));
    } elseif ($bucket === 5 || $bucket === 6) {
        $startsOffset = -(5 + ($id % 20));
        $publishedOffset = $startsOffset - (12 + ($id % 18));
    } else {
        $startsOffset = 3 + ($id % 26);
        $publishedOffset = -(12 + ($id % 18));
    }

    $endsOffset = $startsOffset + (30 + ($id % 20));

    return [
        'published_at' => ntd_datetime($publishedOffset, '10:00:00'),
        'starts_at' => ntd_datetime($startsOffset, '09:00:00'),
        'ends_at' => ntd_datetime($endsOffset, '17:00:00'),
    ];
}

function ntd_run(PDO $pdo): array
{
    $rows = $pdo->query(
        'SELECT id, status, published_at, starts_at, ends_at, application_deadline
         FROM training_listings ORDER BY id'
    )->fetchAll(PDO::FETCH_ASSOC);

    $update = $pdo->prepare(
        'UPDATE training_listings
         SET published_at = :published_at, starts_at = :starts_at,
             ends_at = :ends_at, application_deadline = :deadline, updated_at = NOW()
         WHERE id = :id'
    );

    $counts = ['closed' => 0, 'draft' => 0, 'published' => 0];
    $updated = [];

    foreach ($rows as $row) {
        $status = $row['status'];
        $id = (int) $row['id'];
        $dates = ntd_new_dates($status, $id);
        $dates['application_deadline'] = $dates['starts_at'];

        $update->execute([
            'published_at' => $dates['published_at'],
            'starts_at' => $dates['starts_at'],
            'ends_at' => $dates['ends_at'],
            'deadline' => $dates['starts_at'],
            'id' => $id,
        ]);

        $counts[$status] = ($counts[$status] ?? 0) + 1;
        $updated[] = ['id' => $id, 'status' => $status] + $dates;
    }

    return ['counts' => $counts, 'updated' => $updated];
}

function ntd_verify(PDO $pdo): array
{
    $now = date('Y-m-d H:i:s');
    $checks = [];

    $bad = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE published_at IS NOT NULL AND published_at >= starts_at"
    )->fetchColumn();
    $checks['published_before_start'] = ((int) $bad) === 0;

    $bad = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE application_deadline <> starts_at"
    )->fetchColumn();
    $checks['deadline_equals_starts'] = ((int) $bad) === 0;

    $bad = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE starts_at >= ends_at"
    )->fetchColumn();
    $checks['starts_before_ends'] = ((int) $bad) === 0;

    $open = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE status = 'published'
           AND published_at < '$now' AND application_deadline > '$now'
           AND starts_at > '$now' AND ends_at > '$now'"
    )->fetchColumn();
    $checks['applicable_now'] = (int) $open;

    $closedPast = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE status = 'closed' AND ends_at <= '$now'"
    )->fetchColumn();
    $checks['closed_historical'] = (int) $closedPast;

    $specOpen = $pdo->query(
        "SELECT COUNT(*) FROM training_listings
         WHERE specialization_id = 199 AND status = 'published'
           AND published_at < '$now' AND application_deadline > '$now'
           AND starts_at > '$now' AND ends_at > '$now'"
    )->fetchColumn();
    $checks['spec199_applicable_now'] = (int) $specOpen;

    return $checks;
}

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();
        $result = ntd_run($pdo);
        $pdo->commit();

        echo "Updated trainings by status: "
            . implode(', ', array_map(fn($s, $c) => "$s=$c", array_keys($result['counts']), array_values($result['counts'])))
            . PHP_EOL;

        foreach ($result['updated'] as $r) {
            printf(
                "#%-6d %-9s pub=%s start=%s end=%s deadline=%s\n",
                $r['id'], $r['status'],
                $r['published_at'] ?? 'NULL',
                $r['starts_at'], $r['ends_at'], $r['starts_at']
            );
        }

        echo 'Verify: ' . json_encode(ntd_verify($pdo)) . PHP_EOL;
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, 'Normalizer failed: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}