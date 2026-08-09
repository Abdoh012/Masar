import { SidebarThemeButton } from "./SidebarThemeButton";
import { SidebarLogoutButton } from "./SidebarLogoutButton";

// SidebarFooter: server component — theme + UI-only logout pinned to the
// bottom of the rail regardless of nav length (FR-013). mt-auto anchors it;
// the compact/icon-only treatment in the collapsed rail comes from the
// rail's data-variant CSS.
export function SidebarFooter() {
  return (
    <div className="mt-auto shrink-0 border-t border-border p-3">
      <div className="flex flex-col gap-2">
        <SidebarThemeButton />
        <SidebarLogoutButton />
      </div>
    </div>
  );
}