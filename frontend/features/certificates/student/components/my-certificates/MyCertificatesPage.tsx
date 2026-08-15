"use client";

import { useState, useEffect } from "react";

import type { EligibleCertificate } from "../../types";
import type { EarnedCertificateRef } from "../../types";

import { MOCK_ELIGIBLE_CERTIFICATES } from "./constants";
import { MOCK_CERTIFICATES } from "./constants";

import { EligibleCertificatesSection } from "./EligibleCertificatesSection";
import { EarnedCertificateCard } from "./EarnedCertificateCard";
import { CertificateDetailDialog } from "./CertificateDetailDialog";
import { EmptyCertificatesState } from "./EmptyCertificatesState";

import { showSuccess } from "@/shared/lib/notifications";

export function MyCertificatesPage() {
  const [earned] = useState<EarnedCertificateRef[]>(MOCK_CERTIFICATES);
  const [requestStatus, setRequestStatus] = useState<
    "not-requested" | "pending"
  >("not-requested");
  const earnedCount = earned.length;

  const eligible = MOCK_ELIGIBLE_CERTIFICATES;
  const eligibleHasContent = eligible.length > 0;
  const earnedHasContent = earned.length > 0;

  // Both sections empty → show EmptyCertificatesState; suppress section content
  const bothEmpty = !eligibleHasContent && !earnedHasContent;

  // Detailed certificate state for the modal dialog
  const [detailedCertificate, setDetailedCertificate] = useState<
    EarnedCertificateRef | undefined
  >(undefined);

  // Toast state for request certificate success feedback
  const [requestToast, setRequestToast] = useState(false);

  const onRequestCertificate = (
    certificate: import("../../types").EligibleCertificate,
  ) => {
    setRequestStatus("pending");
    setRequestToast(true);
    showSuccess("Certificate request submitted successfully.");
  };

  const onViewDetail = (certificate: EarnedCertificateRef) => {
    setDetailedCertificate(certificate);
  };

  const onCloseDetail = () => {
    setDetailedCertificate(undefined);
  };

  // Toast auto-clears via sonner's default behavior
  // (sonner toasts auto-dismiss after 5s, or on next interaction)

  useEffect(() => {
    if (requestToast) {
      const timeout = setTimeout(() => setRequestToast(false), 3000);
      return () => clearTimeout(timeout);
    }
  }, [requestToast]);

  return (
    <>
      <h1 className="font-sans text-xl font-semibold text-primary-text">
        My Certificates
      </h1>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {/* Eligible section */}
        {bothEmpty ? // Both empty → don't show eligible section content; EmptyCertificatesState handles it
        null : eligibleHasContent ? (
          // Eligible has content → show card section
          <EligibleCertificatesSection
            earnedCount={earnedCount}
            onRequestCertificate={onRequestCertificate}
          />
        ) : // Eligible empty but earned has content → eligible section absent
        bothEmpty ? null : null}

        {/* Earned section */}
        {bothEmpty ? // Both empty → don't show earned section content
        null : earnedHasContent ? (
          // Earned has content → show certificate cards
          <div className="space-y-4">
            <h2 className="text-lg font-medium text-primary-text">
              Earned certificates
            </h2>
            <div className="space-y-3">
              {earned.map((cert) => (
                <EarnedCertificateCard
                  key={cert.certId}
                  certificate={cert}
                  onViewDetail={onViewDetail}
                />
              ))}
            </div>
          </div>
        ) : bothEmpty ? // Both empty → don't show earned section content
        null : (
          // Eligible has content, earned empty → show empty state message in earned section
          <div className="space-y-4">
            <h2 className="text-lg font-medium text-primary-text">
              Earned certificates
            </h2>
            <p className="text-sm text-muted-foreground">
              No certificates yet — complete a training to earn your first
              certificate.
            </p>
          </div>
        )}
      </div>

      {/* Certificate detail dialog: only when a certificate is selected */}
      {detailedCertificate && (
        <CertificateDetailDialog
          certificate={detailedCertificate}
          onOpenChange={onCloseDetail}
        />
      )}

      {/* Empty state overlay - ONLY when BOTH sections are empty */}
      {bothEmpty && <EmptyCertificatesState variant="both-empty" />}
    </>
  );
}
