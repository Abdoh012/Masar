# MASAR Backend — Student Trainings Feature: Final Report

**Scope:** Backend-only implementation of the Student Trainings feature: browse/search/filter/sort
training listings, save/favorite trainings, view enriched details, and submit a multi-step application
(education/CV/motivation plus dynamic questions/answers).

**Base URL:** `http://localhost/Masar/backend/api/v1` &nbsp;·&nbsp; **DB:** MySQL `masar` &nbsp;·&nbsp; **Stack:** native PHP 8.2 (procedural), PDO prepared statements.

---

## 1. Summary

Implemented the full student-facing trainings flow on the existing native-PHP backend:

- **Discovery**: `GET /api/v1/trainings` with filters (field_id, specialization_id, skill_id,
  training_type, work_mode, paid, employment_possible, company_id, city, keyword), whitelisted sort
  (newest/oldest/title/name/deadline/relevance) and pagination; returns only `published`/`open`/`active`
  listings enriched with skills, specializations and the company.
- **Save/favorite**: `POST /api/v1/trainings/{id}/save`, `DELETE /api/v1/trainings/{id}/save`,
  `GET /api/v1/trainings/saved` — idempotent `INSERT IGNORE`-based persistence, student-token gated.
- **Detail**: `GET /api/v1/trainings/{id}` enriched with company, skills, specializations, application
  questions, plus student context (`is_saved`, `has_applied`, `application`) when a token is present.
- **Application**: `POST /api/v1/applications` now persists the multi-step payload
  (cv_file_id, applicant_type, university_id, faculty_id, academic_year, graduation_year, motivation,
  cover_letter) and dynamic `answers` in a single DB transaction; answers are validated against the
  training's configured questions (required questions, select/radio options). Application detail is
  enriched with answers, university/faculty names and normalized status (`submitted` → `pending`).

All flows were smoke-tested over real HTTP against the Laragon Apache/MySQL environment.

## 2. Deliverables

1. Schema extended (source of truth `database/schema/masar.sql`) **and** applied to the live DB.
2. Training repository/service/controller extended for list/filter/sort/detail + saved-trainings CRUD.
3. Application repository/service/validator extended for the multi-step payload and answers.
4. Search fixed so `/api/v1/search/trainings`, `/companies`, `/students`, `/users`, `/certificates`
   return 200 (see section 10 for the pre-existing bugs fixed).
5. Docs updated: `docs/api/trainings.md`, `docs/api/applications.md`, `docs/api/search.md`.
6. Postman collection `MASAR.json` updated: new `05 - Trainings` folder (5 requests) and an expanded
   "Create Application" body (still valid JSON).

## 3. Files Changed

| File | Change |
|---|---|
| `database/schema/masar.sql` | 3 new tables, new columns, indexes, AUTO_INCREMENT, FKs (see section 4) |
| `app/modules/training/repositories/training_repository.php` | `get_public_list`, `count_public`, new `build_public_query` + `sort_clause`, `saved_add/remove/is_saved/get_saved_ids/count_saved`, `get_skills_by_training_ids`, `get_specializations_by_training_ids`, `get_questions`, `get_study_fields` |
| `app/modules/training/services/training_service.php` | `list` (new filters/sort/enrich), `find` (company+skills+specializations+questions+student context), `save`, `unsave`, `saved_list`; now requires `application_repository` |
| `app/modules/training/controllers/training_controller.php` | `training_controller_student_context()`, `save`, `unsave`, `saved` handlers |
| `routes/trainings.php` | `GET /trainings/saved`, `POST/DELETE /trainings/{id}/save` behind `middleware_student` |
| `app/modules/training/repositories/application_repository.php` | create with new columns; `save_answers`, `get_answers`, `find_university_by_id`, `find_faculty_by_id` |
| `app/modules/training/services/application_service.php` | transactional create + answers, university/faculty + answers validation, `enrich_application`, NotificationService class-name fix |
| `app/modules/training/validators/application_validator.php` | new-field validation + `application_validator_answers` |
| `app/modules/search/repositories/search_repository.php` | trainings → `training_listings`; fixed `companies`/`students`/`users`/`certificates` column definitions |
| `app/modules/search/services/search_service.php` | `sort` null-coalescing fix (line 23) |
| `docs/api/trainings.md`, `docs/api/applications.md`, `docs/api/search.md` | synced with implemented behavior |
| `MASAR.json` | training requests + Create Application payload |

All changed PHP files pass `php -l`.

## 4. Database / Schema Changes

Applied to `database/schema/masar.sql` and the live DB (migration script ran clean, all statements OK):

- **New table `saved_trainings`** — `id`, `student_id`, `training_id`, `created_at`, unique
  `uq_student_training_save`, FKs to `students.id` and `training_listings.id` (cascade on student delete).
