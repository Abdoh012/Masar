"use client";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import { FILTER_LABELS, FILTER_LISTS } from "./constants";

// BrowseFilterBar: the horizontal filter strip below the search/toolbar row
// (Category / Training Type / Mode / Price dropdowns + Clear All).
// Presentational for now — static values, no wiring; filtering returns with
// the backend. Reuses the shared FilterSelect (same pattern as the admin
// moderation bar).
export function BrowseFilterBar() {
  return (
    <div className="flex flex-wrap items-end gap-4 rounded-2xl border border-border bg-card p-4 shadow-card">
      <FilterSelect
        label={FILTER_LABELS.category}
        value=""
        onValueChange={() => {}}
        allLabel={FILTER_LABELS.allCategories}
        options={FILTER_LISTS.category}
      />

      <FilterSelect
        label={FILTER_LABELS.trainingType}
        value=""
        onValueChange={() => {}}
        allLabel={FILTER_LABELS.allTypes}
        options={FILTER_LISTS.trainingType}
      />

      <FilterSelect
        label={FILTER_LABELS.mode}
        value=""
        onValueChange={() => {}}
        allLabel={FILTER_LABELS.allModes}
        options={FILTER_LISTS.mode}
      />

      <FilterSelect
        label={FILTER_LABELS.price}
        value="any"
        onValueChange={() => {}}
        options={FILTER_LISTS.price}
      />

      <ResetFiltersButton onClick={() => {}} label={FILTER_LABELS.clear} />
    </div>
  );
}