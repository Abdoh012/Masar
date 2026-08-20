import type { Metadata } from "next";

import PasswordUpdatedContainer from "@/features/auth/components/password-updated/PasswordUpdatedContainer";

export const metadata: Metadata = {
  title: "Password updated",
};

export default function PasswordUpdatedPage() {
  return <PasswordUpdatedContainer />;
}
