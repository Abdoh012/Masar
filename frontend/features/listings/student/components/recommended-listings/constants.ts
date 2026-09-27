// Copy and limits for the dashboard's recommended-trainings section.

// How many rows the dashboard shows. The backend already caps its
// recommendation selection, so this is a defensive ceiling rather than the
// section's source of truth — it guarantees the section can never grow into a
// second browse grid.
export const RECOMMENDED_LIMIT = 2;

export const RECOMMENDED_LABELS = {
  title: "Recommended trainings",
  viewAll: "View all",
} as const;

// Empty state — rendered when the backend finds no training matching the
// student's specialization (which is also the case for a student whose profile
// is still incomplete).
export const RECOMMENDED_EMPTY = {
  title: "No recommendations yet",
  message: "Complete your profile and we'll match you with relevant trainings.",
} as const;

// The duration chip's suffix. The presenter sends whole days, so the label is
// pluralised around the number rather than baked into it.
export const DURATION_DAYS_SUFFIX = "days";
export const DURATION_DAY_SUFFIX = "day";
