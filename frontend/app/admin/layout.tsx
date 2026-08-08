import type { ReactNode } from "react";

// AdminLayout: TODO — nav shell (sidebar/header) for the admin area.
// Compose it from features/*/{role}/components as those get built.
export default function AdminLayout({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-paper">{children}</div>;
}
