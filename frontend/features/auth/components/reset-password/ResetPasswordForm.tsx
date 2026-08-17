"use client";

import { FIELD_CONFIG } from "../../shared/lib/constants";
import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { resetPassword } from "../../actions";
import { useFormFeedBack } from "../../shared/hooks/useFormFeedback";

interface ResetPasswordFormProps {
  token: string;
  email: string;
}

export function ResetPasswordForm({ token, email }: ResetPasswordFormProps) {
  const { formAction } = useFormFeedBack(resetPassword, null);

  return (
    <form className="space-y-5" action={formAction}>
      <input type="hidden" name="token" value={token} />
      <input type="hidden" name="email" value={email} />

      <FormField
        name="password"
        label={FIELD_CONFIG.newPassword.label}
        type={FIELD_CONFIG.newPassword.type}
        placeholder={FIELD_CONFIG.newPassword.placeholder}
      />

      <FormField
        name="confirmPassword"
        label={FIELD_CONFIG.confirmPassword.label}
        type={FIELD_CONFIG.confirmPassword.type}
        placeholder={FIELD_CONFIG.confirmPassword.placeholder}
      />

      <SubmitButton>Reset password</SubmitButton>
    </form>
  );
}