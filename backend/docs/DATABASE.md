# MASAR Backend - Database

> Full reference: [`docs/database/database_design.md`](docs/database/database_design.md)
> and the entity diagram in [`docs/database/erd.md`](docs/database/erd.md).
> The authoritative schema dump is [`database/schema/masar.sql`](database/schema/masar.sql).

## Engine and conventions

- **Engine:** InnoDB, `utf8mb4` / `utf8mb4_unicode_ci`.
- **Naming:** `snake_case` for tables and columns; every table has an `id`
  primary key.
- **Foreign keys:** named `fk_<table>_<referenced>` (for example
  `fk_certificates_company`).
- **Timestamps:** `created_at` / `updated_at` on business tables; soft deletes
  use `deleted_at` where history matters.
- **Status values:** must match the enums in `app/shared/enums/*`; never
  hardcode status strings in code.
- **Access:** all access goes through `app/core/database/` helpers
  (`db_execute`, `db_fetch_one`, `db_fetch_all`, `db_begin_transaction`,
  `db_commit`, `db_rollback`) using PDO prepared statements.

## Schema source of truth and migrations

- `database/schema/masar.sql` is the complete, importable schema and the source
  of truth. Do not edit it casually (see `docs/AGENTS.md` §30).
- `database/migrations/` contains incremental SQL migrations (currently
  `026`-`034`) applied on top of the base schema.
- `database/seeders/` contains standalone PHP seeding scripts (lookup data and
  demo/test data). Run them with `php database/seeders/<name>.php`.

## Tables by domain

### Identity and access
`users`, `auth_tokens`, `refresh_tokens`, `revoked_access_tokens`,
`password_resets`, `verification_tokens`, `oauth_states`, `audit_logs`.

### Students
`students`, `skills`, `student_skills`.

### Companies
`companies`, `company_specializations`, `company_work_fields`.

### Reference / lookups
`study_fields`, `specializations`, `faculties`, `universities`, `degrees`.

### Trainings
`training_listings`, `training_sessions`, `training_questions`,
`training_skills`, `training_specializations`, `saved_trainings`.

### Applications and payments
`training_applications`, `application_answers`, `payments`.

### Certificates
`certificates`, `certificate_appeals`.

### Messaging and notifications
`conversations`, `messages`, `notifications`.

### Files
`files` (metadata for uploaded CVs, logos, certificates, and general uploads).

## Key relationships (high level)

- `users` is the root identity; a user has at most one `students` or
  `companies` profile depending on role.
- `companies` publish `training_listings`; a listing has one or more
  `training_sessions`.
- `students` submit `training_applications` against listings; applications may
  collect `application_answers`.
- An accepted application can produce a `payments` record (for paid trainings)
  and a `certificates` record; `certificate_appeals` cover rejected/revoked
  disputes.
- `conversations` group `messages` between participants; `notifications` are
  delivered per user.
- `audit_logs` records privileged actions with actor, entity, and before/after
  values.

See `docs/database/erd.md` for the full relationship diagram.

## Migrations and seeders in this repo

- Migrations present: `026_add_field_id_to_specializations` through
  `034_make_certificate_grade_text`.
- Seeders present include `study_fields_seeder`, `specializations_seeder`,
  `faculties_seeder`, `universities_seeder`, `degrees_seeder`,
  `skills_seeder`, `rejection_reasons_seeder`, plus demo and test-data seeders.
