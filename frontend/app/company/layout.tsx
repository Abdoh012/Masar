import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";

// CompanyLayout: horizontal app shell for the company area — persistent
// Sidebar + page area. The sidebar owns its own UI/state; the page area
// adapts to the sidebar's width (no overlay). `pending-approval` inherits
// this shell (R-7 — uniform company sidebar, no special-casing).
export default function CompanyLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-dvh bg-background font-sans text-foreground">
      <Sidebar role="company" />
      <main className="flex flex-1 flex-col">{children}</main>
    </div>
  );
}