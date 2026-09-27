import type { ListingCardData, ListingMode } from "../shared/types";

// Student browse toolbar sort (UI-only, FR-014) — newest by default.
export type BrowseSort =
  | "default"
  | "newest"
  | "oldest"
  | "price_asc"
  | "price_desc"
  | "duration_asc"
  | "duration_desc";

// A training recommended on the student dashboard. Deliberately not
// `ListingCardData`: this is the compact card the dashboard endpoint's
// `recommended_trainings` presenter returns, which carries neither a company
// id, a field, a hire intent nor a status, and reports the participation mode
// and delivery format under its own names (`mode` and `format` here, matching
// the browse grid's vocabulary so the shared ModeBadge and CardMeta can be
// reused unchanged).
export interface RecommendedTraining {
  id: number;
  title: string;
  companyName: string | null;
  mode: ListingMode;
  format: "in_person" | "remote" | "hybrid";
  isPaid: boolean;
  price: number | null;
  trialDays: number | null;
  specializationName: string | null;
  /** ISO-8601 with an explicit offset, as the presenter formats it. Optional
   *  because the presenter would send null rather than a substitute date, and
   *  a missing one drops the row's "Posted" line instead of inventing a date. */
  createdAt?: string;
  /** The last moment an application may be submitted. Genuinely null when the
   *  training has no deadline — never substituted from the start/end dates. */
  applicationDeadline: string | null;
  /** Whole days the training runs for, or null when its dates are incomplete. */
  durationDays: number | null;
}

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