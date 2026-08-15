// Public surface for the "certificates" feature.
// The fixed Masar certificate template lives in shared/ — every role renders the same component, only the data differs. See masar-spec.pdf Section 7.
//
// Only export what other parts of the app (routes, other features via
// shared/) are meant to consume. Nothing outside this feature should ever
// import from a deeper path than this file (R8). Features never import
// from each other directly — promote to top-level shared/ on second use (R7).

export { CertificatesSnapshot } from "./student/components/certificates-snapshot/CertificatesSnapshot";
export { CertificateDocument } from "./shared/components/certificate-document/CertificateDocument";
export { SealMark } from "./shared/components/certificate-document/SealMark";
export type { EligibleCertificate } from "./student/types";
export type { EarnedCertificateRef } from "./student/types";
export type { RequestStatus } from "./student/types";
export { MyCertificatesPage } from "./student/components/my-certificates/MyCertificatesPage";
export { EligibleCertificateCard } from "./student/components/my-certificates/EligibleCertificateCard";
export { EarnedCertificateCard } from "./student/components/my-certificates/EarnedCertificateCard";