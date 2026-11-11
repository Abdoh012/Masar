import { GraduationCap } from "lucide-react";

import {
  fetchEducation,
  fetchEducationDegrees,
  fetchStudyFields,
} from "../../../api";
import {
  normalizeEducation,
  normalizeEducationDegrees,
  normalizeStudyFields,
} from "../../../lib/normalize";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { EducationRow } from "./EducationRow";
import { AddEducationForm } from "./AddEducationForm";
import { EDUCATION_LABELS } from "./constants";
import type {
  EducationDegreeOption,
  StudyFieldOption,
} from "../../../types";

export default async function EducationList() {
  const [educationResult, degreesResult, studyFieldsResult] =
    await Promise.all([
      fetchEducation(),
      fetchEducationDegrees(),
      fetchStudyFields(),
    ]);

  if (!educationResult.success) {
    throw new Error(educationResult.error ?? "Unable to load your education.");
  }

  if (!degreesResult.success) {
    throw new Error(degreesResult.error ?? "Unable to load the degree list.");
  }

  if (!studyFieldsResult.success) {
    throw new Error(
      studyFieldsResult.error ?? "Unable to load the study fields.",
    );
  }

  const education = normalizeEducation(educationResult);
  const degreeOptions: EducationDegreeOption[] = normalizeEducationDegrees(
    degreesResult.data,
  );
  const studyFieldOptions: StudyFieldOption[] = normalizeStudyFields(
    studyFieldsResult.data,
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
              studyFieldOptions={studyFieldOptions}
            />
          ))}
        </ul>
      )}

      <div className="mt-4">
        <AddEducationForm
          degreeOptions={degreeOptions}
          studyFieldOptions={studyFieldOptions}
        />
      </div>
    </ProfileSubPanel>
  );
}
