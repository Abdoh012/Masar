import { LISTING_FORMATS, LISTING_MODES } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";
import type { BrowseSort, ListingFiltersState } from "../../types";

export const BROWSE_HERO = {
  eyebrow: "Explore Opportunities",
  title: "Trainings",
  subtitle:
    "Explore training opportunities provided by companies and apply to grow your skills and career.",
};

export const FILTER_LABELS = {
  title: "Filters",
  category: "Category",
  searchPlaceholder: "Search by training title, company, skill, or keyword...",
  clear: "Clear All",
  allCategories: "All Categories",
  trainingType: "Training Type",
  allTypes: "All Types",
  mode: "Mode",
  allModes: "All Modes",
  price: "Price",
};

export const FILTER_LISTS = {
  trainingType: [...LISTING_MODES] as { value: ListingMode; label: string }[],
  mode: [...LISTING_FORMATS] as { value: "in_person" | "remote" | "hybrid"; label: string }[],
  price: [
    { value: "any", label: "Any" },
    { value: "free", label: "Free" },
    { value: "paid", label: "Paid" },
  ] as { value: "any" | "free" | "paid"; label: string }[],
};

export const DEFAULT_FILTERS: ListingFiltersState = {
  mode: "",
  format: "",
  paid: "any",
  category: "",
  query: "",
  savedOnly: false,
};

export const TOOLBAR_LABELS = {
  countSingular: "training found",
  countPlural: "trainings found",
  savedOnly: "Saved Only",
  sort: "Sort",
};

export const SORT_OPTIONS: { value: BrowseSort; label: string }[] = [
  { value: "default", label: "Default" },
  { value: "newest", label: "Newest First" },
  { value: "oldest", label: "Oldest First" },
  { value: "price_low_to_high", label: "Price: Low to High" },
  { value: "price_high_to_low", label: "Price: High to Low" },
  { value: "duration_short_to_long", label: "Duration: Short to Long" },
  { value: "duration_long_to_short", label: "Duration: Long to Short" },
];

export const BROWSE_EMPTY_STATE = {
  title: "No trainings match your filters",
  message: "Try clearing a filter or two to see more options.",
};
