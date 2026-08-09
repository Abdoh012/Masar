// Role-level types for the certificates student (structure rules §14).

import type { CertificateDocument } from "../shared/types";

export interface CertificateSummary {
  totalCount: number;
  mostRecent: CertificateDocument | null;
}