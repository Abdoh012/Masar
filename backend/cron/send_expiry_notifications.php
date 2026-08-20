<?php

/**
 * MASAR - Send Expiry Notifications
 *
 * Sends notifications to relevant users before or when
 * training opportunities and trial periods expire.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/database/connection.php';

function send_expiry_notifications(PDO $pdo): int
{
    $notifications = [];

    /*
    |--------------------------------------------------------------------------
    | Training Applications / Listings Expiring Soon
    |--------------------------------------------------------------------------
    */

    $trainingStatement = $pdo->prepare(
        "
        SELECT
            tl.id,
            tl.title,
            tl.ends_at,
            tl.company_id
        FROM training_listings tl
        WHERE
            tl.status = 'published'
            AND tl.ends_at IS NOT NULL
            AND tl.ends_at BETWEEN
                CURRENT_TIMESTAMP
                AND DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
        "
    );

    $trainingStatement->execute();

    $trainings = $trainingStatement->fetchAll(
        PDO::FETCH_ASSOC
    );

    /*
    |--------------------------------------------------------------------------
    | Notify Students
    |--------------------------------------------------------------------------
    */

    $studentStatement = $pdo->prepare(
        "
        SELECT DISTINCT
            s.user_id
        FROM training_applications ta
        INNER JOIN students s
            ON s.id = ta.student_id
        WHERE
            ta.training_id = :training_id
            AND ta.status = 'submitted'
        "
    );

    $notificationStatement = $pdo->prepare(
        "
        INSERT INTO notifications (
            user_id,
            type,
            title,
            body,
            entity_type,
            entity_id,
            created_at
        )
        VALUES (
            :user_id,
            :type,
            :title,
            :body,
            :entity_type,
            :entity_id,
            CURRENT_TIMESTAMP
        )
        "
    );

    foreach ($trainings as $training) {
        $studentStatement->execute([
            ':training_id' => $training['id'],
        ]);

        $students = $studentStatement->fetchAll(
            PDO::FETCH_ASSOC
        );

        foreach ($students as $student) {
            $notifications[] = [
                'user_id' => (int) $student['user_id'],
                'type' => 'training_expiry',
                'title' => 'Training opportunity is expiring soon',
                'body' =>
                    'The training opportunity "' .
                    $training['title'] .
                    '" will close soon.',
                'entity_type' => 'training',
                'entity_id' => (int) $training['id'],
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Training Sessions Expiring Soon
    |--------------------------------------------------------------------------
    */

    $sessionStatement = $pdo->prepare(
        "
        SELECT
            ts.id,
            s.user_id,
            ts.trial_ends_at,
            tl.title
        FROM training_sessions ts
        INNER JOIN training_listings tl
            ON tl.id = ts.training_id
        INNER JOIN students s
            ON s.id = ts.student_id
        WHERE
            ts.status = 'trial'
            AND ts.trial_ends_at IS NOT NULL
            AND ts.trial_ends_at BETWEEN
                CURRENT_TIMESTAMP
                AND DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
        "
    );

    $sessionStatement->execute();

    $sessions = $sessionStatement->fetchAll(
        PDO::FETCH_ASSOC
    );

    foreach ($sessions as $session) {
        $notifications[] = [
            'user_id' => (int) $session['user_id'],
            'type' => 'session_expiry',
            'title' => 'Training session is ending soon',
            'body' =>
                'Your training session "' .
                $session['title'] .
                '" is ending soon.',
            'entity_type' => 'session',
            'entity_id' => (int) $session['id'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Notifications
    |--------------------------------------------------------------------------
    */

    $sentCount = 0;

    foreach ($notifications as $notification) {
        $notificationStatement->execute([
            ':user_id' => $notification['user_id'],
            ':type' => $notification['type'],
            ':title' => $notification['title'],
            ':body' => $notification['body'],
            ':entity_type' => $notification['entity_type'],
            ':entity_id' => $notification['entity_id'],
        ]);

        $sentCount++;
    }

    return $sentCount;
}

/*
|--------------------------------------------------------------------------
| Cron Entry Point
|--------------------------------------------------------------------------
*/

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        $pdo = get_database_connection();
        $pdo->beginTransaction();

        $sentCount = send_expiry_notifications($pdo);

        $pdo->commit();

        echo sprintf(
            "[%s] Sent %d expiry notification(s).%s",
            date('Y-m-d H:i:s'),
            $sentCount,
            PHP_EOL
        );
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            sprintf(
                "[%s] Failed to send expiry notifications: %s%s",
                date('Y-m-d H:i:s'),
                $exception->getMessage(),
                PHP_EOL
            )
        );

        exit(1);
    }
}