- **New table `training_questions`** — `id`, `training_id`, `question`, `question_type`
  (text/textarea/select/radio), `required`, `options` (JSON for select/radio), `sort_order`, FK to
  `training_listings.id` (cascade), index on `training_id`.
- **New table `application_answers`** — `id`, `application_id`, `question_id`, `answer`, indexes and
  FKs to `training_applications.id` and `training_questions.id` (cascade).
- **`training_applications`** — new columns `cv_file_id`, `university_id`, `faculty_id`,
  `applicant_type`, `academic_year`, `graduation_year`, `motivation` + FKs
  `fk_applications_cv_file`, `fk_applications_university`, `fk_applications_faculty`.
- **`training_listings`** — added `application_deadline` column.
- AUTO_INCREMENT flags and indexes normalized for the affected tables.

## 5. API Endpoints

Implemented and verified over HTTP:

```text
GET    /api/v1/trainings                 public list (filters + sort + pagination)
GET    /api/v1/trainings/{id}            public detail (enriched, optional student context)
POST   /api/v1/trainings/{id}/save       student (middleware_student)
DELETE /api/v1/trainings/{id}/save       student (middleware_student)
GET    /api/v1/trainings/saved           student (middleware_student)
POST   /api/v1/applications              student apply (multi-step payload + answers)
GET    /api/v1/applications/my           student's own applications
GET    /api/v1/applications/{id}         detail (student/company/admin; enriched)
GET    /api/v1/search/trainings          searches training_listings
GET    /api/v1/search/companies|students|users|certificates
```

## 6. Business Rules

- Only `published`/`open`/`active` trainings are listed or viewable; drafts are excluded.
- Duplicate application → 409 (DB unique constraint `uq_training_student_application` + service check);
  a `rejected` application may be re-applied.
- Application deadline enforced server-side (server clock) → 409 `"The application deadline has passed."`.
- Capacity enforced server-side on apply and on accept → 409 when reached.
- `field_id` filter resolves via `study_fields` (1 Engineering, 2 Computer Science, 3 Business,
  4 Medicine) then matches the listing's `field` name; `training_listings.field` is a name, not an FK.
- Enums: `training_type` = shadowing | hands_on | project_based; `work_mode` = onsite | remote | hybrid
  (the DB columns are `training_type` and `mode`); `applicant_type` = student | graduated.
- Status normalization: DB stores `submitted`; API exposes `pending` (detail + training application summary).
- Saving is idempotent (`INSERT IGNORE`); unsaving a non-saved training still returns success.

## 7. Validation Rules

- `application_validator_create`: cv_file_id/university_id/faculty_id are positive ints;
  applicant_type ∈ {student, graduated}; academic_year ≤ 20 chars; graduation_year ∈ [1950, 2100];
  motivation ≤ 5000; cover_letter ≤ 10000; student_id/training_id validated only for format when present
  (the authenticated student and route/body training are resolved server-side).
- `application_validator_answers`: every required question must be answered and non-empty; answers ≤
  10000 chars; select/radio answers must be one of the question's configured options → 422
  `answers.<question_id>`.
- Service-level existence checks: university/faculty must exist → 422; training must be published and
  not past deadline → 409; sort values are allowlisted (unknown → `newest`); limit clamped 1–100.
- Reject requires `rejection_reason` from the preset list → 422 otherwise.

## 8. Security & Authorization

- All SQL is parameterized (PDO prepared statements); dynamic ORDER BY is allowlisted.
- Save/unsave/saved routes behind `middleware_student`; application routes check `is_student_role` /
  `is_company_role`; application detail checks ownership (student must own it, company must own the
  training, admin allowed).
- Student identity for apply/save is resolved server-side from the authenticated user, never trusted
  from the payload (student_id/training_id are validated for format only).
- Response enrich/format keeps passwords, hashes, tokens and private fields out of search/application
  responses; company name is exposed as `company_name`/`legal_name` (no `name` column).
- No secrets logged; notifications use the existing global `NotificationService`.

## 9. Transaction Handling

- Application submission (create + answers) runs inside `db_begin_transaction` /
  `db_commit` / `db_rollback` with rollback on failure or exception (`db_in_transaction()` guard).
- The `submitted` status is written within the transaction; `application_answers` rows are committed
  atomically with the application.
- Existing accept/reject flows continue to use the same transaction helpers.

## 10. Pre-existing Bugs Fixed

- `application_service.php`: `new \MASAR\Modules\Notifications\Services\NotificationService()` →
  `new \NotificationService()` (4 occurrences) — the namespaced class does not exist; this would have
  broken apply/accept/reject notifications at runtime.
- `search_service.php` line 23: `? $filters['sort'] : 'relevance'` → `? ($filters['sort'] ?? 'relevance') : 'relevance'`
  — the undefined-key warning was converted into an exception by the global error handler, breaking
  **all** search endpoints with 400.
