import type { ReactNode } from "react";

// CompanyLayout: TODO — nav shell (sidebar/header) for the company area.
// Compose it from features/*/{role}/components as those get built.
export default function CompanyLayout({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-paper">{children}</div>;
}
