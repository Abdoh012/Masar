"use client";

import { EligibleCertificateCard } from "./EligibleCertificateCard";
import { EligibleCertificate } from "../../types";

import { MOCK_ELIGIBLE_CERTIFICATES } from "./constants";

interface EligibleCertificatesSectionProps {
  earnedCount: number;
  onRequestCertificate: (certificate: import("../../types").EligibleCertificate) => void;
}

export function EligibleCertificatesSection({
  earnedCount,
  onRequestCertificate,
}: EligibleCertificatesSectionProps) {
  const eligible = MOCK_ELIGIBLE_CERTIFICATES;

  return (
    <div className="space-y-4">
      <h2 className="text-lg font-medium text-primary-text">Eligible for certificate</h2>

      {eligible.length > 0 ? (
        <div className="space-y-3">
          {eligible.map((cert) => (
            <EligibleCertificateCard
              key={cert.id}
              certificate={cert}
              onRequestCertificate={onRequestCertificate}
            />
          ))}
        </div>
      ) : (
        <p className="text-sm text-muted-foreground">
          No eligible trainings yet — complete a training to receive a certificate.
        </p>
      )}
    </div>
  );
}