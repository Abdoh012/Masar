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

interface FilterSelectProps {
  options: FilterOption[];
  placeholder?: string;
  triggerClassName?: string;
}

export function FilterSelect({
  options,
  placeholder,
  triggerClassName,
}: FilterSelectProps) {
  return (
    <div>
      <Select>
        <SelectTrigger className={`font-medium cursor-pointer ${triggerClassName}`}>
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>

        <SelectContent>
          <SelectGroup>
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
