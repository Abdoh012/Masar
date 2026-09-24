"use client";

import { Dialog } from "radix-ui";
import { ShieldCheck } from "lucide-react";

import { CertificateDocument } from "@/shared/components/certificate-document/CertificateDocument";
import { Button } from "@/shared/components/ui/button";

import type { EndedApplication } from "../../../types";
import { buildEndedCertificateDocument } from "../../../lib/ended-certificate-document";
import { ENDED_DIALOG_LABELS } from "./constants";

interface EndedCertificateDialogProps {
  ended: EndedApplication;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

// EndedCertificateDialog: view-only modal for a completed training's
// certificate (the Ended Applications tab). Reuses the shared Masar
// CertificateDocument artifact — the same fixed design the certificates
// feature renders — populated from the ended application's own API data via
// buildEndedCertificateDocument, so the popup shows exactly what
// /applications/certificates reported. Deliberately exposes NO download
// action: downloads belong to the certificates feature; this surface is
// preview-only. Always mounted and controlled via open/onOpenChange (Radix
// keeps it in the DOM through the animate-out so the exit plays), following
// the WithdrawConfirmDialog controlled convention.
export function EndedCertificateDialog({
  ended,
  open,
  onOpenChange,
}: EndedCertificateDialogProps) {
  const document = buildEndedCertificateDocument(ended);

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/50 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
        <Dialog.Content
          data-slot="ended-certificate-dialog"
          className="fixed left-1/2 top-1/2 z-50 flex max-h-[90vh] w-full max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 overflow-y-auto rounded-xl border border-border bg-background p-6 shadow-lg data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
          <Dialog.Title className="sr-only">
            {ENDED_DIALOG_LABELS.title}
          </Dialog.Title>

          <CertificateDocument data={document} variant="paper" />

          {ended.certNumber ? (
            <p className="-mt-1 flex items-center justify-center gap-1.5 text-center font-mono text-xs text-secondary-text">
              <ShieldCheck className="size-3.5" />
              {ended.certNumber}
            </p>
          ) : null}

          <div className="mt-1 flex justify-end">
            <Dialog.Close asChild>
              <Button variant="outline" size="sm">
                {ENDED_DIALOG_LABELS.close}
              </Button>
            </Dialog.Close>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}