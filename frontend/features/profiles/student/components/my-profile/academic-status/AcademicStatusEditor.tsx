"use client";

import { useCallback, useRef, useState } from "react";
import { Info } from "lucide-react";

import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";
import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";

import { updateAcademicStatus, removeAcademicStatusCertificate } from "../../../actions";
import { ProfileFieldInput } from "../ProfileFieldInput";
import { StatusSegmentedControl } from "./StatusSegmentedControl";
import { CertificateDropzone } from "./CertificateDropzone";
import { useFilePreview } from "../../../hooks/useFilePreview";
import {
  CERTIFICATE_UPLOAD_LABELS,
  GRADUATION_FORM_LABELS,
  GRADUATION_LABELS,
  GRADUATION_VALIDATION,
  STUDENT_HINT,
} from "./constants";
import type { AcademicStatusData } from "../../../types";
import { AccountSubmitButton } from "../account-security/AccountSubmitButton";

// AcademicStatusEditor: the interactive half of the academic-status section. The
// server container owns the read; this leaf owns every piece of state — the
// student/graduate level, the graduation date, the locally picked certificate
// file — and the two server actions (save + certificate removal).
//
// The save form is a real <form action> whose File input lives inside it, so
// useActionState/FormData picks up graduation_certificate on submit. The
// dropzone's Browse button clicks that same input via fileInputRef.
export function AcademicStatusEditor({
  initialStatus,
}: {
  initialStatus: AcademicStatusData;
}) {
  const { formAction: saveFormAction, state: saveState, isPending: savePending } =
    useFormFeedBack(updateAcademicStatus, null);

  const { formAction: removeFormAction } = useFormFeedBack(
    removeAcademicStatusCertificate,
    null,
  );
  const removeFormRef = useRef<HTMLFormElement>(null);

  const fileInputRef = useRef<HTMLInputElement | null>(null);
  const { file: localPick, select: selectFile, clear: clearPreview } =
    useFilePreview();

  const [level, setLevel] = useState<"student" | "graduate">(
    initialStatus.academicStatus,
  );
  const [graduationDate, setGraduationDate] = useState(
    initialStatus.graduationDate ?? "",
  );

  const isGraduate = level === "graduate";
  const serverCert = initialStatus.graduationCertificate;
  const hasCertificate = serverCert != null || localPick != null;

  // --- Save form handlers -------------------------------------------------

  const handleFileChange = useCallback(
    (event: React.ChangeEvent<HTMLInputElement>) => {
      const picked = event.target.files?.[0];
      if (picked) {
        selectFile(picked);
      }
      // Reset so re-picking the same file fires another change event.
      event.target.value = "";
    },
    [selectFile],
  );

  const clearLocalFile = useCallback(() => {
    clearPreview();
    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  }, [clearPreview]);

  // Client-side gate: graduate requires a date AND a certificate (server or
  // local). Inline errors render under the relevant field.
  const dateMissing = isGraduate && graduationDate === "";
  const certMissing = isGraduate && !hasCertificate;
  const canSave = !dateMissing && !certMissing;

  // Echo a successful save back into local state so the UI reflects it without
  // waiting for the revalidation-driven server re-render.
  if (saveState?.success && saveState.data) {
    const raw = saveState.data as {
      academic_status?: string;
      graduation_date?: string;
    };
    if (
      raw.academic_status === "student" ||
      raw.academic_status === "graduate"
    ) {
      setLevel(raw.academic_status);
    }
    setGraduationDate(raw.graduation_date ?? "");
  }

  const removeServerCert = () => {
    removeFormRef.current?.requestSubmit();
  };

  const now = new Date();
  const monthMin = "1950-01";
  const monthMax = `${now.getFullYear() + 10}-12`;

  return (
    <div className="space-y-5">
      <StatusSegmentedControl value={level} onChange={setLevel} />

      {/* Hidden field the save action reads off FormData. */}
      <input name="academic_status" type="hidden" value={level} />

      {isGraduate ? (
        <div className="space-y-5">
          <div>
            <ProfileFieldInput
              id="graduation-date"
              label={GRADUATION_LABELS.field}
              name="graduation_date"
              type="month"
              value={graduationDate}
              onChange={setGraduationDate}
              min={monthMin}
              max={monthMax}
            />
            {dateMissing ? (
              <p className="mt-1 text-xs text-error-fg">
                {GRADUATION_VALIDATION.dateRequired}
              </p>
            ) : (
              <p className="mt-1.5 text-xs text-muted-foreground">
                {GRADUATION_LABELS.hint}
              </p>
            )}
          </div>

          <div className="space-y-2">
            <Label className="text-sm font-medium text-foreground">
              {CERTIFICATE_UPLOAD_LABELS.label}
            </Label>
            <p className="text-xs text-muted-foreground">
              Upload your graduation certificate (PDF, PNG or JPG up to 5 MB).
            </p>

            <CertificateDropzone
              serverCert={serverCert}
              localPick={localPick}
              fileInputRef={fileInputRef}
              onLocalSelect={selectFile}
              onLocalClear={clearLocalFile}
              onRemoveServerCert={removeServerCert}
            />

            {certMissing && serverCert == null && localPick == null ? (
              <p className="mt-1 text-xs text-error-fg">
                {GRADUATION_VALIDATION.certificateRequired}
              </p>
            ) : null}
          </div>

          {/* The save form carries the hidden level field + the month input +
              the real file input (ref'd by the dropzone's Browse button). */}
          <form action={saveFormAction} className="space-y-4">
            <input
              ref={fileInputRef}
              name="graduation_certificate"
              type="file"
              accept="application/pdf,image/png,image/jpeg,image/webp"
              className="sr-only"
              onChange={handleFileChange}
            />

            <div className="flex justify-end">
              <AccountSubmitButton
                label={GRADUATION_FORM_LABELS.saveGraduate}
                pendingLabel={GRADUATION_FORM_LABELS.saving}
                disabled={!canSave || savePending}
              />
            </div>
          </form>

          {/* Hidden form that removes the server-stored certificate. */}
          <form
            ref={removeFormRef}
            action={removeFormAction}
            className="hidden"
          />
        </div>
      ) : (
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
      )}
    </div>
  );
}
