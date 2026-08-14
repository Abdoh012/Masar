import type { ListingStatus } from "../../../shared/types";
import type { AdminListingRow } from "../../types";

// Admin moderation constants (R-8; structure rules §14 — no inline data).
// Platform-wide mock rows across companies for the UI-only phase (FR-018).
// company/status filter option lists and disable copy live here too.

export const MOCK_ADMIN_LISTINGS: AdminListingRow[] = [
  {
    id: "36",
    companyId: "c-sawari",
    companyName: "Sawari Digital",
    companyDisplay: "Sawari Digital",
    field: "Software Engineering",
    specialization: "Spring Boot Engineer Trainee",
    mode: "hands_on",
    format: "hybrid",
    hireIntent: true,
    isPaid: true,
    price: 180,
    trialDays: 7,
    status: "published",
    createdAt: "2026-08-01",
    updatedAt: "2026-08-01",
    disabled: false,
  },
  {
    id: "41",
    companyId: "c-mobica",
    companyName: "Mobica Alexandria",
    companyDisplay: "Mobica Alexandria",
    field: "Software Engineering",
    specialization: "React Frontend Intern",
    mode: "observer",
    format: "in_person",
    hireIntent: false,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-04",
    updatedAt: "2026-08-04",
    disabled: false,
  },
  {
    id: "52",
    companyId: "c-startapp",
    companyName: "StartApp Hub",
    companyDisplay: "StartApp Hub",
    field: "Software Engineering",
    specialization: "Quality & Test Engineer Program",
    mode: "hands_on",
    format: "remote",
    hireIntent: true,
    isPaid: false,
    status: "closed",
    createdAt: "2026-08-06",
    updatedAt: "2026-08-06",
    disabled: false,
  },
  {
    id: "63",
    companyId: "c-clouditech",
    companyName: "CloudiTech",
    companyDisplay: "CloudiTech",
    field: "Software Engineering",
    specialization: "DevOps Apprentice",
    mode: "project_based",
    format: "hybrid",
    hireIntent: true,
    isPaid: true,
    price: 240,
    trialDays: 14,
    status: "published",
    createdAt: "2026-08-08",
    updatedAt: "2026-08-08",
    disabled: false,
  },
  {
    id: "70",
    companyId: "c-brightlocal",
    companyName: "BrightLocal Media",
    companyDisplay: "BrightLocal Media",
    field: "Marketing",
    specialization: "Digital Marketing Trainee",
    mode: "observer",
    format: "remote",
    hireIntent: false,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-02",
    updatedAt: "2026-08-02",
    disabled: true,
  },
];

// Company + status filter option lists (FR-018). Derived from the mock rows
// so a filter can never reference a company that doesn't exist. Shared
// { value, label } shape so both feed FilterSelect directly.
export const ADMIN_FILTER_LISTS = {
  company: [...new Set(MOCK_ADMIN_LISTINGS.map((row) => row.companyDisplay))].map(
    (company) => ({ value: company, label: company }),
  ),
  status: [
    { value: "published", label: "Published" },
    { value: "closed", label: "Closed" },
  ] as { value: ListingStatus; label: string }[],
};

export const MODERATION_LABELS = {
  companyFilter: "Company",
  statusFilter: "Status",
  reset: "Reset filters",
  disable: "Disable",
  disabled: "Disabled",
  emptyTitle: "No listings match your filters",
  emptyMessage: "Try adjusting the company or status filter.",
};
