"use client";

import { useState } from "react";

import { Bookmark } from "lucide-react";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";

import { SORT_OPTIONS, TOOLBAR_LABELS } from "./constants";

// BrowseToolbar: the browse page's top row — the Saved-Only toggle plus the
// sort dropdown. Presentational for now — sorting is deferred to backend
// integration (AGENTS.md), so the select owns local value state purely to
// satisfy the controlled FilterSelect API and stay interactive in the UI-only
// phase.
export function BrowseToolbar() {
  const [sort, setSort] = useState("");

  return (
    <div className="flex flex-wrap items-center gap-3">
      <button
        type="button"
        className="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-primary-tint"
      >
        <Bookmark aria-hidden="true" className="size-4" />
        {TOOLBAR_LABELS.savedOnly}
      </button>

      <FilterSelect
        label={TOOLBAR_LABELS.sort}
        value={sort}
        onValueChange={setSort}
        allLabel="Default"
        placeholder={TOOLBAR_LABELS.sort}
        options={SORT_OPTIONS}
      />
    </div>
  );
}