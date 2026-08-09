// Shared certificate-document types. Data always lives in the caller
// (certificates-snapshot/constants.ts etc.).
export interface CertificateDocument {
  studentName: string;
  title: string;
  field: string;
  companyName: string;
  issuedOn: string;
  certId: string;
}