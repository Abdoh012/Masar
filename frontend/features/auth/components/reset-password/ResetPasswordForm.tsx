"use client";

import { FIELD_CONFIG } from "../../lib/constants";
import { FormField } from "../shared/FormField";
import { SubmitButton } from "../shared/SubmitButton";

interface ResetPasswordFormProps {
  token: string;
}

export function ResetPasswordForm({ token }: ResetPasswordFormProps) {
  return (
    <form className="space-y-5">
      <input type="hidden" name="token" value={token} />

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
