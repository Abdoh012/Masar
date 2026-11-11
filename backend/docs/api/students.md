# Students API

The Students API manages the authenticated student's profile, academic information, and skills.

## 1. Get Current Student Header

`GET /api/v1/students/me` — returns only `data.header` (thirteen fields: id, academic_status, is_profile_complete, updated_at, bio, field, profile_image_url, profile_image_file_id, full_name, specialization, university, faculty, member_since).

## 2. Create Student Profile

`POST /api/v1/students/profile` — name-based academic fields; optional `skills` array and `cv_file_id`.

## 3. Update Student Profile

`PUT /api/v1/students/profile` — partial update of profile fields; `skills` array and `cv_file_id` accepted.

## 4. Get Profile Completion Status

`GET /api/v1/students/profile/status` — returns `completed`, `missing_fields`, `completion_percentage` (based on field_id, specialization_id, skills, and CV).

## 5. Complete Student Profile

`POST /api/v1/students/profile/complete` — completes the profile in one request.

## 6. Get My Skills

`GET /api/v1/students/me/skills` — lists the authenticated student's current skills (names), alphabetically.

## 7. Add a Skill

`POST /api/v1/students/me/skills` — body `{"skill": <id>}` (taxonomy lookup ID, integer). Returns 201, or 409 if already held, 422 if unknown.

## 8. Replace All Skills

`PUT /api/v1/students/me/skills` — body `{"skills": [<id>, ...]}`. At most 50, at least 1. Unknown id rejects the whole request.

## 9. Remove a Skill

`DELETE /api/v1/students/me/skills` — body `{"skill": <id>}`. Returns 200 or 404.

## 10. Get / Update the Full Name

`GET/PUT /api/v1/students/me/full-name`.

## 11. Get / Update Contact Information

`GET/PUT /api/v1/students/me/contact` — phone, website, external_profile, address, email.

`GET` returns exactly `{ email, phone, website, external_profile, address }`;
unset optional fields are `null`.

`PUT` is a partial update: only the fields present in the request are
validated and written, and every omitted field keeps its stored value. The five
fields are independent — `email`, `phone`, `website`, `external_profile` and
`address` can each be updated on its own.

```http
PUT /api/v1/students/me/contact
{"phone": "+201112223333"}
```

```http
PUT /api/v1/students/me/contact
{"website": "https://new-example.com"}
```

Optional fields (`phone`, `website`, `external_profile`, `address`) can be
cleared with `null`, an empty string or a whitespace-only string. `website` and
`external_profile` must be valid URLs of at most 255 characters; `address` is
limited to 500 characters; `phone` allows digits, spaces and `+ ( ) - .` with at
least one digit, up to 50 characters.

The email (`users.email`) is validated only when it is supplied:

- Omitted — no email validation runs and the stored email is untouched.
- Identical to the account's current email — accepted as a no-op, so
  resubmitting the whole contact form while changing another field succeeds.
  `users.email_verified_at` is preserved because the address did not change.
- A new, unused address — written, and `users.email_verified_at` is cleared
  because the new address has not been proven.
- An address owned by a different account — rejected as `422` with an `email`
  validation error. The uniqueness check always excludes the authenticated
  user's own id.

No password is required for an email change; password changes remain the
separate `POST /api/v1/auth/change-password` flow.

## 12. Get / Update Academic Status

```http
GET /api/v1/students/me/academic-status
PUT /api/v1/students/me/academic-status
DELETE /api/v1/students/me/academic-status/certificate
```

The profile-level Student / Graduate state (`students.academic_status`,
`enum('student','graduate')`, default `student`). A graduate also stores a
graduation date (`students.graduation_date`) and a graduation certificate
(`students.academic_certificate_file_id`, uploaded through the shared file
upload module).

### Response (student)

```json
{
    "success": true,
    "message": null,
    "data": {
        "academic_status": "student"
    },
    "errors": null
}
```

### Response (graduate)

```json
{
    "success": true,
    "message": null,
    "data": {
        "academic_status": "graduate",
        "graduation_date": "2025-06-15",
        "graduation_certificate": {
            "id": 123,
            "file_name": "graduation_certificate.pdf",
            "file_url": "http://localhost/Masar/backend/api/v1/files/123?download=true"
        }
    },
    "errors": null
}
```

`graduation_certificate` is only present when a certificate file is on file and
still resolves to a file the student owns. `is_graduate`, `graduation_year`,
`graduation_month` and `graduation_month_name` are no longer returned.

### Request (PUT, multipart/form-data)

For `student`:

```
academic_status = student
```

For `graduate`:

```
academic_status = graduate
graduation_date = 2026-06-30
graduation_certificate = [Uploaded PDF or Image]
```

`graduation_date` (YYYY-MM-DD) and `graduation_certificate` (pdf/png/jpg/jpeg,
up to 5 MB) are both required when the status is `graduate`.

### Delete certificate

`DELETE /api/v1/students/me/academic-status/certificate` removes the authenticated
student's graduation certificate: verifies ownership, removes the physical file
and its record, then clears the student reference. Returns `200` on success and
`404` when no certificate exists.

### Relationship to training eligibility

`students.academic_status` is **not** a training-eligibility input.

## 13. Get Profile Views

`GET /api/v1/students/me/profile-views`.

## 14. Education Records

`/api/v1/students/me/education` — list/add/update/delete.

## 15. Profile Image

`POST/DELETE /api/v1/students/me/profile-image` (multipart, field `image`).

## 16. Standard Errors

Standard error envelope and validation errors.

## Endpoint Summary

```
GET    /api/v1/students/me
POST   /api/v1/students/profile
PUT    /api/v1/students/profile
GET    /api/v1/students/profile/status
POST   /api/v1/students/profile/complete

GET    /api/v1/students/me/skills
POST   /api/v1/students/me/skills
PUT    /api/v1/students/me/skills
DELETE /api/v1/students/me/skills

GET    /api/v1/students/me/full-name
PUT    /api/v1/students/me/full-name

GET    /api/v1/students/me/username
PUT    /api/v1/students/me/username

GET    /api/v1/students/me/contact
PUT    /api/v1/students/me/contact

GET    /api/v1/students/me/academic-status
PUT    /api/v1/students/me/academic-status
DELETE /api/v1/students/me/academic-status/certificate

GET    /api/v1/students/me/profile-views

GET    /api/v1/students/me/education
POST   /api/v1/students/me/education
PUT    /api/v1/students/me/education/{id}
DELETE /api/v1/students/me/education/{id}

POST   /api/v1/students/me/profile-image
DELETE /api/v1/students/me/profile-image
```

## Related: Password Changes

See `authentication.md`.

## Related: Company Candidate Filtering

See `search.md`.
