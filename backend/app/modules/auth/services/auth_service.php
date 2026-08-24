<?php

/**
 * MASAR - Auth Service
 *
 * Contains authentication business logic.
 *
 * Responsibilities:
 * - Register users.
 * - Login users.
 * - Logout users.
 * - Retrieve authenticated user.
 * - Change passwords.
 *
 * Does NOT contain SQL queries.
 */

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../repositories/auth_repository.php';
require_once __DIR__ . '/../repositories/password_reset_repository.php';
require_once __DIR__ . '/../repositories/oauth_state_repository.php';
require_once __DIR__ . '/../../companies/repositories/company_repository.php';
require_once __DIR__ . '/../../companies/services/company_service.php';
require_once __DIR__ . '/../../students/services/student_service.php';

require_once __DIR__ . '/../../../core/auth/token.php';
require_once __DIR__ . '/../../../core/auth/password.php';
require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';
require_once __DIR__ . '/../../../shared/functions/email.php';
require_once __DIR__ . '/../../../config/mail.php';

/*
|--------------------------------------------------------------------------
| Google OAuth Clock-Skew Tolerance
|--------------------------------------------------------------------------
|
| Google-issued ID tokens carry iat/nbf derived from Google's UTC clock.
| When the PHP host clock runs a few seconds behind Google, a freshly
| issued token is rejected by the JWT library as "not yet valid"
| (Firebase\JWT\BeforeValidException). A bounded leeway is applied ONLY
| around the Google ID-token verification; signature, audience, issuer
| and expiry are still enforced by the Google client library.
|
*/

const AUTH_GOOGLE_ID_TOKEN_LEEWAY_SECONDS = 300;

function auth_hash_password(string $password): string {
    return password_hash_value($password);
}

function auth_verify_password(string $password, string $password_hash): bool {
    return password_verify_value($password, $password_hash);
}

function auth_google_config(): array {
    return [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: (getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/api/v1/auth/google/callback' : ''),
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ],
    ];
}

function auth_google_client(): ?Google\Client {
    $config = auth_google_config();

    if ($config['client_id'] === '' || $config['client_secret'] === '' || $config['redirect_uri'] === '') {
        return null;
    }

    $client = new Google\Client();
    $client->setClientId($config['client_id']);
    $client->setClientSecret($config['client_secret']);
    $client->setRedirectUri($config['redirect_uri']);
    $client->setAccessType('offline');
    $client->setPrompt('select_account');
    $client->setScopes($config['scopes']);

    return $client;
}

function auth_google_verify_id_token(Google\Client $client, string $idToken): array {
    // The JWT leeway is a static on the Firebase class; widen it only for the
    // duration of Google's ID-token verification, then restore the original
    // value. This tolerates a few seconds of host clock skew without relaxing
    // any other token validation in the application.
    $previous_leeway = \Firebase\JWT\JWT::$leeway;
    \Firebase\JWT\JWT::$leeway = AUTH_GOOGLE_ID_TOKEN_LEEWAY_SECONDS;

    try {
        $payload = $client->verifyIdToken($idToken);
    } catch (\Throwable $exception) {
        if (function_exists('security_log_event')) {
            security_log_event('google_id_token_verify_failed', [
                'exception' => get_class($exception),
                'is_clock_skew' => $exception instanceof \Firebase\JWT\BeforeValidException,
            ]);
        }

        $payload = false;
    } finally {
        \Firebase\JWT\JWT::$leeway = $previous_leeway;
    }

    return is_array($payload) ? $payload : [];
}

function auth_service_google_authorization_url(): array {
    $client = auth_google_client();

    if ($client === null) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Google OAuth is not configured properly.' ];
    }

    $state = bin2hex(random_bytes(16));

    $stored = oauth_state_repository_create($state, 600);

    if (!$stored) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to start Google login.' ];
    }

    $client->setState($state);

    return [ 'data' => [ 'authorization_url' => $client->createAuthUrl() ] ];
}

