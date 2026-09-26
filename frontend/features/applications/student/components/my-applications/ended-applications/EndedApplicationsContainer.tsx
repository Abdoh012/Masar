// EndedApplicationsContainer: server orchestrator for the Ended Applications tab
// of the My Applications page. The dataset (GET /applications/certificates, the
// issued-certificates view) is fetched by MyApplicationsPage through the same
// fetchApplicationsTab call every tab uses, with the active tab value ("ended")
// resolving the endpoint; the page hands the already-normalized cards down here
// and this container owns the tab's own view logic. Unlike the status tabs that
// endpoint is not paginated (it ignores ?page=/?limit= and returns the whole set
// as a bare array), so it pages client-side: it slices the received set into
// APPLICATIONS_PAGE_LIMIT (20) cards per page and renders the same shared
// Pagination control for the in-range page — page stays URL-driven (?page=) and
// clamped so a stale/out-of-range page never shows an empty grid. Composition
// only — the card and dialog live in their own leaves, and the empty state
// reuses the shared EmptyApplicationsState.
import { APPLICATIONS_PAGE_LIMIT } from "../../../api";
import { Pagination } from "@/shared/components/pagination/Pagination";
import { EmptyApplicationsState } from "../EmptyApplicationsState";
import { EndedApplicationCard } from "./EndedApplicationCard";
import { ENDED_EMPTY_STATE } from "./constants";
import type { EndedApplication } from "../../../types";

interface EndedApplicationsContainerProps {
  items: EndedApplication[];
  page: number;
}

export function EndedApplicationsContainer({
  items,
  page,
}: EndedApplicationsContainerProps) {
  if (items.length === 0) {
    return <EmptyApplicationsState {...ENDED_EMPTY_STATE} />;
  }

  const totalPages = Math.ceil(items.length / APPLICATIONS_PAGE_LIMIT);
  // Clamp to a real page — the API here serves the whole list in one shot, so
  // a stale ?page= past the end must not surface as a misleading empty grid.
  const safePage = Math.min(page, totalPages);
  const offset = (safePage - 1) * APPLICATIONS_PAGE_LIMIT;
  const pageItems = items.slice(offset, offset + APPLICATIONS_PAGE_LIMIT);

  return (
    <>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {pageItems.map((ended) => (
          <EndedApplicationCard key={ended.id} ended={ended} />
        ))}
      </div>

      <Pagination
        pagination={{ current_page: safePage, total_pages: totalPages }}
      />
    </>
  );
}
