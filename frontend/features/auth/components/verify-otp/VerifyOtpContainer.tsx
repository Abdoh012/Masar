import Link from "next/link";

import { AuthCard, OtpForm } from "@/features/auth";

// The email passed in from the forgot-password step (via query param) and
// shown in the card description so the user knows where the code went.
interface VerifyOtpContainerProps {
  email: string;
}

export default function VerifyOtpContainer({ email }: VerifyOtpContainerProps) {
  return (
    <AuthCard
      title="Enter verification code"
      description={
        <>
          We sent a code to{" "}
          <span className="font-medium text-primary-text">{email}</span>.
        </>
      }
      footer={
        <p className="text-sm text-muted-foreground">
          Remembered your password?{" "}
          <Link
            href="/sign-in"
            className="font-medium text-secondary-text hover:underline"
          >
            Sign in
          </Link>
        </p>
      }
    >
      <OtpForm />
    </AuthCard>
  );
}