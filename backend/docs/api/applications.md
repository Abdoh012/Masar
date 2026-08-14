# MASAR API — Applications

## Overview

The Applications API manages student applications to training opportunities, including submission, viewing, withdrawal, acceptance, rejection, and application status transitions.

Base URL:

```text
/api/applications
```

All endpoints require authentication.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. List My Applications

Returns applications belonging to the authenticated student.

### Endpoint

```http
GET /api/applications
```

### Authorization

Required role:

```text
student
```

### Query Parameters

```text
page
per_page
status
training_id
company_id
```

### Example

```http
GET /api/applications?page=1&per_page=20&status=pending
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "training": {
                "id": 10,
                "title": "Backend PHP Internship"
            },
            "company": {
                "id": 4,
                "name": "Example Company"
            },
            "status": "pending",
            "applied_at": "2026-08-01 12:00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 1,
        "last_page": 1
    }
}
```

---

# 2. Get Application

Returns a single application.

### Endpoint

```http
GET /api/applications/{applicationId}
```

### Authorization

The request is allowed only when the authenticated user is:

* The student who submitted the application.
* The company that owns the related training.
* An authorized administrator.

### Response

```json
{
    "success": true,
    "data": {
        "id": 15,
        "training": {
            "id": 10,
            "title": "Backend PHP Internship"
        },
        "student": {
            "id": 25,
            "name": "Ahmed Mohamed"
        },
        "company": {
            "id": 4,
            "name": "Example Company"
        },
        "status": "pending",
        "cover_letter": "I am interested in this training opportunity.",
        "applied_at": "2026-08-01 12:00:00"
    }
}
```

---

# 3. Create Application

Creates an application for a training opportunity.

### Endpoint

```http
POST /api/applications
```

### Authorization

Required role:

```text
student
```

### Request

```json
{
    "training_id": 10,
    "cover_letter": "I am interested in this training opportunity because it matches my backend development skills."
}
```

### Response

```json
{
    "success": true,
    "message": "Application submitted successfully.",
    "data": {
        "id": 15,
        "training_id": 10,
        "status": "pending",
        "applied_at": "2026-08-01 12:00:00"
    }
}
```

---

# 4. Update Application

An application may only be updated while it is in an editable state.

### Endpoint

```http
PUT /api/applications/{applicationId}
```

or:

```http
PATCH /api/applications/{applicationId}
```

### Authorization

Only the student who owns the application may update editable fields.

### Request

```json
{
    "cover_letter": "Updated application message."
}
```

### Response

```json
{
    "success": true,
    "message": "Application updated successfully.",
    "data": {
        "id": 15,
        "cover_letter": "Updated application message."
    }
}
```

Once an application reaches a non-editable status, modifications must be rejected.

---

# 5. Withdraw Application

Allows the student to withdraw an eligible application.

### Endpoint

```http
POST /api/applications/{applicationId}/withdraw
```

### Authorization

Required role:

```text
student
```

### Response

```json
{
    "success": true,
    "message": "Application withdrawn successfully.",
    "data": {
        "id": 15,
        "status": "withdrawn"
    }
}
```

The system must verify that the application belongs to the authenticated student.

---

# 6. List Training Applications

Returns applications submitted to a specific training.

### Endpoint

```http
GET /api/applications/training/{trainingId}
```

### Authorization

Allowed for:

* Training owner.
* Authorized administrator.

### Query Parameters

```text
page
per_page
status
```

### Example

```http
GET /api/applications/training/10?status=pending
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "student": {
                "id": 25,
                "name": "Ahmed Mohamed"
            },
            "status": "pending",
            "applied_at": "2026-08-01 12:00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 1,
        "last_page": 1
    }
}
```

---

# 7. Accept Application

Accepts an application.

### Endpoint

```http
POST /api/applications/{applicationId}/accept
```

### Authorization

Required:

```text
training owner
```

or:

```text
administrator
```

### Response

```json
{
    "success": true,
    "message": "Application accepted successfully.",
    "data": {
        "id": 15,
        "status": "accepted"
    }
}
```

Acceptance should be performed inside a transaction when it causes related records to be created.

---

# 8. Reject Application

Rejects an application.

### Endpoint

```http
POST /api/applications/{applicationId}/reject
```

### Authorization

Required:

```text
training owner
```

or:

```text
administrator
```

### Request

```json
{
    "reason_id": 3,
    "note": "The applicant does not meet the required criteria."
}
```

### Response

```json
{
    "success": true,
    "message": "Application rejected successfully.",
    "data": {
        "id": 15,
        "status": "rejected",
        "rejection_reason_id": 3
    }
}
```

The rejection reason must reference a valid configured rejection reason.

---

# 9. Application Status

Application status is controlled by the application's status enum.

Typical states include:

```text
pending
accepted
rejected
withdrawn
```

The exact allowed values must come from:

```text
shared/enums/application_statuses.php
```

---

# 10. Status Transitions

The API must enforce valid state transitions.

Typical flow:

```text
pending
   ├── accepted
   ├── rejected
   └── withdrawn
```

