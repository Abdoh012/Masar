# MASAR API — Students

## Overview

The Students API manages student profiles, academic information, skills, training applications, saved trainings, and student-related data.

Base URL:

```text
/api/students
```

All endpoints require authentication unless explicitly stated otherwise.

---

# Authentication

Protected endpoints require:

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
```

---

# 1. Get Current Student Profile

Returns the complete student profile of the authenticated user.

### Endpoint

```http
GET /api/students/me
```

### Authentication

Required.

### Authorization

Required role:

```text
student
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 25,
        "name": "Ahmed Mohamed",
        "email": "ahmed@example.com",
        "university": {
            "id": 1,
            "name": "Cairo University"
        },
        "faculty": {
            "id": 2,
            "name": "Faculty of Computers and Artificial Intelligence"
        },
        "degree": {
            "id": 3,
            "name": "Bachelor"
        },
        "specialization": {
            "id": 5,
            "name": "Computer Science"
        },
        "graduation_year": 2027
    }
}
```

---

# 2. Create Student Profile

Creates the student profile for the authenticated user.

### Endpoint

```http
POST /api/students
```

### Authentication

Required.

### Authorization

Required role:

```text
student
```

### Request

```json
{
    "university_id": 1,
    "faculty_id": 2,
    "degree_id": 3,
    "specialization_id": 5,
    "graduation_year": 2027
}
```

### Response

```json
{
    "success": true,
    "message": "Student profile created successfully.",
    "data": {
        "id": 1,
        "user_id": 25,
        "university_id": 1,
        "faculty_id": 2,
        "degree_id": 3,
        "specialization_id": 5,
        "graduation_year": 2027
    }
}
```

---

# 3. Update Student Profile

Updates the authenticated student's academic information.

### Endpoint

```http
PUT /api/students/me
```

or:

```http
PATCH /api/students/me
```

### Request

```json
{
    "university_id": 1,
    "faculty_id": 2,
    "degree_id": 3,
    "specialization_id": 5,
    "graduation_year": 2028
}
```

### Response

```json
{
    "success": true,
    "message": "Student profile updated successfully.",
    "data": {
        "id": 1,
        "university_id": 1,
        "faculty_id": 2,
        "degree_id": 3,
        "specialization_id": 5,
        "graduation_year": 2028
    }
}
```

---

# 4. Get Student Skills

Returns all skills associated with the authenticated student.

### Endpoint

```http
GET /api/students/me/skills
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
        },
        {
            "id": 3,
            "name": "Laravel"
        }
    ]
}
```

---

# 5. Add Student Skill

Adds a skill to the student's profile.

### Endpoint

```http
POST /api/students/me/skills
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
    "message": "Skill added successfully.",
    "data": {
        "skill_id": 3
    }
}
```

A student cannot add the same skill more than once.

---

# 6. Remove Student Skill

Removes a skill from the student's profile.

### Endpoint

```http
DELETE /api/students/me/skills/{skillId}
```

### Example

```http
DELETE /api/students/me/skills/3
```

### Response

```json
{
    "success": true,
    "message": "Skill removed successfully."
}
```

---

# 7. Get Student Applications

Returns the authenticated student's training applications.

### Endpoint

```http
GET /api/students/me/applications
```

### Query Parameters

```text
page
per_page
status
```

Example:

```http
GET /api/students/me/applications?page=1&per_page=20&status=pending
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "training_id": 10,
            "training_title": "Backend PHP Internship",
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

# 8. Get Student Application

Returns a specific application belonging to the authenticated student.

### Endpoint

```http
GET /api/students/me/applications/{applicationId}
```

### Response

```json
{
    "success": true,
    "data": {
        "id": 15,
        "training_id": 10,
        "training_title": "Backend PHP Internship",
        "status": "pending",
        "applied_at": "2026-08-01 12:00:00"
    }
}
```

The API must verify that the requested application belongs to the authenticated student.

---

# 9. Get Saved Trainings

