import type { CertificateSummary } from "../../types";

// Mock eligible certificates data (UI-only).
// Training completions where the student has not yet requested a certificate.
export const MOCK_ELIGIBLE_CERTIFICATES = [
  {
    id: "cert-001",
    listingId: "lst-001",
    listingTitle: "Software Engineering Internship",
    companyName: "Hala Bank",
    completedOn: "2026-06-15",
    requestStatus: "not-requested" as const,
    mayLeadToHire: true,
  },
  {
    id: "cert-002",
    listingId: "lst-002",
    listingTitle: "Data Science Trainee",
    companyName: "NileGrants",
    completedOn: "2026-05-20",
    requestStatus: "not-requested" as const,
  },
];

// Mock earned certificates data (UI-only).
// Confirmed certificate records rendered via CertificateDocument.
export const MOCK_CERTIFICATES = [
  {
    studentName: "Nour El-Sayed",
    title: "Software Engineering Internship",
    field: "Software Engineering",
    companyName: "Hala Bank",
    issuedOn: "June 15, 2026",
    certId: "CERT-2026-EG-00482",
  },
];

// Empty variant: no certificates → NoCertificatesYet state.
export const MOCK_CERTIFICATES_NONE: CertificateSummary = {
  totalCount: 0,
  mostRecent: null,
};

// Format a certificate issue date as a short display date.
// Parsed in UTC so the value never shifts across timezones.
const SHORT_DATE = new Intl.DateTimeFormat("en-US", {
  month: "short",
  day: "numeric",
  year: "numeric",
  timeZone: "UTC",
});

export function formatCertificateDate(isoDate: string): string {
  const [year, month, day] = isoDate.split("-").map(Number);
  return SHORT_DATE.format(new Date(Date.UTC(year, month - 1, day)));
}

// Empty variants per UX expectation #7.
export const CERTIFICATES_EMPTY: CertificateSummary = {
  totalCount: 0,
  mostRecent: null,
};

// Copy labels for empty states.
export const EMPTY_STATE_LABELS = {
  bothEmpty: "No certificates yet — complete a training to earn your first certificate.",
  eligibleOnly: "No eligible trainings yet — complete a training to receive a certificate.",
  earnedOnly: "Your completed trainings will appear here once you request a certificate.",
};