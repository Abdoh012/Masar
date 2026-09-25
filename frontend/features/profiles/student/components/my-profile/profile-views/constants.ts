// Copy + display metadata for the profile-views section (structure rules §14).

export const PROFILE_VIEWS_LABELS = {
  title: "Profile views",
  description:
    "Who has been opening your profile. A strong profile with a photo and skills listed gets noticeably more views.",
  // The value is rendered as the big number directly above this label, so the
  // copy deliberately omits it.
  count: "people viewed your profile",
  show: "Show recent viewers",
  hide: "Hide viewers",
  listNote: "Showing the most recent viewers only.",
} as const;

// Initials avatars cycle through these tints so a long list stays scannable
// instead of being one solid block of navy. All are existing token pairs —
// no new colours, and never `sage` (reserved for the hire-confirmed signal).
export const VIEWER_TINTS = [
  "bg-primary-tint text-primary-text",
  "bg-secondary-tint text-secondary-text",
  "bg-info-bg text-info-fg",
] as const;
