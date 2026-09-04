# MASAR API — Administration

## Overview

The Administration API contains endpoints used by authorized administrators to manage and monitor the MASAR platform.

Administrative operations may include:

* User management.
* Company management.
* Training management.
* Application management.
* Certificate management.
* Appeals.
* Notifications.
* Files.
* Payments.
* Audit logs.
* System/reference data.
* Administrative statistics.

Base URL:

```text
/api/admin
```

All endpoints under this section require authentication and explicit administrative authorization.

---

# Authentication

Every administrative request requires:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

The API must identify the authenticated administrator from the access token.

The client must never be allowed to choose the administrator identity.

---

# Authorization

Being authenticated is not sufficient for administrative access.

The request must satisfy:

```text
authenticated
+
active user
+
administrator role
+
required permission
```

Administrative authorization should be based on the project's role definitions:

```text
shared/enums/user_roles.php
```

Where granular permissions are implemented, individual endpoints should check the required permission instead of relying only on the administrator role.

---

# Administrative Security Rule

All administrative endpoints must use server-side authorization.

Never rely on:

```text
hidden frontend buttons
frontend roles
request parameters
client-side permissions
```

A user must not gain administrative access by manually calling:

```http
POST /api/admin/...
```

---

# 1. Dashboard Statistics

Returns high-level platform statistics.

### Endpoint

```http
GET /api/admin/dashboard
```

### Response

```json
{
    "success": true,
    "data": {
        "users": {
            "total": 1200,
            "active": 1100,
            "pending": 50,
            "blocked": 50
        },
        "students": {
            "total": 900
        },
        "companies": {
            "total": 120
        },
        "trainings": {
            "total": 250,
            "published": 180,
            "closed": 50,
            "expired": 20
        },
        "applications": {
            "total": 1500,
            "pending": 300,
            "accepted": 700,
            "rejected": 400,
            "withdrawn": 100
        },
        "certificates": {
            "total": 600,
            "issued": 550,
            "revoked": 50
        }
    }
}
```

The dashboard should use aggregate queries and must not load all records into application memory.

---

# 2. List Users

Returns users for administrative management.

### Endpoint

```http
GET /api/admin/users
```

### Query Parameters

```text
q
role
status
page
per_page
```

### Example

```http
GET /api/admin/users?q=ahmed&role=student&status=active&page=1&per_page=20
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 25,
            "name": "Ahmed Mohamed",
            "email": "ahmed@example.com",
            "role": "student",
            "status": "active",
            "created_at": "2026-08-01 12:00:00"
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

Sensitive fields must not be returned.

---

# 3. Get User

Returns administrative details for a user.

### Endpoint

```http
GET /api/admin/users/{userId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 25,
        "name": "Ahmed Mohamed",
        "email": "ahmed@example.com",
        "role": "student",
        "status": "active",
        "created_at": "2026-08-01 12:00:00",
        "updated_at": "2026-08-05 15:00:00"
    }
}
```

Passwords, tokens, password hashes, and other authentication secrets must never be returned.

---

# 4. Update User Status

Changes a user's administrative status.

### Endpoint

```http
PATCH /api/admin/users/{userId}/status
```

### Request

```json
{
    "status": "blocked"
}
```

### Response

```json
{
    "success": true,
    "message": "User status updated successfully."
}
```

Allowed values must come from:

```text
shared/enums/user_statuses.php
```

The API must reject invalid status values.

---

# 5. Change User Role

Changes a user's role.

### Endpoint

```http
PATCH /api/admin/users/{userId}/role
```

### Request

```json
{
    "role": "company"
}
```

### Response

```json
{
    "success": true,
    "message": "User role updated successfully."
}
```

Role values must come from:

```text
shared/enums/user_roles.php
```

Changing a user's role is a highly privileged operation and should be audit logged.

---

# Administrative Role Protection

An administrator must not be able to accidentally or maliciously remove the final active administrator without an appropriate safety rule.

Recommended protection:

```text
At least one active administrator must remain.
```

If an operation would violate this rule, reject it.

---

# 6. List Companies

### Endpoint

```http
GET /api/admin/companies
```

### Query Parameters

```text
q
status
page
per_page
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 4,
            "name": "Example Company",
            "status": "active",
            "created_at": "2026-07-20 10:00:00"
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

# 7. Update Company Status

### Endpoint

```http
PATCH /api/admin/companies/{companyId}/status
```

