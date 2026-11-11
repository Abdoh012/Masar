"use client";

import { useEffect, useRef, useState } from "react";

import { Button } from "@/shared/components/ui/button";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { addEducation, updateEducation } from "../../../actions";
import type {
  EducationDegreeOption,
  EducationEntry,
  StudyFieldOption,
} from "../../../types";
import { EducationLookupSelect } from "./EducationLookupSelect";
import { LabeledInput } from "./LabeledInput";
import { EDUCATION_LABELS } from "./constants";

type Mode = "add" | "edit";

interface EducationField {
  label: string;
  htmlFor: string;
  name: string;
  type?: string;
  defaultValue?: string | number;
  placeholder?: string;
  disabled?: boolean;
  maxLength?: number;
  min?: string | number;
  max?: string | number;
  colSpan?: string;
}

interface EducationFormProps {
  mode: Mode;
  entry?: EducationEntry;
  degreeOptions: EducationDegreeOption[];
  studyFieldOptions: StudyFieldOption[];
  onSaved?: () => void;
}

export function EducationForm({
  mode,
  entry,
  degreeOptions,
  studyFieldOptions,
  onSaved,
}: EducationFormProps) {
  const action = mode === "edit" ? updateEducation : addEducation;
  const { formAction, state, isPending } = useFormFeedBack(action, null);
  const [formError, setFormError] = useState<string | null>(null);
  const formRef = useRef<HTMLFormElement>(null);

  // Close the form once the action reports success (create or update); the
  // revalidated list already shows the saved record.
  useEffect(() => {
    if (state?.success) onSaved?.();
  }, [state?.success, onSaved]);

  const maxYear = new Date().getFullYear() + 10;

  const defaultDegreeId = entry?.degreeId ?? null;

  // The presented record carries only the field's name (no id), so the select's
  // default is derived by trimmed/case-insensitive name match against the same
  // lookup; no match -> "". Caveat: study_fields.name is not unique in the
  // schema, so if two active fields share a name, the first match wins.
  const fieldOfStudy = entry?.fieldOfStudy ?? "";
  const defaultFieldId =
    fieldOfStudy !== ""
      ? (studyFieldOptions.find(
          (option) =>
            option.name.trim().toLowerCase() ===
            fieldOfStudy.trim().toLowerCase(),
        )?.id ?? null)
      : null;

  const defaultUniversity = entry?.university ?? "";
  const defaultGrade = entry?.grade ?? "";
  const defaultStartYear =
    entry?.startYear != null ? String(entry.startYear) : "";
  const defaultEndYear = entry?.endYear != null ? String(entry.endYear) : "";

  const handleSave = (event: React.MouseEvent) => {
    event.preventDefault();
    setFormError(null);

    const formData = new FormData(formRef.current as HTMLFormElement);
    const degreeName = String(formData.get("degree_name") ?? "");
    const universityName = String(formData.get("university_name") ?? "");

    if (degreeName === "" && universityName === "") {
      setFormError(EDUCATION_LABELS.degreeRequiredOrUniversity);
      return;
    }

    formRef.current?.requestSubmit();
  };

  // Repeated label + input fields rendered from config instead of duplicated
  // markup. The two lookup selects sit in their original grid slots, so the
  // text fields split around the field-of-study one.
  const fieldsBeforeSelect: EducationField[] = [
    {
      label: EDUCATION_LABELS.fields.institution,
      htmlFor: "education-university",
      name: "university_name",
      type: "text",
      defaultValue: defaultUniversity,
      placeholder: EDUCATION_LABELS.placeholders.institution,
      disabled: isPending,
    },
  ];

  const fieldsAfterSelect: EducationField[] = [
    {
      label: EDUCATION_LABELS.fields.grade,
      htmlFor: "education-grade",
      name: "grade",
      type: "text",
      defaultValue: defaultGrade,
      placeholder: EDUCATION_LABELS.placeholders.grade,
      maxLength: 100,
      disabled: isPending,
      colSpan: "sm:col-span-2",
    },
    {
      label: EDUCATION_LABELS.fields.startedAt,
      htmlFor: "education-start-year",
      name: "start_year",
      type: "number",
      min: "1950",
      max: String(maxYear),
      defaultValue: defaultStartYear,
      placeholder: EDUCATION_LABELS.placeholders.startedAt,
      disabled: isPending,
    },
    {
      label: EDUCATION_LABELS.fields.endedAt,
      htmlFor: "education-end-year",
      name: "end_year",
      type: "number",
      min: "1950",
      max: String(maxYear),
      defaultValue: defaultEndYear,
      placeholder: EDUCATION_LABELS.placeholders.endedAt,
      disabled: isPending,
    },
  ];

  const renderField = (field: EducationField) => (
    <div key={field.name} className={field.colSpan ?? "space-y-1.5"}>
      <LabeledInput {...field} />
    </div>
  );

  return (
    <form ref={formRef} action={formAction} className="space-y-3">
      {mode === "edit" && entry != null && (
        <input type="hidden" name="education_id" value={String(entry.id)} />
      )}

      <div className="grid gap-3 sm:grid-cols-2">
        <EducationLookupSelect
          id="education-degree"
          name="degree_name"
          label={EDUCATION_LABELS.fields.degree}
          placeholder={EDUCATION_LABELS.placeholders.degree}
          emptyLabel={EDUCATION_LABELS.fields.degree}
          defaultValue={defaultDegreeId != null ? String(defaultDegreeId) : ""}
          options={degreeOptions}
          disabled={isPending}
        />

        {fieldsBeforeSelect.map(renderField)}

        <div className="sm:col-span-2">
          <EducationLookupSelect
            id="education-field-of-study"
            name="field_id"
            label={EDUCATION_LABELS.fields.fieldOfStudy}
            placeholder={EDUCATION_LABELS.placeholders.fieldOfStudy}
            emptyLabel={EDUCATION_LABELS.fields.fieldOfStudy}
            defaultValue={defaultFieldId != null ? String(defaultFieldId) : ""}
            options={studyFieldOptions}
            disabled={isPending}
          />
        </div>

        {fieldsAfterSelect.map(renderField)}
      </div>

      {formError ? <p className="text-sm text-error-fg">{formError}</p> : null}

      <div className="flex justify-end gap-2">
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={onSaved}
          disabled={isPending || onSaved == null}
        >
          {EDUCATION_LABELS.cancel}
        </Button>
        <Button
          type="button"
          size="sm"
          onClick={handleSave}
          disabled={isPending}
        >
          {mode === "edit" ? EDUCATION_LABELS.save : EDUCATION_LABELS.add}
        </Button>
      </div>
    </form>
  );
}
