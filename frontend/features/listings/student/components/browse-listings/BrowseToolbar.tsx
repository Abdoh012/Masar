"use client";

import { Bookmark } from "lucide-react";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";

import { SORT_OPTIONS, TOOLBAR_LABELS } from "./constants";

// BrowseToolbar: the Saved-Only toggle + sort dropdown in the browse top bar.
// Presentational for now — static values, no wiring; filtering returns with
// the backend.
export function BrowseToolbar() {
  return (
    <div className="flex flex-wrap items-center gap-3">
      <button
        type="button"
        className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-border bg-card px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-primary-tint"
      >
        <Bookmark aria-hidden="true" className="size-4" />
        {TOOLBAR_LABELS.savedOnly}
      </button>

      <FilterSelect
        label={TOOLBAR_LABELS.sort}
        value="newest"
        onValueChange={() => {}}
        options={SORT_OPTIONS}
      />
    </div>
  );
}