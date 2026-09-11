"use server";

import { revalidatePath } from "next/cache";

import { serverFetch } from "@/services/api";
import type { ActionState } from "@/types/server-action";

// Backend-named text fields carried by the wizard's hidden payload inputs
// (ApplicationPayloadFields). Empties are dropped before the request.
const TEXT_FIELDS = [
  "full_name",
  "email",
  "phone",
  "address",
  "city",
  "training_id",
  "university_id",
  "academic_year",
  "graduation_year",
  "applicant_type",
  "message",
  "why_interested",
  "what_to_learn",
] as const;

// submitApplication: relays the wizard's collected form data to
// POST /api/v1/applications as multipart/form-data. The backend is the source
// of truth for validation — its 422 fieldErrors flow back through ActionState
// (toasted by useFormFeedback). Pure relay: no rules live here beyond building
// a clean outbound payload the service accepts.
export async function submitApplication(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState | undefined> {
  const outbound = new FormData();

  for (const field of TEXT_FIELDS) {
    const value = formData.get(field);
    if (typeof value === "string" && value.trim() !== "") {
      outbound.set(field, value.trim());
    }
  }

  // The wizard collects the university as free text, but the service only
  // accepts an existing university id — drop anything non-numeric.
  const universityId = formData.get("university_id");
  if (typeof universityId === "string") {
    outbound.set("university_id", universityId);
  }

  const cv = formData.get("cv");
  if (cv instanceof File && cv.size > 0) {
    outbound.append("cv", cv, cv.name);
  }

  const result = await serverFetch({
    url: "applications",
    method: "POST",
    body: outbound,
  });

  if (!result.success) {
    console.log(result.error);
    
    return {
      success: false,
      error: result.error,
      fieldErrors: result.errors,
    };
  }

  return {
    success: true,
    message: result.message,
  };
}

// withdrawApplication: withdraws an applied training via
// POST /api/v1/applications/withdraw?id=<applicationId> (query param, per the
// backend controller). The backend rejects withdrawals it doesn't allow; its
// message flows back through ActionState (toasted by useFormFeedback).
// revalidatePath("/applications") re-renders the page server-side so the
// withdrawn card disappears (and tab counts adjust) without client refetching.
export async function withdrawApplication(
  applicationId: number,
): Promise<ActionState> {
  const result = await serverFetch({
    url: `applications/withdraw?id=${applicationId}`,
    method: "POST",
    body: {},
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not withdraw the application.",
    };
  }

  revalidatePath("/applications");
  return { success: true, message: result.message };
}

// reportPayment: reports a manual bank transfer for an accepted paid
// application via POST /api/v1/applications/{id}/payment. Native form action
// (structure rules §10): reads the named fields off FormData — application_id
// from the hidden input, reference from the uncontrolled input — and relays
// them to the backend, which is the source of truth for validation (a blank
// reference comes back as fieldErrors.reference; length limits are
// backend-enforced too). Resubmitting for a row still pending verification
// only updates the reference (idempotent); submitting for an already-confirmed
// payment returns 409. revalidatePath("/applications") + Next's automatic
// route refresh re-render the server ReportPaymentZone, whose fetchPaymentStatus
// read then shows the "Payment reported" panel — no client success state.
// Pure relay — no business rules live here.
export async function reportPayment(
  prevState: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const applicationId = Number.parseInt(
    String(formData.get("application_id") ?? ""),
    10,
  );
  const reference = String(formData.get("reference") ?? "").trim();

  if (!Number.isFinite(applicationId)) {
    return { success: false, error: "Missing application id." };
  }

  const result = await serverFetch({
    url: `applications/${applicationId}/payment`,
    method: "POST",
    body: { reference },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not report the payment.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/applications");
  return { success: true, message: result.message };
}