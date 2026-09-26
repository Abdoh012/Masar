// MyApplicationsPage: async server orchestrator for the /applications page
// (FR-001/002/007). Fetches ONLY the active tab's page of cards from the
// backend (student/api.ts) — one query per render, no cross-tab fetching — so
// the active view is exact and server-rendered; the active tab comes from the
// ?tab= search param (parseApplicationsTab) and the page from ?page=
// (parseApplicationsPage, hitting the same shared Pagination component the
// Trainings tab uses). All six tabs go through the same fetch: the status tabs
// page server-side at APPLICATIONS_PAGE_LIMIT (20); only the All view carries a
// count: the header chip (and the All tab badge, same number) reads /all's
// pagination.total, which is the full count regardless of the page. Ended
// Applications is the one different dataset (/applications/certificates — an
// unpaginated bare array of issued certificates), so only its response handling
// differs: it normalizes to ended cards and the section container
// (ended-applications/EndedApplicationsContainer) slices pages client-side.
// Throws on failure so the route-level error.tsx renders. Owns no markup beyond
// composition — the header band, tabs, cards, the empty state, the pager, and
// the per-card withdraw flow are all dedicated leaves.
import { FileText } from "lucide-react";

import { PageHeader } from "@/shared/components/page-header/PageHeader";
import { Pagination } from "@/shared/components/pagination/Pagination";

import { APPLICATIONS_HEADER, EMPTY_STATES, TABS } from "./constants";
import { fetchApplicationsTab } from "../../api";
import {
  parseApplicationsPage,
  parseApplicationsTab,
} from "../../lib/applications-params";
import {
  normalizeApplicationsResponse,
  normalizeEndedApplications,
} from "../../lib/normalize";
import { ApplicationCard } from "./ApplicationCard";
import { ApplicationStatusTabs } from "./ApplicationStatusTabs";
import { EmptyApplicationsState } from "./EmptyApplicationsState";
import { EndedApplicationsContainer } from "./ended-applications/EndedApplicationsContainer";
import type { EndedApplication, MyApplication } from "../../types";

interface MyApplicationsPageProps {
  searchParams: Record<string, string | string[] | undefined>;
}

export async function MyApplicationsPage({
  searchParams,
}: MyApplicationsPageProps) {
  const activeTab = parseApplicationsTab(searchParams.tab);
  const page = parseApplicationsPage(searchParams.page);
  const isEndedTab = activeTab === "ended";

  // Single no-store fetch for the active tab's page only (per-student, must be
  // fresh after a withdraw or staff-side change). Same call for every tab —
  // fetchApplicationsTab resolves the endpoint from the tab.
  const response = await fetchApplicationsTab(activeTab, page);
  if (!response.success) {
    throw new Error(
      response.error ?? `Failed to load ${activeTab} applications.`,
    );
  }

  // Only the status tabs return the { items, pagination } envelope; the ended
  // tab's endpoint returns a bare array, normalized here and paged by its own
  // container.
  let items: MyApplication[] = [];
  let endedItems: EndedApplication[] = [];
  let total = 0;
  let pagination = { current_page: 1, total_pages: 0 };
  if (isEndedTab) {
    endedItems = normalizeEndedApplications(response.data);
  } else {
    const normalized = normalizeApplicationsResponse(response.data);
    ({ items, total } = normalized);
    pagination = normalized.pagination;
  }

  // Only /all's total is in the response, and only when All is the active
  // view — so the All tab is the only badge, shown when its data is loaded.
  const statusTabs = TABS.map((tab) => ({
    ...tab,
    count: tab.value === "all" && activeTab === "all" ? total : undefined,
  }));

  return (
    <div className="space-y-6">
      <PageHeader
        {...APPLICATIONS_HEADER}
        icon={<FileText className="size-6" />}
        actions={
          activeTab === "all" ? (
            <span className="rounded-full bg-primary-tint px-3 py-1 text-sm font-medium text-primary-text">
              {total}
            </span>
          ) : undefined
        }
      />

      <ApplicationStatusTabs tabs={statusTabs} />

      {isEndedTab ? (
        <EndedApplicationsContainer items={endedItems} page={page} />
      ) : items.length > 0 ? (
        <>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {items.map((application) => (
              <ApplicationCard key={application.id} application={application} />
            ))}
          </div>
          <Pagination pagination={pagination} />
        </>
      ) : (
        <EmptyApplicationsState {...EMPTY_STATES[activeTab]} />
      )}
    </div>
  );
}