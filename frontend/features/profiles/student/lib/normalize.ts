// API → UI mapping for the student profile (architecture R14). Raw backend
// shapes are coerced here so no component reaches into them.

import { toInitials } from "@/shared/lib/initials";
import type { TryCatchResponse } from "@/types/server-action";
import type {
  AcademicCertificate,
  AcademicStatusData,
  ContactInfo,
  EducationDegreeOption,
  EducationEntry,
  ProfileIdentity,
  ProfileImagePayload,
  ProfileViewsData,
  Skill,
} from "../types";

// GET /students/me returns exactly data.header (thirteen fields); the hero uses
// full_name, specialization, field, university, faculty, member_since and the
// signed profile_image_url. Every text value is nullable on the wire — the
// backend already normalizes blanks to null — so each one is re-checked here and
// left null for the hero to omit.
export function normalizeProfileIdentity(
  result: TryCatchResponse,
): ProfileIdentity {
  const header = (result.data?.header ?? {}) as Record<string, unknown>;

  const name = toText(header.full_name);
  const university = toText(header.university);
  const faculty = toText(header.faculty);
  const institution =
    [university, faculty].filter(Boolean).join(", ") || null;

  return {
    name,
    initials: toInitials(name),
    specialization: toText(header.specialization),
    field: toText(header.field),
    institution,
    memberSince: toText(header.member_since),
    avatarUrl: readUrl(header.profile_image_url),
  };
}

// GET /students/me/full-name returns a single data.full_name, nullable when
// the account has never set one. The form renders an empty field for that case.
export function normalizeFullName(data: unknown): string | null {
  const raw = (data as { full_name?: unknown } | undefined)?.full_name;
  return toText(raw);
}

// POST/DELETE /students/me/profile-image return the image flat at
// data.profile_image_url.
export function normalizeProfileImagePayload(
  data: unknown,
): ProfileImagePayload {
  const raw = (data as { profile_image_url?: unknown } | undefined)
    ?.profile_image_url;

  return { profileImageUrl: readUrl(raw) ?? null };
}

// GET /students/me/profile-views returns { total_views, recent_limit,
// recent_viewers: [...] }. The total is never capped by the limit, and the
// viewer array may be empty. Each viewer is coerced to the flat ProfileViewer
// shape the row component renders.
export function normalizeProfileViews(data: unknown): ProfileViewsData {
  const raw =
    (data as { total_views?: unknown; recent_viewers?: unknown } | undefined) ?? {};

  const count =
    typeof raw.total_views === "number" && Number.isFinite(raw.total_views)
      ? raw.total_views
      : 0;

  const viewersRaw = Array.isArray(raw.recent_viewers) ? raw.recent_viewers : [];

  const viewers = viewersRaw.map((viewer) => {
    const v = viewer as {
      viewer_id?: unknown;
      display_name?: unknown;
      viewer_role?: unknown;
      viewed_at?: unknown;
    };

    const name = toText(v.display_name);

    return {
      id:
        typeof v.viewer_id === "number" && Number.isFinite(v.viewer_id)
          ? v.viewer_id
          : 0,
      name: name ?? "",
      role: toText(v.viewer_role) ?? "",
      initials: toInitials(name),
      viewedAt: typeof v.viewed_at === "string" ? v.viewed_at : "",
    };
  });

  return { count, viewers };
}

// GET /students/me/contact returns { email, phone, website, external_profile,
// address }, all nullable. The backend key is external_profile; the UI uses
// profileLink. Missing keys and empty strings coerce to null so the UI can
// omit unset fields instead of printing blank rows.
export function normalizeContactInfo(data: unknown): ContactInfo {
  const raw = data as {
    email?: unknown;
    phone?: unknown;
    website?: unknown;
    external_profile?: unknown;
    address?: unknown;
  } | undefined;

  return {
    email: toText(raw?.email),
    phone: toText(raw?.phone),
    website: toText(raw?.website),
    profileLink: toText(raw?.external_profile),
    address: toText(raw?.address),
  };
}

