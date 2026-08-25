import { FilterSearchField } from "./FilterSearchField";
import { BrowseToolbar } from "./BrowseToolbar";
import { FILTER_LABELS } from "./constants";

interface FilterAndSearchProps {
  searchQuery: string;
  sort: string;
  savedOnly: boolean;
  onSearchChange: (query: string) => void;
  onSortChange: (sort: string) => void;
  onSavedOnlyToggle: () => void;
}

export function FilterAndSearch({
  searchQuery,
  sort,
  savedOnly,
  onSearchChange,
  onSortChange,
  onSavedOnlyToggle,
}: FilterAndSearchProps) {
  return (
    <div className="my-4 flex flex-col sm:flex-row items-start sm:items-center justify-between">
      <FilterSearchField
        placeholder={FILTER_LABELS.searchPlaceholder}
        value={searchQuery}
        onSearchChange={onSearchChange}
      />

      <BrowseToolbar
        sort={sort}
        savedOnly={savedOnly}
        onSortChange={onSortChange}
        onSavedOnlyToggle={onSavedOnlyToggle}
      />
    </div>
  );
}
