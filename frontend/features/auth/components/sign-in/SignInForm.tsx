"use client";

import Link from "next/link";

import { FIELD_CONFIG } from "../../lib/constants";
import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";

export function SignInForm() {
  return (
    <form>
      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        placeholder={FIELD_CONFIG.email.placeholder}
        type={FIELD_CONFIG.email.type}
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
