// Public surface for the "listings" feature.
// Two-way discovery: student browse-by-field, company post/edit, admin moderation.
//
// Only export what other parts of the app (routes, other features via
// shared/) are meant to consume. Nothing outside this feature should ever
// import from a deeper path than this file (R8). Features never import
// from each other directly — promote to top-level shared/ on second use (R7).

export { RecommendedListings } from "./student/components/recommended-listings/RecommendedListings";
export { ListingCard } from "./shared/components/listing-card/ListingCard";
export { ModeBadge } from "./shared/components/mode-badge/ModeBadge";
export { PaidBadge } from "./shared/components/paid-badge/PaidBadge";
export { ListingFormContainer } from "./company/components/listing-form/ListingFormContainer";
export { MyListingsListContainer } from "./company/components/my-listings/MyListingsListContainer";
export { BrowseListingsContainer } from "./student/components/browse-listings/BrowseListingsContainer";
export { ListingDetailContainer } from "./student/components/listing-detail/ListingDetailContainer";
export { ModerateListingsTableContainer } from "./admin/components/moderate-listings/ModerateListingsTableContainer";
export { fetchTrainingDetails } from "./student/api";