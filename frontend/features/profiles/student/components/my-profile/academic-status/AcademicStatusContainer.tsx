import { notFound } from "next/navigation";
import { GraduationCap } from "lucide-react";

import { fetchAcademicStatus } from "../../../api";
import { normalizeAcademicStatus } from "../../../lib/normalize";
import { AcademicStatusEditor } from "./AcademicStatusEditor";
import { ProfileSection } from "../ProfileSection";
import { ACADEMIC_STATUS_LABELS } from "./constants";

export default async function AcademicStatusContainer() {
  const result = await fetchAcademicStatus();

  if (result.status === 404) {
    notFound();
  }

  if (!result.success) {
    throw new Error(result.error ?? "Unable to load your academic status.");
  }

  const status = normalizeAcademicStatus(result.data);

  return (
    <ProfileSection
      icon={<GraduationCap className="size-4" />}
      title={ACADEMIC_STATUS_LABELS.title}
      description={ACADEMIC_STATUS_LABELS.description}
    >
      <AcademicStatusEditor initialStatus={status} />
    </ProfileSection>
  );
}
