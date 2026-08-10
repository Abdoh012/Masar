import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";

export default function StudentLayout({ children }: { children: ReactNode }) {
  return <Sidebar role="student">{children}</Sidebar>;
}