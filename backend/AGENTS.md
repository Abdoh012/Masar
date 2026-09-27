# AGENTS.md

Instructions for AI agents working on this repository.

**`backend/` is the product.** The backend is a complete, framework-free PHP 8.2+ REST API. The
`frontend/` Next.js app is a separate scaffold whose feature modules are still stubs.

> **The root `README.md` is stale and will mislead you.** It states the backend is "not yet
> implemented" and "reserved for the backend teammate", and points to a `frontend/.env.example`
> that does not exist. It also claims the frontend talks to `http://localhost:4000/api`, which
> matches nothing here — the real base URL is below. Read `backend/README.md` instead; it is
> accurate and current. (`backend/README.md` in turn links to `docs/AGENTS.md`, which is deleted
> in the working tree — this file supersedes it.)

## Scope

- `backend/` is the primary product and the **default scope** for implementation work.
- Treat `frontend/` as **out of scope** unless the user explicitly asks for frontend changes.
- Do not modify Apache, Laragon, VHost, `php.ini`, server configuration, or environment
  configuration unless the user explicitly asks.
- **Never modify `.env` and never expose secrets.** `.env` is gitignored and already exists
  locally; add new keys to `.env.example` instead.

## Layout

```
backend/
  public/index.php     front controller: prefix-dispatch to routes/, static files, /-landing page
  index.php            shim: requires public/index.php
  .htaccess            REAL web config; backend/ is the docroot, not public/
  routes/*.php         per-domain: if ($path === '/api/v1/...' && $method === '...') guards
  app/
    config/            app, constants, database, cors, mail, upload
    core/              auth, cron, database, errors, helpers, http, logging, middleware, validation
    modules/<domain>/  controllers/ services/ repositories/ (validators/ where they exist)
    shared/            enums/ + functions/
    storage/           runtime: logs, cache, uploads (gitignored)
  database/
    schema/masar.sql   full dump = source of truth
    migrations/        incremental, numbered 026_..034_*.sql
    seeders/           standalone `php database/seeders/<x>_seeder.php`
  cron/                scheduled scripts, blocked over HTTP by .htaccess
  docs/                api/, architecture/, database/, security/, *.md
  tests/               standalone regression runners (see Testing)
  openapi.yaml         machine-readable contract
  MASAR.json           Postman collection: 16 folders / 118 requests
```

Modules are `admin auth certificates companies files lookups messaging notifications payments search
students training users`. Saved Trainings is **not** its own module — it lives in the trainings
domain (`/api/v1/trainings/saved/list`).

Base URL is `http://localhost/Masar/backend/api/v1` (Laragon on Windows is the supported local
setup; see `backend/docs/SETUP_LARAGON.md`).

## Commands

There is no `composer lint`, `composer phpstan`, or `composer format` script. `composer.json`
defines exactly one script, and it is misleading (below).

```bash
cd backend

php -l path/to/file.php                    # the only lint that gates anything
php tests/<name>_regression.php            # run ONE suite
composer validate --no-check-publish      # CI runs this
```

CI (`.github/workflows/`) lints `app routes public cron tests database` with `php -l`, then runs
`composer test`. It has **no MySQL service and no web server**, so it cannot run the regression
suites — CI's behavioral coverage is effectively zero. Also note `quality.yml` has a
`php -l .php-cs-fixer.dist.php` step that is *not* `continue-on-error`, and that file does not
exist, so the Quality workflow is currently red for reasons unrelated to your change. phpstan and
php-cs-fixer are not Composer dependencies; CI fetches both as `.phar`.

## Change strategy

- Inspect the existing architecture and the relevant implementation **before** changing anything.
- Prefer the **smallest safe change** that solves the requested problem.
- Reuse existing helpers, services, repositories, validators, constants, middleware, DTO/response
  patterns and database abstractions wherever possible. Do not introduce a new pattern when an
  existing project pattern already solves the problem.
- Do not create duplicate endpoints or duplicate business logic when an existing implementation can
  be extended safely.
- Preserve backward compatibility unless the user explicitly requests a breaking change.

## Architecture

Always respect the flow: **Controller → Service → Repository → Database.**

- **Controllers** (`*_controller.php`) parse input, invoke validators, call a service, format the
  response. No SQL.
- **Services** (`*_service.php`) hold business rules, workflows and multi-write logic. No SQL.
- **Repositories** (`*_repository.php`) contain **all** SQL and database access.
- **Validators** (`*_validator.php`) validate structure only — no DB, no business rules, no auth.
- Never move SQL into controllers or services, and never bypass the database helper layer.
- **All DB access goes through the helpers** in `app/core/database/query.php`: `db_execute`,
  `db_fetch_one`, `db_fetch_all`, `db_fetch_column`, `db_last_insert_id`, `db_row_count`,
  `db_transaction(callable)`. Do not open your own PDO handle.
- **Prepared statements only.** Never interpolate user input into SQL. Allowlist dynamic
  `ORDER BY` columns and limits.
