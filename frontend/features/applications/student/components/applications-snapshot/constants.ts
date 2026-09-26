import { Briefcase, Check, Undo2, X, type LucideIcon } from "lucide-react";

import type { ApplicationStatus, RecentApplicationRow, StatusCounts } from "../../types";

// Mock applications-snapshot data (UI-only).
export const APPLICATION_STATUSES: ApplicationStatus[] = ["Applied", "Accepted", "Rejected", "Withdrawn"];

// Single source for status pill classes — keeps the dashboard tiles, the recent
// rows and the My Applications badges in sync (component-contracts.md §3 fixed
// mapping). Accepted uses the `success` token, not `sage`: sage is reserved
// exclusively for the confirmed-hire signal (design system), and every other
// status surface in the app already honours that.
export const STATUS_BADGE_CLASSES: Record<ApplicationStatus, string> = {
  Applied: "bg-primary-tint text-primary-text",
  Accepted: "bg-success-bg text-success-fg",
  Rejected: "bg-error-bg text-error-fg",
  Withdrawn: "bg-neutral-badge-bg text-neutral-badge-fg",
};

// The stat tile's glyph. The tile itself is a neutral lifted panel, so the icon
// — not a block of colour — is what tells the four statuses apart; it reuses
// the same bg/text pair as the pill so the two never drift.
export const STATUS_COUNT_ICONS: Record<ApplicationStatus, LucideIcon> = {
  Applied: Briefcase,
  Accepted: Check,
  Rejected: X,
  Withdrawn: Undo2,
};

// The stat number's own colour, so the four counts can be scanned at a glance
// without reading each label. Split from the tile surface above because the
// number needs more saturation than the pill's text carries.
export const STATUS_COUNT_ACCENT_CLASSES: Record<ApplicationStatus, string> = {
  Applied: "text-primary-text",
  Accepted: "text-success-fg",
  Rejected: "text-error-fg",
  Withdrawn: "text-neutral-badge-fg",
};

// One width for every status pill. "Applied" through "Withdrawn" differ by two
// characters, which is enough to make the right edge of the row jump around
// when the label changes; a fixed floor plus centred text keeps it still.
export const STATUS_BADGE_MIN_WIDTH_CLASS = "min-w-20 justify-center text-center";

// Maps display status → StatusCounts key (labels are capitalized, count keys lowercase).
export const STATUS_COUNT_KEYS: Record<ApplicationStatus, keyof StatusCounts> = {
  Applied: "applied",
  Accepted: "accepted",
  Rejected: "rejected",
  Withdrawn: "withdrawn",
};

export const STATUS_COUNTS: StatusCounts = {
  applied: 4,
  accepted: 1,
  rejected: 2,
  withdrawn: 1,
};

export const RECENT_APPLICATIONS: RecentApplicationRow[] = [
  {
    id: "app-1042",
    companyName: "Hala Bank",
    listingTitle: "Software Engineering Trainee",
    status: "Accepted",
    appliedOn: "Jul 20, 2026",
  },
  {
    id: "app-0991",
    companyName: "NileGrants",
    listingTitle: "Data Intern",
    status: "Rejected",
    appliedOn: "Jul 02, 2026",
  },
  {
    id: "app-1010",
    companyName: "Seera Digital",
    listingTitle: "Frontend Intern",
    status: "Withdrawn",
    appliedOn: "Jun 28, 2026",
  },
];

// Empty variant: zeroed counts + no rows → "No applications yet".
export const APPLICATIONS_EMPTY: { counts: StatusCounts; rows: RecentApplicationRow[] } = {
  counts: { applied: 0, accepted: 0, rejected: 0, withdrawn: 0 },
  rows: [],
};