// Badge-scoped static data only. Pricing values (isPaid/trialDays) arrive
// as props — this file holds the display copy and pill styling specific to
// the PaidBadge leaf (spec Paid presentation: "Free" / "Paid · 7d trial").

export const PAID_BADGE_ARIA_LABEL = "Paid status";

export const PAID_BADGE_CLASSES =
  "rounded-full bg-primary-tint px-2.5 py-1 text-[11px] font-semibold text-primary-text";

export const FREE_LABEL = "Free";

// Rendered as "Paid · {n}d trial"; trialDays defaults to the platform
// minimum (TRIAL_MIN_DAYS) when a paid listing omits it.
export const PAID_LABEL_PREFIX = "Paid";
export const PAID_LABEL_TRIAL_SUFFIX = "d trial";