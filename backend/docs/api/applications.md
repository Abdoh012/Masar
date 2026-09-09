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
GET    /api/v1/applications/applied         Student's applied (pending) applications only
GET    /api/v1/applications/accepted        Student's accepted applications only
GET    /api/v1/applications/rejected        Student's rejected applications only
GET    /api/v1/applications/withdrawn       Student's withdrawn applications only
GET    /api/v1/applications/all             Student's applications across all four states
GET    /api/v1/applications/{id}            Application detail (student/company/admin)
GET    /api/v1/applications/{id}/cv         Download the application CV (student/company/admin)
GET    /api/v1/applications?training_id=..  Company's applications for one training
POST   /api/v1/applications/withdraw        Student withdraws a pending application
POST   /api/v1/applications/accept          Company accepts a pending application
POST   /api/v1/applications/reject          Company rejects a pending application
POST   /api/v1/applications/{id}/payment/confirm
                                            Company confirms the manual (bank transfer)
                                            payment for an accepted paid training
POST   /api/v1/applications/{id}/payment    Student submits the bank transfer reference
                                            (creates/updates one pending payment row)
GET    /api/v1/applications/{id}/payment    Student reads the manual payment lifecycle
                                            state (pending/paid) for their application
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

# 1.1 List Applied Applications

Dedicated endpoint for the frontend Applied tab. Returns only the authenticated
student's applications with status pending/submitted (the DB status `submitted` is
exposed as `pending`) **and scoped to the student's own specialization** — the
training's `training_listings.specialization_id` must match the student's
`specialization_id`, the same rule used by the Trainings List. A student with no
specialization set receives an empty result set.

### Endpoint

```http
GET /api/v1/applications/applied
```

### Authorization

```text
student
```

### Query Parameters

```text
page
per_page
```

The status filter is fixed server-side (`pending`) — the client cannot override it with
a `status` query parameter. The response keeps the same `data.items` + `data.pagination`
envelope as the other tab endpoints, but each item is shaped as a clean **Applied Card DTO**
for the frontend Applied tab.

### Response

Every item contains exactly the following fields (no application PII or detail fields
such as `full_name`, `email`, `phone`, `why_interested`, `skills`, rejection info, CV,
or academic fields are exposed):

```json
{
    "success": true,
    "message": "Applications retrieved successfully.",
    "data": {
        "items": [
            {
                "id": 1881,
                "training_id": 100247,
                "training_title": "Security Operations Center (SOC) Internship",
                "status": "Applied",
                "specialization": "Cybersecurity",
                "company_name": "NileTech Solutions",
                "company_logo": null,
                "training_type": "hands_on",
                "method": "onsite",
                "is_paid": true,
                "may_lead_to_hire": true,
                "applied_at": "2026-09-08T03:20:20+03:00",
                "starts_at": "2026-10-01T00:00:00+03:00",
                "ends_at": "2026-12-31T00:00:00+03:00",
                "duration": 113,
                "can_withdraw": true
            }
        ],
        "pagination": {
            "page": 1,
            "per_page": 20,
            "total": 1
        }
    }
}
```

Field notes:

* `status` is always `"Applied"` for items on this endpoint.
* Results are scoped to the student's specialization: only pending applications for
  trainings whose `training_listings.specialization_id` matches the student's own
  specialization are returned. This uses the same source-of-truth
  (`training_listings.specialization_id`) as the Trainings List and Saved Trainings
  filters. A student with no specialization set receives an empty result set (no error).
* `specialization` is the training's specialization name; `training_type` and `method`
  pass through the training's stored enum values (`shadowing`/`hands_on`/`project_based`
  and `onsite`/`remote`/`hybrid`).
