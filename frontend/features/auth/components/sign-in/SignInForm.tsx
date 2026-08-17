"use client";

import Link from "next/link";

import { FIELD_CONFIG } from "../../shared/lib/constants";
import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { signIn } from "../../actions";
import { useFormFeedBack } from "../../shared/hooks/useFormFeedback";
import { SignInValues } from "../../types";

export function SignInForm() {
  const { formAction, state } = useFormFeedBack(signIn, null);

  const restoredValues =
    state && !state.success
      ? (state.data as SignInValues | undefined)
      : undefined;

  return (
    <form action={formAction}>
      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        placeholder={FIELD_CONFIG.email.placeholder}
        type={FIELD_CONFIG.email.type}
        defaultValue={restoredValues?.email}
      />

      <FormField
        name="password"
        label={FIELD_CONFIG.password.label}
        placeholder={FIELD_CONFIG.password.placeholder}
        type={FIELD_CONFIG.password.type}
      />

      <div className="mb-5">
        <Link
          href="/forgot-password"
          className="text-sm font-medium text-secondary-text hover:underline"
        >
          Forgot password?
        </Link>
      </div>

      <SubmitButton>Sign in</SubmitButton>
    </form>
  );
}
