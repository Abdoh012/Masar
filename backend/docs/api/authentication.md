# MASAR API — Authentication

## Overview

Authentication endpoints are responsible for:

* User registration
* User login
* Logout
* Current authenticated user
* Password reset
* Password change
* Token refresh
* Email verification

Base URL:

```text
/api/auth
```

All request and response bodies use JSON unless otherwise specified.

---

## 1. Register

Creates a new user account.

### Endpoint

```http
POST /api/auth/register
```

### Authentication

Not required.

### Request

```json
{
    "full_name": "Ahmed Mohamed",
    "email": "ahmed@example.com",
    "password": "StrongPassword@123",
    "password_confirmation": "StrongPassword@123",
    "accept_terms": true,
    "role": "student",
    "field": "Engineering",
    "specialization": "Mechanical Engineering"
}
```

For student registration, `field` (User Field) and `specialization` (Specialist) are required and must match seeded lookup data. The legacy `faculty` key is accepted as a fallback for `field` (`faculty` + `specialization`), so clients sending the old key name still work. `university` is no longer part of the student model and is ignored if sent.

### Response

```json
{
    "success": true,
    "message": "Registration successful.",
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmed Mohamed",
            "email": "ahmed@example.com",
            "role": "student",
            "status": "active"
        },
        "token": "ACCESS_TOKEN"
    }
}
```

### Possible Errors

```http
422 Unprocessable Entity
```

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "email": [
            "The email has already been registered."
        ]
    }
}
```

---

## 2. Login

Authenticates an existing user.

### Endpoint

```http
POST /api/auth/login
```

### Authentication

Not required.

### Request

```json
{
    "email": "ahmed@example.com",
    "password": "StrongPassword@123"
}
```

### Response

```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmed Mohamed",
            "email": "ahmed@example.com",
            "role": "student",
            "status": "active"
        },
        "token": "ACCESS_TOKEN"
    }
}
```

### Invalid Credentials

```http
401 Unauthorized
```

```json
{
    "success": false,
    "message": "Invalid email or password."
}
```

---

## 3. Current User

Returns the currently authenticated user.

### Endpoint

```http
GET /api/auth/me
```

### Authentication

Required.

```http
Authorization: Bearer ACCESS_TOKEN
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Ahmed Mohamed",
        "email": "ahmed@example.com",
        "role": "student",
        "status": "active"
    }
}
```

---

## 4. Logout

Invalidates the current authentication session/token.

### Endpoint

```http
POST /api/auth/logout
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "message": "Logged out successfully."
}
```

---

## 5. Refresh Token

Creates a new access token using a valid refresh token/session.

### Endpoint

```http
POST /api/auth/refresh
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "data": {
        "token": "NEW_ACCESS_TOKEN"
    }
}
```

---

## 6. Forgot Password

Starts the password recovery process.

### Endpoint

```http
POST /api/auth/forgot-password
```

### Authentication

Not required.

### Request

```json
{
    "email": "ahmed@example.com"
}
```

### Response

```json
{
    "success": true,
    "message": "If the account exists, a password reset link has been sent."
}
```

The response intentionally does not reveal whether the email belongs to an existing account.

---

## 7. Reset Password

Resets a user's password using a valid reset token.

### Endpoint

```http
POST /api/auth/reset-password
```

### Authentication

Not required.

### Request

```json
{
    "token": "RESET_TOKEN",
    "email": "ahmed@example.com",
    "password": "NewStrongPassword@123",
    "password_confirmation": "NewStrongPassword@123"
}
```

### Response

```json
{
    "success": true,
    "message": "Password has been reset successfully."
}
```

---

## 8. Change Password

Changes the password of the authenticated user.

### Endpoint

```http
POST /api/v1/auth/change-password
```

### Authentication

Required.

### Request

```json
{
    "current_password": "OldPassword@123",
    "new_password": "NewPassword@123",
    "new_password_confirmation": "NewPassword@123"
}
```

> The field names are `current_password` / `new_password` /
> `new_password_confirmation`. `password` and `password_confirmation` are **not**
> read by this endpoint.

### Response

```json
{
    "success": true,
    "message": null,
    "data": {
        "message": "Password changed successfully."
    },
    "errors": null
}
```

### Password Policy

The policy is defined once, in
`security_password_strength_errors()` (`app/shared/functions/security.php`), and
is applied by **register**, **change-password** and **reset-password** alike:

- at least **12 characters**
- at least one lowercase letter
- at least one uppercase letter
- at least one digit
- at least one special (non-alphanumeric) character

> **12 characters, not 8.** If a client-side form advertises an 8-character
> minimum it must be updated: the API rejects an 8-character password with
> `"Password must be at least 12 characters long."`

`auth_validate_reset_password()` additionally carries an older `strlen < 8`
check, but the reset controller merges the strong policy on top of it, so the
effective rule on reset is the strict one above. The 8-character message can
only ever be an *additional*, still-true error, never a weaker outcome.

A rate limit applies: four tiers (global, per IP, per user, sensitive) are
checked before validation.

### Rejections

| Status | Condition |
| ------ | --------- |
| `400` | `current_password` is wrong → `"Current password is incorrect."` |
| `422` | `new_password` is identical to the current one → `"New password must be different from the current password."` |
| `422` | Validation: `current_password` missing, `new_password` failing the policy, or `new_password_confirmation` missing / mismatched. `errors` names the field. |
| `429` | A rate-limit tier was exceeded. |

The submitted passwords are never echoed back, and neither the plaintext nor the
hash is ever logged.

### What else the change touches

On success the endpoint rewrites `users.password_hash` and revokes:

- every row in the legacy `auth_tokens` table for that user,
- every row in `refresh_tokens` for that user,
- the **calling device's** access token, blacklisted explicitly.

It also clears the remember-me cookie and the refresh cookie, writes a
`change_password_success` security event and a `password_change_success` audit
entry.

**No application, training, payment or certificate record is read or written.**

### Session behaviour after a change — read this before promising anything

Access tokens are **stateless JWTs** and are not persisted when they are issued,
so there is no list of a user's outstanding access tokens to walk and revoke.

| Scenario | Result |
| -------- | ------ |
| The device that made the change | Signed out immediately: its access token is blacklisted and both cookies are cleared. |
| Refresh tokens (any device) | Revoked. A stolen refresh token can no longer mint a new access token. |
| **Another device's access token** | **Still valid until it expires on its own.** `JWT_ACCESS_TTL` defaults to `3600` seconds — one hour — after which its refresh fails and the user must log in again. |

So the security window after a password change is **up to one hour** for a
session that was already established on another device. Client copy must not
claim that all other sessions are signed out immediately.

Why this is not tightened here: immediate all-device invalidation needs either a
per-user credential version or a persisted denylist of issued access tokens,
checked on every request. Access tokens carry `iat` and `jti` but no
password-version claim, and the existing `revoked_access_tokens` table is not
wired into the auth flow at all — the only reference to it anywhere is
`student_admin_repository_delete_user_tokens()`, which *deletes* rows when a user
is removed, and it is currently empty. Re-populating it would not help either: a
denylist only works for tokens that are already known. Either route is an
authentication-architecture change affecting login, refresh, logout and
remember-me.

The minimal future path is recorded in the source at
`auth_change_user_password()` in
`app/modules/auth/services/auth_service.php`, and the current behaviour is
pinned by `tests/auth_password_policy_regression.php` (cases 9–12) so it cannot
drift silently.

---

## 9. Verify Email

Verifies the authenticated user's email address.

### Endpoint

```http
GET /api/auth/email/verify
```

### Authentication

Required.

### Query Parameters

```text
token=VERIFICATION_TOKEN
```

Example:

```http
GET /api/auth/email/verify?token=VERIFICATION_TOKEN
```

### Response

```json
{
    "success": true,
    "message": "Email verified successfully."
}
```

---

## 10. Resend Verification Email

Sends a new email verification message.

### Endpoint

```http
POST /api/auth/email/resend
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "message": "Verification email sent successfully."
}
```

---

# Authentication Header

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
```

