"use client";

import { useRouter, useSearchParams } from "next/navigation";

import { FilterSelect } from "@/features/listings/shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "@/features/listings/shared/components/filter-controls/ResetFiltersButton";
import createPageUrl from "@/shared/lib/createPageUrl";

import { FILTER_LABELS, FILTER_LISTS } from "./constants";

// BrowseFilterBar: the horizontal filter row (training type / mode / price +
// reset). Reads the active params from the URL and pushes updates via
// router.push — composition only, no data fetching (structure rules §3-4).
export function BrowseFilterBar() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const sp = searchParams.toString();

  const trainingType = searchParams.get("training_type") ?? "";
  const mode = searchParams.get("mode") ?? "";
  const price = searchParams.get("paid") ?? "";

  const handleChange = (key: string, value: string) =>
    router.push(createPageUrl(key, value, sp));

  const handleReset = () => {
    const params = new URLSearchParams(sp);
    for (const key of ["training_type", "mode", "paid", "sort", "page"]) {
      params.delete(key);
    }
    router.push(`?${params.toString()}`);
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-4 flex-wrap items-end gap-4 rounded-2xl border border-border bg-card p-4 shadow-card">
      <FilterSelect
        label={FILTER_LABELS.trainingType}
        value={trainingType}
        onValueChange={(v) => handleChange("training_type", v)}
        allLabel={FILTER_LABELS.allTypes}
        placeholder={FILTER_LABELS.trainingType}
        options={FILTER_LISTS.trainingType}
      />

      <FilterSelect
        label={FILTER_LABELS.mode}
        value={mode}
        onValueChange={(v) => handleChange("mode", v)}
        allLabel={FILTER_LABELS.allModes}
        placeholder={FILTER_LABELS.mode}
        options={FILTER_LISTS.mode}
      />

      <FilterSelect
        label={FILTER_LABELS.price}
        value={price}
        onValueChange={(v) => handleChange("paid", v)}
        allLabel={FILTER_LABELS.allCategories}
        placeholder={FILTER_LABELS.price}
        options={FILTER_LISTS.price}
      />

      <ResetFiltersButton label={FILTER_LABELS.clear} onClick={handleReset} />
    </div>
  );
}