"use server";
// Server actions for the student profile page (architecture R14: role-level
// actions.ts).
//
// Wired today: the profile photo (uploadProfilePhoto / removeProfilePhoto —
// /students/me/profile-image), the account full name (updateFullName —
// PUT /students/me/full-name) and the credential change (changePassword —
// POST /auth/change-password), and the contact info (updateContactInfo —
// PUT /students/me/contact). Each one is a thin relay that reports the
// backend message back through ActionState; the backend owns every rule.
//
// Everything else here is still a placeholder — there is no backend for those
// fields yet, so no other control on /profile fakes a save, a toast, or a
// spinner. Each placeholder throws rather than returning a fabricated
// ActionState, so an accidental call fails loudly instead of being mistaken for
// a real write.
//

import { revalidatePath } from "next/cache";

import { serverFetch } from "@/services/api";
import { clearSessionCookies } from "@/services/cookies";
import type { ActionState } from "@/types/server-action";

import { normalizeProfileImagePayload } from "./lib/normalize";
import type {
  ContactInfo,
  FullNameFormValues,
  ProfileImagePayload,
} from "./types";

function notWired(action: string, endpoint: string): Error {
  return new Error(
    `${action} is not wired yet — the profile API does not exist. Connect it to ${endpoint}.`,
  );
}

