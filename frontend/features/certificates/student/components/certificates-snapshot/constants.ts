import type { CertificateSummary } from "../../types";

// Mock certificates-snapshot data (UI-only).

export const CERTIFICATES: CertificateSummary = {
  totalCount: 3,
  mostRecent: {
    studentName: "Nour El-Sayed",
    title: "Software Engineering Internship",
    field: "Software Engineering",
    companyName: "Hala Bank",
    issuedOn: "June 15, 2026",
    certId: "CERT-2026-EG-00482",
  },
};

// Empty variant: no certificates → NoCertificatesYet state.
export const CERTIFICATES_NONE: CertificateSummary = {
  totalCount: 0,
  mostRecent: null,
};
