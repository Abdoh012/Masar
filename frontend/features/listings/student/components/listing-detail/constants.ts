import type { ApplicationStatus } from "../../types";

export const APPLY_COPY = {
  button: "Apply now",
  applied: "You already applied",
  appliedMessage: "Your application is in. The company will reach out if you're shortlisted.",
};

// Status-specific follow-up lines rendered under the "already applied" marker.
// The backend keeps rejected applications re-appliable and blocks re-applying
// once a new application is submitted, so rejected copy points back at Apply.
export const APPLICATION_STATUS_MESSAGES: Record<
  ApplicationStatus,
  string
> = {
  pending: APPLY_COPY.appliedMessage,
  accepted: "Your application was accepted — the company will contact you with the next steps.",
  rejected:
    "Your application was rejected, but you can apply again for this training.",
  withdrawn: "You withdrew this application, so the company will no longer see it.",
};

// Status-keyed tint for the "already applied" panel — the whole block reads as
// one semantic status color (border + bg + icon/title accent), never sage
// (sage is reserved for the hire-opportunity signal) and never hardcoded hex.
// Accepted → success family; Rejected/Withdrawn → error (danger) family;
// Pending → info, matching AwaitingResponseNote's convention that "being
// reviewed" is informational, never urgency/warning.
export const APPLICATION_STATUS_PANEL_STYLES: Record<
  ApplicationStatus,
  { panel: string; accent: string }
> = {
  pending: { panel: "border-info-bg bg-info-bg/50", accent: "text-info-fg" },
  accepted: {
    panel: "border-success-bg bg-success-bg/50",
    accent: "text-success-fg",
  },
  rejected: {
    panel: "border-error-bg bg-error-bg/50",
    accent: "text-error-fg",
  },
  withdrawn: {
    panel: "border-error-bg bg-error-bg/50",
    accent: "text-error-fg",
  },
};

export const DETAIL_META = {
  company: "Company",
  field: "Field",
  format: "Format",
  posted: "Posted",
};

export const DETAIL_COPY = {
  backToBrowse: "Back to browse",
};
