import { Briefcase, Check, Undo2, X, type LucideIcon } from "lucide-react";

import type { ApplicationStatus, StatusCounts } from "../../types";

// The four status tiles, in display order. The backend sends the counts as
// `applications_snapshot.{applied,accepted,rejected,withdrawn}`; the labels and
// their order are this section's own presentation concern.
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

// The status's own colour, painted as a full-height rail down the tile's left
// edge. The rail is what carries the semantic meaning at a glance while the tile
// surface itself stays neutral — four saturated panels side by side read as a
// colour chart, and this keeps the number the loudest thing on the card.
export const STATUS_RAIL_CLASSES: Record<ApplicationStatus, string> = {
  Applied: "bg-primary",
  Accepted: "bg-success-fg",
  Rejected: "bg-error-fg",
  Withdrawn: "bg-neutral-badge-fg",
};

// How the recent row's status pill differs from the default ApplicationStatusBadge
// geometry. "Applied" through "Withdrawn" differ by two characters, which is
// enough to make the right edge of the row jump around when the label changes,
// so a fixed floor plus centred text keeps it still; the row is standalone, so it
// also gets a slightly heavier pill than the one sitting inside a card's badge row.
export const RECENT_ROW_BADGE_CLASS =
  "inline-flex items-center px-3 py-1 font-semibold min-w-20 justify-center text-center";

// Maps display status → StatusCounts key (labels are capitalized, count keys lowercase).
export const STATUS_COUNT_KEYS: Record<ApplicationStatus, keyof StatusCounts> = {
  Applied: "applied",
  Accepted: "accepted",
  Rejected: "rejected",
  Withdrawn: "withdrawn",
};

// Empty state copy — rendered when the backend reports no applications at all.
export const SNAPSHOT_EMPTY = {
  title: "No applications yet",
  message: "Applications you submit will appear here.",
} as const;

// Captions for the header's headline pair. The numbers themselves are the API's
// (`total`, `acceptance_rate`); only the wording is this section's own.
export const SNAPSHOT_TOTALS_LABELS = {
  total: "total",
  acceptanceRate: "accepted",
} as const;
