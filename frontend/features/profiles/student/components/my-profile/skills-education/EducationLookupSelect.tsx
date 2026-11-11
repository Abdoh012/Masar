import { Label } from "@/shared/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";

interface EducationLookupSelectProps {
  id: string;
  name: string;
  label: string;
  placeholder: string;
  emptyLabel: string;
  defaultValue: string;
  options: ReadonlyArray<{ id: number; name: string }>;
  disabled?: boolean;
}

// EducationLookupSelect: the single definition of a labeled lookup Select for
// the education form — the degree and field-of-study pickers both render
// through it, so the trigger/content/item markup lives here once.
export function EducationLookupSelect({
  id,
  name,
  label,
  placeholder,
  emptyLabel,
  defaultValue,
  options,
  disabled,
}: EducationLookupSelectProps) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>
      <Select name={name} defaultValue={defaultValue} disabled={disabled}>
        <SelectTrigger id={id} className="h-10 hover:cursor-pointer">
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="">{emptyLabel}</SelectItem>
          {options.map((option) => (
            <SelectItem key={option.id} value={String(option.id)}>
              {option.name}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}
