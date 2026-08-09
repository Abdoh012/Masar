import { SidebarClientShell } from "./SidebarClientShell";
import { SidebarBrand } from "./sidebar-brand/SidebarBrand";
import { SidebarNav } from "./sidebar-nav/SidebarNav";
import { SidebarFooter } from "./sidebar-footer/SidebarFooter";

import type { SidebarProps } from "../../types";

// Sidebar: server orchestrator (the feature's public surface, contract §1).
// Takes role, composes the server-rendered brand/nav/footer and passes the
// whole result as children into SidebarClientShell, which owns the
// rail/drawer state and chrome (R-8 — FR-024 holds: all sidebar content stays
// server-rendered; "use client" lives only on the small leaves that need it).
export function Sidebar({ role }: SidebarProps) {
  return (
    <SidebarClientShell role={role}>
      <SidebarBrand />
      <SidebarNav role={role} />
      <SidebarFooter />
    </SidebarClientShell>
  );
}