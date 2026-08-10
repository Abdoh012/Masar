import type { Role } from "@/types/auth";

// Static sidebar config (structure rules §13 — sibling constants, no inline
// static values in components).

export const SIDEBAR_LABELS = {
  navLabel: "Main navigation",
  logout: "Log out",
  theme: "Toggle dark theme",
  lightMode: "Light",
  darkMode: "Dark",
} as const;

export const SIDEBAR_SECTION_TITLES: Record<Role, string> = {
  student: "Student",
  company: "Company",
  admin: "Admin",
};

// Page-width breakpoint the shadcn sidebar uses for its mobile sheet vs the
// desktop rail (see shared/components/ui/sidebar.tsx:useIsMobile). NAV toggles
// between label ("expanded", its natural width) and icon rails by swapping
// grid-template-columns 1fr <-> 0fr so the text shrinks smoothly instead of
// toggling display:none (animate out, not snap).
export const SIDEBAR_LABEL_GRID =
  "grid min-w-0 flex-1 transition-[grid-template-columns] duration-200 ease-linear group-data-[collapsible=icon]:[grid-template-columns:0fr]";

export const SIDEBAR_LABEL_TRACK =
  "overflow-hidden text-left transition-opacity duration-200 ease-linear group-data-[collapsible=icon]:opacity-0";

// Footer rows (theme + logout): an OPAQUE navy panel so they read as real
// buttons against the rail (not a translucent wash), py-5 vertical padding for
// height, and a duration-200 transition including background-color so hover
// color changes animate. In the collapsed rail the button snaps to a square
// (min-h-8) with the content centered (the nav keeps its left-aligned icon,
// the footer centers).
export const SIDEBAR_FOOTER_ROW =
  "cursor-pointer bg-primary-700 py-5 transition-[width,height,padding,background-color,opacity] duration-200 ease-linear hover:bg-primary-600 group-data-[collapsible=icon]:min-h-8 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:gap-0";

// Footer label: same shrink-out as any other label, but in the collapsed rail
// the label grid must stop growing (flex-none + w-0) so the remaining icon
// centers instead of being pushed to the left edge.
export const SIDEBAR_FOOTER_LABEL = `${SIDEBAR_LABEL_GRID} group-data-[collapsible=icon]:flex-none group-data-[collapsible=icon]:w-0`;