* `is_paid` is a boolean; `may_lead_to_hire` equals `is_paid` (the badge is driven by the
  paid flag, not by the training's `may_lead_to_employment` field).
* `applied_at`, `starts_at`, `ends_at` are ISO-8601 strings with timezone offset
  (application timezone `Africa/Cairo`, e.g. `+03:00`).
* `duration` is the remaining calendar days until `ends_at` (`0` on/after the end date,
  `null` when the end date is missing) — same helper used by the training cards.
* `can_withdraw` is `true` while the application is pending/submitted, matching the
  withdraw business rule.

### Frontend tabs mapping

```text
All       -> GET /api/v1/applications/all
Applied   -> GET /api/v1/applications/applied
Accepted  -> GET /api/v1/applications/accepted
Rejected  -> GET /api/v1/applications/rejected
Withdrawn -> GET /api/v1/applications/withdrawn
```

---

# 1.2 List Accepted Applications

Dedicated endpoint for the frontend Accepted tab. Returns only the authenticated
student's applications with status `accepted` **and scoped to the student's own
specialization** — the training's `training_listings.specialization_id` must match
the student's `specialization_id`, the same rule used by the Trainings List and the
Applied tab. A student with no specialization set receives an empty result set.

### Endpoint

```http
GET /api/v1/applications/accepted
```

### Authorization

```text
student
```

### Query Parameters

```text
page
per_page
```

The status filter is fixed server-side (`accepted`) — the client cannot override it
with a `status` query parameter. The response keeps the same `data.items` +
`data.pagination` envelope as `/api/v1/applications/applied`, but each item is shaped
as a clean **Accepted Card DTO** for the frontend Accepted tab.

### Response

Every item contains exactly the following fields (no application PII or detail fields
such as `full_name`, `email`, `phone`, `why_interested`, `skills`, rejection info, CV,
or academic fields are exposed):

```json
{
    "success": true,
    "message": "Applications retrieved successfully.",
    "data": {
        "items": [
            {
                "id": 1882,
                "training_id": 100262,
                "training_title": "Machine Learning API Deployment",
                "status": "Accepted",
                "specialization": "Backend Development",
                "company_name": "Alexandria Digital Labs",
                "company_logo": null,
                "training_type": "project_based",
                "method": "hybrid",
                "is_paid": true,
                "may_lead_to_hire": true,
                "accepted_at": "2026-09-08T03:20:22+03:00",
                "starts_at": "2026-09-15T09:00:00+03:00",
                "ends_at": "2026-10-17T17:00:00+03:00",
                "duration": 39,
                "free_trial_days": 14,
                "free_trial_days_remaining": 14,
                "motivational_message": "Small daily steps build lasting skills - keep going.",
                "payment_status": "pending",
                "bank_account": {
                    "bank_name": "Alexandria Bank of Industry",
                    "account_name": "Alexandria Digital Labs",
                    "account_number": "DEMO-002-3155-7788-2",
                    "instructions": "Place the applicant full name in the transfer reference. Confirmations are posted within one business day."
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1,
            "total_pages": 1,
            "has_next_page": false,
            "has_previous_page": false
        }
    }
}
```

Field notes:

* `status` is always `"Accepted"` for items on this endpoint.
* Results are scoped to the student's specialization, using the same
  `training_listings.specialization_id` source of truth as the Trainings List, Saved
  Trainings and Applied filters. A student with no specialization set receives an
  empty result set (no error). Accepted applications for trainings that do not match
  the student's specialization are excluded.
* `accepted_at` is the acceptance timestamp from `training_applications.reviewed_at`
  (set when the company accepts), formatted as an ISO-8601 string with timezone
  offset (application timezone `Africa/Cairo`, e.g. `+03:00`).
* `is_paid` is a boolean; `may_lead_to_hire` equals `is_paid` (the badge is driven by
  the paid flag, not by the training's `may_lead_to_employment` field).
* `free_trial_days` equals the training's `trial_period_days` for paid trainings and
  is `null` for free trainings. `free_trial_days_remaining` counts the free-trial days
  left: it is the full trial until the training's `starts_at` date, then decrements
  day by day and never goes below `0` (it becomes `0` once the trial period is over).
  Free trainings leave it `null`.
* `motivational_message` is a short deterministic encouragement picked from a fixed
  pool keyed by the application timezone date — every student sees the same message
  on a given day (no external AI or per-user content).
* `payment_status` exposes the manual bank-transfer lifecycle: `not_required` for
  free trainings, `pending` for paid trainings the company has not confirmed yet,
  and `paid` once the company confirms the transfer (see §1.6). It is derived from
  the owning `payments` table (latest row per training + student) and the training's
  `is_paid` flag.
* `bank_account` is the owning company's transfer destination, only for paid
  trainings whose company has configured banking details on the `companies` table
  (`bank_name`, `bank_account_name`, `bank_account_number`,
  `bank_transfer_instructions`); it is `null` for free trainings and for paid
  trainings whose company has not configured banking data yet. It tells the student
  WHERE to pay — it is not a confirmation (see `payment_status`).
* `duration` is the remaining calendar days until `ends_at` (`0` on/after the end
  date, `null` when the end date is missing) — same helper used by the training cards.

---

# 1.3 List Rejected Applications

Dedicated endpoint for the frontend Rejected tab. Returns only the authenticated
student's applications with status `rejected` **and scoped to the student's own
specialization** — the training's `training_listings.specialization_id` must match
the student's `specialization_id`, the same rule used by the Trainings List, the
Applied and the Accepted tabs. A student with no specialization set receives an
empty result set.

### Endpoint

```http
GET /api/v1/applications/rejected
```

### Authorization

```text
student
```

Guest requests get `401 Unauthorized`; company/other roles get `403 Forbidden`.

### Query Parameters

```text
page
per_page
```

The status filter is fixed server-side (`rejected`) — the client cannot override it
with a `status` query parameter. The response keeps the same `data.items` +
`data.pagination` envelope as `/api/v1/applications/applied` and `/api/v1/applications/accepted`.

### Response

Every item contains exactly the following 12 fields (no application PII or detail
fields such as `full_name`, `email`, `phone`, `why_interested`, `skills`, academic
fields, CV, or `withdrawn_at` are exposed):

```json
{
    "success": true,
    "message": "Applications retrieved successfully.",
    "data": {
        "items": [
            {
                "id": 1883,
                "training_id": 100264,
                "training_title": "Database Design for Analytics",
                "status": "Rejected",
                "specialization": "Backend Development",
                "company_name": "TestHire Solutions",
                "company_logo": null,
                "training_type": "shadowing",
                "method": "onsite",
                "rejected_at": "2026-09-08T03:20:22+03:00",
                "rejection_reason": "training_closed",
                "rejection_note": "This training has been discontinued."
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1,
            "total_pages": 1,
            "has_next_page": false,
            "has_previous_page": false
        }
    }
}
```

Field notes:

* `status` is a **fixed frontend display value**: it is always `"Rejected"` on this
  endpoint, never the raw database value `rejected`.
* `specialization` is the training's specialization name; `training_type` and `method`
  pass through the training's stored enum values (`shadowing`/`hands_on`/`project_based`
  and `onsite`/`remote`/`hybrid`).
* `rejected_at` is the **rejection timestamp from `training_applications.reviewed_at`**
  (set when the company rejects the application) — it is NOT `applied_at` — formatted
  as an ISO-8601 string with timezone offset (application timezone `Africa/Cairo`,
  e.g. `+03:00`).
* `rejection_reason` is the stored reason code (e.g. `requirements_not_met`,
  `position_filled`, `candidate_not_suitable`, `training_closed`, `other`).
* `rejection_note` is the optional human-readable note from the company and **may be
  `null`**.
* Results are ordered with the **newest rejection first** (`reviewed_at DESC`).
* This is a summary view: it answers "which trainings did this student apply to,
  which were rejected, when, by which training/company, and why?" — it never exposes
  the complete application record.

---

# 1.4 List Withdrawn Applications

Dedicated endpoint for the frontend Withdrawn tab. Returns only the authenticated
student's applications with status `withdrawn` **and scoped to the student's own
specialization** — the training's `training_listings.specialization_id` must match
the student's `specialization_id`, the same rule used by the Trainings List, the
Applied, the Accepted and the Rejected tabs. A student with no specialization set
receives an empty result set.

### Endpoint

```http
GET /api/v1/applications/withdrawn
```

### Authorization

```text
student
```

Guest requests get `401 Unauthorized`; company/other roles get `403 Forbidden`.

### Query Parameters

```text
page
per_page
```

The status filter is fixed server-side (`withdrawn`) — the client cannot override it
with a `status` query parameter. The response keeps the same `data.items` +
`data.pagination` envelope as `/api/v1/applications/applied`,
`/api/v1/applications/accepted` and `/api/v1/applications/rejected`.

### Response

Every item contains exactly the following 12 fields (no application PII or detail
fields such as `full_name`, `email`, `phone`, `why_interested`, `skills`, academic
fields, CV, or rejection info are exposed):

```json
{
    "success": true,
    "message": "Applications retrieved successfully.",
    "data": {
        "items": [
            {
                "id": 1884,
                "training_id": 100256,
                "training_title": "PostgreSQL Power User Track",
                "status": "Withdrawn",
                "specialization": "Backend Development",
                "company_name": "FutureWorks Software",
                "company_logo": null,
                "training_type": "project_based",
                "method": "onsite",
                "withdrawn_at": "2026-09-08T03:20:23+03:00",
                "starts_at": "2026-09-09T09:00:00+03:00",
                "ends_at": "2026-10-25T17:00:00+03:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 1,
            "total_pages": 1,
            "has_next_page": false,
            "has_previous_page": false
        }
    }
}
```

Field notes:

* `status` is a **fixed frontend display value**: it is always `"Withdrawn"` on this
  endpoint, never the raw database value `withdrawn`.
* `specialization` is the training's specialization name; `training_type` and `method`
  pass through the training's stored enum values (`shadowing`/`hands_on`/`project_based`
  and `onsite`/`remote`/`hybrid`).