### Request

```json
{
    "status": "active"
}
```

Allowed values must come from:

```text
shared/enums/company_statuses.php
```

The operation should generate an audit log.

---

# 8. List Trainings

### Endpoint

```http
GET /api/admin/trainings
```

### Query Parameters

```text
q
status
company_id
training_type
training_mode
page
per_page
```

Administrators may view training records regardless of their normal public visibility, subject to permission rules.

---

# 9. Update Training Status

### Endpoint

```http
PATCH /api/admin/trainings/{trainingId}/status
```

### Request

```json
{
    "status": "closed"
}
```

Allowed values must come from:

```text
shared/enums/training_statuses.php
```

The API should validate whether the requested status transition is legally allowed.

---

# Status Transition Validation

Administrative access does not automatically mean every status transition is valid.

For example:

```text
draft
  ↓
published
  ↓
closed
```

and:

```text
published
  ↓
expired
```

The application should define valid transitions in the business rules.

Invalid transitions should return:

```json
{
    "success": false,
    "message": "Invalid training status transition."
}
```

---

# 10. List Applications

### Endpoint

```http
GET /api/admin/applications
```

### Query Parameters

```text
q
status
training_id
student_id
company_id
page
per_page
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "student_id": 25,
            "training_id": 10,
            "company_id": 4,
            "status": "pending",
            "created_at": "2026-08-07 12:00:00"
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

Application statuses must use:

```text
shared/enums/application_statuses.php
```

---

# 11. Update Application Status

### Endpoint

```http
PATCH /api/admin/applications/{applicationId}/status
```

### Request

```json
{
    "status": "rejected",
    "rejection_reason_id": 3
}
```

If the selected status requires a rejection reason, the API must enforce it.

Rejection reasons are defined through:

```text
shared/enums/rejection_reasons.php
```

and/or:

```text
database/seeders/rejection_reasons_seeder.php
```

depending on the final implementation.

---

# 12. List Certificates

### Endpoint

```http
GET /api/admin/certificates
```

### Query Parameters

```text
q
status
student_id
training_id
page
per_page
```

Certificate statuses must use:

```text
shared/enums/certificate_statuses.php
```

---

# 13. Update Certificate Status

### Endpoint

```http
PATCH /api/admin/certificates/{certificateId}/status
```

### Request

```json
{
    "status": "revoked"
}
```

The operation must validate whether revocation is permitted.

Certificate status changes should be audit logged.

---

# 14. Certificate Appeals

Administrators may review certificate appeals.

### List Appeals

```http
GET /api/admin/certificate-appeals
```

### Query Parameters

```text
status
student_id
certificate_id
page
per_page
```

### Update Appeal Status

```http
PATCH /api/admin/certificate-appeals/{appealId}/status
```

### Request

```json
{
    "status": "approved"
}
```

Allowed values must come from:

```text
shared/enums/appeal_statuses.php
```

---

# 15. List Notifications

Administrators may have access to system-level notification management where required.

### Endpoint

```http
GET /api/admin/notifications
```

Possible filters:

```text
type
user_id
read
page
per_page
```

Administrative access to individual user notifications should be restricted because notifications may contain private information.

---

# 16. Send System Notification

If system notifications are supported, administrators may send notifications to selected users.

### Endpoint

```http
POST /api/admin/notifications
```

### Request

```json
{
    "user_ids": [10, 20, 30],
    "type": "system",
    "title": "System Maintenance",
    "body": "The platform will undergo maintenance tonight."
}
```

The server must validate all recipients.

Administrators must not be allowed to impersonate arbitrary domain events by selecting types such as:

```text
application_accepted
certificate_issued
payment_completed
```

unless the business logic explicitly supports that operation.

---

# 17. File Management

Administrators may manage files according to permissions.

### List Files

```http
GET /api/admin/files
```

### Delete File

```http
DELETE /api/admin/files/{fileId}
```

File deletion must consider domain references.

For example, an administrator must not blindly delete a certificate file that is still required by a valid certificate.

See:

```text
docs/api/files.md
```

for the general file API rules.

---

# 18. Payments

If payment management is enabled, administrators may inspect payment records.

### Endpoint

```http
GET /api/admin/payments
```

### Query Parameters

```text
status
payment_type
user_id
page
per_page
```

Payment status values must use:

```text
shared/enums/payment_statuses.php
```

Payment type values must use:

```text
shared/enums/payment_types.php
```

---

# Payment Security

Payment information is sensitive.

The API must not expose:

```text
full card numbers
CVV
authentication secrets
payment provider private keys
```

Only the minimum information required for administrative operations should be returned.

---

# 19. Audit Logs

Audit logs provide a record of important administrative and security-sensitive actions.

### Endpoint

```http
GET /api/admin/audit-logs
```

### Query Parameters

```text
user_id
action
entity_type
entity_id
date_from
date_to
page
per_page
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 500,
            "user_id": 1,
            "action": "user.status_changed",
            "entity_type": "user",
            "entity_id": 25,
            "created_at": "2026-08-07 21:00:00"
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

