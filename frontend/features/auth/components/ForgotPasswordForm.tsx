"use client";

import { useActionState } from "react";

import { Input } from "@/shared/components/ui/input";
import { initialActionState } from "@/types/server-action";

import { forgotPassword } from "@/features/auth/actions";
import { FIELD_CONFIG } from "@/features/auth/lib/constants";
import { FormAlert } from "@/features/auth/components/FormAlert";
import { FormField } from "@/features/auth/components/shared/FormField";
import { SubmitButton } from "@/features/auth/components/shared/SubmitButton";
import { SuccessPanel } from "@/features/auth/components/SuccessPanel";

export function ForgotPasswordForm() {
  const [state, formAction] = useActionState(
    forgotPassword,
    initialActionState,
  );

  if (state.success) {
    return (
      <SuccessPanel
        title="Check your inbox"
        message="If an account exists with that email, you'll find a reset link there. The link expires in an hour."
      />
    );
  }

  return (
    <form action={formAction} className="space-y-5" noValidate>
      {state.message ? <FormAlert message={state.message} /> : null}

      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        hint="We'll send the reset link to this address."
        error={state.fieldErrors?.email?.[0]}
      >
        <Input
          name="email"
          type="email"
          placeholder={FIELD_CONFIG.email.placeholder}
          autoComplete={FIELD_CONFIG.email.autoComplete}
        />
      </FormField>

      <SubmitButton>Send reset link</SubmitButton>
    </form>
  );
}
