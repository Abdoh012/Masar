import type { Role } from "@/types/auth";

// Feature-level types for the sidebar (structure rules §14 — the sidebar is
// role-neutral, so everything lives in this single types.ts; there are no
// role subfolders). Config-derived data (NAV_ITEMS/ROLE_HOME) stays in
// config/navigation.ts and config/routes.ts — it is never duplicated here.

export type SidebarVariant = "expanded" | "collapsed";

export type SidebarDrawerState = "open" | "closed";

export interface SidebarProps {
  role: Role;
}

export interface SidebarNavItemProps {
  label: string;
  href: string;
  iconName: string;
}