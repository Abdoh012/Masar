// Card-scoped static copy only. Mode/format option lists live in
// features/listings/shared/lib/constants.ts (R-1); badges own their labels.
// The obsolete LISTING_MODE_LABELS (INTERN/TRAINEE/APPRENTICE) was removed —
// modes are the shared ListingMode values now.
export const CARD_ACTION_LABEL = "View training";
export const CARD_SKILLS_ARIA_LABEL = "Skills";
export const CARD_META_POSTED_PREFIX = "Posted";

// Category pill accents, keyed by the listing's field. Brand-token accents
// only (navy/gold/beige) — sage stays reserved for the hire signal.
export const CATEGORY_STYLES: Record<string, { pill: string }> = {
  "Software Engineering": {
    pill: "bg-secondary-tint text-secondary-text",
  },
  "Data & Analytics": {
    pill: "bg-primary-tint text-primary-text",
  },
  Design: {
    pill: "bg-neutral-200 text-neutral-800",
  },
  Marketing: {
    pill: "bg-secondary-tint text-secondary-text",
  },
};

export const DEFAULT_CATEGORY_STYLE = {
  pill: "bg-primary-tint text-primary-text",
};