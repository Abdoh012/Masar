import { TriangleAlert } from "lucide-react";

import type { BankAccount } from "../../types";
import { CopyAccountNumberButton } from "./CopyAccountNumberButton";
import { PAYMENT_LABELS } from "./constants";

interface BankTransferDetailsProps {
  bankAccount: BankAccount;
}

// BankTransferDetails: leaf for the accepted-paid bank transfer phase. Bank
// name / account holder are plain label/value rows; the account number gets
// the block it deserves — muted mono panel with a copy button, since it's the
// field the student types into their banking app; and the transfer
// reference/instructions read as a warning callout, the line that lets the
// company match the payment to this application. Only semantic role tokens
// (neutral/warning/success), so the panel reads correctly in both themes.
export function BankTransferDetails({ bankAccount }: BankTransferDetailsProps) {
  return (
    <div className="space-y-3">
      <dl className="grid gap-1.5">
        {bankAccount.bankName ? (
          <BankDetailRow
            label={PAYMENT_LABELS.bankName}
            value={bankAccount.bankName}
          />
        ) : null}
        {bankAccount.accountName ? (
          <BankDetailRow
            label={PAYMENT_LABELS.accountName}
            value={bankAccount.accountName}
          />
        ) : null}
      </dl>

      <div className="flex items-center justify-between gap-3 rounded-lg bg-neutral-badge-bg px-3 py-2.5">
        <div className="min-w-0">
          <span className="block text-xs text-muted-foreground">
            {PAYMENT_LABELS.accountNumber}
          </span>
          <span className="mt-0.5 block break-all font-mono text-[15px] font-medium text-foreground">
            {bankAccount.accountNumber}
          </span>
        </div>
        <CopyAccountNumberButton accountNumber={bankAccount.accountNumber} />
      </div>

      {bankAccount.instructions ? (
        <div className="flex items-start gap-2.5 rounded-lg bg-warning-bg px-3 py-2.5 text-warning-fg">
          <TriangleAlert className="mt-0.5 size-4 shrink-0" aria-hidden />
          <p className="text-sm leading-relaxed">{bankAccount.instructions}</p>
        </div>
      ) : null}
    </div>
  );
}

function BankDetailRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-baseline justify-between gap-4">
      <dt className="shrink-0 text-xs text-muted-foreground">{label}</dt>
      <dd className="text-right text-sm font-medium text-primary-text">
        {value}
      </dd>
    </div>
  );
}