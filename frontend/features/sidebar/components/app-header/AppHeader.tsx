import { SidebarTrigger } from "@/shared/components/ui/sidebar";
import type { Role } from "@/types/auth";

import { SIDEBAR_SECTION_TITLES } from "../sidebar/constants";

interface AppHeaderProps {
  role: Role;
}

export function AppHeader({ role }: AppHeaderProps) {
  return (
    <header className="flex h-14 shrink-0 items-center gap-2 border-b border-border px-4">
      <SidebarTrigger className="fixed cursor-pointer z-50" />
      <span className="text-sm font-medium text-muted-foreground ms-8">
        {SIDEBAR_SECTION_TITLES[role]}
      </span>
    </header>
  );
}