import type { ReactNode } from "react";

import { Sidebar } from "@/features/sidebar";

export default function CompanyLayout({ children }: { children: ReactNode }) {
  return <Sidebar role="company">{children}</Sidebar>;
}