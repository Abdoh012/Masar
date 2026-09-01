"use client";

import { Dialog } from "radix-ui";
import { Download, ShieldCheck } from "lucide-react";

import { CertificateDocument } from "../../../../shared/components/certificate-document/CertificateDocument";
import { Button } from "@/shared/components/ui/button";

import type { StudentCertificate } from "../../../types";
import { DETAIL_DIALOG_LABELS, LIVE_STATUSES } from "../constants";

interface CertificateDetailDialogProps {
  certificate: StudentCertificate;
  onOpenChange: (open: boolean) => void;
}

// CertificateDetailDialog: modal that shows the full Masar CertificateDocument
// artifact for an issued certificate, plus its cert number and Download/Verify
// actions. For non-live (terminal) records it shows the document (from stored
// identity) with a muted notice instead of download/verify. Built on the
// radix-ui Dialog primitive following the app's dialog conventions.
export function CertificateDetailDialog({
  certificate,
  onOpenChange,
}: CertificateDetailDialogProps) {
  const isLive = LIVE_STATUSES.includes(certificate.status);

  const document = {
    studentName: "Nour El-Sayed",
    title: certificate.listingTitle,
    field: certificate.field,
    companyName: certificate.companyName,
    issuedOn: certificate.issuedOn ?? "",
    certId: certificate.certNumber ?? `MASAR-${certificate.id}`,
  };

  return (
    <Dialog.Root open onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/50 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
        <Dialog.Content
          data-slot="certificate-detail-dialog"
          className="fixed left-1/2 top-1/2 z-50 flex max-h-[90vh] w-full max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 overflow-y-auto rounded-xl border border-border bg-background p-6 shadow-lg data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
          <Dialog.Title className="sr-only">
            {DETAIL_DIALOG_LABELS.viewCertificate}
          </Dialog.Title>

          <CertificateDocument data={document} variant="paper" />

          {certificate.certNumber ? (
            <p className="-mt-1 flex items-center justify-center gap-1.5 text-center font-mono text-xs text-secondary-text">
              <ShieldCheck className="size-3.5" />
              {certificate.certNumber}
            </p>
          ) : null}

          {!isLive ? (
            <p className="rounded-lg bg-warning-bg px-3 py-2 text-center text-xs font-medium text-warning-fg">
              This certificate is no longer active.
            </p>
          ) : null}

          <div className="mt-1 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            {isLive ? (
              <Button type="button" size="sm" variant="default" asChild>
                <a href="#" aria-disabled="true">
                  <Download className="size-4" />
                  {DETAIL_DIALOG_LABELS.download}
                </a>
              </Button>
            ) : null}
            <Dialog.Close asChild>
              <Button variant="outline" size="sm">
                {DETAIL_DIALOG_LABELS.closed}
              </Button>
            </Dialog.Close>
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}
