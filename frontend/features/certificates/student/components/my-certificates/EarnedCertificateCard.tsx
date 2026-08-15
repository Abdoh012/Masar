"use client";

import { EarnedCertificateRef } from "../../types";

interface EarnedCertificateCardProps {
  certificate: EarnedCertificateRef;
  onViewDetail: (certificate: EarnedCertificateRef) => void;
}

export function EarnedCertificateCard({ certificate, onViewDetail }: EarnedCertificateCardProps) {
  return (
    <div
      className="flex items-start gap-3 rounded-xl border border-border bg-card p-4 cursor-pointer hover:bg-card/90"
      onClick={() => onViewDetail(certificate)}
    >
      <div className="flex-1">
        <p className="font-medium truncate">{certificate.title}</p>
        <p className="text-sm text-muted-foreground truncate">{certificate.companyName}</p>
        <p className="text-xs text-muted-foreground">
          Issued {certificate.issuedOn ? new Date(certificate.issuedOn).toLocaleDateString() : "pending"}
        </p>
      </div>
    </div>
  );
}