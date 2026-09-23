import { CheckCircle2 } from "lucide-react";

import type { ApplicationStatus } from "../../types";

import {
  APPLICATION_STATUS_MESSAGES,
  APPLICATION_STATUS_PANEL_STYLES,
  APPLY_COPY,
} from "./constants";

interface AppliedPanelProps {
  status: ApplicationStatus;
}

// AppliedPanel: the "already applied" marker with a status-specific follow-up
// line (pending/accepted/rejected/withdrawn). The panel's tint is status-driven
// via APPLICATION_STATUS_PANEL_STYLES (success for accepted, error for
// rejected/withdrawn, info for pending) — copy stays unhardcoded above the
// map. Pure presentation — no data fetching, no interactivity (server leaf).
export function AppliedPanel({ status }: AppliedPanelProps) {
  return (
    <div
      role="status"
      className={
        "rounded-xl border p-5 " + APPLICATION_STATUS_PANEL_STYLES[status].panel
      }
    >
      <p
        className={
          "flex items-center gap-2 text-sm font-semibold " +
          APPLICATION_STATUS_PANEL_STYLES[status].accent
        }
      >
        <CheckCircle2
          aria-hidden="true"
          className={"size-5 " + APPLICATION_STATUS_PANEL_STYLES[status].accent}
        />
        {APPLY_COPY.applied}
      </p>
      <p className="mt-1 text-sm text-muted-foreground">
        {APPLICATION_STATUS_MESSAGES[status]}
      </p>
    </div>
  );
}