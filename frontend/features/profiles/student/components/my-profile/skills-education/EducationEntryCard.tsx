"use client";

import { Trash2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ProfileFieldInput } from "../ProfileFieldInput";
import { EDUCATION_LABELS } from "./constants";
import type { EducationEntry } from "../../../types";

interface EducationEntryCardProps {
  entry: EducationEntry;
  onChange: (id: string, patch: Partial<EducationEntry>) => void;
  onRemove: (id: string) => void;
}

// EducationEntryCard: one row in the LinkedIn-style education list. The header
// is built from the entry's own values (degree, then institution) so a filled
// list is readable at a glance, and falls back to a generic title while the row
// is still empty. Fields are month pickers for the dates; everything stays in
// the owning container's state, so this leaf only reports edits upward.
export function EducationEntryCard({
  entry,
  onChange,
  onRemove,
}: EducationEntryCardProps) {
  const { id, degree, institution, fieldOfStudy, startedAt, endedAt } = entry;

  const title = degree || EDUCATION_LABELS.newEntryTitle;

  return (
    <li className="rounded-xl border border-border bg-card p-4">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="truncate text-sm font-semibold text-primary-text">
            {title}
          </p>

          {institution ? (
            <p className="mt-0.5 truncate text-xs text-muted-foreground">
              {institution}
            </p>
          ) : null}
        </div>

        <Button
          type="button"
          variant="ghost"
          size="icon"
          className="size-8 shrink-0 cursor-pointer text-muted-foreground hover:text-error-fg"
          onClick={() => onRemove(id)}
          aria-label={EDUCATION_LABELS.remove}
        >
          <Trash2 className="size-4" />
        </Button>
      </div>

      <div className="mt-4 grid gap-3 sm:grid-cols-2">
        <ProfileFieldInput
          id={`${id}-degree`}
          label={EDUCATION_LABELS.fields.degree}
          value={degree}
          placeholder={EDUCATION_LABELS.placeholders.degree}
          onChange={(value) => onChange(id, { degree: value })}
        />

        <ProfileFieldInput
          id={`${id}-institution`}
          label={EDUCATION_LABELS.fields.institution}
          value={institution}
          placeholder={EDUCATION_LABELS.placeholders.institution}
          onChange={(value) => onChange(id, { institution: value })}
        />

        <ProfileFieldInput
          id={`${id}-field-of-study`}
          label={EDUCATION_LABELS.fields.fieldOfStudy}
          value={fieldOfStudy}
          placeholder={EDUCATION_LABELS.placeholders.fieldOfStudy}
          onChange={(value) => onChange(id, { fieldOfStudy: value })}
        />

        <ProfileFieldInput
          id={`${id}-started-at`}
          label={EDUCATION_LABELS.fields.startedAt}
          type="month"
          value={startedAt}
          onChange={(value) => onChange(id, { startedAt: value })}
        />

        <ProfileFieldInput
          id={`${id}-ended-at`}
          label={EDUCATION_LABELS.fields.endedAt}
          type="month"
          value={endedAt}
          onChange={(value) => onChange(id, { endedAt: value })}
        />
      </div>
    </li>
  );
}
