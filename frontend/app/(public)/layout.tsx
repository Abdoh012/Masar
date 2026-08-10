import type { ReactNode } from "react";

import { PublicFooter, PublicHeader } from "@/features/public";

export default function PublicLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-screen flex-col bg-background font-sans text-foreground">
      <PublicHeader />

      <main className="flex-1">{children}</main>

      <PublicFooter />
    </div>
  );
}