* `withdrawn_at` is the **withdrawal timestamp from `training_applications.withdrawn_at`**
  (set when the student withdraws via `POST /api/v1/applications/withdraw`) — it is NOT
  `applied_at` — formatted as an ISO-8601 string with timezone offset (application
  timezone `Africa/Cairo`, e.g. `+03:00`).
* `starts_at` / `ends_at` are the training listing timestamps (ISO-8601, same helpers
  used by the other card DTOs).
* Results are ordered with the **newest withdrawal first** (`withdrawn_at DESC`, ties
  broken by `applied_at DESC`).
* This is a summary view: it answers "which trainings did this student withdraw from,
  when, and by which training/company?" — it never exposes the complete application
  record.

---

# 1.5 List All Applications

Dedicated endpoint for the frontend All tab. Returns **every** application of the
authenticated student across the four tab states in one unified, paginated list —
Applied (status `submitted`), Accepted, Rejected and Withdrawn — **scoped to the
student's own specialization**, the same rule used by the Trainings List and all four
tab endpoints: the training's `training_listings.specialization_id` must match the
student's `specialization_id`. A student with no specialization set receives an empty
result set (success, no error).

### Endpoint

```http
GET /api/v1/applications/all
```

### Authorization

```text
student
```

Guest requests get `401 Unauthorized`; company/other roles get `403 Forbidden`.

