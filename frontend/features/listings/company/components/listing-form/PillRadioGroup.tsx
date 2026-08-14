import { PillOption } from "./PillOption";

interface PillRadioGroupProps {
  name: string;
  legend: string;
  hint?: string;
  options: { value: string; label: string }[];
  defaultValue?: string;
}

// PillRadioGroup: a fieldset of pill-style radio options (structure rules §3
// / §5). Uncontrolled — the selected value is read off FormData by `name` on
// submit; defaultValue prefills edit mode via defaultChecked. Leaf: no state,
// no fetching.
export function PillRadioGroup({
  name,
  legend,
  hint,
  options,
  defaultValue,
}: PillRadioGroupProps) {
  return (
    <fieldset className="space-y-2">
      <legend className="text-sm font-medium text-foreground">{legend}</legend>
      {hint ? <p className="text-xs text-muted-foreground">{hint}</p> : null}
      <div className="flex flex-wrap gap-2">
        {options.map((option) => (
          <PillOption
            key={option.value}
            name={name}
            value={option.value}
            label={option.label}
            defaultChecked={option.value === defaultValue}
          />
        ))}
      </div>
    </fieldset>
  );
}
