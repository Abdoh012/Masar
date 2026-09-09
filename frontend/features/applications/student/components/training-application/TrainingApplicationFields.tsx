import type { TrainingApplicationValues } from "../../types";
import { ApplicationField } from "./ApplicationField";
import { TRAINING_APPLICATION_FIELDS } from "./constants";

interface TrainingApplicationFieldsProps {
  values: TrainingApplicationValues;
  onFieldChange: (field: keyof TrainingApplicationValues, value: string) => void;
}

// TrainingApplicationFields: step 3 of the apply wizard — motivation and
// learning goals. Both textareas are required. Leaf: composes fields, no state.
export function TrainingApplicationFields({
  values,
  onFieldChange,
}: TrainingApplicationFieldsProps) {
  return (
    <div className="space-y-5">
      <ApplicationField
        id="interestReason"
        name="interestReason"
        label={TRAINING_APPLICATION_FIELDS.interestReason.label}
        type="textarea"
        placeholder={TRAINING_APPLICATION_FIELDS.interestReason.placeholder}
        value={values.interestReason}
        onChange={(value) => onFieldChange("interestReason", value)}
        required
      />

      <ApplicationField
        id="learningGoals"
        name="learningGoals"
        label={TRAINING_APPLICATION_FIELDS.learningGoals.label}
        type="textarea"
        placeholder={TRAINING_APPLICATION_FIELDS.learningGoals.placeholder}
        value={values.learningGoals}
        onChange={(value) => onFieldChange("learningGoals", value)}
        required
      />
    </div>
  );
}