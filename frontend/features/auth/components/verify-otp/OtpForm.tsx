"use client";

import { OTP_LENGTH } from "../../shared/lib/constants";
import { SubmitButton } from "../../shared/components/SubmitButton";
import { OtpInput } from "./OtpInput";
import { ResendOtpButton } from "./ResendOtpButton";
import { verifyOtp } from "../../actions";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

interface OtpFormProps {
  email: string;
}

export function OtpForm({ email }: OtpFormProps) {
  const { formAction } = useFormFeedBack(verifyOtp, null);

  return (
    <form className="space-y-5" action={formAction}>
      <input type="hidden" name="email" value={email} />

      <OtpInput />

      <div className="text-center text-sm text-muted-foreground">
        Enter the {OTP_LENGTH}-digit code we sent to your email.
      </div>

      <SubmitButton>Verify</SubmitButton>

      <p className="text-center text-sm text-muted-foreground">
        Didn&apos;t receive the code? <ResendOtpButton email={email} />
      </p>
    </form>
  );
}