import { Bookmark } from "lucide-react";

import { FilterSelect } from "@/features/listings/shared/components/filter-controls/FilterSelect";

import { SORT_OPTIONS, TOOLBAR_LABELS } from "./constants";

interface BrowseToolbarProps {
  sort: string;
  savedOnly: boolean;
  onSortChange: (sort: string) => void;
  onSavedOnlyToggle: () => void;
}

export function BrowseToolbar({
  sort,
  savedOnly,
  onSortChange,
  onSavedOnlyToggle,
}: BrowseToolbarProps) {
  return (
    <div className="flex flex-wrap items-end gap-3">
      <button
        type="button"
        onClick={onSavedOnlyToggle}
        className={`inline-flex cursor-pointer items-center gap-1.5 rounded-md border px-3 py-2 text-sm font-medium transition-colors ${
          savedOnly
            ? "border-primary bg-primary text-primary-foreground"
            : "border-border bg-card text-muted-foreground hover:bg-primary-tint"
        }`}
      >
        <Bookmark className="size-4" />
        {TOOLBAR_LABELS.savedOnly}
      </button>

      <FilterSelect
        label={TOOLBAR_LABELS.sort}
        value={sort}
        onValueChange={onSortChange}
        allLabel="Default"
        placeholder={TOOLBAR_LABELS.sort}
        options={SORT_OPTIONS}
      />
    </div>
  );
}
