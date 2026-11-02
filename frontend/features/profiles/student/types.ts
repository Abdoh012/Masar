// Role-level types for the profiles student (structure rules §14).

/** The identity block at the top of the student dashboard: the student's own
 *  name, academic line and the backend-computed profile completion. Fed by the
 *  dashboard orchestrator from GET /students/dashboard (`student`), so nothing
 *  here is a mock. */
export interface ProfileHeaderStudent {
  name: string;
  /** Study field name; null when the student hasn't set one. */
  field: string | null;
  /** "Faculty · University" as one line, or null when neither is set. */
  studies: string | null;
  initials: string;
  /** Profile completion, 0..100, exactly as the backend computed it
   *  (`student.profile_completion.percentage`). Never derived on the client. */
  completion: number;
}

// The identity block at the top of /profile. There is no profile read endpoint
// yet, so the page renders this from constants — but the shape is the one the
// backend will return (ISO dates, optional avatar URL) so the mock can be
// swapped for a fetch without touching the components.
export interface ProfileIdentity {
  name: string;
  initials: string;
  specialization: string;
  field: string;
  institution: string;
  // ISO date (YYYY-MM-DD) the profile was created — rendered as "Member since".
  memberSince: string;
  avatarUrl?: string;
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

// One entry in the LinkedIn-style education list. Month inputs submit
// year-month strings, so both dates are YYYY-MM.
export interface EducationEntry {
  id: string;
  degree: string;
  institution: string;
  fieldOfStudy: string;
  startedAt: string;
  endedAt: string;
}

export type AcademicLevel = "student" | "graduate";

export interface ContactInfo {
  email: string;
  phone: string;
  website: string;
  address: string;
  // An external portfolio/professional profile (the Upwork-style link).
  profileLink: string;
}

// A file the user picked in the browser. There is no upload yet — the bytes
// never leave the page; `previewUrl` is a local object URL used for thumbnails
// and is revoked as soon as the selection changes.
export interface SelectedFile {
  name: string;
  size: number;
  type: string;
  previewUrl: string | null;
}
