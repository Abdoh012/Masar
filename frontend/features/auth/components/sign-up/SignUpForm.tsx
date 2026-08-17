"use client";

import type { FormEvent } from "react";
import { useState } from "react";

import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { RoleSelector } from "./role-selector/RoleSelector";
import { FIELD_CONFIG } from "../../shared/lib/constants";
import Footer from "./footer/Footer";
import { stageSignup } from "../../actions";
import { useFormFeedBack } from "../../shared/hooks/useFormFeedback";
import { useSignupDraft } from "../../shared/hooks/useSignupDraft";
import type { SignupStepOneValues } from "../../types";

export function SignUpForm() {
  const [role, setRole] = useState<"student" | "company">("student");
  const isCompany = role === "company";

  const { formAction, state } = useFormFeedBack(stageSignup, null);

  const { saveDraft } = useSignupDraft();

  const restoredValues =
    state && !state.success
      ? (state.data as SignupStepOneValues | undefined)
      : undefined;

  // Mirror the step-1 basics into the in-memory draft so the step-2 form
  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    const data = new FormData(event.currentTarget);
    saveDraft({
      role:
        String(data.get("role") ?? "student") === "company"
          ? "company"
          : "student",
      fullName: isCompany ? undefined : String(data.get("fullName") ?? ""),
      companyName: isCompany
        ? String(data.get("companyName") ?? "")
        : undefined,
      email: String(data.get("email") ?? ""),
      password: String(data.get("password") ?? ""),
      acceptTerms: data.get("terms") === "on",
    });
  }

  return (
    <form className="space-y-5" action={formAction} onSubmit={handleSubmit}>
      <RoleSelector value={role} onChange={setRole} />
      <input type="hidden" name="role" value={role} />

      <FormField
        name="fullName"
        label={FIELD_CONFIG.fullName.label}
        type={FIELD_CONFIG.fullName.type}
        placeholder={FIELD_CONFIG.fullName.placeholder}
        defaultValue={restoredValues?.fullName}
      />

      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        type={FIELD_CONFIG.email.type}
        placeholder={FIELD_CONFIG.email.placeholder}
        defaultValue={restoredValues?.email}
      />

      <FormField
        name="password"
        type={FIELD_CONFIG.password.type}
        label={FIELD_CONFIG.password.label}
        placeholder={FIELD_CONFIG.password.placeholder}
      />

      <FormField
        name="confirmPassword"
        type={FIELD_CONFIG.confirmPassword.type}
        label={FIELD_CONFIG.confirmPassword.label}
        placeholder={FIELD_CONFIG.confirmPassword.placeholder}
      />

      {isCompany ? (
        <FormField
          name="companyName"
          label={FIELD_CONFIG.companyName.label}
          type={FIELD_CONFIG.companyName.type}
          placeholder={FIELD_CONFIG.companyName.placeholder}
          defaultValue={restoredValues?.companyName}
        />
      ) : null}

      <Footer />

      <SubmitButton>Next</SubmitButton>
    </form>
  );
}
