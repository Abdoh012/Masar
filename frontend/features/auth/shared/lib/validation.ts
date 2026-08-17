// Pre-request UX validation for the auth actions. The backend is the source
// of truth for every field rule, and HTML input constraints (`required`,
// `type="email"`, …) cover the browser-side basics — so this file keeps
// only the check that genuinely helps the user before a request: the
// password/confirm-password mismatch, which no input type expresses and a
// round trip to the backend would only answer slowly.

import type { ActionState } from "@/types/server-action";

// Per-field error map — same shape as ActionState.fieldErrors, so validators
// feed straight into useActionState.
export type FieldErrors = NonNullable<ActionState["fieldErrors"]>;

export interface ValidationResult {
  values: Record<string, string>;
  errors: FieldErrors;
}

export function validatePasswordConfirmation(
  formData: FormData,
): ValidationResult {
  const password = String(formData.get("password") ?? "");
  const confirmPassword = String(formData.get("confirmPassword") ?? "");
  const errors: FieldErrors = {};

  if (password !== confirmPassword) {
    errors.confirmPassword = ["Passwords don't match."];
  }

  return { values: { password, confirmPassword }, errors };
}

// The first user-facing message in the error map, for the action's generic
// `error` slot (toasted by useFormFeedback).
export function firstPasswordError(errors: FieldErrors): string {
  return errors.confirmPassword?.[0] ?? "Passwords don't match.";
}