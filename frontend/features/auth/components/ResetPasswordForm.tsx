"use client";

import Link from "next/link";
import { useActionState } from "react";

import { Button } from "@/shared/components/ui/button";
import { initialActionState } from "@/types/server-action";

import { resetPassword } from "@/features/auth/actions";
import { FIELD_CONFIG } from "@/features/auth/lib/constants";
import { FormAlert } from "@/features/auth/components/FormAlert";
// import { PasswordField } from "@/features/auth/components/PasswordField";
import { SubmitButton } from "@/features/auth/components/shared/SubmitButton";
import { SuccessPanel } from "@/features/auth/components/SuccessPanel";

interface ResetPasswordFormProps {
  token: string;
}

export function ResetPasswordForm({ token }: ResetPasswordFormProps) {
  const [state, formAction] = useActionState(resetPassword, initialActionState);

  if (state.success) {
    return (
      <SuccessPanel
        title="Password updated"
        message="Your password has been reset. Sign in with your new password."
      >
        <Button asChild className="mt-1">
          <Link href="/sign-in">Back to sign in</Link>
        </Button>
      </SuccessPanel>
    );
  }

  return (
    <form action={formAction} className="space-y-5" noValidate>
      <input type="hidden" name="token" value={token} />

      {state.message ? <FormAlert message={state.message} /> : null}

      {/* <PasswordField
        name="password"
        label={FIELD_CONFIG.newPassword.label}
        placeholder={FIELD_CONFIG.newPassword.placeholder}
        autoComplete={FIELD_CONFIG.newPassword.autoComplete}
        hint={FIELD_CONFIG.password.hint}
        error={state.fieldErrors?.password?.[0]}
      />

      <PasswordField
        name="confirmPassword"
        label={FIELD_CONFIG.confirmPassword.label}
        placeholder={FIELD_CONFIG.confirmPassword.placeholder}
        autoComplete={FIELD_CONFIG.confirmPassword.autoComplete}
        error={state.fieldErrors?.confirmPassword?.[0]}
      /> */}

      <SubmitButton>Reset password</SubmitButton>
    </form>
  );
}
