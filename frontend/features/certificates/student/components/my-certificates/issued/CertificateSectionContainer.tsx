"use client";

import { useState } from "react";
import { BadgeCheck } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type { StudentCertificate } from "../../../types";
import { CERTIFICATE_GROUPS, isRequestedStatus } from "../constants";
import { CertificateDetailDialog } from "./CertificateDetailDialog";
import { CertificateGroup } from "./CertificateGroup";

// CertificateSectionContainer: orchestrator for the "Your certificates"
// section. "use client" because it owns the detail-dialog selection. Splits
// the certificate records into the still-pending "requested" bucket and the
// issued/terminal bucket, composing each via CertificateGroup. No detailed
// markup of its own.
export function CertificateSectionContainer({
  certificates,
}: {
  certificates: StudentCertificate[];
}) {
  const [detailTarget, setDetailTarget] = useState<StudentCertificate | null>(null);

  const requested = certificates.filter((certificate) =>
    isRequestedStatus(certificate.status),
  );
  const issued = certificates.filter(
    (certificate) => !isRequestedStatus(certificate.status),
  );

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="space-y-4"
    >
      <div className="flex items-center gap-2">
        <span className="flex size-6 items-center justify-center rounded-full bg-secondary-tint text-secondary-text">
          <BadgeCheck className="size-3.5" />
        </span>
        <h2 className="text-base font-semibold text-primary-text">Your certificates</h2>
      </div>

      <CertificateGroup
        config={CERTIFICATE_GROUPS.requested}
        certificates={requested}
        onViewDetail={setDetailTarget}
      />

      <CertificateGroup
        config={CERTIFICATE_GROUPS.issued}
        certificates={issued}
        onViewDetail={setDetailTarget}
      />

      {detailTarget ? (
        <CertificateDetailDialog
          certificate={detailTarget}
          onOpenChange={(open) => {
            if (!open) setDetailTarget(null);
          }}
        />
      ) : null}
    </Motion>
  );
}