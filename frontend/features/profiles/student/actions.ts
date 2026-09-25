"use server";

// Placeholder server actions for the student profile page (architecture R14:
// role-level actions.ts).
//
// There is no profile API yet, so NOT ONE of these is wired to the UI. Every
// control on /profile that would normally persist its value is deliberately a
// no-op — a `type="button"` control, or purely local state for the avatar /
// certificate previews — so the page never fakes a save, a toast, or a spinner.
//
// These signatures document the contract the future backend work has to
// satisfy. Each body throws rather than returning a fabricated ActionState, so
// an accidental call fails loudly instead of being mistaken for a real write.

import type {
  AcademicLevel,
  ContactInfo,
  EducationEntry,
} from "./types";

function notWired(action: string, endpoint: string): Error {
  return new Error(
    `${action} is not wired yet — the profile API does not exist. Connect it to ${endpoint}.`,
  );
}

export async function updateUsername(_username: string): Promise<void> {
  // TODO: connect to backend — PATCH /api/v1/profile/username, then
  // revalidatePath("/profile") so the identity card re-reads the new username.
  throw notWired("updateUsername", "PATCH /api/v1/profile/username");
}

export async function updatePassword(_payload: {
  currentPassword: string;
  newPassword: string;
}): Promise<void> {
  // TODO: connect to backend — PATCH /api/v1/profile/password. The backend owns
  // the real password policy; the client-side min-length/match check on the form
  // is UX only (structure rules §10 — validation stays a UX affordance, the API
  // is the source of truth).
  throw notWired("updatePassword", "PATCH /api/v1/profile/password");
}

export async function updateProfilePhoto(_file: File): Promise<void> {
  // TODO: connect to backend — upload the image first (multipart), then PATCH
  // /api/v1/profile/avatar with the returned file id.
  throw notWired("updateProfilePhoto", "the avatar upload + PATCH /api/v1/profile/avatar");
}

export async function updateContactInfo(_contact: ContactInfo): Promise<void> {
  // TODO: connect to backend — PUT /api/v1/profile/contact.
  throw notWired("updateContactInfo", "PUT /api/v1/profile/contact");
}

export async function updateSkills(_skills: string[]): Promise<void> {
  // TODO: connect to backend — PUT /api/v1/profile/skills. The dropdown only
  // offers catalog entries, so every value is already a known skill name.
  throw notWired("updateSkills", "PUT /api/v1/profile/skills");
}

export async function updateEducation(_entries: EducationEntry[]): Promise<void> {
  // TODO: connect to backend — PUT /api/v1/profile/education. The list is
  // edited as a whole (add + remove + field edits) rather than one row at a
  // time, so the endpoint should accept the full ordered set.
  throw notWired("updateEducation", "PUT /api/v1/profile/education");
}

export async function updateAcademicStatus(_payload: {
  level: AcademicLevel;
  graduationDate?: string;
  certificate?: File;
}): Promise<void> {
  // TODO: connect to backend — the graduation certificate is a multipart upload
  // to the files service first, then PATCH /api/v1/profile/academic-status with
  // the returned id. `level` is the only field sent for a student.
  throw notWired(
    "updateAcademicStatus",
    "the graduation certificate upload + PATCH /api/v1/profile/academic-status",
  );
}
