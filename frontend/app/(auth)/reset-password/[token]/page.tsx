import type { Metadata } from "next";
import Link from "next/link";

import { AuthCard, ResetPasswordForm } from "@/features/auth";

export const metadata: Metadata = {
  title: "Reset password",
};

interface PageProps {
  params: Promise<{ token: string }>;
}

export default async function ResetPasswordPage({ params }: PageProps) {
  const { token } = await params;

  return (
    <AuthCard
      title="Set a new password"
      description="Choose a strong password to secure your account."
      footer={
        <p className="text-sm text-muted-foreground">
          Changed your mind?{" "}
          <Link href="/sign-in" className="font-medium text-secondary-text hover:underline">
            Sign in
          </Link>
        </p>
      }
    >
      <ResetPasswordForm token={token} />
    </AuthCard>
  );
}
