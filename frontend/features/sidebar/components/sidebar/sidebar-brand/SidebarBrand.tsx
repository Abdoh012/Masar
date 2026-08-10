import { cn } from "@/shared/lib/utils";
import { BrandMark } from "@/features/auth";

import { SIDEBAR_LABEL_GRID, SIDEBAR_LABEL_TRACK } from "../constants";

// SidebarBrand: the Masar logo at the top of the navy rail. Expanded shows the
// full seal + wordmark lockup (tone="navy" → white wordmark on the primary
// background). The lockup lives in an animated label track so the WORDMARK
// shrinks out on collapse; a mark-only seal (BrandMark markOnly) appears in
// the collapsed rail so the brand icon stays present (FR-012/user directive).
// The seal and wordmark swap is seamless because the seal sits in the same
// spot and simply scales down while the wordmark animates away.
export function SidebarBrand() {
  return (
    <div className="flex h-16 shrink-0 items-center border-b border-sidebar-border px-3 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0">
      <span className={cn(SIDEBAR_LABEL_GRID, "group-data-[collapsible=icon]:flex-none")}>
        <span className={SIDEBAR_LABEL_TRACK}>
          <BrandMark size="sm" layout="horizontal" tone="navy" />
        </span>
      </span>
      <BrandMark
        size="sm"
        tone="navy"
        markOnly
        className="hidden shrink-0 scale-75 group-data-[collapsible=icon]:flex"
      />
    </div>
  );
}