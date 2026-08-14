# MASAR API — Certificates

## Overview

The Certificates API manages training certificates issued to students after successful completion of training sessions.

It covers:

* Certificate creation.
* Certificate issuance.
* Certificate viewing.
* Certificate verification.
* Certificate downloads.
* Certificate appeals.
* Certificate status management.

Base URL:

```text
/api/certificates
```

All endpoints require authentication unless explicitly marked as public.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. List My Certificates

Returns certificates belonging to the authenticated student.

### Endpoint

```http
GET /api/certificates
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
```

### Example

```http
GET /api/certificates?page=1&per_page=20&status=issued
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 50,
            "certificate_number": "MASAR-2026-000050",
            "training": {
                "id": 10,
                "title": "Backend PHP Internship"
            },
            "company": {
                "id": 4,
                "name": "Example Company"
            },
            "status": "issued",
            "issued_at": "2026-11-12"
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

# 2. Get Certificate

Returns detailed information about a certificate.

### Endpoint

```http
GET /api/certificates/{certificateId}
```

### Authorization

Allowed for:

* Certificate owner.
* Company that issued the certificate.
* Authorized administrator.

### Response

```json
{
    "success": true,
    "data": {
        "id": 50,
        "certificate_number": "MASAR-2026-000050",
        "student": {
            "id": 25,
            "name": "Ahmed Mohamed"
        },
        "company": {
            "id": 4,
            "name": "Example Company"
        },
        "training": {
            "id": 10,
            "title": "Backend PHP Internship"
        },
        "status": "issued",
        "issued_at": "2026-11-12"
    }
}
```

Private student information must not be exposed to unauthorized users.

---

# 3. Create Certificate

Creates a certificate record for an eligible completed training session.

### Endpoint

```http
POST /api/certificates
```

### Authorization

Allowed for:

* Authorized company users.
* Authorized administrators.

### Request

```json
{
    "training_session_id": 20
}
```

### Response

```json
{
    "success": true,
    "message": "Certificate created successfully.",
    "data": {
        "id": 50,
        "certificate_number": "MASAR-2026-000050",
        "status": "pending"
    }
}
```

The API must verify that the training session is eligible before creating the certificate.

---

# 4. Issue Certificate

Changes an eligible certificate to the issued state.

### Endpoint

```http
POST /api/certificates/{certificateId}/issue
```

### Authorization

Allowed for the authorized certificate issuer or administrator.

### Response

```json
{
    "success": true,
    "message": "Certificate issued successfully.",
    "data": {
        "id": 50,
        "certificate_number": "MASAR-2026-000050",
        "status": "issued",
        "issued_at": "2026-11-12"
    }
}
```

Issuance must be performed server-side.

---

# 5. Revoke Certificate

Revokes an issued certificate when permitted by the business rules.

### Endpoint

```http
POST /api/certificates/{certificateId}/revoke
```

### Authorization

Authorized issuer or administrator.

### Request

```json
{
    "reason": "Certificate issued in error."
}
```

### Response

```json
{
    "success": true,
    "message": "Certificate revoked successfully.",
    "data": {
        "id": 50,
        "status": "revoked"
    }
}
```

Certificate revocation should be audit logged.

---

# 6. Download Certificate

Downloads the generated certificate document.

### Endpoint

```http
GET /api/certificates/{certificateId}/download
```

### Authorization

Allowed for:

* Certificate owner.
* Issuing company where appropriate.
* Authorized administrator.

### Response

The endpoint returns the certificate file with the appropriate content type.

Example:

```http
Content-Type: application/pdf
Content-Disposition: attachment; filename="MASAR-2026-000050.pdf"
```

The actual storage path must not be exposed directly to the client.

---

# 7. Verify Certificate

Provides public certificate verification.

### Endpoint

```http
GET /api/certificates/verify/{certificateNumber}
```

### Authentication

Not required.

### Example

```http
GET /api/certificates/verify/MASAR-2026-000050
```

### Response

```json
{
    "success": true,
    "data": {
        "valid": true,
        "certificate_number": "MASAR-2026-000050",
        "student_name": "Ahmed Mohamed",
        "training_title": "Backend PHP Internship",
        "company_name": "Example Company",
        "issued_at": "2026-11-12",
        "status": "issued"
    }
}
```

Only information intended for public verification should be returned.

Sensitive student information must never be included.

---

# 8. Get Certificate Verification Status

Returns whether a certificate is currently valid.

### Endpoint

```http
GET /api/certificates/{certificateId}/verification
```

### Response

```json
{
    "success": true,
    "data": {
        "certificate_number": "MASAR-2026-000050",
        "valid": true,
        "status": "issued"
    }
}
```

---

# 9. List Company Certificates

Returns certificates issued by the authenticated company.

### Endpoint

```http
GET /api/certificates/company
```

### Authorization

Required role:

```text
company
```

### Query Parameters

```text
page
per_page
status
student_id
training_id
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 50,
            "certificate_number": "MASAR-2026-000050",
            "student": {
                "id": 25,
                "name": "Ahmed Mohamed"
            },
            "training": {
                "id": 10,
                "title": "Backend PHP Internship"
            },
            "status": "issued",
            "issued_at": "2026-11-12"
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

