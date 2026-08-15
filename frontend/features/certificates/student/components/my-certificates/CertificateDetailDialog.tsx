"use client";

import { Dialog } from "radix-ui";
import { useState } from "react";

import type { EarnedCertificateRef } from "../../types";
import { CertificateDocument } from "@/features/certificates/shared/components/certificate-document/CertificateDocument";
import { cn } from "@/shared/lib/utils";

interface CertificateDetailDialogProps {
  certificate: EarnedCertificateRef;
  onOpenChange: (open: boolean) => void;
}

export function CertificateDetailDialog({
  certificate,
  onOpenChange,
}: CertificateDetailDialogProps) {
  const [open, setOpen] = useState(true);

  return (
    <Dialog.Root
      open={open}
      onOpenChange={(open) => {
        setOpen(open);
        onOpenChange(open);
      }}
    >
      <Dialog.Portal>
        <Dialog.Overlay
          className="fixed inset-0 z-50 bg-black/50 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0"
        />
        <Dialog.Content
          data-slot="certificate-detail-dialog"
          className="fixed left-1/2 top-1/2 z-50 flex -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-xl border border-border bg-background p-6 shadow-lg data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
          <CertificateDocument
            data={certificate}
            variant="paper"
            compact={false}
          />

          <div className="mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Dialog.Close asChild>
              <button className="rounded-sm border border-border bg-card py-2 px-4 text-sm hover:bg-card/90">
                Cancel
              </button>
            </Dialog.Close>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}