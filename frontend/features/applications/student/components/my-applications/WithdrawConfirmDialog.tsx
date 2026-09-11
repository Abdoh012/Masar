"use client";

import { Loader2 } from "lucide-react";
import { Dialog } from "radix-ui";

import { Button } from "@/shared/components/ui/button";

import type { MyApplication } from "../../types";
import { WITHDRAW_DIALOG_LABELS } from "./constants";

interface WithdrawConfirmDialogProps {
  application: MyApplication;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onConfirm: () => void;
  isPending?: boolean;
  error?: string | null;
}

// WithdrawConfirmDialog (FR-021-024): controlled confirm-before-withdraw modal
// built directly on the radix-ui Dialog primitive, following the
// shared/ui/sheet.tsx wrapper conventions (portal + overlay + tw-animate-css
// classes + semantic tokens). Controlled via open/onOpenChange by its owning
// client leaf (ApplicationCardActions), which also supplies the pending flag
// (disables both buttons, spinner on the confirm button) and any failure
// message from the server action, rendered as a role="alert" line. Cancel /
// Escape / overlay close with no change (via onOpenChange); Confirm triggers
// the owning leaf's withdraw server action (FR-024).
export function WithdrawConfirmDialog({
  application,
  open,
  onOpenChange,
  onConfirm,
  isPending = false,
}: WithdrawConfirmDialogProps) {
  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/50 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
        <Dialog.Content
          data-slot="withdraw-dialog"
          className="fixed left-1/2 top-1/2 z-50 flex w-full max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-xl border border-border bg-background p-6 shadow-lg data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
          <Dialog.Title className="font-semibold text-foreground">
            {WITHDRAW_DIALOG_LABELS.title}
          </Dialog.Title>

          <Dialog.Description className="text-sm text-muted-foreground">
            {WITHDRAW_DIALOG_LABELS.description(
              application.listingTitle,
              application.companyName,
            )}
          </Dialog.Description>

          <div className="mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Dialog.Close asChild>
              <Button variant="outline" size="sm" disabled={isPending}>
                {WITHDRAW_DIALOG_LABELS.cancel}
              </Button>
            </Dialog.Close>
            <Button
              variant="destructive"
              size="sm"
              onClick={onConfirm}
              disabled={isPending}
            >
              {isPending ? (
                <>
                  <Loader2 className="size-4 animate-spin" />
                  {WITHDRAW_DIALOG_LABELS.confirmPending}
                </>
              ) : (
                WITHDRAW_DIALOG_LABELS.confirm
              )}
            </Button>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}
