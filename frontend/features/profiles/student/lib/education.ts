import type { EducationEntry } from "../types";

// A blank row for the "Add education" action. Ids are generated here (not in
// the container) so every education entry has exactly one construction path —
// the seeded mock and a freshly added one share the same shape.
export function createEmptyEducationEntry(): EducationEntry {
  return {
    id: crypto.randomUUID(),
    degree: "",
    institution: "",
    fieldOfStudy: "",
    startedAt: "",
    endedAt: "",
  };
}
