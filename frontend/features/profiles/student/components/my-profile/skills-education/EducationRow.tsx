"use client";

import { useState } from "react";

import { Trash2, Edit } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import type { EducationDegreeOption } from "../../../types";
import type { EducationEntry } from "../../../types";
import { EDUCATION_LABELS } from "./constants";
import { EducationForm } from "./EducationForm";
import { EducationRemoveForm } from "./EducationRemoveForm";

export function EducationRow({ entry, degreeOptions }: {
  entry: EducationEntry;
  degreeOptions: EducationDegreeOption[];
}) {
  const [editing, setEditing] = useState(false);

  const title = entry.degree ?? EDUCATION_LABELS.newEntryTitle;

  const subtitleParts = [
    entry.university,
    entry.faculty,
    entry.fieldOfStudy,
  ].filter(Boolean).join(" · ");

  const gradeLine = entry.grade ? (
    <p className="mt-1 text-xs text-primary-text">
      {EDUCATION_LABELS.fields.grade}: {entry.grade}
    </p>
  ) : null;

  const years = () => {
    if (entry.isCurrent) {
      return `${entry.startYear ?? ""} – Present`;
    }
    if (entry.startYear != null && entry.endYear != null) {
      return `${entry.startYear} – ${entry.endYear}`;
    }
    if (entry.startYear != null) {
      return `Started ${entry.startYear}`;
    }
    if (entry.endYear != null) {
      return `Ended ${entry.endYear}`;
    }
    return null;
  };

  const yearsLine = years();
  const studyModeLine = entry.studyMode ? (
    <p className="text-xs text-muted-foreground">{entry.studyMode}</p>
  ) : null;

  return (
    <li className="rounded-xl border border-border bg-card p-4">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="truncate text-sm font-semibold text-primary-text">
            {title}
          </p>
          {subtitleParts ? (
            <p className="mt-0.5 truncate text-xs text-muted-foreground">
              {subtitleParts}
            </p>
          ) : null}
        </div>

        <div className="flex items-center gap-1.5 shrink-0">
          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="size-8 text-muted-foreground hover:text-primary-text"
            onClick={() => setEditing(true)}
            aria-label={EDUCATION_LABELS.edit}
          >
            <Edit className="size-4" />
          </Button>

          <EducationRemoveForm entry={entry} />
        </div>
      </div>

      {gradeLine}

      {yearsLine ? (
        <p className="mt-2 text-xs text-muted-foreground">{yearsLine}</p>
      ) : null}

      {studyModeLine}

      {editing ? (
        <EducationForm
          mode="edit"
          entry={entry}
          degreeOptions={degreeOptions}
          onSaved={() => setEditing(false)}
        />
      ) : null}
    </li>
  );
}
