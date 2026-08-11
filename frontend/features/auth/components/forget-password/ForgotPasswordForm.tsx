"use client";

import type { FormEvent } from "react";
import { useRouter } from "next/navigation";

import { FIELD_CONFIG } from "../../lib/constants";
import { FormField } from "../../shared/components/FormField";
import { SubmitButton } from "../../shared/components/SubmitButton";

export function ForgotPasswordForm() {
  const router = useRouter();

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const formData = new FormData(event.currentTarget);
    const email = String(formData.get("email") ?? "").trim();

    // UI-only handoff to the verification step — no email is sent, no API
    // call is made; the address is just forwarded so the OTP page can
    // display it.
    router.push(`/verify-otp?email=${encodeURIComponent(email)}`);
  }

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
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
