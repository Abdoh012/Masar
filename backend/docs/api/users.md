# MASAR API — Users

## Overview

The Users API handles authenticated user account management, profile information, preferences, avatars, and public user profiles.

Base URL:

```text
/api/users
```

Most endpoints require authentication.

---

## Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. Get Current User

Returns the authenticated user's basic account information.

### Endpoint

```http
GET /api/users/me
```

### Authentication

Required.

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

# 2. Update Current User

Updates the authenticated user's account information.

### Endpoint

```http
PUT /api/users/me
```

or:

```http
PATCH /api/users/me
```

### Authentication

Required.

### Request

```json
{
    "name": "Ahmed Ali Mohamed",
    "email": "ahmed.ali@example.com"
}
```

### Response

```json
{
    "success": true,
    "message": "User updated successfully.",
    "data": {
        "id": 1,
        "name": "Ahmed Ali Mohamed",
        "email": "ahmed.ali@example.com",
        "role": "student",
        "status": "active"
    }
}
```

---

# 3. Delete Current User

Deletes or deactivates the authenticated user's account according to the application's account deletion policy.

### Endpoint

```http
DELETE /api/users/me
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "message": "Account deleted successfully."
}
```

> Account deletion should preserve required audit records when required by the business or legal rules.

---

# 4. Get Current User Profile

Returns the complete profile associated with the authenticated user.

### Endpoint

```http
GET /api/users/me/profile
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Ahmed Mohamed",
        "email": "ahmed@example.com",
        "role": "student",
        "profile": {
            "phone": "+201000000000",
            "bio": "Computer Science student.",
            "avatar": null
        }
    }
}
```

---

# 5. Update Current User Profile

Updates profile information.

### Endpoint

```http
PUT /api/users/me/profile
```

or:

```http
PATCH /api/users/me/profile
```

### Authentication

Required.

### Request

```json
{
    "phone": "+201000000000",
    "bio": "Computer Science student interested in software development."
}
```

### Response

```json
{
    "success": true,
    "message": "Profile updated successfully.",
    "data": {
        "phone": "+201000000000",
        "bio": "Computer Science student interested in software development."
    }
}
```

---

# 6. Upload Avatar

Uploads a profile avatar for the authenticated user.

### Endpoint

```http
POST /api/users/me/avatar
```

### Authentication

Required.

### Content Type

```http
Content-Type: multipart/form-data
```

### Request

```text
avatar: image file
```

### Supported Formats

Recommended formats:

```text
jpg
jpeg
png
webp
```

### Response

```json
{
    "success": true,
    "message": "Avatar uploaded successfully.",
    "data": {
        "file_id": 25,
        "url": "/api/files/25"
    }
}
```

---

# 7. Delete Avatar

Removes the current user's avatar.

### Endpoint

```http
DELETE /api/users/me/avatar
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "message": "Avatar deleted successfully."
}
```

---

# 8. Get User Preferences

Returns the authenticated user's application preferences.

### Endpoint

```http
GET /api/users/me/preferences
```

### Authentication

Required.

### Response

```json
{
    "success": true,
    "data": {
        "language": "en",
        "timezone": "Africa/Cairo",
        "email_notifications": true,
        "push_notifications": true
    }
}
```

---

# 9. Update User Preferences

Updates application preferences.

### Endpoint

```http
PUT /api/users/me/preferences
```

### Authentication

Required.

### Request

```json
{
    "language": "en",
    "timezone": "Africa/Cairo",
    "email_notifications": true,
    "push_notifications": false
}
```

### Response

```json
{
    "success": true,
    "message": "Preferences updated successfully.",
    "data": {
        "language": "en",
        "timezone": "Africa/Cairo",
        "email_notifications": true,
        "push_notifications": false
    }
}
```

---

# 10. Get Public User Profile

Returns publicly available information about a user.

### Endpoint

```http
GET /api/users/{id}
```

### Authentication

Required according to the current route configuration.

### Example

```http
GET /api/users/25
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 25,
        "name": "Ahmed Mohamed",
        "role": "student",
        "avatar": "/api/files/50"
    }
}
```

Private information such as passwords, authentication tokens, and sensitive account data must never be returned.

---

# Validation Rules

## Name

The name should:

* Be required when creating a user.
* Be a string.
* Have a reasonable maximum length.
* Not contain executable content.

## Email

The email must:

* Be a valid email address.
* Be normalized before storage.
* Be unique where required.
* Not expose whether another account exists during password recovery.

## Avatar

Uploaded avatars must:

* Be validated by MIME type and file content.
* Have a configured maximum file size.
* Use generated storage names.
* Not preserve arbitrary executable extensions.
* Be stored outside publicly writable executable directories.

---

# Authorization

The user can modify only their own account through:

```text
/api/users/me
/api/users/me/profile
/api/users/me/avatar
/api/users/me/preferences
```

Administrative changes to other users belong to:

```text
/api/admin/users
```

and require the appropriate administrative role.

---

# Standard Errors

## 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

## 403 Forbidden

```json
{
    "success": false,
    "message": "You do not have permission to perform this action."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "User not found."
}
```

## 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

---

# Related Routes

```text
GET    /api/users/me
PUT    /api/users/me
PATCH  /api/users/me
DELETE /api/users/me

GET    /api/users/me/profile
PUT    /api/users/me/profile
PATCH  /api/users/me/profile

POST   /api/users/me/avatar
DELETE /api/users/me/avatar

GET    /api/users/me/preferences
PUT    /api/users/me/preferences

GET    /api/users/{id}
