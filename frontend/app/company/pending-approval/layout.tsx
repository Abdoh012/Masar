import type { ReactNode } from "react";
import { redirect } from "next/navigation";

import { AuthPageShell } from "@/features/auth";
import { getSession } from "@/services/auth";
import { ROLE_HOME } from "@/config/routes";

// This gate is the company-registration landing: a freshly-registered company
// has no token until an admin approves, so it renders the auth shell (navy
// brand panel + centered card + theme toggle) instead of the company sidebar.
// Approval status never gates company-area access; the check below just keeps
// signed-in approved companies off this waiting page.
export default async function PendingApprovalLayout({
  children,
}: {
  children: ReactNode;
}) {
  const session = await getSession();

  // A freshly-registered company has no session — the backend issues no token
  // until approval, and login is blocked while pending — so this registration
  // landing must render without one. Signed-in users are still gated below:
  // only pending companies stay on this page.
  if (session) {
    if (session.role !== "company") redirect(ROLE_HOME[session.role]);
    if (session.companyStatus !== "pending") redirect(ROLE_HOME.company);
  }

  return <AuthPageShell>{children}</AuthPageShell>;
}
