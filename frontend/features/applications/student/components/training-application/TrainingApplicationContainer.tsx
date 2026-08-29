"use client";

import { startTransition, useRef, useState } from "react";
import type { FormEvent } from "react";
import { notFound } from "next/navigation";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import type {
  ApplicationFormValues,
  CvFileState,
  EducationValues,
  PersonalInfoValues,
  TrainingApplicationStep,
  TrainingApplicationValues,
} from "../../types";
import { submitApplication } from "../../actions";
import { ApplicationPayloadFields } from "./ApplicationPayloadFields";
import { ApplicationSuccess } from "./ApplicationSuccess";
import { CvFileInput } from "./CvFileInput";
import { EducationFields } from "./EducationFields";
import { PersonalInfoFields } from "./PersonalInfoFields";
import { ProgressIndicator } from "./ProgressIndicator";
import { StepHeader } from "./StepHeader";
import { StepNavigation } from "./StepNavigation";
import { TrainingApplicationFields } from "./TrainingApplicationFields";
import { CV_FIELD_LABELS, INITIAL_VALUES } from "./constants";

interface TrainingApplicationContainerProps {
  listingId: string;
  listingTitle?: string;
  companyName?: string;
}

// TrainingApplicationContainer: the student's 3-step application wizard
// (Personal Information → Education → Training Application). Orchestrator only —
// owns the step, the form values (one useState per section), the CV file, and
// submission feedback, then composes the leaves. The whole form is deliberately
// controlled (structure rules §10 exception): steps must survive Back/Continue
// navigation, step 2 renders a field conditional on a radio, and step 3 is a
// chip multi-select — all of which need the container to see every value. One
// shared <form> swaps the active step's fields, so native `required` validation
// always gates exactly the rendered step's text fields. The full snapshot rides
// the form as hidden backend-named payload inputs; the CV rides the form's
// always-mounted CvFileInput carrier (name="cv") — sr-only, so CV presence is
// gated here (inline error under the step-1 field) instead of via a native
// bubble. Step 3 submits to submitApplication (POST /api/v1/applications) via
// useFormFeedback, which toasts backend message/fieldErrors and reports pending
// via isPending.
export function TrainingApplicationContainer({
  listingId,
  listingTitle,
  companyName,
}: TrainingApplicationContainerProps) {
  const { formAction, state, isPending } = useFormFeedBack(
    submitApplication,
    null,
  );

  const [step, setStep] = useState<TrainingApplicationStep>(1);
  const [values, setValues] = useState<ApplicationFormValues>(INITIAL_VALUES);
  const [cvFile, setCvFile] = useState<CvFileState | null>(null);
  const [cvError, setCvError] = useState<string | null>(null);
  const cvInputRef = useRef<HTMLInputElement>(null);

  if (!listingTitle) {
    notFound();
  }

  function updatePersonal(field: keyof PersonalInfoValues, value: string) {
    setValues((current) => ({
      ...current,
      personal: { ...current.personal, [field]: value },
    }));
  }

  function updateEducation(field: keyof EducationValues, value: string) {
    setValues((current) => ({
      ...current,
      education: { ...current.education, [field]: value },
    }));
  }

  function updateApplication(
    field: keyof TrainingApplicationValues,
    value: string | string[],
  ) {
    setValues((current) => ({
      ...current,
      application: { ...current.application, [field]: value },
    }));
  }

  function handleBack() {
    if (step > 1)
      setStep((current) => (current - 1) as TrainingApplicationStep);
  }

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    if (!cvFile) {
      setCvError(CV_FIELD_LABELS.requiredError);
      if (step > 1) setStep(1);
      return;
    }

    if (step < 3) {
      setStep((current) => (current + 1) as TrainingApplicationStep);
      return;
    }

    const payload = new FormData(event.currentTarget);
    // A useActionState dispatch called manually (not via <form action>) must
    // run inside a transition, or isPending never updates.
    startTransition(() => formAction(payload));
  }

  if (state?.success) {
    return (
      <ApplicationSuccess
        listingTitle={listingTitle}
        companyName={companyName ?? ""}
      />
    );
  }

  return (
    <div className="w-full rounded-lg border border-border bg-card p-5 shadow-sm sm:p-8">
      <div className="md:flex md:gap-6 lg:gap-8">
        <ProgressIndicator currentStep={step} />

        <form
          className="mt-8 min-w-0 flex-1 space-y-6 md:mt-0"
          onSubmit={handleSubmit}
        >
          <ApplicationPayloadFields values={values} listingId={listingId} />
          <CvFileInput
            ref={cvInputRef}
            onSelect={(file) => {
              setCvFile({ name: file.name, size: file.size });
              setCvError(null);
            }}
          />

          <Motion key={step} variants={fadeInUp}>
            <div className="space-y-6">
              <StepHeader step={step} />

              {step === 1 && (
                <PersonalInfoFields
                  values={values.personal}
                  cvFile={cvFile}
                  cvError={cvError}
                  onFieldChange={updatePersonal}
                  onCvOpenPicker={() => cvInputRef.current?.click()}
                  onCvRemove={() => {
                    if (cvInputRef.current) cvInputRef.current.value = "";
                    setCvFile(null);
                    setCvError(null);
                  }}
                />
              )}

              {step === 2 && (
                <EducationFields
                  values={values.education}
                  onFieldChange={updateEducation}
                />
              )}

              {step === 3 && (
                <TrainingApplicationFields
                  values={values.application}
                  onFieldChange={updateApplication}
                />
              )}
            </div>
          </Motion>

          <StepNavigation
            step={step}
            isSubmitting={isPending}
            onBack={handleBack}
            backHref={`/listings/${listingId}`}
          />
        </form>
      </div>
    </div>
  );
}
