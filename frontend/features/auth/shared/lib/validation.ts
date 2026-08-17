// Pre-request UX validation for the auth actions. The backend is the source
// of truth for every field rule, and HTML input constraints (`required`,
// `type="email"`, …) cover the browser-side basics — so this file keeps
// only the checks that genuinely help the user before a request.
//
// - Sign-up step 1 (`validateSignupPassword`): mirrors the backend register
//   password rules (min length + uppercase/lowercase/special, see constants.ts)
//   and the password/confirm-password mismatch. Run before the request so a
//   weak password is caught without a round trip.
// - Reset-password (`validatePasswordConfirmation`): mismatch only — the reset
//   backend rule differs (min 8) and stays backend-only.

import type { ActionState } from "@/types/server-action";
import {
  PASSWORD_MIN_LENGTH,
  PASSWORD_UPPERCASE_REGEX,
  PASSWORD_LOWERCASE_REGEX,
  PASSWORD_SPECIAL_CHAR_REGEX,
} from "./constants";

// Per-field error map — same shape as ActionState.fieldErrors, so validators
// feed straight into useActionState.
export type FieldErrors = NonNullable<ActionState["fieldErrors"]>;

export interface ValidationResult {
  values: Record<string, string>;
  errors: FieldErrors;
}

function confirmationError(
  password: string,
  confirmPassword: string,
): FieldErrors {
  if (password !== confirmPassword) {
    return { confirmPassword: ["Passwords don't match."] };
  }
  return {};
}

export function validateSignupPassword(formData: FormData): ValidationResult {
  const password = String(formData.get("password") ?? "");
  const confirmPassword = String(formData.get("confirmPassword") ?? "");
  const errors: FieldErrors = {};

  if (password.length < PASSWORD_MIN_LENGTH) {
    errors.password = [
      `Password must be at least ${PASSWORD_MIN_LENGTH} characters.`,
    ];
  }
  if (!PASSWORD_UPPERCASE_REGEX.test(password)) {
    errors.password = [
      ...(errors.password ?? []),
      "Password must contain an uppercase letter.",
    ];
  }
  if (!PASSWORD_LOWERCASE_REGEX.test(password)) {
    errors.password = [
      ...(errors.password ?? []),
      "Password must contain a lowercase letter.",
    ];
  }
  if (!PASSWORD_SPECIAL_CHAR_REGEX.test(password)) {
    errors.password = [
      ...(errors.password ?? []),
      "Password must contain a special character.",
    ];
  }

  Object.assign(errors, confirmationError(password, confirmPassword));

  return { values: { password, confirmPassword }, errors };
}

export function validatePasswordConfirmation(
  formData: FormData,
): ValidationResult {
  const password = String(formData.get("password") ?? "");
  const confirmPassword = String(formData.get("confirmPassword") ?? "");

  return {
    values: { password, confirmPassword },
    errors: confirmationError(password, confirmPassword),
  };
}

// The first user-facing message in the error map, for the action's generic
// `error` slot (toasted by useFormFeedback). Password rule messages come
// first, then the mismatch.
export function firstPasswordError(errors: FieldErrors): string {
  return (
    errors.password?.[0] ??
    errors.confirmPassword?.[0] ??
    "Passwords don't match."
  );
}