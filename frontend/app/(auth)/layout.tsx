import type { ReactNode } from "react";

// AuthLayout: TODO — nav shell (sidebar/header) for the auth area.
// Compose it from features/*/{role}/components as those get built.
export default function AuthLayout({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-paper">{children}</div>;
}
