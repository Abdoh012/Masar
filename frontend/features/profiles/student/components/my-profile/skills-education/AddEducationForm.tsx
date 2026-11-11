"use client";

import { useState } from "react";

import { Button } from "@/shared/components/ui/button";

import type { EducationDegreeOption, StudyFieldOption } from "../../../types";
import { EducationForm } from "./EducationForm";
import { EDUCATION_LABELS } from "./constants";

interface AddEducationFormProps {
  degreeOptions: EducationDegreeOption[];
  studyFieldOptions: StudyFieldOption[];
}

// AddEducationForm: the add-education trigger and the form it reveals. Owns
// only the open/closed state — the form closes itself (onSaved) after a
// successful save or when Cancel is pressed.
export function AddEducationForm({
  degreeOptions,
  studyFieldOptions,
}: AddEducationFormProps) {
  const [isOpen, setIsOpen] = useState(false);

  if (!isOpen) {
    return (
      <Button
        type="button"
        size="sm"
        aria-expanded={false}
        onClick={() => setIsOpen(true)}
      >
        {EDUCATION_LABELS.add}
      </Button>
    );
  }

  return (
    <EducationForm
      mode="add"
      degreeOptions={degreeOptions}
      studyFieldOptions={studyFieldOptions}
      onSaved={() => setIsOpen(false)}
    />
  );
}
