"use client";

import { useRef, useState } from "react";

import { Download, Loader2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { showError, showSuccess } from "@/shared/lib/notifications";

import { CertificateDocument } from "../../../../shared/components/certificate-document/CertificateDocument";
import type { StudentCertificate } from "../../../types";
import { buildCertificateDocument } from "../../../lib/certificate-document";
import { downloadElementAsPdf } from "../../../lib/certificate-pdf";
import { DOWNLOAD_LABELS } from "../constants";

interface DownloadCertificateButtonProps {
  certificate: StudentCertificate;
  className?: string;
}

// DownloadCertificateButton: client leaf that downloads a certificate as a
// PDF built from the REAL rendered CertificateDocument artifact (client-side
// raster → jsPDF — the backend has no download endpoint; can_download is
// always false there). Renders a hidden off-screen copy of the document it
// prints (fixed A4 width so the PDF scale matches the UI), and on click
// captures it and saves "MASAR-….pdf" (the cert number, same convention as
// the preview's certId). Owns its own pending state so duplicate downloads
// are impossible while one is in flight; success/error feedback goes through
// the shared toast helpers. Lives in the certificate detail dialog — the row
// surfaces download only via that dialog.
export function DownloadCertificateButton({
  certificate,
  className,
}: DownloadCertificateButtonProps) {
  const [isPending, setIsPending] = useState(false);
  const captureRef = useRef<HTMLDivElement | null>(null);

  const document = buildCertificateDocument(certificate);

  const handleDownload = async () => {
    if (isPending || !captureRef.current) return;

    setIsPending(true);
    try {
      await downloadElementAsPdf(captureRef.current, `${document.certId}.pdf`);
      showSuccess(DOWNLOAD_LABELS.success);
    } catch {
      showError(DOWNLOAD_LABELS.error);
    } finally {
      setIsPending(false);
    }
  };

  return (
    <>
      <Button
        type="button"
        size="sm"
        variant="default"
        className={className}
        disabled={isPending}
        onClick={handleDownload}
      >
        {isPending ? (
          <Loader2 className="size-4 animate-spin" />
        ) : (
          <Download className="size-4" />
        )}
        <span>{DOWNLOAD_LABELS.download}</span>
      </Button>

      {/* Hidden capture copy — the off-screen positioning lives on the outer
          (non-captured) wrapper because html-to-image clones the captured
          node's computed position into its SVG foreignObject: if the captured
          node itself carried `left: -9999px`, the whole document would render
          off-canvas and the PDF would come out blank. The captured node below
          sits at the natural origin and carries only the fixed A4 width. */}
      <div aria-hidden="true" className="pointer-events-none fixed -left-[9999px] top-0 z-[-1]">
        <div ref={captureRef} className="w-[794px]">
          <CertificateDocument data={document} variant="paper" />
        </div>
      </div>
    </>
  );
}