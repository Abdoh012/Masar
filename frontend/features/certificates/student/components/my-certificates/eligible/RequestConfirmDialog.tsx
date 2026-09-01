"use client";

import { Dialog } from "radix-ui";

import { Button } from "@/shared/components/ui/button";

import type { EligibleTraining } from "../../../types";
import { REQUEST_DIALOG_LABELS } from "../constants";

interface RequestConfirmDialogProps {
  training: EligibleTraining | null;
  onOpenChange: (open: boolean) => void;
  onConfirm: () => void;
}

// RequestConfirmDialog: confirm-before-request modal for an eligible training.
// Mirrors the app's dialog conventions (radix-ui + tw-animate-css + semantic
// tokens). `training === null` → closed; otherwise open, identifying the exact
// training/company in the copy. Confirm fires the orchestrator's local state
// transition; Cancel/Escape/overlay close with no change.
export function RequestConfirmDialog({
  training,
  onOpenChange,
  onConfirm,
}: RequestConfirmDialogProps) {
  const open = training !== null;

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/50 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
        <Dialog.Content
          data-slot="request-certificate-dialog"
          className="fixed left-1/2 top-1/2 z-50 flex w-full max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-xl border border-border bg-background p-6 shadow-lg data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
          <Dialog.Title className="font-semibold text-foreground">
            {REQUEST_DIALOG_LABELS.title}
          </Dialog.Title>

          <Dialog.Description className="text-sm text-muted-foreground">
            {training
              ? REQUEST_DIALOG_LABELS.description(training.listingTitle, training.companyName)
              : null}
          </Dialog.Description>

          <div className="mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Dialog.Close asChild>
              <Button variant="outline" size="sm">
                {REQUEST_DIALOG_LABELS.cancel}
              </Button>
            </Dialog.Close>
            <Button variant="accent" size="sm" onClick={onConfirm}>
              {REQUEST_DIALOG_LABELS.confirm}
            </Button>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}
