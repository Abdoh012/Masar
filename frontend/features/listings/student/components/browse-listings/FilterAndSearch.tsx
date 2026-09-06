"use client";

import { useRouter, useSearchParams } from "next/navigation";

import createPageUrl from "@/shared/lib/createPageUrl";

import { BrowseToolbar } from "./BrowseToolbar";
import { FilterSearchField } from "./FilterSearchField";
import { FILTER_LABELS } from "./constants";

// FilterAndSearch: the browse top row (keyword search + saved-only/sort toolbar).
// Reads the active params from the URL and pushes updates via router.push —
// composition only, no data fetching (structure rules §3-4).
export function FilterAndSearch() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const sp = searchParams.toString();

  const searchQuery = searchParams.get("q") ?? "";
  const sort = searchParams.get("sort") ?? "";
  const savedOnly = searchParams.get("saved") === "1";

  const handleSearchChange = (query: string) =>
    router.push(createPageUrl("q", query, sp));

  const handleSortChange = (next: string) =>
    router.push(createPageUrl("sort", next, sp));

  const handleSavedOnlyToggle = () =>
    router.push(createPageUrl("saved", savedOnly ? "" : "1", sp));

  return (
    <div className="my-4 flex flex-col sm:flex-row items-start sm:items-center justify-between">
      <FilterSearchField
        placeholder={FILTER_LABELS.searchPlaceholder}
        value={searchQuery}
        onSearchChange={handleSearchChange}
      />

      <BrowseToolbar
        sort={sort}
        savedOnly={savedOnly}
        onSortChange={handleSortChange}
        onSavedOnlyToggle={handleSavedOnlyToggle}
      />
    </div>
  );
}
