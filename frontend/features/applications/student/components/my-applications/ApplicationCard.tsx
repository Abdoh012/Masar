import { STATUS_BADGE_CLASSES } from "../applications-snapshot/constants";
import { formatApplicationDate } from "./constants";
import { TrialCountdown } from "../../../shared/components/trial-countdown/TrialCountdown";
import type { MyApplication } from "../../types";
import { ApplicationCardActions } from "./ApplicationCardActions";

interface ApplicationCardProps {
  application: MyApplication;
  onWithdraw: (application: MyApplication) => void;
}

// Leaf: one application card on the My Applications page (FR-010/012/013).
// Renders listing/company, the formatted application date, the status badge
// (reusing the feature's status token map), the conditional rejection reason
// and "may lead to hire" note, and the per-status actions row. Accepted paid
// applications additionally render the shared trial countdown inline with the
// remaining days (FR-011); "Continue past trial" is display-only text, never an
// action (FR-014).
export function ApplicationCard({ application, onWithdraw }: ApplicationCardProps) {
  return (
    <div className="flex flex-col gap-3 rounded-xl border border-border bg-card p-5">
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

      <p className="truncate font-sans text-base font-semibold text-foreground">
        {application.listingTitle}
      </p>
      <p className="truncate text-sm text-muted-foreground">{application.companyName}</p>

      <time className="font-mono text-xs text-muted-foreground" dateTime={application.appliedOn}>
        Applied {formatApplicationDate(application.appliedOn)}
      </time>

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