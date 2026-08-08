import type { ReactNode } from "react";

// StudentLayout: TODO — nav shell (sidebar/header) for the student area.
// Compose it from features/*/{role}/components as those get built.
export default function StudentLayout({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-paper">{children}</div>;
}
