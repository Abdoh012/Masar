// Static data + display copy for the My Applications page section (structure
// rules §14). Cards are fetched per tab from the backend (student/api.ts) —
// nothing here is mock data.

import type {
  ApplicationStatus,
  MyApplication,
  TabValue,
} from "@/features/applications/student/types";

// Header copy.
export const APPLICATIONS_TITLE = "My Applications";

// Status tabs. "All" is the default active tab; values map to the backend
// list endpoints (student/api.ts) and drive the URL (?tab=).
export const TABS: { value: TabValue; label: string }[] = [
  { value: "all", label: "All" },
  { value: "applied", label: "Applied" },
  { value: "accepted", label: "Accepted" },
  { value: "rejected", label: "Rejected" },
  { value: "withdrawn", label: "Withdrawn" },
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

// Remaining calendar days until the training ends (card `duration`). Only
// rendered when the API sends a number.
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

// Accepted-paid card: how early the payment phase surfaces. Bank transfers
// take lead time, so the payment-info panel appears while the free trial still
// has this many days left instead of waiting until it ends (the card shows the
// countdown while daysRemaining > this, the payment panel from here down).
// Currently aligned with the countdown's critical threshold (TRIAL_CRITICAL_DAYS
// in the shared trial-countdown constants) but kept independent so either can
// change without forcing the other.
export const PAYMENT_NOTICE_DAYS = 3;

// Accepted-paid card copy for the payment phase (late trial + after the trial
// ends). `sage` stays reserved for the hire-opportunity-confirmed signal —
// these states use neutral/secondary/info tokens; only the transfer-reference
// callout uses the warning role (copy lives with the raw instructions text
// from the API).
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