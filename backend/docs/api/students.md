# MASAR API — Students

## Overview

The Students API manages the authenticated student's profile, academic information, skills, and CV.

Base URL:

```text
http://localhost/Masar/backend/api/v1
```

All endpoints require a Bearer token unless stated otherwise.

```http
Authorization: Bearer ACCESS_TOKEN
Accept: application/json
Content-Type: application/json
```

All endpoints below require the authenticated user role `student`, except `GET /students/{id}` which also allows `company` and `admin`.

---

## 1. Get Current Student Profile

```http
GET /api/v1/students/me
```

Returns the authenticated student's full profile including the attached CV file ID.

### Response

```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "user_id": 25,
            "full_name": "Test Student",
            "phone": null,
            "bio": null,
            "field_id": 1,
            "degree_id": 3,
            "specialization_id": 5,
            "graduation_year": 2027,
            "city": null,
            "profile_image_file_id": null,
            "cv_file_id": 9,
            "is_profile_complete": 1,
            "created_at": "2026-08-01 12:00:00",
            "updated_at": "2026-08-01 12:00:00"
        },
        "profile": {
            "student_id": 1,
            "skills": ["PHP", "MySQL"],
            "cv_file_id": 9
        }
    }
}
```

---

## 2. Create Student Profile

```http
POST /api/v1/students/profile
```

Creates the student profile for the authenticated user. Academic fields are name-based and resolved to internal IDs.

### Request

```json
{
    "full_name": "Test Student",
    "field": "Engineering",
    "specialization": "Artificial Intelligence",
    "degree": "Bachelor of Computer Science",
    "graduation_year": 2027,
    "bio": "Backend developer",
    "phone": "01000000002",
    "city": "Cairo",
    "skills": ["PHP", "MySQL"],
    "cv_file_id": 9
}
```

### Notes

- `field` and `specialization` are required and must match seeded lookup data. The legacy `faculty` key is accepted as a fallback for the User Field (`faculty` + `specialization`), so clients sending the old key name still work.
- `degree`, `graduation_year`, `bio`, `phone`, `city`, `skills`, and `cv_file_id` are optional.
- If `cv_file_id` is supplied, the file must belong to the authenticated user.
- `university` is no longer part of the student model and is ignored if sent.

### Response

`201 Created`

```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "full_name": "Test Student",
            "field_id": 1,
            "degree_id": 3,
            "specialization_id": 5,
            "graduation_year": 2027,
            "is_profile_complete": 0
        }
    }
}
```

---

## 3. Update Student Profile

```http
PUT /api/v1/students/profile
```

Updates any subset of the authenticated student's profile fields.

### Request

```json
{
    "field": "Engineering",
    "specialization": "Software Engineering",
    "degree": "Bachelor of Computer Science",
    "graduation_year": 2028,
    "bio": "Updated bio",
    "phone": "01000000002",
    "city": "Giza",
    "skills": ["PHP", "Laravel", "MySQL"],
    "cv_file_id": 9
}
```

### Notes

- `field` and `specialization` must be provided together when either is updated. The legacy `faculty` key is accepted as a fallback for the User Field.
- Pass an empty string for `degree`, or `0`/`null` for `cv_file_id`, to clear that field.
- `cv_file_id` is only accepted when the file belongs to the authenticated user. Otherwise the request is rejected with `422`.
- `university` is ignored if sent.

### Response

```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "full_name": "Test Student",
            "field_id": 1,
            "degree_id": 3,
            "specialization_id": 7,
            "graduation_year": 2028,
            "cv_file_id": 9
        }
    }
}
```

---

## 4. Get Profile Completion Status

```http
GET /api/v1/students/profile/status
```

Reports whether the profile is complete and which fields are still missing.

### Response

```json
{
    "success": true,
    "data": {
        "completed": false,
        "missing_fields": ["skills", "cv"],
        "completion_percentage": 60
    }
}
```

Completion is based on `field_id`, `specialization_id`, skills, and an attached CV.

---

## 5. Complete Student Profile

```http
POST /api/v1/students/profile/complete
```

Completes the profile in one request and sets `is_profile_complete = 1`.

### Request

```json
{
    "full_name": "Test Student",
    "field": "Engineering",
    "specialization": "Artificial Intelligence",
    "degree": "Bachelor of Computer Science",
    "bio": "Backend developer",
    "skills": ["PHP", "Laravel", "MySQL"],
    "cv_file_id": 9
}
```

### Notes

- `full_name`, `field`, `specialization`, `skills` and `cv_file_id` are required. The legacy `faculty` key is accepted as a fallback for the User Field.
- `cv_file_id` must belong to the authenticated user.

### Response

