import type { ListingMode, ListingStatus } from "../types";

// Single source of truth for the mode / format / status option lists and
// display labels, consumed by the company form, the student filters, the
// shared badges, and the role rows (R-1).

export const LISTING_MODES: { value: ListingMode; label: string }[] = [
  { value: "observer", label: "Observer" },
  { value: "hands_on", label: "Hands-on" },
  { value: "project_based", label: "Project-based" },
];

export const LISTING_FORMATS: { value: "in_person" | "remote" | "hybrid"; label: string }[] = [
  { value: "in_person", label: "In-person" },
  { value: "remote", label: "Remote" },
  { value: "hybrid", label: "Hybrid" },
];

// Platform minimum for paid listings' trial period (FR-003).
export const TRIAL_MIN_DAYS = 7;

export const MODE_LABELS: Record<ListingMode, string> = {
  observer: "Observer",
  hands_on: "Hands-on",
  project_based: "Project-based",
};

export const FORMAT_LABELS: Record<"in_person" | "remote" | "hybrid", string> = {
  in_person: "In-person",
  remote: "Remote",
  hybrid: "Hybrid",
};

export const STATUS_LABELS: Record<ListingStatus, string> = {
  draft: "Draft",
  published: "Published",
  closed: "Closed",
};