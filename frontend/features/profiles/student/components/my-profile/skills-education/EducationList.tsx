import { notFound } from "next/navigation";

import { GraduationCap, Plus } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { fetchEducation, fetchEducationDegrees } from "../../../api";
import {
  normalizeEducation,
  normalizeEducationDegrees,
} from "../../../lib/normalize";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { EducationRow } from "./EducationRow";
import { EducationForm } from "./EducationForm";
import { EDUCATION_LABELS } from "./constants";
import type { EducationDegreeOption } from "../../../types";

export default async function EducationList() {
  const [educationResult, degreesResult] = await Promise.all([
    fetchEducation(),
    fetchEducationDegrees(),
  ]);

  if (!educationResult.success) {
    throw new Error(educationResult.error ?? "Unable to load your education.");
  }

  if (!degreesResult.success) {
    throw new Error(degreesResult.error ?? "Unable to load the degree list.");
  }

  const education = normalizeEducation(educationResult);
  const degreeOptions: EducationDegreeOption[] = normalizeEducationDegrees(
    degreesResult.data,
  );

  return (
    <ProfileSubPanel
      icon={<GraduationCap className="size-4" />}
      title={EDUCATION_LABELS.title}
      description={EDUCATION_LABELS.description}
    >
      {education.length === 0 ? (
        <p className="text-sm text-muted-foreground">
          {EDUCATION_LABELS.emptyTitle}
        </p>
      ) : (
        <ul className="grid grid-cols-1 lg:grid-cols-2 gap-3">
          {education.map((entry) => (
            <EducationRow
              key={entry.id}
              entry={entry}
              degreeOptions={degreeOptions}
            />
          ))}
        </ul>
      )}

      <div className="mt-4">
        <EducationForm mode="add" degreeOptions={degreeOptions} />
      </div>
    </ProfileSubPanel>
  );
}
