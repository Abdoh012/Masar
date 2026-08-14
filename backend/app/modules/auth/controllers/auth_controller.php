<?php

/**
 * MASAR - Auth Controller
 *
 * Responsible for handling authentication HTTP requests.
 *
 * IMPORTANT:
 * - No business logic should be placed here.
 * - Business logic belongs to auth_service.php.
 * - Database operations belong to auth_repository.php.
 * - Input validation belongs to auth_validator.php.
 */


/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

$app_config = require_once __DIR__ . '/../../../config/app.php';

require_once __DIR__ . '/../../../core/http/request.php';
require_once __DIR__ . '/../../../core/http/response.php';

require_once __DIR__ . '/../../../core/auth/auth.php';
require_once __DIR__ . '/../../../shared/functions/recaptcha.php';
require_once __DIR__ . '/../../../shared/functions/security.php';
require_once __DIR__ . '/../../../shared/functions/audit.php';

require_once __DIR__ . '/../services/auth_service.php';
require_once __DIR__ . '/../validators/auth_validator.php';

/*
|--------------------------------------------------------------------------
| Register
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/register
|
*/

function auth_handle_register(): void
{
    $data = request_input();

    if (empty($data)) {
        $raw_input = file_get_contents('php://input');
        if ($raw_input !== false && trim($raw_input) !== '') {
            $decoded = json_decode($raw_input, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
    }

    if (empty($data)) {
        $data = $_POST;
    }

    $identifier = request_ip() ?? 'unknown';
    $rate_limits = [
        security_check_rate_limit_tier('global', 'register', $identifier, 60, 60),
        security_check_rate_limit_tier('ip', 'register', $identifier, 10, 900),
        security_check_rate_limit_tier('sensitive', 'register', $identifier, 5, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_register($data);
    $password_rules = security_password_strength_errors($data['password'] ?? '');
    if (!empty($password_rules)) {
        $errors['password'] = array_values(array_unique(array_merge($errors['password'] ?? [], $password_rules)));
    }
    $errors = array_merge_recursive($errors, auth_recaptcha_validation_errors($data));

    if (!empty($errors)) {
        security_log_event('register_validation_failed', ['errors' => $errors, 'ip' => $identifier]);
        response_validation_error($errors, 'Registration validation failed.');
        return;
    }

    $result = auth_register_user($data);

    if (isset($result['error']) && $result['error'] === true) {
        security_log_event('register_failed', ['ip' => $identifier, 'message' => $result['message'] ?? 'Registration failed.']);
        audit_log_user_action('register_failed', 'user', null, [], ['email' => $data['email'] ?? null, 'role' => $data['role'] ?? null, 'ip' => $identifier]);
        response_error($result['message'] ?? 'Registration failed.', $result['status'] ?? 400);
        return;
    }

    $role = strtolower(trim($data['role'] ?? ''));
    $message = 'Account created successfully.';

    if ($role === USER_ROLE_STUDENT) {
        $message = 'Student registration successful. Welcome to MASAR!';
    } elseif ($role === USER_ROLE_COMPANY) {
        $message = 'Company registration submitted successfully and is pending admin approval.';
    }

    security_log_event('register_success', ['role' => $role, 'ip' => $identifier]);
    $created_user = $result['data']['user'] ?? $result['data'] ?? null;
    if (is_array($created_user) && !empty($created_user['id'])) {
        audit_log_user_action('register_success', 'user', $created_user['id'], [], ['role' => $role, 'email' => $created_user['email'] ?? ($data['email'] ?? null)], $created_user);
    } else {
        audit_log_user_action('register_success', 'user', null, [], ['role' => $role, 'email' => $data['email'] ?? null, 'ip' => $identifier]);
    }
    response_created($result['data'] ?? $result, $message);
}

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/login
|
*/

function auth_handle_login(): void
{
    global $app_config;

    $data = request_input();
    $identifier = request_ip() ?? 'unknown';
    $user_email = strtolower(trim((string) ($data['email'] ?? '')));
    $rate_limits = [
        security_check_rate_limit_tier('global', 'login', $identifier, 120, 60),
        security_check_rate_limit_tier('ip', 'login', $identifier, 20, 900),
        security_check_rate_limit_tier('user', 'login', $user_email !== '' ? $user_email : $identifier, 10, 900),
        security_check_rate_limit_tier('sensitive', 'login', $identifier, 10, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_login($data);
    $errors = array_merge_recursive($errors, auth_recaptcha_validation_errors($data));

    if (!empty($errors)) {
        security_log_event('login_validation_failed', ['ip' => $identifier, 'errors' => $errors]);
        response_validation_error($errors, 'Login validation failed.');
        return;
    }

    $remember = !empty($data['remember']);
    $remember_seconds = $remember ? ($app_config['auth']['remember_expiration'] ?? 60 * 60 * 24 * 30) : null;

    $result = auth_service_login_user($data, $remember_seconds);

    if (isset($result['error']) && $result['error'] === true) {
        security_log_event('login_failed', ['ip' => $identifier, 'message' => $result['message'] ?? 'Invalid credentials.']);
        audit_log_user_action('login_failed', 'user', null, [], ['email' => $data['email'] ?? null, 'ip' => $identifier]);
        response_error($result['message'] ?? 'Invalid credentials.', $result['status'] ?? 401);
        return;
    }

    response_clear_remember_cookie();

    if ($remember && isset($result['data']['token'])) {
        $expires = time() + ($remember_seconds ?? ($app_config['auth']['remember_expiration'] ?? 60 * 60 * 24 * 30));
        response_set_remember_cookie($result['data']['token'], $expires);
    }

    $access_token = $result['data']['token'] ?? null;
    if (is_string($access_token) && $access_token !== '') {
        $refresh_token = $result['jwt']['refresh_token'] ?? null;
        if (is_string($refresh_token) && $refresh_token !== '') {
            jwt_set_refresh_cookie($refresh_token, (int) (getenv('JWT_REFRESH_TTL') ?: 2592000));
        }

        $csrf_token = csrf_generate_token();
        csrf_set_cookie($csrf_token);
    }

    security_log_event('login_success', ['ip' => $identifier, 'user_id' => $result['data']['user']['id'] ?? null]);
    $logged_user = $result['data']['user'] ?? null;
    if (is_array($logged_user) && !empty($logged_user['id'])) {
        audit_log_user_action('login_success', 'user', $logged_user['id'], [], ['role' => $logged_user['role'] ?? null, 'ip' => $identifier], $logged_user);
    }
    response_success($result['data'] ?? $result, 'Welcome back!');
}

/*
|--------------------------------------------------------------------------
| Refresh Token
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/refresh
|
*/
function auth_handle_refresh(): void
{
    $refresh_cookie = request_cookie('refresh_token');
    $csrf_cookie = request_cookie(csrf_cookie_name());
    $csrf_header = request_header(csrf_header_name());

    if (!is_string($refresh_cookie) || trim($refresh_cookie) === '') {
        response_unauthorized('Refresh token is required.');
    }

    if (!is_string($csrf_cookie) || trim($csrf_cookie) === '' || !is_string($csrf_header) || trim($csrf_header) === '' || !hash_equals($csrf_cookie, $csrf_header)) {
        response_error('CSRF token validation failed.', 403, [
            'csrf_token' => ['Invalid or missing CSRF token.'],
        ]);
    }

    $payload = jwt_validate_refresh_token($refresh_cookie);
    if ($payload === false) {
        jwt_mark_refresh_token_reused($refresh_cookie);
        response_unauthorized('Invalid or expired refresh token.');
    }

    $user_id = (int) ($payload['sub'] ?? 0);
    $user = auth_find_user_by_id($user_id);
    if ($user === null) {
        response_unauthorized('User not found.');
    }

    $old_hash = hash('sha256', $refresh_cookie);
    $existing_record = db_fetch_one('SELECT id, revoked_at FROM refresh_tokens WHERE token_hash = :token_hash LIMIT 1', ['token_hash' => $old_hash]);
    if (!is_array($existing_record) || !empty($existing_record['revoked_at'])) {
        response_unauthorized('Refresh token has already been used or revoked.');
    }

    // A disabled/deleted/suspended user must never obtain a fresh access token or
    // continue rotating a live refresh token. The presented refresh token is revoked
    // before rejecting so the inactive session cannot keep refreshing at all.
    if (!auth_user_is_active($user)) {
        db_execute('UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = :token_hash AND revoked_at IS NULL', ['token_hash' => $old_hash]);
        response_unauthorized('Account is not active.');
    }

    db_execute('UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = :token_hash AND revoked_at IS NULL', ['token_hash' => $old_hash]);

    $new_access_token = jwt_issue_access_token($user);
    $new_refresh_token = jwt_issue_refresh_token($user);
    $refresh_ttl = (int) (getenv('JWT_REFRESH_TTL') ?: 2592000);

    jwt_set_refresh_cookie($new_refresh_token, $refresh_ttl);
    $new_csrf = csrf_generate_token();
    csrf_set_cookie($new_csrf);

    if (request_cookie(token_cookie_name()) !== null && !empty($new_access_token)) {
        response_set_remember_cookie($new_access_token, time() + ($app_config['auth']['remember_expiration'] ?? 60 * 60 * 24 * 30));
    }

    response_success([
        'user' => auth_sanitize_user($user),
        'token' => $new_access_token,
        'token_type' => 'bearer',
        'expires_in' => (int) (getenv('JWT_ACCESS_TTL') ?: 3600),
    ], 'Token refreshed successfully.');
}

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/logout
|
*/
function auth_handle_logout(): void
{
    global $app_config;

    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    jwt_revoke_all_refresh_tokens_for_user( (int) $user['id'] );

    $access_token = jwt_current_bearer_token();
    if (is_string($access_token) && trim($access_token) !== '') {
        jwt_revoke_access_token($access_token);
    }

    audit_log_user_action('logout_success', 'user', $user['id'], [], ['role' => $user['role'] ?? null], $user);
    response_clear_remember_cookie();
    jwt_clear_refresh_cookie();

    response_success( [ 'message' => 'Logged out successfully.' ] );
}

/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
|
| GET /api/v1/auth/me
|
*/

function auth_handle_me(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    $result = auth_get_current_user( $user['id'] );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to retrieve user.', $result['status'] ?? 404 );
        return;
    }

    response_success( $result['data'] ?? $result );
}


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
|
| POST /api/auth/change-password
|
*/

function auth_handle_change_password(): void
{
    $user = auth_user();

    if (!$user) {
        response_unauthorized( 'Authentication required.' );
        return;
    }

    $data = request_input();
    $identifier = request_ip() ?? 'unknown';
    $user_key = (string) ($user['id'] ?? $identifier);
    $rate_limits = [
        security_check_rate_limit_tier('global', 'change_password', $identifier, 60, 60),
        security_check_rate_limit_tier('ip', 'change_password', $identifier, 20, 900),
        security_check_rate_limit_tier('user', 'change_password', $user_key, 10, 900),
        security_check_rate_limit_tier('sensitive', 'change_password', $identifier, 10, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_change_password( $data );

    if (!empty($errors)) {
        security_log_event('change_password_validation_failed', ['ip' => $identifier, 'user_id' => $user['id'] ?? null, 'errors' => $errors]);
        response_validation_error( $errors, 'Password change validation failed.' );
        return;
    }

    $result = auth_change_user_password( $user['id'], $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        security_log_event('change_password_failed', ['ip' => $identifier, 'user_id' => $user['id'] ?? null, 'message' => $result['message'] ?? 'Password change failed.']);
        audit_log_user_action('password_change_failed', 'user', $user['id'], [], ['ip' => $identifier], $user);
        response_error( $result['message'] ?? 'Password change failed.', $result['status'] ?? 400 );
        return;
    }

    security_log_event('change_password_success', ['ip' => $identifier, 'user_id' => $user['id'] ?? null]);
    audit_log_user_action('password_change_success', 'user', $user['id'], [], ['ip' => $identifier], $user);

    $access_token = jwt_current_bearer_token();
    if (is_string($access_token) && trim($access_token) !== '') {
        jwt_revoke_access_token($access_token);
    }

    response_clear_remember_cookie();
    jwt_clear_refresh_cookie();

    response_success( $result['data'] ?? [ 'message' => 'Password changed successfully.'] );
}

/*
|--------------------------------------------------------------------------
| Forgot Password (send reset OTP)
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/forgot-password
|
*/
function auth_handle_forgot_password(): void
{
    $data = request_input();
    $identifier = request_ip() ?? 'unknown';
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $rate_limits = [
        security_check_rate_limit_tier('global', 'forgot_password', $identifier, 50, 60),
        security_check_rate_limit_tier('ip', 'forgot_password', $identifier, 5, 900),
        security_check_rate_limit_tier('user', 'forgot_password', $email !== '' ? $email : $identifier, 3, 900),
        security_check_rate_limit_tier('sensitive', 'forgot_password', $identifier, 3, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_forgot_password( $data );
    $errors = array_merge_recursive($errors, auth_recaptcha_validation_errors($data));

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = auth_send_password_reset_otp( $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to send password reset OTP.', $result['status'] ?? 500 );
        return;
    }

    response_success( $result['data'] ?? [ 'message' => 'If the account exists, a password reset OTP has been sent.' ] );
}

/*
|--------------------------------------------------------------------------
| Resend Password Reset OTP
|--------------------------------------------------------------------------
|
| POST/GET /api/v1/auth/resend-reset-otp?email={email}
|
*/
function auth_handle_resend_reset_otp(): void
{
    $data = ['email' => trim(request_query('email') ?? '')];
    $identifier = request_ip() ?? 'unknown';
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $rate_limits = [
        security_check_rate_limit_tier('global', 'resend_reset_otp', $identifier, 40, 60),
        security_check_rate_limit_tier('ip', 'resend_reset_otp', $identifier, 5, 900),
        security_check_rate_limit_tier('user', 'resend_reset_otp', $email !== '' ? $email : $identifier, 3, 900),
        security_check_rate_limit_tier('sensitive', 'resend_reset_otp', $identifier, 3, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_forgot_password( $data );

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = auth_send_password_reset_otp( $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to resend password reset OTP.', $result['status'] ?? 500 );
        return;
    }

    response_success( $result['data'] ?? [ 'message' => 'A new password reset OTP has been sent.' ] );
}

/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/reset-password
|
*/
function auth_handle_reset_password(): void
{
    $data = request_input();
    $identifier = request_ip() ?? 'unknown';
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $rate_limits = [
        security_check_rate_limit_tier('global', 'reset_password', $identifier, 40, 60),
        security_check_rate_limit_tier('ip', 'reset_password', $identifier, 5, 900),
        security_check_rate_limit_tier('user', 'reset_password', $email !== '' ? $email : $identifier, 3, 900),
        security_check_rate_limit_tier('sensitive', 'reset_password', $identifier, 3, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_reset_password( $data );
    $password_rules = security_password_strength_errors($data['password'] ?? $data['new_password'] ?? '');
    if (!empty($password_rules)) {
        $errors['password'] = array_values(array_unique(array_merge($errors['password'] ?? [], $password_rules)));
    }
    $errors = array_merge_recursive($errors, auth_recaptcha_validation_errors($data));

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = auth_reset_user_password( $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to reset password.', $result['status'] ?? 400 );
        return;
    }

    response_clear_remember_cookie();
    jwt_clear_refresh_cookie();

    response_success( $result['data'] ?? [ 'message' => 'Password has been reset successfully.' ] );
}

/*
|--------------------------------------------------------------------------
| Verify Password Reset OTP
|--------------------------------------------------------------------------
|
| POST /api/v1/auth/verify-reset-otp
|
*/
function auth_handle_verify_reset_otp(): void
{
    $data = request_input();
    $identifier = request_ip() ?? 'unknown';
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $rate_limits = [
        security_check_rate_limit_tier('global', 'verify_reset_otp', $identifier, 60, 60),
        security_check_rate_limit_tier('ip', 'verify_reset_otp', $identifier, 10, 900),
        security_check_rate_limit_tier('user', 'verify_reset_otp', $email !== '' ? $email : $identifier, 10, 900),
        security_check_rate_limit_tier('sensitive', 'verify_reset_otp', $identifier, 10, 900),
    ];

    foreach ($rate_limits as $rate_limit) {
        if (!$rate_limit['allowed']) {
            response_too_many_requests($rate_limit['message']);
            return;
        }
    }

    $errors = auth_validate_verify_reset_otp( $data );

    if (!empty($errors)) {
        response_validation_error( $errors );
        return;
    }

    $result = auth_verify_password_reset_otp( $data );

    if ( isset($result['error']) && $result['error'] === true ) {
        response_error( $result['message'] ?? 'Unable to verify OTP.', $result['status'] ?? 400 );
        return;
    }

    response_success( $result['data'] ?? [ 'message' => 'OTP verified successfully. You can now reset your password.' ] );
}

/*
|--------------------------------------------------------------------------
| Start Google OAuth (get authorization URL)
|--------------------------------------------------------------------------
|
| GET /api/v1/auth/google
|
*/
function auth_handle_google_oauth(): void
{
    $result = auth_service_google_authorization_url();

    if (isset($result['error']) && $result['error'] === true) {
        response_error($result['message'] ?? 'Unable to start Google login.', $result['status'] ?? 500);
        return;
    }

    response_success($result['data'] ?? [], 'Google login URL generated.');
}

/*
|--------------------------------------------------------------------------
| Google OAuth Callback
|--------------------------------------------------------------------------
|
| GET /api/v1/auth/google/callback
|
*/
function auth_handle_google_oauth_callback(): void
{
    $code = trim(request_query('code') ?? '');
    $state = trim(request_query('state') ?? '');

    if ($code === '') {
        response_error('Google authorization code is required.', 400);
        return;
    }

    // API/JSON clients (e.g. Postman) receive the same MASAR authentication
    // response as a normal Login (access token + user) so the access token can
    // be captured. The Google OAuth access token is never exposed as the MASAR
    // token; only the freshly issued MASAR access token is returned here.
    if (auth_google_callback_requests_json()) {
        $result = auth_service_google_callback($code, $state);

        if (isset($result['error']) && $result['error'] === true) {
            response_error($result['message'] ?? 'Google login failed.', $result['status'] ?? 400);
            return;
        }

        $access_token = $result['data']['token'] ?? null;
        if (is_string($access_token) && $access_token !== '') {
            $refresh_token = $result['jwt']['refresh_token'] ?? null;
            if (is_string($refresh_token) && $refresh_token !== '') {
                jwt_set_refresh_cookie($refresh_token, (int) (getenv('JWT_REFRESH_TTL') ?: 2592000));
            }

            $csrf_token = csrf_generate_token();
            csrf_set_cookie($csrf_token);
        }

        response_success($result['data'] ?? $result, 'Welcome back!');
    }

    // Browser request: the Google authorization code is single-use, so it must
    // NOT be exchanged here. Instead, relay the code and the matching state to
    // the frontend success page so they can be captured (e.g. copied into the
    // Postman `google_code`/`google_state` variables) and then submitted to this
    // callback as an API/JSON request. The MASAR access token is never placed in
    // this redirect URL.
    $frontendUrl = rtrim(getenv('FRONTEND_URL'), '/') ?: 'http://localhost:3000';
    $successUrl = $frontendUrl . '/auth/google/success'
        . '?code=' . urlencode($code)
        . '&state=' . urlencode($state);
    header('Location: ' . $successUrl);
    header('Cache-Control: no-store, no-cache');
    exit;
}

function auth_google_callback_requests_json(): bool
{
    $accept = trim((string) (request_header('Accept') ?? ''));

    if ($accept !== '' && str_contains(strtolower($accept), 'application/json')) {
        return true;
    }

    return strtolower(trim((string) (request_query('format') ?? ''))) === 'json';
}
