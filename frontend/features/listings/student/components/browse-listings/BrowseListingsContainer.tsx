import { Suspense } from "react";

import { parseBrowseParams } from "../../lib/browse-params";

import { BrowseFilterBar } from "./BrowseFilterBar";
import { BrowseGridSkeleton } from "./BrowseGridSkeleton";
import { BrowseHero } from "./BrowseHero";
import { BrowseResults } from "./BrowseResults";
import { BROWSE_PAGE_LIMIT } from "./constants";
import { FilterAndSearch } from "./FilterAndSearch";
import { GridErrorBoundary } from "./GridErrorBoundary";

interface BrowseListingsContainerProps {
  searchParams: Record<string, string | string[] | undefined>;
}

// BrowseListingsContainer: server orchestrator for the browse page. Composes the
// hero, filter rows and the card grid. Only the grid suspends (Suspense-scoped
// to BrowseResults), so the hero/filters render immediately while the cards
// stream in with a skeleton. Interactivity stays in the client leaves; save/
// unsave revalidates these routes so the server re-renders fresh.
export function BrowseListingsContainer({
  searchParams,
}: BrowseListingsContainerProps) {
  const params = parseBrowseParams(searchParams);

  return (
    <div>
      <BrowseHero />

      <div className="px-10">
        <FilterAndSearch />
        <BrowseFilterBar />

        <Suspense fallback={<BrowseGridSkeleton />}>
          <GridErrorBoundary>
            <BrowseResults params={params} limit={BROWSE_PAGE_LIMIT} />
          </GridErrorBoundary>
        </Suspense>
      </div>
    </div>
  );
}