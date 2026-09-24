// Maps an EndedApplication (one /applications/certificates issued record —
// the completed training the "Ended Applications" tab shows) to the shared
// CertificateDocument data rendered by the view-only modal. Same job as the
// certificates feature's buildCertificateDocument: the document is populated
// from the API-returned training data, so what the user sees in the popup is
// exactly what the endpoint reported.
import type { CertificateDocument as CertificateDocumentData } from "@/shared/types/certificateDocument";

import type { EndedApplication } from "../types";

export function buildEndedCertificateDocument(
  ended: EndedApplication,
): CertificateDocumentData {
  return {
    studentName: ended.studentName,
    title: ended.listingTitle,
    field: ended.specialization,
    companyName: ended.companyName,
    issuedOn: ended.issuedOn,
    certId: ended.certNumber || `MASAR-${ended.id}`,
    grade: ended.grade,
    gradeLabel: ended.gradeLabel,
  };
}