import type { Metadata } from "next";

import ForgetPasswordContainer from "@/features/auth/components/forget-password/ForgetPasswordContainer";

export const metadata: Metadata = {
  title: "Forgot password",
};

export default async function ForgotPasswordPage() {
  return <ForgetPasswordContainer />;
}
