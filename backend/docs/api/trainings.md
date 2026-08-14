# MASAR API — Trainings

## Overview

The Trainings API manages training listings, discovery, filtering, details, applications, skills, specializations, and training lifecycle operations.

Base URL:

```text
/api/trainings
```

Most read operations require authentication according to the current API security policy. Management operations require the appropriate company or administrator permissions.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. List Trainings

Returns a paginated list of available training opportunities.

### Endpoint

```http
GET /api/trainings
```

### Query Parameters

```text
page
per_page
search
type
mode
status
specialization_id
skill_id
company_id
location
start_date
end_date
```

### Example

```http
GET /api/trainings?page=1&per_page=20&type=internship&mode=onsite
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 10,
            "title": "Backend PHP Internship",
            "company": {
                "id": 4,
                "name": "Example Company"
            },
            "type": "internship",
            "mode": "onsite",
            "location": "Cairo",
            "status": "published",
            "application_deadline": "2026-08-30",
            "start_date": "2026-09-05",
            "end_date": "2026-11-05"
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

Only training listings that are eligible for discovery should normally be returned.

---

# 2. Get Training Details

Returns complete details for a training opportunity.

### Endpoint

```http
GET /api/trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 10,
        "title": "Backend PHP Internship",
        "description": "Practical backend development training.",
        "company": {
            "id": 4,
            "name": "Example Company"
        },
        "type": "internship",
        "mode": "onsite",
        "location": "Cairo",
        "capacity": 10,
        "status": "published",
        "application_deadline": "2026-08-30",
        "start_date": "2026-09-05",
        "end_date": "2026-11-05",
        "specializations": [
            {
                "id": 5,
                "name": "Computer Science"
            }
        ],
        "skills": [
            {
                "id": 1,
                "name": "PHP"
            },
            {
                "id": 2,
                "name": "MySQL"
            }
        ]
    }
}
```

---

# 3. Create Training

Creates a training listing for the authenticated company.

### Endpoint

```http
POST /api/trainings
```

### Authorization

Required role:

```text
company
```

The company must have an approved account.

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
    "end_date": "2026-11-05",
    "specialization_ids": [
        5
    ],
    "skill_ids": [
        1,
        2
    ]
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

A newly created listing may require administrative review before publication.

---

# 4. Update Training

Updates a training listing owned by the authenticated company.

### Endpoint

```http
PUT /api/trainings/{trainingId}
```

or:

```http
PATCH /api/trainings/{trainingId}
```

### Request

```json
{
    "title": "Advanced Backend PHP Internship",
    "capacity": 15,
    "description": "Advanced practical backend development training."
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

The API must verify that the authenticated company owns the training.

---

# 5. Delete Training

Deletes an eligible training listing.

### Endpoint

```http
DELETE /api/trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "message": "Training deleted successfully."
}
```

If the training has applications, active sessions, certificates, or other historical records, the system should prefer closing or archiving the listing instead of destructive deletion.

---

# 6. Publish Training

Publishes a training listing when the authenticated company is authorized to publish it.

### Endpoint

```http
POST /api/trainings/{trainingId}/publish
```

### Response

```json
{
    "success": true,
    "message": "Training published successfully.",
    "data": {
        "id": 10,
        "status": "published"
    }
}
```

If administrative approval is required, this endpoint should not bypass that approval process.

---

# 7. Close Training

Closes a training listing and prevents new applications.

### Endpoint

```http
POST /api/trainings/{trainingId}/close
```

### Response

```json
{
    "success": true,
    "message": "Training closed successfully.",
    "data": {
        "id": 10,
        "status": "closed"
    }
}
```

---

# 8. Get Training Applications

Returns applications associated with a training.

### Endpoint

```http
GET /api/trainings/{trainingId}/applications
```

### Authorization

The authenticated user must have permission to access the training's applications.

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

# 9. Apply for Training

Creates an application for the authenticated student.

### Endpoint

```http
POST /api/trainings/{trainingId}/apply
```

### Authorization

Required role:

```text
student
```

### Request

```json
{
    "cover_letter": "I am interested in this training opportunity because..."
}
```

### Response

```json
{
    "success": true,
    "message": "Application submitted successfully.",
    "data": {
        "application_id": 15,
        "training_id": 10,
        "status": "pending"
    }
}
```

---

# 10. Withdraw Application

Withdraws an eligible application belonging to the authenticated student.

### Endpoint

```http
POST /api/trainings/{trainingId}/applications/{applicationId}/withdraw
```

### Response

```json
{
    "success": true,
    "message": "Application withdrawn successfully.",
    "data": {
        "application_id": 15,
        "status": "withdrawn"
    }
}
```

The system must validate that the application belongs to the authenticated student.

---

# 11. Get Training Specializations

Returns specializations associated with a training.

### Endpoint

```http
GET /api/trainings/{trainingId}/specializations
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "name": "Computer Science"
        },
        {
            "id": 8,
            "name": "Software Engineering"
        }
    ]
}
```

---

# 12. Add Training Specialization

Associates a specialization with a training.

### Endpoint

```http
POST /api/trainings/{trainingId}/specializations
```

### Authorization

Training owner or authorized administrator.

### Request

```json
{
    "specialization_id": 8
}
```

### Response

```json
{
    "success": true,
    "message": "Specialization added successfully."
}
```

---

# 13. Remove Training Specialization

### Endpoint

```http
DELETE /api/trainings/{trainingId}/specializations/{specializationId}
```

### Response

```json
{
    "success": true,
    "message": "Specialization removed successfully."
}
```

---

# 14. Get Training Skills

Returns skills required or associated with a training.

### Endpoint

```http
GET /api/trainings/{trainingId}/skills
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "PHP"
        },
        {
            "id": 2,
            "name": "MySQL"
        }
    ]
}
```

---

# 15. Add Training Skill

Associates a skill with a training.

### Endpoint

```http
POST /api/trainings/{trainingId}/skills
```

### Request

```json
{
    "skill_id": 3
}
```

### Response

```json
{
    "success": true,
    "message": "Skill added successfully."
}
```

---

# 16. Remove Training Skill

### Endpoint

```http
DELETE /api/trainings/{trainingId}/skills/{skillId}
```

### Response

```json
{
    "success": true,
    "message": "Skill removed successfully."
}
```

---

# 17. Save Training

Allows an authenticated student to save a training opportunity.

### Endpoint

```http
POST /api/trainings/{trainingId}/save
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
    "message": "Training saved successfully."
}
```

---

# 18. Unsave Training

Removes a training from the student's saved list.

### Endpoint

```http
DELETE /api/trainings/{trainingId}/save
```

### Response

```json
{
    "success": true,
    "message": "Training removed from saved list."
}
```

---

# 19. Get Training Sessions

Returns training sessions associated with a training listing.

### Endpoint

```http
GET /api/trainings/{trainingId}/sessions
```

### Authorization

The endpoint must restrict access to authorized parties, such as the training owner, participating student, or administrator.

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 20,
            "student_id": 25,
            "status": "active",
            "start_date": "2026-09-05",
            "end_date": "2026-11-05"
        }
    ]
}
```

---

# Training Types

Training types are controlled by the application's configured training type enum.

Examples may include:

```text
internship
training
apprenticeship
```

The API must reject unsupported values.

---

# Training Modes

Training modes are controlled by the configured training mode enum.

Typical values include:

```text
onsite
remote
hybrid
```

Only values defined by the application enum should be accepted.

---

# Training Status

Training status is controlled by the configured training status enum.

Typical lifecycle:

```text
draft
   ↓
