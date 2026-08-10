import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";

export default function AdminLayout({ children }: { children: ReactNode }) {
  return <Sidebar role="admin">{children}</Sidebar>;
}