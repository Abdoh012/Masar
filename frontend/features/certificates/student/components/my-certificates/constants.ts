// Static data + copy for the my-certificates page (structure rules §14). All
// page data is server-fetched — statistics, the eligible feed, and the
// certificate records (the page orchestrator → student/api.ts). This file
// holds only copy and display metadata; the request transition is backend-
// wired (student/actions.ts requestCertificate → POST /certificates, then
// revalidatePath) — the client keeps no pending-record list state.

import type { CertificateStatus } from "../../types";

// --- Page header copy ---

export const PAGE_HEADER = {
  eyebrow: "Certificates",
  title: "Your certificates",
  description:
    "Earn a verified certificate for every training you complete. Review what you're eligible for, track requests, and download your issued certificates.",
} as const;

export const SUMMARY_LABELS = {
  eligible: "Eligible to request",
  pending: "In review",
  issued: "Issued",
} as const;

// --- Summary stat cards: per-lifecycle-state display metadata ---

// One entry per stat card — label (reusing SUMMARY_LABELS), icon key (mapped
// to a lucide icon in the SummaryCounts orchestrator), and the semantic status
// tint applied as a soft card wash (cardTint) with a matching icon tile
// (iconTint). Eligible uses the info (blue) tint — the actionable, not-yet-
// in-flight state; In review the warning (amber) tint; Issued the success
// (green) tint. All three pairs already exist in the token architecture
// (globals.css §3/§4) — nothing new is introduced here.
export const SUMMARY_CARD_META = [
  {
    key: "eligible",
    label: SUMMARY_LABELS.eligible,
    icon: "badge-check",
    cardTint: "bg-info-bg",
    iconTint: "text-info-fg",
  },
  {
    key: "pending",
    label: SUMMARY_LABELS.pending,
    icon: "clock",
    cardTint: "bg-warning-bg",
    iconTint: "text-warning-fg",
  },
  {
    key: "issued",
    label: SUMMARY_LABELS.issued,
    icon: "check-circle",
    cardTint: "bg-success-bg",
    iconTint: "text-success-fg",
  },
] as const;

// --- "Why / how it works" explainer copy (the spec's eligibility chain) ---

export const HOW_IT_WORKS_TITLE = "How certificates work";

export const HOW_IT_WORKS_STEPS = [
  {
    icon: "check",
    title: "Complete a training",
    body: "Finish an approved training from start to end.",
  },
  {
    icon: "send",
    title: "Request your certificate",
    body: "Companies confirm the completion, then we generate and verify it.",
  },
  {
    icon: "download",
    title: "Get your certificate",
    body: "Download and share your verified certificate anywhere.",
  },
] as const;

// --- From the spec's eligibility chain (readonly display reference) ---

export const LIFE_CYCLE_STEPS = [
  { label: "Completed training", done: true },
  { label: "Eligible for certificate", done: true },
  { label: "Request confirmed", done: true },
  { label: "Certificate issued", done: true },
] as const;

// --- Certificate status display metadata (single source) ---

export const STATUS_DISPLAY: Record<
  CertificateStatus,
  { label: string; badge: string; icon: "clock" | "check" | "alert" | "ban" }
> = {
  pending: { label: "Pending confirmation", badge: "bg-warning-bg text-warning-fg", icon: "clock" },
  issued: { label: "Issued", badge: "bg-success-bg text-success-fg", icon: "check" },
  active: { label: "Active", badge: "bg-success-bg text-success-fg", icon: "check" },
  revoked: { label: "Revoked", badge: "bg-neutral-badge-bg text-neutral-badge-fg", icon: "ban" },
  expired: { label: "Expired", badge: "bg-neutral-badge-bg text-neutral-badge-fg", icon: "ban" },
  cancelled: { label: "Cancelled", badge: "bg-neutral-badge-bg text-neutral-badge-fg", icon: "ban" },
} as const;

// A status is live/valid — downloadable + verifiable.
export const LIVE_STATUSES: CertificateStatus[] = ["issued", "active"];

