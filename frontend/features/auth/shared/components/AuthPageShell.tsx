import type { ReactNode } from "react";

import { ThemeToggle } from "../../components/theme-toggle/ThemeToggle";
import { BrandMark } from "./BrandMark";
import { BrandPanel } from "./BrandPanel";

interface AuthPageShellProps {
  children: ReactNode;
}

export function AuthPageShell({ children }: AuthPageShellProps) {
  return (
    <div className="flex min-h-screen bg-background font-sans text-foreground">
      <BrandPanel />
      
      <main className="flex flex-1 flex-col justify-center px-6 py-16 sm:px-12">
        <ThemeToggle className="fixed right-4 top-4 z-50 sm:right-6 sm:top-6" />
        <div className="mx-auto w-full max-w-md">
          <div className="mb-10 lg:hidden">
            <BrandMark tone="paper" size="sm" layout="horizontal" />
          </div>
          {children}
        </div>
      </main>
    </div>
  );
}
