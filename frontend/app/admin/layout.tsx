import type { ReactNode } from "react";
import { redirect } from "next/navigation";

import { Sidebar } from "@/features/sidebar";
import { getSession } from "@/services/auth";
import { ROLE_HOME } from "@/config/routes";

export default async function AdminLayout({
  children,
}: {
  children: ReactNode;
}) {
  const session = await getSession();

  if (!session) redirect("/sign-in");
  if (session.role !== "admin") redirect(ROLE_HOME[session.role]);

  return <Sidebar role="admin">{children}</Sidebar>;
}