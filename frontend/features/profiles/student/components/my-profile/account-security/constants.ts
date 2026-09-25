// Copy + policy for the account-security section (structure rules §14).

// The client-side minimum only. The backend owns the real password policy;
// this exists so the form gives feedback before it would send a request
// (structure rules §10).
export const PASSWORD_MIN_LENGTH = 8;

// The live requirement checklist under the password fields. Keyed by id so the
// leaf can map the row's booleans onto the label without a second array.
export const PASSWORD_RULES = [
  { id: "length", label: `Use at least ${PASSWORD_MIN_LENGTH} characters` },
  { id: "match", label: "Both passwords match" },
] as const;

export const ACCOUNT_SECURITY_LABELS = {
  title: "Account security",
  description:
    "Your sign-in credentials. Changing your password doesn't affect any application you've already sent.",

  username: {
    title: "Username",
    description:
      "Your public handle — companies see it next to your name, not your email.",
    label: "Username",
    placeholder: "nour.elsayed",
    // Placeholder until a profile read lands; the field is uncontrolled, so this
    // only seeds the input once.
    defaultValue: "nour.elsayed",
    save: "Save username",
    // The no-op submit handler's explanation, surfaced in the UI as a hint so
    // the button not appearing to do anything isn't confusing.
    pending: "Saving isn't available yet — the profile API isn't connected.",
  },

  password: {
    title: "Password",
    description:
      "Pick something you don't use anywhere else. You'll stay signed in on your other devices.",
    newPasswordLabel: "New password",
    confirmPasswordLabel: "Confirm password",
    save: "Update password",
  },
} as const;
