import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";

// StudentLayout: horizontal app shell for the student area — persistent
// Sidebar + page area. The sidebar owns its own UI/state; the page area
// adapts to the sidebar's width (no overlay).
export default function StudentLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-dvh bg-background font-sans text-foreground">
      <Sidebar role="student" />
      <main className="flex flex-1 flex-col">{children}</main>
    </div>
  );
}