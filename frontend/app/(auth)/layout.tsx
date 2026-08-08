import type { ReactNode } from "react";

import { BrandMark, BrandPanel, ThemeToggle } from "@/features/auth";

// Auth area shell: navy brand panel on the left (desktop), the form card
// centered on the right, and the theme toggle fixed to the top-right.
// On mobile the panel is hidden and a compact paper-toned brand lockup
// appears above the card.
export default function AuthLayout({ children }: { children: ReactNode }) {
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
