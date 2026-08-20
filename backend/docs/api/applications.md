# MASAR API — Applications

## Overview

The Applications API manages student applications to training opportunities, including submission, viewing, withdrawal, acceptance, rejection, and application status transitions.

Base URL:

```text
/api/v1/applications
```

All endpoints require authentication.

---

# 0. Implemented Endpoints

The routes currently implemented and verified:

```text
POST   /api/v1/applications                 Student submits an application
GET    /api/v1/applications/my              Student's own applications
GET    /api/v1/applications/{id}            Application detail (student/company/admin)
GET    /api/v1/applications?training_id=..  Company's applications for one training
POST   /api/v1/applications/withdraw        Student withdraws a pending application
POST   /api/v1/applications/accept          Company accepts a pending application
POST   /api/v1/applications/reject          Company rejects a pending application
```

Student routes require `role=student`; company routes require `role=company`.
Submission is transactional: the application record and all answers are created in a
single DB transaction (`db_begin_transaction` / `db_commit` / `db_rollback`).

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
GET /api/v1/applications/my
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
GET /api/v1/applications/{applicationId}
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
    "message": "Application retrieved successfully.",
    "data": {
        "id": 5,
        "training_id": 2,
        "student_id": 101,
        "message": null,
        "status": "pending",
        "rejection_reason": null,
        "rejection_note": null,
        "applied_at": "2026-08-20 15:20:40",
        "reviewed_at": null,
        "withdrawn_at": null,
        "reviewed_by": null,
        "cv_file_id": 17,
        "university_id": 1,
        "faculty_id": 2,
        "applicant_type": "student",
        "academic_year": "2nd",
        "graduation_year": 2028,
        "motivation": "I am excited about this opportunity.",
        "training_title": "Frontend Engineering Internship",
        "training_company_id": 46,
        "student_user_id": 209,
        "student_name": "Student Two",
        "student_email": "stu2@test.local",
        "answers": [
            {
                "question_id": 1,
                "answer": "I am eager.",
                "question": "Why do you want this internship?",
                "question_type": "textarea",
                "options": null
            }
        ],
        "university_name": "Cairo University",
        "faculty_name": "Faculty of Computers and Artificial Intelligence"
    }
}
```

The response is enriched by `application_service_enrich_application`: it attaches the
submitted `answers`, resolves `university_name` / `faculty_name`, and normalizes the
status (the DB stores `submitted`, which is exposed as `pending`). The `my applications`
list (`/api/v1/applications/my`) returns the same fields but without `answers` /
`university_name` / `faculty_name` and keeps the raw `submitted` status.

---

# 3. Create Application

Creates an application for a training opportunity. This is the student's multi-step
application: education/CV/motivation plus the training's dynamic questions/answers.

### Endpoint

```http
POST /api/v1/applications
```

### Authorization

Required role:

```text
student
```

### Request

```json
{
    "training_id": 2,
    "cv_file_id": 17,
    "applicant_type": "student",
    "university_id": 1,
    "faculty_id": 1,
    "academic_year": "3rd year",
    "graduation_year": 2027,
    "motivation": "I am passionate about frontend development and want hands-on experience.",
    "cover_letter": "Optional cover letter.",
    "answers": [
        {
            "question_id": 1,
            "answer": "I want to learn modern frontend engineering in a real team."
        }
    ]
}
```

Fields:

```text
training_id      int          required  (validated server-side; must be published,
                                        deadline not passed, capacity available)
student_id       ignored      the authenticated student is resolved server-side
cv_file_id       int          optional  must reference an existing file record
university_id    int          optional  must exist in universities (422 if not)
faculty_id       int          optional  must exist in faculties (422 if not)
applicant_type   student|graduated (default student)
academic_year    string       max 20 chars
graduation_year  int          between 1950 and 2100
motivation       string       max 5000 chars
cover_letter     string       max 10000 chars
answers          array        validated against the training's questions:
                              required questions must be answered; select/radio
                              answers must be one of the question's options
```

### Validation responses

```text
409  duplicate application          "You have already applied for this training opportunity."
409  deadline passed                "The application deadline has passed."
409  training not accepting         "This training opportunity is not accepting applications."
409  capacity reached               "This training opportunity has reached its capacity."
422  invalid university/faculty     "Selected university/faculty was not found."
422  missing required answer        answers.<question_id> => "This question is required."
422  invalid select/radio option    answers.<question_id> => "Selected option is not valid."
```

### Response

```json
{
    "success": true,
    "message": "Application submitted successfully.",
    "data": {
        "id": 15,
        "training_id": 2,
        "student_id": 100,
        "cv_file_id": 17,
        "university_id": 1,
        "faculty_id": 1,
        "applicant_type": "student",
        "academic_year": "3rd year",
        "graduation_year": 2027,
        "motivation": "...",
        "cover_letter": "...",
        "status": "pending",
        "applied_at": "2026-08-20 12:00:00"
    }
}
```

The application and its answers are created inside a database transaction.

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
POST /api/v1/applications/withdraw
```

### Request

```json
{
    "id": 15
}
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
POST /api/v1/applications/accept
```

### Request

```json
{
    "id": 15
}
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
POST /api/v1/applications/reject
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
    "id": 15,
    "rejection_reason": "Candidate did not meet minimum requirements"
}
```

`rejection_reason` is required and must be one of the preset values:

```text
Candidate did not meet minimum requirements
Position already filled
Insufficient capacity in training
Application incomplete
Candidate withdrew consideration
Training program discontinued
```

### Response

```json
{
    "success": true,
    "message": "Application rejected successfully.",
    "data": {
        "id": 15,
        "status": "rejected"
    }
}
```

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

Implemented (base URL `/api/v1`):

```text
POST   /api/v1/applications
GET    /api/v1/applications/my
GET    /api/v1/applications/{applicationId}
GET    /api/v1/applications?training_id={trainingId}
POST   /api/v1/applications/withdraw   (body: { "id": applicationId })
POST   /api/v1/applications/accept     (body: { "id": applicationId })
POST   /api/v1/applications/reject     (body: { "id": applicationId, "rejection_reason": "..." })
```

Design/spec-only (not routed yet):

```text
PUT/PATCH /api/v1/applications/{applicationId}
GET       /api/v1/applications/training/{trainingId}
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
