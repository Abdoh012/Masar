# MASAR Security Hardening Plan

## 1. Authentication Security

### Implemented baseline
- Strong password validation
- JWT access and refresh tokens
- Refresh cookie + CSRF protection
- Rate limiting for auth endpoints
- Security headers
- Account activation gate for company accounts

### Remaining production hardening
- Refresh token rotation
- Refresh token reuse detection
- Token revocation and invalidation
- Token family tracking per user session family
- Session management and concurrent session control
- Password history checks
- Password change invalidates active sessions
- Email verification enforcement
- Password reset token security and single-use handling
- Login attempt tracking and brute-force protection
- Suspicious login detection based on IP/device changes

## 2. Authorization hardening

- Add RBAC permission map for students, companies, admins
- Enforce object-level authorization for resource ownership
- Enforce action-level authorization before state-changing actions
- Prevent IDOR/BOLA by validating ownership on every resource read/write
- Distinguish between user role and current user context

## 3. Request Security

- Size limits for body, headers, and JSON payloads
- JSON depth and parameter-count validation
- Method allowlist per endpoint
- Content-Type allowlist
- Request timeout and malformed request rejection
- Replay detection for sensitive operations

## 4. Rate Limiting

- Global, IP, user, endpoint, user+IP, and sensitive-action tiers
- Progressive delay / lockout strategy
- Higher limits for sensitive actions like login, reset, refresh

## 5. CORS Security

- Explicit allowed origins in production
- Restrict methods and headers
- Set credentials only for trusted origins
- Use different config for dev vs production

## 6. Cookie Security

- HttpOnly, Secure, SameSite, Path, Domain, Expiration
- Ensure refresh and CSRF cookies are isolated and scoped correctly

## 7. JWT Security

- Allowlist algorithms and reject alg=none
- Validate issuer, audience, expiration, nbf, iat
- Require unique jti
- Keep access tokens short-lived
- Rotate refresh tokens and revoke old tokens
- Store issuance metadata for family tracking
- Do not trust role in JWT blindly when role may change during token lifetime

## 8. Upload Security

- extension validation
- MIME validation
- magic-bytes validation
- random file names
- path traversal protection
- double-extension blocking
- executable file blocking
- archive bomb protection
- malware scanning integration where possible
- storage isolation and download authorization

## 9. XSS prevention

- Context-aware output encoding
- HTML sanitization for rich text
- CSP headers
- Safe handling of messages and rich content
- Escape user-generated content before rendering in API-provided HTML or admin dashboard

## 10. SQL injection prevention

- No raw user input inside SQL
- Prepared statements everywhere
- Allowlist ORDER BY and column names
- Safe pagination
- Safe search filters

## 11. Race Conditions

- Use transactions and row locks for capacity-limited operations
- Protect application acceptance, payment, certificate issuance, and token rotation paths

## 12. Idempotency

- Payment webhooks and replays
- certificate generation
- refresh token operations
- notification deduplication

## 13. Payment Security

- webhook signature verification
- replay protection
- amount/currency verification
- transaction ownership checks
- server-side confirmation
- audit trail for payment state changes

## 14. Notification / Messaging Security

- Authorization on notification reads
- Rate limiting and deduplication
- Sensitive data filtering
- XSS-safe content rendering

## 15. Admin Security

- strong auth and session controls
- admin-only actions require re-auth for sensitive operations
- MFA support for privileged accounts
- audit logs for role changes and privileged operations

## 16. MFA

- architecture ready for TOTP / passkey-based MFA
- admin-first rollout

## 17. Audit and Security Events

- record events such as login success/failure, token revocation, suspicious login, authorization failures, file rejection, rate limits, and privilege changes

## 18. Security Monitoring

- detect repeated failed logins, abnormal 401/403 patterns, token reuse, large request volume, suspicious admin actions, payment anomalies

## 19. Error handling

- do not leak stack traces or internal secrets in production responses
- log technical details only server-side

## 20. Encryption and secrets

- separate hashing vs encryption
- use external secret management in production
- rotate secrets regularly
- never log tokens or secrets

## 21. Server hardening

- HTTPS/TLS/HSTS
- reverse proxy and firewall controls
- secure PHP config
- least-privilege file permissions
- database network restrictions

## 22. Dependency security

- run composer audit
- keep dependencies updated
- review composer.lock and security advisories

## 23. Immediate next implementation priorities

1. JWT refresh rotation + token family tracking
2. object-level ownership checks in resources
3. upload validation hardening
4. admin sensitive actions + audit logging
5. rate-limit tiers by user/IP/endpoint
6. CORS allowlist for production
7. exception sanitization
8. dependency audit
