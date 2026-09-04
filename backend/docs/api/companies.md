# MASAR API — Companies

## Overview

The Companies API manages company profiles, specializations, training opportunities, applications, training sessions, and company-issued certificates.

Base URL:

```text
/api/companies
```

All endpoints require authentication.

Company-management endpoints require the authenticated user to have the `company` role.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. Get Current Company

Returns the company profile associated with the authenticated user.

### Endpoint

```http
GET /api/companies/me
```

### Authorization

Required role:

```text
company
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 45,
        "user_id": 203,
        "company_name": "Test Company",
        "description": "Test company account for MASAR development.",
        "approval_status": "pending",
        "work_fields": [
            { "field_id": 1, "field_name": "Engineering" }
        ],
        "specializations": [
            { "id": 1, "name": "Software Engineering" }
        ]
    }
}
```

`work_fields` lists the company's work fields (legacy/current field-based classification). Each entry references the `study_fields` lookup table (`field_id` → `study_fields.id`); `study_fields` is the single source of truth for work field names shared with students.

`specializations` lists the company's industry specializations (new specialization-based industry classification). Each entry references the `specializations` lookup table (`id` → `specializations.id`). This is what training matching uses to match companies against a student's specialization.

---

# 2. Create Company

Creates a company profile for the authenticated user.

### Endpoint

```http
POST /api/companies
```

### Request

```json
{
    "company_name": "Test Company",
    "work_field_ids": [1, 2],
    "specialization_ids": [1, 4],
    "description": "Works across engineering and computer science."
}
```

`work_field_ids` (legacy/current field-based classification) are study field IDs from the `study_fields` lookup table (`1` = Engineering, `2` = Computer Science, `3` = Business, `4` = Medicine). The legacy `industry` name is also accepted and resolved against `study_fields`.

`specialization_ids` (new specialization-based industry classification) are specialization IDs from the `specializations` lookup table and are stored in `company_specializations`. They are optional; when supplied, `work_field_ids`/`industry` become optional as well. Both keys may be sent together. Invalid or non-existent IDs are rejected with `422`; duplicate specialization IDs are rejected with `422`.

### Response

`201 Created`

```json
{
    "success": true,
    "message": "Company profile created successfully.",
    "data": {
        "id": 50,
        "user_id": 220,
        "company_name": "Test Company",
        "description": "Works across engineering and computer science.",
        "approval_status": "pending",
        "work_fields": [
            { "field_id": 1, "field_name": "Engineering" },
            { "field_id": 2, "field_name": "Computer Science" }
        ],
        "specializations": [
            { "id": 1, "name": "Software Engineering" },
            { "id": 4, "name": "Data Science" }
        ]
    }
}
```

---

# 3. Update Company

Updates the authenticated company's basic information and work fields.

### Endpoint

```http
PUT /api/companies/me
```

### Request

```json
{
    "company_name": "Test Company",
    "work_field_ids": [1, 2],
    "specialization_ids": [1, 4],
    "description": "Works across engineering and computer science."
}
```

### Notes

- `work_field_ids` (legacy/current field-based classification) is an array of study field IDs that replaces the company's work fields. Pass an empty array to clear them.
- The legacy `industry` name is accepted and resolved against `study_fields`. Invalid or non-existent study fields are rejected with `422`.
- `specialization_ids` (new specialization-based industry classification) is an optional array of specialization IDs that replaces the company's specializations in `company_specializations`. Pass an empty array to clear them. Invalid, non-existent, or duplicate IDs are rejected with `422`.

### Response

```json
{
    "success": true,
    "message": "Company profile updated successfully.",
    "data": {
        "id": 50,
        "user_id": 220,
        "company_name": "Test Company",
        "description": "Works across engineering and computer science.",
        "approval_status": "pending",
        "work_fields": [
            { "field_id": 1, "field_name": "Engineering" },
            { "field_id": 2, "field_name": "Computer Science" }
        ],
        "specializations": [
            { "id": 1, "name": "Software Engineering" },
            { "id": 4, "name": "Data Science" }
        ]
    }
}
```

---

# 4. Get Company Profile

Returns the detailed profile of the authenticated company.

### Endpoint

```http
GET /api/companies/me/profile
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 4,
        "name": "Example Company",
        "description": "Software development company.",
        "website": "https://example.com",
        "phone": "+201000000000",
        "email": "contact@example.com",
        "status": "approved"
    }
}
```

---

# 5. Update Company Profile

### Endpoint

```http
PUT /api/companies/me/profile
```

or:

```http
PATCH /api/companies/me/profile
```

### Request

```json
{
    "description": "A software company specializing in web applications.",
    "website": "https://example.com",
    "phone": "+201000000000"
}
```

### Response

```json
{
    "success": true,
    "message": "Company profile updated successfully.",
    "data": {
        "description": "A software company specializing in web applications.",
        "website": "https://example.com",
        "phone": "+201000000000"
    }
}
```

---