Invalid transitions must be rejected.

For example:

```text
rejected → accepted
accepted → pending
withdrawn → accepted
```

must not be allowed unless explicitly supported by the business rules.

---

# 11. Duplicate Applications

A student must not be able to create multiple active applications for the same training.

The database should enforce uniqueness at the appropriate level.

Recommended logical constraint:

```text
student_id + training_id
```

If historical re-application is required later, the business rule should explicitly define when another application is permitted.

---

# 12. Eligibility Validation

Before creating an application, the server should validate:

```text
Student exists
    +
Student account is active
    +
Training exists
    +
Training is published
    +
Application deadline is valid
    +
Capacity rules are satisfied
    +
Student has no conflicting application
    +
Required eligibility rules are satisfied
```

Client-side validation must never replace these checks.

---

# 13. Application Deadline

Applications cannot normally be submitted after the configured deadline.

Example:

```text
application_deadline = 2026-08-30
```

The API must compare the deadline against server-side time.

The client must not be trusted to provide the current timestamp.

---

# 14. Capacity Validation

If the training has a maximum capacity, acceptance must verify that capacity has not already been reached.

The check must be performed server-side and safely under concurrent requests.

Example:

```text
capacity = 10
accepted applications = 10
```

A new application may remain pending, but another application must not be accepted if doing so exceeds the configured capacity.

---

# 15. Acceptance Side Effects

Accepting an application may trigger creation of a training session.

Typical flow:

```text
Application
    ↓
accepted
    ↓
Training Session
    ↓
scheduled / active
```

The operation should use a database transaction when multiple records are changed.

---

# 16. Notifications

Application state changes should generate notifications where configured.

Examples:

```text
Student applies
        ↓
Company notification

Company accepts
        ↓
Student notification

Company rejects
        ↓
Student notification
```

Notification creation should not expose sensitive information.

---

# 17. Audit Logging

Important application operations should be recorded in the audit log.

Examples:

```text
application.created
application.updated
application.withdrawn
application.accepted
application.rejected
```

Audit records should include the relevant actor and timestamp.

---

# 18. Ownership Rules

## Student

A student may:

```text
Create own applications
View own applications
Update editable own applications
Withdraw own applications
```

A student must never be able to:

```text
Accept an application
Reject another student's application
Modify another student's application
View another student's private application data
```

---

## Company

A company may:

```text
View applications for its own trainings
Accept applications for its own trainings
Reject applications for its own trainings
```

A company must never be able to access applications belonging to another company.

---

## Administrator

Authorized administrators may manage applications according to the admin permission policy.

Administrative actions should be audit logged.

---

# 19. Filtering

Applications may be filtered using:

```text
status
training_id
company_id
student_id
```

Student-facing endpoints must ignore or reject unauthorized attempts to use another student's ID for data access.

---

# 20. Pagination

List endpoints support:

```text
page
per_page
```

Example:

```http
GET /api/applications?page=2&per_page=20
```

Response metadata:

```json
{
    "meta": {
        "current_page": 2,
        "per_page": 20,
        "total": 45,
        "last_page": 3
    }
}
```

The API should enforce a maximum `per_page`.

---

# 21. Validation Errors

### 422 Unprocessable Entity

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "training_id": [
            "The selected training is invalid."
        ]
    }
}
```

---

# 22. Conflict Errors

### 409 Conflict

Example: duplicate application.

```json
{
    "success": false,
    "message": "You have already applied for this training."
}
```

Another example:

```json
{
    "success": false,
    "message": "This application cannot be accepted because the training capacity has been reached."
}
```

---

# 23. Unauthorized

### 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

---

# 24. Forbidden

### 403 Forbidden

```json
{
    "success": false,
    "message": "You do not have permission to perform this action."
}
```

---

# 25. Not Found

### 404 Not Found

```json
{
    "success": false,
    "message": "Application not found."
}
```

---

# Related Endpoints

```text
GET    /api/applications
GET    /api/applications/{applicationId}

POST   /api/applications
PUT    /api/applications/{applicationId}
PATCH  /api/applications/{applicationId}

POST   /api/applications/{applicationId}/withdraw

GET    /api/applications/training/{trainingId}

POST   /api/applications/{applicationId}/accept
POST   /api/applications/{applicationId}/reject
```

---

# Related Database Tables

The Applications API primarily interacts with:

```text
users
students
companies
training_listings
training_applications
training_sessions
notifications
audit_logs
```

The primary application record is stored in:

```text
database/migrations/014_create_training_applications_table.sql
```

---

# Related Enums

Application states are defined by:

```text
shared/enums/application_statuses.php
```

Rejection reasons are defined by:

```text
shared/enums/rejection_reasons.php
```

---

# Business Rule Summary

```text
Student
   │
   │ applies
   ▼
Training
   │
   ▼
Application: pending
   │
   ├───────────────┐
   │               │
   ▼               ▼
accepted        rejected
   │
   ▼
Training Session
   │
   ▼
Certificate eligibility
```

All transitions must be validated server-side and recorded when audit logging is required.
