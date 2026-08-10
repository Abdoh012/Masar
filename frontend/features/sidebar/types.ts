import type { ReactNode } from "react";
import type { Role } from "@/types/auth";

// Feature-level types for the sidebar (structure rules §14 — the sidebar is
// role-neutral, so everything lives in this single types.ts; there are no
// role subfolders). Config-derived data (NAV_ITEMS/ROLE_HOME) stays in
// config/navigation.ts and config/routes.ts — it is never duplicated here.

export interface SidebarProps {
  role: Role;
  // The page content rendered inside SidebarInset (from the signed-in layout).
  children: ReactNode;
}

export interface SidebarClientShellProps {
  // Server-rendered sidebar sections, passed as ReactNode props into the
  // client shell so it never authors content (research R-8).
  header: ReactNode;
  navigation: ReactNode;
  footer: ReactNode;
  appHeader: ReactNode;
  content: ReactNode;
}

export interface SidebarNavItemProps {
  label: string;
  href: string;
  iconName: string;
}