function auth_service_google_callback(string $code, ?string $client_state = null): array {
    $client = auth_google_client();

    if ($client === null) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Google OAuth is not configured properly.' ];
    }

    $client_state = is_string($client_state) ? trim($client_state) : '';

    // Validate and atomically consume the single-use stored state BEFORE any
    // exchange with Google. Missing / unknown / expired / reused states all fail
    // here and the authorization code is never exchanged.
    if (!oauth_state_repository_validate_and_consume($client_state)) {
        return [ 'error' => true, 'status' => 400, 'message' => 'Google authorization state validation failed.' ];
    }

    $token = $client->fetchAccessTokenWithAuthCode($code);

    if (empty($token) || isset($token['error'])) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'Unable to exchange the authorization code for a Google access token.',
        ];
    }

    $idToken = $token['id_token'] ?? null;

    if (!is_string($idToken) || $idToken === '') {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'Google ID token is missing from the response.',
        ];
    }

    $payload = auth_google_verify_id_token($client, $idToken);

    if (empty($payload['email'])) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'Unable to verify Google user information.',
        ];
    }

    if (isset($payload['email_verified']) && $payload['email_verified'] !== true && $payload['email_verified'] !== 'true') {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'Google email address is not verified.',
        ];
    }

    $email = strtolower(trim((string) $payload['email']));
    $user = auth_repository_find_user_by_email($email);

    if ($user === null) {
        $password = bin2hex(random_bytes(16));
        $password_hash = auth_hash_password($password);
        $user_id = auth_repository_create_user([
            'email' => $email,
            'password_hash' => $password_hash,
            'role' => USER_ROLE_STUDENT,
            'status' => USER_STATUS_ACTIVE,
        ]);

if ($user_id === false) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to create account from Google profile.' ];
        }

        $user = auth_repository_find_user_by_id($user_id);

        if ($user === null) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to load created user account.' ];
        }

        // Google verified the email, so mark it verified immediately
        auth_repository_set_email_verified_at((int) $user['id']);

        $user = auth_repository_find_user_by_id($user_id);

        if ($user === null) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to load created user account.' ];
        }
    }

    // Google OAuth in MASAR creates and authenticates Student accounts only.
    if (isset($user['role']) && !is_student_role($user['role'])) {
        if (!user_status_allows_login($user['status'] ?? null)) {
            return [ 'error' => true, 'status' => 403, 'message' => 'This account is not active.' ];
        }

        return [ 'error' => true, 'status' => 403, 'message' => 'Google login is only available for student accounts.' ];
    }

    // Existing student: Google verified this email, so mark it verified if it is
    // still unverified. An existing non-NULL email_verified_at is never overwritten.
    if (empty($user['email_verified_at'])) {
        auth_repository_set_email_verified_at((int) $user['id']);

        $user = auth_repository_find_user_by_id((int) $user['id']);

        if ($user === null) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to load user account.' ];
        }
    }

    $student = student_repository_find_by_user_id((int) $user['id']);
    $new_student_created = false;
    $new_student_name = '';

    if ($student === null) {
        $google_name = trim((string) ($payload['name'] ?? ''));
        $full_name = $google_name !== '' ? $google_name : $email;

        $student_id = student_repository_create([
            'user_id' => (int) $user['id'],
            'full_name' => $full_name,
            'university_id' => null,
            'faculty_id' => null,
            'specialization_id' => null,
        ]);

        if ($student_id === false) {
            return [ 'error' => true, 'status' => 500, 'message' => 'Unable to create the student profile from the Google account.' ];
        }

        $new_student_created = true;
        $new_student_name = $full_name;
    }

    if ($new_student_created) {
        $welcome_sent = auth_send_student_welcome_email($email, $new_student_name);

        if (function_exists('security_log_event')) {
            security_log_event(
                $welcome_sent ? 'welcome_email_sent' : 'welcome_email_failed',
                [
                    'recipient' => $email,
                    'email_domain' => email_domain($email),
                    'role' => USER_ROLE_STUDENT,
                ]
            );
        }
    }

    auth_repository_update_last_login($user['id']);

    $access_token = jwt_issue_access_token($user);
    $refresh_token = jwt_issue_refresh_token($user);

    return [
        'data' => [
            'user' => auth_sanitize_user($user),
            'token' => $access_token,
            'token_type' => 'bearer',
            'expires_in' => (int) (getenv('JWT_ACCESS_TTL') ?: 3600),
            'refresh_expires_in' => (int) (getenv('JWT_REFRESH_TTL') ?: 2592000),
        ],
        'jwt' => [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ],
    ];
}

