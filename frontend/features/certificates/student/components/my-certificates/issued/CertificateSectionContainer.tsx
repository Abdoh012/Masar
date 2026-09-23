import { BadgeCheck } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import {
  fetchIssuedCertificates,
  fetchPendingCertificates,
  fetchRevokedCertificates,
} from "../../../api";
import { normalizeStudentCertificates } from "../../../lib/normalize";
import { CERTIFICATE_GROUPS } from "../constants";
import { CertificateGroup } from "./CertificateGroup";

// CertificateSectionContainer: server orchestrator for the "Your certificates"
// section. Fetches the three state-specific lists server-side —
// GET /certificates/pending, /issued, /revoked — and feeds one CertificateGroup
// per list (requested / issued / revoked). Throws on a failed fetch so the
// route-level error boundary renders; empty lists fall through to each group's
// own empty state. Composition only — no detailed markup of its own.
export async function CertificateSectionContainer() {
  const [pendingRes, issuedRes, revokedRes] = await Promise.all([
    fetchPendingCertificates(),
    fetchIssuedCertificates(),
    fetchRevokedCertificates(),
  ]);

  for (const [res, label] of [
    [pendingRes, "requested certificates"],
    [issuedRes, "issued certificates"],
    [revokedRes, "revoked certificates"],
  ] as const) {
    if (!res.success || res.data === undefined) {
      throw new Error(res.error ?? `Failed to load ${label}.`);
    }
  }

  const pending = normalizeStudentCertificates(pendingRes.data as unknown[]);
  const issued = normalizeStudentCertificates(issuedRes.data as unknown[]);
  const revoked = normalizeStudentCertificates(revokedRes.data as unknown[]);

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

      <CertificateGroup config={CERTIFICATE_GROUPS.requested} items={pending} />

      <CertificateGroup config={CERTIFICATE_GROUPS.issued} items={issued} />

      <CertificateGroup config={CERTIFICATE_GROUPS.revoked} items={revoked} />
    </Motion>
  );
}