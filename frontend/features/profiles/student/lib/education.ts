import type { EducationEntry } from "../types";

// A blank row for the "Add education" action. The id is a placeholder (new
// records receive real ids from the backend on create); every other field is
// null/false so the row renders empty until filled. This mirrors the backend's
// EducationEntry shape so the seeded mock and a freshly added row share it.
export function createEmptyEducationEntry(): EducationEntry {
  return {
    id: 0,
    degreeId: null,
    degree: null,
    university: null,
    faculty: null,
    fieldOfStudy: null,
    specialization: null,
    studyMode: null,
    startYear: null,
    endYear: null,
    grade: null,
    isCurrent: false,
    createdAt: null,
    updatedAt: null,
  };
}