### Query Parameters

```text
page
per_page
```

The status set is fixed server-side (the four tab states) — the client cannot
override it with a `status` query parameter. The response keeps the same
`data.items` + `data.pagination` envelope as the four tab endpoints. `total` is the
number of eligible applications across **all four statuses**.

### Response

The list is a **merged union of the four tab card DTOs**: every item keeps the common
card fields (`id`, `training_id`, `training_title`, `status`, `specialization`,
`company_name`, `company_logo`, `training_type`, `method`) plus exactly the
status-specific fields of its own tab card — Applied items expose the 16-field Applied
Card, Accepted items the 20-field Accepted Card, Rejected items the 12-field Rejected
Card, Withdrawn items the 12-field Withdrawn Card. No application PII or detail fields
(`full_name`, `email`, `phone`, `why_interested`, `skills`, academic fields, CV,
`student_id`, `company_id`, raw `reviewed_at`, …) are ever exposed.

```json
{
    "success": true,
    "message": "Applications retrieved successfully.",
    "data": {
        "items": [
            {
                "id": 1885,
                "training_id": 100255,
                "training_title": "Secure DevOps Pipeline",
                "status": "Withdrawn",
                "specialization": "Backend Development",
                "company_name": "NileWorks",
                "company_logo": null,
                "training_type": "project_based",
                "method": "onsite",
                "withdrawn_at": "2026-09-08T03:20:23+03:00",
                "starts_at": "2026-10-04T09:00:00+03:00",
                "ends_at": "2026-11-18T17:00:00+02:00"
            },
            {
                "id": 1883,
                "training_id": 100264,
                "training_title": "Database Design for Analytics",
                "status": "Rejected",
                "specialization": "Backend Development",
                "company_name": "TestHire Solutions",
                "company_logo": null,
                "training_type": "shadowing",
                "method": "onsite",
                "rejected_at": "2026-09-08T03:20:22+03:00",
                "rejection_reason": "training_closed",
                "rejection_note": "This training has been discontinued."
            },
            {
                "id": 1882,
                "training_id": 100262,
                "training_title": "Machine Learning API Deployment",
                "status": "Accepted",
                "specialization": "Backend Development",
                "company_name": "Alexandria Digital Labs",
                "company_logo": null,
                "training_type": "project_based",
                "method": "hybrid",
                "is_paid": true,
                "may_lead_to_hire": true,
                "accepted_at": "2026-09-08T03:20:22+03:00",
                "starts_at": "2026-09-15T09:00:00+03:00",
                "ends_at": "2026-10-17T17:00:00+03:00",
                "duration": 39,
                "free_trial_days": 14,
                "free_trial_days_remaining": 14,
                "motivational_message": "Small daily steps build lasting skills - keep going.",
                "payment_status": "pending",
                "bank_account": {
                    "bank_name": "Alexandria Bank of Industry",
                    "account_name": "Alexandria Digital Labs",
                    "account_number": "DEMO-002-3155-7788-2",
                    "instructions": "Place the applicant full name in the transfer reference. Confirmations are posted within one business day."
                }
            },
            {
                "id": 1881,
                "training_id": 100247,
                "training_title": "Security Operations Center (SOC) Internship",
                "status": "Applied",
                "specialization": "Backend Development",
                "company_name": "NileTech Solutions",
                "company_logo": null,
                "training_type": "hands_on",
                "method": "onsite",
                "is_paid": true,
                "may_lead_to_hire": true,
                "applied_at": "2026-09-08T03:20:20+03:00",
                "starts_at": "2026-10-01T00:00:00+03:00",
                "ends_at": "2026-12-31T00:00:00+03:00",
                "duration": 113,
                "can_withdraw": true
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 4,
            "total_pages": 1,
            "has_next_page": false,
            "has_previous_page": false
        }
    }
}
```

