"use client";

import { useState } from "react";

import { Button } from "@/shared/components/ui/button";

import type { StudentCertificate } from "../../../types";
import { DETAIL_DIALOG_LABELS, LIVE_STATUSES } from "../constants";
import { CertificateDetailDialog } from "./CertificateDetailDialog";

interface CertificateCardActionsProps {
  certificate: StudentCertificate;
}

// CertificateCardActions: client leaf owning the interactive actions on a
// certificate row. "View certificate" (shown for live records) opens the shared
// CertificateDetailDialog, which hosts the Download button — the row itself
// stays clean, with download reachable only inside the dialog.
export function CertificateCardActions({
  certificate,
}: CertificateCardActionsProps) {
  const [dialogOpen, setDialogOpen] = useState(false);
  const isLive = LIVE_STATUSES.includes(certificate.status);

  return (
    <>
      <div className="flex shrink-0 items-center gap-2 self-start sm:self-center">
        {isLive ? (
          <Button
            type="button"
            size="sm"
            variant="outline"
            className="cursor-pointer bg-primary text-white hover:bg-primary/90"
            onClick={() => setDialogOpen(true)}
          >
            {DETAIL_DIALOG_LABELS.viewCertificate}
          </Button>
        ) : null}
      </div>

      {dialogOpen ? (
        <CertificateDetailDialog
          certificate={certificate}
          onOpenChange={setDialogOpen}
        />
      ) : null}
    </>
  );
}