The company may access only certificates that it issued or is authorized to manage.

---

# 10. Get Student Certificates

Returns certificates for the authenticated student.

### Endpoint

```http
GET /api/certificates/student
```

### Authorization

Required role:

```text
student
```

This endpoint is equivalent to the student's certificate collection endpoint and may be exposed as an alias if desired.

---

# 11. Create Certificate Appeal

Allows a student to appeal a certificate-related decision.

### Endpoint

```http
POST /api/certificates/{certificateId}/appeals
```

### Authorization

The certificate owner may submit an appeal.

### Request

```json
{
    "reason": "The certificate information is incorrect.",
    "details": "The training end date displayed on the certificate is incorrect."
}
```

### Response

```json
{
    "success": true,
    "message": "Certificate appeal submitted successfully.",
    "data": {
        "id": 12,
        "certificate_id": 50,
        "status": "pending"
    }
}
```

---

# 12. List Certificate Appeals

Returns appeals associated with a certificate.

### Endpoint

```http
GET /api/certificates/{certificateId}/appeals
```

### Authorization

Allowed for the certificate owner, issuing company where appropriate, and authorized administrators.

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 12,
            "certificate_id": 50,
            "status": "pending",
            "reason": "The certificate information is incorrect.",
            "created_at": "2026-11-13 10:00:00"
        }
    ]
}
```

---

# 13. Get Certificate Appeal

Returns a specific certificate appeal.

### Endpoint

```http
GET /api/certificates/{certificateId}/appeals/{appealId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 12,
        "certificate_id": 50,
        "status": "pending",
        "reason": "The certificate information is incorrect.",
        "details": "The training end date displayed on the certificate is incorrect."
    }
}
```

The API must verify that the appeal belongs to the requested certificate.

---

# 14. Resolve Certificate Appeal

Allows an authorized administrator or authorized issuer to resolve an appeal.

### Endpoint

```http
POST /api/certificates/{certificateId}/appeals/{appealId}/resolve
```

### Request

```json
{
    "decision": "approved",
    "note": "Certificate information corrected."
}
```

### Response

```json
{
    "success": true,
    "message": "Certificate appeal resolved successfully.",
    "data": {
        "id": 12,
        "status": "approved"
    }
}
```

---

# 15. Reject Certificate Appeal

Rejects a certificate appeal.

### Endpoint

```http
POST /api/certificates/{certificateId}/appeals/{appealId}/reject
```

### Request

```json
{
    "note": "The certificate information was verified and is correct."
}
```

### Response

```json
{
    "success": true,
    "message": "Certificate appeal rejected successfully.",
    "data": {
        "id": 12,
        "status": "rejected"
    }
}
```

---

# Certificate Status

Certificate status is controlled by:

```text
shared/enums/certificate_statuses.php
```

Typical states may include:

```text
pending
issued
revoked
```

The exact values must match the application's enum.

---

# Certificate Appeal Status

Appeal status is controlled by:

```text
shared/enums/appeal_statuses.php
```

Typical lifecycle:

```text
pending
   ├── approved
   └── rejected
```

The exact values must come from the configured enum.

---

# Certificate Eligibility

A certificate should be issued only after the required training conditions have been satisfied.

Typical eligibility chain:

```text
Training
   ↓
Application accepted
   ↓
Training session created
   ↓
Training session completed
   ↓
Eligibility verified
   ↓
Certificate created
   ↓
Certificate issued
```

The API must not allow a client to bypass these conditions by directly submitting a forged status.

---

# Certificate Number

Each certificate must have a unique certificate number.

Example:

```text
MASAR-2026-000050
```

The certificate number should:

* Be generated server-side.
* Be unique.
* Never be supplied as the authoritative identifier by the client.
* Remain associated with the certificate record.
* Be suitable for public verification.

The database should enforce uniqueness.

---

# Certificate File

Generated certificate files should be stored using the application's file-storage system.

Recommended logical flow:

```text
Certificate
     ↓