Field notes:

* `status` is the **frontend display value per tab card**: Applied, Accepted, Rejected
  or Withdrawn — never the raw database value (`submitted` / `accepted` / `rejected` /
  `withdrawn`).
* Each item is shaped by the **same card function its tab endpoint uses**, so the All
  tab and the four tab tabs never drift: a merged list item is byte-identical in shape
  to the same application returned by its dedicated endpoint.
* Results are scoped to the student's specialization (`training_listings.specialization_id`,
  the same source of truth used by the Trainings List and the Applied/Accepted/Rejected/
  Withdrawn tabs). A student with no specialization set receives an empty result set
  (no error). The scoping happens at the SQL/repository level — out-of-specialization
  applications of any status are never returned.
* Ordering is **deterministic newest-activity-first across all statuses**: for each
  application the effective activity timestamp is `withdrawn_at` (Withdrawn),
  `reviewed_at` for Accepted/Rejected (when the company decided), and `applied_at` for
  still-pending ones. The list sorts by
  `GREATEST(withdrawn_at, reviewed_at, applied_at) DESC`, tie-broken by `id DESC` so
  pagination is stable.
* The per-status fields keep their exact tab semantics: `applied_at`/`accepted_at`/
  `rejected_at`/`withdrawn_at` are ISO-8601 with timezone offset; `can_withdraw` is
  `true` only for pending applications; Rejected items may carry `null`
  `rejection_reason` / `rejection_note`; Accepted items carry the `free_trial_*`,
  `payment_status` and `bank_account` fields from §1.2, including the same payment
  enrichment (the payment map is fetched once for the whole page).

---

# 1.6 Manual Payment (Bank Transfer)

