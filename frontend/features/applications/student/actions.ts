"use server";

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

  const skills = formData.getAll("skills");
  for (const skill of skills) {
    if (typeof skill === "string" && skill.trim() !== "") {
      outbound.append("skills[]", skill.trim());
    }
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