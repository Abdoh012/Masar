import { Building2, GraduationCap } from "lucide-react";

import type { Role } from "@/types/auth";

export const SIGN_UP_ROLES = [
  {
    value: "student" as RoleType,
    label: "Student",
    description: "Find training & internships",
    icon: GraduationCap,
  },
  {
    value: "company" as RoleType,
    label: "Company",
    description: "Post listings & hire talent",
    icon: Building2,
  },
];

export const FIELD_CONFIG = {
  fullName: {
    label: "Full name",
    type: "text",
    placeholder: "e.g. Omar Khaled",
    autoComplete: "name",
  },
  email: {
    label: "Email",
    type: "email",
    placeholder: "you@example.com",
    autoComplete: "email",
  },
  password: {
    label: "Password",
    type: "password",
    placeholder: "Enter your password",
    autoComplete: "current-password",
    hint: "At least 8 characters.",
  },
  newPassword: {
    label: "New password",
    type: "password",
    placeholder: "Enter a new password",
    autoComplete: "new-password",
  },
  confirmPassword: {
    label: "Confirm password",
    type: "password",
    placeholder: "Re-enter the new password",
    autoComplete: "new-password",
  },
  companyName: {
    label: "Company name",
    type: "text",
    placeholder: "e.g. Nile Valley Consulting",
    autoComplete: "organization",
  },
} as const;

// OTP verification step in the forgot-password flow (UI-only for now).
export const OTP_LENGTH = 6;

// Placeholder token the Verify button routes to while reset-password is
// still a UI-only destination with no real token from a verified OTP.
export const RESET_PASSWORD_PLACEHOLDER_TOKEN = "placeholder";

// Roles selectable at sign-up (subset of the global Role — admin is never an option).
export type RoleType = Exclude<Role, "admin">;
