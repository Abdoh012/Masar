"use client";

import { useActionState, useEffect, useState } from "react";
import { useFormStatus } from "react-dom";
import { Loader2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { showError, showInfo, showSuccess } from "@/shared/lib/notifications";

import { reportPayment } from "../../actions";
import { initialActionState, type ActionState } from "@/types/server-action";
import { PAYMENT_REPORT } from "./constants";

interface ReportPaymentFormProps {
  applicationId: number;
}

// ReportPaymentForm: client leaf for the "I've paid" flow (rendered by the
// server ReportPaymentZone when no reference was reported yet). Follows the
// native form pattern (structure rules §10): useActionState owns the action +
// state, an uncontrolled name="reference" input is read off FormData inside
// the action, and the submit control uses useFormStatus for pending — no
// hand-rolled async/error state. The only local state is the open boolean
// that reveals the inline form (two-step "I've paid" → confirm). Feedback is
// toast-driven (app-wide norm): success + pending-verification info fire on
// success, failed reports toast the backend error — with the error also kept
// inline under the input as the persistent anchor. The zone then swaps to
// the server "Payment reported" panel via revalidatePath + Next's automatic
// route refresh.
export function ReportPaymentForm({ applicationId }: ReportPaymentFormProps) {
  const [open, setOpen] = useState(false);
  const [state, formAction] = useActionState<ActionState, FormData>(
    reportPayment,
    initialActionState,
  );

  const referenceError = state.error;

  const hasFeedback =
    Boolean(state.success) ||
    Boolean(state.error) ||
    (state.fieldErrors != null && Object.keys(state.fieldErrors).length > 0);

  useEffect(() => {
    if (!hasFeedback) return;

    if (state.success) {
      showSuccess(PAYMENT_REPORT.successMessage);
      showInfo(PAYMENT_REPORT.infoMessage);
      return;
    }

    showError(state.error ?? PAYMENT_REPORT.failedGeneric);
  }, [state, hasFeedback]);

  if (!open) {
    return (
      <Button
        className="w-full"
        onClick={() => {
          setOpen(true);
        }}
      >
        {PAYMENT_REPORT.idleLabel}
      </Button>
    );
  }

  return (
    <div className="space-y-2.5">
      <p className="text-sm font-medium text-primary-text">
        {PAYMENT_REPORT.confirmPrompt}
      </p>

      <form action={formAction} className="space-y-2.5">
        <input type="hidden" name="application_id" value={applicationId} />

        <div className="space-y-1">
          <Input
            type="text"
            name="reference"
            placeholder={PAYMENT_REPORT.referencePlaceholder}
            required
            autoFocus
          />
          {referenceError ? (
            <p className="text-xs text-error-fg" role="alert">
              {referenceError}
            </p>
          ) : null}
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <SubmitReportButton />
          <Button
            type="button"
            size="sm"
            variant="ghost"
            onClick={() => setOpen(false)}
          >
            {PAYMENT_REPORT.cancelLabel}
          </Button>
        </div>
      </form>
    </div>
  );
}

// SubmitReportButton: the form's submit control. useFormStatus gives it the
// pending state for free — disabled + spinner + "Submitting…" while the
// action runs, no local isPending.
function SubmitReportButton() {
  const { pending } = useFormStatus();
  return (
    <Button size="sm" disabled={pending}>
      {pending ? <Loader2 className="animate-spin" aria-hidden /> : null}
      {pending ? PAYMENT_REPORT.submittingLabel : PAYMENT_REPORT.confirmLabel}
    </Button>
  );
}
