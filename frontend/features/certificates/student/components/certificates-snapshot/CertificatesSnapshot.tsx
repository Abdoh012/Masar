import { Award } from "lucide-react";

import { CertificateDocument } from "@/shared/components/certificate-document/CertificateDocument";
import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";
import Motion from "@/shared/components/animation/Motion";
import { scaleIn } from "@/shared/lib/animations";

import { CERTIFICATES } from "./constants";
import { NoCertificatesYet } from "./NoCertificatesYet";
import { VerifiedSeal } from "./VerifiedSeal";

// CertificatesSnapshot: total count + the most recent certificate rendered as
// the identity's certificate document (reuses the shared CertificateDocument),
// matted like a framed print and stamped with the verification seal.
export function CertificatesSnapshot() {
  const { totalCount, mostRecent } = CERTIFICATES;

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
          mostRecent ? (
            <span className="shrink-0 rounded-full bg-secondary-tint px-3 py-1 text-xs font-semibold uppercase tracking-wide text-secondary-text">
              {totalCount} certificates
            </span>
          ) : null
        }
      />

      {/* Certificate content (either the recent certificate document or the empty state) */}
      {mostRecent ? (
        // Centred in whatever height the row resolves to, so sharing a row with
        // the (capped) notifications feed reads as a deliberate mount rather
        // than as a card that ran out of content.
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
              <CertificateDocument data={mostRecent} compact />
            </Motion>
          </div>

          <p className="mt-4 text-center font-mono text-xs text-muted-foreground">
            Verified certificate · Issued {mostRecent.issuedOn}
          </p>
        </div>
      ) : (
        <NoCertificatesYet />
      )}
    </DashboardSection>
  );
}
