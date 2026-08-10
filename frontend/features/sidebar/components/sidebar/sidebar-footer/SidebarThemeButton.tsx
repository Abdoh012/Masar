"use client";

import { Moon, Sun } from "lucide-react";
import { useTheme } from "next-themes";

import { SidebarMenuButton } from "@/shared/components/ui/sidebar";

import {
  SIDEBAR_FOOTER_LABEL,
  SIDEBAR_FOOTER_ROW,
  SIDEBAR_LABEL_TRACK,
  SIDEBAR_LABELS,
} from "../constants";

export function SidebarThemeButton() {
  const { theme, setTheme } = useTheme();
  const isDark = theme === "dark";

  return (
    <SidebarMenuButton
      tooltip={SIDEBAR_LABELS.theme}
      className={SIDEBAR_FOOTER_ROW}
      onClick={() => setTheme(isDark ? "light" : "dark")}
    >
      {isDark ? (
        <Moon strokeWidth={2} className="text-primary-200" />
      ) : (
        <Sun strokeWidth={2} className="text-secondary-500" />
      )}
      <span className={SIDEBAR_FOOTER_LABEL}>
        <span className={SIDEBAR_LABEL_TRACK}>
          {isDark ? SIDEBAR_LABELS.darkMode : SIDEBAR_LABELS.lightMode}
        </span>
      </span>
    </SidebarMenuButton>
  );
}
