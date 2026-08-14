<?php

require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/core/http/request.php';
require_once __DIR__ . '/../app/core/http/response.php';
require_once __DIR__ . '/../app/core/cron/runner.php';

$path = request_path();
$method = request_method();

if ($path === '/cron' && $method === 'GET') {
    response_success([
        'available_jobs' => cron_list_jobs(),
        'message' => 'Cron runner is available.'
    ]);
    return;
}

if ($path === '/cron/run' && $method === 'POST') {
    $input = request_input();
    $job = $input['job'] ?? null;

    if ($job) {
        $result = cron_run_job((string) $job);
        response_success($result, 'Cron job executed.');
        return;
    }

    $result = cron_run_all_jobs();
    response_success($result, 'All cron jobs executed.');
    return;
}

response_not_found('Cron endpoint not found.');
