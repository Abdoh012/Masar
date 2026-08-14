import type { ListingCardData, ListingStatus } from "../shared/types";

// Admin-side UI-only display type for the moderation table (FR-018). Extends
// the shared card data with a disabled flag — a local UI state this phase
// (the disable action is a session-local toggle, FR-019). NOT part of the TBD
// backend Listing shape (R-2).

export interface AdminListingRow extends ListingCardData {
  companyDisplay: string;
  status: ListingStatus;
  disabled: boolean;
}
