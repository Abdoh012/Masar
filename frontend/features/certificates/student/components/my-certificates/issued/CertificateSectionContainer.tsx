import { BadgeCheck } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import { CERTIFICATE_GROUPS } from "../constants";
import { CertificateGroup } from "./CertificateGroup";

// CertificateSectionContainer: orchestrator for the "Your certificates"
// section. Server component — splits the certificate records into the
// still-pending "requested" bucket and the issued/terminal bucket, composing
// each via CertificateGroup. Data wiring (records list) lands here; the groups
// currently render their sample placeholders. No detailed markup of its own.
export function CertificateSectionContainer() {
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

      <CertificateGroup config={CERTIFICATE_GROUPS.requested} />

      <CertificateGroup config={CERTIFICATE_GROUPS.issued} />
    </Motion>
  );
}