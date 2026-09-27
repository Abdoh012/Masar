# MASAR Backend - Authentication and Authorization

> Endpoint-level detail: [`docs/api/authentication.md`](docs/api/authentication.md)
> and `docs/api/users.md`. Deep reference: `docs/architecture/architecture.md`
> (auth sections) and `docs/AGENTS.md` §22.

## Authentication model

MASAR uses **JWT access tokens** for API authentication plus an **HttpOnly
refresh cookie** for session continuity.

| Token | Where | Lifetime (default) | Purpose |
| --- | --- | --- | --- |
| Access token | `Authorization: Bearer <jwt>` | `JWT_ACCESS_TTL` (3600s) | Authenticates each request |
| Refresh token | HttpOnly cookie | `JWT_REFRESH_TTL` (2592000s) | Obtains a new access token |
| Remember cookie | `MASAR_REMEMBER` | see `response_remember_cookie_settings` | Optional persistent login |

- JWTs are signed with `JWT_ALGORITHM` (default HS256) and `JWT_SECRET`.
- Cookie-based state-changing requests must send the CSRF header
  (`CSRF_HEADER_NAME`, default `X-CSRF-Token`); see `app/core/middleware/csrf.php`.
- `SECURE_COOKIES=true` marks cookies Secure (required in production/HTTPS).

## Flows

**Registration** -> `POST /api/v1/auth/register` (student or company; students
must supply a valid seeded `field` + `specialization`).

**Login** -> `POST /api/v1/auth/login` returns an access token and sets the
refresh cookie. Login is rate-limited (`sensitive` tier).

**Refresh** -> `POST /api/v1/auth/refresh` reads the refresh cookie and returns a
new access token.

**Logout** -> `POST /api/v1/auth/logout` invalidates the session and clears the
refresh cookie.

**Google OAuth** -> `GET /api/v1/auth/google` starts the flow;
`GET /api/v1/auth/google/callback` completes it. The callback query string must
never be logged (see `docs/AGENTS.md` §14).

**Password reset** -> `POST /api/v1/auth/forgot-password` ->
`GET|POST /api/v1/auth/resend-reset-otp` -> `POST /api/v1/auth/verify-reset-otp`
-> `POST /api/v1/auth/reset-password`. Responses never reveal whether an email
exists.

**Change password** -> `POST /api/v1/auth/change-password` (authenticated).

Current user -> `GET /api/v1/auth/me`.

## Authorization

- **Middleware** (`app/core/middleware/`): `middleware_auth`,
  `middleware_jwt_auth`, `middleware_admin`, `middleware_company`,
  `middleware_student`, `csrf_require`.
- **RBAC:** permission map in `app/shared/functions/authorization.php`
  (`auth_role_permissions`, `auth_user_has_permission`). Roles: `admin`,
  `super_admin`, `student`, `company`, `trainer`, `moderator`, `employee`,
  `guest`.
- **Ownership checks:** `auth_user_can_access_resource` /
  `auth_require_ownership` before reading or writing a resource (prevents
  IDOR/BOLA).
- **Sensitive admin actions:** require re-authentication via
  `security_require_admin_reauth`.
- **Account gating:** inactive, suspended, or blocked users cannot act
  (`auth_user_is_active`).
- **Never trust role/id in a JWT blindly** - re-check against the database when
  it matters (`docs/AGENTS.md` §13).

## Security rules (must hold)

1. Passwords are hashed with a secure algorithm; never stored or logged in plain text.
2. Tokens and secrets are never logged or echoed.
3. Reset tokens expire and are single-use.
4. Login and reset flows are rate-limited.
5. Sensitive auth errors do not disclose account existence.
6. Protected endpoints validate the authenticated user and required role.
7. Secrets live in `.env`, read via `getenv()`.
