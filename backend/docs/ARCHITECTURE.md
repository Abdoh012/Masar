# MASAR Backend - Architecture

> This is the concise project entry point. The exhaustive reference (93 sections
> covering every subsystem) is [`docs/architecture/architecture.md`](docs/architecture/architecture.md).
> Read this file first, then follow the links for depth.

## Overview

MASAR's backend is a **namespace-free, flat-file modular PHP application**. There
is no framework: each request enters through a single front controller, is routed
by path, and is handled by a controller that delegates to services and
repositories. Dependencies are plain `require_once` chains and Composer autoload
for third-party libraries.

```
                 Apache + .htaccess (Deny sensitive dirs, route to front controller)
                                    |
                          public/index.php  (front controller)
                                    |
        app/core/http  ->  request parsing, response envelope, CORS, security headers
                                    |
        routes/*.php   ->  path-based dispatch (if ($path === '/api/v1/...'))
                                    |
        app/core/middleware -> auth (JWT), admin/company/student gates, CSRF, rate limit
                                    |
             app/modules/<domain>/controllers/*_controller.php   (thin)
                                    |
             app/modules/<domain>/services/*_service.php         (workflows)
                                    |
             app/modules/<domain>/repositories/*_repository.php  (all SQL)
                                    |
                     app/core/database  ->  PDO prepared statements
```

## Layer responsibilities

| Layer | Location | Rule |
| --- | --- | --- |
| Controllers | `app/modules/*/controllers` | Parse input, validate structure, call a service, format the response. No SQL. |
| Services | `app/modules/*/services` | Business logic and workflows. No SQL. |
| Repositories | `app/modules/*/repositories` | All database access via `db_execute` / `db_fetch_*` / transactions. |
| Validators | `app/modules/*/validators` | Structure-only validation (not present in every module). |
| Enums | `app/shared/enums` | Canonical status/type values; never hardcode status strings. |
| HTTP | `app/core/http` | Request helpers and the response envelope. |
| Middleware | `app/core/middleware` | Auth, RBAC gates, CSRF, CORS, security headers. |
| Errors/Logging | `app/core/errors`, `app/core/logging` | Global handlers, safe messages, `storage/logs`. |

Dependency direction is always inward: **route -> controller -> service ->
repository -> database**. Cross-module calls are allowed only when needed
(for example, `auth` calling `companies`/`students` services).

## Request lifecycle

1. Apache rewrites the request to `public/index.php` (the root `.htaccess` also
   blocks direct access to `.env`, `app/`, `database/`, `routes/`, `storage/`,
   `tests/`, `docs/`, `postman/`, and `vendor/`).
2. The front controller loads config, registers error/exception handlers,
   applies CORS and security headers, and resolves the request path/method.
3. `routes/*.php` match the path and dispatch to a controller.
4. Middleware runs where required (JWT auth, role gate, CSRF, rate limiting).
5. The controller calls a service; the service calls repositories; repositories
   use PDO prepared statements.
6. A single JSON envelope is returned (`success` / `message` / `data` /
   `errors`), or an error handler produces the same envelope.

## Modules

`app/modules/` contains: `admin`, `auth`, `certificates`, `companies`, `files`,
`messaging`, `notifications`, `payments`, `search`, `students`, `training`,
`users`. Each module follows the controller/service/repository shape; `files`
has controllers but no validators, and `payments` currently exposes only
repositories and services.

## Cross-cutting concerns

- **Routing:** path guards in `routes/*.php`; see `routes/api.php` for the
  health endpoint and root discovery.
- **Configuration:** `app/config/*.php` read `getenv()` with safe defaults;
  `.env` is the source of secrets. See [`docs/AGENTS.md`](docs/AGENTS.md) §20.
- **Authentication/authorization:** see [`AUTH.md`](AUTH.md).
- **Data:** MySQL/MariaDB, schema source of truth `database/schema/masar.sql`;
  see [`DATABASE.md`](DATABASE.md).
- **Errors:** see [`ERRORS.md`](ERRORS.md).
- **Security:** see [`SECURITY.md`](SECURITY.md) and the hardening plan at
  [`docs/security/security-hardening-plan.md`](docs/security/security-hardening-plan.md).
- **Background jobs:** idempotent scripts in `cron/`; see
  [`config/monitoring.yml`](config/monitoring.yml).
- **Runtime storage:** `storage/` and `app/storage/` (uploads, cache, logs) are
  not committed.

## Frontend boundary

The frontend is a separate project that consumes this API. No frontend code
lives in this repository, and the backend must not depend on it beyond the
`FRONTEND_URL` redirect target for OAuth.