// The certificates section is grouped by lifecycle state, one group per
// dedicated endpoint: requested (pending, still awaiting confirmation), issued
// (issued/active), and revoked (revoked, with its reason). Heading, icon,
// tint, and empty-state copy.
export const CERTIFICATE_GROUPS = {
  requested: {
    title: "Requested certificates",
    icon: "clock",
    accent: "bg-warning-bg text-warning-fg",
    emptyTitle: "No requests yet",
    emptyMessage:
      "Certificates you've requested that are still awaiting confirmation will appear here.",
  },
  issued: {
    title: "Issued certificates",
    icon: "check",
    accent: "bg-success-bg text-success-fg",
    emptyTitle: "No certificates yet",
    emptyMessage:
      "Once a company confirms your request, your verified certificate will appear here and be ready to download.",
  },
  revoked: {
    title: "Revoked certificates",
    icon: "ban",
    accent: "bg-neutral-badge-bg text-neutral-badge-fg",
    emptyTitle: "No revoked certificates",
    emptyMessage:
      "Certificates that have been revoked — with the reason — will appear here.",
  },
} as const;

// The date line shown on a certificate row is per-status: its label and which
// record field carries the date the backend reports for that state (pending →
// requested_at, issued/active → issued_at, revoked → revoked_at).
export const CERTIFICATE_DATE_META: Record<
  CertificateStatus,
  { label: string; field: "requestedOn" | "issuedOn" | "revokedOn" }
> = {
  pending: { label: "Requested", field: "requestedOn" },
  issued: { label: "Issued", field: "issuedOn" },
  active: { label: "Issued", field: "issuedOn" },
  revoked: { label: "Revoked", field: "revokedOn" },
  expired: { label: "Issued", field: "issuedOn" },
  cancelled: { label: "Issued", field: "issuedOn" },
};

// True when a certificate is still awaiting confirmation (the "requested" group).
export function isRequestedStatus(status: CertificateStatus): boolean {
  return status === "pending";
}

// --- Empty variants (used by the EmptyState) ---

export const EMPTY_COPY = {
  title: "No certificates yet",
  message:
    "Once you complete a training, you'll be eligible to request a certificate here. Browse listings and start your next training.",
  ctaHref: "/listings",
  ctaLabel: "Browse listings",
} as const;

// --- Request confirm dialog copy ---

export const REQUEST_DIALOG_LABELS = {
  title: "Request certificate?",
  description: (title: string, company: string) =>
    `We'll ask ${company} to confirm your completion of "${title}". Once confirmed, your certificate will be verified and appear in your certificates.`,
  cancel: "Not now",
  confirm: "Request certificate",
  confirmPending: "Requesting…",
} as const;

// --- Request trigger copy ---

export const REQUEST_ACTION_LABELS = {
  trigger: "Request certificate",
  failedGeneric: "Could not request the certificate right now. Please try again.",
} as const;

// --- Certificate detail dialog copy ---

export const DETAIL_DIALOG_LABELS = {
  viewCertificate: "View certificate",
  verify: "Verify",
  verified: "Verified by Masar",
  closed: "Close",
} as const;

// --- Certificate PDF download copy ---

export const DOWNLOAD_LABELS = {
  download: "Download PDF",
  success: "Certificate downloaded.",
  error: "Could not download the certificate. Please try again.",
} as const;

// --- Request-success toast copy ---

export const REQUEST_TOAST_COPY = {
  message: (company: string) =>
    `Your certificate request is now with ${company} for confirmation. You'll be notified once it's issued.`,
} as const;

// --- Format a date for the summary header ---

// The backend's date/datetime DTOs arrive as "YYYY-MM-DD" or
// "YYYY-MM-DD HH:MM:SS"; slice off the time portion before parsing so a
// datetime value never yields NaN (which turns into a RangeError in
// Intl.DateTimeFormat.format). Returns an invalid Date when the string
// isn't a real date, and formatShortDate falls back to the raw string.
function toUtcDate(isoDate: string): Date {
  const [year, month, day] = isoDate.slice(0, 10).split("-").map(Number);
  if (
    !Number.isFinite(year) ||
    !Number.isFinite(month) ||
    !Number.isFinite(day) ||
    isoDate.length < 10
  ) {
    return new Date(NaN);
  }
  return new Date(Date.UTC(year, month - 1, day));
}

const SHORT_DATE = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  timeZone: "UTC",
});

export function formatShortDate(isoDate: string): string {
  const date = toUtcDate(isoDate);
  if (Number.isNaN(date.getTime())) {
    return isoDate;
  }
  return SHORT_DATE.format(date);
}
