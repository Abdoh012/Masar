"use client";

import { ChevronsLeft, ChevronsRight } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { SIDEBAR_LABELS } from "./constants";
import type { SidebarVariant } from "../../types";

interface SidebarCollapseButtonProps {
  variant: SidebarVariant;
  onToggle: () => void;
}

// SidebarCollapseButton: the FR-012 expand/collapse control, mounted by the
// shell in the rail's top header strip (contract §3). Purely presentational —
// no state; the shell owns SidebarVariant and passes variant + onToggle.
export function SidebarCollapseButton({ variant, onToggle }: SidebarCollapseButtonProps) {
  const collapsed = variant === "collapsed";
  const Icon = collapsed ? ChevronsRight : ChevronsLeft;

  return (
    <Button
      type="button"
      variant="ghost"
      size="icon"
      aria-label={collapsed ? SIDEBAR_LABELS.expandSidebar : SIDEBAR_LABELS.collapseSidebar}
      onClick={onToggle}
    >
      <Icon strokeWidth={2} aria-hidden="true" />
    </Button>
  );
}