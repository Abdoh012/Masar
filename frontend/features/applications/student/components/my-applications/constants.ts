// Static data + labels for the My Applications page section (UI-only; the real
// list is fetched by the route in a later phase — structure rules §14).

import type { ApplicationStatus, MyApplication } from "../../types";

// Header copy.
export const APPLICATIONS_TITLE = "My Applications";

// Status tabs. "All" is the default active tab; values drive client-side
// filtering on the already-loaded list (FR-003/005/006).
export type TabValue = "all" | "applied" | "accepted" | "rejected" | "withdrawn";

export const TABS: { value: TabValue; label: string }[] = [
  { value: "all", label: "All" },
  { value: "applied", label: "Applied" },
  { value: "accepted", label: "Accepted" },
  { value: "rejected", label: "Rejected" },
  { value: "withdrawn", label: "Withdrawn" },
];

// Mock applications covering every status plus the conditional card cases:
// accepted-paid with trial (incl. a display-only "Continue past trial" note),
// accepted free (no trial), rejected with and without a reason, and a
// "may lead to hire" case. appliedOn is ISO so sorting newest-first is exact.
export const MOCK_APPLICATIONS: MyApplication[] = [
  {
    id: "app-1042",
    listingId: "lst-101",
    listingTitle: "Software Engineering Trainee",
    companyName: "Hala Bank",
    status: "Accepted",
    appliedOn: "2026-07-20",
    mayLeadToHire: true,
    trial: { daysRemaining: 12, continuePastTrial: true },
  },
  {
    id: "app-1018",
    listingId: "lst-104",
    listingTitle: "Data Intern",
    companyName: "NileGrants",
    status: "Accepted",
    appliedOn: "2026-07-10",
    trial: { daysRemaining: 0 },
  },
  {
    id: "app-0991",
    listingId: "lst-102",
    listingTitle: "Data Intern",
    companyName: "NileGrants",
    status: "Rejected",
    appliedOn: "2026-07-02",
    rejectionReason: "The position was filled by an internal candidate.",
  },
  {
    id: "app-1010",
    listingId: "lst-105",
    listingTitle: "Frontend Intern",
    companyName: "Seera Digital",
    status: "Withdrawn",
    appliedOn: "2026-06-28",
  },
  {
    id: "app-0977",
    listingId: "lst-103",
    listingTitle: "QA Trainee",
    companyName: "Craft Labs",
    status: "Applied",
    appliedOn: "2026-06-15",
    mayLeadToHire: true,
  },
  {
    id: "app-0953",
    listingId: "lst-106",
    listingTitle: "UI/UX Trainee",
    companyName: "Pixel Works",
    status: "Applied",
    appliedOn: "2026-05-30",
  },
  {
    id: "app-0922",
    listingId: "lst-107",
    listingTitle: "Backend Trainee",
    companyName: "Orbit Systems",
    status: "Rejected",
    appliedOn: "2026-05-12",
  },
  {
    id: "app-0890",
    listingId: "lst-108",
    listingTitle: "Operations Intern",
    companyName: "Bright Path",
    status: "Accepted",
    appliedOn: "2026-04-20",
  },
];

// Card action copy (US3).
export const CARD_ACTION_LABELS = {
  viewListing: "View Listing",
  withdraw: "Withdraw",
} as const;

// Withdraw confirmation dialog copy (US4, FR-021-024).
export const WITHDRAW_DIALOG_LABELS = {
  title: "Withdraw application?",
  description: (listingTitle: string, companyName: string) =>
    `This will withdraw your application to "${listingTitle}" at ${companyName}. The application will be marked Withdrawn and will move out of your pending list.`,
  cancel: "Cancel",
  confirm: "Withdraw",
} as const;

// Per-tab empty-state copy (FR-026/027). The browse-Listings CTA appears only
// on All and Applied; the status tabs show a message with no CTA.
export const EMPTY_STATES: Record<TabValue, { title: string; message: string; ctaHref?: string; ctaLabel?: string }> = {
  all: {
    title: "No applications yet",
    message: "Applications you submit will appear here.",
    ctaHref: "/listings",
    ctaLabel: "Browse listings",
  },
  applied: {
    title: "No pending applications",
    message: "Applications you submit will appear here.",
    ctaHref: "/listings",
    ctaLabel: "Browse listings",
  },
  accepted: {
    title: "No accepted applications yet",
    message: "Accepted applications will show up here.",
  },
  rejected: {
    title: "No rejected applications",
    message: "Rejected applications will show up here.",
  },
  withdrawn: {
    title: "No withdrawn applications",
    message: "Applications you withdraw will show up here.",
  },
};

// A status matches a tab when it's the "all" tab or the status equals the
// tab's value case-insensitively (ApplicationStatus values are capitalized
// — "Applied" — while TabValue statuses are lowercase — "applied"). Kept in
// one place so counts, filtering, and the tab bar never drift apart.
export function matchesTab(status: ApplicationStatus, tab: TabValue): boolean {
  return tab === "all" || status.toLowerCase() === tab;
}

// Formats an ISO date (YYYY-MM-DD) as a short display date, e.g. "Jul 20, 2026".
// Parsed in UTC so the value never shifts across timezones.
const SHORT_DATE = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  timeZone: "UTC",
});

export function formatApplicationDate(isoDate: string): string {
  const [year, month, day] = isoDate.split("-").map(Number);
  return SHORT_DATE.format(new Date(Date.UTC(year, month - 1, day)));
}