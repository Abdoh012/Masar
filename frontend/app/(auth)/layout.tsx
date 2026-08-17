import type { ReactNode } from "react";

import { AuthPageShell, SignupDraftProvider } from "@/features/auth";

export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <AuthPageShell>
      <SignupDraftProvider>{children}</SignupDraftProvider>
    </AuthPageShell>
  );
}
