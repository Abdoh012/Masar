"use client";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import { FILTER_LABELS, FILTER_LISTS } from "./constants";

export function BrowseFilterBar() {
  return (
    <div className="grid grid-cols-1 md:grid-cols-4 flex-wrap items-end gap-4 rounded-2xl border border-border bg-card p-4 shadow-card">
      {/* <FilterSelect placeholder="" options={FILTER_LISTS.category} /> */}

      <FilterSelect
        placeholder={FILTER_LABELS.trainingType}
        options={FILTER_LISTS.trainingType}
      />

      <FilterSelect
        placeholder={FILTER_LABELS.mode}
        options={FILTER_LISTS.mode}
      />

      <FilterSelect
        placeholder={FILTER_LABELS.price}
        options={FILTER_LISTS.price}
      />

      <ResetFiltersButton label={FILTER_LABELS.clear} />
    </div>
  );
}
