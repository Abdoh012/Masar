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
POST /api/auth/change-password
```

### Authentication

Required.

### Request

```json
{
    "current_password": "OldPassword@123",
    "password": "NewPassword@123",
    "password_confirmation": "NewPassword@123"
}
```

### Response

```json
{
    "success": true,
    "message": "Password changed successfully."
}
```

### Invalid Current Password

```http
422 Unprocessable Entity
```

```json
{
    "success": false,
    "message": "Current password is incorrect."
}
```

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
