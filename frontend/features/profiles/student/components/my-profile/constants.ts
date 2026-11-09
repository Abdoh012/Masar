// Static data + copy for the /profile page (structure rules §14).
//
// The identity hero is fully backend-wired (read + mutation live in the
// feature's api.ts / actions.ts), so it has no seeded data here. Every other
// text value is still placeholder data the page renders from, and every section
// edits locally in client state — nothing else is persisted. Section-specific
// UI copy (labels, hints, button text) lives in each section's own constants.ts;
// this file holds the seeded page data.
//
// The contact-info section is now backend-wired (GET /students/me/contact +
// PUT /students/me/contact), so INITIAL_CONTACT has been removed from here.

import type {
  AcademicLevel,
  EducationEntry,
} from "../../types";

// --- Education ---

export const INITIAL_EDUCATION: EducationEntry[] = [
  {
    id: 1,
    degreeId: null,
    degree: "Bachelor of Science",
    university: "Cairo University",
    faculty: null,
    fieldOfStudy: "Computer science",
    specialization: null,
    studyMode: null,
    startYear: 2022,
    endYear: 2026,
    grade: null,
    isCurrent: false,
    createdAt: null,
    updatedAt: null,
  },
  {
    id: 2,
    degreeId: null,
    degree: "High school",
    university: "Assiut College",
    faculty: null,
    fieldOfStudy: "Science — mathematics track",
    specialization: null,
    studyMode: null,
    startYear: 2019,
    endYear: 2022,
    grade: null,
    isCurrent: false,
    createdAt: null,
    updatedAt: null,
  },
];

// --- Academic status ---

// Seeded on "graduate" so the graduate-only fields (graduation date + the
// certificate dropzone) are visible when reviewing the page.
export const INITIAL_ACADEMIC_LEVEL: AcademicLevel = "graduate";

export const INITIAL_GRADUATION_DATE = "2026-06";
