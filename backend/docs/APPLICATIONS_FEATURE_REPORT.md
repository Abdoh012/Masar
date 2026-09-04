# MASAR Backend — Applications Feature: Final Report

**Scope:** Backend-only hardening and completion of the existing Applications module: multi-step
submission payload (student snapshot, education, skills, dynamic answers), CV ownership + authorized
CV download, company accept/reject/withdraw flow fixes, strict student/company/admin authorization
with IDOR protection, status normalization, tests, docs and Postman updates.

**Base URL:** `http://localhost/Masar/backend/api/v1` &nbsp;·&nbsp; **DB:** MySQL `masar`
&nbsp;·&nbsp; **Stack:** native PHP 8.2 (procedural), PDO prepared statements, JWT auth.

---

## 1. Summary

Extended and fixed the existing Application module (no new system was created):

- **Submission** `POST /api/v1/applications` now persists a full snapshot
  (`full_name`, `email`, `phone`, `city`, `address`, `why_interested`, `what_to_learn`, `skills`),
  the education fields, and the dynamic `answers` inside a single DB transaction. Snapshot fields
  fall back to the student profile / account email when omitted.
- **Answers** are validated against the training's own questions: foreign `question_id`s are
  rejected, required questions must be answered, and select/radio answers must match the question's
  options (options are decoded from JSON in responses).
- **CV ownership**: `cv_file_id` is validated to be owned by the authenticated student; a new
  `GET /api/v1/applications/{id}/cv` endpoint streams the CV for the owning student, the owning
  company (via Application → Training → Company) or an admin.
- **Company/student flow fixes**: accept/reject/withdraw resolve ownership server-side; the missing
  `application_repository_find_company_by_user_id` (which made accept/reject and the company list
  fatal in production) was added; `rejection_reason` (DB enum) is mapped from the human presets and
  the human text (or a custom `rejection_note`) is stored in `rejection_note`.
- **Status normalization**: the DB `submitted` status is exposed as `pending` consistently across
  list/detail responses, and `submitted`/`pending` are both accepted in status-gated operations.
- Verified with a 13-test PHPUnit integration suite (63 assertions), a full-suite run, and a live
  HTTP pass covering all endpoints and authorization/IDOR cases.

## 2. Deliverables

1. Schema extended (`database/schema/masar.sql`) **and** applied to the live DB.
2. Application validator/repository/service/controller updated for the full payload + fixes.
3. `routes/applications.php` updated: detail route now `middleware_auth()`; new CV route.
4. New PHPUnit integration suite `tests/ApplicationFlowTest.php` (13 tests, 63 assertions).
5. Docs rewritten/updated: `docs/api/applications.md`.
6. Postman `MASAR.json` updated: Applications folder now has 8 requests (added company list,
   accept, reject, CV download) and the Create Application body carries the new snapshot fields.

## 3. Files Changed

| File | Change |
|---|---|
| `database/schema/masar.sql` | `training_applications` gained the 8 snapshot columns (see section 4) |
| `app/modules/training/validators/application_validator.php` | `current_status`, snapshot fields (full_name/email optional-but-validated, phone/city/address), `why_interested`/`what_to_learn` required, `skills` array rules, conditional `academic_year`/`graduation_year`, answers validator rejects foreign question ids and enforces required/options |
| `app/modules/training/repositories/application_repository.php` | `create` inserts snapshot columns (skills JSON); new `application_repository_find_student_by_id`; `find_student_by_user_id` LEFT JOINs users for `user_email`; **added missing `application_repository_find_company_by_user_id`**; `get_answers` decodes `options` JSON; `reject` maps preset reasons → enum codes, stores human/custom text in `rejection_note`, accepts `reviewed_by`; `get_by_student` adds `company_name` |
| `app/modules/training/services/application_service.php` | create: CV ownership, `current_status` alias, snapshot fallbacks, enriched response (skills decoded + answers); accept/reject/withdraw: ownership via `training_repository_find_by_id` + `training.company_id`, status accepts `submitted`/`pending`, accept capacity uses `$training['capacity']`, `$updated` fetch restored, notify uses the resolved student; admin branch via `is_admin_role()`; list status normalization; new `application_service_cv` |
| `app/modules/training/controllers/application_controller.php` | new `application_controller_cv` (auth + service call) |
| `routes/applications.php` | detail route changed `middleware_student()` → `middleware_auth()`; new `GET /api/v1/applications/{id}/cv` streaming route |
| `tests/bootstrap.php` | loads application/training module files |
| `tests/ApplicationFlowTest.php` (new) | 13 integration tests / 63 assertions (see section 11) |
| `docs/api/applications.md` | rewritten to the final contract (payload, validation, CV endpoint, id as query param) |
| `MASAR.json` | Applications folder: +4 requests; Create Application body extended (still valid JSON, CRLF) |
| `APPLICATIONS_FEATURE_REPORT.md` | this report |

