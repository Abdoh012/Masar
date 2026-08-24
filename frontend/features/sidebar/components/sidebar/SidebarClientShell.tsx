"use client";

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarInset,
  SidebarProvider,
} from "@/shared/components/ui/sidebar";

import type { SidebarClientShellProps } from "../../types";

export function SidebarClientShell({
  header,
  navigation,
  footer,
  appHeader,
  content,
}: SidebarClientShellProps) {
  return (
    <SidebarProvider className="bg-background font-sans text-foreground">
      <Sidebar collapsible="icon" side="left">
        {/* sidebar header */}
        <SidebarHeader>{header}</SidebarHeader>

        {/* sidebar navigation */}
        <SidebarContent>{navigation}</SidebarContent>

        {/* sidebar footer */}
        <SidebarFooter>{footer}</SidebarFooter>
      </Sidebar>

      {/* sidebar inset */}
      <SidebarInset>
        {appHeader}
        <div className="flex flex-1 flex-col pb-8">{content}</div>
      </SidebarInset>
    </SidebarProvider>
  );
}
