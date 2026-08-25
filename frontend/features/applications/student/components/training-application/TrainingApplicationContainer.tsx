"use client";

import { useMemo, useState } from "react";
import { notFound } from "next/navigation";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type {
  ApplicationFormValues,
  CvFileState,
  EducationValues,
  PersonalInfoValues,
  TrainingApplicationStep,
  TrainingApplicationValues,
} from "../../types";
import { ApplicationSuccess } from "./ApplicationSuccess";
import { EducationFields } from "./EducationFields";
import { PersonalInfoFields } from "./PersonalInfoFields";
import { ProgressIndicator } from "./ProgressIndicator";
import { StepHeader } from "./StepHeader";
import { StepNavigation } from "./StepNavigation";
import { TrainingApplicationFields } from "./TrainingApplicationFields";
import {
  INITIAL_VALUES,
  MOCK_APPLY_TRAININGS,
  SUBMIT_DELAY_MS,
} from "./constants";

interface TrainingApplicationContainerProps {
  listingId: string;
}

// TrainingApplicationContainer: the student's 3-step application wizard
// (Personal Information → Education → Training Application). Orchestrator only —
// owns the step, the form values (one useState per section), the CV file, and
// the simulated submit state, then composes the leaves. The whole form is
// deliberately controlled (structure rules §10 exception): steps must survive
// Back/Continue navigation, step 2 renders a field conditional on a radio, and
// step 3 is a chip multi-select — all of which need the container to see every
// value. One shared <form> swaps the active step's fields, so native `required`
// validation always gates exactly the rendered step. Submission is UI-only:
// a timeout simulates the backend call, then the Success panel renders.
export function TrainingApplicationContainer({ listingId }: TrainingApplicationContainerProps) {
  const listing = MOCK_APPLY_TRAININGS[listingId];

  const [step, setStep] = useState<TrainingApplicationStep>(1);
  const [values, setValues] = useState<ApplicationFormValues>(INITIAL_VALUES);
  const [cvFile, setCvFile] = useState<CvFileState | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  const validListing = useMemo(() => listing ?? null, [listing]);

  if (!validListing) {
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
    if (step > 1) setStep((current) => (current - 1) as TrainingApplicationStep);
  }

  function handleSubmit() {
    if (step < 3) {
      setStep((current) => (current + 1) as TrainingApplicationStep);
      return;
    }

    setIsSubmitting(true);
    window.setTimeout(() => {
      setIsSubmitting(false);
      setSubmitted(true);
    }, SUBMIT_DELAY_MS);
  }

  if (submitted) {
    return (
      <ApplicationSuccess
        specialization={validListing.specialization}
        companyName={validListing.companyName}
      />
    );
  }

  return (
    <div className="w-full rounded-lg border border-border bg-card p-5 shadow-sm sm:p-8">
<<<<<<< HEAD
      <ProgressIndicator currentStep={step} />

      <form
        className="mt-8 space-y-6"
        onSubmit={(event) => {
          event.preventDefault();
          handleSubmit();
        }}
      >
        <Motion key={step} variants={fadeInUp}>
          <div className="space-y-6">
            <StepHeader step={step} />

            {step === 1 ? (
              <PersonalInfoFields
                values={values.personal}
                cvFile={cvFile}
                onFieldChange={updatePersonal}
                onCvSelect={(file) => setCvFile({ name: file.name, size: file.size })}
                onCvRemove={() => setCvFile(null)}
              />
            ) : null}

            {step === 2 ? (
              <EducationFields values={values.education} onFieldChange={updateEducation} />
            ) : null}

            {step === 3 ? (
              <TrainingApplicationFields
                values={values.application}
                onFieldChange={updateApplication}
              />
            ) : null}
          </div>
        </Motion>

        <StepNavigation
          step={step}
          isSubmitting={isSubmitting}
          onBack={handleBack}
          backHref={`/listings/${listingId}`}
        />
      </form>
=======
      <div className="md:flex md:gap-6 lg:gap-8">
        <ProgressIndicator currentStep={step} />

        <form
          className="mt-8 min-w-0 flex-1 space-y-6 md:mt-0"
          onSubmit={(event) => {
            event.preventDefault();
            handleSubmit();
          }}
        >
          <Motion key={step} variants={fadeInUp}>
            <div className="space-y-6">
              <StepHeader step={step} />

              {step === 1 ? (
                <PersonalInfoFields
                  values={values.personal}
                  cvFile={cvFile}
                  onFieldChange={updatePersonal}
                  onCvSelect={(file) => setCvFile({ name: file.name, size: file.size })}
                  onCvRemove={() => setCvFile(null)}
                />
              ) : null}

              {step === 2 ? (
                <EducationFields values={values.education} onFieldChange={updateEducation} />
              ) : null}

              {step === 3 ? (
                <TrainingApplicationFields
                  values={values.application}
                  onFieldChange={updateApplication}
                />
              ) : null}
            </div>
          </Motion>

          <StepNavigation
            step={step}
            isSubmitting={isSubmitting}
            onBack={handleBack}
            backHref={`/listings/${listingId}`}
          />
        </form>
      </div>
>>>>>>> dev
    </div>
  );
}