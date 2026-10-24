# Masar — Frontend

Next.js 15 + React 19 + TypeScript + Tailwind v4. Structure follows
`nextjs-architecture-directive.md` and `component-structure-rules.md`.
There is **no `src/` directory** — `app/`, `features/`, `shared/`,
`services/`, `config/`, and `types/` are top-level.

## Stack

- Next.js 15 App Router, React 19, TypeScript
- Tailwind CSS v4 (`@theme` tokens in `app/globals.css`)
- shadcn-style primitives on Radix UI, lucide icons
- Framer Motion through the shared `Motion` abstraction
- Sonner toasts, `next-themes` for light/dark
- `html-to-image` + `jsPDF` for client-side certificate PDFs

## Commands

```bash
pnpm dev                  # http://localhost:3000
pnpm build
pnpm start
pnpm exec tsc --noEmit    # the working type check (ESLint config is not set up yet)
```

## Environment

Copy `.env.example` to `.env`:

- `NEXT_PUBLIC_API_URL` — base URL of the PHP API **including** `/api/v1`
  (default `http://localhost:8000/api/v1`). It must be publicly reachable
  when deployed, because reads happen server-side via `serverFetch`.
- `NEXT_PUBLIC_SITE_URL` — public URL of this app (currently unused by app code).

## What's implemented

| Area | Routes | Status |
| --- | --- | --- |
| Public site | `/`, `/about`, `/support`, `/privacy`, `/terms` | Done |
| Auth | `/sign-in`, `/sign-up`, `/profile-information`, `/forgot-password`, `/verify-otp`, `/reset-password/[token]`, `/password-updated` | Backend-wired |
| Company gate | `/company/pending-approval` | Done |
| Student dashboard | `/dashboard` | Backend-wired |
| Student listings | `/listings`, `/listings/[id]` | Backend-wired |
| Apply | `/listings/[id]/apply` | Backend-wired |
| Applications | `/applications` | Backend-wired |
| Certificates | `/certificates` | Backend-wired |
| Profile | `/profile` | UI-only (local state) |
| Company listings | `/company/listings`, `/company/listings/new`, `/company/listings/[id]` | UI-only (feature-local mock store) |
| Admin moderation | `/admin/listings` | UI-only (local state) |
| Placeholders | company dashboard/students/messages/profile, admin companies/certificates, student messages | Not built |

## Architecture

- `app/` is routing-only. Pages are thin shells that unwrap params and compose
  feature containers; route groups `(public)`, `(auth)`, `(student)` have no URL
  prefix, while `company/` and `admin/` are real segments.
- `features/<feature>/` owns business logic, with `shared/` plus role subfolders
  (`student/`, `company/`, `admin/`) created only when needed. Features expose
  exactly one public surface, `index.ts`.
- `shared/` holds cross-feature primitives: UI components, the `PageHeader`
  band, `CertificateDocument`, pagination, skeletons, animation variants.
- `services/api.ts` (`serverFetch`/`apiFetch`) is the only HTTP layer; feature
  `api.ts` files call it with `cache: "no-store"`. Mutations are server actions
  in `actions.ts` that call `revalidatePath()`.
- `middleware.ts` enforces role-based access and owns access-token refresh via
  the backend's rotating refresh cookie.
- Session cookies: `masarJwt`, `masarRole`, `companyStatus`, `refresh_token`,
  `csrf_token` — constants live in `shared/lib/authCookies.ts`.

## Design system

Tokens come from `masar-identity.html`. Navy primary, seal-gold secondary,
paper/stone neutrals; `sage` is reserved for the confirmed-hire signal. Card
elevation is a four-tier scale (`shadow-card`, `-md`, `-lg`, `lift`). The seal
logo is the raw `public/logo.png`, never redrawn.

## Backend

The PHP API lives in `../backend` — see `../backend/README.md`.
