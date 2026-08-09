import { Award } from "lucide-react";

import { CertificateDocument } from "@/features/certificates/shared/components/certificate-document/CertificateDocument";
import Motion from "@/shared/components/animation/Motion";
import { fadeInUp, scaleIn } from "@/shared/lib/animations";
import { CERTIFICATES } from "./constants";
import { NoCertificatesYet } from "./NoCertificatesYet";

// CertificatesSnapshot: total count + the most recent certificate rendered as
// the identity's certificate document (reuses the shared CertificateDocument).
export function CertificatesSnapshot() {
  const { totalCount, mostRecent } = CERTIFICATES;

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="flex h-full flex-col rounded-2xl border border-border bg-card p-5 shadow-card"
    >
      <div className="flex items-center justify-between gap-2">
        <h2 className="flex items-center gap-2 text-base font-semibold text-primary-text">
          <span
            aria-hidden="true"
            className="flex size-6 items-center justify-center rounded-full bg-secondary-tint text-secondary-text"
          >
            <Award className="size-3.5" />
          </span>
          Certificates
        </h2>
        {mostRecent ? (
          <span className="shrink-0 rounded-full bg-secondary-tint px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-secondary-text">
            {totalCount} certificates
          </span>
        ) : null}
      </div>

      {mostRecent ? (
        <div className="mt-4">
          <Motion
            variants={scaleIn}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="rounded-xl shadow-card-sm"
          >
            <CertificateDocument data={mostRecent} compact />
          </Motion>
          <p className="mt-3 text-center font-mono text-[11px] text-muted-foreground">
            Verified certificate · Issued {mostRecent.issuedOn}
          </p>
        </div>
      ) : (
        <NoCertificatesYet />
      )}
    </Motion>
  );
}