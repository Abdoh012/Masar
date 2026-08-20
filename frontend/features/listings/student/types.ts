import type { ListingCardData } from "../shared/types";

// Student-side UI-only display types (R-2). BrowseListing is the card data
// the student browse grid renders; the already-applied marker is a simple
// UI-only flag used by the detail screen (FR-017) — never part of the TBD
// backend Listing shape.

export type BrowseListing = ListingCardData;

// Student browse filter state (FR-013), used by the browse orchestrator and
// the ListingFilters leaf. The paid control is a ternary with a neutral "any"
// state; mode/format/category are "no filter" when empty; query is the
// sidebar keyword search; savedOnly is the toolbar's Saved-Only toggle.
export interface ListingFiltersState {
  mode: string;
  format: string;
  paid: "any" | "free" | "paid";
  category: string;
  query: string;
  savedOnly: boolean;
}

// Student browse toolbar sort (UI-only, FR-014) — newest by default.
export type BrowseSort = "newest" | "oldest";

export interface AlreadyAppliedMarker {
  listingId: string;
  alreadyApplied: true;
}