function password_reset_generate_token(): string {
    return sprintf('%06d', random_int(0, 999999));
}

/*
|--------------------------------------------------------------------------
| Register User
|--------------------------------------------------------------------------
*/

function auth_register_user(array $data): array
{
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $role     = strtolower(trim($data['role'] ?? ''));

    if (auth_repository_email_exists($email)) {
        return ['error' => true, 'status' => 409, 'message' => 'An account with this email already exists.'];
    }

    $allowed_roles = [USER_ROLE_STUDENT, USER_ROLE_COMPANY];

    if (! in_array($role, $allowed_roles, true)) {
        return ['error' => true, 'status' => 422, 'message' => 'Invalid account role.'];
    }

    $status = $role === USER_ROLE_COMPANY ? USER_STATUS_PENDING : USER_STATUS_ACTIVE;
    $password_hash = auth_hash_password($password);
    $registration_error_status = 500;
    $registration_error_message = 'Unable to create account.';

    try {
        $user_id = db_transaction(function () use (
            $email,
            $password_hash,
            $role,
            $status,
            $data,
            &$registration_error_status,
            &$registration_error_message
        ): int {
            $created_user_id = auth_repository_create_user([
                'email' => $email,
                'password_hash' => $password_hash,
                'role' => $role,
                'status' => $status,
            ]);

            if (!$created_user_id) {
                throw new RuntimeException('Unable to create account.');
            }

            if ($role === USER_ROLE_STUDENT) {
                $student_result = student_service_create_profile($created_user_id, [
                    'full_name' => trim($data['full_name'] ?? ''),
                    'field' => trim((string) ($data['field'] ?? '')),
                    'faculty' => trim((string) ($data['faculty'] ?? '')),
                    'specialization' => trim((string) ($data['specialization'] ?? '')),
                    'field_id' => $data['field_id'] ?? null,
                    'specialization_id' => $data['specialization_id'] ?? null,
                ]);

                if (isset($student_result['error'])) {
                    $registration_error_status = (int) ($student_result['status'] ?? 500);
                    $registration_error_message = $student_result['message'] ?? 'Unable to complete student registration.';
                    throw new RuntimeException($registration_error_message);
                }
            }

            if ($role === USER_ROLE_COMPANY) {
                $company_name = trim($data['company_name'] ?? $data['legal_name'] ?? '');

                $company_payload = [
                    'company_name' => $company_name,
                    'description' => trim($data['description'] ?? ''),
                ];

                // Industry: a specialization name, an array of names, or absent.
                if (isset($data['industry'])) {
                    $company_payload['industry'] = is_array($data['industry'])
                        ? $data['industry']
                        : trim((string) $data['industry']);
                }

                if (isset($data['work_field_ids']) && is_array($data['work_field_ids'])) {
                    $company_payload['work_field_ids'] = $data['work_field_ids'];
                }

                if (isset($data['specialization_ids']) && is_array($data['specialization_ids'])) {
                    $company_payload['specialization_ids'] = $data['specialization_ids'];
                }

                $company_result = company_service_create($created_user_id, $company_payload);

                if (
                    (isset($company_result['error']) && $company_result['error'] === true)
                    ||
                    (isset($company_result['success']) && $company_result['success'] === false)
                ) {
                    $registration_error_status = (int) ($company_result['status'] ?? 500);
                    $registration_error_message = $company_result['message'] ?? 'Unable to create company account.';
                    throw new RuntimeException($registration_error_message);
                }
            }

            // 🔐 Create verification token and send verification email
            $verification_token = auth_repository_create_verification_token($created_user_id);
            $verification_expires_at = date('Y-m-d H:i:s', time() + (60 * 60 * 24)); // 24 hours
            $app_url = getenv('APP_URL') ?: 'http://localhost';
            $verify_url = rtrim($app_url, '/') . '/api/v1/auth/verify-email?token=' . urlencode($verification_token);

            if ($role === USER_ROLE_STUDENT) {
                $email_sent = auth_send_verification_email($email, 'student', $verification_token, $verify_url);

                // Record that the verification email was sent by setting the email_verified_at
                // as requested: students' email_verified_at is set at the time of sending the
                // verification message (the email is considered 'sent' even if delivery later fails).
                auth_repository_set_email_verified_at((int) $created_user_id);
            } else {
                $email_sent = auth_send_verification_email($email, 'company', $verification_token, $verify_url);
            }

            if (!$email_sent) {
                // Log but don't block registration — the user can retry verification
                if (function_exists('security_log_event')) {
                    security_log_event('verification_email_failed', [
                        'email' => $email,
                        'role' => $role,
                    ]);
                }
            }

            return (int) $created_user_id;
        });
    } catch (Throwable $exception) {
        if (function_exists('security_log_event')) {
            security_log_event('register_transaction_failed', [
                'email' => $email,
                'role' => $role,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            if (auth_repository_email_exists($email)) {
                return [
                    'error' => true,
                    'status' => 409,
                    'message' => 'An account with this email already exists.',
                ];
            }
        } catch (Throwable $ignored) {
            // Preserve the generic safe response when the database is unavailable.
        }

        return [
            'error' => true,
            'status' => $registration_error_status,
            'message' => $registration_error_message,
        ];
    }

    if ($role === USER_ROLE_STUDENT) {
        // Welcome email is sent separately; verification email already sent above
        $welcome_sent = auth_send_student_welcome_email($email, trim($data['full_name'] ?? ''));

        if (function_exists('security_log_event')) {
            security_log_event(
                $welcome_sent ? 'welcome_email_sent' : 'welcome_email_failed',
                [
                    'recipient' => $email,
                    'email_domain' => email_domain($email),
                    'role' => $role,
                ]
            );
        }
    } elseif ($role === USER_ROLE_COMPANY) {
        auth_send_company_pending_email($email, trim($data['company_name'] ?? $data['legal_name'] ?? ''));
    }

    $user = auth_repository_find_user_by_id($user_id);

    if (! $user) {
        return ['error' => true, 'status' => 500, 'message' => 'Account was created but could not be retrieved.'];
    }

    $response_data = ['user' => auth_sanitize_user($user)];

    if ($role !== USER_ROLE_COMPANY) {
        $response_data['token'] = token_create($user['id'], request_ip(), request_user_agent());
        $response_data['token_type'] = 'bearer';
    }

    return ['data' => $response_data];
}

/*
|--------------------------------------------------------------------------
| Login User
|--------------------------------------------------------------------------
*/

function auth_service_login_user( array $data, ?int $remember_seconds = null ): array {
    $email = strtolower( trim( $data['email'] ?? '' ) );
    $password = $data['password'] ?? '';
    $user = auth_repository_find_user_by_email( $email );

    if ( !$user ) {
        return [ 'error'   => true, 'status'  => 401, 'message' => 'Invalid email or password.', ];
    }

    if ( is_company_role($user['role'] ?? null) && is_pending_user_status($user['status'] ?? null) ) {
        return [ 'error'   => true, 'status'  => 403, 'message' => 'Your company registration is pending approval.', ];
    }

    if ( !user_status_allows_login($user['status'] ?? null) ) {
        return [ 'error'   => true, 'status'  => 403, 'message' => 'This account is not active.', ];
    }

    if ( !auth_verify_password( $password, $user['password_hash'] ) ) {
        return [ 'error'   => true, 'status'  => 401, 'message' => 'Invalid email or password.', ];
    }

    if ( is_company_role($user['role'] ?? null) ) {
        $company = company_repository_find_by_user_id( $user['id'] );

        if ( ! $company ) {
            return [
                'error' => true,
                'status' => 403,
                'message' => 'This company account is not yet approved.',
            ];
        }

        $approval_status = $company['approval_status'] ?? null;

        if ( $approval_status !== 'approved' ) {
            $message = 'Your company registration is pending approval.';

            if ( $approval_status === 'rejected' ) {
                $message = 'Your company registration was rejected. Please contact support for next steps.';
            }

return [
                'error' => true,
                'status' => 403,
                'message' => $message,
];
        }
    }

    auth_repository_update_last_login( $user['id'] );

    $access_token = jwt_issue_access_token($user);
    $refresh_token = jwt_issue_refresh_token($user);

    return [
        'data' => [
            'user' => auth_sanitize_user($user),
            'token' => $access_token,
            'token_type' => 'bearer',
            'expires_in' => (int) (getenv('JWT_ACCESS_TTL') ?: 3600),
            'refresh_expires_in' => (int) (getenv('JWT_REFRESH_TTL') ?: 2592000),
        ],
        'jwt' => [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Logout User
|--------------------------------------------------------------------------
*/

function auth_logout_user( int $user_id ): array {
    $revoked = token_revoke_current();

    if ( !$revoked ) {
        return [ 'error'   => true, 'status'  => 400, 'message' => 'Unable to logout.', ];
    }

    return [ 'data' => [ 'message' => 'Logged out successfully.', ], ];
}

/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

function auth_get_current_user( int $user_id ): array {

    $user = auth_repository_find_user_by_id( $user_id );

    if ( !$user ) {
        return [ 'error'   => true, 'status'  => 404, 'message' => 'User not found.', ];
    }

    return [ 'data' => auth_sanitize_user( $user ), ];
}

/*
|--------------------------------------------------------------------------
| Change User Password
|--------------------------------------------------------------------------
*/

function auth_change_user_password( int $user_id, array $data ): array {
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $user = auth_repository_find_user_by_id( $user_id );

    if (! $user) {
        return [ 'error'   => true, 'status'  => 404, 'message' => 'User not found.', ];
    }

    if ( !auth_verify_password( $current_password, $user['password_hash'] ) ) {
        return [ 'error'   => true, 'status'  => 400, 'message' => 'Current password is incorrect.', ];
    }

    if ( auth_verify_password( $new_password, $user['password_hash'] ) ) {
        return [ 'error'   => true, 'status'  => 422, 'message' => 'New password must be different from the current password.', ];
    }

    $password_hash = auth_hash_password( $new_password );
    $updated = auth_repository_update_password( $user_id, $password_hash );

    if (! $updated) {
        return [ 'error'   => true, 'status'  => 500, 'message' => 'Unable to change password.', ];
    }

    token_revoke_all_for_user( $user_id );
    jwt_revoke_all_refresh_tokens_for_user( $user_id );

    return [ 'data' => [ 'message' => 'Password changed successfully.' ], ];
}

function auth_send_password_reset_otp( array $data ): array {
    $email = strtolower(trim($data['email'] ?? ''));
    $user = auth_repository_find_user_by_email($email);

    if ( ! $user ) {
        return [ 'data' => [ 'message' => 'If the account exists, a password reset OTP has been sent.' ] ];
    }

    $token = password_reset_generate_token();
    $stored = password_reset_repository_create($user['id'], $token, PASSWORD_RESET_TOKEN_EXPIRATION_MINUTES);

    if (! $stored) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to create password reset request.' ];
    }

    $app_url = getenv('APP_URL') ?: 'http://localhost';
    $resend_url = rtrim($app_url, '/') . '/api/v1/auth/resend-reset-otp?email=' . urlencode($email);

    $reset_subject = 'Reset your MASAR password';
    $reset_body = '<p>We received a request to reset your MASAR password.</p>' .
        '<p>Your password reset code is: <strong>' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '</strong></p>' .
        '<p>This code will expire in ' . PASSWORD_RESET_TOKEN_EXPIRATION_MINUTES . ' minutes.</p>' .
        '<p>If you need a new code, click <a href="' . htmlspecialchars($resend_url, ENT_QUOTES, 'UTF-8') . '">Resend latest code</a>.</p>' .
        '<p>If you did not request a password reset, please ignore this email.</p>';

    $sent = send_email(
        $email,
        $reset_subject,
        $reset_body,
        [ 'html' => true ]
    );

    if (! $sent) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to send password reset OTP email.' ];
    }

    return [ 'data' => [ 'message' => 'If the account exists, a password reset OTP has been sent.' ] ];
}

function auth_verify_password_reset_otp( array $data ): array {
    $email = strtolower(trim($data['email'] ?? ''));
    $token = trim($data['token'] ?? '');

    $user = auth_repository_find_user_by_email($email);

    if (! $user) {
        return [ 'error' => true, 'status' => 400, 'message' => 'Invalid OTP or email.' ];
    }

    $reset = password_reset_repository_find_valid($user['id'], $token);

    if (! $reset) {
        return [ 'error' => true, 'status' => 400, 'message' => 'Invalid or expired OTP.' ];
    }

    return [ 'data' => [ 'message' => 'OTP is valid.' ] ];
}

function auth_reset_user_password( array $data ): array {
    $email = strtolower(trim($data['email'] ?? ''));
    $token = trim($data['token'] ?? '');
    $new_password = $data['password'] ?? $data['new_password'] ?? '';

    $user = auth_repository_find_user_by_email($email);

    if (! $user) {
        return [ 'error' => true, 'status' => 400, 'message' => 'Invalid reset token or email.' ];
    }

    $reset = password_reset_repository_find_valid($user['id'], $token);

    if (! $reset) {
        return [ 'error' => true, 'status' => 400, 'message' => 'Invalid or expired reset token.' ];
    }

    $password_hash = auth_hash_password($new_password);
    $updated = auth_repository_update_password($user['id'], $password_hash);

    if (! $updated) {
        return [ 'error' => true, 'status' => 500, 'message' => 'Unable to reset password.' ];
    }

    password_reset_repository_mark_used($reset['id']);
    token_revoke_all_for_user($user['id']);
    jwt_revoke_all_refresh_tokens_for_user($user['id']);

    return [ 'data' => [ 'message' => 'Password reset successfully.' ] ];
}

function auth_send_student_welcome_email(string $email, string $full_name): bool {
    $message = email_build_welcome_message($full_name);

    return send_email(
        $email,
        $message['subject'],
        $message['html'],
        [
            'html' => true,
            'text' => $message['text'],
        ]
    );
}

function auth_send_company_pending_email(string $email, string $company_name): bool {
    $company_name = trim($company_name) !== '' ? trim($company_name) : 'your company';
    $subject = 'Your MASAR company registration is pending approval';
    $body = '<p>Hello,</p>' .
        '<p>Thanks for registering your company with MASAR.</p>' .
        '<p>We have received your registration for <strong>' . htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . '</strong> and will review it shortly.</p>' .
        '<p>Once approved by the MASAR team, you will be able to sign in and manage your company profile.</p>' .
        '<p>Best regards,<br>MASAR Team</p>';

    return send_email($email, $subject, $body, ['html' => true]);
}

function auth_send_company_approved_email(string $email, string $company_name): bool {
    $company_name = trim($company_name) !== '' ? trim($company_name) : 'your company';
    $subject = 'Your MASAR company account is approved';
    $body = '<p>Hello,</p>' .
        '<p>Great news! Your company registration for <strong>' . htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . '</strong> has been approved.</p>' .
        '<p>You can now sign in to MASAR and start using your company dashboard.</p>' .
        '<p>Thank you for registering.</p>' .
        '<p>Best regards,<br>MASAR Team</p>';

    return send_email($email, $subject, $body, ['html' => true]);
}

function auth_send_verification_email(string $email, string $role, string $token, string $verify_url): bool {
    $role_capital = ucfirst(strtolower($role));
    $subject = 'Verify your MASAR ' . $role_capital . ' account';
    $body = '<p>Hello,</p>' .
        '<p>Welcome to MASAR! Please verify your email address by clicking the button below.</p>' .
        '<p>This verification link will expire in 24 hours.</p>' .
        email_template_button('Verify your email', $verify_url) .
        '<p style="margin:0 0 16px 0;color:#6b7280;font-size:14px;">If the button above does not work, copy and paste this link into your browser:</p>' .
        '<p style="margin:0 0 24px 0;color:#6b7280;font-size:14px;word-break:break-all;">' . $verify_url . '</p>' .
        '<p style="margin:0;">If you did not create this account, you can safely ignore this email.</p>' .
        '<p style="margin:0;">Best regards,<br>MASAR Team</p>';

    return send_email($email, $subject, $body, ['html' => true]);
}

function auth_verify_email(string $token): array {
    $token_record = auth_repository_find_verification_token($token);

    if ($token_record === null) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'Invalid verification token.',
        ];
    }

    if (!empty($token_record['used_at'])) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'This verification token has already been used.',
        ];
    }

    if (strtotime($token_record['expires_at']) <= time()) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'This verification token has expired.',
        ];
    }

    $user = auth_repository_find_user_by_id((int) $token_record['user_id']);

    if ($user === null) {
        return [
            'error' => true,
            'status' => 400,
            'message' => 'User associated with this token no longer exists.',
        ];
    }

    // Check that the user's current email still matches the token's email
    // (the token was created for this user, and we verify the user still exists)
    // We don't store the email in the token because it's redundant — the user_id
    // relationship guarantees the email belongs to the correct user.

    // Mark the token as used
    $mark_used = auth_repository_mark_verification_used($token_record['id']);

    if (! $mark_used) {
        return [
            'error' => true,
            'status' => 500,
            'message' => 'Unable to mark verification token as used.',
        ];
    }

    // Set email_verified_at on the user
    $verified = auth_repository_set_email_verified_at((int) $user['id']);

    if (! $verified) {
        return [
            'error' => true,
            'status' => 500,
            'message' => 'Unable to set email verified state.',
        ];
    }

    return [
        'error' => false,
        'status' => 200,
        'message' => 'Email verified successfully.',
        'data' => [
            'user' => auth_sanitize_user($user),
],
    ];
}