The manual (bank transfer) lifecycle for **paid accepted trainings** has four steps:

1. The student transfers the fee to the owning company's `bank_account` (shown by the
   Accepted Card, §1.2).
2. The student **submits the transfer reference** (`POST .../{id}/payment`). This
   creates the `payments` row as `status=pending` (or updates the reference on the
   existing pending row) — it never marks the row paid.
3. The owning company **confirms receipt** (`POST .../{id}/payment/confirm`), which
   promotes the row to `status=paid`, sets `paid_at`, and flips that training's
   `payment_status` in the student's Accepted ledger (§1.3 and §1.2) to `paid`.
4. The student **reads the state** (`GET .../{id}/payment`) and sees `pending` until
   step 3, then `paid`.

The fee recorded on the `payments` row comes from the training's own
`compensation_amount` / `compensation_currency` (default currency `EGP`). There is
**no payment gateway** — this is a purely manual, company-confirmed flow.

The application status is NEVER changed by any payment endpoint. A student who used up
the free trial without paying is blocked from continuing by the session/trial logic and
is never auto-withdrawn by the payment machine.

### Submit the transfer reference (Student)

```http
POST /api/v1/applications/{applicationId}/payment
Content-Type: application/json

{
    "reference": "MANUAL-20260902-10001"
}
```

`reference` is required, trimmed, and limited to 255 characters (the `external_reference`
column on the `payments` row). The bank given to the student is the owning company's
transfer destination; always state the transfer reference the student should place in the
bank transfer.

#### Authorization

Required role:

```text
student
```

The student must be the **owner** of the application
(`training_applications.student_id == students.id` of the authenticated user).

#### Rules enforced (in order)

```text
404  student profile not found / application not found
409  application is not accepted
403  the application does not belong to the authenticated student
422  the training is not paid (or has no fee configured)
409  payment already confirmed (status=paid) — a reference cannot be changed after paid
```

The write is **idempotent**: submitting again for a pending row updates that same row's
reference — a refresh never creates a duplicate `payments` row. The amount, currency,
commission and method are computed server-side from the training
(`compensation_amount`/`compensation_currency`, `platform_commission_rate = 0.00`,
`payment_method = manual`). No client-supplied amount is ever trusted.

#### Response

```json
{
    "success": true,
    "message": "Payment reference submitted successfully. Payment is pending verification.",
    "data": {
        "application_id": 1882,
        "training_id": 100262,
        "payment_id": 52,
        "status": "pending",
        "payment_method": "manual",
        "reference": "MANUAL-20260902-10001",
        "amount": 3600,
        "currency": "EGP",
        "paid_at": null
    }
}
```

### Read the payment state (Student)

```http
GET /api/v1/applications/{applicationId}/payment
```

Student-only; the application must belong to the authenticated student. Returns the
latest `payments` row (per training + student) when one exists. Before any reference is
submitted there is no row yet: `payment_id = 0`, `reference = null`, `submitted = false`,
`status = "pending"`, matching the Accepted ledger.

```json
{
    "success": true,
    "message": "Payment retrieved successfully.",
    "data": {
        "application_id": 1882,
        "training_id": 100262,
        "payment_id": 52,
        "status": "pending",
        "payment_method": "manual",
        "reference": "MANUAL-20260902-10001",
        "amount": 3600,
        "currency": "EGP",
        "paid_at": null,
        "submitted": true
    }
}
```

After the company confirms, the same call reports `status = "paid"` with `paid_at` set.

### Confirm the payment (Company)

### Endpoint

```http
POST /api/v1/applications/{applicationId}/payment/confirm
```

### Authorization

Required role:

```text
company
```

The acting company must be the **owner** of the training the application belongs to
(`training_listings.company_id == companies.id` of the authenticated user).

### Rules enforced (in order)

```text
404  company profile not found / application not found
409  application is not accepted
403  the acting company does not own the training
422  the training is not paid (or has no fee configured)
```

A success is **idempotent**: confirming twice returns the same `payment_id` with
`already_confirmed=true` and never creates a duplicate `payments` row. The write
(create or promote the payment row to `paid`) happens in one DB transaction.

