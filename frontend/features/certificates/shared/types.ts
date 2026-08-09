// Shared types for certificates — used across roles within the feature
// (certificate-document shared component + student certificates-snapshot).

export interface CertificateDocument {
  studentName: string;
  title: string;
  field: string;
  companyName: string;
  issuedOn: string;
  certId: string;
}