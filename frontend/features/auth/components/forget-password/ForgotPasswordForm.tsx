"use client";

import { FIELD_CONFIG } from "../../lib/constants";
import { FormField } from "../shared/FormField";
import { SubmitButton } from "../shared/SubmitButton";

export function ForgotPasswordForm() {
  return (
    <form className="space-y-5">
      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        type={FIELD_CONFIG.email.type}
        placeholder={FIELD_CONFIG.email.placeholder}
      />

      <SubmitButton>Send reset link</SubmitButton>
    </form>
  );
}
