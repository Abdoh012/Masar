import type { SidebarVariant } from "../../types";

// Static sidebar config (structure rules §13 — sibling constants, no inline
// static values in components). Widths are the Tailwind classes the rail
// toggles between via its data-variant.

export const SIDEBAR_LABELS = {
  navLabel: "Main navigation",
  openDrawer: "Open menu",
  closeDrawer: "Close menu",
  collapseSidebar: "Collapse sidebar",
  expandSidebar: "Expand sidebar",
  logout: "Log out",
  drawerTitle: "Sidebar navigation",
} as const;

export const SIDEBAR_IDS = {
  rail: "sidebar-rail",
  drawerTitle: "sidebar-drawer-title",
} as const;

export const RAIL_WIDTH: Record<SidebarVariant, string> = {
  expanded: "w-64",
  collapsed: "w-[4.5rem]",
};