// Shared types for the listings feature — used across every role folder
// (shared components, company, student, admin). One definition per type,
// never redefined per role (FR-021).

export type ListingMode = "observer" | "hands_on" | "project_based";

export type ListingStatus = "draft" | "published" | "closed";

// The core training-opportunity shape, per the architecture's suggested
// attribute set. The full shape is TBD with the backend teammate before
// either side writes listings code (FR-027) — nothing here is a finalized
// API/database contract.
export interface Listing {
  id: string;
  companyId: string;
  field: string;
  specialization: string;
  description?: string;
  mode: ListingMode;
  format: "in_person" | "remote" | "hybrid";
  hireIntent: boolean;
  isPaid: boolean;
  price?: number;
  trialDays?: number;
  status: ListingStatus;
  createdAt: string;
  updatedAt: string;
}

// UI-only display extension used by the shared ListingCard and role rows:
// companyName is resolved from companyId by the data layer once the backend
// exists; skills/duration/saved are display-only card content (skills tags,
// duration line, saved-bookmark state). NOT a backend contract — do not
// treat any of it as part of the TBD shape.
export interface ListingCardData extends Listing {
  companyName: string;
  skills?: string[];
  duration?: string;
  saved?: boolean;
  hasApplied?: boolean;
  companyLogo?: string;
}