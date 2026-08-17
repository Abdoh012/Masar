import type { Metadata } from "next";

import ResetPasswordContainer from "@/features/auth/components/reset-password/ResetPasswordContainer";

export const metadata: Metadata = {
  title: "Reset password",
};

interface PageProps {
  params: Promise<{ token: string }>;
  searchParams: Promise<{ email?: string }>;
}

export default async function ResetPasswordPage({
  params,
  searchParams,
}: PageProps) {
  const { token } = await params;
  const { email } = await searchParams;

  return <ResetPasswordContainer token={token} email={email ?? ""} />;
}