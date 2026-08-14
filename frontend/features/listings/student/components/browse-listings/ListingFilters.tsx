"use client";

import { ListFilter } from "lucide-react";

import type { ListingFiltersState } from "../../types";
import {
  FilterSelect,
} from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import { DEFAULT_FILTERS, FILTER_LABELS, FILTER_LISTS } from "./constants";

interface ListingFiltersProps {
  filters: ListingFiltersState;
  onChange: (filters: ListingFiltersState) => void;
}

// Student browse filter controls (FR-013). Pure presentational leaf: receives
// the filter state and an onChange, renders mode/format/paid controls driven
// by the shared option lists (R-1) and a reset button. Owns no state and no
// fetch logic — the BrowseListingsContainer orchestrator owns the state (R-6).

export function ListingFilters({ filters, onChange }: ListingFiltersProps) {
  function update<K extends keyof ListingFiltersState>(key: K, value: ListingFiltersState[K]) {
    onChange({ ...filters, [key]: value });
  }

  function reset() {
    onChange(DEFAULT_FILTERS);
  }

  const hasActiveFilters = filters.mode !== "" || filters.format !== "" || filters.paid !== "any";

  return (
    <div className="space-y-4 rounded-xl border border-border bg-card p-4">
      <div className="flex items-center justify-between gap-2">
        <h2 className="flex items-center gap-2 text-sm font-semibold text-foreground">
          <ListFilter aria-hidden="true" className="size-4 text-muted-foreground" />
          {FILTER_LABELS.title}
        </h2>
        {hasActiveFilters ? (
          <ResetFiltersButton onClick={reset} label={FILTER_LABELS.reset} />
        ) : null}
      </div>

      <div className="grid gap-4 sm:grid-cols-3">
        <FilterSelect
          label={FILTER_LABELS.mode}
          value={filters.mode}
          onValueChange={(value) => update("mode", value)}
          allLabel={FILTER_LABELS.allModes}
          options={FILTER_LISTS.mode}
        />

        <FilterSelect
          label={FILTER_LABELS.format}
          value={filters.format}
          onValueChange={(value) => update("format", value)}
          allLabel={FILTER_LABELS.allFormats}
          options={FILTER_LISTS.format}
        />

        <FilterSelect
          label={FILTER_LABELS.paid}
          value={filters.paid}
          onValueChange={(value) => update("paid", value as ListingFiltersState["paid"])}
          placeholder={FILTER_LABELS.any}
          options={FILTER_LISTS.paid}
        />
      </div>
    </div>
  );
}
