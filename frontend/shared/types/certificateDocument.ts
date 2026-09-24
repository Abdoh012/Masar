// Promoted business type (structure rules §15, architecture R7): the Masar
// certificate document data shape. Originally features/certificates/shared/
// types.ts; promoted to top-level shared/types/ once the applications feature
// also needed the certificate artifact (the Ended Applications tab renders the
// same CertificateDocument component over completed-training data).

export interface CertificateDocument {
  studentName: string;
  title: string;
  field: string;
  companyName: string;
  issuedOn: string;
  certId: string;
  grade?: string;
  gradeLabel?: string;
}