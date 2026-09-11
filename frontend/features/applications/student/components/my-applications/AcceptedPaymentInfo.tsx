// AcceptedPaymentInfo: leaf showing what happens after a paid training's free
// trial ends (the card gates on status === "Accepted" && isPaid — this
// component never sees free trainings; paymentStatus "not_required" returns
// nothing as a defensive no-op). Three copy-only states driven by backend
// data, no invented business rules:
//  - paymentStatus "paid"       → confirmed
//  - paymentStatus "pending" with bankAccount     → BankTransferDetails leaf + report zone
//  - paymentStatus "pending" without bankAccount  → neutral placeholder + report zone
// Every state renders ONE flat block, divided from the content above by a
// hairline top border — never a card-within-a-card. `sage` stays reserved for
// hire-opportunity-confirmed; these use neutral/secondary/info tokens, plus
// the warning role for the transfer-reference callout.
import { CheckCircle2, Info, Landmark } from "lucide-react";

import type { BankAccount, PaymentStatus } from "../../types";
import { BankTransferDetails } from "./BankTransferDetails";
import { PAYMENT_LABELS } from "./constants";
import { ReportPaymentZone } from "./ReportPaymentZone";

interface AcceptedPaymentInfoProps {
  applicationId: number;
  paymentStatus?: PaymentStatus;
  bankAccount?: BankAccount | null;
}

export function AcceptedPaymentInfo({
  applicationId,
  paymentStatus,
  bankAccount,
}: AcceptedPaymentInfoProps) {
  if (!paymentStatus || paymentStatus === "not_required") return null;

  if (paymentStatus === "paid") {
    return (
      <div className="flex items-start gap-2.5 border-t border-border pt-3">
        <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-secondary-text" />
        <div className="min-w-0">
          <p className="text-sm font-medium text-primary-text">
            {PAYMENT_LABELS.paidTitle}
          </p>
          <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
            {PAYMENT_LABELS.paidMessage}
          </p>
        </div>
      </div>
    );
  }

  if (!bankAccount) {
    return (
      <div className="border-t border-border pt-3">
        <div className="flex items-start gap-2.5">
          <Info className="mt-0.5 size-4 shrink-0 text-neutral-badge-fg" />
          <p className="text-sm leading-relaxed text-muted-foreground">
            {PAYMENT_LABELS.pendingNoBank}
          </p>
        </div>
        <div className="mt-3">
          <ReportPaymentZone applicationId={applicationId} />
        </div>
      </div>
    );
  }

  return (
    <div className="border-t border-border pt-3">
      <p className="flex items-center gap-1.5 text-sm font-semibold text-primary-text">
        <Landmark className="size-4 shrink-0 text-secondary-text" />
        {PAYMENT_LABELS.bankTitle}
      </p>
      <div className="mt-3">
        <BankTransferDetails bankAccount={bankAccount} />
      </div>
      <div className="mt-3">
        <ReportPaymentZone applicationId={applicationId} />
      </div>
    </div>
  );
}