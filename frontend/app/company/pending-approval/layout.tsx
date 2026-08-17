import type { ReactNode } from "react";
import { redirect } from "next/navigation";

import { AuthPageShell } from "@/features/auth";
import { getSession } from "@/services/auth";
import { ROLE_HOME } from "@/config/routes";

// Pending companies are locked to /company/pending-approval until an admin
// approves them, so this gate renders the auth shell (navy brand panel +
// centered card + theme toggle) instead of the company sidebar.
export default async function PendingApprovalLayout({
  children,
}: {
  children: ReactNode;
}) {
  const session = await getSession();

  if (!session) redirect("/sign-in");
  if (session.role !== "company") redirect(ROLE_HOME[session.role]);

  return <AuthPageShell>{children}</AuthPageShell>;
}