# 6. Get Company Specializations

Returns the specializations associated with the company.

### Endpoint

```http
GET /api/companies/me/specializations
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Software Development"
        },
        {
            "id": 2,
            "name": "Artificial Intelligence"
        }
    ]
}
```

---

# 7. Add Company Specialization

Associates a specialization with the authenticated company.

### Endpoint

```http
POST /api/companies/me/specializations
```

### Request

```json
{
    "specialization_id": 2
}
```

### Response

```json
{
    "success": true,
    "message": "Specialization added successfully."
}
```

A specialization must not be associated with the same company more than once.

---

# 8. Remove Company Specialization

Removes a specialization from the company.

### Endpoint

```http
DELETE /api/companies/me/specializations/{specializationId}
```

### Example

```http
DELETE /api/companies/me/specializations/2
```

### Response

```json
{
    "success": true,
    "message": "Specialization removed successfully."
}
```

---

# 9. Get Company Trainings

Returns training opportunities created by the authenticated company.

### Endpoint

```http
GET /api/companies/me/trainings
```

### Query Parameters

```text
page
per_page
status
type
mode
```

### Example

```http
GET /api/companies/me/trainings?page=1&per_page=20&status=published
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 10,
            "title": "Backend PHP Internship",
            "type": "internship",
            "mode": "onsite",
            "status": "published",
            "application_deadline": "2026-08-30"
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

# 10. Create Training

Creates a new training opportunity.

### Endpoint

```http
POST /api/companies/me/trainings
```

### Request

```json
{
    "title": "Backend PHP Internship",
    "description": "Practical backend development training.",
    "type": "internship",
    "mode": "onsite",
    "location": "Cairo",
    "capacity": 10,
    "application_deadline": "2026-08-30",
    "start_date": "2026-09-05",
    "end_date": "2026-11-05"
}
```

### Response

```json
{
    "success": true,
    "message": "Training created successfully.",
    "data": {
        "id": 10,
        "title": "Backend PHP Internship",
        "status": "pending"
    }
}
```

New training listings may require administrative approval before becoming `published`.

---

# 11. Get Company Training

Returns a training opportunity owned by the authenticated company.

### Endpoint

```http
GET /api/companies/me/trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 10,
        "title": "Backend PHP Internship",
        "description": "Practical backend development training.",
        "status": "published"
    }
}
```

The API must verify that the training belongs to the authenticated company.

---

# 12. Update Company Training

### Endpoint

```http
PUT /api/companies/me/trainings/{trainingId}
```

or:

```http
PATCH /api/companies/me/trainings/{trainingId}
```

### Request

```json
{
    "title": "Advanced Backend PHP Internship",
    "capacity": 15
}
```

### Response

```json
{
    "success": true,
    "message": "Training updated successfully.",
    "data": {
        "id": 10,
        "title": "Advanced Backend PHP Internship",
        "capacity": 15
    }
}
```

---

# 13. Delete Company Training

Deletes a training opportunity owned by the company.

### Endpoint

```http
DELETE /api/companies/me/trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "message": "Training deleted successfully."
}
```

A training that already has active sessions or legally relevant records may instead be closed or archived rather than physically deleted.

---

# 14. Get Training Applications

Returns applications submitted to a company's training opportunity.

### Endpoint

```http
GET /api/companies/me/trainings/{trainingId}/applications
```

### Query Parameters

```text
page
per_page
status
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

# 15. Get Application

Returns a specific application submitted to the company's training.

### Endpoint

```http
GET /api/companies/me/trainings/{trainingId}/applications/{applicationId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 15,
        "training_id": 10,
        "student": {
            "id": 25,
            "name": "Ahmed Mohamed"
        },
        "status": "pending"
    }
}
```

The company can access only applications belonging to its own training listings.

---

# 16. Accept Application

Accepts a student's application.

### Endpoint

```http
POST /api/companies/me/trainings/{trainingId}/applications/{applicationId}/accept
```

### Response

```json
{
    "success": true,
    "message": "Application accepted successfully.",
    "data": {
        "application_id": 15,
        "status": "accepted"
    }
}
```

Accepting an application may create a training session according to the application's business rules.

---

# 17. Reject Application

Rejects a student's application.

### Endpoint

```http
POST /api/companies/me/trainings/{trainingId}/applications/{applicationId}/reject
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
        "application_id": 15,
        "status": "rejected"
    }
}
```

The rejection reason should correspond to a valid configured rejection reason.

---

# 18. Get Company Sessions

Returns training sessions belonging to the authenticated company.

### Endpoint

```http
GET /api/companies/me/sessions
```

### Query Parameters

```text
page
per_page
status
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 20,
            "training_id": 10,
            "student_id": 25,
            "status": "active",
            "start_date": "2026-09-05",
            "end_date": "2026-11-05"
        }
    ]
}
```

---

# 19. Get Training Session

Returns a specific session belonging to the company.

### Endpoint

