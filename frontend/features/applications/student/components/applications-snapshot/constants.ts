import type { ApplicationStatus, RecentApplicationRow, StatusCounts } from "../../types";

// Mock applications-snapshot data (UI-only).
export const APPLICATION_STATUSES: ApplicationStatus[] = ["Applied", "Accepted", "Rejected", "Withdrawn"];

// Single source for status pill classes — keeps badges, rows and the Accepted
// sage signal in sync (component-contracts.md §3 fixed mapping).
export const STATUS_BADGE_CLASSES: Record<ApplicationStatus, string> = {
  Applied: "bg-primary-tint text-primary-text",
  Accepted: "bg-sage-tint text-sage-text",
  Rejected: "bg-error-bg text-error-fg",
  Withdrawn: "bg-neutral-badge-bg text-neutral-badge-fg",
};

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