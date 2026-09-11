"use client";

import Link from "next/link";
import { useState, useTransition } from "react";

import { Button } from "@/shared/components/ui/button";
import { showError, showSuccess } from "@/shared/lib/notifications";

import { withdrawApplication } from "../../actions";
import type { MyApplication } from "../../types";
import { CARD_ACTION_LABELS, WITHDRAW_TOASTS } from "./constants";
import { WithdrawConfirmDialog } from "./WithdrawConfirmDialog";

interface ApplicationCardActionsProps {
  application: MyApplication;
}

// ApplicationCardActions: client leaf owning the card's action row
// (FR-015-018/025). View Listing is a shared Button-as-Link to the listing,
// rendered for every status; Withdraw appears only when the backend reports
// can_withdraw (pending queue) and opens this card's confirm dialog. The
// withdraw call runs through the withdrawApplication server action, which
// revalidates /applications — the page re-renders server-side with the card
// gone and counts adjusted, no client refetch. Feedback follows the app-wide
// toast norm: success toasts "Application withdrawn.", failures toast the
// backend message AND keep the inline role="alert" line inside the dialog.
// The dialog's pending/error state lives here, per card.
export function ApplicationCardActions({
  application,
}: ApplicationCardActionsProps) {
  const [open, setOpen] = useState(false);
  const [isPending, startTransition] = useTransition();

  const handleConfirm = () => {
    startTransition(async () => {
      const result = await withdrawApplication(application.id);
      if (!result.success) {
        const message = result.error ?? WITHDRAW_TOASTS.failedGeneric;
        showError(message);
        return;
      }
      setOpen(false);
      showSuccess(WITHDRAW_TOASTS.success);
    });
  };

  return (
    <>
      <div className="mt-auto flex flex-wrap items-center gap-2 pt-1">
        <Button asChild size="sm" variant="outline">
          <Link href={`/listings/${application.trainingId}`}>
            {CARD_ACTION_LABELS.viewListing}
          </Link>
        </Button>

        {application.canWithdraw ? (
          <Button size="sm" variant="destructive" onClick={() => setOpen(true)}>
            {CARD_ACTION_LABELS.withdraw}
          </Button>
        ) : null}
      </div>

      <WithdrawConfirmDialog
        application={application}
        open={open}
        onOpenChange={setOpen}
        onConfirm={handleConfirm}
        isPending={isPending}
      />
    </>
  );
}
