<?php

/**
 * MASAR - Conversations List Default Limit Regression
 *
 * Focused check: GET /api/v1/conversations returns 15 conversations when the
 * client provides no `limit` parameter, and respects an explicit valid limit.
 * Uses the reusable case runner: tests/messaging_conversations_list_case.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE', dirname(__DIR__) . '/');

require_once BASE . 'vendor/autoload.php';
if (file_exists(BASE . '.env')) {
    Dotenv\Dotenv::createUnsafeImmutable(BASE)->safeLoad();
}

require_once BASE . 'app/services/jwt_service.php';

$failures = 0;

function check(string $label, bool $condition): void
{
    global $failures;

    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";

    if (!$condition) {
        $failures++;
    }
}

function conv_list_request(
    string $path,
    string $token
): array {

    $command =
        escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg(BASE . 'tests/messaging_conversations_list_case.php')
        . ' '
        . escapeshellarg($path)
        . ' GET bearer '
        . escapeshellarg($token);

    $environment = [
        'MASAR_CASE_PATH' => $path,
        'MASAR_CASE_BODY' => '',
    ] + getenv();

    $process =
        proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            BASE,
            $environment
        );

    $output =
        (string) stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $status = 0;
    $raw = '';

    foreach (explode("\n", $output) as $line) {

        if (str_starts_with($line, 'STATUS=')) {
            $status = (int) substr($line, 7);
        }

        if (str_starts_with($line, 'BODY=')) {
            $raw = substr($line, 5);
        }
    }

    $json = json_decode($raw, true);

    return [
        'status'     => $status,
        'json'       => is_array($json) ? $json : [],
        'data'       => is_array($json['data'] ?? null) ? $json['data'] : [],
        'pagination' => is_array($json['data']['pagination'] ?? null) ? $json['data']['pagination'] : [],
    ];
}

echo "== Conversations list default limit ==\n";

$token =
    jwt_issue_access_token([
        'id'   => 100532,
        'role' => 'student',
    ]);

$default_page =
    conv_list_request('/api/v1/conversations', $token);

check(
    'GET /api/v1/conversations succeeds',
    $default_page['status'] === 200
);

check(
    'without a limit parameter, the default limit is 20',
    ($default_page['pagination']['limit'] ?? null) === 20
);

$explicit =
    conv_list_request('/api/v1/conversations?limit=7', $token);

check(
    'an explicit valid limit is respected',
    $explicit['status'] === 200
        && ($explicit['pagination']['limit'] ?? null) === 7
);

$invalid =
    conv_list_request('/api/v1/conversations?limit=101', $token);

check(
    'limit=101 is still rejected with 400',
    $invalid['status'] === 400
);

$zero =
    conv_list_request('/api/v1/conversations?limit=0', $token);

check(
    'limit=0 is still rejected with 400',
    $zero['status'] === 400
);

echo "\n== Result ==\n";

if ($failures === 0) {
    echo "ALL PASS\n";
    exit(0);
}

echo "FAILURES: {$failures}\n";
exit(1);
