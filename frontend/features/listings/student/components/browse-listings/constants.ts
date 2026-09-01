import type { BrowseSort } from "../../types";

export const BROWSE_HERO = {
  eyebrow: "Explore Opportunities",
  title: "Trainings",
  subtitle:
    "Explore training opportunities provided by companies and apply to grow your skills and career.",
};

export const FILTER_LABELS = {
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

// Filter dropdown options keyed to the training filter API's query values
// (training_type: shadowing|hands_on|project_based, mode: onsite|remote|hybrid,
// paid: 0=Free|1=Paid). These values are used verbatim in the URL and sent
// straight to the API.
export const FILTER_LISTS = {
  trainingType: [
    { value: "shadowing", label: "Observer" },
    { value: "hands_on", label: "Hands-on" },
    { value: "project_based", label: "Project-based" },
  ] as { value: string; label: string }[],
  mode: [
    { value: "onsite", label: "In-person" },
    { value: "remote", label: "Remote" },
    { value: "hybrid", label: "Hybrid" },
  ] as { value: string; label: string }[],
  price: [
    { value: "0", label: "Free" },
    { value: "1", label: "Paid" },
  ] as { value: "0" | "1"; label: string }[],
};

export const TOOLBAR_LABELS = {
  savedOnly: "Saved Only",
  sort: "Sort",
};

export const SORT_OPTIONS: { value: BrowseSort; label: string }[] = [
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
