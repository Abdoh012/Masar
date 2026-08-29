"use client";

import { FIELD_CONFIG } from "../../shared/lib/constants";
import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { forgotPassword } from "../../actions";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

export function ForgotPasswordForm() {
  const { formAction } = useFormFeedBack(forgotPassword, null);

  return (
    <form className="space-y-5" action={formAction}>
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