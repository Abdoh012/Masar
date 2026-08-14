import type { ReactNode } from "react";

import { AuthPageShell } from "@/features/auth";

// Pending companies are locked to /company/pending-approval until an admin
// approves them, so this gate renders the auth shell (navy brand panel +
// centered card + theme toggle) instead of the company sidebar.
export default function PendingApprovalLayout({ children }: { children: ReactNode }) {
  return <AuthPageShell>{children}</AuthPageShell>;
}