### Response

```json
{
    "success": true,
    "message": "Payment confirmed successfully.",
    "data": {
        "application_id": 1882,
        "training_id": 100262,
        "payment_id": 51,
        "status": "paid",
        "payment_method": "manual",
        "amount": 3600,
        "currency": "EGP",
        "paid_at": "2026-09-08 03:43:23",
        "already_confirmed": false
    }
}
```

Repeating the request returns the same shape with `message = "Payment already confirmed."`
and `already_confirmed = true`.

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
        "full_name": "Student Two",
        "email": "stu2@test.local",
        "phone": "01012345678",
        "city": "Cairo",
        "address": "12 Nile Street",
        "why_interested": "I want hands-on experience in a real team.",
        "what_to_learn": "Modern frontend engineering and mentorship.",
        "skills": ["PHP", "SQL", "Teamwork"],
        "rejection_reason": null,
        "rejection_note": null,
        "applied_at": "2026-08-20 15:20:40",
        "reviewed_at": null,
        "withdrawn_at": null,
        "reviewed_by": null,
        "cv_file_id": 17,
        "university": "Cairo University",
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
                "options": []
            }
        ],
        "university": "Cairo University",
        "faculty_name": "Faculty of Computers and Artificial Intelligence"
    }
}
```

The response is enriched by `application_service_enrich_application`: it attaches the
submitted `answers`, resolves the `faculty_name`, decodes the `skills`
snapshot, and normalizes the status (the DB stores `submitted`, which is exposed as
`pending`). The tab list endpoints (`/api/v1/applications/applied`, `/accepted`,
`/rejected`, `/withdrawn`, `/all`) return card DTOs with `training_title` /
`company_name` but without `answers` / `faculty_name`; their status values are
displayed as tab labels. The university is stored and returned as free text
(`university`), so no `university_name` lookup field is produced.

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
    "current_status": "student",
    "full_name": "Student Two",
    "email": "stu2@test.local",
    "phone": "01012345678",
    "city": "Cairo",
    "address": "12 Nile Street",
    "why_interested": "I want hands-on experience in a real team.",
    "what_to_learn": "Modern frontend engineering and mentorship.",
    "skills": ["PHP", "SQL", "Teamwork"],
    "cv_file_id": 17,
    "university": "Cairo University",
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
company_id       ignored      derived automatically from training_id -> training_listings.company_id.
                                        A client-supplied company_id is never trusted and is
                                        overwritten with the real training company.
student_id       ignored      the authenticated student is resolved server-side
current_status   student|graduated       applicant status (canonical input; stored in
                                        applicant_type). Aliased by applicant_type.
full_name        string       optional  snapshot; falls back to the student profile name
email            string       optional  snapshot; falls back to the user's account email
phone            string       optional  max 20 chars
city             string       optional  max 100 chars
address          string       optional  max 500 chars
why_interested   string       required  max 5000 chars
what_to_learn    string       required  max 5000 chars
skills           array        optional  array of skill names (max 50); stored as JSON
cv_file_id       int          optional  must reference a file record owned by the
                                        authenticated student (422 otherwise)
university       string       optional  free-text university name (e.g.
                                        "Cairo University"); max 255 chars.
                                        It is NOT an ID reference anymore.
faculty_id       int          optional  must exist in faculties (422 if not)
applicant_type   student|graduated (alias of current_status)
academic_year    string       required  when current_status is student; max 20 chars
graduation_year  int          required  when current_status is graduated; 1950..2100
motivation       string       optional  max 5000 chars
cover_letter     string       optional  max 10000 chars
answers          array        validated against the training's questions:
                              only questions of this training are accepted;
                              required questions must be answered; select/radio
                              answers must be one of the question's options
```


The `application/json` body with `cv_file_id` continues to work unchanged.
`company_id` is always derived from the selected training in the JSON flow.

### Validation responses