```http
GET /api/companies/me/sessions/{sessionId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 20,
        "training_id": 10,
        "student": {
            "id": 25,
            "name": "Ahmed Mohamed"
        },
        "status": "active",
        "start_date": "2026-09-05",
        "end_date": "2026-11-05"
    }
}
```

---

# 20. Update Training Session

### Endpoint

```http
PUT /api/companies/me/sessions/{sessionId}
```

or:

```http
PATCH /api/companies/me/sessions/{sessionId}
```

### Request

```json
{
    "end_date": "2026-11-10"
}
```

### Response

```json
{
    "success": true,
    "message": "Training session updated successfully."
}
```

---

# 21. Start Training Session

Marks an eligible training session as started.

### Endpoint

```http
POST /api/companies/me/sessions/{sessionId}/start
```

### Response

```json
{
    "success": true,
    "message": "Training session started successfully.",
    "data": {
        "session_id": 20,
        "status": "active"
    }
}
```

---

# 22. Complete Training Session

Marks a training session as completed.

### Endpoint

```http
POST /api/companies/me/sessions/{sessionId}/complete
```

### Response

```json
{
    "success": true,
    "message": "Training session completed successfully.",
    "data": {
        "session_id": 20,
        "status": "completed"
    }
}
```

A completed session may become eligible for certificate issuance.

---

# 23. Cancel Training Session

Cancels an eligible training session.

### Endpoint

```http
POST /api/companies/me/sessions/{sessionId}/cancel
```

### Request

```json
{
    "reason": "Student discontinued the training."
}
```

### Response

```json
{
    "success": true,
    "message": "Training session cancelled successfully.",
    "data": {
        "session_id": 20,
        "status": "cancelled"
    }
}
```

---

# 24. Get Company Certificates

Returns certificates issued by the authenticated company.

### Endpoint

```http
GET /api/companies/me/certificates
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 50,
            "student_id": 25,
            "training_id": 10,
            "status": "issued",
            "issued_at": "2026-11-12"
        }
    ]
}
```

---

# 25. Public Company Profile

Returns publicly available company information.

### Endpoint

```http
GET /api/companies/{id}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 4,
        "name": "Example Company",
        "description": "Software development company.",
        "status": "approved",
        "specializations": [
            {
                "id": 1,
                "name": "Software Development"
            }
        ]
    }
}
```

Private company information must not be exposed through this endpoint.

---

# Company Status

Company accounts may use statuses such as:

```text
pending
approved
rejected
suspended
inactive
```

Only an approved company should normally be allowed to publish or operate active training opportunities.

---

# Training Ownership

Every company training must belong to exactly one company.

Before performing any operation on:

```text
training
application
training session
certificate
```

the API must verify the ownership relationship.

A company must never access another company's private applications or sessions by modifying an ID in the URL.

---

# Application Lifecycle

A typical application lifecycle is:

```text
pending
   ├── accepted
   │      ↓
   │   training session
   │
   └── rejected
```

Applications must follow valid status transitions and must not be changed arbitrarily.

---

# Training Session Lifecycle

A typical lifecycle is:

```text
scheduled
    ↓
active
    ↓
completed
```

Alternative terminal state:

```text
scheduled / active
        ↓
     cancelled
```

Expired sessions may be handled by the scheduled cron jobs.

---

# Certificate Eligibility

A completed training session may qualify the student for a certificate when all configured business requirements have been satisfied.

Certificate issuance should not be triggered merely by changing a client-side status.

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
    "message": "Company access is required."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Company or resource not found."
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

# Related Endpoints

```text
GET    /api/companies/me
PUT    /api/companies/me
PATCH  /api/companies/me

GET    /api/companies/me/profile
PUT    /api/companies/me/profile
PATCH  /api/companies/me/profile

GET    /api/companies/me/specializations
POST   /api/companies/me/specializations
DELETE /api/companies/me/specializations/{specializationId}

GET    /api/companies/me/trainings
POST   /api/companies/me/trainings
GET    /api/companies/me/trainings/{trainingId}
PUT    /api/companies/me/trainings/{trainingId}
PATCH  /api/companies/me/trainings/{trainingId}
DELETE /api/companies/me/trainings/{trainingId}

GET    /api/companies/me/trainings/{trainingId}/applications
GET    /api/companies/me/trainings/{trainingId}/applications/{applicationId}
POST   /api/companies/me/trainings/{trainingId}/applications/{applicationId}/accept
POST   /api/companies/me/trainings/{trainingId}/applications/{applicationId}/reject

GET    /api/companies/me/sessions
GET    /api/companies/me/sessions/{sessionId}
PUT    /api/companies/me/sessions/{sessionId}
PATCH  /api/companies/me/sessions/{sessionId}
POST   /api/companies/me/sessions/{sessionId}/start
POST   /api/companies/me/sessions/{sessionId}/complete
POST   /api/companies/me/sessions/{sessionId}/cancel

GET    /api/companies/me/certificates

GET    /api/companies/{id}
