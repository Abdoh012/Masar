"use client";

import { useState, useTransition } from "react";

import { Button } from "@/shared/components/ui/button";
import { showError, showSuccess } from "@/shared/lib/notifications";

import { requestCertificate } from "../../../actions";
import type { EligibleTraining } from "../../../types";
import {
  REQUEST_ACTION_LABELS,
  REQUEST_TOAST_COPY,
} from "../constants";
import { RequestConfirmDialog } from "./RequestConfirmDialog";

interface EligibleRequestActionProps {
  training: EligibleTraining;
}

// EligibleRequestAction: client leaf owning the "Request certificate" trigger
// for one eligible training — opens the confirm dialog, dispatches the
// requestCertificate server action via useTransition, and toasts the outcome.
// isPending disables the dialog's confirm button so a request can't be
// submitted twice; on success the action revalidates /certificates and the
// page re-renders server-side with the training gone from the eligible feed
// (the backend stops listing it once a certificate row exists) — the client
// holds no duplicate list state.
export function EligibleRequestAction({
  training,
}: EligibleRequestActionProps) {
  const [open, setOpen] = useState(false);
  const [isPending, startTransition] = useTransition();

  const handleConfirm = () => {
    startTransition(async () => {
      const result = await requestCertificate(Number(training.listingId));
      if (!result.success) {
        showError(result.error ?? REQUEST_ACTION_LABELS.failedGeneric);
        return;
      }
      setOpen(false);
      showSuccess(REQUEST_TOAST_COPY.message(training.companyName));
    });
  };

  return (
    <>
      <Button
        type="button"
        size="sm"
        className="shrink-0 self-start cursor-pointer sm:self-center"
        onClick={() => setOpen(true)}
      >
        {REQUEST_ACTION_LABELS.trigger}
      </Button>

      <RequestConfirmDialog
        training={open ? training : null}
        onOpenChange={setOpen}
        onConfirm={handleConfirm}
        isPending={isPending}
      />
    </>
  );
}