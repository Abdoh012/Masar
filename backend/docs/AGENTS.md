# AI_INSTRUCTIONS.md

> **Main AI development instruction and project rules document for OpenCode.**
> Read this file before any backend task. If a rule here conflicts with instructions given elsewhere, this file wins unless the task explicitly says otherwise.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Purpose](#2-purpose)
3. [How to Use This Document](#3-how-to-use-this-document)
4. [Core Development Rules](#4-core-development-rules)
5. [AI Agent Behavior Rules](#5-ai-agent-behavior-rules)
6. [Language and Communication](#6-language-and-communication)
7. [Development Workflow](#7-development-workflow)
8. [Task Execution Guidelines](#8-task-execution-guidelines)
9. [Code Changes](#9-code-changes)
10. [Testing Rules](#10-testing-rules)
11. [Verification Rules](#11-verification-rules)
12. [Error Handling and Debugging](#12-error-handling-and-debugging)
13. [Security Rules](#13-security-rules)
14. [Secrets and Sensitive Data](#14-secrets-and-sensitive-data)
15. [SQL and Database Rules](#15-sql-and-database-rules)
16. [File Structure and Organization](#16-file-structure-and-organization)
17. [Module Conventions](#17-module-conventions)
18. [Naming Conventions](#18-naming-conventions)
19. [Code Style](#19-code-style)
20. [Environment and Configuration](#20-environment-and-configuration)
21. [API Standards](#21-api-standards)
22. [Authentication and Authorization](#22-authentication-and-authorization)
23. [Rate Limiting and Abuse Protection](#23-rate-limiting-and-abuse-protection)
24. [File Uploads and Storage](#24-file-uploads-and-storage)
25. [Email and Notifications](#25-email-and-notifications)
26. [Audit Logging](#26-audit-logging)
27. [Documentation Rules](#27-documentation-rules)
28. [Commit Rules](#28-commit-rules)
29. [Cron Jobs](#29-cron-jobs)
30. [Database Migration Rules](#30-database-migration-rules)
31. [Testing and Validation of Changes](#31-testing-and-validation-of-changes)
32. [Known Implementation Gaps](#32-known-implementation-gaps)
33. [Repository Layout](#33-repository-layout)
34. [Final AI Behavior](#34-final-ai-behavior)

---

## 1. Project Overview

**MASAR** is a modular web platform that connects students with companies offering training and internship opportunities.

- **Backend** is a custom PHP 8.2 application (no framework) with a flat-file modular architecture.
- **Database**: MySQL / MariaDB (`masar`).
- **API**: REST, JSON, JWT-authenticated, base URL `http://localhost/Masar/backend/api/v1`.
- **Frontend**: outside this repository (consumes this API).

### Stack

| Layer | Technology |
|---|---|
| Language | PHP `^8.2` |
| HTTP entry | `public/index.php` (Apache + `.htaccess` rewrite) |
| Database | MySQL/MariaDB via PDO (prepared statements) |
| Auth | JWT (HS256, native `hash_hmac`) + DB tokens + Google OAuth |
| Mail | PHPMailer via `app/shared/functions/email.php` |
| Dependencies | Composer: `google/apiclient`, `phpmailer/phpmailer`, `vlucas/phpdotenv`, `phpunit/phpunit` |

---

## 2. Purpose

This document is the authoritative set of instructions and conventions for any AI agent (e.g. OpenCode) working on this repository. It ensures:

- Consistent architecture, structure, and code style.
- Security is never regressed.
- Business rules are enforced in the right layer.
- Docs and code stay in sync.
- The agent does not "fix" things that are intentionally designed a certain way.

---

## 3. How to Use This Document

1. Read this file fully at the start of a session.
2. When starting a task, locate the relevant module (`app/modules/*`), route (`routes/*`), and docs (`docs/*`).
3. Follow the layered architecture: Controller -> Service -> Repository.
4. After changes, verify: no new security holes, no broken endpoints, style consistent.
5. If a requested change contradicts this document, stop and ask the user before proceeding.

---

## 4. Core Development Rules

1. **Controllers are thin.** They parse input, validate, call a service, and format the response. No SQL in controllers.
2. **Services hold business logic and workflows.** No SQL in services.
3. **Repositories isolate all database access.** `db_execute` / `db_fetch_one` / transactions are used here.
4. **Validators validate structure only.** Business-state validation lives in services.
5. **Enums centralize statuses.** Never hardcode status strings in services/controllers — use `app/shared/enums/*`.
6. **Use parameterized queries only.** Never concatenate user input into SQL.
7. **Keep HTTP concerns in the HTTP layer.** `request_*()` / `response_*()` helpers only in controllers/routes/middleware.
8. **Do not log secrets.** Never log tokens, passwords, OAuth codes, or full payment credentials.
9. **Follow existing patterns.** When extending a module, mirror its existing validators/services/repositories.
10. **Do not rewrite working code.** If code works and follows conventions, preserve it unless the task demands change.

---

## 5. AI Agent Behavior Rules

- **Ask before acting** if the task is ambiguous or conflicts with existing conventions.
- **Do not invent** features, endpoints, or config keys that the user did not request.
- **Do not modify** `database/schema/masar.sql` unless explicitly asked (it is the source-of-truth schema dump).
- **Never modify** `vendor/`, `composer.lock`, or `.env` unless explicitly asked.
- **Never commit** unless the user explicitly asks.
- **Preserve behavior.** Small cosmetic renames are allowed; refactors that change behavior require confirmation.
- **Report what changed.** Summarize files touched and any gaps discovered.

---

## 6. Language and Communication

- The project UI/docs use **English**.
- Code identifiers, comments, and commit messages should be in **English**.
- API responses and error messages are in **English**.

---

## 7. Development Workflow

Standard flow for adding or modifying a feature:

1. Understand the existing module and its docs.
2. Plan the change across the correct layers (route -> middleware -> controller -> validator -> service -> repository).
3. Implement, following existing code style and naming.
4. Run PHP lint on changed files (`php -l file.php`).
5. Run tests if a test command is available (see §10).
6. Manually verify the endpoint via the API (e.g. `curl` or Postman collection if present).
7. Update docs (`docs/api/*`) when API behavior changes.
8. Summarize the work.

---

## 8. Task Execution Guidelines

- Break large work into small, verifiable steps.
- Prefer modifying existing files over creating new ones.
- Do not create unnecessary files.
- When a task says "implement X," locate the closest existing pattern and extend it.
- When unsure whether a directory/file should exist, check `docs/architecture/architecture.md` and the module conventions.

---

## 9. Code Changes

- **No comments unless they explain why** (the codebase uses short section banners and occasional rationale comments; follow that style).
- Match the existing style: 4-space indent, `snake_case` functions, files without a closing `?>`, `require_once` dependency chains at the top.
- Keep functions small and single-purpose.
- Use the project's helpers (`request_input`, `response_error`, `db_execute`, etc.) instead of raw `$_POST` / `echo` / `mysqli`.
- If a change breaks a documented endpoint contract, update the corresponding `docs/api/*.md`.

---

## 10. Testing Rules

- The project uses **PHPUnit 10.5** (`require-dev`).
- Test command: `composer test` (runs `phpunit --configuration phpunit.xml`).
- **Known issue:** `phpunit.xml` does not currently exist in the repository and there is no `tests/` directory committed. See §32. Do not claim tests passed unless you actually ran them.
- If you add a testable pure function (validator, enum helper, utility), prefer adding a unit test under `tests/`.
- If no test infrastructure is present, do not fabricate test results. State what was verified manually instead.

---

## 11. Verification Rules

- **Always verify your work** before declaring completion.
- Run `php -l` on every changed/created `.php` file.
- Test affected endpoints with real HTTP requests (e.g. `curl` against `http://localhost/Masar/backend/api/v1/...`).
- Verify DB-related changes against the actual database where possible.
- Never say "tests pass" without evidence.
- If you cannot verify something, say so explicitly.

---

## 12. Error Handling and Debugging

- The app registers global error and exception handlers (`app/core/errors/error_handler.php`, `exception_handler.php`).
- Production responses must **not** leak stack traces, DB credentials, or internal details.
- Log technical details server-side only (`error_log`, `storage/logs/`).
- When debugging, prefer reading logs (`storage/logs/`) and reproducing the request rather than adding debug prints that are left in the code.

---

## 13. Security Rules

Security is multi-layered: web server -> HTTP -> auth -> authorization -> validation -> business rules -> DB constraints -> audit. Treat every layer as required.

- **Never trust client input.** Validate everything (size, type, content, structure).
- **Never trust the `role`/`id` in a JWT blindly** — re-check role/status against the database when it matters.
- **Ownership checks** (`auth_user_can_access_resource`) before reading/writing resources; prevent IDOR/BOLA.
- **Sensitive admin actions** require re-authentication (`security_require_admin_reauth`).
- **CSRF** must be validated on state-changing requests that rely on cookies (`csrf_require`).
- **CORS** must use explicit allowed origins; never `*` for authenticated APIs in production.
- **Uploads**: validate extension, MIME, size; generate random filenames; never execute stored files; block dangerous extensions.
- **SQL**: prepared statements only.
- **Cookies**: HttpOnly + SameSite; Secure when `SECURE_COOKIES=true`.
- **Headers**: applied by `security_apply_http_headers()`.

---

## 14. Secrets and Sensitive Data

- Secrets live in `.env` only. `.env` is never committed.
- Never log or echo: passwords, tokens (JWT, refresh, reset, OAuth codes/state), API keys, or full payment credentials.
- The Google OAuth callback query string must never be logged (only presence/length metadata is allowed).
- Never commit `.env`, secrets, or credentials.
- If you must reference a secret in code, read it via `getenv()`.

---

## 15. SQL and Database Rules

- All access through `app/core/database/` helpers: `db_execute`, `db_fetch_one` (PDO prepared statements), and transaction helpers `db_begin_transaction` / `db_commit` / `db_rollback` / `db_in_transaction`.
- **Transactions** belong at the service/use-case level when an operation performs multiple writes.
- **Never concatenate user input into SQL.**
- **Allowlist** dynamic values such as ORDER BY columns and pagination limits.
- Schema source of truth is `database/schema/masar.sql`. Do not edit it casually.
- Status/enum values in SQL must match `app/shared/enums/*`.

---

## 16. File Structure and Organization

The backend is a flat-file, namespace-free PHP application organized by responsibility.

```text
backend/
|-- public/                 # Web root (only web-accessible directory)
|   |-- index.php           # Front controller
|   `-- .htaccess
|-- app/
|   |-- config/             # app, constants, cors, database, mail, upload
|   |-- core/               # auth, cron, database, errors, helpers, http,
|   |   |                   # logging, middleware, validation
|   |-- modules/            # admin, auth, certificates, companies, files,
|   |   |                   # messaging, notifications, payments, search,
|   |   |                   # students, training, users
|   |-- services/           # jwt_service.php
|   |-- shared/             # enums/ + functions/
|   `-- storage/            # uploads, cache, logs (runtime)
|-- routes/                 # api, auth, users, students, companies, trainings,
|   |                       # certificates, applications, messaging, notifications,
|   |                       # files, search, admin, cron
|-- database/
|   |-- schema/masar.sql    # complete schema dump (source of truth)
|   `-- seeders/            # 7 seeder scripts
|-- cron/                   # 5 background scripts
|-- docs/
|   |-- api/                # per-domain endpoint docs
|   |-- architecture/
|   |-- database/
|   `-- security/
|-- .env.example
|-- composer.json / composer.lock
`-- .postman/
```

---

## 17. Module Conventions

Each feature module in `app/modules/*` follows a consistent shape:

```text
app/modules/<feature>/
|-- controllers/
|-- repositories/
|-- services/
`-- validators/          # not present in every module (see §32)
```

- **Module boundaries:** a module owns its domain. Cross-module calls are allowed only when needed (e.g. auth -> companies/students services).
- **Controllers** live in `controllers/*_controller.php`.
- **Repositories** live in `repositories/*_repository.php` and contain all SQL.
- **Services** live in `services/*_service.php` and contain workflows; no SQL.
- **Validators** live in `validators/*_validator.php`; pure structure validation, no DB, no business logic, no auth.

---

## 18. Naming Conventions

- **Files:** `snake_case.php` (e.g. `user_validator.php`, `auth_service.php`).
- **Functions:** `snake_case`, usually prefixed with the domain (e.g. `db_execute`, `auth_user`, `security_check_rate_limit`, `user_validate_update`).
- **Constants/enums:** `UPPER_SNAKE_CASE` (e.g. `USER_ROLE_ADMIN`, `USER_STATUS_ACTIVE`).
- **Variables:** `snake_case`.
- **Routes:** path-based routing in `routes/*.php` with `if ($path === '/api/v1/...')` guards.

---

## 19. Code Style

- 4-space indentation, no tabs.
- Functions use the `/* banner */` or doc-comment style already present in the codebase.
- `require_once` dependencies at the top of each file.
- No closing `?>` at the end of PHP files.
- Prefer early returns; keep functions short.
- Use strict comparisons where appropriate (`===`, `!==`, `in_array(..., true)`).
- Validate input types (`is_string`, `is_array`, `filter_var`) before use.

---

## 20. Environment and Configuration

- Config lives in `app/config/*.php` and is read from `getenv()` with safe defaults.
- Copy `.env.example` to `.env` and adjust for the local machine.
- `.env` keys in use include: `APP_*`, `DB_*`, `MAIL_*`, `UPLOAD_*`, `GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI`, `RECAPTCHA_*`, `JWT_SECRET/JWT_ALGORITHM/JWT_ACCESS_TTL/JWT_REFRESH_TTL`, `CSRF_COOKIE_NAME/CSRF_HEADER_NAME`, `SECURE_COOKIES`, `CORS_ALLOWED_ORIGINS`, `RATE_LIMIT_ENABLED`.
- Do not add new config without a corresponding `.env.example` entry.
- Default DB: `mysql` / `127.0.0.1:3306` / `masar` / root / empty password.

---

## 21. API Standards

- **Base URL:** `http://localhost/Masar/backend/api/v1`
- **Versioning:** `/api/v1/*`
- **Format:** JSON everywhere.
- **Response envelope:**
  - Success: `{ "success": true, "message": "...", "data": { ... } }`
  - Error: `{ "success": false, "message": "...", "errors": { field: [ "...", ... ] } }`
- Use `response_success`, `response_error`, `response_unauthorized`, `response_forbidden`, `response_not_found`, `response_validation_error` helpers.
- **HTTP status codes:** 200/201/204, 400/401/403/404/409/422/429, 500.
- **Pagination:** consistent shape via `app/shared/functions/pagination.php` and `request_get_int` (`page`, `per_page`); defaults 20, max 100.
- Each domain has docs in `docs/api/*.md` — keep them in sync with routes.

---

## 22. Authentication and Authorization

- **Auth flow:**
  - `POST /api/v1/auth/register`, `POST /api/v1/auth/login`, `POST /api/v1/auth/refresh`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`.
  - Password reset: forgot-password / resend-reset-otp / verify-reset-otp / reset-password.
  - Google OAuth: `GET /api/v1/auth/google`, `GET /api/v1/auth/google/callback`.
- **Tokens:** JWT access token (Bearer header) + refresh token (HttpOnly cookie) + optional DB token cookie (`MASAR_REMEMBER`).
- **Middleware** (`app/core/middleware/`): `middleware_auth`, `middleware_jwt_auth`, `middleware_admin`, `middleware_company`, `middleware_student`, `csrf_require`, `cors_handle`, `security_apply_http_headers`.
- **RBAC:** permission map in `app/shared/functions/authorization.php` (`auth_role_permissions`, `auth_user_has_permission`). Roles: admin, super_admin, student, company, trainer, moderator, employee, guest.
- **Ownership:** use `auth_user_can_access_resource` / `auth_require_ownership` before resource access.
- **Account status gating:** inactive/suspended/blocked users cannot act (`auth_user_is_active`).

---

## 23. Rate Limiting and Abuse Protection

- Rate limiting lives in `app/shared/functions/security.php`.
- `security_check_rate_limit($action, $identifier, $max, $window)` — file-backed sliding window.
- Tiers: `global`, `ip`, `user`, `endpoint`, `sensitive` (see `security_rate_limit_tier_defaults`).
- Protect at minimum: login, password reset, refresh, file upload, search, messaging.
- Rate limit is disabled for local testing when `RATE_LIMIT_ENABLED=false` and not in production (`security_check_rate_limit` handles this).

---

## 24. File Uploads and Storage

- Config: `app/config/upload.php`.
- Validation: extension + MIME + size (per directory type), random filenames, blocked executable extensions, MIME sniffing.
- Storage roots: `app/storage/uploads/` (`cv`, `certificates`, `profile`, `general`, `temp`).
- DB metadata in `files` table; physical files on disk.
- Expose downloads through controlled API endpoints, never direct filesystem paths.
- Do not store or serve files with user-controlled paths.

---

## 25. Email and Notifications

- Email via PHPMailer in `app/shared/functions/email.php`; templates in `email_templates.php`.
- Mail config: `app/config/mail.php` (SMTP defaults to Gmail; enable in `.env`).
- Notifications module: `app/modules/notifications/` with `channels/`, `controllers/`, `repositories/`, `services/`.
- In-app notifications + optional email. Keep notification deduplication in mind.
- Do not send email synchronously inside SQL-heavy loops without considering cost; prefer the notification service layer.

---

## 26. Audit Logging

- Central audit helper: `audit_log_event` / `audit_log_user_action` in `app/shared/functions/audit.php`.
- Writes to `audit_logs` table (user_id, action, entity_type, entity_id, old/new values, IP, user agent).
- Log: login success/failure, sensitive admin actions, token revocation, status changes, privileged operations.
- Never log passwords, tokens, or full secrets in audit `new_values`/`old_values`.

---

## 27. Documentation Rules

- API docs live in `docs/api/*.md` (one file per domain).
- Architecture reference: `docs/architecture/architecture.md`.
- Database reference: `docs/database/database_design.md`, `docs/database/erd.md`.
- Security reference: `docs/security/security-hardening-plan.md`.
- **Keep docs in sync with code.** When you change an endpoint contract, update its `docs/api/*.md`.
- Do not create new top-level docs unless the user asks; prefer updating existing ones.

---

## 28. Commit Rules

- **Never commit unless the user explicitly asks.**
- Before committing: review `git status` and `git diff`; stage only intended files.
- Never commit secrets, `.env`, `vendor/`, or build artifacts.
- Write concise commit messages matching the repo style (the repo uses plain descriptive messages).
- Do not amend, force-push, or rewrite history unless asked.

---

## 29. Cron Jobs

Scheduled background scripts in `cron/`:

- `close_expired_trainings.php` — close trainings past deadline (idempotent).
- `expire_trial_periods.php` — expire trial periods.
- `send_expiry_notifications.php` — notify about expiring resources (idempotent, dedup).
- `cleanup_temp_files.php` — remove expired temp uploads.
- `cleanup_expired_tokens.php` — purge expired/revoked tokens.

Rules:

- Cron scripts must not be exposed as public HTTP endpoints. (`/cron` and `/cron/run` are the only HTTP hook, gated by `routes/cron.php` — check before assuming.)
- Each job must be idempotent (running twice must not corrupt data).
- Load the same bootstrap/config as the app (`app/core/cron/runner.php`).

---

## 30. Database Migration Rules

- **Current state:** `database/migrations/` does **not** exist in the working tree (deleted/uncommitted). The schema source of truth is `database/schema/masar.sql`.
- If you must change the schema, prefer editing `database/schema/masar.sql` only after confirming with the user.
- Seeders live in `database/seeders/*_seeder.php` (7 files). Run as standalone PHP scripts.
- Keep enum values in sync between DB and `app/shared/enums/*`.
- Use the transaction helpers when a migration/seed touches multiple tables.

---

## 31. Testing and Validation of Changes

1. Lint every changed file: `php -l <file>`.
2. If tests exist, run `composer test` (see §10 — currently unavailable).
3. Manually exercise affected endpoints with HTTP requests and confirm status codes + envelope shape.
4. Verify authorization: test as student, company, and admin where relevant; confirm 403s.
5. Check no sensitive data leaks into responses/logs.
6. Confirm docs are updated if contracts changed.

---

## 32. Known Implementation Gaps

The original specification referenced several files/features that are currently **absent from the working tree**. Do not treat the spec as the current truth for these items:

| Spec / doc reference | Current reality |
|---|---|
| `docs/business_rules.md` | **Missing** (deleted from working tree). Business rules are instead scattered in services and `docs/architecture/architecture.md`. |
| `docs/USER_MODULE_SECURITY.md` | **Missing** (deleted from working tree). User-module security is described in `docs/api/users.md` + `docs/security/security-hardening-plan.md`. |
| `database/migrations/*.sql` (25 files) | **Missing** (deleted from working tree). Schema source of truth is `database/schema/masar.sql`. |
| `postman/collections/` | **Missing** (deleted). Only `.postman/resources.yaml` remains (references `../postman/collections/MASAR API`, which no longer exists). |
| `tests/` + `phpunit.xml` | **Missing**. `composer test` is currently broken because `phpunit.xml` does not exist. |
| `backend/AGENTS.md`, `FINAL_VERIFICATION.md`, `MOVE_REPORT.md`, `masar.php` | Deleted from working tree. |
| Spec paths like `shared/enums/user_roles.php` | Actual paths are under `app/` (e.g. `app/shared/enums/user_roles.php`). |
| All modules have `validators/` | Only auth, certificates, companies, messaging, students, training, users have validators. admin, files, notifications, payments, search do not. |
| Payments module full shape | Only `repositories/` + `services/` exist (no controllers/validators). Files module has controllers but no validators. |
| API base path `/api/v1` | Confirmed by routes and `docs/api/*`. Root entry also serves static public assets and a landing page. |

When a task references any of the above, work from the current code, not the deleted spec files, and note the difference.

---

## 33. Repository Layout

```text
backend/
|-- .env.example
|-- .htaccess
|-- .postman/
|-- AI_INSTRUCTIONS.md
|-- SETUP_LARAGON.md
|-- composer.json
|-- composer.lock
|-- app/
|-- cron/
|-- database/
|-- docs/
|-- public/
|-- routes/
```

Full detail in §16 and `docs/architecture/architecture.md`.

---

## 34. Final AI Behavior

- **Complete the task fully** and verify it works; do not stop at a partial implementation.
- **Be conservative:** prefer the smallest change that satisfies the request while respecting architecture and security.
- **Ask when blocked or ambiguous;** do not guess silently on security-sensitive decisions.
- **Report concisely:** what changed, what was verified, and any gaps/risks you noticed.
- When in doubt, this file is the source of truth for backend work.
