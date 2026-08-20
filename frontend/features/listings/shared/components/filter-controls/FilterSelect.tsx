import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  SelectGroup,
} from "@/shared/components/ui/select";

export interface FilterOption {
  value: string;
  label: string;
}

// "All" sentinel: Radix Select rejects empty string values, so the reset/all
// option uses a sentinel token that maps back to an empty string for filtering.
const ALL_VALUE = "__all__";

interface FilterSelectProps {
  label: string;
  value: string;
  onValueChange: (value: string) => void;
  allLabel: string;
  options: FilterOption[];
  placeholder?: string;
  triggerClassName?: string;
}

// FilterSelect: the shared labeled filter dropdown (AGENTS.md). Controlled by
// the caller (value + onValueChange); the first, always-present item is the
// "All" sentinel, whose selection clears the filter (maps to ""). The Radix
// SelectGroup lists the real options underneath. Pure leaf — no state of its own.
export function FilterSelect({
  label,
  value,
  onValueChange,
  allLabel,
  options,
  placeholder,
  triggerClassName,
}: FilterSelectProps) {
  const selected = value === "" ? ALL_VALUE : value;

  function handleChange(next: string) {
    onValueChange(next === ALL_VALUE ? "" : next);
  }

  return (
    <div className="space-y-1.5">
      <span className="text-xs font-medium text-muted-foreground">{label}</span>
      <Select value={selected} onValueChange={handleChange}>
        <SelectTrigger className={`font-medium cursor-pointer ${triggerClassName}`}>
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>

        <SelectContent>
          <SelectGroup>
            <SelectItem value={ALL_VALUE} className="cursor-pointer">
              {allLabel}
            </SelectItem>
            {options.map((item) => (
              <SelectItem key={item.value} value={item.value} className="cursor-pointer">
                {item.label}
              </SelectItem>
            ))}
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
  );
}