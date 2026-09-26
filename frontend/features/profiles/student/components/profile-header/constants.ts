import type { StudentProfile } from "../../types";

export const PROFILE: StudentProfile = {
  name: "Nour El-Sayed",
  field: "Software Engineering",
  initials: "NE",
  isComplete: true,
  studies: "Faculty of Computers & Information, Cairo University",
};

// Copy for the completion nudge. The header shows this on `!isComplete`.
export const PROFILE_COMPLETION_CTA = {
  label: "Complete your profile",
} as const;

// Flip the ProfileHeader read to this to review the "Complete your profile" nudge.
export const PROFILE_INCOMPLETE: StudentProfile = {
  ...PROFILE,
  isComplete: false,
};
