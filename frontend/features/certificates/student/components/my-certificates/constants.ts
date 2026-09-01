// Static data + copy for the my-certificates page (UI-only — no backend yet;
// structure rules §14). Mock data demonstrates every certificate state the
// specification describes: eligible-not-requested, requested/pending, issued,
// and terminal (revoked).

import type {
  CertificateCounts,
  CertificateStatus,
  EligibleTraining,
  StudentCertificate,
} from "../../types";
import type { CertificateDocument } from "../../../shared/types";

// --- Page header copy ---

export const PAGE_LABELS = {
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

// The certificates collection is split into two groups: certificates the
// student has requested (still awaiting confirmation) vs. the ones actually
// issued (issued/active, plus terminal records). Heading, icon, tint, and
// empty-state copy.
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
} as const;

// True when a certificate is still awaiting confirmation (the "requested" group).
export function isRequestedStatus(status: CertificateStatus): boolean {
  return status === "pending";
}

// --- Mock data: eligible trainings (completed, can request) ---

export const MOCK_ELIGIBLE: EligibleTraining[] = [
  {
    id: "eg-001",
    listingId: "lst-201",
    listingTitle: "Software Engineering Internship",
    field: "Software Engineering",
    companyName: "Hala Bank",
    completedOn: "2026-06-15",
    mayLeadToHire: true,
  },
  {
    id: "eg-003",
    listingId: "lst-203",
    listingTitle: "QA Testing Internship",
    field: "Software Quality",
    companyName: "Craft Labs",
    completedOn: "2026-04-10",
  },
];

// --- Mock data: certificate records (requested / issued / terminal) ---

export const MOCK_CERTIFICATES: StudentCertificate[] = [
  {
    id: "cert-00385",
    listingId: "lst-202",
    listingTitle: "Data Science Trainee",
    field: "Data Science",
    companyName: "NileGrants",
    status: "pending",
    requestedOn: "2026-06-01",
    canDownload: false,
    canVerify: false,
  },
  {
    id: "cert-00482",
    listingId: "lst-101",
    listingTitle: "Backend Internship",
    field: "Software Engineering",
    companyName: "Orbit Systems",
    status: "issued",
    issuedOn: "2026-05-11",
    certNumber: "MASAR-2026-000482",
    canDownload: true,
    canVerify: true,
    mayLeadToHire: true,
  },
  {
    id: "cert-00317",
    listingId: "lst-104",
    listingTitle: "Data Intern",
    field: "Data Science",
    companyName: "NileGrants",
    status: "issued",
    issuedOn: "2026-02-02",
    certNumber: "MASAR-2026-000317",
    canDownload: true,
    canVerify: true,
  },
  {
    id: "cert-00220",
    listingId: "lst-107",
    listingTitle: "Frontend Intern",
    field: "Frontend Engineering",
    companyName: "Seera Digital",
    status: "revoked",
    issuedOn: "2025-11-08",
    revokedOn: "2026-01-15",
    revokeReason: "Training completion was not verified by the company.",
    certNumber: "MASAR-2025-000220",
    canDownload: false,
    canVerify: false,
  },
];

// --- Mock summary counts (derived to match the mock arrays above) ---
// Single source of truth: "eligible" = requestable trainings, "pending" =
// records still awaiting confirmation, "issued" = live records.

export const MOCK_COUNTS: CertificateCounts = {
  eligible: MOCK_ELIGIBLE.length,
  pending: MOCK_CERTIFICATES.filter((c) => c.status === "pending").length,
  issued: MOCK_CERTIFICATES.filter((c) => LIVE_STATUSES.includes(c.status)).length,
};

// Build the pending certificate record created the moment a request is
// confirmed: the training leaves "Eligible to request" and re-appears here as
// a record awaiting confirmation (UI-only, no backend).
export function buildPendingCertificate(eligible: EligibleTraining): StudentCertificate {
  return {
    id: `cert-${eligible.listingId}`,
    listingId: eligible.listingId,
    listingTitle: eligible.listingTitle,
    field: eligible.field,
    companyName: eligible.companyName,
    status: "pending",
    requestedOn: new Date().toISOString().slice(0, 10),
    canDownload: false,
    canVerify: false,
    mayLeadToHire: eligible.mayLeadToHire,
  };
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
} as const;

// --- Certificate detail dialog copy ---

export const DETAIL_DIALOG_LABELS = {
  viewCertificate: "View certificate",
  download: "Download PDF",
  verify: "Verify",
  verified: "Verified by Masar",
  closed: "Close",
} as const;

// --- Request-success toast copy ---

export const REQUEST_TOAST_COPY = {
  message: (company: string) =>
    `Your certificate request is now with ${company} for confirmation. You'll be notified once it's issued.`,
} as const;

// --- Format a date for the summary header ---

function toUtcDate(isoDate: string): Date {
  const [year, month, day] = isoDate.split("-").map(Number);
  return new Date(Date.UTC(year, month - 1, day));
}

const SHORT_DATE = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  timeZone: "UTC",
});

export function formatShortDate(isoDate: string): string {
  return SHORT_DATE.format(toUtcDate(isoDate));
}
