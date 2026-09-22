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

export const DETAIL_META = {
  company: "Company",
  field: "Field",
  format: "Format",
  posted: "Posted",
};

export const DETAIL_COPY = {
  backToBrowse: "Back to browse",
};
