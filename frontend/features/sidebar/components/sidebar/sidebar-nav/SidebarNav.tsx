import { NAV_ITEMS } from "@/config/navigation";
import type { Role } from "@/types/auth";

import { SIDEBAR_LABELS } from "../constants";
import { SidebarNavItem } from "./SidebarNavItem";

interface SidebarNavProps {
  role: Role;
}

// SidebarNav: server component mapping the config's per-role NAV_ITEMS into
// SidebarNavItem leaves. It stays server-rendered — no active detection and
// no client state here (FR-006/FR-008, R-1/R-8); each item computes its own
// active state. Flexible/scrollable so a long list never pushes the footer
// out (FR-013).
export function SidebarNav({ role }: SidebarNavProps) {
  const items = NAV_ITEMS[role];

  return (
    <nav aria-label={SIDEBAR_LABELS.navLabel} className="min-h-0 flex-1 overflow-y-auto px-3 py-4">
      <ul className="space-y-1">
        {items.map((item) => (
          <SidebarNavItem
            key={item.href}
            label={item.label}
            href={item.href}
            iconName={item.icon}
          />
        ))}
      </ul>
    </nav>
  );
}