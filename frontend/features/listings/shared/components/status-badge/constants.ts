// Badge-scoped static data only. The status label map itself lives in
// features/listings/shared/lib/constants.ts (R-1) — this file holds the
// pill styling and a11y copy specific to the StatusBadge leaf.

export const STATUS_BADGE_ARIA_LABEL = "Listing status";

export const STATUS_BADGE_CLASSES =
  "inline-flex items-center rounded-full bg-primary-tint px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-primary-text";