// Static copy + display helpers for the Ended Applications tab section
// (structure rules §14). Cards are server-fetched from /applications/
// certificates (student/api.ts) — nothing here is mock data.

// Card labels (the "Completed" badge, facts rows and the modal entry point).
export const ENDED_CARD_LABELS = {
  completed: "Completed",
  degree: "Degree",
  specialization: "Specialization",
  completedOn: "Completed",
  viewCertificate: "View Certificate",
} as const;

// View-only certificate modal copy — deliberately no download action: this
// surface is preview-only, the certificates feature owns downloads.
export const ENDED_DIALOG_LABELS = {
  title: "Certificate",
  close: "Close",
} as const;

// Empty state for the Ended Applications tab. "Ended" here means trainings the
// student has seen through to an issued certificate — the /applications/
// certificates dataset.
export const ENDED_EMPTY_STATE = {
  title: "No ended applications yet",
  message: "Trainings you've completed with a certificate will appear here.",
} as const;

// Degree line: "Excellent (A+)" when both parts exist, else whichever is
// present; undefined when neither exists so the card hides the row.
export function formatDegree(
  gradeLabel?: string,
  grade?: string,
): string | undefined {
  if (gradeLabel) return grade ? `${gradeLabel} (${grade})` : gradeLabel;
  return grade;
}