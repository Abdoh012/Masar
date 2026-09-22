import { Award } from "lucide-react";

import { PageHeader } from "@/shared/components/page-header/PageHeader";

import { CertificateCounts } from "../../types";
import { CertificateHowItWorks } from "./CertificateHowItWorks";

import { PAGE_HEADER } from "./constants";
import { SummaryCounts } from "./summary-counts/SummaryCounts";
import { EligibleSectionContainer } from "./eligible/EligibleSectionContainer";

import { fetchCertificateStats } from "../../api";

import type { CertificateStatistics } from "../../types";
import { normalizeCertificateCounts } from "../../lib/normalize";

export async function CertificatePageContent() {
  const statsRes = await fetchCertificateStats();

  if (!statsRes.success || statsRes.data === undefined) {
    throw new Error(statsRes.error ?? "Failed to load certificate statistics.");
  }

  const initialCounts: CertificateCounts = normalizeCertificateCounts(
    statsRes.data as CertificateStatistics,
  );

  return (
    <div className="space-y-8">
      {/* Header */}
      <PageHeader
        {...PAGE_HEADER}
        icon={<Award className="size-6" />}
        actions={
          <span className="flex items-center gap-1.5 rounded-full bg-primary-tint px-3 py-1.5 text-sm font-semibold text-primary-text">
            <Award className="size-4" />
            {initialCounts.issued} issued
          </span>
        }
      />

      {/* Summary counts */}
      <SummaryCounts counts={initialCounts} />

      {/* How it works */}
      <CertificateHowItWorks />

      {/* Eligible to request */}

      <EligibleSectionContainer />

      {/* Your certificates */}
      {/* <CertificateSectionContainer /> */}
    </div>
  );
}
