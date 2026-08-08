import Link from "next/link";

import { AuthCard, ResetPasswordForm } from "@/features/auth";

interface ResetPasswordContainerProps {
  token: string;
}

export default function ResetPasswordContainer({
  token,
}: ResetPasswordContainerProps) {
  return (
    <AuthCard
      title="Set a new password"
      description="Choose a strong password to secure your account."
      footer={
        <p className="text-sm text-muted-foreground">
          Changed your mind?{" "}
          <Link
            href="/sign-in"
            className="font-medium text-secondary-text hover:underline"
          >
            Sign in
          </Link>
        </p>
      }
    >
      <ResetPasswordForm token={token} />
    </AuthCard>
  );
}
