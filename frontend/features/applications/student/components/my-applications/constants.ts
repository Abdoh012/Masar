// Static data + display copy for the My Applications page section (structure
// rules §14). Cards are fetched per tab from the backend (student/api.ts) —
// nothing here is mock data.

import type {
  ApplicationStatus,
  MyApplication,
  TabValue,
} from "@/features/applications/student/types";

// Header copy.
export const APPLICATIONS_HEADER = {
  eyebrow: "Track your progress",
  title: "My Applications",
  description:
    "Follow every listing you've applied to — from submitted to outcome — all in one place.",
} as const;

// Status tabs. "All" is the default active tab; the five status values map to
// the backend list endpoints (student/api.ts) and drive the URL (?tab=). The
// last one, "Ended Applications", maps instead to /applications/certificates
// (completed trainings with an issued certificate) — see
// ended-applications/EndedApplicationsContainer.
export const TABS: { value: TabValue; label: string }[] = [
  { value: "all", label: "All" },
  { value: "applied", label: "Applied" },
  { value: "accepted", label: "Accepted" },
  { value: "rejected", label: "Rejected" },
  { value: "withdrawn", label: "Withdrawn" },
  { value: "ended", label: "Ended Applications" },
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
  confirmPending: "Withdrawing…",
} as const;

// Withdraw toast feedback (app-wide toast norm, same as the report flow):
// success toasts on a confirmed withdraw, failures toast AND stay inline in
// the dialog.
export const WITHDRAW_TOASTS = {
  success: "Application withdrawn.",
  failedGeneric: "Could not withdraw the application.",
} as const;

// Per-tab empty-state copy (FR-026/027). The browse-Listings CTA appears only
// on All and Applied; the status tabs show a message with no CTA.
export const EMPTY_STATES: Record<
  TabValue,
  { title: string; message: string; ctaHref?: string; ctaLabel?: string }
> = {
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
  ended: {
    title: "No ended applications yet",
    message: "Trainings you've completed with a certificate will appear here.",
  },
};

// Date shown on a card is the current status's own date, not the applied
// date: Accepted → acceptedOn, Rejected → rejectedOn, Withdrawn →
// withdrawnOn, Applied → appliedOn. Label + field come from this single map
// so the card and any future consumers never drift (FR-013).
export const STATUS_DATE_FIELDS = {
  Applied: "appliedOn",
  Accepted: "acceptedOn",
  Rejected: "rejectedOn",
  Withdrawn: "withdrawnOn",
} as const;

// Resolves the card's displayed date for an application — the current
// status's date when present, falling back to the always-present appliedOn
// (Applied always hits the fallback path by design; the backend sends
// applied_at on every card).
export function getStatusDate(application: MyApplication): string {
  return (
    application[STATUS_DATE_FIELDS[application.status]] ??
    application.appliedOn
  );
}

// Backend dates are ISO-8601 with the app's +03:00 offset. Formatting in the
// same fixed timezone (Africa/Cairo — the backend's APP_TIMEZONE) keeps the
// wall-clock date stable regardless of viewer locale, e.g. "Jul 20, 2026".
const SHORT_DATE = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  timeZone: "Africa/Cairo",
});

export function formatApplicationDate(isoDate: string): string {
  const date = new Date(isoDate);
  if (Number.isNaN(date.getTime())) return "";
  return SHORT_DATE.format(date);
}

// Total calendar days of the training (card `duration`), e.g. "35 days".
// Only rendered when the API sends a number, always beside its "Duration" label.
export function formatDurationDays(days: number): string {
  return `${days} ${days === 1 ? "day" : "days"}`;
}

// Rejection reasons arrive as stable codes (backend
// app/shared/enums/rejection_reasons.php); mapping happens here so the card
// only renders spans. Unknown codes resolve to undefined and hide the line.
export const REJECTION_REASON_LABELS: Record<string, string> = {
  incomplete_data: "Incomplete Data",
  invalid_data: "Invalid Data",
  not_eligible: "Not Eligible",
  capacity_full: "Capacity Full",
  requirements_not_met: "Requirements Not Met",
  duplicate_application: "Duplicate Application",
  documents_missing: "Documents Missing",
  documents_invalid: "Documents Invalid",
  company_not_approved: "Company Not Approved",
  training_not_available: "Training Not Available",
  payment_failed: "Payment Failed",
  policy_violation: "Policy Violation",
  other: "Other",
};

