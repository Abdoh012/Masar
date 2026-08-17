"use client";

import { useTransition } from "react";

import { showError, showSuccess } from "@/shared/lib/notifications";
import { resendOtp } from "../../actions";

interface ResendOtpButtonProps {
  email: string;
}

export function ResendOtpButton({ email }: ResendOtpButtonProps) {
  const [isPending, startTransition] = useTransition();

  function handleResend() {
    const formData = new FormData();
    formData.set("email", email);

    startTransition(async () => {
      const result = await resendOtp(null, formData);
      if (result?.success) {
        showSuccess(result.message || "A new code has been sent to your email.");
      } else {
        showError(result?.error || "Unable to resend the code.");
      }
    });
  }

  return (
    <button
      type="button"
      onClick={handleResend}
      disabled={isPending}
      className="cursor-pointer font-medium text-secondary-text hover:underline disabled:cursor-not-allowed disabled:opacity-50"
    >
      {isPending ? "Sending..." : "Resend code"}
    </button>
  );
}