Returns training opportunities saved by the authenticated student.

### Endpoint

```http
GET /api/students/me/saved-trainings
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
            "status": "published"
        }
    ]
}
```

---

# 10. Save Training

Saves a training opportunity for later.

### Endpoint

```http
POST /api/students/me/saved-trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "message": "Training saved successfully."
}
```

---

# 11. Remove Saved Training

Removes a training opportunity from the student's saved list.

### Endpoint

```http
DELETE /api/students/me/saved-trainings/{trainingId}
```

### Response

```json
{
    "success": true,
    "message": "Training removed from saved list."
}
```

---

# 12. Student Dashboard

Returns summarized information required by the student dashboard.

### Endpoint

```http
GET /api/students/me/dashboard
```

### Response

```json
{
    "success": true,
    "data": {
        "applications": {
            "total": 12,
            "pending": 4,
            "accepted": 3,
            "rejected": 5
        },
        "saved_trainings": 7,
        "active_sessions": 1,
        "certificates": 2,
        "unread_notifications": 4
    }
}
```

---

# 13. Student Training Sessions

Returns the student's active and completed training sessions.

### Endpoint

```http
GET /api/students/me/sessions
```

### Query Parameters

```text
status
page
per_page
```

Example:

```http
GET /api/students/me/sessions?status=active
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 20,
            "training_id": 10,
            "training_title": "Backend PHP Internship",
            "status": "active",
            "start_date": "2026-08-01",
            "end_date": "2026-10-01"
        }
    ]
}
```

---

# 14. Student Certificates

Returns certificates issued to the authenticated student.

### Endpoint

```http
GET /api/students/me/certificates
```

### Response

```json
{
    "success": true,
    "data": [
        {
            "id": 7,
            "training_id": 10,
            "title": "Backend PHP Internship",
            "status": "issued",
            "issued_at": "2026-10-05"
        }
    ]
}
```

---

# Academic Validation

When creating or updating academic information, the API should validate relationships between:

```text
University
    ↓
Faculty
    ↓
Degree
    ↓
Specialization
```

For example, a specialization belonging to one faculty must not be assigned to a student enrolled in an unrelated faculty.

---

# Graduation Year

`graduation_year` must:

* Be numeric.
* Represent a valid academic year.
* Not be unreasonably far in the past.
* Not exceed configured future limits.

Example:

```json
{
    "graduation_year": 2027
}
```

---

# Skills Rules

Students may have multiple skills.

The relationship is represented by:

```text
students
    ↓
student_skills
    ↓
skills
```

The same `(student_id, skill_id)` pair must not be duplicated.

---

# Application Rules

A student:

1. Can apply only to published training opportunities.
2. Cannot apply after the application deadline.
3. Cannot submit duplicate applications for the same training.
4. Can withdraw an eligible application.
5. Can view only their own applications.
6. Must satisfy any required training constraints.
7. Must not bypass application status transitions.

Typical application lifecycle:

```text
pending
   ↓
accepted
   ↓
completed
```

or:

```text
pending
   ↓
rejected
```

---

# Student Authorization

Student endpoints must verify:

```text
Authenticated
    +
Role = student
    +
Resource belongs to authenticated student
```

A student must never be able to access or modify another student's private data by changing an ID in the URL.

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
    "message": "Student access is required."
}
```

## 404 Not Found

```json
{
    "success": false,
    "message": "Student profile not found."
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
GET    /api/students/me
POST   /api/students
PUT    /api/students/me
PATCH  /api/students/me

GET    /api/students/me/skills
POST   /api/students/me/skills
DELETE /api/students/me/skills/{skillId}

GET    /api/students/me/applications
GET    /api/students/me/applications/{applicationId}

GET    /api/students/me/saved-trainings
POST   /api/students/me/saved-trainings/{trainingId}
DELETE /api/students/me/saved-trainings/{trainingId}

GET    /api/students/me/dashboard
GET    /api/students/me/sessions
GET    /api/students/me/certificates
