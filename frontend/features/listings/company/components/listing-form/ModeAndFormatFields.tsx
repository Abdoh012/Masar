import { LISTING_FORMATS, LISTING_MODES } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";

import { FORMAT_SECTION, MODE_SECTION } from "./constants";

interface ModeAndFormatFieldsProps {
  defaultMode?: ListingMode;
  defaultFormat?: "in_person" | "remote" | "hybrid";
}

// Client-bound presentational leaf — no directive required. Renders the
// mode and format options from shared/lib/constants.ts as uncontrolled
// pill-style radio inputs (R-1, structure rules §10): the selected value is
// read off FormData by `name` on submit. No local state needed;
// defaultMode/defaultFormat prefill edit mode via defaultChecked.

export function ModeAndFormatFields({
  defaultMode,
  defaultFormat,
}: ModeAndFormatFieldsProps) {
  return (
    <div className="space-y-5">
      <fieldset className="space-y-2">
        <legend className="text-sm font-medium text-foreground">{MODE_SECTION.label}</legend>
        <p className="text-xs text-muted-foreground">{MODE_SECTION.hint}</p>
        <div className="flex flex-wrap gap-2">
          {LISTING_MODES.map(({ value, label }) => (
            <label key={value}>
              <input
                type="radio"
                name="mode"
                value={value}
                defaultChecked={value === defaultMode}
                className="peer sr-only"
              />
              <span className="inline-flex cursor-pointer items-center rounded-full border border-input px-3 py-1.5 text-sm text-foreground transition-colors hover:border-primary/60 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground">
                {label}
              </span>
            </label>
          ))}
        </div>
      </fieldset>

      <fieldset className="space-y-2">
        <legend className="text-sm font-medium text-foreground">{FORMAT_SECTION.label}</legend>
        <p className="text-xs text-muted-foreground">{FORMAT_SECTION.hint}</p>
        <div className="flex flex-wrap gap-2">
          {LISTING_FORMATS.map(({ value, label }) => (
            <label key={value}>
              <input
                type="radio"
                name="format"
                value={value}
                defaultChecked={value === defaultFormat}
                className="peer sr-only"
              />
              <span className="inline-flex cursor-pointer items-center rounded-full border border-input px-3 py-1.5 text-sm text-foreground transition-colors hover:border-primary/60 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground">
                {label}
              </span>
            </label>
          ))}
        </div>
      </fieldset>
    </div>
  );
}