Audit logs should normally be immutable through the public API.

---

# Audited Administrative Actions

Important actions include:

```text
user.role_changed
user.status_changed

company.status_changed

training.status_changed

application.status_changed

certificate.status_changed

certificate_appeal.status_changed

file.deleted
file.accessed

notification.sent

payment.updated
```

The final list should match the application's actual audit policy.

---

# Audit Log Data

A useful audit record may contain:

```text
id
user_id
action
entity_type
entity_id
old_values
new_values
ip_address
user_agent
created_at
```

Sensitive data should not be stored unnecessarily in audit records.

---

# 20. Reference Data

Administrators may manage reference data if the platform requires it.

Examples:

```text
Universities
Faculties
Degrees
Specializations
Skills
Rejection reasons
```

However, reference-data CRUD should be implemented only where the product requirements require administrative editing.

Seeded reference data may remain read-only after deployment.

---

# 21. Universities

### List

```http
GET /api/admin/universities
```

### Create

```http
POST /api/admin/universities
```

### Update

```http
PUT /api/admin/universities/{universityId}
```

### Delete

```http
DELETE /api/admin/universities/{universityId}
```

Deletion must be blocked if existing students, faculties, or other records still reference the university, unless a safe migration strategy exists.

---

# 22. Faculties

### List

```http
GET /api/admin/faculties
```

### Create

```http
POST /api/admin/faculties
```

### Update

```http
PUT /api/admin/faculties/{facultyId}
```

### Delete

```http
DELETE /api/admin/faculties/{facultyId}
```

A faculty belongs to a university.

The API must validate:

```text
faculty.university_id
```

before creation or update.

---

# 23. Degrees

### List

```http
GET /api/admin/degrees
```

### Create

```http
POST /api/admin/degrees
```

### Update

```http
PUT /api/admin/degrees/{degreeId}
```

### Delete

```http
DELETE /api/admin/degrees/{degreeId}
```

Degrees should maintain their relationship with the appropriate faculty.

---

# 24. Specializations

### List

```http
GET /api/admin/specializations
```

### Create

```http
POST /api/admin/specializations
```

### Update

```http
PUT /api/admin/specializations/{specializationId}
```

### Delete

```http
DELETE /api/admin/specializations/{specializationId}
```

Deletion must consider relationships with:

```text
students
companies
trainings
```

---

# 25. Skills

### List

```http
GET /api/admin/skills
```

### Create

```http
POST /api/admin/skills
```

### Update

```http
PUT /api/admin/skills/{skillId}
```

### Delete

```http
DELETE /api/admin/skills/{skillId}
```

Deletion must consider:

```text
student_skills
training_skills
```

---

# Bulk Operations

Bulk administrative operations should be introduced carefully.

Possible examples:

```text
Bulk user status update
Bulk training closure
Bulk notification delivery
```

Example:

```http
POST /api/admin/users/bulk-status
```

```json
{
    "user_ids": [10, 20, 30],
    "status": "blocked"
}
```

Bulk operations must:

* Validate every ID.
* Enforce authorization.
* Use transactions where appropriate.
* Record audit events.
* Avoid partial silent failures.

---

# Administrative Pagination

All large administrative collections must be paginated.

Example:

```text
page=1
per_page=20
```

A maximum `per_page` must be enforced.

Administrative endpoints must never return an unbounded table by default.

---

# Administrative Search

Administrative search may reuse the Search service but must not bypass authorization.

For example:

```text
GET /api/admin/users?q=ahmed
```

must still use parameterized queries and controlled filtering.

---

# Administrative Actions and Transactions

Operations that modify multiple related records should use database transactions.

Example:

```text
Accept application
      ↓
Update application
      ↓
Create training session
      ↓
Create notification
      ↓
Commit
```

