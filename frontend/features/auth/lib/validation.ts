// Server-side validation for the auth actions. Each form passes its
// FormData through one of the validate* helpers, which return typed field
// values plus per-field error arrays that map straight onto ActionState.
// Validators are intentionally small (no external schema library yet) —
// the actions/ folder is where they'll be swapped for zod or a backend
// schema once the API contract exists.

import type { ActionState } from "@/types/server-action";

// Per-field error map — same shape as ActionState.fieldErrors, so validators
// feed straight into useActionState.
export type FieldErrors = NonNullable<ActionState["fieldErrors"]>;

type Rule = (value: string | undefined) => string | undefined;

export function required(value: string | undefined): string | undefined {
  return value && value.trim().length > 0 ? undefined : "This field is required.";
}

export function email(value: string | undefined): string | undefined {
  const trimmed = value?.trim() ?? "";
  if (!trimmed) return "This field is required.";
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed) ? undefined : "Enter a valid email address.";
}

export function minLength(min: number, message: string): Rule {
  return (value) => {
    if (!value) return undefined; // emptiness is `required`'s job
    return value.length >= min ? undefined : message;
  };
}

export interface ValidationResult {
  values: Record<string, string>;
  errors: FieldErrors;
}

function validate(formData: FormData, fields: Record<string, Rule[]>): ValidationResult {
  const values: Record<string, string> = {};
  const errors: FieldErrors = {};

  for (const [name, rules] of Object.entries(fields)) {
    const raw = formData.get(name);
    const value = typeof raw === "string" ? raw : "";
    values[name] = value;

    const message = rules.map((rule) => rule(value)).find((m): m is string => Boolean(m));
    if (message) errors[name] = [message];
  }

  return { values, errors };
}

export function validateSignIn(formData: FormData): ValidationResult {
  return validate(formData, {
    email: [email],
    password: [required],
  });
}

export function validateSignUp(formData: FormData): ValidationResult {
  const role = String(formData.get("role") ?? "student");

  const fields: Record<string, Rule[]> = {
    fullName: [required],
    email: [email],
    password: [required, minLength(8, "Password must be at least 8 characters.")],
    terms: [required],
  };

  if (role === "company") fields.companyName = [required];

  return validate(formData, fields);
}

export function validateForgotPassword(formData: FormData): ValidationResult {
  return validate(formData, {
    email: [email],
  });
}

export function validateResetPassword(formData: FormData): ValidationResult {
  const result = validate(formData, {
    password: [required, minLength(8, "Password must be at least 8 characters.")],
    confirmPassword: [required],
  });

  if (
    !result.errors.password &&
    result.values.password !== result.values.confirmPassword
  ) {
    result.errors.confirmPassword = ["Passwords don't match."];
  }

  return result;
}
