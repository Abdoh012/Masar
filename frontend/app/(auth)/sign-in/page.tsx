import type { Metadata } from "next";

import SignInContainer from "@/features/auth/components/sign-in/SignInContainer";

export const metadata: Metadata = {
  title: "Sign in",
};

interface PageProps {
  searchParams: Promise<{ sessionExpired?: string; error?: string }>;
}

export default async function SignInPage({ searchParams }: PageProps) {
  const { sessionExpired, error } = await searchParams;

  return (
    <SignInContainer sessionExpired={sessionExpired === "1"} error={error ?? ""} />
  );
}