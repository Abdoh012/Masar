# Masar

**From training to opportunity.**

Masar connects students and fresh graduates with real companies offering training, hands-on experience, and short-term paid or unpaid project work. The goal is to give people who cannot find a job real, verifiable experience — and give strong trainees a documented path toward an actual offer, before they ever apply elsewhere.

Two things differentiate Masar from a standard job board:

- **A certification layer** — trainees earn a real, company-backed credential on a fixed Masar-branded template.
- **Two-way discovery** — companies can find students directly, not only through postings.

**v1 scope:** Egypt (Alexandria/Cairo focus), English UI (Arabic UI planned for v2).

---

## Repository structure

```
frontend/   Next.js 15 + React 19 + TypeScript + Tailwind v4 (deployed to Vercel)
backend/    PHP 8.2+ REST API (framework-free, MySQL, JWT)
```

## Current status

Both applications are real and runnable. The **student journey is end-to-end against the live PHP API**; the company and admin areas are partly wired (listings) and partly still UI-only placeholders.

| Area | Status |
| --- | --- |
| Backend API | Implemented — 13 domain modules, MySQL schema + migrations, OpenAPI contract, Postman collection, regression suites. |
| Frontend — auth | Implemented and backend-wired (student/company sign-up, sign-in, password reset via OTP, company approval gate). |
| Frontend — student area | Implemented: dashboard, listings, applications, certificates, profile (UI-only), public marketing pages. |
| Frontend — company area | Listings create/edit/close are UI-only (feature-local mock store); dashboard, students, messages, profile are placeholders. |
| Frontend — admin area | Listings moderation is UI-only; companies and certificates are placeholders. |
| Chat / notifications | Backend exists; the frontend chat is a placeholder and notifications render from the dashboard payload. |

### Completed features

**Authentication & access**

- Two-step student/company sign-up (basic details → profile information, one register call), sign-in, forgot password → OTP verification → reset, and a post-reset landing page.
- Company approval gate: freshly registered companies land on `/company/pending-approval` until an admin approves them; approval status never blocks the rest of the company area.
- Middleware owns request-level access: role gating, auth-route redirection, and access-token refresh with server-side rotation and cookie persistence.
- Shared auth shell, brand lockup, theme toggle, and toast-driven form feedback.

**Student dashboard** (`/dashboard`)

- Backed by a single `GET /students/dashboard` call: identity hero with profile-completion meter, active training progress (date-derived), three quick-stat cards, applications snapshot, recommended listings, certificates snapshot, and recent notifications.

**Trainings / listings** (`/listings`)

- Browse published trainings from the API with keyword search, saved-only toggle, sort and filter controls, plus a content-shaped loading skeleton.
- Training detail page with mode/paid badges, deadline, application-status panel, and save/unsave.
- Recommended listings on the dashboard.
- Company-side listing create/edit with pricing, mode/format, trial days, hire intent, and close action (UI-only for now); admin moderation list for disabling listings (UI-only).

**Applications** (`/applications`, `/listings/[id]/apply`)

- Three-step application wizard with CV upload (`pdf`/`doc`/`docx`, 10 MB), profile prefill fields, and backend submission.
- My Applications page with status tabs (All, Applied, Accepted, Rejected, Withdrawn, Ended), server-side pagination, and per-status cards.
- Withdraw application (server action + confirmation dialog).
- Accepted applications show the trial countdown, bank-transfer details for paid trainings, and a payment-report form (`GET`/`POST /applications/{id}/payment`).
- Ended applications render the completion state and open the issued certificate in a view-only dialog.

**Certificates** (`/certificates`)

- Server-rendered eligibility feed, request flow, and pending/issued/revoked lifecycle groups.
- Shared `CertificateDocument` artifact (navy border, gold frame, seal, signature line) reused by the certificates page and the ended-applications dialog.
- Client-side PDF download that captures the on-screen document pixel-for-pixel on A4.

