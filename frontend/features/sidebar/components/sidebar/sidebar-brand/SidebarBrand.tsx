import { BrandMark } from "@/features/auth";

// SidebarBrand: the Masar logo lockup at the top of the sidebar (FR-005).
// Server-rendered. The compact (mark-only) treatment in the collapsed rail is
// applied by globals.css via the rail's data-variant — no collapsed prop
// (research R-8).
export function SidebarBrand() {
  return (
    <div className="sidebar-brand flex h-16 shrink-0 items-center border-b border-border px-3">
      <BrandMark size="sm" layout="horizontal" tone="paper" />
    </div>
  );
}