// GET /students/me/academic-status returns { academic_status,
// graduation_date, graduation_certificate?: { id, file_name, file_url } }.
// The backend defaults academic_status to "student" when there is no record,
// so a missing or unrecognized value reads as "student" rather than an
// unknown state. An incomplete certificate object (missing id/file_name/
// file_url) reads as null.
export function normalizeAcademicStatus(
  result: TryCatchResponse,
): AcademicStatusData {
  const raw =
    (result.data as {
      academic_status?: unknown;
      graduation_date?: unknown;
      graduation_certificate?: unknown;
    } | undefined) ?? {};

  const academicStatus =
    typeof raw.academic_status === "string" &&
    (raw.academic_status === "student" || raw.academic_status === "graduate")
      ? raw.academic_status
      : "student";

  const graduationDate = toText(raw.graduation_date);

  const certificateRaw =
    raw.graduation_certificate &&
    typeof raw.graduation_certificate === "object"
      ? (raw.graduation_certificate as {
          id?: unknown;
          file_name?: unknown;
          file_url?: unknown;
        })
      : null;

  const graduationCertificate =
    certificateRaw &&
    typeof certificateRaw.id === "number" &&
    Number.isFinite(certificateRaw.id) &&
    typeof certificateRaw.file_name === "string" &&
    certificateRaw.file_name !== "" &&
    typeof certificateRaw.file_url === "string" &&
    certificateRaw.file_url !== ""
      ? {
          id: certificateRaw.id,
          fileName: certificateRaw.file_name,
          fileUrl: certificateRaw.file_url,
        }
      : null;

  return { academicStatus, graduationDate, graduationCertificate };
}

// GET /students/me/skills returns { skills: string[] } — the student's own
// skill names. Coerce defensively: skip anything that isn't a string so the
// UI never renders a non-name, and default to an empty list when the key is
// absent or not an array.
export function normalizeStudentSkills(data: unknown): string[] {
  const raw = (data as { skills?: unknown } | undefined)?.skills;

  if (!Array.isArray(raw)) return [];

  return raw
    .map((entry) => toText(entry))
    .filter((name): name is string => name !== null);
}

// GET /lookups/skills returns { skills: [{ id, name }] } — the active
// taxonomy ordered by name. Coerce each id to a number so consumers can
// compare it and send it back in POST/DELETE bodies; default to [].
export function normalizeSkillOptions(data: unknown): Skill[] {
  const raw = (data as { skills?: unknown } | undefined)?.skills;

  if (!Array.isArray(raw)) return [];

  return raw.map((entry) => {
    const option = entry as { id?: unknown; name?: unknown };

    return {
      id: Number(option.id),
      name: toText(option.name) ?? "",
    };
  });
}

// GET /students/me/education returns { data: { items: [...] } } where each item
// is a presented record in snake_case. Coerce defensively (numbers via Number,
// strings via toText, booleans via !!) and default to [] so consumers can map
// safely without null checks.
export function normalizeEducation(
  result: TryCatchResponse,
): EducationEntry[] {
  const itemsRaw = (result.data as { items?: unknown } | undefined)?.items;

  if (!Array.isArray(itemsRaw)) return [];

  return itemsRaw.map((entry) => {
    const e = entry as {
      id?: unknown;
      degree_id?: unknown;
      degree?: unknown;
      university?: unknown;
      faculty?: unknown;
      field?: unknown;
      specialization?: unknown;
      study_mode?: unknown;
      start_year?: unknown;
      end_year?: unknown;
      grade?: unknown;
      is_current?: unknown;
      created_at?: unknown;
      updated_at?: unknown;
    };

    return {
      id:
        typeof e.id === "number" && Number.isFinite(e.id) ? e.id : 0,
      degreeId:
        typeof e.degree_id === "number" && Number.isFinite(e.degree_id)
          ? e.degree_id
          : null,
      degree: toText(e.degree),
      university: toText(e.university),
      faculty: toText(e.faculty),
      fieldOfStudy: toText(e.field),
      specialization: toText(e.specialization),
      studyMode: toText(e.study_mode),
      startYear:
        typeof e.start_year === "number" && Number.isFinite(e.start_year)
          ? e.start_year
          : null,
      endYear:
        typeof e.end_year === "number" && Number.isFinite(e.end_year)
          ? e.end_year
          : null,
      grade: toText(e.grade),
      isCurrent: !!e.is_current,
      createdAt: toText(e.created_at),
      updatedAt: toText(e.updated_at),
    };
  });
}

// GET /lookups/education-degrees returns { data: { education_degrees: [...] } }.
// Coerce each id to a number so consumers can send it back in POST/PUT bodies;
// default to [] when the key is absent or not an array.
export function normalizeEducationDegrees(
  data: unknown,
): EducationDegreeOption[] {
  const raw =
    (data as { education_degrees?: unknown } | undefined)?.education_degrees;

  if (!Array.isArray(raw)) return [];

  return raw.map((entry) => {
    const option = entry as { id?: unknown; name?: unknown };

    return {
      id:
        typeof option.id === "number" && Number.isFinite(option.id)
          ? option.id
          : 0,
      name: toText(option.name) ?? "",
    };
  });
}

function toText(value: unknown): string | null {
  if (typeof value !== "string") return null;

  const trimmed = value.trim();
  return trimmed === "" ? null : trimmed;
}

// Signed URLs are the only non-empty string we accept; anything else (null,
// "", a missing key) means "no photo", which renders the initials fallback.
function readUrl(value: unknown): string | undefined {
  return typeof value === "string" && value !== "" ? value : undefined;
}
