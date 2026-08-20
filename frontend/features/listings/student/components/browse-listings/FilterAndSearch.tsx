import { BrowseToolbar } from "./BrowseToolbar";
import { FILTER_LABELS } from "./constants";
import { FilterSearchField } from "./FilterSearchField";

// FilterAndSearch: the top row of the browse page — keyword search on the
// left, Saved-Only / sort toolbar on the right. Presentational for now; the
// controls get wired to real filtering once the backend exists.
export default function FilterAndSearch() {
  return (
    <div className="my-4 flex items-center justify-between">
      <FilterSearchField placeholder={FILTER_LABELS.searchPlaceholder} />

      <BrowseToolbar />
    </div>
  );
}