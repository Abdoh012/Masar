import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
} from "@/shared/components/ui/sidebar";
import { NAV_ITEMS } from "@/config/navigation";
import type { Role } from "@/types/auth";

import { SIDEBAR_LABELS } from "../constants";
import { SidebarNavItem } from "./SidebarNavItem";

interface SidebarNavProps {
  role: Role;
}

export function SidebarNav({ role }: SidebarNavProps) {
  const items = NAV_ITEMS[role];

  return (
    <SidebarGroup role="navigation">
      <SidebarGroupContent>
        <SidebarMenu className="gap-2">
          {items.map((item) => (
            <SidebarNavItem
              key={item.href}
              label={item.label}
              href={item.href}
              iconName={item.icon}
            />
          ))}
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  );
}