pending
   ↓
published
   ↓
closed
```

Possible rejection path:

```text
pending
   ↓
rejected
```

Expired listings may be automatically closed by the scheduled cron process.

---

# Application Rules

A student can apply only when all of the following are true:

1. The training exists.
2. The training is published.
3. The application deadline has not passed.
4. The training has available capacity where capacity applies.
5. The student has not already submitted an application.
6. The student account is active.
7. Any required eligibility rules are satisfied.

---

# Capacity

When a training has a fixed capacity, the system must prevent accepting more students than the configured limit.

Capacity checks must be performed server-side.

Concurrent application/acceptance operations must be handled safely to prevent overbooking.

---

# Application Deadline

Applications must be rejected after:

```text
application_deadline
```

The server's current time must be used for this validation.

Client-provided timestamps must never be trusted for deadline enforcement.

---

# Ownership and Authorization

Training modification operations require ownership or administrative permission.

The API must verify:

```text
authenticated user
        ↓
company account
        ↓
training.company_id
```

A company must never modify another company's training by changing the URL ID.

---

# Search and Filtering

Training discovery may support:

```text
search
type
mode
location
company
specialization
skill
status
start_date
end_date
```

Example:

```http
GET /api/trainings?search=backend&skill_id=1&mode=remote
```

Search results should return only records the authenticated user is allowed to discover.

---

# Pagination

List endpoints use:

```text
page
per_page
```

Example:

```http
GET /api/trainings?page=2&per_page=20
```

Example metadata:

```json
{
    "meta": {
        "current_page": 2,
        "per_page": 20,
        "total": 47,
        "last_page": 3
    }
}
```

The API should enforce a maximum `per_page` value.

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
    "message": "You do not have permission to access this training."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Training not found."
}
```

## 409 Conflict

Used when an operation conflicts with the current state.

```json
{
    "success": false,
    "message": "You have already applied for this training."
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
GET    /api/trainings
GET    /api/trainings/{trainingId}

POST   /api/trainings
PUT    /api/trainings/{trainingId}
PATCH  /api/trainings/{trainingId}
DELETE /api/trainings/{trainingId}

POST   /api/trainings/{trainingId}/publish
POST   /api/trainings/{trainingId}/close

GET    /api/trainings/{trainingId}/applications
POST   /api/trainings/{trainingId}/apply
POST   /api/trainings/{trainingId}/applications/{applicationId}/withdraw

GET    /api/trainings/{trainingId}/specializations
POST   /api/trainings/{trainingId}/specializations
DELETE /api/trainings/{trainingId}/specializations/{specializationId}

GET    /api/trainings/{trainingId}/skills
POST   /api/trainings/{trainingId}/skills
DELETE /api/trainings/{trainingId}/skills/{skillId}

POST   /api/trainings/{trainingId}/save
DELETE /api/trainings/{trainingId}/save

GET    /api/trainings/{trainingId}/sessions