/*
|--------------------------------------------------------------------------
| Change User Email
|--------------------------------------------------------------------------
*/

function auth_sanitize_user( array $user ): array {
    $safe_fields = [
        'id',
        'email',
        'role',
        'status',
        'email_verified_at',
        'created_at',
        'updated_at',
        'last_login_at',
    ];

    $sanitized = [];

    foreach ( $safe_fields as $field ) {
        if ( array_key_exists( $field, $user ) ) {
            $sanitized[$field] = $user[$field];
        }
    }

    return $sanitized;
}

/*
|--------------------------------------------------------------------------
| Change User Email
|--------------------------------------------------------------------------
*/

function auth_change_email(int $user_id, string $new_email): array {

    $new_email = strtolower(trim($new_email));

    $user = auth_repository_find_user_by_id($user_id);

    if (! $user) {
        return [
            'error' => true,
            'status' => 404,
            'message' => 'User not found.',
        ];
    }

    // Don't allow changing to the same email without verification reset
    if ($user['email'] === $new_email) {
        // If the email is the same and already verified, keep it verified
        if ($user['email_verified_at'] !== null) {
            return [
                'data' => [
                    'user' => auth_sanitize_user($user),
                ],
                'message' => 'Email is already verified and unchanged.',
                'status' => 200,
            ];
        }
        // If the email is the same but not verified, send a new verification email
        $token = auth_repository_create_verification_token($user_id);
        $app_url = getenv('APP_URL') ?: 'http://localhost';
        $verify_url = rtrim($app_url, '/') . '/api/v1/auth/verify-email?token=' . urlencode($token);
        $email_sent = auth_send_verification_email($user['email'], $user['role'] ?? 'student', $token, $verify_url);

        return [
            'data' => [
                'user' => auth_sanitize_user($user),
            ],
            'message' => $email_sent ? 'Verification email sent.' : 'Failed to send verification email.',
            'status' => $email_sent ? 200 : 500,
        ];
    }

    // Email is changing: update email, clear verification, create new token
    $updated = auth_repository_update_email($user_id, $new_email);

    if (! $updated) {
        return [
            'error' => true,
            'status' => 500,
            'message' => 'Unable to change email.',
        ];
    }

    // Create new verification token for the new email
    $token = auth_repository_create_verification_token($user_id);
    $app_url = getenv('APP_URL') ?: 'http://localhost';
    $verify_url = rtrim($app_url, '/') . '/api/v1/auth/verify-email?token=' . urlencode($token);
    $email_sent = auth_send_verification_email($new_email, $user['role'] ?? 'student', $token, $verify_url);

    return [
        'data' => [
            'user' => auth_sanitize_user(auth_repository_find_user_by_id($user_id)),
        ],
        'message' => $email_sent ? 'Email changed and verification email sent.' : 'Email changed but failed to send verification email.',
        'status' => $email_sent ? 200 : 500,
    ];
}

