import { GraduationCap } from "lucide-react";
import { Suspense } from "react";

import { PageHeader } from "@/shared/components/page-header/PageHeader";

import { parseBrowseParams } from "../../lib/browse-params";

import { BrowseFilterBar } from "./BrowseFilterBar";
import { BrowseGridSkeleton } from "./BrowseGridSkeleton";
import { BrowseResults } from "./BrowseResults";
import { BROWSE_HERO, BROWSE_PAGE_LIMIT } from "./constants";
import { FilterAndSearch } from "./FilterAndSearch";

interface BrowseListingsContainerProps {
  searchParams: Record<string, string | string[] | undefined>;
}

// BrowseListingsContainer: server orchestrator for the browse page. Composes the
// shared header band, filter rows and the card grid. Only the grid suspends
// (Suspense-scoped to BrowseResults), so the header/filters render immediately
// while the cards stream in with a skeleton. Interactivity stays in the client
// leaves; save/unsave revalidates these routes so the server re-renders fresh.
export function BrowseListingsContainer({
  searchParams,
}: BrowseListingsContainerProps) {
  const params = parseBrowseParams(searchParams);

  return (
    <div className="space-y-6">
      <PageHeader {...BROWSE_HERO} icon={<GraduationCap className="size-6" />} />

      <div>
        <FilterAndSearch />
        <BrowseFilterBar />

        <Suspense fallback={<BrowseGridSkeleton />}>
            <BrowseResults params={params} limit={BROWSE_PAGE_LIMIT} />
        </Suspense>
      </div>
    </div>
  );
}