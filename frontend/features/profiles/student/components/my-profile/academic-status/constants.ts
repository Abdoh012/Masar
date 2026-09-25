// Copy + display metadata for the academic-status section (structure rules §14).

export const ACADEMIC_STATUS_LABELS = {
  title: "Academic status",
  description:
    "Are you still studying or already graduated? It decides which training listings you can apply to, and whether a graduation certificate is needed.",
  groupLabel: "Academic status",
} as const;

// The two segments of the control. The icon for each is resolved in the
// component (a component reference can't cross the server/client boundary as
// data), so this stays plain config.
export const ACADEMIC_LEVELS = [
  {
    value: "student",
    label: "Student",
    description: "Still studying — you can apply to trainee listings.",
  },
  {
    value: "graduate",
    label: "Graduate",
    description: "Graduated — attach your certificate to unlock graduate listings.",
  },
] as const;

export const STUDENT_HINT = {
  title: "Nothing else to fill in",
  body: "Graduation date and certificate only become available once you switch to Graduate.",
} as const;

export const GRADUATION_LABELS = {
  field: "Graduation date",
  // The month input's placeholder attribute is ignored by browsers; this is the
  // hint shown beneath the field instead.
  hint: "The month your degree or certificate was awarded.",
} as const;

export const CERTIFICATE_UPLOAD_LABELS = {
  label: "Graduation certificate",
  empty: "Drop your certificate here",
  hint: "PDF, PNG or JPG up to 5 MB",
  browse: "Browse files",
  localOnly: "Preview only — nothing is uploaded yet.",
  remove: "Remove",
} as const;
