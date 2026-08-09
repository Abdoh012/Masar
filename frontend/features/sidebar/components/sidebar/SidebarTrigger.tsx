"use client";

import { Menu } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { SIDEBAR_LABELS } from "./constants";

interface SidebarTriggerProps {
  onOpen: () => void;
}

// SidebarTrigger: mobile (<lg) open-control for the drawer. Rendered by
// SidebarClientShell — never by layouts (research R-7/R-8); fixed top-left so
// small screens always have a navigation entry point (FR-016).
export function SidebarTrigger({ onOpen }: SidebarTriggerProps) {
  return (
    <Button
      type="button"
      variant="ghost"
      size="icon"
      aria-label={SIDEBAR_LABELS.openDrawer}
      onClick={onOpen}
      className="fixed left-4 top-4 z-30 border border-border bg-card shadow-card-sm lg:hidden"
    >
      <Menu strokeWidth={2} aria-hidden="true" />
    </Button>
  );
}