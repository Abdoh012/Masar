<?php

/**
 * MASAR - Users Seeder
 *
 * Seeds the users table with deterministic development/demo accounts.
 *
 * IMPORTANT:
 * - Intended for development/testing environments.
 * - Do not use these credentials in production.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

function seed_users(PDO $pdo): void
{
    $users = [
        [
            'role' => 'admin',
            'email' => 'admin@masar.test',
            'password' => 'Admin@123456',
            'status' => 'active',
        ],
        [
            'role' => 'student',
            'email' => 'student@masar.test',
            'password' => 'Student@123456',
            'status' => 'active',
        ],
        [
            'role' => 'company',
            'email' => 'company@masar.test',
            'password' => 'Company@123456',
            'status' => 'active',
        ],
    ];

    $sql = "
        INSERT INTO users (
            role,
            email,
            password_hash,
            status,
            email_verified_at
        )
        VALUES (
            :role,
            :email,
            :password_hash,
            :status,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            role = VALUES(role),
            password_hash = VALUES(password_hash),
            status = VALUES(status),
            email_verified_at = VALUES(email_verified_at)
    ";

    $statement = $pdo->prepare($sql);

    foreach ($users as $user) {
        $statement->execute([
            ':role' => $user['role'],
            ':email' => $user['email'],
            ':password_hash' => password_hash(
                $user['password'],
                PASSWORD_DEFAULT
            ),
            ':status' => $user['status'],
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| CLI Entry Point
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->beginTransaction();

        seed_users($pdo);

        $pdo->commit();

        echo "Users seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            "Users seeder failed: " .
            $exception->getMessage() .
            PHP_EOL
        );

        exit(1);
    }
}