All changed PHP files pass `php -l`.

## 4. Database / Schema Changes

Applied to `database/schema/masar.sql` and the live `training_applications` table (verified via
`SHOW COLUMNS`):

```text
full_name        varchar(255)
email            varchar(255)
phone            varchar(20)
city             varchar(100)
address          varchar(500)
why_interested   text
what_to_learn    text
skills           text   -- JSON array of skill names
```

No migration file was created/edited: the repo's migration directory is empty and the source of
truth is `database/schema/masar.sql` + the live DB (per `docs/AGENTS.md` §30).

## 5. API Endpoints

```text
POST   /api/v1/applications                          student submits an application
GET    /api/v1/applications/my                       student's own applications
GET    /api/v1/applications/{id}                     detail (owning student / owning company / admin)
GET    /api/v1/applications/{id}/cv                  download CV (same authorization as detail)
GET    /api/v1/applications?training_id={id}         owning company lists one training's applications
POST   /api/v1/applications/withdraw?id={id}         student withdraws a pending application
POST   /api/v1/applications/accept?id={id}           owning company accepts a pending application
POST   /api/v1/applications/reject?id={id}           owning company rejects (JSON body: reason + note)
```

All require authentication; `/my` and `withdraw` require `student`, the list/accept/reject require
`company`, and role-agnostic routes enforce the access inside the service layer.

## 6. Business Rules

- A student may apply once per training (409 otherwise); a student cannot apply to a draft training,
  a training past its deadline, or one at capacity (409).
- Only a pending (`submitted`/`pending`) application can be withdrawn, accepted or rejected (409).
- Accept increments applied capacity; a training at capacity can no longer be accepted into.
- Rejection requires one of the preset human reasons; the DB `rejection_reason` enum stores the
  mapped code and `rejection_note` stores the human preset or a custom company note.
- List/detail responses expose the DB `submitted` status as `pending`.

## 7. Validation Rules

- `training_id` required; `current_status` in `student|graduated` (aliased by `applicant_type`);
  `why_interested` and `what_to_learn` required (≤ 5000); `skills` array ≤ 50 of non-empty strings
  ≤ 100 chars; phone ≤ 20, city ≤ 100, address ≤ 500; `academic_year` required for students,
  `graduation_year` (1950..2100) required for graduates.
- `full_name`/`email` are validated when present and fall back to the profile when absent.
- `cv_file_id` must be a file owned by the authenticated student (422 otherwise).
- `university_id`/`faculty_id` must exist (422 otherwise).
- Answers: every `question_id` must belong to the selected training; required questions must be
  answered; select/radio answers must be a valid option; empty-questions-with-answers is rejected.

## 8. Security & Authorization

- `student_id`, `company_id`, and `training_id` are never trusted from the client — the student is
  resolved from the JWT and the company/training ownership from the DB.
- Detail and CV access: owning student, owning company (Application → Training → Company), or admin
  — enforced in the service layer, so the routes only require plain auth.
- Company list/accept/reject are gated by `middleware_company` and ownership is re-checked in the
  service (`training.company_id`), preventing cross-company access.
- CV download is scoped to the application (a company can never reach arbitrary student files
  through `GET /api/v1/files/{id}/download`, which remains owner-only).
- No `password_hash`, tokens or secrets are exposed/logged anywhere in the flow.

## 9. Transaction Handling

`application_service_create` inserts the application and all answers inside a single DB
transaction (`db_begin_transaction` / `db_commit` / `db_rollback`) — an answer failure rolls back
the whole submission. Accept/reject/withdraw perform their status update atomically; notify
failures are non-fatal.

## 10. Pre-existing Bugs Fixed

| Bug | Fix |
|---|---|
| `application_repository_find_company_by_user_id` was referenced but **never defined** — accept, reject, company list and company detail were fatal-error broken in production | function added to `application_repository.php` |
| Accept/reject/withdraw resolved ownership from client-supplied ids or the wrong table | ownership resolved via `training_repository_find_by_id` + `training.company_id` |
| `$updated` row was fetched then commented out in accept — response returned stale data | fetch restored |
| Notification recipient resolved from the wrong query (missing student) | new `application_repository_find_student_by_id` |
| Accept used `$training['capacity']` from a malformed training row | capacity read from the resolved training |
| `rejection_reason` was written as the raw human preset into an **enum column** (would error/fail) | repository maps presets → enum codes; human/custom text stored in `rejection_note` |
| `training_questions.options` returned as an un-decoded string | decoded to array in `get_answers` |
| Any `question_id` in `answers` was accepted | answers validator rejects ids not belonging to the training |
| `cv_file_id` accepted without ownership | create validates ownership via `file_repository_find_for_user` |
| `GET /applications/{id}` required a student token (companies/admins got 401/403) | route now `middleware_auth()`; access enforced in service |
| Lists exposed raw `submitted` | status normalized to `pending` |

