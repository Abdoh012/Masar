"use client";

import type { FormEvent } from "react";
import { useRouter } from "next/navigation";

import {
  OTP_LENGTH,
  RESET_PASSWORD_PLACEHOLDER_TOKEN,
} from "../../lib/constants";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { OtpInput } from "./OtpInput";

// OtpForm: the UI-only verification step. Submitting navigates to the
// existing reset-password screen with a placeholder token — no API call,
// no OTP verification, no resend wiring yet.
export function OtpForm() {
  const router = useRouter();

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    router.push(`/reset-password/${RESET_PASSWORD_PLACEHOLDER_TOKEN}`);
  }

  return (
    <form className="space-y-5" onSubmit={handleSubmit}>
      <OtpInput />

      <div className="text-center text-sm text-muted-foreground">
        Enter the {OTP_LENGTH}-digit code we sent to your email.
      </div>

      <SubmitButton>Verify</SubmitButton>

      <p className="text-center text-sm text-muted-foreground">
        Didn&apos;t receive the code?{" "}
        <span className="cursor-pointer font-medium text-secondary-text hover:underline">
          Resend code
        </span>
      </p>
    </form>
  );
}