Generate PDF
     ↓
storage/certificates/
     ↓
files record
     ↓
download endpoint
```

The physical storage path should not be exposed directly.

Generated filenames should not depend solely on user-provided input.

---

# Public Verification Security

The public verification endpoint must not expose:

```text
student email
student phone
password
authentication tokens
private files
internal database IDs
audit information
```

It should expose only information necessary to confirm certificate authenticity.

---

# Duplicate Certificates

The system should prevent duplicate certificates for the same eligible training session unless re-issuance is explicitly supported.

Recommended logical relationship:

```text
training_session_id
        ↓
certificate
```

A unique constraint should normally prevent accidental duplicate certificate records.

---

# Certificate Revocation

A revoked certificate must no longer be considered valid by the public verification endpoint.

Example:

```json
{
    "success": true,
    "data": {
        "valid": false,
        "certificate_number": "MASAR-2026-000050",
        "status": "revoked"
    }
}
```

Revocation must not silently delete the certificate record.

Historical certificate records should remain available for audit purposes.

---

# Certificate Appeals Rules

A student may submit an appeal when allowed by the business rules.

The API should prevent:

* Duplicate unresolved appeals for the same issue.
* Appeals for certificates the student does not own.
* Unauthorized modification of another user's appeal.
* Resolving an already resolved appeal without explicit administrative permission.

All appeal decisions should be audit logged.

---

# Notifications

Certificate-related events may generate notifications.

Examples:

```text
certificate.created
certificate.issued
certificate.revoked
certificate.appeal_created
certificate.appeal_approved
certificate.appeal_rejected
```

The notification system should be used instead of directly exposing internal processing details.

---

# Audit Logging

The following operations should be recorded when audit logging is enabled:

```text
certificate.created
certificate.issued
certificate.downloaded
certificate.revoked
certificate.appeal_created
certificate.appeal_approved
certificate.appeal_rejected
```

Audit records should identify the actor, target resource, action, and timestamp.

---

# Authorization Rules

## Student

A student may:

```text
View own certificates
Download own certificates
Verify certificates publicly
Create eligible certificate appeals
View own certificate appeals
```

A student must not:

```text
Issue certificates
Revoke certificates
Modify another student's certificates
Resolve appeals
```

---

## Company

An authorized company may:

```text
View certificates issued by the company
Issue eligible certificates
Perform authorized certificate operations
View relevant appeals
```

A company must not access certificates issued by another company.

---

## Administrator

Authorized administrators may manage certificates and appeals according to administrative permissions.

Administrative actions must be audit logged.

---

# Pagination

List endpoints support:

```text
page
per_page
```

Example:

```http
GET /api/certificates?page=1&per_page=20
```

Response:

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 35,
        "last_page": 2
    }
}
```

The API should enforce a maximum `per_page`.

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
    "message": "You do not have permission to access this certificate."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Certificate not found."
}
```

## 409 Conflict

```json
{
    "success": false,
    "message": "A certificate already exists for this training session."
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

# Related Database Tables

The Certificates API primarily interacts with:

```text
users
students
companies
training_listings
training_sessions
certificates
certificate_appeals
files
notifications
audit_logs
```

Primary certificate table:

```text
database/migrations/016_create_certificates_table.sql
```

Appeal table:

```text
database/migrations/017_create_certificate_appeals_table.sql
```

---

# Related Enums

Certificate statuses:

```text
shared/enums/certificate_statuses.php
```

Appeal statuses:

```text
shared/enums/appeal_statuses.php
```

---

# Related Storage

Generated certificates belong under:

```text
storage/certificates/
```

Temporary certificate generation files should be removed after successful processing.

---

# Related Routes

```text
GET    /api/certificates
GET    /api/certificates/{certificateId}

POST   /api/certificates
POST   /api/certificates/{certificateId}/issue
POST   /api/certificates/{certificateId}/revoke

GET    /api/certificates/{certificateId}/download

GET    /api/certificates/verify/{certificateNumber}
GET    /api/certificates/{certificateId}/verification

GET    /api/certificates/company
GET    /api/certificates/student

POST   /api/certificates/{certificateId}/appeals
GET    /api/certificates/{certificateId}/appeals
GET    /api/certificates/{certificateId}/appeals/{appealId}

POST   /api/certificates/{certificateId}/appeals/{appealId}/resolve
POST   /api/certificates/{certificateId}/appeals/{appealId}/reject
