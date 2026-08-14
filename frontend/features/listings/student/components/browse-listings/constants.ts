import { LISTING_FORMATS, LISTING_MODES } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";
import type { BrowseListing } from "../../types";

// Student browse constants (R-8; structure rules §14 — no inline data).
// MOCK_BROWSE_LISTINGS is pre-scoped to the student's field ("Software
// Engineering") — the server-side auto-scope (architecture §1) is deferred to
// backend integration; this mock stands in for that scoped result. UI-only.

export const MOCK_BROWSE_LISTINGS: BrowseListing[] = [
  {
    id: "36",
    companyId: "c-sawari",
    companyName: "Sawari Digital",
    field: "Software Engineering",
    specialization: "Spring Boot Engineer Trainee",
    description: "Hands-on backend training on real production Java services.",
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
];

// Filter option lists (FR-013). Reused directly so the form and filters can
// never drift from each other (R-1). "paid" is a ternary control with a
// neutral "Any" state.
export const FILTER_LISTS = {
  mode: [...LISTING_MODES] as { value: ListingMode; label: string }[],
  format: [...LISTING_FORMATS] as { value: "in_person" | "remote" | "hybrid"; label: string }[],
  paid: [
    { value: "any", label: "Any" },
    { value: "free", label: "Free" },
    { value: "paid", label: "Paid" },
  ] as { value: "any" | "free" | "paid"; label: string }[],
};

export const FILTER_LABELS = {
  mode: "Mode",
  format: "Format",
  paid: "Price",
  reset: "Reset filters",
  title: "Filters",
};

export const BROWSE_EMPTY_STATE = {
  title: "No trainings match your filters",
  message: "Try clearing a filter or two to see more options.",
};
