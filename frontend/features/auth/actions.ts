"use server";

import { redirect } from "next/navigation";

import { COMPANY_PENDING_ROUTE, ROLE_HOME } from "@/config/routes";
import { apiFetch } from "@/services/api";
import { setCookie } from "@/services/cookies";
import type { ActionState } from "@/types/server-action";
import type { CompanyStatus, Role } from "@/types/auth";

import {
  validateForgotPassword,
  validateResetPassword,
  validateSignIn,
  validateSignUp,
} from "./lib/validation";

// The backend doesn't exist yet, so these actions currently fail at the
// apiFetch call with a readable message. The shape below is the intended
// contract: every auth endpoint returns a session object, which the
// action persists as cookies (matching services/auth.ts getSession) and
// uses to redirect the user to their role home.

const NETWORK_ERROR =
  "We couldn't reach Masar's servers. Please try again in a moment.";

interface AuthSessionResponse {
  token?: string;
  role?: Role;
  companyStatus?: CompanyStatus;
}

interface PersistedSession {
  token: string;
  role: Role;
  companyStatus?: CompanyStatus;
}

const SESSION_MAX_AGE = 60 * 60 * 24 * 7;

async function persistSession({ token, role, companyStatus }: PersistedSession) {
  await setCookie("jwt", token, { maxAge: SESSION_MAX_AGE });
  await setCookie("role", role, { maxAge: SESSION_MAX_AGE });
  if (companyStatus) {
    await setCookie("companyStatus", companyStatus, { maxAge: SESSION_MAX_AGE });
  }
}

function sessionTarget(role: Role, companyStatus?: CompanyStatus): string {
  return role === "company" && companyStatus === "pending"
    ? COMPANY_PENDING_ROUTE
    : ROLE_HOME[role];
}

export async function signIn(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const { values, errors } = validateSignIn(formData);
  if (Object.keys(errors).length > 0) return { success: false, fieldErrors: errors };

  let session: AuthSessionResponse;
  try {
    session = await apiFetch<AuthSessionResponse>("/auth/login", {
      method: "POST",
      body: JSON.stringify({ email: values.email, password: values.password }),
    });
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : NETWORK_ERROR };
  }

  if (!session.token || !session.role) {
    return { success: false, message: "Something went wrong. Please try again." };
  }

  const { token, role, companyStatus } = session;
  await persistSession({ token, role, companyStatus });
  redirect(sessionTarget(role, companyStatus));
}

export async function signUp(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const { values, errors } = validateSignUp(formData);
  if (Object.keys(errors).length > 0) return { success: false, fieldErrors: errors };

  const role = String(formData.get("role") ?? "student") as Role;

  const payload = {
    fullName: values.fullName,
    email: values.email,
    password: values.password,
    role,
    ...(role === "company" ? { companyName: values.companyName } : {}),
  };

  let session: AuthSessionResponse;
  try {
    session = await apiFetch<AuthSessionResponse>("/auth/register", {
      method: "POST",
      body: JSON.stringify(payload),
    });
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : NETWORK_ERROR };
  }

  if (!session.token || !session.role) {
    return { success: false, message: "Something went wrong. Please try again." };
  }

  const { token, role: sessionRole, companyStatus } = session;
  await persistSession({ token, role: sessionRole, companyStatus });
  redirect(sessionTarget(sessionRole, companyStatus));
}

export async function forgotPassword(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const { values, errors } = validateForgotPassword(formData);
  if (Object.keys(errors).length > 0) return { success: false, fieldErrors: errors };

  try {
    await apiFetch("/auth/forgot-password", {
      method: "POST",
      body: JSON.stringify({ email: values.email }),
    });
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : NETWORK_ERROR };
  }

  // Always report success regardless of whether an account exists — we
  // don't leak which emails are registered.
  return { success: true };
}

export async function resetPassword(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const { values, errors } = validateResetPassword(formData);
  if (Object.keys(errors).length > 0) return { success: false, fieldErrors: errors };

  const token = formData.get("token");
  if (!token) {
    return { success: false, message: "This reset link is invalid or expired." };
  }

  try {
    await apiFetch("/auth/reset-password", {
      method: "POST",
      body: JSON.stringify({ token: String(token), password: values.password }),
    });
  } catch (error) {
    return { success: false, message: error instanceof Error ? error.message : NETWORK_ERROR };
  }

  return { success: true };
}