- **Wrap multi-write operations in `db_transaction()`.**
- **New endpoint:** add the guard to the domain file in `routes/`, not to `public/index.php`
  (which only dispatches by path prefix). Each route file `require_once`s its own middleware and
  controller — that self-contained loading is what lets the case shims require it directly.
- **Route-level middleware** is per-guard (`middleware_student()`, `middleware_company()`,
  `middleware_admin()`), applied inside the route file, not centrally.

## Authentication and authorization

Preserve existing authentication behavior unless the task specifically concerns authentication.

- Preserve role-based authorization and the existing middleware/authorization boundaries. Never
  weaken middleware or ownership checks to make an endpoint work.
- **IDOR/BOLA prevention is mandatory**: run ownership checks (`auth_user_can_access_resource`,
  `auth_require_ownership`, both in `app/shared/functions/authorization.php`) before touching a
  resource.
- **Do not trust client-provided user IDs, roles, ownership or authorization state** — and do not
  trust `role`/`id` from a JWT. Re-check sensitive authorization conditions against the database
  where the existing architecture requires it.
- Uploads: validate extension, MIME and size; random filenames; never serve from user-controlled
  paths.
- **Never log or echo** passwords, JWTs, refresh/reset tokens, OAuth codes/state, or payment
  credentials. `public/index.php` deliberately logs only the request path, plus `code`/`state`
  *lengths* for the Google callback — keep it that way.

## Don't regress other domains

Modifying one domain must not change behavior in the others. Protect in particular: Applications,
Trainings, Payments, Certificates, Saved Trainings, Search, Training creation, Authentication, and
Student/company/admin authorization.

**Before changing shared logic, inspect its existing consumers and its regression coverage.**

## Training lifecycle rules

- The repository's current implementation and documented product decision are the source of truth.
  Do not introduce a second expiration/deadline mechanism without explicit requirements.
- **Fixed duration ≠ remaining/countdown.** `training_calculate_duration(starts_at, ends_at)`
  returns a value that must not change just because the current date changed;
  `training_calculate_remaining_days(ends_at)` is the variable countdown. Both functions in
  `app/modules/training/services/training_service.php` document this distinction — keep it.
- Preserve existing sorting semantics, and **verify the actual SQL `ORDER BY`** before changing or
  asserting any ordering.

## Applications and payments

- Preserve the canonical application statuses (`submitted`, `accepted`, `rejected`, `withdrawn`).
- **Keep application lifecycle state separate from payment state.** `training_applications` has no
  payment column; payment state lives in the `payments` table / `PAYMENT_STATUS_*` constants. Do
  not add a new application status merely to represent payment state.
- Preserve application history when an application is withdrawn/rejected/etc. — the lifecycle is
  carried by the `applied_at` / `reviewed_at` / `withdrawn_at` timestamps, not by overwriting.
- Preserve the authorization and ownership rules for student/company application actions.
- For paid-training flows, preserve the existing payment-reference/payment-status model and its
  backward compatibility.

## Response format and constants

Preserve the API response envelope: `{ success, message, data, errors }`, where `success` is
derived from the status code. Use the project's `response_*` helpers
(`response_success`, `response_created`, `response_error`, `response_validation_error`,
`response_unauthorized`, `response_forbidden`, `response_not_found`, …) instead of hand-building
inconsistent shapes.

- **Format every datetime with `application_iso8601()`**
  (`app/shared/functions/application_cards.php`). It honors `APP_TIMEZONE` (default
  `Africa/Cairo`) and returns `null` for null/empty. Do not hand-roll date formatting.
- **Never hardcode status/type strings.** Use the constants in `app/config/constants.php`.
  `constants.php` also `require_once`s all 14 files in `app/shared/enums/`, so both vocabularies
  are live in every request — but **they are not interchangeable**:
  - `training_listings.training_type` is `enum('shadowing','hands_on','project_based')`, matching
    `constants.php` (`TRAINING_TYPE_SHADOWING` etc.). `app/shared/enums/training_types.php`
    defines a *different, unused* set (`TRAINING_TYPE_IN_PERSON`/`_ONLINE`/…). Don't conflate them.
  - `training_applications.status` is `enum('submitted','accepted','rejected','withdrawn')`,
    matching `constants.php` (`APPLICATION_STATUS_ACCEPTED`). The enums file's
    `_PENDING`/`_UNDER_REVIEW`/`_APPROVED` match `certificate_appeals.status` instead. Using
    `APPLICATION_STATUS_APPROVED` against `training_applications` is a silent bug.
  - Enum files guard each constant with `defined()`, so `constants.php` wins on the few names they
    share. Match whichever file the column's own `enum(...)` lists.

## Testing: the big trap

**`composer test` / PHPUnit executes zero tests.** `phpunit.xml` points its testsuite at `tests/`,
but the 17 `*_regression.php` files are *procedural scripts*, not PHPUnit classes. PHPUnit reports
`No tests executed!` and exits 0 — a green CI run proves nothing about behavior, and **you must
not claim a feature is tested merely because `composer test` exited 0**.

The real suites are run directly:

```bash
php tests/dashboard_endpoint_regression.php
```