export function rejectionReasonLabel(code?: string): string | undefined {
  if (!code) return undefined;
  return REJECTION_REASON_LABELS[code];
}

// Accepted-paid card: the payment phase. Bank transfers take lead time, so the
// payment-info panel stays visible for the whole period the payment hasn't
// been submitted yet — the student reports the transfer whenever they're
// ready, and only `payment_submitted` (the paid-successfully signal) swaps the
// card to the remaining-days countdown.
export const PAYMENT_LABELS = {
  paidTitle: "Payment confirmed",
  paidMessage: "Your payment for this training has been confirmed.",
  bankTitle: "Bank transfer details",
  bankName: "Bank",
  accountName: "Account holder",
  accountNumber: "Account number",
  copyAccountNumber: "Copy account number",
  accountNumberCopied: "Account number copied",
  pendingNoBank:
    "Payment details will appear here once the company adds its bank information.",
} as const;

// ReportPaymentZone/ReportPaymentForm copy — the accepted-paid card's "I've
// paid" flow. The backend requires the bank transfer reference on submit (422
// otherwise), so the form collects it; on success the action revalidates and
// the server zone swaps to the reported panel (restored from the
// fetchPaymentStatus read on every render, so it survives a refresh). The
// success/info pair toasts when a report lands (successMessage +
// infoMessage); failed reports toast the backend error (failedGeneric is the
// fallback).
export const PAYMENT_REPORT = {
  idleLabel: "I've paid",
  confirmPrompt: "Report payment for this training?",
  confirmLabel: "Confirm",
  cancelLabel: "Cancel",
  submittingLabel: "Submitting…",
  successMessage: "Payment reported. The company has been notified.",
  infoMessage: "Payment is pending verification.",
  failedGeneric: "Could not report the payment. Please try again.",
  referenceLabel: "Transfer reference",
  referencePlaceholder: "Enter the bank transfer reference",
} as const;

// "May lead to hire" pill copy — mirrors the backend `may_lead_to_hire` field
// on Applied/Accepted cards (equals is_paid).
export const MAY_LEAD_TO_HIRE_LABEL = "May lead to hire";

// --- Free/paid training labels ---

// "Duration" is the meta-chip label on every card, stating the training's
// total term (fixed, not a countdown — the remaining days own that signal via
// the shared countdown chip), while the remaining-days countdown itself is
// labeled by the shared TRAINING_PERIOD_LABEL from the trial-countdown
// constants. (The old Mode/Starts facts rows are gone — free trainings show
// only the countdown / finished note now.)
export const FREE_TRAINING_INFO_LABELS = {
  duration: "Duration",
} as const;

// Accepted card: the training's run-down hit zero. Replaces the remaining-days
// countdown so the card says the training is over instead of an alarming
// critical "0 days remaining" chip.
export const TRAINING_FINISHED_NOTE = {
  title: "Training finished",
  subline: "You've completed this training — the run-down has reached zero.",
} as const;

// --- Free/paid indicator pill (Applied + Accepted cards) ---

// Applied and Accepted cards tell the user whether the training is paid —
// important before they act on it. Free = success status token (no cost,
// positive); paid = the brand's seal-gold secondary tint, deliberately
// different from the primary-tint "May lead to hire" pill so a paid card never
// shows two identical-looking pills. Only the Applied/Accepted DTOs send
// `is_paid` (backend application_cards.php), so the pill renders solely there;
// Rejected/Withdrawn cards omit it entirely instead of defaulting to a lie.
export const PAYMENT_BADGE_LABELS = {
  free: "Free",
  paid: "Paid",
  ariaFree: "Free training",
  ariaPaid: "Paid training",
} as const;

export const PAYMENT_BADGE_STYLES = {
  free: "bg-success-bg text-success-fg",
  paid: "bg-secondary-tint text-secondary-text",
} as const;

// --- Applied-card status note (fills the Applied cards' empty slot) ---

// Applied cards render no trial/facts/motivational section, so the area
// between the meta row and the actions reads as a gap. This note states the
// pending state in the same chip shape as the trial countdown; "reviewing" is
// informational, never urgent — the info status tokens, not warning.
export const AWAITING_RESPONSE_NOTE = {
  title: "Awaiting response",
  subline:
    "The company is reviewing your application — you'll hear back as soon as they decide.",
} as const;