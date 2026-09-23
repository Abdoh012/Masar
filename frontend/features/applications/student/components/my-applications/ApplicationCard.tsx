import { Clock } from "lucide-react";

import { STATUS_BADGE_CLASSES } from "../applications-snapshot/constants";
import {
  TRAINING_PERIOD_LABEL,
  TRIAL_CRITICAL_DAYS,
} from "../../../shared/components/trial-countdown/constants";
import { TrialCountdown } from "../../../shared/components/trial-countdown/TrialCountdown";
import type { MyApplication } from "../../types";
import { AcceptedPaymentInfo } from "./AcceptedPaymentInfo";
import { ApplicationCardActions } from "./ApplicationCardActions";
import { AwaitingResponseNote } from "./AwaitingResponseNote";
import { PaymentStatusBadge } from "./PaymentStatusBadge";
import { TrainingFinishedNote } from "./TrainingFinishedNote";
import {
  formatApplicationDate,
  formatDurationDays,
  FREE_TRAINING_INFO_LABELS,
  getStatusDate,
  MAY_LEAD_TO_HIRE_LABEL,
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
// statuses. The time signal on Accepted cards is the shared countdown chip:
// paid trainings run it for the free trial only during the trial's final
// stretch — while `payment_submitted` is still false and fewer than
// TRIAL_CRITICAL_DAYS days remain (so the free-days countdown sits right
// above the payment-info panel, which surfaces on the same condition; bank
// transfers need lead time, so the details appear before the trial ends). The
// moment the student has paid, and every free accepted training, the same
// chip switches to the training's remaining days (backend `remaining_days` —
// a countdown, not the fixed duration) below the motivation message; when
// those remaining days hit 0 a completion note replaces the countdown instead
// of showing a "0 days remaining" read. Cards flow two-per-row from the
// orchestrator's grid.
export function ApplicationCard({ application }: ApplicationCardProps) {
  const trial = application.trial;
  const rejectionLabel = rejectionReasonLabel(application.rejectionReasonCode);
  const remaining = application.remainingDays;
  const totalTrainingDays = application.duration ?? remaining;
  const paid = application.paymentSubmitted === true;

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
          {!paid &&
          trial !== undefined &&
          trial.daysRemaining !== null &&
          trial.daysRemaining > 0 &&
          trial.daysRemaining < TRIAL_CRITICAL_DAYS ? (
            <TrialCountdown
              daysRemaining={trial.daysRemaining}
              totalDays={trial.days ?? trial.daysRemaining}
            />
          ) : null}

          {!paid && (trial?.daysRemaining ?? 0) < TRIAL_CRITICAL_DAYS ? (
            <AcceptedPaymentInfo
              applicationId={application.id}
              paymentStatus={application.paymentStatus}
              bankAccount={application.bankAccount}
            />
          ) : null}

          {paid && remaining !== null && remaining > 0 ? (
            <TrialCountdown
              daysRemaining={remaining}
              totalDays={totalTrainingDays ?? remaining}
              periodLabel={TRAINING_PERIOD_LABEL}
            />
          ) : null}

          {paid && remaining === 0 ? <TrainingFinishedNote /> : null}
        </>
      ) : null}

      {application.status === "Accepted" && !application.isPaid ? (
        <>
          {remaining !== null && remaining > 0 ? (
            <TrialCountdown
              daysRemaining={remaining}
              totalDays={totalTrainingDays ?? remaining}
              periodLabel={TRAINING_PERIOD_LABEL}
            />
          ) : null}

          {remaining === 0 ? <TrainingFinishedNote /> : null}
        </>
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