If one required operation fails, the transaction should roll back where appropriate.

---

# Concurrent Administrative Actions

The API should account for concurrent modifications.

Example:

```text
Admin A ── closes training
Admin B ── closes same training
```

The application should ensure that the resulting state remains valid and duplicate side effects are avoided.

---

# Soft Deletion

Where historical relationships matter, prefer soft deletion over immediate physical deletion.

This may apply to:

```text
Users
Companies
Trainings
Files
Reference data
```

The exact policy should follow the database design.

---

# Destructive Operations

Destructive operations should require:

```text
explicit endpoint
+
permission
+
validation
+
audit log
```

Do not implement destructive actions through ambiguous generic update endpoints.

---

# Error Handling

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
    "message": "Administrator access is required."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Resource not found."
}
```

## 409 Conflict

```json
{
    "success": false,
    "message": "The requested operation conflicts with the current resource state."
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

# Administrative Rate Limiting

Administrative endpoints should also be rate limited.

Higher limits may be configured for trusted administrative operations, but rate limiting should not be completely disabled.

Particularly sensitive endpoints should have stricter protection:

```text
Role changes
Password/reset operations
Bulk operations
Notification broadcasting
File downloads
Payment operations
```

---

# Administrative Logging

Every security-sensitive administrative action should be traceable.

Recommended minimum:

```text
administrator_id
action
resource
resource_id
timestamp
result
```

For sensitive changes:

```text
old_value
new_value
```

may also be recorded when safe.

---

# Administrative API Routes

Recommended route structure:

```text
/api/admin
│
├── dashboard
│
├── users
│   ├── GET /
│   ├── GET /{id}
│   ├── PATCH /{id}/status
│   └── PATCH /{id}/role
│
├── companies
│   ├── GET /
│   └── PATCH /{id}/status
│
├── trainings
│   ├── GET /
│   └── PATCH /{id}/status
│
├── applications
│   ├── GET /
│   └── PATCH /{id}/status
│
├── certificates
│   ├── GET /
│   └── PATCH /{id}/status
│
├── certificate-appeals
│   ├── GET /
│   └── PATCH /{id}/status
│
├── notifications
│   ├── GET /
│   └── POST /
│
├── files
│   ├── GET /
│   └── DELETE /{id}
│
├── payments
│   └── GET /
│
├── audit-logs
│   └── GET /
│
├── universities
├── faculties
├── degrees
├── specializations
└── skills
```

---

# Related Database Tables

Administrative operations may interact with:

```text
users
students
companies
universities
faculties
degrees
specializations
skills
student_skills
company_specializations
training_listings
training_specializations
training_skills
training_applications
training_sessions
certificates
certificate_appeals
conversations
messages
notifications
files
payments
audit_logs
```

---

# Related Enums

Administrative validation may depend on:

```text
shared/enums/user_roles.php
shared/enums/user_statuses.php
shared/enums/company_statuses.php
shared/enums/training_statuses.php
shared/enums/training_types.php
shared/enums/training_modes.php
shared/enums/application_statuses.php
shared/enums/rejection_reasons.php
shared/enums/training_session_statuses.php
shared/enums/certificate_statuses.php
shared/enums/appeal_statuses.php
shared/enums/payment_types.php
shared/enums/payment_statuses.php
shared/enums/notification_types.php
```

---

# Related Database Migrations

Important administrative data is stored through migrations including:

```text
001_create_users_table.sql
003_create_companies_table.sql
011_create_training_listings_table.sql
014_create_training_applications_table.sql
016_create_certificates_table.sql
017_create_certificate_appeals_table.sql
020_create_notifications_table.sql
021_create_files_table.sql
022_create_payments_table.sql
023_create_audit_logs_table.sql
```

---

# Business Flow

```text
Administrator
      │
      ▼
Authentication
      │
      ▼
Role / Permission Check
      │
      ▼
Admin Route
      │
      ▼
Admin Controller
      │
      ▼
Admin Service
      │
      ├───────────────┐
      ▼               ▼
Domain Service     Audit Log
      │
      ▼
Database
      │
      ▼
Response
```

---

# Core Administrative Principle

The Administration API is a privileged layer over the normal MASAR domain.

It may provide broader visibility and management capabilities, but it must **not bypass domain integrity, authorization, validation, transactions, or audit requirements**.

Every administrative mutation should be treated as a controlled business operation rather than a direct database edit.
