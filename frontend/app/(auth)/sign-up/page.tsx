import SignUpContainer from "@/features/auth/components/sign-up/SignUpContainer";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sign up",
};

export default function SignUpPage() {
  return <SignUpContainer />;
}
