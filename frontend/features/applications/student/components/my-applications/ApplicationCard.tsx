import { Clock } from "lucide-react";

import { STATUS_BADGE_CLASSES } from "../applications-snapshot/constants";
import { TrialCountdown } from "../../../shared/components/trial-countdown/TrialCountdown";
import type { MyApplication } from "../../types";
import { AcceptedPaymentInfo } from "./AcceptedPaymentInfo";
import { ApplicationCardActions } from "./ApplicationCardActions";
import { AwaitingResponseNote } from "./AwaitingResponseNote";
import { FreeTrainingInfo } from "./FreeTrainingInfo";
import { PaymentStatusBadge } from "./PaymentStatusBadge";
import {
  formatApplicationDate,
  formatDurationDays,
  FREE_TRAINING_INFO_LABELS,
  getStatusDate,
  MAY_LEAD_TO_HIRE_LABEL,
  PAYMENT_NOTICE_DAYS,
  rejectionReasonLabel,
} from "./constants";

interface ApplicationCardProps {
  application: MyApplication;
}

// Leaf: one application card on the My Applications page (FR-010/012/013).
// Renders listing/company, a compact meta row (status date + a "Duration ·
// 35 days"-style chip stating the training's total term), the badge row
// (status + Free/Paid pill on
// Applied/Accepted cards + the "may lead to hire" pill on Applied/Accepted
// paid cards), the Applied-card awaiting-response note (fills the slot the
// Accepted sections leave empty), the conditional rejected-reason panel, and
// the per-status actions row. The date shown is the current status's own date
// (accepted/rejected/withdrawn/
// applied — STATUS_DATE_FIELDS), never the raw applied date for terminal
// statuses. Accepted paid applications render the shared trial countdown for
// as long as the free trial runs, with the payment-info panel joining it once
// ≤ PAYMENT_NOTICE_DAYS days remain (bank transfers need lead time, so the
// details surface before the trial ends — the countdown stays visible through
// its final days, and after the trial only the panel remains); free accepted
// trainings fill that same slot with the FreeTrainingInfo facts block instead.
// Cards flow two-per-row from the orchestrator's grid.
export function ApplicationCard({ application }: ApplicationCardProps) {
  const trial = application.trial;
  const rejectionLabel = rejectionReasonLabel(application.rejectionReasonCode);

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

        {application.status === "Applied" || application.status === "Accepted" ? (
          <PaymentStatusBadge isPaid={application.isPaid} />
        ) : null}

        {application.mayLeadToHire ? (
          <span className="rounded-full bg-primary-tint px-2.5 py-0.5 text-xs font-medium text-primary-text">
            {MAY_LEAD_TO_HIRE_LABEL}
          </span>
        ) : null}
      </div>

      <div className="space-y-0.5">
        <p className="truncate font-sans text-base font-semibold text-foreground">
          {application.listingTitle}
        </p>
        <p className="truncate text-sm text-muted-foreground">
          {application.companyName}
        </p>
      </div>

      <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
        <time
          className="font-mono text-xs text-muted-foreground"
          dateTime={getStatusDate(application)}
        >
          {application.status}{" "}
          {formatApplicationDate(getStatusDate(application))}
        </time>

        {application.duration !== null ? (
          <span className="inline-flex items-center gap-1.5 rounded-md bg-neutral-badge-bg px-2 py-0.5 text-xs font-medium text-neutral-badge-fg">
            <Clock className="size-3" />
            {FREE_TRAINING_INFO_LABELS.duration} ·{" "}
            {formatDurationDays(application.duration)}
          </span>
        ) : null}
      </div>

      {application.status === "Applied" ? <AwaitingResponseNote /> : null}

      {application.status === "Accepted" && application.motivationalMessage ? (
        <p className="text-xs italic text-muted-foreground">
          {application.motivationalMessage}
        </p>
      ) : null}

      {application.status === "Accepted" && application.isPaid ? (
        <>
          {trial !== undefined &&
          trial.daysRemaining !== null &&
          trial.daysRemaining > 0 ? (
            <TrialCountdown
              daysRemaining={trial.daysRemaining}
              totalDays={trial.days ?? trial.daysRemaining}
            />
          ) : null}

          {trial === undefined ||
          trial.daysRemaining === null ||
          trial.daysRemaining <= PAYMENT_NOTICE_DAYS ? (
            <AcceptedPaymentInfo
              applicationId={application.id}
              paymentStatus={application.paymentStatus}
              bankAccount={application.bankAccount}
            />
          ) : null}
        </>
      ) : null}

      {application.status === "Accepted" && !application.isPaid ? (
        <FreeTrainingInfo
          duration={application.duration}
          method={application.method}
          startsAt={application.startsAt}
        />
      ) : null}

      {application.status === "Rejected" &&
      (rejectionLabel || application.rejectionNote) ? (
        <div className="space-y-1 rounded-lg bg-neutral-badge-bg/60 px-3 py-2">
          {rejectionLabel ? (
            <p className="text-xs font-semibold text-neutral-badge-fg">
              {rejectionLabel}
            </p>
          ) : null}
          {application.rejectionNote ? (
            <p className="text-xs text-muted-foreground">
              {application.rejectionNote}
            </p>
          ) : null}
        </div>
      ) : null}

      <ApplicationCardActions application={application} />
    </div>
  );
}
