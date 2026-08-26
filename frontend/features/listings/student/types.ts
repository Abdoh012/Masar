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
export type BrowseSort =
  | "default"
  | "newest"
  | "oldest"
  | "price_low_to_high"
  | "price_high_to_low"
  | "duration_short_to_long"
  | "duration_long_to_short";
