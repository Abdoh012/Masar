<?php

/**
 * MASAR - reCAPTCHA Helper
 *
 * Responsible for verifying Google reCAPTCHA tokens.
 */

function recaptcha_config(): array
{
    return [
        'enabled' => filter_var(getenv('RECAPTCHA_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'secret_key' => getenv('RECAPTCHA_SECRET_KEY') ?: '',
        'site_key' => getenv('RECAPTCHA_SITE_KEY') ?: '',
        'version' => strtolower(trim(getenv('RECAPTCHA_VERSION') ?: 'v2')),
        'min_score' => is_numeric(getenv('RECAPTCHA_MIN_SCORE')) ? (float) getenv('RECAPTCHA_MIN_SCORE') : 0.5,
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ];
}

function recaptcha_is_enabled(): bool
{
    $config = recaptcha_config();
    return $config['enabled'] && $config['secret_key'] !== '';
}

function recaptcha_verify_token(string $token): array
{
    $config = recaptcha_config();

    if (!recaptcha_is_enabled()) {
        return [
            'success' => true,
            'score' => null,
            'action' => null,
        ];
    }

    if ($token === '') {
        return [
            'success' => false,
            'message' => 'reCAPTCHA token is required.',
        ];
    }

    $payload = http_build_query([
        'secret' => $config['secret_key'],
        'response' => $token,
    ]);

    $response = null;

    if (function_exists('curl_version')) {
        $ch = curl_init($config['verify_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);
        $response = @file_get_contents($config['verify_url'], false, $context);
    }

    if ($response === false || $response === null) {
        return [
            'success' => false,
            'message' => 'Unable to verify reCAPTCHA at this time.',
        ];
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        return [
            'success' => false,
            'message' => 'Invalid reCAPTCHA response from verification server.',
        ];
    }

    if (empty($decoded['success'])) {
        $message = 'reCAPTCHA verification failed.';
        if (!empty($decoded['error-codes'])) {
            $message .= ' ' . implode(' ', (array) $decoded['error-codes']);
        }

        return [
            'success' => false,
            'message' => trim($message),
        ];
    }

    if ($config['version'] === 'v3' || $config['version'] === 'score') {
        if (isset($decoded['score']) && $decoded['score'] !== null) {
            $minScore = $config['min_score'];
            if ($minScore > 0 && $decoded['score'] < $minScore) {
                return [
                    'success' => false,
                    'message' => 'reCAPTCHA score is too low. Please try again.',
                ];
            }
        }
    }

    return [
        'success' => true,
        'score' => $decoded['score'] ?? null,
        'action' => $decoded['action'] ?? null,
        'challenge_ts' => $decoded['challenge_ts'] ?? null,
    ];
}

function recaptcha_validate_request(array $data): array
{
    if (!recaptcha_is_enabled()) {
        return [];
    }

    $token = trim(
        $data['recaptcha_token'] ??
        $data['g_recaptcha_response'] ??
        $data['g-recaptcha-response'] ??
        $data['recaptchaResponse'] ?? ''
    );

    if ($token === '') {
        return [
            'recaptcha_token' => ['reCAPTCHA verification is required.'],
        ];
    }

    $result = recaptcha_verify_token($token);

    if (empty($result['success'])) {
        return [
            'recaptcha_token' => [$result['message'] ?? 'reCAPTCHA validation failed.']];
    }

    return [];
}

function auth_recaptcha_validation_errors(array $data): array
{
    return recaptcha_validate_request($data);
}
