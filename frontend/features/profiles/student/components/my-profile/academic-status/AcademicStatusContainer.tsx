"use client";

import { useState } from "react";
import { GraduationCap, Info } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { expandCollapse } from "@/shared/lib/animations";

import { useFilePreview } from "../../../hooks/useFilePreview";
import { ProfileFieldInput } from "../ProfileFieldInput";
import { ProfileSection } from "../ProfileSection";
import {
  INITIAL_ACADEMIC_LEVEL,
  INITIAL_GRADUATION_DATE,
} from "../constants";
import {
  ACADEMIC_STATUS_LABELS,
  GRADUATION_LABELS,
  STUDENT_HINT,
} from "./constants";
import { CertificateDropzone } from "./CertificateDropzone";
import { StatusSegmentedControl } from "./StatusSegmentedControl";
import type { AcademicLevel } from "../../../types";

// AcademicStatusContainer: the Student / Graduate switch and, for a graduate, the
// graduation date and certificate dropzone it reveals. All three are local state
// — the file in particular never leaves the browser. There is no academic-status
// API yet, so the section has no save control at all yet rather than a button
// that pretends to persist.
export default function AcademicStatusContainer() {
  const [level, setLevel] = useState<AcademicLevel>(INITIAL_ACADEMIC_LEVEL);
  const [graduationDate, setGraduationDate] = useState(INITIAL_GRADUATION_DATE);
  const { file, select, clear } = useFilePreview();

  const isGraduate = level === "graduate";

  return (
    <ProfileSection
      icon={GraduationCap}
      title={ACADEMIC_STATUS_LABELS.title}
      description={ACADEMIC_STATUS_LABELS.description}
    >
      <div className="space-y-5">
        <StatusSegmentedControl value={level} onChange={setLevel} />

        {isGraduate ? (
          <Motion
            // Keyed on the level so switching re-mounts the branch and the
            // height reveal replays each time, instead of the two branches
            // reusing one Motion instance and swapping content instantly.
            key="graduate"
            variants={expandCollapse}
            initial="hidden"
            animate="visible"
            className="space-y-5 overflow-hidden"
          >
            <div>
              <ProfileFieldInput
                id="graduation-date"
                label={GRADUATION_LABELS.field}
                type="month"
                value={graduationDate}
                onChange={setGraduationDate}
              />

              <p className="mt-1.5 text-xs text-muted-foreground">
                {GRADUATION_LABELS.hint}
              </p>
            </div>

            <CertificateDropzone
              file={file}
              onSelect={select}
              onClear={clear}
            />
          </Motion>
        ) : (
          <Motion
            key="student"
            variants={expandCollapse}
            initial="hidden"
            animate="visible"
            className="overflow-hidden"
          >
            <div className="flex items-start gap-3 rounded-xl border border-dashed border-border p-4">
              <span className="grid size-8 shrink-0 place-items-center rounded-full bg-primary-tint text-primary-text">
                <Info className="size-4" />
              </span>

              <div className="min-w-0">
                <p className="text-sm font-medium text-primary-text">
                  {STUDENT_HINT.title}
                </p>
                <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                  {STUDENT_HINT.body}
                </p>
              </div>
            </div>
          </Motion>
        )}
      </div>

      {/* A save control belongs here once the academic-status API exists — it
          would dispatch the updateAcademicStatus server action with the level,
          the graduation date, and the certificate file. Deliberately absent
          rather than inert, so the section never implies a write happened. */}
    </ProfileSection>
  );
}
