"use client";

import { EligibleCertificate } from "../../types";

interface EligibleCertificateCardProps {
  certificate: EligibleCertificate;
  onRequestCertificate: (certificate: EligibleCertificate) => void;
}

export function EligibleCertificateCard({ certificate, onRequestCertificate }: EligibleCertificateCardProps) {
  const showPending = certificate.requestStatus === "pending";

  return (
    <div className="flex items-start gap-3 rounded-xl border border-border bg-card p-4">
      <div className="flex-1">
        <p className="font-medium truncate">{certificate.listingTitle}</p>
        <p className="text-sm text-muted-foreground truncate">{certificate.companyName}</p>
        <p className="text-xs text-muted-foreground">
          Completed {certificate.completedOn ? new Date(certificate.completedOn).toLocaleDateString() : "pending"}
        </p>
      </div>

      <div className="text-sm">
        {showPending ? (
          <span className="rounded-full bg-secondary-tint px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-secondary-text">
            Pending confirmation
          </span>
        ) : (
          <button
            onClick={() => onRequestCertificate(certificate)}
            className="rounded-full bg-secondary-tint px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide text-secondary-text cursor-pointer"
            disabled={showPending}
            aria-disabled={showPending}
          >
            Request Certificate
          </button>
        )}
      </div>
    </div>
  );
}