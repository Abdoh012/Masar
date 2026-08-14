interface PillOptionProps {
  name: string;
  value: string;
  label: string;
  defaultChecked?: boolean;
}

// PillOption: one pill-style radio input (structure rules §10). The native
// input stays visually hidden (peer sr-only) and the styled span reflects the
// checked state via peer-checked — the value is read off FormData by `name`
// on submit. Leaf: no state, no fetching.
export function PillOption({ name, value, label, defaultChecked }: PillOptionProps) {
  return (
    <label>
      <input
        type="radio"
        name={name}
        value={value}
        defaultChecked={defaultChecked}
        className="peer sr-only"
      />
      <span className="inline-flex cursor-pointer items-center rounded-full border border-input px-3 py-1.5 text-sm text-foreground transition-colors hover:border-primary/60 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground">
        {label}
      </span>
    </label>
  );
}
