# Changelog

All notable changes to the MASAR backend are recorded in this file.

The project is not yet version-tagged; the API is versioned under `/api/v1`.

> **Note:** this changelog was introduced on 2026-09-17. Earlier history was not
> maintained as a changelog and most older commits are generic
> ("Update backend" / "replace backend with local version"), so they are not
> itemized here. The baseline section summarizes the functionality present when
> this file was introduced.

## [Unreleased]

### Added
- Project configuration: `.editorconfig`, `.gitattributes`, `phpstan.neon`,
  `.php-cs-fixer.dist.php`, and a `tests/bootstrap.php` for PHPUnit.
- `openapi.yaml` describing the current `/api/v1` surface.
- Top-level documentation: `README.md`, `ARCHITECTURE.md`, `DATABASE.md`,
  `AUTH.md`, `ERRORS.md`, `DEPLOYMENT.md`, `SECURITY.md`,
  `SECURITY_CHECKLIST.md`, and this `CHANGELOG.md`.
- Reference configuration files under `config/` (`cache.yml`, `logging.yml`,
  `monitoring.yml`, `alerts.yml`).
- CI workflows: `tests.yml`, `quality.yml`, and `security.yml`.

### Changed
- `.env.example` now contains placeholders only (real Gmail and reCAPTCHA
  credentials removed), de-duplicates repeated blocks, and covers the
  environment keys the code actually reads.
- `.gitignore` now ignores runtime uploads under `app/storage/` and quality-tool
  caches, and no longer ignores images/PDFs repo-wide.

### Fixed
- `phpunit.xml` referenced a missing `tests/bootstrap.php`; the bootstrap now
  exists, so `composer test` no longer fails to start.

## [Baseline] - 2026-09-17

Current feature surface at the time this changelog was introduced:

- **Authentication:** registration, login, refresh, logout, current user,
  change/forgot/reset password with OTP, and Google OAuth.
- **Users:** self profile read/update/delete and user lookup.
- **Students:** profile create/update/status/complete, skills management, and CV
  upload/delete.
- **Companies:** company profile, logo upload, and company lookup.
- **Trainings:** create/update/delete, browse/list, details, sessions, and
  save/unsave for students.
- **Applications:** submission, student status tabs (applied/accepted/rejected/
  withdrawn), accept/reject/withdraw, CV download, and payment reporting.
- **Certificates:** request, eligibility, pending/issued/revoked lists, search,
  statistics, verify/confirm/revoke, and appeals.
- **Files:** single/multiple upload, metadata, and controlled download.
- **Messaging and notifications:** conversations/messages and per-user
  notifications with unread counts and read state.
- **Search and lookups:** unified and scoped search, suggestions/recent, plus
  study-field and specialization lookups.
- **Admin:** dashboard and admin student management.
- **Background jobs:** close-expired-trainings, expire-trial-periods,
  send-expiry-notifications, cleanup-temp-files, cleanup-expired-tokens, and
  cleanup-audit-logs.