## 11. Verification Evidence

PHPUnit (`composer test`, PHP 8.4.5, PHPUnit 10.5.64):

- `tests/ApplicationFlowTest.php`: **13 tests / 63 assertions — OK** (create snapshot+answers,
  duplicate 409, foreign question 422, invalid option 422, foreign CV 422, graduate-year 422,
  withdraw, accept by owner, accept by other company 403, accept twice 409, reject stores preset +
  custom note, find ownership, company list ownership, CV download authorization).
- Full suite: **33 tests / 90 assertions — OK** (the 13 `D` incomplete tests and 2 deprecations are
  pre-existing in `tests/MASAR/Modules/Training/ApplicationServiceTest.php`).
- `php -l` clean on every changed PHP file.

Live HTTP pass against Laragon Apache/MySQL (PASS on every check):

- student `/my` returns normalized `pending`, `training_title`, `company_name`; detail has answers
  with decoded options.
- IDOR: another student gets 403 on detail and CV; unauth gets 401; student gets 403 on the company
  list; company CV of a no-CV application → 404.
- CV download streams binary (`200`, `application/pdf`, `Content-Disposition: attachment`) for the
  owning student, the owning company and admin.
- Create: full snapshot + answers → 201; duplicate → 409; foreign question / invalid select option /
  missing required answer / foreign CV → 422; company accept → 200; accept twice → 409; reject with
  preset + custom note → 200 with `rejection_reason=requirements_not_met` and the custom note stored.
- Company list for `training_id=2` returns its applications with no `submitted` leaking.

## 12. Documentation Updated

`docs/api/applications.md`: implemented-endpoint list (adds the CV endpoint), full create payload +
field table + validation responses, snapshot example, CV download section, id-as-query-parameter for
withdraw/accept/reject, `rejection_note`, related tables with the new snapshot columns.

## 13. Postman Collection (MASAR.json)

- Applications folder: 8 requests (Create, List My, Get By ID, Withdraw, **List Training
  Applications**, **Accept**, **Reject**, **Download Application CV**).
- Create Application body extended with `current_status`, `full_name`, `email`, `phone`, `city`,
  `address`, `why_interested`, `what_to_learn`, `skills`.
- File validated as JSON with CRLF line endings (5641 lines), no duplicate request names.

## 14. Known Limitations / Remaining Gaps

- Physical-file resolution uses the stored `path` as-is (relative legacy rows such as
  `uploads/test_cv.pdf` only resolve when the process CWD makes them resolvable). This mirrors the
  existing files-module behavior (`file_controller_download`); new uploads store absolute paths and
  download correctly. The unit/HTTP verification exercised the absolute-path case.
- The DB's seeded account list does not match `database/seeders/users_seeder.php` credentials; the
  HTTP pass used the live accounts (`student@test.local`, `stu2@test.local`, `companywf1@test.local`,
  `admin@test.local`).
- Reject requires one of the six human preset reasons; arbitrary free-text reasons are rejected at
  the service level by design.

## 16. Addendum — Re-apply After Rejection Fix

A follow-up integration pass found and fixed a latent bug in the create flow:

- **Bug:** `application_service_create` allowed a student whose previous application for the same
  training was `rejected` to re-apply, but the INSERT path then collided with the live DB's unique
  `uq_training_student_application (training_id, student_id)` index, returning a 500
  ("Unable to submit application.").
- **Fix:** the re-application now reuses the existing rejected row. New repository functions
  `application_repository_reapply` (resets status to `submitted`, clears `rejection_reason` /
  `rejection_note` / `reviewed_by` / `reviewed_at`, replaces the snapshot fields and `applied_at`)
  and `application_repository_delete_answers` (replaces the previous answers) are used in the
  existing transaction; the normal INSERT path is unchanged.
- **Test:** `tests/ApplicationFlowTest.php::testRejectedStudentCanReapplyAfterRejection` (new) —
  full suite now **34 tests / 104 assertions — OK** (the 13 incomplete legacy tests and 2
  deprecations are unchanged). Live HTTP pass confirmed create → reject → re-apply returns 201 and
  reuses the same application row with the rejection/review data cleared.

## 15. Test Data & Next Steps

- HTTP verification created throwaway students/applications which were cleaned up afterwards; file
  id 17 was repointed to an existing absolute path so its CV download is functional.
- Recommended next steps: (1) normalize legacy relative file `path` values to absolute during upload
  migration; (2) seed accounts that match `database/seeders/users_seeder.php`; (3) once the frontend
  consumes these endpoints, re-run the Postman collection (all 8 Application requests) against a
  clean environment.