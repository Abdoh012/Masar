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
frontend/
backend/
```

## Current status

Early-stage scaffold. The frontend foundation is in place; feature logic and the backend are not yet implemented.

| Area | Status |
| --- | --- |
| Frontend | Scaffold with real infrastructure (design system, routing, config, services, shared UI). Feature modules are empty stubs. |
| Backend | Not yet implemented. |

## Frontend

Next.js 15 + TypeScript, feature-based architecture (not role-based). `app/` stays routing-only; each feature owns its business logic under `frontend/features/`, with a `shared/` subfolder plus role subfolders created only as needed. Code that more than one feature needs is promoted to `frontend/shared/`.

### What is wired up

- **Design system** — IBM Plex Sans / Serif / Mono / Arabic fonts, and Tailwind v4 design tokens pulled directly from `masar-identity.html` (ink navy, seal gold, paper, sage, stone). Sage is reserved exclusively for the "hire opportunity confirmed" signal.
- **Routing skeleton** — `(public)`, `(auth)`, and student routes share no URL prefix; `company/` and `admin/` are real URL prefixes. Landing and placeholder pages exist for every area (auth, student dashboard/listings/applications/certificates/messages/profile, company dashboard/listings/applicants/messages/pending-approval/profile/students, admin companies/listings/certificates).
- **Config** — routes, role-based path permissions, per-role navigation, site metadata, environment access.
- **Services** — `apiFetch` wrapper (JWT header, empty-body-safe responses), cookie helpers, `getSession()` for server components.
- **Types** — `Role`, `Session`, `CompanyStatus`, `ApiResponse<T>`, `PaginatedResponse<T>`, `ActionState<T>`.
- **Shared UI primitives** — minimal shadcn baseline: button, card, input (more added per-feature via the shadcn CLI).

### Not yet implemented

Feature modules for `auth`, `listings`, `applications`, `chat`, `certificates`, `notifications`, and `profiles` are stubbed as folders and public-surface `index.ts` files only. The intended behavior for each is documented in the stubs, but no components, hooks, actions, or API calls exist yet.

## Backend

Not started — `backend/` is reserved for the backend teammate.

## Roles

| Role | Description |
| --- | --- |
| **Student** | A student or fresh graduate seeking training or experience. |
| **Company** | An organization offering training, shadowing, or paid project work (pending until Admin approves). |
| **Admin** | Masar's internal team — approval gate, moderation, certificate oversight. |

## Core flow

1. Company signs up → Admin reviews and approves → the company can post listings and browse student profiles.
2. Discovery is two-way: students browse listings filtered to their field; companies browse/search student profiles in their field.
3. Student applies to a listing (optional short note) → company reviews.
4. Company accepts or rejects; on acceptance, in-app chat unlocks automatically.
5. Paid listings have a free trial period (platform-enforced minimum) before any payment can apply.
6. After training, the student requests a certificate → company confirms → the certificate (with accurate dates) appears on the student's profile, optionally with a confirmed "hire opportunity" flag. Disputes escalate to Admin.

## Getting started

```bash
cd frontend
pnpm install      # or npm install
pnpm dev          # http://localhost:3000
```

The frontend expects a backend API at `NEXT_PUBLIC_API_URL` (default `http://localhost:4000/api`). See `frontend/.env.example` for the available environment variables.

## Design system

Full visual identity lives in `frontend/masar-identity.html`. The signature element is a circular seal/stamp mark used wherever something is verified — company approval, certificate issuance, confirmed hire opportunity.
