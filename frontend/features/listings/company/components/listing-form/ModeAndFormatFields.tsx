import { LISTING_FORMATS, LISTING_MODES } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";

import { FORMAT_SECTION, MODE_SECTION } from "./constants";
import { PillRadioGroup } from "./PillRadioGroup";

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
      <PillRadioGroup
        name="mode"
        legend={MODE_SECTION.label}
        hint={MODE_SECTION.hint}
        options={LISTING_MODES}
        defaultValue={defaultMode}
      />

      <PillRadioGroup
        name="format"
        legend={FORMAT_SECTION.label}
        hint={FORMAT_SECTION.hint}
        options={LISTING_FORMATS}
        defaultValue={defaultFormat}
      />
    </div>
  );
}
