// EndedApplicationsContainer: async server orchestrator for the Ended
// Applications tab of the My Applications page. Fetches the completed-trainings
// dataset — GET /applications/certificates, the issued-certificates view — via
// student/api.ts, normalizes it to EndedApplication cards, and composes the
// card grid or the section's empty state. Throws on failure so the route-level
// error.tsx renders. The endpoint returns an unpaginated bare array (no
// pagination envelope), so unlike the status tabs this container pages
// client-side: it slices the fetched set into APPLICATIONS_PAGE_LIMIT (20)
// cards per page and renders the same shared Pagination control for the
// in-range page — page stays URL-driven (?page=) and clamped server-side so a
// stale/out-of-range page never shows an empty grid. Composition only — the
// card and dialog live in their own leaves, and the empty state reuses the
// shared EmptyApplicationsState.
import { fetchEndedApplications, APPLICATIONS_PAGE_LIMIT } from "../../../api";
import { normalizeEndedApplications } from "../../../lib/normalize";
import { Pagination } from "@/shared/components/pagination/Pagination";
import { EmptyApplicationsState } from "../EmptyApplicationsState";
import { EndedApplicationCard } from "./EndedApplicationCard";
import { ENDED_EMPTY_STATE } from "./constants";

interface EndedApplicationsContainerProps {
  page: number;
}

export async function EndedApplicationsContainer({
  page,
}: EndedApplicationsContainerProps) {
  const response = await fetchEndedApplications();
  if (!response.success) {
    throw new Error(response.error ?? "Failed to load ended applications.");
  }

  const items = normalizeEndedApplications(response.data);

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