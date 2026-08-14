import type { ListingCardData } from "../shared/types";

// Role-level UI-only display type for My Listings rows (FR-007).
// applicantCount and postedDate are UI-only display fields — NOT part of the
// TBD backend Listing shape (R-2, G9). postedDate derives from createdAt in
// real data; mock constants supply it directly this phase.
export interface MyListingRow extends ListingCardData {
  applicantCount: number;
  postedDate: string;
}