export async function updateFullName(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState<FullNameFormValues>> {
  const fullName = String(formData.get("full_name") ?? "").trim();

  if (fullName === "") {
    return {
      success: false,
      error: "Enter your full name.",
      fieldErrors: { full_name: ["Enter your full name."] },
      data: { fullName },
    };
  }

  const result = await serverFetch({
    url: "students/me/full-name",
    method: "PUT",
    body: { full_name: fullName },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not update your full name.",
      fieldErrors: result.errors,
      data: { fullName },
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Full name updated.",
    data: { fullName },
  };
}

// changePassword: POST /auth/change-password. The field names are the backend
// (`current_password` / `new_password` / `new_password_confirmation`), not the
// register/reset aliases. The mismatch check runs before the request only
// because the API cannot explain it any better; the password policy stays the
// backend, and its 422 field errors flow straight back.
//
// A successful change deliberately ends the session: the endpoint revokes every
// access/refresh token the user holds and clears the refresh cookie, so the
// browser copy is dead the moment it returns. The cookies are therefore
// cleared here instead of leaving the UI claiming to be signed in with a revoked
// token; the form toasts the message below and sends the student to sign in.
export async function changePassword(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const currentPassword = String(formData.get("current_password") ?? "");
  const newPassword = String(formData.get("new_password") ?? "");
  const confirmation = String(formData.get("new_password_confirmation") ?? "");

  if (currentPassword === "") {
    return {
      success: false,
      error: "Enter your current password.",
      fieldErrors: { current_password: ["Enter your current password."] },
    };
  }

  if (newPassword === "") {
    return {
      success: false,
      error: "Enter a new password.",
      fieldErrors: { new_password: ["Enter a new password."] },
    };
  }

  if (newPassword !== confirmation) {
    return {
      success: false,
      error: "Passwords do not match.",
      fieldErrors: {
        new_password_confirmation: ["Passwords do not match."],
      },
    };
  }

  const result = await serverFetch({
    url: "auth/change-password",
    method: "POST",
    body: {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: confirmation,
    },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not change your password.",
      fieldErrors: result.errors,
    };
  }

  await clearSessionCookies();

  return {
    success: true,
    message: "Password updated. Please sign in again with your new password.",
  };
}

// uploadProfilePhoto: multipart relay to POST /api/v1/students/me/profile-image
// (the backend field is image). The backend owns format/size validation and
// swaps the stored image atomically, so a failed replacement leaves the
// existing photo in place. revalidatePath("/profile") re-renders the server
// hero section, whose read returns the freshly signed URL — the returned URL is
// only used to bridge the moment before that refresh lands.
export async function uploadProfilePhoto(
  file: File,
): Promise<ActionState<ProfileImagePayload>> {
  if (!(file instanceof File) || file.size === 0) {
    return { success: false, error: "Choose an image to upload." };
  }

  const outbound = new FormData();
  outbound.append("image", file, file.name);

  const result = await serverFetch({
    url: "students/me/profile-image",
    method: "POST",
    body: outbound,
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not upload the profile photo.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message,
    data: normalizeProfileImagePayload(result.data),
  };
}

// removeProfilePhoto: DELETE /api/v1/students/me/profile-image. Removal is
// immediate (no confirm step) — the photo can be re-uploaded, and the backend
// clears the stored reference in one write.
export async function removeProfilePhoto(): Promise<
  ActionState<ProfileImagePayload>
> {
  const result = await serverFetch({
    url: "students/me/profile-image",
    method: "DELETE",
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not remove the profile photo.",
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message,
    data: normalizeProfileImagePayload(result.data),
  };
}

export async function updateContactInfo(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState<ContactInfo>> {
  const email = String(formData.get("email") ?? "");
  const phone = String(formData.get("phone") ?? "");
  const website = String(formData.get("website") ?? "");
  const profileLink = String(formData.get("profile_link") ?? "");
  const address = String(formData.get("address") ?? "");

  const body: {
    phone: string | null;
    website: string | null;
    external_profile: string | null;
    address: string | null;
    email: string;
  } = {
    phone: phone === "" ? null : phone,
    website: website === "" ? null : website,
    external_profile: profileLink === "" ? null : profileLink,
    address: address === "" ? null : address,
    email: email,
  };

  const result = await serverFetch({
    url: "students/me/contact",
    method: "PUT",
    body,
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not update your contact info.",
      fieldErrors: result.errors,
      data: { email, phone, website, profileLink, address },
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Contact info updated.",
  };
}

export async function addStudentSkill(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const skill = Number(formData.get("skill"));

  const result = await serverFetch({
    url: "students/me/skills",
    method: "POST",
    body: { skill },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not add the skill.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Skill added.",
    data: { skills: result.data?.skills },
  };
}

export async function removeStudentSkill(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const skill = Number(formData.get("skill"));

  const result = await serverFetch({
    url: "students/me/skills",
    method: "DELETE",
    body: { skill },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not remove the skill.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Skill removed.",
    data: { skills: result.data?.skills },
  };
}

export async function updateSkills(_skills: string[]): Promise<void> {
  // TODO: connect to backend — PUT /api/v1/profile/skills. The dropdown only
  // offers catalog entries, so every value is already a known skill name.
  throw notWired("updateSkills", "PUT /api/v1/profile/skills");
}

// readEducationBody: reads the editable education fields off the frontend
// FormData and builds a plain JSON body, sending only fields that are present
// and non-empty. Numbers are converted with Number(); booleans (is_current)
// are included only when the field is present in the form. Shared by add and
// update since both POST/PUT the same field set as JSON.
function readEducationBody(formData: FormData): Record<string, unknown> {
  const body: Record<string, unknown> = {};

  const present = (name: string) => {
    const raw = formData.get(name);
    return raw !== null && String(raw) !== "";
  };

  const num = (name: string) => {
    const raw = formData.get(name);
    return raw !== null && String(raw) !== "" ? Number(raw) : undefined;
  };

  if (present("degree_name"))
    body.degree_name = Number(formData.get("degree_name"));
  const universityName = String(formData.get("university_name") ?? "");
  if (universityName !== "") body.university_name = universityName;
  const facultyId = num("faculty_id");
  if (facultyId !== undefined) body.faculty_id = facultyId;
  const fieldId = num("field_id");
  if (fieldId !== undefined) body.field_id = fieldId;
  const specializationId = num("specialization_id");
  if (specializationId !== undefined) body.specialization_id = specializationId;
  const studyMode = String(formData.get("study_mode") ?? "");
  if (studyMode !== "") body.study_mode = studyMode;
  const startYear = num("start_year");
  if (startYear !== undefined) body.start_year = startYear;
  const endYear = num("end_year");
  if (endYear !== undefined) body.end_year = endYear;
  const grade = String(formData.get("grade") ?? "");
  if (grade !== "") body.grade = grade;
  const isCurrentRaw = formData.get("is_current");
  if (isCurrentRaw !== null) body.is_current = String(isCurrentRaw) === "true";

  return body;
}

// educationIdFromForm: reads the hidden education_id field and coerces it to a
// number, returning NaN when absent or not a number.
function educationIdFromForm(formData: FormData): number {
  const raw = formData.get("education_id");
  return typeof raw === "string" && raw !== "" ? Number(raw) : NaN;
}

// addEducation: POST /students/me/education as JSON. degree_name is sent as a
// number (the backend rejects free-text degrees); is_current as a boolean.
// revalidatePath("/profile") re-runs the education list read so the new record
// shows without a client refetch.
export async function addEducation(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const result = await serverFetch({
    url: "students/me/education",
    method: "POST",
    body: readEducationBody(formData),
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not add the education record.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Education added.",
    data: result.data,
  };
}

// updateEducation: PUT /students/me/education/{education_id} as JSON. The body
// is partial — only fields present in the form are sent, so untouched fields
// keep their stored value. education_id comes from a hidden FormData field.
export async function updateEducation(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const educationId = educationIdFromForm(formData);

  if (Number.isNaN(educationId)) {
    return {
      success: false,
      error: "Missing education record id.",
    };
  }

  const result = await serverFetch({
    url: `students/me/education/${educationId}`,
    method: "PUT",
    body: readEducationBody(formData),
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not update the education record.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Education updated.",
    data: result.data,
  };
}

export async function removeEducation(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const educationId = educationIdFromForm(formData);

  if (Number.isNaN(educationId)) {
    return {
      success: false,
      error: "Missing education record id.",
    };
  }

  const result = await serverFetch({
    url: `students/me/education/${educationId}`,
    method: "DELETE",
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not remove the education record.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Education removed.",
    data: { id: educationId },
  };
}

// updateAcademicStatus: PUT /students/me/academic-status as
// multipart/form-data. Fields: academic_status (required),
// graduation_date (only when the student is a graduate), and
// graduation_certificate (a File, only when a graduate). The backend
// validates presence by role via request_file('graduation_certificate'), so we
// append only what the student actually provided — never an empty file field.
// serverFetch skips Content-Type for FormData bodies and attaches the Bearer
// token automatically. On success /profile re-runs so the section shows the
// saved status without a client refetch.
export async function updateAcademicStatus(
  _prev: ActionState | null,
  formData: FormData,
): Promise<ActionState> {
  const academicStatus = String(formData.get("academic_status") ?? "");
  const graduationDate = String(formData.get("graduation_date") ?? "");
  const certificate = formData.get("graduation_certificate");

  const outbound = new FormData();
  outbound.append("academic_status", academicStatus);
  if (graduationDate !== "") {
    outbound.append("graduation_date", graduationDate);
  }
  if (certificate instanceof File) {
    outbound.append("graduation_certificate", certificate, certificate.name);
  }

  const result = await serverFetch({
    url: "students/me/academic-status",
    method: "PUT",
    body: outbound,
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not update your academic status.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Academic status updated.",
    data: { ...result.data },
  };
}

// removeAcademicStatusCertificate: DELETE /students/me/academic-status/certificate.
// No body — the backend clears the stored graduation certificate for the
// student identified by the access token. On success /profile re-runs so the
// certificate row disappears.
export async function removeAcademicStatusCertificate(): Promise<ActionState> {
  const result = await serverFetch({
    url: "students/me/academic-status/certificate",
    method: "DELETE",
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not remove the graduation certificate.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/profile");

  return {
    success: true,
    message: result.message ?? "Graduation certificate removed.",
  };
}
