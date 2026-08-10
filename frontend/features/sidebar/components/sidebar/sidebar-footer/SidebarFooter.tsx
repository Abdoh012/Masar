import {
  SidebarFooter as SidebarFooterPrimitive,
  SidebarMenu,
} from "@/shared/components/ui/sidebar";

import { SidebarThemeButton } from "./SidebarThemeButton";
import { SidebarLogoutButton } from "./SidebarLogoutButton";

export function SidebarFooter() {
  return (
    <div className="border-t border-sidebar-border pt-3">
      <SidebarMenu>
        <SidebarThemeButton />
        <SidebarLogoutButton />
      </SidebarMenu>
    </div>
  );
}