```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "is_profile_complete": 1,
            "cv_file_id": 9
        },
        "profile": {
            "skills": ["PHP", "Laravel", "MySQL"]
        }
    }
}
```

---

## 6. Get Public Student Profile

```http
GET /api/v1/students/{id}
```

Returns a sanitized public profile. `phone`, `user_id`, `cv_file_id`, and `profile_image_file_id` are removed.

### Authentication

Allowed roles: `student`, `company`, `admin`.

### Response

```json
{
    "success": true,
    "data": {
        "student": {
            "id": 1,
            "full_name": "Test Student",
            "bio": "Backend developer",
            "field_id": 1,
            "degree_id": 3,
            "specialization_id": 5,
            "graduation_year": 2027,
            "city": "Cairo"
        },
        "profile": {
            "skills": ["PHP", "MySQL"]
        }
    }
}
```

---

## 7. Get My Skills

```http
GET /api/v1/students/me/skills
```

Returns the skill names attached to the authenticated student.

### Response

```json
{
    "success": true,
    "data": {
        "skills": ["PHP", "MySQL"]
    }
}
```

---

## 8. Add a Skill

```http
POST /api/v1/students/me/skills
```

### Request

```json
{
    "skill": "Laravel"
}
```

The skill must exist in the `skills` lookup table. Duplicate skills are ignored.

### Response

`201 Created`

```json
{
    "success": true,
    "data": {
        "skills": ["PHP", "MySQL", "Laravel"]
    }
}
```

---

## 9. Replace All Skills

```http
PUT /api/v1/students/me/skills
```

Replaces the student's full skill list.

### Request

```json
{
    "skills": ["PHP", "Laravel", "MySQL"]
}
```

### Response

```json
{
    "success": true,
    "data": {
        "student_id": 1,
        "skills": ["PHP", "Laravel", "MySQL"]
    }
}
```

---

## 10. Remove a Skill

```http
DELETE /api/v1/students/me/skills
```

### Request

```json
{
    "skill": "Laravel"
}
```

### Response

```json
{
    "success": true,
    "data": {
        "skills": ["PHP", "MySQL"]
    }
}
```

---

## 11. Get My CV

```http
GET /api/v1/students/me/cv
```

Returns the file ID of the attached CV, or `null` if none is attached.

### Response

```json
{
    "success": true,
    "data": {
        "cv_file_id": 9
    }
}
```

---

## 12. Attach CV

```http
POST /api/v1/students/me/cv
```

Attaches an uploaded file as the student's CV.

### Request

```json
{
    "file_id": 9
}
```

The file must belong to the authenticated user. Otherwise the request is rejected with `422`.

### Response

```json
{
    "success": true,
    "data": {
        "message": "CV attached successfully.",
        "cv_file_id": 9
    }
}
```

---

## 13. Remove CV

```http
DELETE /api/v1/students/me/cv
```

Requires a student Bearer token. Removes the current CV:

- deletes the physical file from storage (`app/storage/uploads/...`),
- deletes the corresponding `files` record,
- clears the student's `cv_file_id` reference.

The deletion is scoped to the authenticated student's stored CV record only; the
physical path is resolved from the database and validated to stay inside the
application's upload/storage directory, so no client-supplied or out-of-tree path
can ever be deleted. Returns `404` with `No CV found.` when the student has no CV.

### Response (success)

```json
{
    "success": true,
    "message": null,
    "data": {
        "message": "CV removed successfully."
    },
    "errors": null
}
```

### Response (no CV attached)

```json
{
    "success": false,
    "message": "No CV found.",
    "data": null,
    "errors": null
}
```

---

## Related: Uploading the CV File

The CV endpoints above accept a `file_id`. To create the file, upload it first:

```http
POST /api/v1/files
Content-Type: multipart/form-data

type=cv
file=<file>
```

The response contains the new file `id`, `url`, and safe metadata only. No filesystem paths are exposed. Download the file with:

```http
GET /api/v1/files/{id}?download=true
```

---

## Standard Errors

### 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "success": false,
    "message": "Only students can access this resource."
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Student profile not found."
}
```

### 422 Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "full_name": "Full name is required."
    }
}
```

---

## Endpoint Summary

```text
GET    /api/v1/students/me
POST   /api/v1/students/profile
PUT    /api/v1/students/profile
GET    /api/v1/students/profile/status
POST   /api/v1/students/profile/complete
GET    /api/v1/students/{id}

GET    /api/v1/students/me/skills
POST   /api/v1/students/me/skills
PUT    /api/v1/students/me/skills
DELETE /api/v1/students/me/skills

GET    /api/v1/students/me/cv
POST   /api/v1/students/me/cv
DELETE /api/v1/students/me/cv
```