**Public site**

- Landing page (hero, features, how-it-works, CTA band), about, support, privacy, and terms pages, with shared header/footer and the Masar design system.

## Architecture

Feature-based, not role-based. `frontend/app/` is routing-only — pages are thin shells that compose feature containers. Business logic lives in `frontend/features/<feature>/`, with `shared/` plus role subfolders created only as needed. Code used by more than one feature is promoted to top-level `frontend/shared/`.

The frontend defaults to server-side reads (`serverFetch` in `services/api.ts`) so the httpOnly session cookie reaches the API; mutations go through server actions that `revalidatePath()` on success. `middleware.ts` handles session refresh because Next only allows cookie writes inside Server Actions.

The backend follows Controller → Service → Repository → Database, with all SQL in repositories and prepared statements only. See `backend/README.md` and `backend/docs/` for the API contract, auth model, database design, and deployment notes.

## Roles

| Role | Description |
| --- | --- |
| **Student** | A student or fresh graduate seeking training or experience. |
| **Company** | An organization offering training, shadowing, or paid project work (pending until Admin approves). |
| **Admin** | Masar's internal team — approval gate, moderation, certificate oversight. |

## Core flow

1. Company signs up → Admin reviews and approves → the company can post listings and browse student profiles.
2. Discovery is two-way: students browse listings filtered to their field; companies browse/search student profiles in their field.
3. Student applies to a listing with an optional CV and short note → company reviews.
4. Company accepts or rejects; on acceptance, in-app chat unlocks automatically.
5. Paid listings have a free trial period (platform-enforced minimum) before any payment can apply.
6. After training, the student requests a certificate → company confirms → the certificate (with accurate dates) appears on the student's profile, optionally with a confirmed "hire opportunity" flag. Disputes escalate to Admin.

## Getting started

### Frontend

```bash
cd frontend
pnpm install
cp .env.example .env      # set NEXT_PUBLIC_API_URL to your backend
pnpm dev                  # http://localhost:3000
```

`NEXT_PUBLIC_API_URL` must include the `/api/v1` prefix (default `http://localhost:8000/api/v1`). The frontend is deployed on Vercel with this variable pointing at a publicly reachable `https` backend.

### Backend

```bash
cd backend
composer install
cp .env.example .env      # database + JWT + mail settings
mysql -u root masar < database/schema/masar.sql
php -S localhost:8000 -t public
```

Under Laragon/Apache the API is reached at `http://localhost/Masar/backend/api/v1`; the health check is `/api/v1/health`. Full local setup: `backend/docs/SETUP_LARAGON.md`.

## Repository commands

| Location | Command | Purpose |
| --- | --- | --- |
| `frontend/` | `pnpm dev` | Dev server at http://localhost:3000 |
| `frontend/` | `pnpm build` | Production build |
| `frontend/` | `pnpm exec tsc --noEmit` | Type check (lint is not configured yet) |
| `backend/` | `php -l path/to/file.php` | PHP syntax lint |
| `backend/` | `php tests/<name>_regression.php` | Run a regression suite against a live database |
| `backend/` | `composer validate --no-check-publish` | Validate Composer metadata |

## Design system

Full visual identity lives in `frontend/masar-identity.html` and is implemented as Tailwind v4 `@theme` tokens in `frontend/app/globals.css`: ink navy primary, seal gold secondary, paper/stone neutrals. The circular seal/stamp mark is used wherever something is verified — company approval, certificate issuance, confirmed hire opportunity — and `sage` is reserved exclusively for the confirmed-hire signal.

## Documentation

- `frontend/README.md` — frontend structure and conventions.
- `AGENTS.md` (repo root) — engineering rules for the frontend.
- `backend/README.md` — backend stack, layout, and commands.
- `backend/docs/` — architecture, auth, database, security, deployment, and per-domain API references.
- `backend/openapi.yaml` — machine-readable API contract.