```text
409  duplicate application          "You have already applied for this training opportunity."
409  deadline passed                "The application deadline has passed."
409  training not accepting         "This training opportunity is not accepting applications."
409  capacity reached               "This training opportunity has reached its capacity."
422  invalid faculty             "Selected faculty was not found."
422  invalid cv ownership           "Selected CV file was not found."
422  foreign question answer        answers.<question_id> => "This question does not belong to the selected training."
422  missing required answer        answers.<question_id> => "This question is required."
422  invalid select/radio option    answers.<question_id> => "Selected option is not valid."
422  missing conditional year       graduation_year => "Graduation year is required for graduates."
                                   academic_year => "Academic year is required for students."
```

A **rejected** application may be re-submitted by the same student for the same training. Because
`training_applications` enforces a unique `(training_id, student_id)` constraint, the re-submission
reuses the existing application row: it resets the status to `pending`, clears the rejection and
review data (`rejection_reason`, `rejection_note`, `reviewed_by`, `reviewed_at`), replaces the
snapshot fields, and replaces the previous answers.

### Response

```json
{
    "success": true,
    "message": "Application submitted successfully.",
    "data": {
        "id": 15,
        "training_id": 2,
        "student_id": 100,
        "company_id": 46,
        "full_name": "Student Two",
        "email": "stu2@test.local",
        "phone": "01012345678",
        "city": "Cairo",
        "address": "12 Nile Street",
        "why_interested": "I want hands-on experience in a real team.",
        "what_to_learn": "Modern frontend engineering.",
        "skills": ["PHP", "SQL"],
        "cv_file_id": 17,
        "university": "Cairo University",
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

# 4. Download Application CV

Downloads the CV attached to an application as a binary file.

### Endpoint

```http
GET /api/v1/applications/{applicationId}/cv
```

### Authorization

Access is authorized through the application:

```text
The owning student
The company that owns the training (Application → Training → Company)
An administrator
```

A company can never download the CV of an application belonging to another
company, and it cannot reach arbitrary student files through the general
`GET /api/v1/files/{id}/download` endpoint (that endpoint is owner-only).

### Response

```text
200 OK          binary stream (Content-Disposition: attachment)
401             unauthenticated
403             not allowed to access this CV
404             application / CV not found
```

---

# 5. Withdraw Application

Allows the student to withdraw an eligible application.

### Endpoint

```http
POST /api/v1/applications/withdraw
```

### Request

The application id is passed as a query parameter:

```http
POST /api/v1/applications/withdraw?id=15
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

The application id is passed as a query parameter:

```http
POST /api/v1/applications/accept?id=15
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

The application id is passed as a query parameter:

```http
POST /api/v1/applications/reject?id=15
```

Optional JSON body:

```json
{
    "rejection_reason": "Candidate did not meet minimum requirements",
    "rejection_note": "Optional human-readable detail for the student."
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
GET    /api/v1/applications/all         (student's applications across all four states)
GET    /api/v1/applications/applied     (student's pending applications only)
GET    /api/v1/applications/accepted    (student's accepted applications only)
GET    /api/v1/applications/rejected    (student's rejected applications only)
GET    /api/v1/applications/withdrawn   (student's withdrawn applications only)
GET    /api/v1/applications/{applicationId}
GET    /api/v1/applications/{applicationId}/cv
GET    /api/v1/applications?training_id={trainingId}
POST   /api/v1/applications/withdraw   (query: id)
POST   /api/v1/applications/accept     (query: id)
POST   /api/v1/applications/reject     (query: id, JSON body: rejection_reason, rejection_note)
POST   /api/v1/applications/{applicationId}/payment/confirm   (company manual payment)
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
payments
notifications
audit_logs
```

The primary application record is stored in `training_applications` (source of truth:
`database/schema/masar.sql`). It carries the student snapshot columns used by the
submission endpoint:

```text
company_id       bigint UNSIGNED (nullable; FK -> companies.id, ON DELETE CASCADE).
                 Populated automatically from training_id -> training_listings.company_id
                 when an application is created. Existing legacy rows whose training no
                 longer exists keep NULL.
full_name        varchar(255)
email            varchar(255)
phone            varchar(20)
city             varchar(100)
address          varchar(500)
why_interested   text
what_to_learn    text
skills           text  (JSON array of skill names)
```

Answers to the training questions are stored in `application_answers` and the
student's declared skills are stored as JSON in the `skills` column of the
application record.

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
