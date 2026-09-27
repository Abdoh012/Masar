import { Award } from "lucide-react";

import { CertificateDocument } from "@/shared/components/certificate-document/CertificateDocument";
import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";
import Motion from "@/shared/components/animation/Motion";
import { scaleIn } from "@/shared/lib/animations";

import { buildSnapshotCertificateDocument } from "../../lib/certificate-document";
import type { CertificateSnapshotCounts, SnapshotCertificate } from "../../types";
import { CertificateCountsRow } from "./CertificateCountsRow";
import {
  CERTIFICATES_ISSUED_LABEL,
  CERTIFICATE_DOCUMENT_CAPTION,
  formatCertificateIssuedOn,
} from "./constants";
import { NoCertificatesYet } from "./NoCertificatesYet";
import { VerifiedSeal } from "./VerifiedSeal";

interface CertificatesSnapshotProps {
  counts: CertificateSnapshotCounts;
  recent: SnapshotCertificate[];
}

// CertificatesSnapshot: the issued count, the four lifecycle counts, and the
// most recent certificate rendered as the identity's certificate document
// (reuses the shared CertificateDocument), matted like a framed print and
// stamped with the verification seal.
//
// Everything arrives from the dashboard orchestrator, which reads it off
// GET /students/dashboard. The previous build drew this card from a static
// summary, so every student saw the same three certificates and the same name
// printed on the document.
export function CertificatesSnapshot({
  counts,
  recent,
}: CertificatesSnapshotProps) {
  const mostRecent = recent[0] ?? null;
  const issuedOn = formatCertificateIssuedOn(mostRecent?.issuedAt ?? null);
  const document = mostRecent
    ? buildSnapshotCertificateDocument(mostRecent, issuedOn)
    : null;

  return (
    <DashboardSection>
      <DashboardSectionHeading
        title="Certificates"
        icon={
          <span className="flex size-7 items-center justify-center rounded-full bg-secondary-tint text-secondary-text">
            <Award className="size-4" />
          </span>
        }
        action={
          counts.issued > 0 ? (
            <span className="shrink-0 rounded-full bg-secondary-tint px-3 py-1 text-xs font-semibold uppercase tracking-wide text-secondary-text">
              {counts.issued} {CERTIFICATES_ISSUED_LABEL}
            </span>
          ) : null
        }
      />

      {/* Certificate content (either the recent certificate document or the empty state) */}
      {document ? (
        // Centred in whatever height the row resolves to, so sharing a row with
        // the notifications feed reads as a deliberate mount rather than as a
        // card that ran out of content.
        <div className="mt-5 flex flex-1 flex-col justify-center">
          {/* Document mat — `paper` is the certificate-only neutral per the design
              system, so the document sits on a mount rather than the card surface. */}
          <div className="relative rounded-xl bg-neutral-100 p-4 dark:bg-neutral-800">
            <VerifiedSeal className="absolute -right-2 -top-2" />

            <Motion
              variants={scaleIn}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true }}
              className="rounded-lg shadow-card-md"
            >
              <CertificateDocument data={document} compact />
            </Motion>
          </div>

          {issuedOn ? (
            <p className="mt-4 text-center font-mono text-xs text-muted-foreground">
              {CERTIFICATE_DOCUMENT_CAPTION} — issued {issuedOn}
            </p>
          ) : null}
        </div>
      ) : (
        <NoCertificatesYet />
      )}

      <CertificateCountsRow counts={counts} />
    </DashboardSection>
  );
}
