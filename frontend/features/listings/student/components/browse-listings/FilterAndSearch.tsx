import { BrowseToolbar } from "./BrowseToolbar";
import { FILTER_LABELS } from "./constants";
import { FilterSearchField } from "./FilterSearchField";

export default function FilterAndSearch() {
  return (
    <div className="my-4 flex flex-col sm:flex-row items-start sm:items-center justify-between">
      <FilterSearchField placeholder={FILTER_LABELS.searchPlaceholder} />

      <BrowseToolbar />
    </div>
  );
}