import Link from "next/link";

import { AuthCard, ForgotPasswordForm } from "@/features/auth";

export default function ForgetPasswordContainer() {
  return (
    <AuthCard
      title="Reset your password"
      description="Enter your account email and we'll send you a reset link."
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
      <ForgotPasswordForm />
    </AuthCard>
  );
}
