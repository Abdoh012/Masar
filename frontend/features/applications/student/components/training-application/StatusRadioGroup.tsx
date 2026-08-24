import { cn } from "@/shared/lib/utils";

import type { EducationStatus } from "../../types";
import { EDUCATION_FIELDS, EDUCATION_STATUS_OPTIONS } from "./constants";

interface StatusRadioGroupProps {
  value: EducationStatus;
  onChange: (value: EducationStatus) => void;
}

// StatusRadioGroup: the pill-style Current Status radio (structure rules §3/§5).
// Controlled — the selected status drives which conditional year field renders
// in EducationFields, so this is the wizard's intended per-field exception.
export function StatusRadioGroup({ value, onChange }: StatusRadioGroupProps) {
  return (
    <fieldset className="space-y-2">
      <legend className="text-sm font-medium text-foreground">
        {EDUCATION_FIELDS.status.label}
      </legend>

      <div className="flex flex-wrap gap-2">
        {EDUCATION_STATUS_OPTIONS.map((option) => {
          const isSelected = value === option.value;

          return (
            <label key={option.value}>
              <input
                type="radio"
                name="status"
                value={option.value}
                checked={isSelected}
                onChange={() => onChange(option.value)}
                className="peer sr-only"
              />
              <span
                className={cn(
                  "inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-sm text-foreground transition-colors",
                  isSelected
                    ? "border-primary bg-primary text-primary-foreground"
                    : "border-input hover:border-primary/60",
                )}
              >
                {option.label}
              </span>
            </label>
          );
        })}
      </div>
    </fieldset>
  );
}