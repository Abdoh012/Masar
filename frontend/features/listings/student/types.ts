import type { ListingCardData } from "../shared/types";

// Student browse toolbar sort (UI-only, FR-014) — newest by default.
export type BrowseSort =
  | "default"
  | "newest"
  | "oldest"
  | "price_asc"
  | "price_desc"
  | "duration_asc"
  | "duration_desc";

// Application status as reported by the training detail response's
// `application.status` (the backend maps submitted → "pending").
export type ApplicationStatus =
  | "pending"
  | "accepted"
  | "rejected"
  | "withdrawn";

// Detail-page enrichment of the shared card shape: applicationStatus is only
// present after the student has an application on the training.
export interface ListingDetail extends ListingCardData {
  applicationStatus?: ApplicationStatus;
}