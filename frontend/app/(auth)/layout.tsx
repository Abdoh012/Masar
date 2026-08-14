import type { ReactNode } from "react";

import { AuthPageShell } from "@/features/auth";

export default function AuthLayout({ children }: { children: ReactNode }) {
  return <AuthPageShell>{children}</AuthPageShell>;
}
