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

// --- Page model (my-certificates) ---

// Certificate lifecycle status of a certificate record, mirroring the backend
// certificate_statuses enum. "issued" and "active" are valid + verifiable;
// "revoked", "expired", "cancelled" are terminal.
export type CertificateStatus =
  | "pending"
  | "issued"
  | "active"
  | "revoked"
  | "expired"
  | "cancelled";

// An eligible completed training: the student can request a certificate for
// it. Requested items leave this list and surface as pending certificate
// records in the "Requested certificates" group instead.
export interface EligibleTraining {
  id: string;
  listingId: string;
  listingTitle: string;
  field: string;
  companyName: string;
  completedOn: string;
  mayLeadToHire?: boolean;
}

// A certificate record the student owns (requested, issued, or terminal).
export interface StudentCertificate {
  id: string;
  listingId: string;
  listingTitle: string;
  field: string;
  companyName: string;
  status: CertificateStatus;
  requestedOn?: string;
  issuedOn?: string;
  certNumber?: string;
  revokedOn?: string;
  revokeReason?: string;
  canDownload: boolean;
  canVerify: boolean;
  mayLeadToHire?: boolean;
}

// Per-tab summary counts shown in the page header.
export interface CertificateCounts {
  eligible: number;
  pending: number;
  issued: number;
}

// The student's full certificates page state (UI-only; no backend).
export interface CertificatesPageState {
  eligible: EligibleTraining[];
  certificates: StudentCertificate[];
  counts: CertificateCounts;
}
