import { ThemeToggle } from "@/features/auth";

// SidebarThemeButton: server wrapper reusing the auth ThemeToggle — the app's
// single next-themes mechanism (FR-014). The toggle reads/writes the existing
// provider so the sidebar stays in sync with any other toggle.
export function SidebarThemeButton() {
  return <ThemeToggle className="w-full justify-center sm:justify-center" />;
}