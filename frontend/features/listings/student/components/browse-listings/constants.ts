import { LISTING_FORMATS, LISTING_MODES } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";
import type { BrowseListing, BrowseSort, ListingFiltersState } from "../../types";

// Student browse constants (R-8; structure rules §14 — no inline data).
// MOCK_BROWSE_LISTINGS is static/UI-only; the Category filter + card pills
// read fields straight from it, so the list stays the single source for the
// browse grid (backend scoping is deferred to backend integration).

export const MOCK_BROWSE_LISTINGS: BrowseListing[] = [
  {
    id: "36",
    companyId: "c-sawari",
    companyName: "Sawari Digital",
    field: "Software Engineering",
    specialization: "Spring Boot Engineer Trainee",
    description: "Hands-on backend training on real production Java services.",
    skills: ["Java", "Spring Boot"],
    duration: "3 Months",
    mode: "hands_on",
    format: "hybrid",
    hireIntent: true,
    isPaid: true,
    price: 180,
    trialDays: 7,
    status: "published",
    createdAt: "2026-08-01",
    updatedAt: "2026-08-01",
  },
  {
    id: "41",
    companyId: "c-mobica",
    companyName: "Mobica Alexandria",
    field: "Software Engineering",
    specialization: "React Frontend Intern",
    description: "Learn the React stack while shadowing the product team.",
    skills: ["React", "TypeScript"],
    duration: "2 Months",
    saved: true,
    mode: "observer",
    format: "in_person",
    hireIntent: false,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-04",
    updatedAt: "2026-08-04",
  },
  {
    id: "52",
    companyId: "c-startapp",
    companyName: "StartApp Hub",
    field: "Software Engineering",
    specialization: "Quality & Test Engineer Program",
    description: "Project-based QA training across web and mobile products.",
    skills: ["QA", "Test Automation"],
    duration: "4 Months",
    mode: "hands_on",
    format: "remote",
    hireIntent: true,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-06",
    updatedAt: "2026-08-06",
  },
  {
    id: "63",
    companyId: "c-clouditech",
    companyName: "CloudiTech",
    field: "Software Engineering",
    specialization: "DevOps Apprentice",
    description: "Build CI/CD pipelines and infrastructure as code.",
    skills: ["CI/CD", "Docker", "Linux"],
    duration: "6 Months",
    mode: "project_based",
    format: "hybrid",
    hireIntent: true,
    isPaid: true,
    price: 240,
    trialDays: 14,
    status: "published",
    createdAt: "2026-08-08",
    updatedAt: "2026-08-08",
  },
  {
    id: "73",
    companyId: "c-orbit",
    companyName: "Orbit Software",
    field: "Software Engineering",
    specialization: "Frontend Developer Program",
    description: "Ship accessible interfaces with a senior mentor.",
    skills: ["React", "Accessibility"],
    duration: "2 Months",
    saved: true,
    mode: "hands_on",
    format: "remote",
    hireIntent: true,
    isPaid: true,
    price: 200,
    trialDays: 7,
    status: "published",
    createdAt: "2026-08-07",
    updatedAt: "2026-08-07",
  },
  {
    id: "81",
    companyId: "c-greentech",
    companyName: "GreenTech Solutions",
    field: "Data & Analytics",
    specialization: "Data Analysis Training Program",
    description: "Turn raw company data into decisions with dashboards and reports.",
    skills: ["Excel", "SQL"],
    duration: "2 Months",
    saved: true,
    mode: "hands_on",
    format: "hybrid",
    hireIntent: true,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-09",
    updatedAt: "2026-08-09",
  },
  {
    id: "92",
    companyId: "c-creativemind",
    companyName: "Creative Mind Studio",
    field: "Design",
    specialization: "UI/UX Design Workshop",
    description: "A hands-on workshop covering research, wireframes, and design systems.",
    skills: ["Figma", "Prototyping"],
    duration: "1 Month",
    mode: "observer",
    format: "in_person",
    hireIntent: false,
    isPaid: true,
    price: 150,
    trialDays: 7,
    status: "published",
    createdAt: "2026-08-10",
    updatedAt: "2026-08-10",
  },
  {
    id: "104",
    companyId: "c-marketly",
    companyName: "Marketly",
    field: "Marketing",
    specialization: "Digital Marketing Internship",
    description: "Plan and run campaigns across search, social, and email.",
    skills: ["SEO", "Social Media"],
    duration: "3 Months",
    mode: "hands_on",
    format: "remote",
    hireIntent: true,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-11",
    updatedAt: "2026-08-11",
  },
];

// Browse hero copy (reference header band).
export const BROWSE_HERO = {
  eyebrow: "Explore Opportunities",
  title: "Trainings",
  subtitle:
    "Explore training opportunities provided by companies and apply to grow your skills and career.",
};

export const FILTER_LABELS = {
  title: "Filters",
  category: "Category",
  searchPlaceholder: "Search by training title, company, skill, or keyword...",
  clear: "Clear All",
  allCategories: "All Categories",
  trainingType: "Training Type",
  allTypes: "All Types",
  mode: "Mode",
  allModes: "All Modes",
  price: "Price",
};

// Filter option lists (FR-013). Category is derived from the mock data so the
// pills and options can never drift from the grid; mode/format reuse the
// shared lists (R-1); paid is a ternary with a neutral "any" state.
export const FILTER_LISTS = {
  category: [...new Set(MOCK_BROWSE_LISTINGS.map((listing) => listing.field))].map((field) => ({
    value: field,
    label: field,
  })),
  trainingType: [...LISTING_MODES] as { value: ListingMode; label: string }[],
  mode: [...LISTING_FORMATS] as { value: "in_person" | "remote" | "hybrid"; label: string }[],
  price: [
    { value: "any", label: "Any" },
    { value: "free", label: "Free" },
    { value: "paid", label: "Paid" },
  ] as { value: "any" | "free" | "paid"; label: string }[],
};

// No-filter default state (FR-013).
export const DEFAULT_FILTERS: ListingFiltersState = {
  mode: "",
  format: "",
  paid: "any",
  category: "",
  query: "",
  savedOnly: false,
};

export const TOOLBAR_LABELS = {
  countSingular: "training found",
  countPlural: "trainings found",
  savedOnly: "Saved Only",
  sort: "Sort",
};

export const SORT_OPTIONS: { value: BrowseSort; label: string }[] = [

  { value: "default", label: "Default" },
  { value: "newest", label: "Newest First" },
  { value: "oldest", label: "Oldest First" },
  { value: "price_low_to_high", label: "Price: Low to High" },
  { value: "price_high_to_low", label: "Price: High to Low" },
  { value: "duration_short_to_long", label: "Duration: Short to Long" },
  { value: "duration_long_to_short", label: "Duration: Long to Short" },
  
];

export const BROWSE_EMPTY_STATE = {
  title: "No trainings match your filters",
  message: "Try clearing a filter or two to see more options.",
};