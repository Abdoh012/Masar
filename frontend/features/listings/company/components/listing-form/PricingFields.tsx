import { Checkbox } from "@/shared/components/ui/checkbox";
import { Label } from "@/shared/components/ui/label";

import { TRIAL_MIN_DAYS } from "../../../shared/lib/constants";

import { LabeledInput } from "./LabeledInput";
import { FORM_FIELDS, PAID_TOGGLE } from "./constants";

interface PricingFieldsProps {
  isPaid: boolean;
  onPaidChange: (paid: boolean) => void;
  defaultPrice?: number;
  defaultTrialDays?: number;
}

// Client-bound presentational leaf — no directive required. The paid toggle
// is the one deliberately controlled field (per-field exception, structure
// rules §10 / R-7): the orchestrator owns `isPaid` and passes it back down so
// price + trial-days show only when paid (FR-002). Price and trial days stay
// uncontrolled named inputs read off FormData on submit; the trial minimum
// (FR-003) is enforced in the orchestrator using the shared constant.
// defaultPrice/defaultTrialDays prefill edit mode via defaultValue.

export function PricingFields({
  isPaid,
  onPaidChange,
  defaultPrice,
  defaultTrialDays,
}: PricingFieldsProps) {
  return (
    <div className="space-y-5">
      <div className="space-y-2">
        <Label htmlFor="isPaid" className="flex items-center gap-2">
          <Checkbox
            id="isPaid"
            checked={isPaid}
            onCheckedChange={(checked) => onPaidChange(checked === true)}
          />
          <span>{PAID_TOGGLE.label}</span>
        </Label>
        <p className="pl-6 text-xs text-muted-foreground">{PAID_TOGGLE.hint}</p>
      </div>

      {isPaid ? (
        <div className="space-y-5">
          <LabeledInput
            id="price"
            name="price"
            label={FORM_FIELDS.price.label}
            type="number"
            min="0"
            step="0.01"
            inputMode="decimal"
            placeholder={FORM_FIELDS.price.placeholder}
            required={FORM_FIELDS.price.required}
            defaultValue={defaultPrice}
          />

          <LabeledInput
            id="trialDays"
            name="trialDays"
            label={FORM_FIELDS.trialDays.label}
            type="number"
            min={TRIAL_MIN_DAYS}
            inputMode="numeric"
            placeholder={FORM_FIELDS.trialDays.placeholder}
            required={FORM_FIELDS.trialDays.required}
            defaultValue={defaultTrialDays}
          />
        </div>
      ) : null}
    </div>
  );
}
