import { LogOut } from "lucide-react";

import { SidebarMenuButton } from "@/shared/components/ui/sidebar";

import {
  SIDEBAR_FOOTER_LABEL,
  SIDEBAR_FOOTER_ROW,
  SIDEBAR_LABEL_TRACK,
  SIDEBAR_LABELS,
} from "../constants";

export function SidebarLogoutButton() {
  return (
    <SidebarMenuButton
      asChild
      tooltip={SIDEBAR_LABELS.logout}
      className={SIDEBAR_FOOTER_ROW}
    >
      <button type="button">
        <LogOut strokeWidth={2} />
        <span className={SIDEBAR_FOOTER_LABEL}>
          <span className={SIDEBAR_LABEL_TRACK}>{SIDEBAR_LABELS.logout}</span>
        </span>
      </button>
    </SidebarMenuButton>
  );
}
