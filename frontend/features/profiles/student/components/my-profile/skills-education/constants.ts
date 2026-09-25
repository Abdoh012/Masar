// Copy for the skills & education section (structure rules §14).

export const SECTION_LABELS = {
  title: "Skills & education",
  description:
    "What you can do and where you studied — the first two things a reviewer looks at.",
} as const;

export const SKILLS_LABELS = {
  title: "Skills",
  description: (catalogSize: number) =>
    `Pick from ${catalogSize} common skills — companies filter candidates by skill.`,
  selectLabel: "Add a skill",
  selectPlaceholder: "Select a skill…",
  allAdded: "That's every skill in the list.",
  remove: (skill: string) => `Remove ${skill}`,
  empty: "No skills yet — add the ones you're confident being interviewed on.",
} as const;

export const EDUCATION_LABELS = {
  title: "Education",
  description:
    "Add every degree or certificate that counts for you. Post-secondary and language certificates both help.",
  add: "Add education",
  emptyTitle: "No education added yet.",
  // A new, still-empty row needs a title before it has a degree to show.
  newEntryTitle: "New entry",
  remove: "Remove entry",
  fields: {
    degree: "Degree",
    institution: "Institution",
    fieldOfStudy: "Field of study",
    startedAt: "Started",
    endedAt: "Ended",
  },
  placeholders: {
    degree: "Bachelor of Science",
    institution: "Cairo University",
    fieldOfStudy: "Computer science",
  },
} as const;