- `search_repository.php`: definitions referenced non-existent columns — `companies` had no `name`
  (it has `legal_name`), `students` had no `name` (it has `full_name`), `users` had no `name`/
  `username`, `certificates` had no `certificate_number`/`description`. All definitions now match the
  real schema, so `/api/v1/search/companies|students|users|certificates|trainings` return 200.

## 11. Verification Evidence

Verified over HTTP at `http://localhost/Masar/backend/api/v1` (Laragon, live MySQL):

- Health check 200; list returns only published trainings with skills/specializations enriched and
  `is_saved`; filters `field_id=1/2`, `sort=title` and `sort=deadline`, `paid=1`, `keyword`,
  `training_type`, `specialization_id` all return correct subsets.
- Detail with student token returns questions, specializations, `is_saved`, `has_applied`,
  `application`; guests get neutral defaults.
- Save → duplicate save (idempotent) → saved list (`is_saved:1`, total correct) → unsave → unsave of
  non-saved (200, `"was not in the saved list"`); 401 without a token.
- Apply 201 with all new columns + answers persisted; duplicate 409; deadline-passed 409; capacity 409;
  missing required answer 422; invalid select option 422; invalid university 422 (from earlier session).
- Application detail shows answers, `university_name`, `faculty_name`, `status: pending`.
- Search: `q=frontend` on trainings/companies/students/generic returns 200.
- `php -l` passes on every changed PHP file; `MASAR.json` re-parses as valid JSON with the new
  `05 - Trainings` folder intact.

Note: PHPUnit is not runnable in this repo (no `phpunit.xml`/`tests/`, per AGENTS.md §10/§32), so
verification is by lint + live HTTP assertions above.

## 12. Documentation Updated

- `docs/api/trainings.md` — new "Implemented Endpoints (Student Feature)" section with real base URL,
  filter/sort/pagination reference, exact list/detail response shapes, save/unsave/saved behavior,
  actual enum values, and a clear split between implemented routes and design/spec-only routes.
- `docs/api/applications.md` — implemented-endpoints list, full multi-step create payload, validation
  error table, enriched detail shape, actual withdraw/accept/reject contract (body `{id}` /
  `{id, rejection_reason}` with the preset reasons), and the spec-only note.
- `docs/api/search.md` — implemented search endpoints, parameters, response shape, the
  `legal_name`/`full_name` mapping note, and the trainings/companies/students fix.

## 13. Postman Collection (MASAR.json)

- New top-level folder **`05 - Trainings`** with 5 requests: List Trainings (with query params),
  Get Training Details, Save Training, Unsave Training, Get Saved Trainings — all carrying the bearer
  auth and `{{base_url}}`/`{{training_id}}` variables.
- "Create Application" request body expanded to the full multi-step payload
  (training_id, cv_file_id, applicant_type, university_id, faculty_id, academic_year, graduation_year,
  motivation, cover_letter, answers).
- Collection re-validated as JSON and folder contents confirmed.

## 14. Known Limitations / Remaining Gaps

- `training_controller_update/publish/close/delete` and the specializations/skills/sessions/apply
  sub-routes still exist in the controller/service but are **not routed** (documented as spec-only);
  apply is exposed via `POST /api/v1/applications`.
- List items expose `is_saved` as `0/1` integers while detail exposes a boolean — cosmetic
  inconsistency preserved from the existing SQL shape.
- `/api/v1/applications/my` returns the raw `submitted` status (not normalized to `pending`); only
  detail and the training detail summary normalize it.
- `training_questions.options` is stored/returned inconsistently: detail returns a JSON array while
  application answers return the raw JSON string for `options`. The validator handles both.
- No automated test suite exists in the repo; coverage is via the live HTTP checks in section 11.

## 15. Test Data & Next Steps

**Test data seeded into the live DB** (used for verification; safe to keep in dev or delete):
trainings id 2 (Frontend Engineering Internship, published), 3 (Data Science Shadowing, published),
4 (Marketing Project-Based Training, deadline passed), 5 (Draft Training), training_questions 1–2,
training_applications 3–6, application_answers, saved_trainings rows created during testing, CV
`files` id 17, and students (user 202 → student 100, user 209 → student 101 "Student Two",
`stu2@test.local`). A DB token (`auth_tokens`) for user 202 and JWT access tokens were minted during
testing.

**Next steps (recommended):**
1. Route the spec-only training management endpoints (update/publish/close/delete, skills,
   specializations, sessions) when the company side is built.
2. Decide whether to normalize `is_saved`/`status` consistently across list/detail responses.
3. Add a real test suite (`tests/` + `phpunit.xml`) per AGENTS.md §10 so `composer test` works.
4. Clean up seeded test rows before any staging/production deploy (see section 15 list).