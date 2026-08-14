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
            tl.application_deadline,
            tl.company_id
        FROM training_listings tl
        WHERE
            tl.status = 'published'
            AND tl.application_deadline IS NOT NULL
            AND tl.application_deadline BETWEEN
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
            ta.student_id
        FROM training_applications ta
        WHERE
            ta.training_id = :training_id
            AND ta.status IN ('pending', 'submitted')
        "
    );

    $notificationStatement = $pdo->prepare(
        "
        INSERT INTO notifications (
            user_id,
            type,
            title,
            message,
            data,
            created_at
        )
        VALUES (
            :user_id,
            :type,
            :title,
            :message,
            :data,
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
                'user_id' => (int) $student['student_id'],
                'type' => 'training_expiry',
                'title' => 'Training opportunity is expiring soon',
                'message' =>
                    'The training opportunity "' .
                    $training['title'] .
                    '" will close soon.',
                'data' => json_encode(
                    [
                        'training_id' => (int) $training['id'],
                        'deadline' => $training['application_deadline'],
                    ],
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ),
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
            ts.student_id,
            ts.end_date,
            tl.title
        FROM training_sessions ts
        INNER JOIN training_listings tl
            ON tl.id = ts.training_id
        WHERE
            ts.status = 'active'
            AND ts.end_date IS NOT NULL
            AND ts.end_date BETWEEN
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
            'user_id' => (int) $session['student_id'],
            'type' => 'session_expiry',
            'title' => 'Training session is ending soon',
            'message' =>
                'Your training session "' .
                $session['title'] .
                '" is ending soon.',
            'data' => json_encode(
                [
                    'session_id' => (int) $session['id'],
                    'end_date' => $session['end_date'],
                ],
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ),
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
            ':message' => $notification['message'],
            ':data' => $notification['data'],
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
