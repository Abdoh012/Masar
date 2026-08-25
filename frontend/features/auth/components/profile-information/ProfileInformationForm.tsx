"use client";

import { useEffect, useState } from "react";

import type { SignupDraft, SignupStepTwoValues } from "../../types";
import {
  COMPANY_PROFILE_FIELDS,
  STUDENT_PROFILE_FIELDS,
} from "../../shared/lib/constants";
import {
  useLookupOptions,
  STUDY_FIELDS_ENDPOINT,
  SPECIALIZATIONS_ENDPOINT,
} from "../../shared/hooks/useLookupOptions";
import { useSpecializationOptions } from "../../shared/hooks/useSpecializationOptions";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { ProfileField } from "./ProfileField";
import { ProfileSelectField } from "./ProfileSelectField";
import { FieldErrorList } from "../../shared/components/FieldErrorList";
import { signup } from "../../actions";
import { useFormFeedBack } from "../../shared/hooks/useFormFeedback";

interface ProfileInformationFormProps {
  draft: SignupDraft;
}

export function ProfileInformationForm({ draft }: ProfileInformationFormProps) {
  const { formAction, state } = useFormFeedBack(signup, null);
  const isCompany = draft.role === "company";

  // Profile values the signup action echoes back when the register call
  // rejects. React 19 resets uncontrolled inputs after a form action, so the
  // fields are re-seeded from these; passwords are never echoed.
  const restoredValues =
    state && !state.success
      ? (state.data as SignupStepTwoValues | undefined)
      : undefined;

  // Backend field errors from the rejected register call (keyed by the
  // register payload field names). Password errors render in their own block
  // below, since the password itself is a hidden step-1 input on this step.
  const fieldErrors = state?.fieldErrors ?? {};

  // Lookup options fetched dynamically from the backend.
  const fieldOptions = useLookupOptions(STUDY_FIELDS_ENDPOINT);
  const industryOptions = useLookupOptions(SPECIALIZATIONS_ENDPOINT);

  // Student specialization depends on the selected study field.
  const [selectedFieldId, setSelectedFieldId] = useState<string | null>(null);
  const specializationOptions = useSpecializationOptions(selectedFieldId);
  const [selectedSpecialization, setSelectedSpecialization] = useState<
    string | undefined
  >(restoredValues?.specialist);

  // Clear specialization when the field changes so stale values aren't submitted.
  // eslint-disable-next-line react-hooks/exhaustive-deps -- selectedFieldId drives the reset
  useEffect(() => {
    setSelectedSpecialization(undefined);
  }, [selectedFieldId]);

  return (
    <form className="space-y-5" action={formAction}>
      <input type="hidden" name="role" value={draft.role} />
      <input type="hidden" name="email" value={draft.email} />
      <input type="hidden" name="password" value={draft.password} />
      <input
        type="hidden"
        name="acceptTerms"
        value={draft.acceptTerms ? "on" : ""}
      />
      <input
        type="hidden"
        name={isCompany ? "companyName" : "fullName"}
        value={isCompany ? (draft.companyName ?? "") : (draft.fullName ?? "")}
      />

      {fieldErrors.password?.length ? (
        <div className="space-y-1.5 rounded-md bg-error-bg p-3">
          <p className="text-sm font-medium text-error-fg">Password</p>
          <FieldErrorList errors={fieldErrors.password} />
        </div>
      ) : null}

      {isCompany ? (
        <>
          <ProfileSelectField
            name="industry"
            label={COMPANY_PROFILE_FIELDS.industry.label}
            placeholder={COMPANY_PROFILE_FIELDS.industry.placeholder}
            defaultValue={restoredValues?.industry}
            options={industryOptions.options}
            loading={industryOptions.loading}
            error={industryOptions.error}
            errors={fieldErrors.industry}
          />

          <ProfileField
            name="description"
            label={COMPANY_PROFILE_FIELDS.description.label}
            placeholder={COMPANY_PROFILE_FIELDS.description.placeholder}
            optional
            defaultValue={restoredValues?.description}
            errors={fieldErrors.description}
          />
        </>
      ) : (
        <>
          <ProfileSelectField
            name="userField"
            label={STUDENT_PROFILE_FIELDS.userField.label}
            placeholder={STUDENT_PROFILE_FIELDS.userField.placeholder}
            value={selectedFieldId ?? ""}
            onValueChange={(value) => setSelectedFieldId(value || null)}
            options={fieldOptions.options}
            loading={fieldOptions.loading}
            error={fieldOptions.error}
            errors={fieldErrors.faculty}
          />

          <ProfileSelectField
            name="specialist"
            label={STUDENT_PROFILE_FIELDS.specialist.label}
            placeholder={STUDENT_PROFILE_FIELDS.specialist.placeholder}
            value={selectedSpecialization ?? ""}
            onValueChange={setSelectedSpecialization}
            disabled={!selectedFieldId}
            options={specializationOptions.options.map((s) => s.name)}
            loading={specializationOptions.loading}
            error={specializationOptions.error}
            errors={fieldErrors.specialization}
          />
        </>
      )}

      <SubmitButton>Register</SubmitButton>
    </form>
  );
}
