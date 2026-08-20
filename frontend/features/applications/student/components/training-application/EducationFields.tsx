import type { EducationValues } from "../../types";
import { ApplicationField } from "./ApplicationField";
import { StatusRadioGroup } from "./StatusRadioGroup";
import { EDUCATION_FIELDS } from "./constants";

interface EducationFieldsProps {
  values: EducationValues;
  onFieldChange: (field: keyof EducationValues, value: string) => void;
}

// EducationFields: step 2 of the apply wizard — university + current status.
// The status radio (controlled, via StatusRadioGroup) decides which conditional
// year field renders: "Still a student" shows Academic Year, "Graduated" shows
// Graduation Year. Only the visible field is required, so native validation
// always gates exactly the relevant field. Leaf: composes fields, no state.
export function EducationFields({ values, onFieldChange }: EducationFieldsProps) {
  const isStudent = values.status === "student";

  return (
    <div className="space-y-5">
      <ApplicationField
        id="university"
        name="university"
        label={EDUCATION_FIELDS.university.label}
        placeholder={EDUCATION_FIELDS.university.placeholder}
        value={values.university}
        onChange={(value) => onFieldChange("university", value)}
        required
      />

      <StatusRadioGroup
        value={values.status}
        onChange={(status) => onFieldChange("status", status)}
      />

      {isStudent ? (
        <ApplicationField
          id="academicYear"
          name="academicYear"
          label={EDUCATION_FIELDS.academicYear.label}
          placeholder={EDUCATION_FIELDS.academicYear.placeholder}
          value={values.academicYear}
          onChange={(value) => onFieldChange("academicYear", value)}
          required
        />
      ) : (
        <ApplicationField
          id="graduationYear"
          name="graduationYear"
          label={EDUCATION_FIELDS.graduationYear.label}
          placeholder={EDUCATION_FIELDS.graduationYear.placeholder}
          value={values.graduationYear}
          onChange={(value) => onFieldChange("graduationYear", value)}
          required
        />
      )}
    </div>
  );
}