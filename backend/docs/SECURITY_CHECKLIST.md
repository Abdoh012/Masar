# MASAR Backend - Pre-Deployment Security Checklist

Use this before exposing an environment to real users. Items marked
**(required)** must pass; others are hardening tracked in
[`docs/security/security-hardening-plan.md`](docs/security/security-hardening-plan.md).

## Environment and secrets

- [ ] `.env` exists on the server, is **not** in version control, and is not web-accessible. **(required)**
- [ ] `APP_ENV=production` and `APP_DEBUG=false`. **(required)**
- [ ] `JWT_SECRET` is a long random value unique to this environment (not the example value). **(required)**
- [ ] Mail, Google OAuth, and reCAPTCHA credentials are real values, not example placeholders, and are stored only in `.env`. **(required)**
- [ ] Secrets are rotated on any suspected exposure and removed from history.

## Transport and cookies

- [ ] HTTPS/TLS is enforced for the API and frontend; HTTP redirects to HTTPS. **(required)**
- [ ] `SECURE_COOKIES=true`. **(required)**
- [ ] `CORS_ALLOWED_ORIGINS` lists explicit trusted origins (never `*`). **(required)**
- [ ] HSTS, `X-Content-Type-Options`, and clickjacking protection are active at the web server or via security headers.

## Access control

- [ ] Admin, company, and student endpoints return 403 for the wrong role (verified manually). **(required)**
- [ ] Object-level ownership is enforced for resource reads/writes (student/company cannot read another user's records). **(required)**
- [ ] Sensitive admin actions require re-authentication.
- [ ] Account status gating blocks inactive/suspended/blocked users.

## Rate limiting and abuse protection

- [ ] `RATE_LIMIT_ENABLED=true` in production. **(required)**
- [ ] Login, password reset, refresh, upload, search, and messaging are rate-limited. **(required)**
- [ ] `storage/cache/security` is writable by the web user and reviewed for stale files.

## Uploads and files

- [ ] Upload size limits, extension, and MIME validation are active. **(required)**
- [ ] Upload directories are not executable and are outside the public web root. **(required)**
- [ ] Downloads always go through controlled endpoints (no raw filenames from user input).
- [ ] Malware scanning is integrated where available.

## Database

- [ ] The database user has least privilege (no `DROP`/`GRANT` for the app user). **(required)**
- [ ] The database is not reachable from the public internet. **(required)**
- [ ] Prepared statements are used everywhere; ORDER BY/columns are allowlisted. **(required)**
- [ ] Backups run and restores have been tested.

## Errors, logging, and monitoring

- [ ] Production responses never contain stack traces or internal detail. **(required)**
- [ ] `storage/logs/` is writable and not web-accessible. **(required)**
- [ ] Tokens, passwords, reset codes, OAuth codes, and payment credentials are never logged. **(required)**
- [ ] `composer audit` is run and known advisories are addressed.
- [ ] Alerting covers 5xx spikes, failed-login spikes, rate-limit hits, and missed cron runs (see `config/alerts.yml`).

## Operations

- [ ] `cron/` jobs are scheduled and idempotent; the HTTP cron hook (`/cron`, `/cron/run`) is protected or disabled.
- [ ] The web user has least-privilege filesystem permissions on `storage/` and `app/storage/`.
- [ ] A rollback plan (previous release + DB restore point) is ready before deploying.
- [ ] The disposable `tests/` scripts and any debug endpoints are not exposed publicly.

## Post-deployment smoke test

- [ ] `GET /api/v1/health` returns `success: true`.
- [ ] Login returns a token and sets the refresh cookie; refresh works.
- [ ] A protected endpoint returns 401 without a token and 200 with one.
- [ ] A cross-role request returns 403.
