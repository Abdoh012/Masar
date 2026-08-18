"use client";

import { Bookmark } from "lucide-react";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";

import { SORT_OPTIONS, TOOLBAR_LABELS } from "./constants";

export function BrowseToolbar() {
  return (
    <div className="flex flex-wrap items-center gap-3">
      <button
        type="button"
        className="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-primary-tint"
      >
        <Bookmark aria-hidden="true" className="size-4" />
        {TOOLBAR_LABELS.savedOnly}
      </button>

      <FilterSelect placeholder={TOOLBAR_LABELS.sort} options={SORT_OPTIONS} />
    </div>
  );
}
