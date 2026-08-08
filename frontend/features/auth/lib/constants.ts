import { Building2, GraduationCap } from "lucide-react";

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
    hint: "We'll never share your email.",
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

export type RoleType = "student" | "company";
