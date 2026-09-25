// Static data + copy for the /profile page (structure rules §14).
//
// There is no profile API yet, so every value here is placeholder data the page
// renders from and every section edits locally in client state — nothing is
// persisted. The shapes match `../../types` (ISO dates, month-input year-month
// strings) so the mock can be swapped for a fetch without touching a
// component. Section-specific UI copy (labels, hints, button text) lives in
// each section's own constants.ts; this file holds the page header copy and
// the seeded page data.

import type {
  AcademicLevel,
  ContactInfo,
  EducationEntry,
  ProfileIdentity,
  ProfileViewer,
} from "../../types";

// --- Page header copy ---

export const PAGE_HEADER = {
  eyebrow: "Your account",
  title: "My profile",
  description:
    "Keep your details, skills and academic status up to date — this is what companies see when they review your profile.",
} as const;

// --- Identity (the hero card) ---

export const PROFILE_IDENTITY: ProfileIdentity = {
  name: "Nour El-Sayed",
  initials: "NE",
  specialization: "Front-end engineering",
  field: "Computer science",
  institution: "Cairo University, Faculty of Computers & Information",
  memberSince: "2024-09-12",
};

// --- Profile views ---

// The headline count is deliberately larger than the seeded viewer rows: the
// count is the total, the list is only the most recent slice of it.
export const PROFILE_VIEW_COUNT = 24;

export const PROFILE_VIEWERS: ProfileViewer[] = [
  {
    id: 1,
    name: "Mariam Fouad",
    role: "Talent partner · Nile Software",
    initials: "MF",
    viewedAt: "2026-09-26T09:12:00",
  },
  {
    id: 2,
    name: "Ahmed Nasser",
    role: "Engineering manager · Paymob",
    initials: "AN",
    viewedAt: "2026-09-25T18:40:00",
  },
  {
    id: 3,
    name: "Sara Ibrahim",
    role: "People operations · Instabug",
    initials: "SI",
    viewedAt: "2026-09-24T11:05:00",
  },
  {
    id: 4,
    name: "Omar El-Sayed",
    role: "Product designer · Khazna",
    initials: "OE",
    viewedAt: "2026-09-22T15:30:00",
  },
  {
    id: 5,
    name: "Yasmin Adel",
    role: "Data team lead · Fawry",
    initials: "YA",
    viewedAt: "2026-09-19T08:20:00",
  },
  {
    id: 6,
    name: "Karim Mostafa",
    role: "Head of development · Dsquares",
    initials: "KM",
    viewedAt: "2026-09-15T13:45:00",
  },
];

// --- Skills ---

// The predefined list the skill picker filters against. Free text is still
// accepted, so this is a shortcut rather than a whitelist.
export const SKILL_CATALOG = [
  "React",
  "TypeScript",
  "JavaScript",
  "Node.js",
  "Python",
  "Java",
  "C++",
  "SQL",
  "REST APIs",
  "GraphQL",
  "Git",
  "Docker",
  "AWS",
  "PostgreSQL",
  "MongoDB",
  "Figma",
  "UI/UX design",
  "Data analysis",
  "Machine learning",
  "Power BI",
  "Product management",
  "Digital marketing",
  "SEO",
  "Communication",
  "Teamwork",
  "Problem solving",
  "Agile / Scrum",
  "Arabic",
  "English",
] as const;

export const INITIAL_SKILLS: string[] = [
  "React",
  "TypeScript",
  "Figma",
  "Git",
  "Communication",
];

// --- Education ---

export const INITIAL_EDUCATION: EducationEntry[] = [
  {
    id: "education-bachelors",
    degree: "Bachelor of Science",
    institution: "Cairo University",
    fieldOfStudy: "Computer science",
    startedAt: "2022-09",
    endedAt: "2026-06",
  },
  {
    id: "education-diploma",
    degree: "High school",
    institution: "Assiut College",
    fieldOfStudy: "Science — mathematics track",
    startedAt: "2019-09",
    endedAt: "2022-06",
  },
];

// --- Contact info ---

export const INITIAL_CONTACT: ContactInfo = {
  email: "nour.elsayed@example.com",
  phone: "+20 100 123 4567",
  website: "nourelsayed.dev",
  address: "Cairo, Egypt",
  profileLink: "https://linkedin.com/in/nour-elsayed",
};

// --- Academic status ---

// Seeded on "graduate" so the graduate-only fields (graduation date + the
// certificate dropzone) are visible when reviewing the page.
export const INITIAL_ACADEMIC_LEVEL: AcademicLevel = "graduate";

export const INITIAL_GRADUATION_DATE = "2026-06";
