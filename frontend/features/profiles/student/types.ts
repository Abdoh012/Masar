// Role-level types for the profiles student (structure rules §14).

/** The identity block at the top of the student dashboard: the student's own
 *  name, academic line and the backend-computed profile completion. Fed by the
 *  dashboard orchestrator from GET /students/dashboard (student), so nothing
 *  here is a mock. */
export interface ProfileHeaderStudent {
  name: string;
  /** Study field name; null when the student hasn't set one. */
  field: string | null;
  /** "Faculty · University" as one line, or null when neither is set. */
  studies: string | null;
  initials: string;
  /** Profile completion, 0..100, exactly as the backend computed it
   *  (student.profile_completion.percentage). Never derived on the client. */
  completion: number;
}

// The identity hero at the top of /profile, mapped from GET /students/me's
// header by lib/normalize.ts. A null means the student hasn't set that value
// yet — the hero omits those pieces instead of printing a placeholder.
export interface ProfileIdentity {
  /** Full name; null when the account has none set. */
  name: string | null;
  /** Monogram derived from the name ("?" when there is no usable name). */
  initials: string;
  specialization: string | null;
  field: string | null;
  /** University and faculty joined for display, or whichever one is set. */
  institution: string | null;
  /** ISO-8601 datetime of account creation — rendered as "Member since". */
  memberSince: string | null;
  /** Signed, short-lived profile image URL; absent when no photo is set. */
  avatarUrl?: string;
}

/** The one value the profile-image endpoints report back: the freshly signed
 *  URL of the stored image, or null once it has been removed. */
export interface ProfileImagePayload {
  profileImageUrl: string | null;
}

/** Values the full-name action echoes back in ActionState.data, so a rejected
 *  submit can re-seed the (uncontrolled) input instead of blanking what the
 *  student typed — React resets uncontrolled fields once an action completes. */
export interface FullNameFormValues {
  fullName: string;
}

// One row in the "profile views" list — a company member who opened the profile.
export interface ProfileViewer {
  id: number;
  name: string;
  role: string;
  initials: string;
  // ISO datetime the profile was last viewed.
  viewedAt: string;
}

// The full profile-views payload: the total count plus the most recent viewers,
// mapped from GET /students/me/profile-views by lib/normalize.ts.
export interface ProfileViewsData {
  count: number;
  viewers: ProfileViewer[];
}

// One entry in the student's education list, mapped from
// GET /api/v1/students/me/education by lib/normalize.ts. Fields mirror the
// backend's presented record (snake_case on the wire, camelCase here).
export interface EducationEntry {
  id: number;
  degreeId: number | null;
  degree: string | null;
  university: string | null;
  faculty: string | null;
  fieldOfStudy: string | null;
  specialization: string | null;
  studyMode: string | null;
  startYear: number | null;
  endYear: number | null;
  grade: string | null;
  isCurrent: boolean;
  createdAt: string | null;
  updatedAt: string | null;
}

// One option from the public education-degrees taxonomy
// (GET /api/v1/lookups/education-degrees).
export interface EducationDegreeOption {
  id: number;
  name: string;
}

// One option from the public study-fields taxonomy
// (GET /api/v1/lookups/study-fields). The education form submits the chosen id
// as field_id — the backend resolves a field by id (its update path ignores a
// free-text field name), and the presented record reports only the field name.
export interface StudyFieldOption {
  id: number;
  name: string;
}

export type AcademicLevel = "student" | "graduate";

// The graduation certificate reported by GET /students/me/academic-status:
// { id, file_name, file_url } on the wire, camelCased for the UI.
export interface AcademicCertificate {
  id: number;
  fileName: string;
  fileUrl: string;
}

// The student's academic status, mapped from GET /students/me/academic-status
// by lib/normalize.ts. academic_status defaults to "student" when there is no
// record, and graduation_date / the certificate are null when not set.
export interface AcademicStatusData {
  academicStatus: "student" | "graduate";
  graduationDate: string | null; // YYYY-MM-DD, null when not set
  graduationCertificate: AcademicCertificate | null;
}

// The student's contact details. Every field is nullable because the backend
// (GET /students/me/contact) reports each one as optional — an unset field is
// null, never an empty string.
export interface ContactInfo {
  email: string | null;
  phone: string | null;
  website: string | null;
  address: string | null;
  // An external portfolio/professional profile (the Upwork-style link). The
  // backend reports this as external_profile.
  profileLink: string | null;
}

// A file the user picked in the browser. previewUrl is a local object URL
// used for thumbnails and revoked as soon as the selection changes. The
// certificate dropzone never uploads it; the avatar uploads the original File
// separately and uses this only for the pre-upload preview.
export interface SelectedFile {
  name: string;
  size: number;
  type: string;
  previewUrl: string | null;
}

// One entry in the skills taxonomy (GET /api/v1/lookups/skills): an active
// skill the student can add to their profile. `id` is the lookup id the
// backend expects in POST/DELETE /students/me/skills bodies.
export interface Skill {
  id: number;
  name: string;
}
