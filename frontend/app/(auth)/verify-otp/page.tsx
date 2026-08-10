import type { Metadata } from "next";

import VerifyOtpContainer from "@/features/auth/components/verify-otp/VerifyOtpContainer";

export const metadata: Metadata = {
  title: "Verify email",
};

interface PageProps {
  searchParams: Promise<{ email?: string }>;
}

export default async function VerifyOtpPage({ searchParams }: PageProps) {
  const { email } = await searchParams;

  return <VerifyOtpContainer email={email ?? ""} />;
}