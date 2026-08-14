import { LabeledInput } from "./LabeledInput";

import { FORM_FIELDS } from "./constants";

interface BasicInfoFieldsProps {
  defaultField?: string;
  defaultSpecialization?: string;
  defaultDescription?: string;
}

// Client-bound presentational leaf — no directive required. The orchestrator
// owns the <form>; this leaf composes the shared LabeledInput leaf for the
// listing's basic info (FR-001), read off FormData on submit (structure
// rules §10 / R-7). defaultValue prefills edit mode without controlling the
// fields.

export function BasicInfoFields({
  defaultField,
  defaultSpecialization,
  defaultDescription,
}: BasicInfoFieldsProps) {
  return (
    <div className="space-y-5">
      <LabeledInput
        id="field"
        name="field"
        label={FORM_FIELDS.field.label}
        placeholder={FORM_FIELDS.field.placeholder}
        required={FORM_FIELDS.field.required}
        defaultValue={defaultField}
      />

      <LabeledInput
        id="specialization"
        name="specialization"
        label={FORM_FIELDS.specialization.label}
        placeholder={FORM_FIELDS.specialization.placeholder}
        required={FORM_FIELDS.specialization.required}
        defaultValue={defaultSpecialization}
      />

      <LabeledInput
        id="description"
        name="description"
        label={FORM_FIELDS.description.label}
        placeholder={FORM_FIELDS.description.placeholder}
        required={FORM_FIELDS.description.required}
        defaultValue={defaultDescription}
      />
    </div>
  );
}