import type { StudentProfile } from "../../types";

export const PROFILE: StudentProfile = {
  name: "Nour El-Sayed",
  field: "Software Engineering",
  initials: "NE",
  isComplete: true,
  studies: "Faculty of Computers & Information, Cairo University",
};

// Flip the ProfileHeader read to this to review the "Complete your profile" nudge.
export const PROFILE_INCOMPLETE: StudentProfile = {
  ...PROFILE,
  isComplete: false,
};