Each suite prints `PASS -`/`FAIL -` lines, a `== Result ==` block (`ALL PASS` / `FAILURES: n`),
and exits non-zero on failure. Grep the output; there is no per-test selector.

### Prerequisites

- **A live, seeded MySQL/MariaDB** (`masar`) is required. Suites read and write real tables and
  resolve fixtures from live rows, so they are not hermetic and not order-independent.
- **No web server is needed.** Each `*_case.php` is a subprocess shim that boots the app
  in-process: it sets `$_SERVER`, calls `ob_start()`, `require`s the domain route file directly,
  and prints `STATUS=<code>` / `BODY=<json>`. The regression driver parses those two lines.

### Workflow before declaring a task complete

1. Identify the relevant existing regression suite.
2. Run the smallest relevant suite first.
3. Run additional related suites whenever shared code changed.
4. Report exactly which suites you ran and their results.

### Adding a suite

Follow the existing two-file pattern:

- `tests/<domain>_<feature>_regression.php` — the driver: `check()` assertions, `run_scenario()`
  which shells out to the case file and parses `STATUS=`/`BODY=`.
- `tests/<domain>_<feature>_case.php` — the subprocess shim. Copy an existing one; it is a
  request replay, not a test.

### Existing data and fixtures

- Inspect existing database rows **before** creating fixtures. Avoid duplicate or conflicting
  fixtures, and do not modify unrelated production-like data.
- Prefer idempotent seeders where the repository already uses that pattern.
- **Tag every fixture row** with a unique string prefix and delete it in a cleanup function that is
  also registered via `register_shutdown_function`, so a crashed run cannot poison the DB. Clean up
  temporary regression fixtures even when the test fails.
- **Never assert on data the code does not guarantee.** Check the repository's real `ORDER BY`
  before asserting order — several queries have no tie-breaker, and seeded data really does contain
  duplicate timestamps. Also beware `??` in assertions: `null ?? 'x'` is `'x'`, so it inverts a
  NULL check.
- An "at most N" assertion can never catch an under-limit. Pair it with a fixture that creates
  N+ rows.

### Known pre-existing failure

`tests/training_dates_normalization_regression.php` has **3 failing assertions** unrelated to any
current work — stale dataset expectations (it expects 72 trainings; the DB has 95; 23 rows where
`deadline != starts_at`; 23 published-and-ended rows instead of only fixture `#100305`). Do not
chase it. The other 16 suites pass.

## API contract synchronization

Whenever an API contract changes, keep implementation and docs synchronized. Update all three:

- `backend/openapi.yaml` — preserve existing schemas and `$ref` structure; do not introduce
  duplicate schemas or paths; validate that all `$ref` references remain resolvable.
- `backend/docs/api/<domain>.md`.
- The matching request/test in `backend/MASAR.json` — **edit only the affected
  request/folder/block.** Never regenerate the collection, and preserve existing collection
  structure, variables, examples and unrelated requests.

`MASAR.json` is large (515 KB) and hand-maintained. Validate with
`php -r "json_decode(file_get_contents('MASAR.json'), true, 512, JSON_THROW_ON_ERROR);"`.

## Database changes

- Do **not** modify `database/schema/masar.sql` casually. Inspect the current schema and the related
  `database/migrations/*.sql` first, and prefer the existing migration strategy
  (next number: `035_…`) for a required schema change.
- Do not add a migration merely to work around an application-layer bug.
- Never delete or reset existing data as part of a normal implementation task, and preserve
  backward compatibility with existing data wherever possible.
- Keep enum values in sync between the DB columns and the constants/enums files.
- Seeders are standalone scripts: `php database/seeders/<name>_seeder.php`.

## Config

`app/config/database.php` reads `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
(the returned array keys are `database`, `username`, `password` — not `name`/`user`/`pass`).
**Any new config key needs a matching `.env.example` entry.** CI validates `composer.json` and
`MASAR.json` as JSON and `config/*.yml` + `openapi.yaml` as YAML, so a syntax error in any of them
breaks the build.

## Style

4-space PHP indent, 2-space JSON/YAML, LF endings, no closing `?>`, no tabs. Comments only where
they explain *why* — the codebase uses `/* banner */` section headers and occasional rationale
blocks; match that rather than narrating code. `.gitattributes` forces LF for `*.php`, so on
Windows a benign `CRLF will be replaced by LF` warning is expected.

## Completion report

At the end of every implementation task, report:

- Files changed
- Endpoints added/changed
- Database / schema / migration changes
- Tests and regression suites executed, with results
- API documentation changes
- `MASAR.json` changes
- Any known pre-existing failures
- Any remaining risks or follow-up work

**Do not claim success for checks that were not actually executed.**

## Git

**Never commit unless explicitly asked.** The working tree carries a large amount of uncommitted
work across both apps; `git status` will look alarming. Do not revert or "clean up" modified
files you did not touch, and do not stage unrelated changes. Never commit `.env`, `vendor/`,
`storage/*` runtime dirs, `postman/`, or `/uploads/`.
