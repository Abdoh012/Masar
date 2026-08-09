interface SidebarTooltipProps {
  label: string;
  left: number;
  top: number;
}

// SidebarTooltip: the page-name label shown next to a collapsed rail item on
// hover/focus (FR-011, research R-5). Positioned `fixed` at the item's right
// edge so the rail's scroll container never clips it; rendered only when
// SidebarNavItem detects the collapsed rail.
export function SidebarTooltip({ label, left, top }: SidebarTooltipProps) {
  return (
    <span role="tooltip" style={{ left, top }} className="pointer-events-none fixed z-50 -translate-y-1/2">
      <span className="block max-w-56 truncate rounded-md border border-border bg-card px-2.5 py-1.5 text-sm font-medium text-foreground shadow-card-sm">
        {label}
      </span>
    </span>
  );
}