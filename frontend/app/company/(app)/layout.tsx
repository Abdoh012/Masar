import type { ReactNode } from "react";
import { redirect } from "next/navigation";

import { Sidebar } from "@/features/sidebar";
import { getSession } from "@/services/auth";
import { ROLE_HOME } from "@/config/routes";

export default async function CompanyLayout({
  children,
}: {
  children: ReactNode;
}) {
  const session = await getSession();

  if (!session) redirect("/sign-in");
  if (session.role !== "company") redirect(ROLE_HOME[session.role]);

  return <Sidebar role="company">{children}</Sidebar>;
}