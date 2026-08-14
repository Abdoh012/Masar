import type { ListingStatus } from "../../../shared/types";
import type { MyListingRow } from "../../types";
// Company-side mock list + tab definition for My Listings (R-5, R-8; structure
// rules §14 — no inline data). MOCK_MY_LISTINGS doubles as the edit-mode
// prefill source (T021) and the Phase 5 render data. UI-only: applicantCount
// and postedDate are display fields derived from real data later (R-2/G9).

export const MOCK_MY_LISTINGS: MyListingRow[] = [
  {
    id: "1",
    companyId: "c-nile",
    companyName: "Nile Valley Consulting",
    field: "Software Engineering",
    specialization: "Spring Boot Engineer Trainee",
    description: "Hands-on backend training with production Java services.",
    mode: "hands_on",
    format: "hybrid",
    hireIntent: true,
    isPaid: true,
    price: 200,
    trialDays: 7,
    status: "published",
    createdAt: "2026-07-28",
    updatedAt: "2026-08-01",
    applicantCount: 12,
    postedDate: "2026-07-28",
  },
  {
    id: "2",
    companyId: "c-nile",
    companyName: "Nile Valley Consulting",
    field: "Software Engineering",
    specialization: "Frontend React Intern",
    description: "Learn the React stack alongside our product team.",
    mode: "observer",
    format: "remote",
    hireIntent: false,
    isPaid: false,
    status: "published",
    createdAt: "2026-08-02",
    updatedAt: "2026-08-02",
    applicantCount: 7,
    postedDate: "2026-08-02",
  },
  {
    id: "3",
    companyId: "c-nile",
    companyName: "Nile Valley Consulting",
    field: "Data Science",
    specialization: "ML Engineering Apprentice",
    description: "Project-based training on real client datasets.",
    mode: "project_based",
    format: "in_person",
    hireIntent: true,
    isPaid: true,
    price: 250,
    trialDays: 14,
    status: "draft",
    createdAt: "2026-08-05",
    updatedAt: "2026-08-05",
    applicantCount: 0,
    postedDate: "2026-08-05",
  },
  {
    id: "4",
    companyId: "c-nile",
    companyName: "Nile Valley Consulting",
    field: "Design",
    specialization: "UI/UX Design Trainee",
    description: "Design sprints with mentorship from senior designers.",
    mode: "hands_on",
    format: "hybrid",
    hireIntent: false,
    isPaid: false,
    status: "closed",
    createdAt: "2026-06-10",
    updatedAt: "2026-07-15",
    applicantCount: 23,
    postedDate: "2026-06-10",
  },
];

// My Listings tabs: All shows every status; Published/Closed filter by
// ListingStatus (FR-008). Draft rows only appear under All for now.
export const MY_LISTINGS_TABS: { value: "all" | ListingStatus; label: string }[] = [
  { value: "all", label: "All" },
  { value: "published", label: "Published" },
  { value: "closed", label: "Closed" },
];

// Row display + action copy (FR-007/009/010/011).
export const ROW_LABELS = {
  applicantCount: (count: number) => `${count} ${count === 1 ? "applicant" : "applicants"}`,
  edit: "Edit",
  close: "Close",
  viewApplicants: "View Applicants",
  closeUnavailable: "Closed",
} as const;

export const LISTING_LIST_LABELS = {
  newListing: "New listing",
  emptyTitle: "No listings here yet",
  emptyMessage: "Listings you publish will appear here.",
} as const;
