// MyApplicationsPage: async server orchestrator for the /applications page
// (FR-001/002/007). Fetches ONLY the active tab's cards from the backend
// (student/api.ts) — one query per render, no cross-tab fetching — so the
// active view's list is exact and server-rendered; the active tab comes from
// the ?tab= search param (parseApplicationsTab). Only the All view carries a
// count: the header chip (and the All tab badge, same number) shows /all's
// total while All is active; non-All tabs are label-only because their counts
// aren't fetched. Throws on failure so the route-level error.tsx renders.
// Owns no markup beyond composition — tabs, cards, the empty state, and the
// per-card withdraw flow are all dedicated leaves.
import { APPLICATIONS_TITLE, EMPTY_STATES, TABS } from "./constants";
import { fetchApplicationsTab } from "../../api";
import { parseApplicationsTab } from "../../lib/applications-params";
import { normalizeApplicationsResponse } from "../../lib/normalize";
import { ApplicationCard } from "./ApplicationCard";
import { ApplicationStatusTabs } from "./ApplicationStatusTabs";
import { EmptyApplicationsState } from "./EmptyApplicationsState";

interface MyApplicationsPageProps {
  searchParams: Record<string, string | string[] | undefined>;
}

export async function MyApplicationsPage({
  searchParams,
}: MyApplicationsPageProps) {
  const activeTab = parseApplicationsTab(searchParams.tab);

  // Single no-store fetch for the active tab only (per-student, must be fresh
  // after a withdraw or staff-side change).
  const response = await fetchApplicationsTab(activeTab);
  if (!response.success) {
    throw new Error(
      response.error ?? `Failed to load ${activeTab} applications.`,
    );
  }
  const { items, total } = normalizeApplicationsResponse(response.data);

  // Only /all's total is in the response, and only when All is the active
  // view — so the All tab is the only badge, shown when its data is loaded.
  const statusTabs = TABS.map((tab) => ({
    ...tab,
    count: tab.value === "all" && activeTab === "all" ? total : undefined,
  }));

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="font-sans text-2xl font-semibold text-primary-text">
          {APPLICATIONS_TITLE}
        </h1>
        {activeTab === "all" ? (
          <span className="rounded-full bg-primary-tint px-3 py-1 text-sm font-medium text-primary-text">
            {total}
          </span>
        ) : null}
      </div>

      <ApplicationStatusTabs tabs={statusTabs} />

      {items.length > 0 ? (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {items.map((application) => (
            <ApplicationCard key={application.id} application={application} />
          ))}
        </div>
      ) : (
        <EmptyApplicationsState {...EMPTY_STATES[activeTab]} />
      )}
    </div>
  );
}