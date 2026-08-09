"use client";

import { useState, type ReactNode } from "react";

import { cn } from "@/shared/lib/utils";

import { RAIL_WIDTH, SIDEBAR_IDS } from "./constants";
import { SidebarCollapseButton } from "./SidebarCollapseButton";
import { SidebarTrigger } from "./SidebarTrigger";
import { SidebarDrawer } from "./sidebar-drawer/SidebarDrawer";
import type { SidebarVariant } from "../../types";
import type { Role } from "@/types/auth";

interface SidebarClientShellProps {
  role: Role;
  children: ReactNode;
}

// SidebarClientShell: client leaf owning the layout-wide sidebar state —
// SidebarVariant (desktop rail) and SidebarDrawerState (mobile drawer). It
// receives 100% server-rendered sidebar content as children (R-8) and only
// provides the chrome: the rail, its internal Collapse/Trigger controls, and
// the drawer. No sidebar content is authored here.
export function SidebarClientShell({ children }: SidebarClientShellProps) {
  const [variant, setVariant] = useState<SidebarVariant>("expanded");
  const [drawerOpen, setDrawerOpen] = useState(false);

  return (
    <>
      <aside
        id={SIDEBAR_IDS.rail}
        data-variant={variant}
        className={cn(
          "sticky top-0 hidden h-dvh flex-col border-r border-border bg-background font-sans text-foreground transition-[width] duration-200 motion-reduce:transition-none lg:flex",
          RAIL_WIDTH[variant],
        )}
      >
        <div className="flex h-12 shrink-0 items-center justify-end border-b border-border px-2">
          <SidebarCollapseButton
            variant={variant}
            onToggle={() => setVariant((v) => (v === "expanded" ? "collapsed" : "expanded"))}
          />
        </div>
        <div className="flex min-h-0 flex-1 flex-col">{children}</div>
      </aside>

      <SidebarTrigger onOpen={() => setDrawerOpen(true)} />

      <SidebarDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)}>
        {children}
      </SidebarDrawer>
    </>
  );
}