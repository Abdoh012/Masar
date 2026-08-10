import { SidebarClientShell } from "./SidebarClientShell";
import { SidebarBrand } from "./sidebar-brand/SidebarBrand";
import { SidebarNav } from "./sidebar-nav/SidebarNav";
import { SidebarFooter } from "./sidebar-footer/SidebarFooter";
import { AppHeader } from "../app-header/AppHeader";

import type { SidebarProps } from "../../types";


export function Sidebar({ role, children }: SidebarProps) {
  return (
    <SidebarClientShell
      header={<SidebarBrand />}
      navigation={<SidebarNav role={role} />}
      footer={<SidebarFooter />}
      appHeader={<AppHeader role={role} />}
      content={children}
    />
  );
}