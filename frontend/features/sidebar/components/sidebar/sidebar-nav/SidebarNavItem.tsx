"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

import Motion from "@/shared/components/animation/Motion";
import {
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from "@/shared/components/ui/sidebar";

import { ICON_MAP } from "../../../lib/icon-map";
import { SIDEBAR_LABEL_GRID, SIDEBAR_LABEL_TRACK } from "../constants";
import type { SidebarNavItemProps } from "../../../types";

export function SidebarNavItem({ label, href, iconName }: SidebarNavItemProps) {
  const pathname = usePathname();
  const { setOpenMobile } = useSidebar();
  const isActive = pathname === href || pathname.startsWith(`${href}/`);
  const Icon = ICON_MAP[iconName];

  return (
    <SidebarMenuItem>
      {/* active pill */}
      {isActive ? (
        <Motion
          as="span"
          layoutId="sidebar-active-pill"
          aria-hidden="true"
          transition={{ type: "spring", stiffness: 380, damping: 32 }}
          className="absolute inset-0 rounded-md bg-sidebar-accent"
        />
      ) : null}

      {/* sidebar nav item */}
      <SidebarMenuButton
        asChild
        tooltip={label}
        className="relative px-3 py-5 transition-all duration-300"
        onClick={() => setOpenMobile(false)}
      >
        <Link href={href}>
          {Icon ? <Icon strokeWidth={2} aria-hidden="true" /> : null}

          <span className={SIDEBAR_LABEL_GRID}>
            <span className={SIDEBAR_LABEL_TRACK}>{label}</span>
          </span>
        </Link>
      </SidebarMenuButton>
    </SidebarMenuItem>
  );
}
