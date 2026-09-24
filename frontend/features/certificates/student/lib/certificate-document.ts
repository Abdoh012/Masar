// Maps a StudentCertificate record to the shared CertificateDocument data
// rendered by the CertificateDocument template (structure rules §16 — the
// document leaf takes plain data, this derivation happens once, upstream).
// Used by the CertificateDetailDialog preview and the client-side PDF
// download, so the document a user downloads is exactly the one they see.
import type { CertificateDocument as CertificateDocumentData } from "@/shared/types/certificateDocument";
import type { StudentCertificate } from "../types";

export function buildCertificateDocument(
  certificate: StudentCertificate,
): CertificateDocumentData {
  return {
    studentName: certificate.studentName,
    title: certificate.listingTitle,
    field: certificate.field,
    companyName: certificate.companyName,
    issuedOn: certificate.issuedOn ?? "",
    certId: certificate.certNumber ?? `MASAR-${certificate.id}`,
    grade: certificate.grade,
    gradeLabel: certificate.gradeLabel,
  };
}