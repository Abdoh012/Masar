import { ListingCard } from "../../../shared/components/listing-card/ListingCard";

import { BrowseHero } from "./BrowseHero";
import { BrowseFilterBar } from "./BrowseFilterBar";
import { MOCK_BROWSE_LISTINGS } from "./constants";
import FilterAndSearch from "./FilterAndSearch";

// Student browse orchestrator (FR-012/014). Composes the hero band, the
// search/toolbar row, and the horizontal filter bar — the filter controls are
// presentational for now (client filter logic returns with backend
// integration), then renders the full mock grid through the shared
// ListingCard. No fetching, no backend (R-8).
export function BrowseListingsContainer() {
  return (
    <div>
      <BrowseHero />

      <div className="px-10">
        <FilterAndSearch />
        <BrowseFilterBar />

        <div className="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
          {MOCK_BROWSE_LISTINGS.map((listing) => (
            <ListingCard key={listing.id} {...listing} />
          ))}
        </div>
      </div>
    </div>
  );
}
