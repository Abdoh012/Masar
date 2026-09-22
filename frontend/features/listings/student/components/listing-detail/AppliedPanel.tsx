import { CheckCircle2 } from "lucide-react";

import type { ApplicationStatus } from "../../types";

import { APPLICATION_STATUS_MESSAGES, APPLY_COPY } from "./constants";

interface AppliedPanelProps {
  status: ApplicationStatus;
}

// AppliedPanel: the "already applied" marker with a status-specific follow-up
// line (pending/accepted/rejected/withdrawn). Pure presentation — no data
// fetching, no interactivity (server leaf).
export function AppliedPanel({ status }: AppliedPanelProps) {
  return (
    <div role="status" className="rounded-xl border border-primary-tint bg-primary-tint/50 p-5">
      <p className="flex items-center gap-2 text-sm font-semibold text-primary-text">
        <CheckCircle2 aria-hidden="true" className="size-5 text-primary" />
        {APPLY_COPY.applied}
      </p>
      <p className="mt-1 text-sm text-muted-foreground">
        {APPLICATION_STATUS_MESSAGES[status]}
      </p>
    </div>
  );
}