Example:

```http
GET /api/auth/me
Authorization: Bearer eyJ...
Accept: application/json
```

---

# Standard Authentication Errors

### 401 Unauthorized

Returned when authentication is missing or invalid.

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### 403 Forbidden

Returned when the authenticated user does not have permission.

```json
{
    "success": false,
    "message": "You do not have permission to perform this action."
}
```

### 422 Validation Error

Returned when request validation fails.

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

### 429 Too Many Requests

Returned when authentication rate limits are exceeded.

```json
{
    "success": false,
    "message": "Too many requests. Please try again later."
}
```

### 500 Internal Server Error

Returned when an unexpected server-side error occurs.

```json
{
    "success": false,
    "message": "Internal server error."
}
```

---

# Security Requirements

Authentication implementation must follow these rules:

1. Passwords must never be stored as plain text.
2. Passwords must be hashed using a secure password hashing algorithm.
3. Authentication tokens must not be logged.
4. Password reset tokens must expire.
5. Password reset tokens must be single-use.
6. Login attempts should be rate limited.
7. Sensitive authentication errors should not expose account existence.
8. Protected endpoints must validate the authenticated user.
9. Role-protected endpoints must validate the user's role.
10. Authentication secrets must be stored outside source code.

---

# Related Routes

```text
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/forgot-password
POST   /api/auth/reset-password

GET    /api/auth/me
POST   /api/auth/logout
POST   /api/auth/change-password
POST   /api/auth/refresh

GET    /api/auth/email/verify
POST   /api/auth/email/resend
