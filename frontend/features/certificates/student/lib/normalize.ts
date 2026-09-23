// API → UI mapping for the student certificates page (structure rules §14 —
// role-level normalize.ts, the same convention as listings/applications). The
// raw backend DTOs (statistics, eligible feed, certificate records) are mapped
// to the page's types here, in one place, keeping containers free of coercion.
//
//   - eligible card: the statistics endpoint's `valid` count — the backend
//     reports the eligible/requestable certificate count under that key (the
//     endpoint has no literal "eligible" key).
//   - issued card + header chip: statistics.issued + statistics.active — the
//     "live/verifiable" set (backend certificate_status_is_valid() and the
//     frontend LIVE_STATUSES agree). `total` includes terminal records, so it
//     is not a correct "issued" count.
//   - pending card: statistics.pending.
import type {
  CertificateCounts,
  CertificateStatistics,
  CertificateStatus,
  EligibleTraining,
  StudentCertificate,
} from "../types";

function toCount(value: unknown): number {
  const n = typeof value === "number" ? value : Number(value);
  return Number.isFinite(n) && n > 0 ? Math.floor(n) : 0;
}

export function normalizeCertificateCounts(
  statistics: CertificateStatistics,
): CertificateCounts {
  return {
    eligible: toCount(statistics.valid),
    pending: toCount(statistics.pending),
    issued: toCount(statistics.issued) + toCount(statistics.active),
  };
}

// Maps the /certificates/eligible feed to EligibleTraining. The DTO carries no
// specialization/field and no hire-intent flag, so field stays "" — the
// eligible card doesn't render it, and a pending record built from an eligible
// item renders without the "— field" suffix (see StudentCertificateCard).
export function normalizeEligibleTrainings(raw: unknown[]): EligibleTraining[] {
  return raw
    .filter(
      (item): item is Record<string, unknown> =>
        !!item && typeof item === "object" && !Array.isArray(item),
    )
    .map((item) => {
      const trainingId = toCount(item.training_id);
      return {
        id: String(trainingId),
        listingId: String(trainingId),
        listingTitle: String(item.training_title ?? ""),
        field: "",
        companyName: String(item.company_name ?? ""),
        completedOn: item.completed_on ? String(item.completed_on) : "",
      };
    });
}

// Maps the /certificates list presenter output to StudentCertificate,
// field-for-field. studentName maps the presenter's student.full_name (what
// the certificate document prints). can_download / can_verify come straight
// from the API (the list presenter currently reports can_download=false on
// every record — the frontend gates downloads on LIVE_STATUSES instead and
// builds the PDF client-side; these flags are kept mapped for API fidelity).
// mayLeadToHire maps the presenter's employment_eligible flag (0/1).
export function normalizeStudentCertificates(
  raw: unknown[],
): StudentCertificate[] {
  return raw
    .filter(
      (item): item is Record<string, unknown> =>
        !!item && typeof item === "object" && !Array.isArray(item),
    )
    .map((item) => ({
      id: String(item.id ?? ""),
      listingId: String(item.training_id ?? item.id ?? ""),
      listingTitle: String(item.training_title ?? ""),
      field: String(item.specialization_name ?? ""),
      companyName: String(item.company_name ?? ""),
      studentName: String(
        (item.student as Record<string, unknown> | undefined)?.full_name ??
          "",
      ),
      status: (item.status ?? "pending") as CertificateStatus,
      grade: item.grade ? String(item.grade) : undefined,
      gradeLabel: item.grade_label ? String(item.grade_label) : undefined,
      requestedOn: item.requested_at ? String(item.requested_at) : undefined,
      issuedOn: item.issued_at ? String(item.issued_at) : undefined,
      certNumber: item.certificate_number
        ? String(item.certificate_number)
        : undefined,
      revokedOn: item.revoked_at ? String(item.revoked_at) : undefined,
      revokeReason: item.revocation_reason
        ? String(item.revocation_reason)
        : undefined,
      canDownload: Boolean(item.can_download),
      canVerify: Boolean(item.can_verify),
      mayLeadToHire:
        item.employment_eligible === 1 || item.employment_eligible === "1"
          ? true
          : undefined,
    }));
}