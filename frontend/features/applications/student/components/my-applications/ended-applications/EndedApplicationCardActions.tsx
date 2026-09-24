"use client";

import { useState } from "react";

import { Button } from "@/shared/components/ui/button";

import type { EndedApplication } from "../../../types";
import { ENDED_CARD_LABELS } from "./constants";
import { EndedCertificateDialog } from "./EndedCertificateDialog";

interface EndedApplicationCardActionsProps {
  ended: EndedApplication;
}

// EndedApplicationCardActions: client leaf owning the ended card's action row.
// The single "View Certificate" action opens this card's controlled
// EndedCertificateDialog (which hosts the shared CertificateDocument) — no
// other actions on an ended application, so the row stays clean. The dialog
// stays mounted (controlled) so its exit animation plays on close.
export function EndedApplicationCardActions({
  ended,
}: EndedApplicationCardActionsProps) {
  const [dialogOpen, setDialogOpen] = useState(false);

  return (
    <>
      <div className="mt-auto flex flex-wrap items-center gap-2 pt-1">
        <Button
          type="button"
          size="sm"
          variant="outline"
          className="cursor-pointer"
          onClick={() => setDialogOpen(true)}
        >
          {ENDED_CARD_LABELS.viewCertificate}
        </Button>
      </div>

      <EndedCertificateDialog
        ended={ended}
        open={dialogOpen}
        onOpenChange={setDialogOpen}
      />
    </>
  );
}