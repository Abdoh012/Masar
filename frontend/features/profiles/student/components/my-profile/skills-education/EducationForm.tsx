"use client";

import { useRef, useState } from "react";

import { Button } from "@/shared/components/ui/button";
import { Label } from "@/shared/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { addEducation, updateEducation } from "../../../actions";
import type { EducationDegreeOption } from "../../../types";
import type { EducationEntry } from "../../../types";
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
  onSaved?: () => void;
}

export function EducationForm({
  mode,
  entry,
  degreeOptions,
  onSaved,
}: EducationFormProps) {
  const action = mode === "edit" ? updateEducation : addEducation;
  const { formAction, isPending } = useFormFeedBack(action, null);
  const [formError, setFormError] = useState<string | null>(null);
  const formRef = useRef<HTMLFormElement>(null);

  const maxYear = new Date().getFullYear() + 10;
  

  const defaultDegreeId = entry?.degreeId ?? null;
  const defaultUniversity = entry?.university ?? "";
  const defaultFieldOfStudy = entry?.fieldOfStudy ?? "";
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
  // markup. Degree (Select) stays outside.
  const fields: EducationField[] = [
    {
      label: EDUCATION_LABELS.fields.institution,
      htmlFor: "education-university",
      name: "university_name",
      type: "text",
      defaultValue: defaultUniversity,
      placeholder: EDUCATION_LABELS.placeholders.institution,
      disabled: isPending,
    },
    {
      label: EDUCATION_LABELS.fields.fieldOfStudy,
      htmlFor: "education-field-of-study",
      name: "field_study",
      type: "text",
      defaultValue: defaultFieldOfStudy,
      placeholder: EDUCATION_LABELS.placeholders.fieldOfStudy,
      disabled: isPending,
      colSpan: "sm:col-span-2",
    },
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

  return (
    <form ref={formRef} action={formAction} className="space-y-3">
      {mode === "edit" && entry != null && (
        <input type="hidden" name="education_id" value={String(entry.id)} />
      )}

      <div className="grid gap-3 sm:grid-cols-2">
        <div className="space-y-1.5">
          <Label htmlFor="education-degree">
            {EDUCATION_LABELS.fields.degree}
          </Label>
          <Select
            name="degree_name"
            defaultValue={
              defaultDegreeId != null ? String(defaultDegreeId) : ""
            }
            disabled={isPending}
          >
            <SelectTrigger className="h-10 hover:cursor-pointer">
              <SelectValue placeholder={EDUCATION_LABELS.placeholders.degree} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{EDUCATION_LABELS.fields.degree}</SelectItem>
              {degreeOptions.map((option) => (
                <SelectItem key={option.id} value={String(option.id)}>
                  {option.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {fields.map((field) => (
          <div key={field.name} className={field.colSpan ?? "space-y-1.5"}>
            <LabeledInput {...field} />
          </div>
        ))}
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
