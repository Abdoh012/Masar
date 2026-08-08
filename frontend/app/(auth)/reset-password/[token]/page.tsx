import type { Metadata } from "next";

import ResetPasswordContainer from "@/features/auth/components/reset-password/ResetPasswordContainer";

export const metadata: Metadata = {
  title: "Reset password",
};

interface PageProps {
  params: Promise<{ token: string }>;
}

export default async function ResetPasswordPage({ params }: PageProps) {
  const { token } = await params;

  return <ResetPasswordContainer token={token} />;
}
