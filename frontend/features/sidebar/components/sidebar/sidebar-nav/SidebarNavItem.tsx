"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";

import { cn } from "@/shared/lib/utils";
import { Button } from "@/shared/components/ui/button";

import { ICON_MAP } from "../../../lib/icon-map";
import { SidebarTooltip } from "../sidebar-tooltip/SidebarTooltip";
import type { SidebarNavItemProps } from "../../../types";

interface TooltipState {
  left: number;
  top: number;
}

// SidebarNavItem: client leaf rendering one navigation row. It is the only
// place active-page detection happens — each item reads the current path and
// decides its own `isActive` (exact OR prefix match, research R-1). It also
// owns the collapsed-rail tooltip (hover + keyboard focus, FR-011): when the
// surrounding rail reports data-variant="collapsed" the page name is shown as
// a fixed-position tooltip beside the icon.
export function SidebarNavItem({ label, href, iconName }: SidebarNavItemProps) {
  const pathname = usePathname();
  const isActive = pathname === href || pathname.startsWith(`${href}/`);
  const Icon = ICON_MAP[iconName];
  const [tooltip, setTooltip] = useState<TooltipState | null>(null);

  const showTooltip = (el: HTMLElement) => {
    const rail = el.closest<HTMLElement>("[data-variant]");
    if (rail?.dataset.variant !== "collapsed") return;
    const rect = el.getBoundingClientRect();
    setTooltip({ left: rect.right + 8, top: rect.top + rect.height / 2 });
  };

  return (
    <li
      className="sidebar-nav-item relative"
      onMouseEnter={(e) => showTooltip(e.currentTarget)}
      onMouseLeave={() => setTooltip(null)}
      onFocus={(e) => showTooltip(e.currentTarget)}
      onBlur={() => setTooltip(null)}
    >
      <Button
        asChild
        variant="ghost"
        size="default"
        className={cn("w-full justify-start px-3 text-left min-h-11", isActive && "bg-primary-tint font-semibold")}
      >
        <Link href={href} aria-current={isActive ? "page" : undefined}>
          {Icon ? <Icon strokeWidth={2} aria-hidden="true" /> : null}
          <span className="sidebar-label truncate">{label}</span>
        </Link>
      </Button>
      {tooltip ? <SidebarTooltip label={label} left={tooltip.left} top={tooltip.top} /> : null}
    </li>
  );
}