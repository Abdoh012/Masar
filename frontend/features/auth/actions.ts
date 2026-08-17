"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { serverFetch } from "@/services/api";
import { ActionState } from "@/types/server-action";
import { COMPANY_PENDING_ROUTE } from "@/config/routes";
import { authenticate } from "./services";
import {
  validateSignupPassword,
  validatePasswordConfirmation,
  firstPasswordError,
} from "./shared/lib/validation";

// Step 1 of sign-up: run the pre-request password checks, then hand off to
// step 2 (/profile-information) via the in-memory signup draft.
export async function stageSignup(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const { errors } = validateSignupPassword(formData);
  if (Object.keys(errors).length > 0) {
    return {
      success: false,
      error: firstPasswordError(errors),
      fieldErrors: errors,
      data: {
        fullName: String(formData.get("fullName") ?? ""),
        companyName: String(formData.get("companyName") ?? ""),
        email: String(formData.get("email") ?? ""),
      },
    };
  }

  redirect("/profile-information");
}

// Step 2 of sign-up: combine the step-1 basics (hidden inputs on this form)
export async function signup(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const role =
    String(formData.get("role") ?? "") === "company" ? "company" : "student";
  const email = String(formData.get("email") ?? "");
  const password = String(formData.get("password") ?? "");

  if (!email || !password) {
    return {
      success: false,
      error: "Your sign-up session expired. Please start over.",
      data: null,
    };
  }

  const base = {
    email,
    password,
    password_confirmation: password,
    accept_terms: formData.get("acceptTerms") === "on",
    role,
  };

  const description = String(formData.get("description") ?? "");

  const payload =
    role === "company"
      ? {
          ...base,
          company_name: String(formData.get("companyName") ?? ""),
          industry: String(formData.get("industry") ?? ""),
          description: description || undefined,
        }
      : {
          ...base,
          full_name: String(formData.get("fullName") ?? ""),
          university: String(formData.get("university") ?? ""),
          faculty: String(formData.get("userField") ?? ""),
          specialization: String(formData.get("specialist") ?? ""),
        };

  const result = await authenticate({ url: "auth/register", body: payload });

  if (!result.success) {
    return {
      success: false,
      error: result.error,
      fieldErrors: result.fieldErrors,
      data: {
        userField: String(formData.get("userField") ?? ""),
        specialist: String(formData.get("specialist") ?? ""),
        university: String(formData.get("university") ?? ""),
        industry: String(formData.get("industry") ?? ""),
        description: String(formData.get("description") ?? ""),
      },
    };
  }

  // Companies get no token on register — they're pending approval.
  if (result.role === "company") {
    redirect(COMPANY_PENDING_ROUTE);
  }

  redirect(result.redirectPath ?? "/sign-in");
}

export async function signIn(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const email = String(formData.get("email") ?? "");
  const password = String(formData.get("password") ?? "");

  const result = await authenticate({
    url: "auth/login",
    body: { email, password },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error,
      fieldErrors: result.fieldErrors,
      data: { email },
    };
  }

  redirect(result.redirectPath ?? "/dashboard");
}

export async function forgotPassword(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const email = String(formData.get("email") ?? "");

  const result = await serverFetch({
    url: "auth/forgot-password",
    method: "POST",
    body: { email },
  });

  if (!result.success) {
    return { success: false, error: result.error, data: result.userData };
  }

  redirect(`/verify-otp?email=${encodeURIComponent(email)}`);
}

export async function verifyOtp(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const email = String(formData.get("email") ?? "");
  const token = String(formData.get("token") ?? "");

  const result = await serverFetch({
    url: "auth/verify-reset-otp",
    method: "POST",
    body: { email, token },
  });

  if (!result.success) {
    return { success: false, error: result.error, data: result.userData };
  }

  // The verified OTP is itself the reset token the final step validates.
  redirect(
    `/reset-password/${encodeURIComponent(token)}?email=${encodeURIComponent(email)}`,
  );
}

export async function resendOtp(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const email = String(formData.get("email") ?? "");

  const result = await serverFetch({
    url: `auth/resend-reset-otp?email=${encodeURIComponent(email)}`,
    method: "POST",
    body: {},
  });

  if (!result.success) {
    return { success: false, error: result.error };
  }

  return {
    success: true,
    message: result.message || "A new code has been sent to your email.",
  };
}

export async function resetPassword(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const { values, errors } = validatePasswordConfirmation(formData);
  if (Object.keys(errors).length > 0) {
    return {
      success: false,
      error: firstPasswordError(errors),
      fieldErrors: errors,
      data: values,
    };
  }

  const email = String(formData.get("email") ?? "");
  const token = String(formData.get("token") ?? "");

  const result = await serverFetch({
    url: "auth/reset-password",
    method: "POST",
    body: {
      email,
      token,
      password: values.password,
      password_confirmation: values.confirmPassword,
    },
  });

  if (!result.success) {
    return { success: false, error: result.error, data: result.userData };
  }

  redirect("/sign-in");
}

export async function logout() {
  await serverFetch({ url: "auth/logout", method: "POST", body: {} });

  const cookieStore = await cookies();
  cookieStore.delete("masarJwt");
  cookieStore.delete("masarRole");
  cookieStore.delete("companyStatus");

  redirect("/sign-in");
}
