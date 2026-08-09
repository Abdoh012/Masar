import { LogOut } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { SIDEBAR_LABELS } from "../constants";

// SidebarLogoutButton: UI-only logout control (FR-015/FR-025) — a real,
// visible button with NO handler this phase (no session removal, no
// redirect). Its label hides in the collapsed rail via data-variant CSS.
export function SidebarLogoutButton() {
  return (
    <Button type="button" variant="ghost" size="default" className="w-full justify-start px-3 text-left min-h-11">
      <LogOut strokeWidth={2} aria-hidden="true" />
      <span className="sidebar-label truncate">{SIDEBAR_LABELS.logout}</span>
    </Button>
  );
}