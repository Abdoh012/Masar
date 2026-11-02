// Maps a StudentCertificate record to the shared CertificateDocument data
// rendered by the CertificateDocument template (structure rules §16 — the
// document leaf takes plain data, this derivation happens once, upstream).
// Used by the CertificateDetailDialog preview and the client-side PDF
// download, so the document a user downloads is exactly the one they see.
import type { CertificateDocument as CertificateDocumentData } from "@/shared/types/certificateDocument";
import type { SnapshotCertificate, StudentCertificate } from "../types";

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

// The dashboard's snapshot record maps to the document the same way a page
// record does — the training title is the document's title, the student's own
// field is its field — with one difference: `issuedAt` arrives as the
// repository's raw MySQL datetime, so it is formatted here rather than printed
// as "2026-09-16 04:15:01". The document and the caption beneath it read this
// one string, so the two can never disagree.
export function buildSnapshotCertificateDocument(
  certificate: SnapshotCertificate,
  issuedOn: string,
): CertificateDocumentData {
  return {
    studentName: certificate.studentName,
    title: certificate.trainingTitle,
    field: certificate.specializationName ?? certificate.field ?? "",
    companyName: certificate.companyName ?? "",
    issuedOn,
    certId: certificate.certificateNumber ?? `MASAR-${certificate.id}`,
    grade: certificate.grade,
    gradeLabel: certificate.gradeLabel,
  };
}