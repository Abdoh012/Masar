import { Clock } from "lucide-react";

import { STATUS_BADGE_CLASSES } from "../applications-snapshot/constants";
import { formatApplicationDate, getStatusDate } from "./constants";
import { TrialCountdown } from "../../../shared/components/trial-countdown/TrialCountdown";
import type { MyApplication } from "../../types";
import { ApplicationCardActions } from "./ApplicationCardActions";

interface ApplicationCardProps {
  application: MyApplication;
  onWithdraw: (application: MyApplication) => void;
}

// Leaf: one application card on the My Applications page (FR-010/012/013).
// Renders listing/company, a compact meta row (status date + training
// duration), the status badge, the conditional rejection reason and "may lead
// to hire" note, and the per-status actions row. The date shown is the current
// status's own date (accepted/rejected/withdrawn/applied — STATUS_DATE_FIELDS),
// never the raw applied date for terminal statuses. Accepted paid applications
// additionally render the shared trial countdown inline with the remaining
// days (FR-011); "Continue past trial" is display-only text, never an action
// (FR-014). Cards flow two-per-row from the orchestrator's grid.
export function ApplicationCard({ application, onWithdraw }: ApplicationCardProps) {
  return (
    <div className="flex h-full flex-col gap-3 rounded-xl border border-border bg-card p-5">
      <div className="flex flex-wrap items-center gap-2">
        <span
          className={
            "rounded-full px-2.5 py-0.5 text-xs font-medium " +
            STATUS_BADGE_CLASSES[application.status]
          }
        >
          {application.status}
        </span>

        {application.mayLeadToHire ? (
          <span className="rounded-full bg-primary-tint px-2.5 py-0.5 text-xs font-medium text-primary-text">
            May lead to hire
          </span>
        ) : null}
      </div>

      <div className="space-y-0.5">
        <p className="truncate font-sans text-base font-semibold text-foreground">
          {application.listingTitle}
        </p>
        <p className="truncate text-sm text-muted-foreground">{application.companyName}</p>
      </div>

      <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
        <time className="font-mono text-xs text-muted-foreground" dateTime={getStatusDate(application)}>
          {application.status} {formatApplicationDate(getStatusDate(application))}
        </time>
        {application.duration ? (
          <span className="inline-flex items-center gap-1.5 rounded-md bg-neutral-badge-bg px-2 py-0.5 text-xs font-medium text-neutral-badge-fg">
            <Clock className="size-3" />
            {application.duration}
          </span>
        ) : null}
      </div>

      {application.status === "Accepted" && application.trial ? (
        <TrialCountdown daysRemaining={application.trial.daysRemaining} />
      ) : null}

      {application.trial?.continuePastTrial ? (
        <p className="text-xs font-medium text-secondary-text">Continue past trial</p>
      ) : null}

      {application.rejectionReason ? (
        <p className="text-xs text-muted-foreground">{application.rejectionReason}</p>
      ) : null}

      <ApplicationCardActions application={application} onWithdraw={onWithdraw} />
    </div>
  );
}