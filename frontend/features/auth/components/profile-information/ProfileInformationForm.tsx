"use client";

import type { FormEvent } from "react";
import { useRouter } from "next/navigation";

import { PROFILE_INFO_FIELDS } from "../../lib/constants";
import { SubmitButton } from "../shared/SubmitButton";
import { ProfileField } from "./ProfileField";

export function ProfileInformationForm() {
  const router = useRouter();

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    // UI-only handoff to sign-in — no account is created, no API call is
    // made; the flow simply finishes at the existing auth screen.
    router.push("/sign-in");
  }

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
      <ProfileField
        name="userField"
        label={PROFILE_INFO_FIELDS.userField.label}
        placeholder={PROFILE_INFO_FIELDS.userField.placeholder}
      />

      <ProfileField
        name="specialist"
        label={PROFILE_INFO_FIELDS.specialist.label}
        placeholder={PROFILE_INFO_FIELDS.specialist.placeholder}
      />

      <ProfileField
        name="university"
        label={PROFILE_INFO_FIELDS.university.label}
        placeholder={PROFILE_INFO_FIELDS.university.placeholder}
      />

      <ProfileField
        name="description"
        label={PROFILE_INFO_FIELDS.description.label}
        placeholder={PROFILE_INFO_FIELDS.description.placeholder}
        optional
      />

      <SubmitButton>Register</SubmitButton>
    </form>
  );
}
