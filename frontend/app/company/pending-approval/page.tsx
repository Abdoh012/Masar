import type { Metadata } from "next";

import PendingApprovalContainer from "@/features/auth/components/pending-approval/PendingApprovalContainer";

export const metadata: Metadata = {
  title: "Pending Approval",
};

export default function PendingApprovalPage() {
  return <PendingApprovalContainer />;
}
