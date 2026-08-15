// Role-level types for the certificates student (structure rules §14).

import type { CertificateDocument } from "../shared/types";

export interface CertificateSummary {
  totalCount: number;
  mostRecent: CertificateDocument | null;
}

// Request status for eligible certificate items.
export type RequestStatus = "not-requested" | "pending";

// Eligible certificate: a completed training where the student can request a certificate.
export interface EligibleCertificate {
  id: string;
  listingId: string;
  listingTitle: string;
  companyName: string;
  completedOn: string;
  requestStatus: RequestStatus;
  mayLeadToHire?: boolean;
}

// Earned certificate reference: identity data for the shared CertificateDocument.
export interface EarnedCertificateRef {
  studentName: string;
  title: string;
  field: string;
  companyName: string;
  issuedOn: string;
  certId: string;
}