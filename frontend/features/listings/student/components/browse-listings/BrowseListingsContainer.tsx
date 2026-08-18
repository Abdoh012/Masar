import { ListingCard } from "../../../shared/components/listing-card/ListingCard";

import { BrowseHero } from "./BrowseHero";
import { BrowseFilterBar } from "./BrowseFilterBar";
import { MOCK_BROWSE_LISTINGS } from "./constants";
import FilterAndSearch from "./FilterAndSearch";

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
