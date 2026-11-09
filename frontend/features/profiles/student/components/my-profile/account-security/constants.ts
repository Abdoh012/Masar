// Copy + policy for the account-security section (structure rules §14).

// The client-side mirror of the backend's change-password policy
// (security_password_strength_errors in app/shared/functions/security.php) so
// the form can explain itself and gate its submit before the request. The API
// remains the source of truth: its 422 field errors are shown as-is.
export const PASSWORD_MIN_LENGTH = 12;
export const PASSWORD_UPPERCASE_REGEX = /[A-Z]/;
export const PASSWORD_LOWERCASE_REGEX = /[a-z]/;
export const PASSWORD_DIGIT_REGEX = /[0-9]/;
export const PASSWORD_SPECIAL_CHAR_REGEX = /[^A-Za-z0-9]/;

// The live requirement checklist under the password fields. Keyed by id so the
// form's resolved booleans and the leaf's labels can never drift apart.
export const PASSWORD_RULES = [
  { id: "length", label: `At least ${PASSWORD_MIN_LENGTH} characters` },
  { id: "case", label: "Uppercase and lowercase letters" },
  { id: "digit", label: "At least one number" },
  { id: "special", label: "At least one special character" },
  { id: "match", label: "Both passwords match" },
] as const;

export type PasswordRuleId = (typeof PASSWORD_RULES)[number]["id"];

export const ACCOUNT_SECURITY_LABELS = {
  title: "Account security",
  description:
    "Your name and sign-in credentials. Changing either doesn't affect any application you've already sent.",

  fullName: {
    title: "Full name",
    description:
      "The name companies see on your profile and on every application you send.",
    label: "Full name",
    placeholder: "e.g. Nour El Sayed",
    hint: "It is also the name printed on the certificates you earn.",
    save: "Save full name",
    saving: "Saving…",
  },

  password: {
    title: "Password",
    description:
      "Pick something you don't use anywhere else. For your security, changing it signs you out of every device.",
    currentPasswordLabel: "Current password",
    newPasswordLabel: "New password",
    confirmPasswordLabel: "Confirm new password",
    save: "Update password",
    saving: "Updating…",
  },
} as const;
