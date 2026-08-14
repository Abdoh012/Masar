import type { ListingCardData } from "../shared/types";

// Student-side UI-only display types (R-2). BrowseListing is the card data
// the student browse grid renders; the already-applied marker is a simple
// UI-only flag used by the detail screen (FR-017) — never part of the TBD
// backend Listing shape.

export type BrowseListing = ListingCardData;

export interface AlreadyAppliedMarker {
  listingId: string;
  alreadyApplied: true;
}