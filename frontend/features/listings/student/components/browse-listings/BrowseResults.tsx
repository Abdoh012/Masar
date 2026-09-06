import { fetchBrowseListings } from "../../api";
import type { BrowseParams } from "../../lib/browse-params";
import { ListingCard } from "@/features/listings/shared/components/listing-card/ListingCard";
import { Pagination } from "@/features/listings/shared/components/pagination/Pagination";

import { BrowseEmptyState } from "./BrowseEmptyState";

interface BrowseResultsProps {
  params: BrowseParams;
  limit: number;
}

// BrowseResults: fetches the trainings for the active browse params and renders
// the card grid (or the empty state) plus pagination. The orchestrator suspends
// on this component alone, so only the grid area shows a loader while fetching;
// the hero and filter rows render immediately.
export async function BrowseResults({ params, limit }: BrowseResultsProps) {
  const { items, pagination } = await fetchBrowseListings(params, limit);

  if (items.length === 0) {
    return <BrowseEmptyState />;
  }

  return (
    <>
      <div className="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
        {items.map((listing) => (
          <ListingCard key={listing.id} {...listing} />
        ))}
      </div>

      <Pagination pagination={pagination} />
    </>
  );
}