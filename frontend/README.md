# Masar — Project Scaffold

Structure follows `nextjs-architecture-directive.md` +
`component-structure-rules.md`. This is the bare skeleton agreed on:
**infrastructure is real, `features/` is empty folders** — fill in feature
code together, feature by feature.

## What's actually wired up

- **Fonts** (`src/app/layout.tsx`) — IBM Plex Sans (UI), IBM Plex Serif
  (certificate/formal text), IBM Plex Mono (certificate IDs), IBM Plex Sans
  Arabic (wired now for v2, unused in v1 UI).
- **Design tokens** (`src/app/globals.css`) — Tailwind v4 `@theme`, colors
  pulled straight from `masar-identity.html`. `sage` is reserved for the
  "hire opportunity confirmed" signal only — don't reuse it as a generic
  success color.
- **`middleware.ts`** — fixed from the uploaded `proxy.ts` (which was never
  actually being called — Next requires the file named `middleware.ts` with
  an export named `middleware`). Extended for 3 roles (student/company/admin)
  and to lock pending companies to `/company/pending-approval` until Admin
  approves them.
- **`shared/components/animation/Motion.tsx`** — fixed the `motion[as] as
  ElementType` bug (breaks in Framer Motion v11+) using your established
  `motion.create()` + `useMemo` pattern. Import corrected to `framer-motion`
  to match your dependency list.
- **`shared/lib/animations.ts`** — your variants file, unchanged, just
  relocated.
- **Root `loading.tsx` / `error.tsx` / `not-found.tsx`** — plus one set per
  route segment, per Rule R2.
- **`shared/components/ui/`** — minimal shadcn baseline (button, card,
  input). Add more via the shadcn CLI as features need them:
  `npx shadcn@latest add dialog`
- **`services/`** — base `apiFetch` wrapper (JWT header, empty-body-safe
  like your el-le3ba `DELETE` fix), cookie helpers, `getSession()`.
- **`config/`** — routes, role→path permissions, nav items per role, site
  metadata.
- **`types/`** — `Role`, `Session`, `ApiResponse<T>`, `PaginatedResponse<T>`,
  `ActionState<T>`.

## What's intentionally empty

- **`src/features/*`** — folders only (`shared/` + the roles that need
  feature-specific logic), each with an `index.ts` stub explaining what
  belongs there. No components, hooks, actions, or api calls yet.
- **`src/shared/components/{dialogs,forms,layout,navigation,...}`** — empty,
  created on demand per the promote-on-second-use rule.

## Route shape

- `(public)`, `(auth)`, `(student)` — route groups, **no URL prefix**.
  Student is the default/unprefixed area.
- `company/`, `admin/` — real URL prefixes (required — a route group can't
  share a URL with another group, so company/admin needed real segments to
  avoid colliding with student routes like `/dashboard`).

## Next steps

1. `npm install` (or `pnpm install`, matching your usual workflow).
2. Nail the API contract for Auth + Listings + Applications with your
   backend teammate.
3. Build the `auth` feature first (student/company sign-up, company
   approval) — see build-order reasoning from our planning conversation.
4. `npx shadcn@latest init` isn't needed — `components.json` is already
   configured; just `npx shadcn@latest add <component>` as needed.
