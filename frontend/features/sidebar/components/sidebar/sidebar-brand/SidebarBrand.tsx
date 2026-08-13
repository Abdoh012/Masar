import { cn } from "@/shared/lib/utils";
import { BrandMark } from "@/features/auth";

import { SIDEBAR_LABEL_GRID, SIDEBAR_LABEL_TRACK } from "../constants";

export function SidebarBrand() {
  return (
    <div className="flex h-16 shrink-0 items-center border-b border-sidebar-border px-3 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0">
      <span
        className={cn(
          SIDEBAR_LABEL_GRID,
          "group-data-[collapsible=icon]:flex-none",
        )}
      >
        <span className={SIDEBAR_LABEL_TRACK}>
          <BrandMark chip="always" onDark size="sm" layout="horizontal" />
        </span>
      </span>
      <BrandMark
        chip="always"
        size="sm"
        markOnly
        className="hidden shrink-0 scale-75 group-data-[collapsible=icon]:flex"
      />
    </div>
  );
}
