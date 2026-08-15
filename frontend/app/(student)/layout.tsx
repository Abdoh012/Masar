import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";
import { Toaster } from "sonner";

export default function StudentLayout({ children }: { children: ReactNode }) {
  return (
    <Sidebar role="student">
      <Toaster />
      {children}
    </Sidebar>
  );
}