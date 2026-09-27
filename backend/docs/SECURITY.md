# MASAR Backend - Security

> This document summarizes the security model **as implemented today** and links
> to the authoritative sources. The forward-looking backlog lives in
> [`docs/security/security-hardening-plan.md`](docs/security/security-hardening-plan.md).
> Operational checks are in [`SECURITY_CHECKLIST.md`](SECURITY_CHECKLIST.md).

Security is layered: web server -> HTTP -> authentication -> authorization ->
validation -> business rules -> database constraints -> audit. Treat every layer
as required; a single missing layer is a defect.

## Implemented controls

### Authentication and sessions
- Passwords are hashed with a secure algorithm; never stored plain.
- JWT access tokens (HS256 by default) + HttpOnly refresh cookie; optional
  remember cookie. Access tokens are short-lived (`JWT_ACCESS_TTL`).
- CSRF protection for cookie-based state-changing requests
  (`app/core/middleware/csrf.php`).
- Login/reset flows are rate-limited at the `sensitive` tier.
- Account status gating: inactive/suspended/blocked users cannot act.

### Authorization
- Role-based access via middleware (`middleware_admin`,
  `middleware_company`, `middleware_student`) and a permission map in
  `app/shared/functions/authorization.php`.
- Ownership checks (`auth_user_can_access_resource`) before resource access to
  prevent IDOR/BOLA.
- Sensitive admin actions require re-authentication
  (`security_require_admin_reauth`).
- Role/id in a JWT is never trusted blindly; it is re-checked against the
  database when it matters.

### Input and output
- Never trust client input: validate size, type, structure.
- Parameterized (prepared) SQL statements only; dynamic ORDER BY/columns are
  allowlisted.
- File uploads validate extension, MIME, and size, generate random filenames,
  block dangerous extensions, and are never executed. Downloads go through
  controlled endpoints - not raw filesystem paths.
- Rich/messaging content is treated as untrusted; CSP and security headers are
  applied by `security_apply_http_headers()`.
- Error responses never leak stack traces or internals in production.

### Rate limiting
- File-backed sliding-window limiter in `app/shared/functions/security.php`.
- Tiers: `global`, `ip`, `user`, `endpoint`, `sensitive` (see
  `security_rate_limit_tier_defaults`).
- Disabled for local testing when `RATE_LIMIT_ENABLED=false` and not production.

### Transport, cookies, CORS
- `SECURE_COOKIES=true` marks cookies Secure; HttpOnly + SameSite are used.
- CORS uses explicit allowed origins (`CORS_ALLOWED_ORIGINS`) - never `*` for
  authenticated APIs in production.
- HTTPS/TLS is a deployment requirement (see [`DEPLOYMENT.md`](DEPLOYMENT.md)).

### Secrets and auditing
- Secrets live in `.env` and are read via `getenv()`; `.env` is never committed.
- Never log passwords, JWTs, refresh/reset/OTP tokens, OAuth codes/state, or
  payment credentials. The Google OAuth callback query string is never logged.
- Central audit helper (`audit_log_event`) records privileged actions to
  `audit_logs` (actor, action, entity, before/after values, IP, user agent).
- Audit history is short-lived by default: `cron/cleanup_audit_logs.php` deletes
  **all** `audit_logs` rows on each run (no retention period). Adjust or disable
  it to meet your retention policy.

## Reporting a vulnerability

Do not open a public issue for security problems. Report privately to the
maintainers with reproduction steps, affected endpoint, and impact.

## Known gaps and next steps

The hardening plan tracks work that is not yet complete (for example refresh-token
rotation/reuse detection, object-level authorization coverage, admin MFA,
malware scanning on uploads). See
[`docs/security/security-hardening-plan.md`](docs/security/security-hardening-plan.md)
for the prioritized list before making security claims about a deployment.
