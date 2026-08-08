import SignInContainer from "@/features/auth/components/sign-in/SignInContainer";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sign in",
};

export default function SignInPage() {
  return <SignInContainer />;
}
