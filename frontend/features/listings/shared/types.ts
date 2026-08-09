// Shared types for listings — used across roles within the feature
// (listing-card shared component + recommended-listings student).

export interface Listing {
  id: string;
  title: string;
  companyName: string;
  field: string;
  location?: string;
  mode: string;
  free?: boolean;
}