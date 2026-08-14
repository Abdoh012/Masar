import type { ListingCardData } from "../../../shared/types";

// Listing detail constants (R-8; structure rules §14 — no inline data).
// Mock detail listings keyed by id for the UI-only phase (FR-015/017). The
// already-applied marker and apply copy are UI-only placeholders until the
// applications backend exists — nothing here is a submission contract.

export const MOCK_DETAIL_LISTINGS: Record<string, ListingCardData> = {
  "36": {
    id: "36",
    companyId: "c-sawari",
    companyName: "Sawari Digital",
    field: "Software Engineering",
    specialization: "Spring Boot Engineer Trainee",
    description:
      "Hands-on backend training on real production Java services. You'll pair with senior engineers, ship small features behind feature flags, and learn Spring Boot, PostgreSQL, and observability in a live SaaS environment.",
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
  "41": {
    id: "41",
    companyId: "c-mobica",
    companyName: "Mobica Alexandria",
    field: "Software Engineering",
    specialization: "React Frontend Intern",
    description:
      "Learn the React stack while shadowing the product team. Contribute to component libraries, learn TypeScript and testing, and get direct code review from frontend leads.",
    mode: "observer",
    format: "in_person",
    hireIntent: false,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-04",
    updatedAt: "2026-08-04",
  },
  "52": {
    id: "52",
    companyId: "c-startapp",
    companyName: "StartApp Hub",
    field: "Software Engineering",
    specialization: "Quality & Test Engineer Program",
    description:
      "Project-based QA training across web and mobile products. Write test plans, automate E2E suites, and learn how quality gates protect every release.",
    mode: "hands_on",
    format: "remote",
    hireIntent: true,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-06",
    updatedAt: "2026-08-06",
  },
  "63": {
    id: "63",
    companyId: "c-clouditech",
    companyName: "CloudiTech",
    field: "Software Engineering",
    specialization: "DevOps Apprentice",
    description:
      "Build CI/CD pipelines and infrastructure as code. Work on Kubernetes, Terraform, and monitoring while owning real environments under mentorship.",
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
  "73": {
    id: "73",
    companyId: "c-orbit",
    companyName: "Orbit Software",
    field: "Software Engineering",
    specialization: "Frontend Developer Program",
    description:
      "Ship accessible interfaces with a senior mentor. Cover React, design systems, and performance, ending with a portfolio project you own.",
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
};

// Listings the mock student has already applied to (FR-017, UI-only marker).
export const MOCK_APPLIED_LISTING_IDS: string[] = ["41"];

export const APPLY_COPY = {
  title: "Apply to this training",
  button: "Apply now",
  applied: "You already applied",
  appliedMessage: "Your application is in. The company will reach out if you're shortlisted.",
  noteLabel: "Add a note",
  notePlaceholder: "Optional — tell the company why you're a good fit",
  success: "Application sent",
  successMessage: "Your application was recorded for this session.",
};

export const DETAIL_COPY = {
  backToBrowse: "Back to browse",
};
