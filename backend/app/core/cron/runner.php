<?php

require_once __DIR__ . '/../database/connection.php';

require_once __DIR__ . '/../../../cron/cleanup_temp_files.php';
require_once __DIR__ . '/../../../cron/close_expired_trainings.php';
require_once __DIR__ . '/../../../cron/expire_trial_periods.php';
require_once __DIR__ . '/../../../cron/send_expiry_notifications.php';
require_once __DIR__ . '/../../../cron/cleanup_expired_tokens.php';
require_once __DIR__ . '/../../../cron/cleanup_audit_logs.php';

function cron_list_jobs(): array
{
    return [
        'cleanup_temp_files',
        'close_expired_trainings',
        'expire_trial_periods',
        'send_expiry_notifications',
        'cleanup_expired_tokens',
        'cleanup_audit_logs',
    ];
}

function cron_run_job(string $jobName): array
{
    $jobName = strtolower(trim($jobName));

    $pdo = get_database_connection();

    try {
        switch ($jobName) {
            case 'cleanup_temp_files':
                $deletedCount = cleanup_temp_files(
                    __DIR__ . '/../../../storage/uploads/temp',
                    24
                );

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Temporary files cleaned successfully.',
                    'deleted_count' => $deletedCount,
                ];

            case 'close_expired_trainings':
                $closedCount = close_expired_trainings($pdo);

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Expired trainings closed successfully.',
                    'closed_count' => $closedCount,
                ];

            case 'expire_trial_periods':
                $expiredCount = expire_trial_periods($pdo);

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Trial periods expired successfully.',
                    'expired_count' => $expiredCount,
                ];

            case 'send_expiry_notifications':
                $sentCount = send_expiry_notifications($pdo);

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Expiry notifications sent successfully.',
                    'sent_count' => $sentCount,
                ];

            case 'cleanup_expired_tokens':
                $deletedCount = cleanup_expired_tokens();

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Expired tokens cleaned successfully.',
                    'deleted_count' => $deletedCount,
                ];

            case 'cleanup_audit_logs':
                $deletedCount = cleanup_audit_logs();

                return [
                    'success' => true,
                    'job' => $jobName,
                    'message' => 'Audit logs cleaned successfully.',
                    'deleted_count' => $deletedCount,
                ];

            default:
                return [
                    'success' => false,
                    'job' => $jobName,
                    'message' => 'Unknown cron job.',
                ];
        }
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'job' => $jobName,
            'message' => $exception->getMessage(),
        ];
    }
}

function cron_run_all_jobs(): array
{
    $results = [];

    foreach (cron_list_jobs() as $jobName) {
        $results[] = cron_run_job($jobName);
    }

    return $results;
}
