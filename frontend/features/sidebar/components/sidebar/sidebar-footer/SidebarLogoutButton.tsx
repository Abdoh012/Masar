"use client";

import { useTransition } from "react";
import { LogOut } from "lucide-react";

import { SidebarMenuButton } from "@/shared/components/ui/sidebar";
import { logout } from "@/services/auth";

import {
  SIDEBAR_FOOTER_LABEL,
  SIDEBAR_FOOTER_ROW,
  SIDEBAR_LABEL_TRACK,
  SIDEBAR_LABELS,
} from "../constants";

export function SidebarLogoutButton() {
  const [isPending, startTransition] = useTransition();

  return (
    <SidebarMenuButton
      asChild
      tooltip={SIDEBAR_LABELS.logout}
      className={SIDEBAR_FOOTER_ROW}
    >
      <button
        type="button"
        disabled={isPending}
        onClick={() => startTransition(() => logout())}
        className="cursor-pointer disabled:pointer-events-auto! disabled:cursor-not-allowed"
      >
        <LogOut strokeWidth={2} />
        <span className={SIDEBAR_FOOTER_LABEL}>
          <span className={SIDEBAR_LABEL_TRACK}>{SIDEBAR_LABELS.logout}</span>
        </span>
      </button>
    </SidebarMenuButton>
  );
}
