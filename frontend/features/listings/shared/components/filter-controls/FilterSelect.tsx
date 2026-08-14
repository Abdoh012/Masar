import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";

export interface FilterOption {
  value: string;
  label: string;
}

interface FilterSelectProps {
  label: string;
  value: string;
  onValueChange: (value: string) => void;
  options: FilterOption[];
  allLabel?: string;
  placeholder?: string;
  triggerClassName?: string;
}

// Radix Select rejects empty-string item values, so the "no filter" state
// ("") is mapped to this sentinel when an "All" option is present — the same
// convention the browse and moderation filters each duplicated before.
const ALL_FILTER_VALUE = "all";

// FilterSelect: the shared labeled filter dropdown used by the student browse
// filters and the admin moderation filters. When `allLabel` is provided it
// renders the "All <x>" sentinel item and maps "" <-> sentinel for the parent;
// without it (e.g. a ternary "Any/Free/Paid" control) the value passes
// through unchanged. Pure leaf: no state, no fetching.
export function FilterSelect({
  label,
  value,
  onValueChange,
  options,
  allLabel,
  placeholder,
  triggerClassName,
}: FilterSelectProps) {
  const hasAll = allLabel !== undefined;
  const controlValue = hasAll && !value ? ALL_FILTER_VALUE : value;

  function handleValueChange(next: string) {
    onValueChange(hasAll && next === ALL_FILTER_VALUE ? "" : next);
  }

  return (
    <div className="space-y-1.5">
      <span className="block text-xs font-medium text-muted-foreground">{label}</span>
      <Select value={controlValue} onValueChange={handleValueChange}>
        <SelectTrigger className={triggerClassName} aria-label={label}>
          <SelectValue placeholder={placeholder ?? allLabel} />
        </SelectTrigger>
        <SelectContent>
          {hasAll ? (
            <SelectItem value={ALL_FILTER_VALUE}>{allLabel}</SelectItem>
          ) : null}
          {options.map((option) => (
            <SelectItem key={option.value} value={option.value}>
              {option.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}