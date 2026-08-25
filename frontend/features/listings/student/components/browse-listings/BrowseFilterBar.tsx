"use client";

import { FilterSelect } from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import { FILTER_LABELS, FILTER_LISTS } from "./constants";

interface BrowseFilterBarProps {
  trainingType: string;
  mode: string;
  price: string;
  onTrainingTypeChange: (value: string) => void;
  onModeChange: (value: string) => void;
  onPriceChange: (value: string) => void;
  onReset: () => void;
}

export function BrowseFilterBar({
  trainingType,
  mode,
  price,
  onTrainingTypeChange,
  onModeChange,
  onPriceChange,
  onReset,
}: BrowseFilterBarProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-4 flex-wrap items-end gap-4 rounded-2xl border border-border bg-card p-4 shadow-card">
      <FilterSelect
        label={FILTER_LABELS.trainingType}
        value={trainingType}
        onValueChange={onTrainingTypeChange}
        allLabel={FILTER_LABELS.allTypes}
        placeholder={FILTER_LABELS.trainingType}
        options={FILTER_LISTS.trainingType}
      />

      <FilterSelect
        label={FILTER_LABELS.mode}
        value={mode}
        onValueChange={onModeChange}
        allLabel={FILTER_LABELS.allModes}
        placeholder={FILTER_LABELS.mode}
        options={FILTER_LISTS.mode}
      />

      <FilterSelect
        label={FILTER_LABELS.price}
        value={price}
        onValueChange={onPriceChange}
        allLabel={FILTER_LABELS.allCategories}
        placeholder={FILTER_LABELS.price}
        options={FILTER_LISTS.price}
      />

      <ResetFiltersButton label={FILTER_LABELS.clear} onClick={onReset} />
    </div>
  );
}
