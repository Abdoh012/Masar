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

require_once __DIR__ . '/../../config/database.php';

function seed_users(PDO $pdo): void
{
    $users = [
        [
            'uuid' => '10000000-0000-4000-8000-000000000001',
            'role' => 'admin',
            'email' => 'admin@masar.test',
            'password' => 'Admin@123456',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '01000000001',
            'status' => 'active',
        ],
        [
            'uuid' => '10000000-0000-4000-8000-000000000002',
            'role' => 'student',
            'email' => 'student@masar.test',
            'password' => 'Student@123456',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'phone' => '01000000002',
            'status' => 'active',
        ],
        [
            'uuid' => '10000000-0000-4000-8000-000000000003',
            'role' => 'company',
            'email' => 'company@masar.test',
            'password' => 'Company@123456',
            'first_name' => 'Test',
            'last_name' => 'Company',
            'phone' => '01000000003',
            'status' => 'active',
        ],
    ];

    $sql = "
        INSERT INTO users (
            uuid,
            role,
            email,
            password_hash,
            first_name,
            last_name,
            phone,
            status,
            email_verified_at,
            phone_verified_at
        )
        VALUES (
            :uuid,
            :role,
            :email,
            :password_hash,
            :first_name,
            :last_name,
            :phone,
            :status,
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            role = VALUES(role),
            password_hash = VALUES(password_hash),
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            phone = VALUES(phone),
            status = VALUES(status),
            email_verified_at = VALUES(email_verified_at),
            phone_verified_at = VALUES(phone_verified_at)
    ";

    $statement = $pdo->prepare($sql);

    foreach ($users as $user) {
        $statement->execute([
            ':uuid' => $user['uuid'],
            ':role' => $user['role'],
            ':email' => $user['email'],
            ':password_hash' => password_hash(
                $user['password'],
                PASSWORD_DEFAULT
            ),
            ':first_name' => $user['first_name'],
            ':last_name' => $user['last_name'],
            ':phone' => $user['phone'],
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
