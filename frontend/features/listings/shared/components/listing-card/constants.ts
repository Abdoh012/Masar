// Shared Listing types/labels — data always lives in the caller (RecommendedListings etc.).
export interface Listing {
  id: string;
  title: string;
  companyName: string;
  field: string;
  location?: string;
  mode: string;
  free?: boolean;
}

// Display labels only for the preset mock modes.
export const LISTING_MODE_LABELS: Record<string, string> = {
  INTERN: "Intern",
  TRAINEE: "Trainee",
  APPRENTICE: "Apprentice",
};