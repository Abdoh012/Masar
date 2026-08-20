"use client";

import { useState } from "react";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import { FILTER_LABELS, FILTER_LISTS } from "./constants";

// BrowseFilterBar: the browse page's horizontal filter row (mode/format/price).
// Presentational for now — client filtering is deferred to backend integration
// (AGENTS.md), so the selects own local value state purely to satisfy the
// controlled FilterSelect API and keep the controls interactive in the UI-only
// phase. The "All" sentinel maps each select back to its empty state via Reset.
export function BrowseFilterBar() {
  const [mode, setMode] = useState("");
  const [format, setFormat] = useState("");
  const [price, setPrice] = useState("");

  function handleReset() {
    setMode("");
    setFormat("");
    setPrice("");
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-4 flex-wrap items-end gap-4 rounded-2xl border border-border bg-card p-4 shadow-card">
      {/* <FilterSelect placeholder="" options={FILTER_LISTS.category} /> */}

      <FilterSelect
        label={FILTER_LABELS.trainingType}
        value={mode}
        onValueChange={setMode}
        allLabel={FILTER_LABELS.allTypes}
        placeholder={FILTER_LABELS.trainingType}
        options={FILTER_LISTS.trainingType}
      />

      <FilterSelect
        label={FILTER_LABELS.mode}
        value={format}
        onValueChange={setFormat}
        allLabel={FILTER_LABELS.allModes}
        placeholder={FILTER_LABELS.mode}
        options={FILTER_LISTS.mode}
      />

      <FilterSelect
        label={FILTER_LABELS.price}
        value={price}
        onValueChange={setPrice}
        allLabel={FILTER_LABELS.allCategories}
        placeholder={FILTER_LABELS.price}
        options={FILTER_LISTS.price}
      />

      <ResetFiltersButton label={FILTER_LABELS.clear} onClick={handleReset} />
    </div>
  );
}